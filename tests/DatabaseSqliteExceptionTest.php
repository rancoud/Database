<?php /** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PDOStatement;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseSqliteExceptionTest.
 */
class DatabaseSqliteExceptionTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'engine'       => 'sqlite',
        'host'         => '127.0.0.1',
        'user'         => '',
        'password'     => '',
        'database'     => __DIR__ . '/test_database.db',
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
        $this->db = null;
    }

    /**
     * @throws DatabaseException
     */
    public function testFirstLaunch(): void
    {
        try {
            $success = $this->db->dropTables(['test', 'test_select']);
            static::assertTrue($success);
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
            $success = $this->db->exec('CREATE TABLE test (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );');

            static::assertTrue($success);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    public function testExecException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Prepare Statement');

        $this->db->exec('aaa');
    }

    public function testInsert(): void
    {
        $sql = 'INSERT INTO test (name) VALUES ("A")';
        $id = $this->db->insert($sql);
        static::assertTrue($id);

        $sql = 'INSERT INTO test (name) VALUES (:name)';
        $params = ['name' => 'B'];
        $id = $this->db->insert($sql, $params);
        static::assertTrue($id);

        $params = ['name' => 'C'];
        $getLastInsertId = true;
        $id = $this->db->insert($sql, $params, $getLastInsertId);
        static::assertSame(3, $id);
    }

    public function testInsertException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'INSERT INTO test (name) VALUES (:name)';

        $this->db->insert($sql);
    }

    public function testUpdate(): void
    {
        $sql = 'UPDATE test SET name = "AA" WHERE id = 1';
        $rowsAffected = $this->db->update($sql);
        static::assertTrue($rowsAffected);

        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 2, 'name' => 'BB'];
        $rowsAffected = $this->db->update($sql, $params);
        static::assertTrue($rowsAffected);

        $params = ['id' => 3, 'name' => 'CC'];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);
    }

    public function testDelete(): void
    {
        $sql = 'DELETE FROM test WHERE id = 1';
        $rowsAffected = $this->db->delete($sql);
        static::assertTrue($rowsAffected);

        $sql = 'DELETE FROM test WHERE id = :id';
        $params = ['id' => 2];
        $rowsAffected = $this->db->delete($sql, $params);
        static::assertTrue($rowsAffected);

        $params = ['id' => 3];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);
    }

    public function testUseSqlFile(): void
    {
        $this->db->exec('CREATE TABLE test_select (
            id      INT          PRIMARY KEY
            NOT NULL,
            name    VARCHAR (45) NOT NULL,
            rank    INT (1)      NOT NULL,
            comment TEXT
            );');
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (1, 'A', 0, NULL);");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (2, 'B', 10, 'yes');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (3, 'C', 20, 'maybe');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (4, 'D', 30, 'no');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (5, 'E', 25, NULL);");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (6, 'F', 5, NULL);");
        static::assertSame(6, $this->db->count('SELECT COUNT(*) FROM test_select'));
    }

    public function testUseSqlFileException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('File missing for useSqlFile method: ./missing-dump.sql');

        $this->db->useSqlFile('./missing-dump.sql');
    }

    public function testSelectAll(): void
    {
        $sql = 'SELECT * FROM test_select';
        $rows = $this->db->selectAll($sql);
        static::assertSame($this->data, $rows);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $rows = $this->db->selectAll($sql, $params);
        $data[] = $this->data[2];
        $data[] = $this->data[3];
        $data[] = $this->data[4];
        static::assertSame($data, $rows);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $rows = $this->db->selectAll($sql, $params);
        static::assertSame([], $rows);
    }

    public function testSelectAllError(): void
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $data = $this->db->selectAll($sql);
        static::assertSame([], $data);
    }

    public function testSelectRow(): void
    {
        $sql = 'SELECT * FROM test_select';
        $row = $this->db->selectRow($sql);
        static::assertSame($this->data[0], $row);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $row = $this->db->selectRow($sql, $params);
        static::assertSame($this->data[2], $row);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $row = $this->db->selectRow($sql, $params);
        static::assertSame([], $row);
    }

    public function testSelectRowError(): void
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $row = $this->db->selectRow($sql);
        static::assertSame([], $row);
    }

    public function testSelectCol(): void
    {
        $sql = 'SELECT * FROM test_select';
        $col = $this->db->selectCol($sql);
        static::assertSame(['1', '2', '3', '4', '5', '6'], $col);

        $sql = 'SELECT name FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $col = $this->db->selectCol($sql, $params);
        static::assertSame(['C', 'D', 'E'], $col);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $col = $this->db->selectCol($sql, $params);
        static::assertSame([], $col);
    }

    public function testSelectColError(): void
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $row = $this->db->selectCol($sql);
        static::assertSame([], $row);
    }

    public function testSelectVar(): void
    {
        $sql = 'SELECT id FROM test_select';
        $var = $this->db->selectVar($sql);
        static::assertSame('1', $var);

        $sql = 'SELECT id FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $var = $this->db->selectVar($sql, $params);
        static::assertSame('3', $var);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $var = $this->db->selectVar($sql, $params);
        static::assertFalse($var);
    }

    public function testSelectVarError(): void
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $variable = $this->db->selectVar($sql);
        static::assertFalse($variable);
    }

    public function testPdoParamType(): void
    {
        $sql = 'SELECT :true AS `true`, :false AS `false`, :null AS `null`, :float AS `float`,
                :int AS `int`, :string AS `string`, :resource AS `resource`';
        $params = [
            'true'    => true,
            'false'   => false,
            'null'    => null,
            'float'   => 1.2,
            'int'     => 800,
            'string'  => 'string',
            'resource'=> fopen(__DIR__ . '/test-dump-mysql.sql', 'r')
        ];
        $row = $this->db->selectRow($sql, $params);

        static::assertSame('1', $row['true']);
        static::assertSame('0', $row['false']);
        static::assertSame(null, $row['null']);
        static::assertSame('1.2', $row['float']);
        static::assertSame('800', $row['int']);
        static::assertSame('string', $row['string']);
        static::assertSame('-- MySQL dump', mb_substr($row['resource'], 0, 13));
    }

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

    public function testSelect(): void
    {
        $sql = 'SELECT * FROM test_select';
        $statement = $this->db->select($sql);
        static::assertSame(PDOStatement::class, get_class($statement));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $statement = $this->db->select($sql, $params);
        static::assertSame(PDOStatement::class, get_class($statement));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $statement = $this->db->select($sql, $params);
        static::assertSame(PDOStatement::class, get_class($statement));
    }

    public function testRead(): void
    {
        $sql = 'SELECT * FROM test_select';
        $statement = $this->db->select($sql);
        $row = $this->db->read($statement);
        static::assertSame(4, count($row));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $statement = $this->db->select($sql, $params);
        $row = $this->db->read($statement);
        static::assertSame(4, count($row));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $statement = $this->db->select($sql, $params);
        $row = $this->db->read($statement);
        static::assertFalse($row);

        $rows = [];
        $statement = $this->db->select('SELECT * FROM test_select');
        while ($row = $this->db->read($statement)) {
            $rows[] = $row;
        }

        static::assertSame(6, count($rows));
    }

    public function testReadAll(): void
    {
        $sql = 'SELECT * FROM test_select';
        $statement = $this->db->select($sql);
        $rows = $this->db->readAll($statement);
        static::assertSame(6, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $statement = $this->db->select($sql, $params);
        $rows = $this->db->readAll($statement);
        static::assertSame(3, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $statement = $this->db->select($sql, $params);
        $rows = $this->db->readAll($statement);
        static::assertSame(0, count($rows));
    }

    public function testTransaction(): void
    {
        $this->db->completeTransaction();

        $this->db->startTransaction();

        $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
        $params = ['name' => 'my name', 'id' => 1];
        $this->db->update($sql, $params);

        $this->db->completeTransaction();

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('my name', $this->db->selectVar($sql, $params));
    }

    public function testTransactionError(): void
    {
        try {
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));

            $this->db->selectVar($sql);

            $this->db->completeTransaction();
        } catch (DatabaseException $e) {
            $this->db->completeTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('A', $this->db->selectVar($sql, $params));
        }
    }

    // errors

    public function testErrors(): void
    {
        static::assertFalse($this->db->hasErrors());
        static::assertSame([], $this->db->getErrors());
        static::assertSame(null, $this->db->getLastError());

        try {
            $this->db->selectVar('SELECT name FROM test WHERE id = :id');
        } catch (DatabaseException $e) {
            static::assertTrue($this->db->hasErrors());
            static::assertSame(4, count($this->db->getLastError()));

            $this->db->cleanErrors();

            static::assertFalse($this->db->hasErrors());
            static::assertSame([], $this->db->getErrors());
            static::assertSame(null, $this->db->getLastError());
        }
    }

    // save queries

    public function testSaveQueries(): void
    {
        static::assertFalse($this->db->hasSaveQueries());

        $this->db->enableSaveQueries();

        static::assertTrue($this->db->hasSaveQueries());

        $this->db->disableSaveQueries();

        static::assertFalse($this->db->hasSaveQueries());

        $this->db->enableSaveQueries();

        static::assertTrue($this->db->hasSaveQueries());

        static::assertSame([], $this->db->getSavedQueries());

        $this->db->selectAll('SELECT * FROM test_select');

        $queries = $this->db->getSavedQueries();

        static::assertSame(2, count($queries));

        $this->db->cleanSavedQueries();

        $queries = $this->db->getSavedQueries();

        static::assertSame(0, count($queries));
    }

    // specific command

    public function testTruncateTable(): void
    {
        static::assertTrue($this->db->truncateTable('test'));
    }

    public function testTruncateTables(): void
    {
        static::assertTrue($this->db->truncateTables(['test', 'test_select']));
    }

    public function testDropTable(): void
    {
        static::assertTrue($this->db->dropTable('test'));
    }

    public function testDropTables(): void
    {
        static::assertTrue($this->db->dropTables(['test', 'toto']));
    }

    // low level

    public function testConnect(): void
    {
        static::assertNull($this->db->getPdo());

        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));
    }

    public function testGetPdo(): void
    {
        static::assertNull($this->db->getPdo());

        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));
    }

    public function testDisconnect(): void
    {
        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));

        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }
}
