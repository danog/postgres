<?php

namespace Amp\Postgres\Test;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Postgres\Connection;
use Amp\Postgres\ConnectionConfig;
use Amp\Promise;
use Amp\Sql\FailureException;
use function Amp\Postgres\connect;

class FunctionsTest extends AsyncTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!\extension_loaded('pgsql') && !\extension_loaded('pq')) {
            $this->markTestSkipped('This test requires either ext/pgsql or pecl/pq');
        }
    }

    public function testConnect(): \Generator
    {
        $connection = yield connect(ConnectionConfig::fromString('host=localhost user=postgres'));
        $this->assertInstanceOf(Connection::class, $connection);
    }

    public function testConnectInvalidUser(): Promise
    {
        $this->expectException(FailureException::class);

        return connect(ConnectionConfig::fromString('host=localhost user=invalid'));
    }
}
