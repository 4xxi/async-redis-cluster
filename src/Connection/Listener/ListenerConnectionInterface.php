<?php

namespace Fourxxi\AsyncRedisCluster\Connection\Listener;

interface ListenerConnectionInterface
{
    public function onData($chunk);

    public function onError(\Exception $e);

    public function onClose();

    public function onEnd();
}
