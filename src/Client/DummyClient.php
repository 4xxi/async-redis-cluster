<?php

namespace Fourxxi\AsyncRedisCluster\Client;

use Clue\Redis\Protocol\Factory;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Serializer\SerializerInterface;
use Fourxxi\AsyncRedisCluster\Command\Command;
use Fourxxi\AsyncRedisCluster\Connection\Connection;
use Fourxxi\AsyncRedisCluster\Connection\Listener\ListenerConnectionInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;

class DummyClient implements ListenerConnectionInterface
{
    /**
     * @var \SplDoublyLinkedList
     */
    protected $commandQueue;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * DummyClient constructor.
     *
     * @param LoopInterface       $loop
     * @param ParserInterface     $parser
     * @param SerializerInterface $serializer
     * @param string              $connectionString
     */
    public function __construct(
        string $connectionString,
        LoopInterface $loop,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    ) {
        $this->commandQueue = new \SplDoublyLinkedList();
        $this->loop = $loop;

        $protocolFactory = new Factory();

        $this->serializer = $serializer ?? $protocolFactory->createSerializer();
        $this->parser = $parser ?? $protocolFactory->createResponseParser();

        $this->connection = new Connection($this->loop, $this, $connectionString);
    }

    public function connect()
    {
        return $this->connection->connect();
    }

    public function reconnectToNode(string $connectionString)
    {
        $this->connection->close()
            ->then(function () use ($connectionString) {
                unset($this->connection);

                $this->connection = new Connection($this->loop, $this, $connectionString);

                return $this->connect();
            })
            ->then(function (ConnectionInterface $connection) {
                $this->executeCommandFromEndOfQueue();
            });
    }

    public function __call($name, $arguments)
    {
        $deferred = new Deferred();

        $command = new Command($this->serializer->getRequestMessage($name, $arguments), $deferred);
        $this->commandQueue->unshift($command);

        if (1 === $this->commandQueue->count()) {
            $this->executeCommand($command);
        }

        return $deferred->promise();
    }

    public function onData($chunk)
    {
        $models = $this->parser->pushIncoming($chunk);

        if (0 === count($models)) {
            return;
        }

        if ($models[0] instanceof ErrorReply && 0 === mb_strpos($models[0]->getValueNative(), 'MOVED')) {
            $movedIp = explode(' ', $models[0]->getValueNative())[2];
            $this->reconnectToNode($movedIp);

            return;
        }

        /**
         * @var Command
         */
        $command = $this->commandQueue->pop();

        $command->setStatus(Command::STATUS_DONE);
        $command->getDeferred()->resolve($models);

        unset($command);

        $this->executeCommandFromEndOfQueue();
    }

    protected function executeCommandFromEndOfQueue()
    {
        if (0 === $this->commandQueue->count()) {
            return;
        }

        $this->executeCommand($this->commandQueue->top());
    }

    protected function executeCommand(Command $command)
    {
        $command->setStatus(Command::STATUS_EXECUTING);
        $this->connection->write($command->getCommandString());
    }

    public function onError(\Exception $e)
    {
    }

    public function onClose()
    {
    }

    public function onEnd()
    {
    }
}
