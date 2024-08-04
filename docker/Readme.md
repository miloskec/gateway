## Microservice base

docker build -f ./docker/Dockerfile.base --build-arg APP_TIMEZONE=CET --build-arg WWWGROUP=1000 --no-cache  --progress=plain -t base .  

## Gateway 

docker build -f ./docker/Dockerfile.gateway --build-arg APP_TIMEZONE=CET --build-arg WWWGROUP=1000 --no-cache  --progress=plain -t gateway . 
docker-compose -f docker-compose.prod.yml up --build 

## Gateway development

docker build -f ./docker/Dockerfile.gateway --build-arg APP_TIMEZONE=CET --build-arg WWWGROUP=1000 --no-cache -t gateway:dev . 
docker-compose -f docker-compose.dev.yml up --build 

## Memcached
 
docker build -f ./docker/memcached/Dockerfile --build-arg APP_TIMEZONE=CET --build-arg WWWGROUP=1000 --no-cache  --progress=plain -t gateway-memcached .  

docker-compose -f docker-compose.prod.yml up --build 
docker-compose -f docker-compose.dev.yml up --build 


