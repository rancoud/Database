<?php

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;

/**
 * Class DatabaseSqliteSilentTest.
 */
class DatabaseSqliteSilentTest extends TestCase
{
    /** @var Database */
    protected Database $db;

    protected array $params = [
        'engine' => 'sqlite',
        'host' => '127.0.0.1',
        'user' => '',
        'password' => '',
        'database' => __DIR__ . '/test_database.db',
        'report_error' => 'silent'
    ];

    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function testDropOneTable(): void
    {
        static::assertNull(null, $this->db->dropTable('test'));
    }

    public function testDropMultiTable(): void
    {
        static::assertNull(null, $this->db->dropTables(['test', 'toto']));
    }

    public function testExec(): void
    {
        $this->db->exec('CREATE TABLE test (
                                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                                    name VARCHAR (255) NOT NULL
                                );');
        static::assertFalse($this->db->hasErrors());
    }

    public function testInsert(): void
    {
        $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
        static::assertSame(1, $id);
    }

    public function testUpdate(): void
    {
        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 1, 'name' => 'google'];
        $rowsAffected = $this->db->update($sql, $params, true);
        static::assertSame(1, $rowsAffected);
    }

    public function testDelete(): void
    {
        $rowsAffected = $this->db->delete('DELETE FROM test WHERE name = :name1', ['name1' => 'google'], true);
        static::assertSame(1, $rowsAffected);
    }

    public function testRead(): void
    {
        $res = [];

        $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
        $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'B'], true);
        $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'C'], true);

        $cursor = $this->db->select('SELECT * FROM test');
        while ($row = $this->db->read($cursor)) {
            $res[] = $row;
        }

        static::assertSame(3, count($res));
    }

    public function testCount(): void
    {
        $count = $this->db->count('SELECT COUNT(*) FROM test');
        static::assertSame(3, $count);
    }

    public function testSelectAll(): void
    {
        $rows = $this->db->selectAll('SELECT * FROM test');
        static::assertSame(3, count($rows));
    }

    public function testSelectRow(): void
    {
        $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
        static::assertSame('B', $row['name']);
    }

    public function testSelectCol(): void
    {
        $col = $this->db->selectCol('SELECT id FROM test');
        static::assertSame(3, count($col));
    }

    public function testSelectVar(): void
    {
        $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
        static::assertSame('B', $var);
    }

    public function testGetError(): void
    {
        $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
        static::assertTrue($this->db->hasErrors());
    }

    public function testTruncateTable(): void
    {
        $this->db->truncateTable('test');
        static::assertFalse($this->db->hasErrors());
    }

    public function testTruncateTables(): void
    {
        $this->db->truncateTables(['test', 'test']);
        static::assertFalse($this->db->hasErrors());
    }

    public function testDisconnect(): void
    {
        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }

    public function testExecStatementFalse(): void
    {
        $sql = 'SELECT namebbb FROM test WHERE id = :id';
        $success = $this->db->exec($sql);
        static::assertFalse($success);
    }

    public function testInsertStatementFalse(): void
    {
        $sql = 'a :a';
        $success = $this->db->insert($sql);
        static::assertFalse($success);
    }

    public function testUpdateStatementFalse(): void
    {
        $sql = 'a :a';
        $success = $this->db->update($sql);
        static::assertFalse($success);
    }

    public function testDeleteStatementFalse(): void
    {
        $sql = 'a :a';
        $success = $this->db->delete($sql);
        static::assertFalse($success);
    }

    public function testCountStatementFalse(): void
    {
        $sql = 'a';
        $success = $this->db->count($sql);
        static::assertFalse($success);
    }

    public function testSelectAllStatementFalse(): void
    {
        $sql = 'a :a';
        $success = $this->db->selectAll($sql);
        static::assertFalse($success);
    }

    public function testSelectRowStatementFalse(): void
    {
        $sql = 'a';
        $success = $this->db->selectRow($sql);
        static::assertFalse($success);
    }

    public function testSelectColStatementFalse(): void
    {
        $sql = 'a :a';
        $success = $this->db->selectCol($sql);
        static::assertFalse($success);
    }
}
