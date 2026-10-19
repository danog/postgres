<?php declare(strict_types=1);

namespace Amp\Postgres\Test;

use Amp\Postgres\PgSqlConnection;
use Amp\Postgres\PostgresConfig;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;
use function Amp\delay;

/**
 * Not an AsyncTestCase: the test runs on {main}, whose suspension is resumed by interrupting the event loop,
 * so the query waiting for the DEALLOCATE to complete is sent before the fiber that sent the DEALLOCATE resumes.
 *
 * @requires extension pgsql
 */
class PgSqlStatementDeallocateTest extends TestCase
{
    public function testQueryWhileDeallocatingStatement(): void
    {
        if (EventLoop::getDriver()->getHandle() instanceof \EvLoop) {
            $this->markTestSkipped("ext-pgsql is not compatible with pecl-ev");
        }

        $timeout = EventLoop::delay(5, static fn () => throw new \Error('The result of a query was lost'));

        try {
            $connection = PgSqlConnection::connect(
                PostgresConfig::fromString('host=localhost user=postgres password=postgres'),
            );

            for ($i = 0; $i < 20; ++$i) {
                $statement = $connection->prepare("SELECT $i AS value");
                self::assertSame($i, $statement->execute()->fetchRow()['value']);
                unset($statement); // Deallocates the statement in another fiber.

                delay(0); // Lets the DEALLOCATE be sent.

                self::assertSame($i, $connection->query("SELECT $i AS value")->fetchRow()['value']);
            }

            $connection->close();
        } finally {
            EventLoop::cancel($timeout);
        }
    }
}
