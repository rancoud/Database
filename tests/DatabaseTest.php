<?php
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PDOStatement;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseTest.
 */
class DatabaseTest extends TestCase
{
    protected array $sgbds = [
        'mysql_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'mysql',
                'host'         => '127.0.0.1',
                'user'         => 'root',
                'password'     => '',
                'database'     => 'test_database',
                'report_error' => 'exception'
            ],
        ],
        'mysql_silent' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'mysql',
                'host'         => '127.0.0.1',
                'user'         => 'root',
                'password'     => '',
                'database'     => 'test_database',
                'report_error' => 'silent'
            ],
        ],
        'sqlite_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'sqlite',
                'host'         => '127.0.0.1',
                'user'         => '',
                'password'     => '',
                'database'     => __DIR__ . '/test_database.db',
                'report_error' => 'exception'
            ],
        ],
        'sqlite_silent' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'        => 'sqlite',
                'host'          => '127.0.0.1',
                'user'          => '',
                'password'      => '',
                'database'      => __DIR__ . '/test_database.db',
                'report_error'  => 'silent'
            ],
        ],
        'postgresql_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'        => 'pgsql',
                'host'          => '127.0.0.1',
                'user'          => 'postgres',
                'password'      => '',
                'database'      => 'test_database',
                'report_error'  => 'exception'
            ],
        ],
    ];

    protected array $sqlQueries = [
        'mysql' => [
            'exec' => 'CREATE TABLE test_exec (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );',
            'insert' => 'CREATE TABLE test_insert (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [],
        ],
        'pgsql' => [
            'exec' => 'CREATE TABLE test_exec (
                id SERIAL PRIMARY KEY,
                name character varying(255) NOT NULL
            );',
            'insert' => 'CREATE TABLE test_insert (
                id SERIAL PRIMARY KEY,
                name character varying(255) NOT NULL
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id SERIAL PRIMARY KEY,
                    name character varying(255) NOT NULL
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [],
        ],
        'sqlite' => [
            'exec' => 'CREATE TABLE test_exec (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );',
            'insert' => 'CREATE TABLE test_insert (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR (255) NOT NULL
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [],
        ],
    ];

    // region Data Provider

    public function sgbds(): array
    {
        return [
            'mysql_exception' => ['mysql_exception'],
            'mysql_silent' => ['mysql_silent'],
            'sqlite_exception' => ['sqlite_exception'],
            'sqlite_silent' => ['sqlite_silent'],
            'postgresql_exception' => ['postgresql_exception'],
        ];
    }

    public function sgbdsException(): array
    {
        return [
            'mysql_exception' => ['mysql_exception'],
            'sqlite_exception' => ['sqlite_exception'],
            'postgresql_exception' => ['postgresql_exception']
        ];
    }

    public function sgbdsSilent(): array
    {
        return [
            'mysql_silent' => ['mysql_silent'],
            'sqlite_silent' => ['sqlite_silent']
        ];
    }

    // endregion

    // region Setup / Teardown

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        foreach ($this->sgbds as $k => $sgbd) {
            $configurator = new Configurator($this->sgbds[$k]['parameters']);
            $this->sgbds[$k]['db'] = new Database(new Configurator($this->sgbds[$k]['parameters']));
            $pdo = $configurator->createPDOConnection();

            $pdo->exec('DROP TABLE IF EXISTS test_exec');
            $pdo->exec('DROP TABLE IF EXISTS test_insert');
            $pdo->exec('DROP TABLE IF EXISTS test_update');
            $pdo->exec('DROP TABLE IF EXISTS test_delete');
        }
    }

    public function tearDown(): void
    {
        foreach ($this->sgbds as $k => $sgbd) {
            $this->sgbds[$k]['db']->disconnect();
            $this->sgbds[$k]['db'] = null;
        }
    }

    // endregion

    // region Database->Exec

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExec(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sql = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['exec'];

        try {
            $success = $db->exec($sql);
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExecException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->exec('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExecError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->exec('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Insert

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsert(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sql = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['insert'];
        $db->exec($sql);

        try {
            $sql = "INSERT INTO test_insert (name) VALUES ('A')";
            $id = $db->insert($sql);
            static::assertTrue($id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='A' AND id=1");
            static::assertSame(1, $count);

            $sql = 'INSERT INTO test_insert (name) VALUES (:name)';
            $params = ['name' => 'B'];
            $id = $db->insert($sql, $params);
            static::assertTrue($id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='B' AND id=2");
            static::assertSame(1, $count);

            $params = ['name' => 'C'];
            $getLastInsertId = true;
            $id = $db->insert($sql, $params, $getLastInsertId);
            static::assertSame(3, $id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='C' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsertException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->insert('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsertError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->insert('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Update

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdate(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['update'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = "UPDATE test_update SET name = 'AA' WHERE id = 1";
            $rowsAffected = $db->update($sql);
            static::assertTrue($rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='AA' AND id=1");
            static::assertSame(1, $count);

            $sql = "UPDATE test_update SET name = :name WHERE id = :id";
            $params = ['id' => 2, 'name' => 'BB'];
            $rowsAffected = $db->update($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='BB' AND id=2");
            static::assertSame(1, $count);

            $params = ['id' => 3, 'name' => 'CC'];
            $getCountRowsAffected = true;
            $rowsAffected = $db->update($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='CC' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdateException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->update('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdateError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->update('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion
}
