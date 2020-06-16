<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseSqliteSilentTest.
 */
class DatabaseSqliteSilentTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'engine' => 'sqlite',
        'host' => '127.0.0.1',
        'user' => '',
        'password' => '',
        'database' => __DIR__ . '/test_database.db',
        'report_error' => 'silent'
    ];

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    /**
     * @throws DatabaseException
     */
    public function testDropOneTable(): void
    {
        try {
            static::assertTrue($this->db->dropTable('test'));
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDropMultiTable(): void
    {
        try {
            static::assertTrue($this->db->dropTables(['test', 'toto']));
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testExec(): void
    {
        try {
            $this->db->exec('CREATE TABLE test (
                                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                                    name VARCHAR (255) NOT NULL
                                );');
            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testInsert(): void
    {
        try {
            $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
            static::assertSame(1, $id);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdate(): void
    {
        try {
            $sql = 'UPDATE test SET name = :name WHERE id = :id';
            $params = ['id' => 1, 'name' => 'google'];
            $rowsAffected = $this->db->update($sql, $params, true);
            static::assertSame(1, $rowsAffected);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDelete(): void
    {
        try {
            $rowsAffected = $this->db->delete('DELETE FROM test WHERE name = :name1', ['name1' => 'google'], true);
            static::assertSame(1, $rowsAffected);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     * @noinspection PhpAssignmentInConditionInspection
     */
    public function testRead(): void
    {
        try {
            $res = [];

            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'B'], true);
            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'C'], true);

            $cursor = $this->db->select('SELECT * FROM test');
            while ($row = $this->db->read($cursor)) {
                $res[] = $row;
            }

            static::assertCount(3, $res);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCount(): void
    {
        try {
            $count = $this->db->count('SELECT COUNT(*) FROM test');
            static::assertSame(3, $count);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectAll(): void
    {
        try {
            $rows = $this->db->selectAll('SELECT * FROM test');
            static::assertCount(3, $rows);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRow(): void
    {
        try {
            $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $row['name']);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectCol(): void
    {
        try {
            $col = $this->db->selectCol('SELECT id FROM test');
            static::assertCount(3, $col);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectVar(): void
    {
        try {
            $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $var);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetError(): void
    {
        try {
            $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
            static::assertTrue($this->db->hasErrors());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTruncateTable(): void
    {
        try {
            $this->db->truncateTable('test');
            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTruncateTables(): void
    {
        try {
            $this->db->truncateTables(['test', 'test']);

            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    public function testDisconnect(): void
    {
        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }

    /**
     * @throws DatabaseException
     */
    public function testExecStatementFalse(): void
    {
        try {
            $sql = 'SELECT namebbb FROM test WHERE id = :id';
            $success = $this->db->exec($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testInsertStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->insert($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdateStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->update($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDeleteStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->delete($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCountStatementFalse(): void
    {
        try {
            $sql = 'a';
            $success = $this->db->count($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectAllStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->selectAll($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRowStatementFalse(): void
    {
        try {
            $sql = 'a';
            $success = $this->db->selectRow($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectColStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->selectCol($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }
}
