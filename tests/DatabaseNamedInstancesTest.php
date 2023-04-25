<?php

/**
 * @noinspection SqlDialectInspection
 */

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;
use ReflectionClass;

/**
 * Class DatabaseNamedInstancesTest.
 */
class DatabaseNamedInstancesTest extends TestCase
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
        $reflectedProperty = $class->getProperty('instances');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue([]);

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

        $reflectedProperty->setValue([]);
    }

    /**
     * @throws DatabaseException
     */
    public function testSetInstanceThrowException(): void
    {
        $class = new ReflectionClass(Database::class);
        $reflectedProperty = $class->getProperty('instances');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue([]);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Cannot overwrite instance "primary"');

        Database::setInstance(new Configurator($this->params));
        Database::setInstance(new Configurator($this->params));
    }

    /**
     * @throws DatabaseException
     */
    public function testHasInstance(): void
    {
        $class = new ReflectionClass(Database::class);
        $reflectedProperty = $class->getProperty('instances');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue([]);

        static::assertFalse(Database::hasInstance());

        Database::setInstance(new Configurator($this->params));

        static::assertTrue(Database::hasInstance());

        static::assertFalse(Database::hasInstance('secondary'));

        Database::setInstance(new Configurator($this->params), 'secondary');

        static::assertTrue(Database::hasInstance('secondary'));

        $reflectedProperty->setValue([]);
    }

    /**
     * @throws DatabaseException
     */
    public function testGetInstance(): void
    {
        $class = new ReflectionClass(Database::class);
        $reflectedProperty = $class->getProperty('instances');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue([]);

        static::assertNull(Database::getInstance());
        static::assertNull(Database::getInstance('secondary'));

        $db1 = Database::setInstance(new Configurator($this->params));
        $db2 = Database::setInstance(new Configurator($this->params), 'secondary');

        static::assertInstanceOf(Database::class, Database::getInstance());
        static::assertInstanceOf(Database::class, Database::getInstance('secondary'));

        static::assertSame($db1, Database::getInstance());
        static::assertSame($db2, Database::getInstance('secondary'));

        $reflectedProperty->setValue([]);
    }
}
