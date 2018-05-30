<?php

namespace Fourxxi\AsyncRedisCluster\Tests\Functional;

use Fourxxi\AsyncRedisCluster\Client\DummyClient;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class CommandsTest extends TestCase
{
    /**
     * @var DummyClient
     */
    private $redisClusterClient;

    /**
     * @var LoopInterface
     */
    private $loop;

    protected function setUp()
    {
        $this->loop = Factory::create();
        $this->redisClusterClient = new DummyClient(getenv('REDIS_CONNECTION_STRING'), $this->loop);
        $this->redisClusterClient->connect();
    }

    protected function tearDown()
    {
        $this->redisClusterClient->close();
    }

    public function testSetGet()
    {
        $this->redisClusterClient->set('foo', 'test')->then(function (array $models) {
            $this->assertSame(1, count($models));
            $this->assertSame($models[0]->getValueNative(), 'OK');
        })->done();

        $this->redisClusterClient->get('foo')->then(function (array $models) {
            $this->assertSame($models[0]->getValueNative(), 'test');
            $this->loop->stop();
        })->done();

        $this->loop->run();
    }

    public function testHmsetHgetall()
    {
        $hmsetArray = [
            'name' => 'name value',
            'comment' => 'name comment',
        ];

        $hashKey = 'foohash';

        $this->redisClusterClient->hmset($hashKey, $hmsetArray)->then(function (array $models) {
            $this->assertSame(1, count($models));
            $this->assertSame($models[0]->getValueNative(), 'OK');
        })->done();

        $this->redisClusterClient->hgetall($hashKey)->then(function (array $values) use ($hmsetArray) {
            ksort($values);
            ksort($hmsetArray);

            $this->assertSame($values, $hmsetArray);
            $this->loop->stop();
        })->done();

        $this->loop->run();
    }

    public function testExists()
    {
        $key = 'testKey';

        $this->redisClusterClient->del($key)->done();

        $this->redisClusterClient->exists($key)->then(function (bool $value) {
            $this->assertSame(false, $value);
        });

        $this->redisClusterClient->set($key, 'test')->done();

        $this->redisClusterClient->exists($key)->then(function (bool $value) {
            $this->assertSame(true, $value);

            $this->loop->stop();
        });

        $this->loop->run();
    }
}
