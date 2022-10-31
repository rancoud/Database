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
 * Class DNISetInstanceThrowExceptionTest.
 */
class DNISetInstanceThrowExceptionTest extends TestCase
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
    public function testSetInstanceThrowException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Cannot overwrite instance "primary"');

        Database::setInstance(new Configurator($this->params));
        Database::setInstance(new Configurator($this->params));
    }
}
