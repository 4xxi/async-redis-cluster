<?php

namespace Fourxxi\AsyncRedisCluster\Connection;

use Fourxxi\AsyncRedisCluster\Connection\Listener\ListenerConnectionInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class Connection
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var ListenerConnectionInterface
     */
    protected $listenerConnection;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var string
     */
    protected $connectionUrl;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * ReactPHPAsyncConnection constructor.
     *
     * @param LoopInterface               $loop
     * @param ListenerConnectionInterface $listenerConnection
     * @param string                      $connectionUrl
     */
    public function __construct(LoopInterface $loop, ListenerConnectionInterface $listenerConnection, string $connectionUrl)
    {
        $this->loop = $loop;
        $this->listenerConnection = $listenerConnection;
        $this->connector = new Connector($loop);
        $this->connectionUrl = $connectionUrl;
        $this->connection = null;
    }

    /**
     * @param string $connectionUrl
     *
     * @return Connection|\React\Promise\Promise
     */
    public function connect()
    {
        $this->connection = $this
            ->connector
            ->connect($this->connectionUrl)
            ->then(function (ConnectionInterface $connection) {
                $connection->on('data', [$this->listenerConnection, 'onData']);

                $connection->on('end', [$this->listenerConnection, 'onEnd']);

                $connection->on('error', [$this->listenerConnection, 'onError']);

                $connection->on('close', [$this->listenerConnection, 'onClose']);

                return $connection;
            });

        return $this->connection;
    }

    public function write(string $data)
    {
        $this->connection->then(function (ConnectionInterface $connection) use ($data) {
            $connection->write($data);
        });
    }

    /**
     * @return mixed
     */
    public function close()
    {
        return $this->connection->then(function (ConnectionInterface $connection) {
            $connection->close();
        });
    }
}
