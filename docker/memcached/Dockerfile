# Use the official memcached alpine image as a base
FROM memcached:alpine

ARG APP_TIMEZONE
ENV TZ=$APP_TIMEZONE
# Switch to root user
USER root

RUN apk update && apk add --no-cache tzdata libmemcached netcat-openbsd && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create memcached user and group (if not exist)
RUN addgroup -S memcached && adduser -S memcached -G memcached
# Switch back to memcached user (if needed)
USER memcached

# Entrypoint to start memcached
ENTRYPOINT ["memcached"]