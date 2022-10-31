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
use ReflectionClass;

/**
 * Class DNISetInstanceTest.
 */
class DNISetInstanceTest extends TestCase
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
    public function testSetInstance(): void
    {
        $class = new ReflectionClass(Database::class);

        $db1 = Database::setInstance(new Configurator($this->params));

        $propertiesOne = $class->getStaticProperties();
        static::assertNotEmpty($propertiesOne['instances']);
        static::assertArrayHasKey('primary', $propertiesOne['instances']);
        static::assertInstanceOf(Database::class, $propertiesOne['instances']['primary']);
        static::assertSame($db1, $propertiesOne['instances']['primary']);

        $db2 = Database::setInstance(new Configurator($this->params), 'secondary');
        $propertiesTwo = $class->getStaticProperties();
        static::assertNotEmpty($propertiesTwo['instances']);
        static::assertArrayHasKey('secondary', $propertiesTwo['instances']);
        static::assertInstanceOf(Database::class, $propertiesTwo['instances']['secondary']);
        static::assertSame($db2, $propertiesTwo['instances']['secondary']);
    }
}
