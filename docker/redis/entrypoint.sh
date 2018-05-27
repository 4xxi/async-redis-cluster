#!/usr/bin/env bash

sed -i "1iport ${REDIS_PORT}" /usr/local/etc/redis/redis.conf

exec redis-server /usr/local/etc/redis/redis.conf