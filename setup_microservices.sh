#!/bin/bash

# Define the path to the sail command
SAIL_PATH="vendor/bin/sail"

# Function to check if docker network exists and create if it doesn't
check_create_network() {
  network_name=$1
  if ! docker network ls | grep -q $network_name; then
    echo "Network $network_name does not exist. Creating..."
    docker network create $network_name
  else
    echo "Network $network_name already exists."
  fi
}

# Function to navigate into a folder, run docker-compose up, and optionally wait for services to be healthy
docker_compose_up() {
  folder=$1
  cd "../$folder" # Navigate to the folder in the parent directory
  echo "Starting docker-compose in $folder"

  # Set the time zone environment variable
  if [ -f /etc/timezone ]; then
    TZ=$(cat /etc/timezone)
  else
    TZ=$(date +%Z)
  fi
  echo "Setting time zone to $TZ for $folder"

  export TZ

  TZ=$TZ docker-compose up -d # Pass the TZ variable to docker-compose up

  # Wait for services to be healthy
  echo "Waiting for services in $folder to be healthy..."
  while ! docker-compose ps | grep -q 'healthy'; do
    sleep 5
  done
  echo "Services in $folder are healthy"

  cd - # Navigate back to the original directory
}

# Function to run artisan commands and log output
run_artisan_commands() {
  folder=$1
  log_file=$2

  cd "../$folder"
  echo "Running artisan commands in $folder"

  $SAIL_PATH artisan migrate
  if [ $? -eq 0 ]; then
    echo "Migration successful in $folder"
    if [ "$folder" == "authentication" ]; then
      $SAIL_PATH artisan queue:work >>$log_file 2>&1 &
      echo "Queue in $folder started"
    elif [ "$folder" == "authorization" ]; then
      echo "Seeder in $folder runing..."
      $SAIL_PATH artisan db:seed --class=RoleSeeder
      if [ $? -eq 0 ]; then
        echo "Seeding successful in $folder"
      else
        echo "Seeding failed in $folder"
      fi
      $SAIL_PATH artisan app:consume-kafka-messages >>$log_file 2>&1 &
      echo "Consuming Kafka messages in $folder is set"
    elif [ "$folder" == "profile" ]; then
      $SAIL_PATH artisan app:consume-kafka-messages >>$log_file 2>&1 &
      echo "Consuming Kafka messages in $folder is set"
    fi
  else
    echo "Migration failed in $folder"
  fi
  cd - # Navigate back to the original directory
}

# Start from the gateway folder
echo "Starting setup script from gateway folder..."

# Step 1: Check if network with name "sail" exists and create if not
check_create_network "sail"

# Set the time zone environment variable
if [ -f /etc/timezone ]; then
  TZ=$(cat /etc/timezone)
else
  TZ=$(date +%Z)
fi
echo "Setting time zone to $TZ for gateway"
export TZ

# Step 2: Run docker-compose up in gateway
echo "Starting docker-compose in gateway"
TZ=$TZ docker-compose up -d

# Wait for services in gateway to be healthy
echo "Waiting for services in gateway to be healthy..."
while ! docker-compose ps | grep -q 'healthy'; do
  sleep 5
done
echo "Services in gateway are healthy"

# Get the current time zone inside the container
CONTAINER_TZ=$(docker exec -it gateway-memcached-1 date +%Z)
LOCAL_TZ=$(docker exec -it gateway-gateway-1 date +%Z)

echo "CONTAINER_TZ: $CONTAINER_TZ"
echo "LOCAL_TZ: $LOCAL_TZ"
# Compare and set the time zone if different
if [ "$CONTAINER_TZ" != "$LOCAL_TZ" ]; then
  # Step 2.1: Run necessary commands as root inside the memcached container and set the time zone
  docker exec -u root gateway-memcached-1 sh -c "apk add --no-cache tzdata && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone"
fi

$SAIL_PATH artisan migrate

# Step 3: Get into kafka folder
docker_compose_up "kafka"

# Step 4: Run commands inside kafka container to create a topic
docker exec -it kafka /bin/bash -c "\
  kafka-topics.sh --create --topic user_created_topic --bootstrap-server localhost:9092 --partitions 1 --replication-factor 1  2>/dev/null \
"

# Step 5: Get into authentication folder
docker_compose_up "authentication"

# Step 6: Get into authorization folder
docker_compose_up "authorization"

# Step 7: Get into profile folder
docker_compose_up "profile"

# Step 8: Run artisan commands in authentication folder
run_artisan_commands "authentication" "authentication_setup.log"

# Step 9: Run artisan commands in authorization folder
run_artisan_commands "authorization" "authorization_setup.log"

# Step 10: Run artisan commands in profile folder
run_artisan_commands "profile" "profile_setup.log"

echo "All operations completed successfully."
