# Database Package

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rancoud/database)
[![Packagist Version](https://img.shields.io/packagist/v/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Packagist Downloads](https://img.shields.io/packagist/dt/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Composer dependencies](https://img.shields.io/badge/dependencies-0-brightgreen)](https://github.com/rancoud/database/blob/master/composer.json)
[![Test workflow](https://img.shields.io/github/actions/workflow/status/rancoud/database/test.yml?branch=master)](https://github.com/rancoud/database/actions/workflows/test.yml)
[![Codecov](https://img.shields.io/codecov/c/github/rancoud/database?logo=codecov)](https://codecov.io/gh/rancoud/database)

Request Database (use PDO). Supported drivers: MySQL, PostgreSQL, SQLite.

## Installation
```php
composer require rancoud/database
```

## How to use it?
### Connection to a database
```php
// Create a configurator
$params = [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'user'      => 'root',
    'password'  => '',
    'database'  => 'test_database'
];
$databaseConf = new Configurator($params);

// No singleton
$database = new Database($databaseConf);

// With named instances
$database = Database::setInstance($databaseConf, 'primary');
```

## Examples
For example we have a table `users` with this schema:

| Field | Type | Options |
| --- | --- | --- |
| id | int(8) | primary key, auto increment |
| username | varchar(255) |  |
| ranking | int(8) |  |

In the table we have these data:

| id | username | ranking |
| --- | --- | --- |
| 1 | taylor | 10 |
| 2 | alison | 30 |
| 3 | swifts | 20 |

### Select methods
The output is always an array.

#### SelectAll
Return all rows
```php
$results = $database->selectAll("SELECT * FROM users");

// Output be like
[
    ['id' => '1', 'username' => 'taylor', 'ranking' => 10],
    ['id' => '2', 'username' => 'alison', 'ranking' => 30],
    ['id' => '3', 'username' => 'swifts', 'ranking' => 20]
]
```

#### SelectRow
Return only the first row
```php
$results = $database->selectRow("SELECT * FROM users");

// Output be like
['id' => '1', 'username' => 'taylor', 'ranking' => 10]
```

#### SelectCol
Return only the first column
```php
$results = $database->selectCol("SELECT username FROM users");

// Output be like
[
    'taylor',
    'alison',
    'swifts'
]
```

#### SelectVar
Return only the first value of first line
```php
$results = $database->selectVar("SELECT username FROM users WHERE id = 3");

// Output be like
'swifts'
```

#### Select + (Read OR ReadAll)
Having the statement and use read to get row by row or readAll for all data.
Useful when you want to use a specific fetch mode.
```php
$statement = $database->select("SELECT * FROM users");
$row = $database->read($statement);

// Output be like
['id' => '1', 'username' => 'taylor', 'ranking' => 10]

$statement = $database->select("SELECT * FROM users");
$rows = $database->readAll($statement);

// Output be like
[
    ['id' => '1', 'username' => 'taylor', 'ranking' => 10],
    ['id' => '2', 'username' => 'alison', 'ranking' => 30],
    ['id' => '3', 'username' => 'swifts', 'ranking' => 20]
]
```

#### Count
Return only the value when using `SELECT COUNT(*) FROM ...`.
```php
$count = $database->count("SELECT COUNT(*) FROM users");

// Output be like
3
```

## Insert
```php
// insert with parameters and get last insert id
$params = ['username' => 'adam', 'ranking' => 100];
$lastInsertId = $database->insert("INSERT INTO users (username, ranking) VALUES (:username, :ranking)", $params, true);

// Output be like
4
```

## Update
```php
// update with parameters and get the number of affected rows
$params = ['username' => 'adam', 'id' => 4];
$affectedRowsCount = $database->update("UPDATE users SET username = :username WHERE id = :id", $params, true);

// Output be like
1
```

## Delete
```php
// delete with parameters and get the number of affected rows
$params = ['id' => 4];
$affectedRowsCount = $database->delete("DELETE FROM users WHERE id = :id", $params, true);

// Output be like
1
```

## Transactions
Nested transactions are supported for MySQL, PostgreSQL, SQLite.
```php
$database->startTransaction();

if (isOk()) {
    $database->commitTransaction();
} else {
    $database->rollbackTransaction();
}
```

## Named instances
You have to name your instances.  
Then you can get them by their name.
```php
Database::setInstance($databaseConfA, 'primary');
Database::setInstance($databaseConfB, 'secondary');

/** A few moments later **/

$db = Database::getInstance('secondary');
```

## Configurator
### Constructor Settings
Here is the description of the array passed to the construct

#### Mandatory keys
| Parameter | Type | Description |
| --- | --- | --- |
| driver | string | driver of the database, it will be check with PDO::getAvailableDrivers |
| host | string | hostname of the database (port number may be included, e.g `example.org:5342`) |
| user | string | user used to connect to the database |
| password | string | password used to connect to the database |
| database | string | name of the database |

#### Optional keys
| Parameter | Type | Default value | Description |
| --- | --- | --- | --- |
| save_queries | bool | true | all queries will be saved in memory with execution time and the connection time |
| persistent_connection | bool | false | use persistent connection |
| charset | string | it depends on the driver (MySQL: `utf8mb4` , PostgreSQL: `UTF8`) | set specific database charset |
| parameters | array | [] | extra parameters used by PDO on connection |

### Methods
* createPDOConnection(): PDO
* disablePersistentConnection(): void
* disableSaveQueries(): void
* enablePersistentConnection(): void
* enableSaveQueries(): void
* getCharset(): string
* getDatabase(): string
* getDSN(): string
* getDriver(): string
* getHost(): string
* getParameters(): array
* getParametersForPDO(): array
* getPassword(): string
* getUser(): string
* hasPersistentConnection(): bool
* hasSavedQueries(): bool
* setCharset(charset: string): void
* setDatabase(database: string): void
* setDriver(driver: string): void
* setHost(host: string): void
* setParameter(key: mixed, value: mixed): void
* setParameters(parameters: array): void
* setPassword(password: string): void
* setUser(user: string): void

## Database
### Constructor
#### Mandatory
| Parameter | Type | Description |
| --- | --- | --- |
| configurator | Configurator | Database configuration |

### General Commands
* selectAll(sql: string, [parameters: array = []]): array
* selectRow(sql: string, [parameters: array = []]): array
* selectCol(sql: string, [parameters: array = []]): array
* selectVar(sql: string, [parameters: array = []]): mixed
* insert(sql: string, [parameters: array = []], [getLastInsertId: bool = false]): ?int
* update(sql: string, [parameters: array = []], [getAffectedRowsCount: bool = false]): ?int
* delete(sql: string, [parameters: array = []], [getAffectedRowsCount: bool = false]): ?int
* count(sql: string, [parameters: array = []]): ?int
* exec(sql: string, [parameters: array = []]): void
* select(sql: string, [parameters: array = []]): PDOStatement
* read(statement: PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]): mixed
* readAll(statement: PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]): array

### Transactions
* startTransaction(): void
* completeTransaction(): void
* commitTransaction(): void
* rollbackTransaction(): void

### Errors
* hasErrors(): bool
* getErrors(): array
* getLastError(): ?array
* cleanErrors(): void

### Save Queries
* hasSavedQueries(): bool
* enableSaveQueries(): void
* disableSaveQueries(): void
* cleanSavedQueries(): void
* getSavedQueries(): array

### Specific Commands
* truncateTables(...tables: string): void
* dropTables(...tables: string): void
* useSqlFile(filepath: string): void

### Low Level
* connect(): void
* disconnect(): void
* getPDO(): ?PDO

### Static Method
* setInstance(configurator: Configurator, [name: string = primary]]): self
* hasInstance([name: string = primary]): bool
* getInstance([name: string = primary]): ?self

## How to Dev
`docker-compose build && docker-compose run lib composer ci` for launching tests
