FROM ruby:2.5

RUN gem install redis -v 3.2 && \
    curl -O http://download.redis.io/releases/redis-3.2.11.tar.gz && \
    tar -xvzf redis-3.2.11.tar.gz && \
    chmod +x redis-3.2.11/src/redis-trib.rb && \
    rm -rf redis-3.2.11.tar.gz && \
    ln -s $(pwd)/redis-3.2.11/src/redis-trib.rb /bin/redis-trib.rb

COPY wait-for-it.sh .
COPY entrypoint.sh .

RUN chmod +x wait-for-it.sh entrypoint.sh

ENV REDIS_NODES="192.168.99.100:7000 192.168.99.100:7001 192.168.99.100:7002"
ENV REDIS_REPLICAS=0

ENTRYPOINT ["./entrypoint.sh"]

