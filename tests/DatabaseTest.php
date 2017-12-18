<?php

namespace Rancoud\Database\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;

/**
 * Class DatabaseTest.
 */
class DatabaseTest extends TestCase
{
    /** @var Database */
    protected $db;

    public function setUp()
    {
        $params = ['engine' => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'silent'];
        $databaseConf = new Configurator($params);
        $this->db = new Database($databaseConf);
    }

    public function testDropOneTable()
    {
        static::assertNull(null, $this->db->dropTable('test'));
    }

    public function testDropMultiTable()
    {
        $this->assertNull(null, $this->db->dropTables(['test', 'toto']));
    }

    public function testExec()
    {
        $this->db->exec('CREATE TABLE `test` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(255) NOT NULL,
          PRIMARY KEY (`id`) );');
        $this->assertFalse($this->db->hasErrors());
    }

    public function testInsert()
    {
        $id = $this->db->insert('INSERT INTO test (`name`) VALUES (:name)', ['name' => 'A'], true);
        $this->assertSame(1, $id);
    }

    public function testUpdate()
    {
        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 1, 'name' => 'google'];
        $rowsAffected = $this->db->update($sql, $params, true);
        $this->assertSame(1, $rowsAffected);
    }

    public function testDelete()
    {
        $rowsAffected = $this->db->delete('DELETE FROM test WHERE name = :name1', ['name1' => 'google'], true);
        $this->assertSame(1, $rowsAffected);
    }

    public function testRead()
    {
        $res = [];

        $id = $this->db->insert('INSERT INTO test (`name`) VALUES (:name)', ['name' => 'A'], true);
        $id = $this->db->insert('INSERT INTO test (`name`) VALUES (:name)', ['name' => 'B'], true);
        $id = $this->db->insert('INSERT INTO test (`name`) VALUES (:name)', ['name' => 'C'], true);

        $cursor = $this->db->select('SELECT * FROM test');
        while ($row = $this->db->read($cursor)) {
            $res[] = $row;
        }

        $this->assertSame(3, count($res));
    }

    public function testCount()
    {
        $count = $this->db->count('SELECT COUNT(*) FROM test');
        $this->assertSame(3, $count);
    }

    public function testSelectAll()
    {
        $rows = $this->db->selectAll('SELECT * FROM test');
        $this->assertSame(3, count($rows));
    }

    public function testSelectRow()
    {
        $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
        $this->assertSame('B', $row['name']);
    }

    public function testSelectCol()
    {
        $col = $this->db->selectCol('SELECT id FROM test');
        $this->assertSame(3, count($col));
    }

    public function testSelectVar()
    {
        $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
        $this->assertSame('B', $var);
    }

    public function testGetError()
    {
        $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
        $this->assertSame(true, $this->db->hasErrors());
    }
}
