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
     * @param null|Connector              $connector
     */
    public function __construct(
        LoopInterface $loop,
        ListenerConnectionInterface $listenerConnection,
        string $connectionUrl,
        ?Connector $connector = null
    ) {
        $this->loop = $loop;
        $this->listenerConnection = $listenerConnection;
        $this->connector = $connector ?? new Connector($loop);
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
        if (null === $this->connection) {
            $this->connect();
        }

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
