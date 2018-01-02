<?php

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
    protected $db;

    protected $params = [
        'engine'       => 'sqlite',
        'host'         => '127.0.0.1',
        'user'         => '',
        'password'     => '',
        'database'     => __DIR__ . '/test_database.db',
        'report_error' => 'silent'
    ];

    public function setUp()
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function testDropOneTable()
    {
        static::assertNull(null, $this->db->dropTable('test'));
    }

    public function testDropMultiTable()
    {
        static::assertNull(null, $this->db->dropTables(['test', 'toto']));
    }

    public function testExec()
    {
        $this->db->exec('CREATE TABLE test (
                                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                                    name VARCHAR (255) NOT NULL
                                );');
        static::assertFalse($this->db->hasErrors());
    }

    public function testInsert()
    {
        $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
        static::assertSame(1, $id);
    }

    public function testUpdate()
    {
        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 1, 'name' => 'google'];
        $rowsAffected = $this->db->update($sql, $params, true);
        static::assertSame(1, $rowsAffected);
    }

    public function testDelete()
    {
        $rowsAffected = $this->db->delete('DELETE FROM test WHERE name = :name1', ['name1' => 'google'], true);
        static::assertSame(1, $rowsAffected);
    }

    public function testRead()
    {
        $res = [];

        $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
        $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'B'], true);
        $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'C'], true);

        $cursor = $this->db->select('SELECT * FROM test');
        while ($row = $this->db->read($cursor)) {
            $res[] = $row;
        }

        static::assertSame(3, count($res));
    }

    public function testCount()
    {
        $count = $this->db->count('SELECT COUNT(*) FROM test');
        static::assertSame(3, $count);
    }

    public function testSelectAll()
    {
        $rows = $this->db->selectAll('SELECT * FROM test');
        static::assertSame(3, count($rows));
    }

    public function testSelectRow()
    {
        $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
        static::assertSame('B', $row['name']);
    }

    public function testSelectCol()
    {
        $col = $this->db->selectCol('SELECT id FROM test');
        static::assertSame(3, count($col));
    }

    public function testSelectVar()
    {
        $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
        static::assertSame('B', $var);
    }

    public function testGetError()
    {
        $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
        static::assertTrue($this->db->hasErrors());
    }

    public function testTruncateTable()
    {
        $this->db->truncateTable('test');
        static::assertFalse($this->db->hasErrors());
    }

    public function testTruncateTables()
    {
        $this->db->truncateTables(['test', 'test']);
        static::assertFalse($this->db->hasErrors());
    }

    public function testDisconnect()
    {
        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }

    public function testExecStatementFalse()
    {
        $sql = 'SELECT namebbb FROM test WHERE id = :id';
        $success = $this->db->exec($sql);
        static::assertFalse($success);
    }

    public function testInsertStatementFalse()
    {
        $sql = 'a :a';
        $success = $this->db->insert($sql);
        static::assertFalse($success);
    }

    public function testUpdateStatementFalse()
    {
        $sql = 'a :a';
        $success = $this->db->update($sql);
        static::assertFalse($success);
    }

    public function testDeleteStatementFalse()
    {
        $sql = 'a :a';
        $success = $this->db->delete($sql);
        static::assertFalse($success);
    }

    public function testCountStatementFalse()
    {
        $sql = 'a';
        $success = $this->db->count($sql);
        static::assertFalse($success);
    }

    public function testSelectAllStatementFalse()
    {
        $sql = 'a :a';
        $success = $this->db->selectAll($sql);
        static::assertFalse($success);
    }

    public function testSelectRowStatementFalse()
    {
        $sql = 'a';
        $success = $this->db->selectRow($sql);
        static::assertFalse($success);
    }

    public function testSelectColStatementFalse()
    {
        $sql = 'a :a';
        $success = $this->db->selectCol($sql);
        static::assertFalse($success);
    }
}
