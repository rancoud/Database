<?php

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection SqlDialectInspection
 */

declare(strict_types=1);

namespace tests\separate_process;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DNIHasInstanceTest.
 */
class DNIHasInstanceTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'driver'       => 'mysql',
        'host'         => 'localhost',
        'user'         => 'root',
        'password'     => '',
        'database'     => 'test_database'
    ];

    /**
     * @throws DatabaseException
     */
    public function testHasInstance(): void
    {
        static::assertFalse(Database::hasInstance());

        Database::setInstance(new Configurator($this->params));

        static::assertTrue(Database::hasInstance());

        static::assertFalse(Database::hasInstance('secondary'));

        Database::setInstance(new Configurator($this->params), 'secondary');

        static::assertTrue(Database::hasInstance('secondary'));
    }
}
