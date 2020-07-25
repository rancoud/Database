# Database Package

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rancoud/database)
[![Packagist Version](https://img.shields.io/packagist/v/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Packagist Downloads](https://img.shields.io/packagist/dt/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Composer dependencies](https://img.shields.io/badge/dependencies-0-brightgreen)](https://github.com/rancoud/database/blob/master/composer.json)
[![Test workflow](https://img.shields.io/github/workflow/status/rancoud/database/test?label=test&logo=github)](https://github.com/rancoud/database/actions?workflow=test)
[![Codecov](https://img.shields.io/codecov/c/github/rancoud/database?logo=codecov)](https://codecov.io/gh/rancoud/database)
[![composer.lock](https://poser.pugx.org/rancoud/database/composerlock)](https://packagist.org/packages/rancoud/database)

Request Database (use PDO), tested with MySQL / PostgreSQL / SQLite.  

## Installation
```php
composer require rancoud/database
```

## How to use it?
### Connection to a database
```php
// Create a configurator
$params = [
    'engine'    => 'mysql',
    'host'      => 'localhost',
    'user'      => 'root',
    'password'  => '',
    'database'  => 'test_database'
];
$databaseConf = new Configurator($params);

// No singleton
$database = new Database($databaseConf);

// With singleton
$singletonDatabase = Database::getInstance($databaseConf);
```

## Examples
For example we have a table `users` with this schema:  

| Field | Type | Options |
| --- | --- | --- |
| id | int(8) | primary key, auto increment |
| username | varchar(255) |  |
| ranking | int(8) |  |

In the table we have thoses datas:  

| id | username | ranking |
| --- | --- | --- |
| 1 | taylor | 10 |
| 2 | alison | 30 |
| 3 | swifts | 20 |

### Selects
The output is always an associative array.  

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
Return only the column  
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
Return only the field  
```php
$results = $database->selectVar("SELECT username FROM users WHERE id = 3");

// Output be like
'swifts'
```

#### Select + (Read OR ReadAll)
Having the statement and use read for getting row by row or readAll for all data.  
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
// insert with parameters and getting last insert id
$params = ['username' => 'adam', 'ranking' => 100];
$lastInsertId = $database->insert("INSERT INTO users (username, ranking) VALUES (:username, :ranking)", $params, true);

// Output be like
4
```

## Update
```php
// update with parameters and getting count row affected
$params = ['username' => 'adam', 'id' => 4];
$countRowAffected = $database->update("UPDATE users SET username = :username WHERE id = :id", $params, true);

// Output be like
1
```

## Delete
```php
// delete with parameters and getting count row affected
$countRowAffected = $database->delete("DELETE FROM users WHERE id = 4", [], true);

// Output be like
1
```

## Transactions
Nested transactions are supported for MySQL / PostgreSQL / SQLite.  
```php
$database->startTransaction();

if (isOk()) {
    $database->commitTransaction();
} else {
    $database->rollbackTransaction();
}
```

## Configurator
### Constructor Settings
Here it's the description of the array passed to the construct  

#### Mandatory
| Parameter | Type | Description |
| --- | --- | --- |
| engine | string | engine of the database, it will be check with PDO::getAvailableDrivers |
| host | string | hostname of the database |
| user | string | user used to connect to the database |
| password | string | password used to connect to the database |
| database | string | name of the database |

#### Optionnals
| Parameter | Type | Default value | Description |
| --- | --- | --- | --- |
| save_queries | bool | true | all queries will be saved in memory with execution time and the connection time |
| permanent_connection | bool | false | use permanent connection |
| report_error | string | 'exception' | how PDO react with errors; values used: silent \| exception |
| charset | string | 'utf8mb4' | set specific database charset |
| parameters | array | [] | extra parameters used by PDO on connection |

### Methods
* createPDOConnection(): PDO  
* disablePermanentConnection(): void  
* disableSaveQueries(): void  
* enablePermanentConnection(): void  
* enableSaveQueries(): void  
* getCharset(): string  
* getDatabase(): string  
* getDsn(): string  
* getEngine(): string  
* getHost(): string  
* getParameters(): array  
* getParametersForPDO(): array  
* getPassword(): string  
* getReportError(): string  
* getUser(): string  
* hasPermanentConnection(): bool  
* hasSaveQueries(): bool  
* setCharset(charset: string): void  
* setDatabase(database: string): void  
* setEngine(engine: string): void  
* setHost(host: string): void  
* setParameter(key: mixed, value: mixed): void  
* setParameters(parameters: array): void  
* setPassword(password: string): void  
* setReportError(reportError: string): void  
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
* update(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]): ?int  
* delete(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]): ?int  
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
* hasSaveQueries(): bool  
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
* getPdo(): ?PDO  

### Static Method
* getInstance([configurator: Configurator = null]): self  

## How to Dev
`composer ci` for php-cs-fixer and phpunit and coverage  
`composer lint` for php-cs-fixer  
`composer test` for phpunit and coverage  