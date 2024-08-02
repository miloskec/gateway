docker build -f ./docker/8.3/Dockerfile --build-arg APP_TIMEZONE=CET --build-arg WWWGROUP=1000 --no-cache  --progress=plain -t gateway:8.3 .
