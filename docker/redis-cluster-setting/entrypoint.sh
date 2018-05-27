#!/usr/bin/env bash

IFS=' ' read -r -a REDIS_NODES_ARRAY <<< "${REDIS_NODES}"

for REDIS_NODE in "${REDIS_NODES_ARRAY[@]}"
do
    ./wait-for-it.sh ${REDIS_NODE}

   if [ ! $? -eq 0 ]; then
    exit 1
   fi
done

exec sh -c "printf \"yes\" | redis-trib.rb create  --replicas ${REDIS_REPLICAS} ${REDIS_NODES}"

