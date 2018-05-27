#!/usr/bin/env bash

set -e

if [[ "$1" == 'docker-machine' ]]; then
   DOCKER_HOST=$(docker-machine ip)
elif [[ "$1" == 'native' ]]; then
   DOCKER_HOST=$(ifconfig docker0 | grep "inet addr" | cut -d ':' -f 2 | cut -d ' ' -f 1)
else
    echo "Usage: $0 <docker_type>(docker-machine|native)"

    exit 1
fi

cp docker-compose.yml.dist docker-compose.yml

# .bak for compatibility with mac os
sed -i.bak "s/<DOCKER_HOST>/${DOCKER_HOST}/g" docker-compose.yml && rm -rf docker-compose.yml.bak




