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
 * Class DatabaseMysqlExceptionTest.
 */
class DatabaseMysqlExceptionTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'engine'       => 'mysql',
        'host'         => '127.0.0.1',
        'user'         => 'root',
        'password'     => '',
        'database'     => 'test_database',
        'report_error' => 'exception'
    ];

    protected array $data = [
        [
            'id'      => '1',
            'name'    => 'A',
            'rank'    => '0',
            'comment' => null,
        ],
        [
            'id'      => '2',
            'name'    => 'B',
            'rank'    => '10',
            'comment' => 'yes',
        ],
        [
            'id'      => '3',
            'name'    => 'C',
            'rank'    => '20',
            'comment' => 'maybe',
        ],
        [
            'id'      => '4',
            'name'    => 'D',
            'rank'    => '30',
            'comment' => 'no',
        ],
        [
            'id'      => '5',
            'name'    => 'E',
            'rank'    => '25',
            'comment' => null,
        ],
        [
            'id'      => '6',
            'name'    => 'F',
            'rank'    => '5',
            'comment' => null,
        ]
    ];

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function tearDown(): void
    {
        $this->db->disconnect();
        $this->db = null;
    }

    /**
     * @throws DatabaseException
     */
    public function testExec(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS test_exec (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );';

        try {
            $success = $this->db->exec($sql);
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testExecException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $this->db->exec('aaa');
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForInsert(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test_insert');
        $this->db->exec('CREATE TABLE test_insert (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );');
    }

    /**
     * @throws DatabaseException
     */
    public function testInsert(): void
    {
        try {
            $this->setTestTableForInsert();

            $sql = 'INSERT INTO test_insert (name) VALUES ("A")';
            $id = $this->db->insert($sql);
            static::assertTrue($id);

            $count = $this->db->count('SELECT COUNT(*) FROM test_insert WHERE name="A" AND id=1');
            static::assertSame(1, $count);

            $sql = 'INSERT INTO test_insert (name) VALUES (:name)';
            $params = ['name' => 'B'];
            $id = $this->db->insert($sql, $params);
            static::assertTrue($id);

            $count = $this->db->count('SELECT COUNT(*) FROM test_insert WHERE name="B" AND id=2');
            static::assertSame(1, $count);

            $params = ['name' => 'C'];
            $getLastInsertId = true;
            $id = $this->db->insert($sql, $params, $getLastInsertId);
            static::assertSame(3, $id);

            $count = $this->db->count('SELECT COUNT(*) FROM test_insert WHERE name="C" AND id=3');
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testInsertException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'INSERT INTO test (name) VALUES (:name)';

        $this->db->insert($sql);
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForUpdate(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test_update');
        $this->db->exec('CREATE TABLE test_update (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );');
        $this->db->exec('INSERT INTO test_update (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test_update (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test_update (name) VALUES ("C");');
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdate(): void
    {
        try {
            $this->setTestTableForUpdate();

            $sql = 'UPDATE test_update SET name = "AA" WHERE id = 1';
            $rowsAffected = $this->db->update($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_update WHERE name="AA" AND id=1');
            static::assertSame(1, $count);

            $sql = 'UPDATE test_update SET name = :name WHERE id = :id';
            $params = ['id' => 2, 'name' => 'BB'];
            $rowsAffected = $this->db->update($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_update WHERE name="BB" AND id=2');
            static::assertSame(1, $count);

            $params = ['id' => 3, 'name' => 'CC'];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_update WHERE name="CC" AND id=3');
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testUpdateException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'UPDATE test SET name = :name WHERE id = :id';

        $this->db->update($sql);
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForDelete(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test_delete');
        $this->db->exec('CREATE TABLE test_delete (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );');
        $this->db->exec('INSERT INTO test_delete (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test_delete (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test_delete (name) VALUES ("C");');
    }

    /**
     * @throws DatabaseException
     */
    public function testDelete(): void
    {
        try {
            $this->setTestTableForDelete();

            $sql = 'DELETE FROM test_delete WHERE id = 1';
            $rowsAffected = $this->db->delete($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_delete WHERE id=1');
            static::assertSame(0, $count);

            $sql = 'DELETE FROM test_delete WHERE id = :id';
            $params = ['id' => 2];
            $rowsAffected = $this->db->delete($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_delete WHERE id=2');
            static::assertSame(0, $count);

            $params = ['id' => 3];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test_delete WHERE id=3');
            static::assertSame(0, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testDeleteException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'DELETE FROM `test` WHERE id = :id';

        $this->db->delete($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testUseSqlFile(): void
    {
        try {
            $success = $this->db->useSqlFile(__DIR__ . '/test-dump-mysql.sql');
            static::assertTrue($success);

            static::assertSame(6, $this->db->count('SELECT COUNT(*) FROM test_select'));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testUseSqlFileException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('File missing for useSqlFile method: ./missing-dump.sql');

        $this->db->useSqlFile('./missing-dump.sql');
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForSelects(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test_select');
        $this->db->exec('CREATE TABLE `test_select` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(45) NOT NULL,
              `rank` tinyint(1) unsigned NOT NULL,
              `comment` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (1, "A", 0, NULL);');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (2, "B", 10, "yes");');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (3, "C", 20, "maybe");');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (4, "D", 30, "no");');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (5, "E", 25, NULL);');
        $this->db->exec('INSERT INTO test_select (id, name, `rank`, comment) VALUES (6, "F", 5, NULL);');
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectAll(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $rows = $this->db->selectAll($sql);
            static::assertSame($this->data, $rows);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $rows = $this->db->selectAll($sql, $params);
            $data[] = $this->data[2];
            $data[] = $this->data[3];
            $data[] = $this->data[4];
            static::assertSame($data, $rows);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $rows = $this->db->selectAll($sql, $params);
            static::assertSame([], $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectAllException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';

        $this->db->selectAll($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRow(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $row = $this->db->selectRow($sql);
            static::assertSame($this->data[0], $row);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $row = $this->db->selectRow($sql, $params);
            static::assertSame($this->data[2], $row);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $row = $this->db->selectRow($sql, $params);
            static::assertSame([], $row);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectRowException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';

        $this->db->selectRow($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectCol(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $col = $this->db->selectCol($sql);
            static::assertSame(['1', '2', '3', '4', '5', '6'], $col);

            $sql = 'SELECT `name` FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $col = $this->db->selectCol($sql, $params);
            static::assertSame(['C', 'D', 'E'], $col);

            $sql = 'SELECT `rank` FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $col = $this->db->selectCol($sql, $params);
            static::assertSame([], $col);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectColException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';

        $this->db->selectCol($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectVar(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT `id` FROM `test_select`';
            $var = $this->db->selectVar($sql);
            static::assertSame('1', $var);

            $sql = 'SELECT `name` FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $var = $this->db->selectVar($sql, $params);
            static::assertSame('C', $var);

            $sql = 'SELECT `rank` FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $var = $this->db->selectVar($sql, $params);
            static::assertFalse($var);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectVarException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';

        $this->db->selectVar($sql);
    }

    /**
     * @throws DatabaseException
     * @noinspection FopenBinaryUnsafeUsageInspection
     */
    public function testPdoParamType(): void
    {
        try {
            $sql = 'SELECT :true AS `true`, :false AS `false`, :null AS `null`, :float AS `float`,
                :int AS `int`, :string AS `string`, :resource AS `resource`';
            $params = [
                'true' => true,
                'false' => false,
                'null' => null,
                'float' => 1.2,
                'int' => 800,
                'string' => 'string',
                'resource' => fopen(__DIR__ . '/test-dump-mysql.sql', 'r')
            ];
            $row = $this->db->selectRow($sql, $params);

            static::assertSame('1', $row['true']);
            static::assertSame('0', $row['false']);
            static::assertNull($row['null']);
            static::assertSame('1.2', $row['float']);
            static::assertSame('800', $row['int']);
            static::assertSame('string', $row['string']);
            static::assertSame('-- MySQL dump', mb_substr($row['resource'], 0, 13));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }
/*
    public function testPdoParamTypeException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Bind Value');

        $sql = 'SELECT :array AS array';
        $params = ['array' => []];
        $this->db->selectRow($sql, $params);
    }

    public function testPrepareBindException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT :a';
        $params = [':a' => 'a'];
        $this->db->selectRow($sql, $params);
    }
*/
    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testSelect(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $statement = $this->db->select($sql);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';

        $this->db->select($sql);
    }

    /**
     * @throws DatabaseException
     * @noinspection PhpAssignmentInConditionInspection
     */
    public function testRead(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $statement = $this->db->select($sql);
            $row = $this->db->read($statement);
            static::assertCount(4, $row);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            $row = $this->db->read($statement);
            static::assertCount(4, $row);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            $row = $this->db->read($statement);
            static::assertFalse($row);

            $rows = [];
            $statement = $this->db->select('SELECT * FROM `test_select`');
            while ($row = $this->db->read($statement)) {
                $rows[] = $row;
            }

            static::assertCount(6, $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testReadAll(): void
    {
        try {
            $this->setTestTableForSelects();

            $sql = 'SELECT * FROM `test_select`';
            $statement = $this->db->select($sql);
            $rows = $this->db->readAll($statement);
            static::assertCount(6, $rows);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            $rows = $this->db->readAll($statement);
            static::assertCount(3, $rows);

            $sql = 'SELECT * FROM `test_select` WHERE `rank` >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            $rows = $this->db->readAll($statement);
            static::assertCount(0, $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCompleteTransaction(): void
    {
        try {
            $this->setTestTableForSelects();

            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $this->db->completeTransaction();

            $sql = 'SELECT `name` FROM `test_select` WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testRollbackTransaction(): void
    {
        try {
            $this->setTestTableForSelects();

            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $sql = 'SELECT `name` FROM `test_select` WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));

             $this->db->rollbackTransaction();

            $sql = 'SELECT `name` FROM `test_select` WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('A', $this->db->selectVar($sql, $params));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    /*public function testCompleteTransactionException(): void
    {
        try {
            $this->setTestTableForSelects();

            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = [];
            $this->db->update($sql, $params);

            $success = $this->db->completeTransaction();
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }*/

    /**
     * @throws DatabaseException
     */
    public function testInTransactionError(): void
    {
        try {
            $success = $this->db->completeTransaction();
            static::assertFalse($success);

            $success = $this->db->commitTransaction();
            static::assertFalse($success);

            $success = $this->db->rollbackTransaction();
            static::assertFalse($success);

            $db = new Database(new Configurator($this->params));
            $success = $db->completeTransaction();
            static::assertFalse($success);

            $db = new Database(new Configurator($this->params));
            $success = $db->commitTransaction();
            static::assertFalse($success);

            $db = new Database(new Configurator($this->params));
            $success = $db->rollbackTransaction();
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    // errors

    public function testErrors(): void
    {
        static::assertFalse($this->db->hasErrors());
        static::assertSame([], $this->db->getErrors());
        static::assertNull($this->db->getLastError());

        try {
            $this->db->selectVar('SELECT `name` FROM `test` WHERE id = :id');
        } catch (DatabaseException $e) {
            static::assertTrue($this->db->hasErrors());
            static::assertCount(4, $this->db->getLastError());

            $this->db->cleanErrors();

            static::assertFalse($this->db->hasErrors());
            static::assertSame([], $this->db->getErrors());
            static::assertNull($this->db->getLastError());
        }
    }

    // save queries

    /**
     * @throws DatabaseException
     */
    public function testSaveQueries(): void
    {
        try {
            $this->setTestTableForSelects();

            static::assertFalse($this->db->hasSaveQueries());

            $this->db->enableSaveQueries();

            static::assertTrue($this->db->hasSaveQueries());

            $this->db->disableSaveQueries();

            static::assertFalse($this->db->hasSaveQueries());

            $this->db->enableSaveQueries();

            static::assertTrue($this->db->hasSaveQueries());

            static::assertSame([], $this->db->getSavedQueries());

            $this->db->selectAll('SELECT * FROM `test_select`');

            $queries = $this->db->getSavedQueries();

            static::assertCount(1, $queries);

            $this->db->cleanSavedQueries();

            $queries = $this->db->getSavedQueries();

            static::assertCount(0, $queries);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    // specific command

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForTruncateAndDrop(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test_truncate1');
        $this->db->exec('CREATE TABLE test_truncate1 (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );');
        $this->db->exec('INSERT INTO test_truncate1 (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test_truncate1 (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test_truncate1 (name) VALUES ("C");');

        $this->db->exec('DROP TABLE IF EXISTS test_truncate2');
        $this->db->exec('CREATE TABLE test_truncate2 (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );');
        $this->db->exec('INSERT INTO test_truncate2 (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test_truncate2 (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test_truncate2 (name) VALUES ("C");');
    }

    /**
     * @throws DatabaseException
     */
    public function testTruncateTables(): void
    {
        try {
            $this->setTestTableForTruncateAndDrop();

            static::assertSame(3, $this->db->count('SELECT COUNT(*) FROM test_truncate1'));
            static::assertSame(3, $this->db->count('SELECT COUNT(*) FROM test_truncate2'));
            static::assertTrue($this->db->truncateTables('test_truncate1', 'test_truncate2'));
            static::assertSame(0, $this->db->count('SELECT COUNT(*) FROM test_truncate1'));
            static::assertSame(0, $this->db->count('SELECT COUNT(*) FROM test_truncate2'));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDropTables(): void
    {
        try {
            $this->setTestTableForTruncateAndDrop();

            $sql1 = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'test_database' AND table_name = 'test_truncate1';";
            $sql2 = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'test_database' AND table_name = 'test_truncate2';";

            static::assertSame(1, $this->db->count($sql1));
            static::assertSame(1, $this->db->count($sql2));
            static::assertTrue($this->db->dropTables('test_truncate1', 'test_truncate2'));
            static::assertSame(0, $this->db->count($sql1));
            static::assertSame(0, $this->db->count($sql2));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    // low level

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testConnect(): void
    {
        try {
            static::assertNull($this->db->getPdo());

            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testConnectException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Connecting Database');

        $params = $this->params;
        $params['password'] = 'password';
        $databaseConf = new Configurator($params);
        $db = new Database($databaseConf);
        $db->connect();
    }

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testGetPdo(): void
    {
        try {
            static::assertNull($this->db->getPdo());

            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testDisconnect(): void
    {
        try {
            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));

            $this->db->disconnect();

            static::assertNull($this->db->getPdo());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }
}
