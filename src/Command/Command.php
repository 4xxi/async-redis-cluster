<?php

namespace Fourxxi\AsyncRedisCluster\Command;

use React\Promise\Deferred;

class Command
{
    public const STATUS_INQUEUE = 0;
    public const STATUS_EXECUTING = 1;
    public const STATUS_DONE = 2;

    /**
     * @var string
     */
    protected $commandString;

    /**
     * @var Deferred
     */
    protected $deferred;

    /**
     * @var int
     */
    protected $status;

    /**
     * Command constructor.
     *
     * @param string        $commandString
     * @param Deferred|null $deferred
     */
    public function __construct(string $commandString, Deferred $deferred = null)
    {
        $this->commandString = $commandString;
        $this->deferred = $deferred ?? new Deferred();
        $this->status = self::STATUS_INQUEUE;
    }

    /**
     * @return string
     */
    public function getCommandString(): string
    {
        return $this->commandString;
    }

    /**
     * @return Deferred
     */
    public function getDeferred(): Deferred
    {
        return $this->deferred;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return Command
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
