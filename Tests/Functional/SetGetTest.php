<?php

namespace Fourxxi\AsyncRedisCluster\Tests\Functional;

use Fourxxi\AsyncRedisCluster\Client\DummyClient;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;

class SetGetTest extends TestCase
{
    public function testSetGet()
    {
        $loop = Factory::create();
        $redisClusterClient = new DummyClient(getenv('REDIS_CONNECTION_STRING'), $loop);

        $redisClusterClient->connect();

        $numOfTests = 10;

        for ($i = 1; $i <= $numOfTests; ++$i) {
            $val = rand(0, 5000);

            $redisClusterClient->set("foo${i}", $val)->then(function (array $models) {
                $this->assertSame(1, count($models));
                $this->assertSame($models[0]->getValueNative(), 'OK');
            })->done();

            $redisClusterClient->get("foo${i}")->then(function (array $models) use ($i, $numOfTests, $loop, $val) {
                $this->assertSame($models[0]->getValueNative(), (string) $val);
                if ($numOfTests === $i) {
                    $loop->stop();
                }
            })->done();
        }

        $loop->run();
    }
}
