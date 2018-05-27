FROM redis:3.2

COPY redis.conf /usr/local/etc/redis/redis.conf
COPY entrypoint.sh /bin/entrypoint.sh

RUN chmod +x /bin/entrypoint.sh

ENV REDIS_PORT=6379

CMD [ "/bin/entrypoint.sh" ]