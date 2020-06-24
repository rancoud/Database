# Database Package

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rancoud/database)
[![Packagist Version](https://img.shields.io/packagist/v/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Packagist Downloads](https://img.shields.io/packagist/dt/rancoud/database)](https://packagist.org/packages/rancoud/database)
[![Composer dependencies](https://img.shields.io/badge/dependencies-0-brightgreen)](https://github.com/rancoud/Pagination/blob/master/composer.json)
[![Test workflow](https://img.shields.io/github/workflow/status/rancoud/database/test?label=test&logo=github)](https://github.com/rancoud/database/actions?workflow=test)
[![Codecov](https://img.shields.io/codecov/c/github/rancoud/database?logo=codecov)](https://codecov.io/gh/rancoud/database)
[![composer.lock](https://poser.pugx.org/rancoud/database/composerlock)](https://packagist.org/packages/rancoud/database)

Request Database (use PDO).  

## Installation
```php
composer require rancoud/database
```

## How to use it?
```php
$params = ['engine' => 'mysql',
        'host'          => 'localhost',
        'user'          => 'root',
        'password'      => '',
        'database'      => 'test_database'];
$databaseConf = new Configurator($params);

// No singleton
$database = new Database($databaseConf);

// With singleton
$singletonDatabase = Database::getInstance($databaseConf);

// The output is always an associative array
$results = $database->selectAll("SELECT * FROM mytable WHERE something > :thing", ['thing' => 5]);
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
| charset | string | 'utf8' | set specific database charset |
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
* hasThrowException(): bool  
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
* selectAll(sql: string, [parameters: array = []]): array|bool  
* selectRow(sql: string, [parameters: array = []]): array|bool  
* selectCol(sql: string, [parameters: array = []]): array|bool  
* selectVar(sql: string, [parameters: array = []]): mixed|bool  
* insert(sql: string, [parameters: array = []], [getLastInsertId: bool = false]): int|bool  
* update(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]): int|bool  
* delete(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]): int|bool  
* count(sql: string, [parameters: array = []]): int|bool  
* exec(sql: string, [parameters: array = []]): bool  
* select(sql: string, [parameters: array = []]): ?PDOStatement  
* read(statement: PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]): mixed  
* readAll(statement: PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]): array  

### Transactions
* startTransaction(): bool  
* completeTransaction(): bool  
* commitTransaction(): bool  
* rollbackTransaction(): bool  

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
* truncateTables(...tables: string): bool  
* dropTables(...tables: string): bool  
* useSqlFile(filepath: string): bool  

### Low Level
* connect(): bool  
* disconnect(): void  
* getPdo(): ?PDO  

### Static Method
* getInstance([configurator: Configurator = null]): self  

## How to Dev
`composer ci` for php-cs-fixer and phpunit and coverage  
`composer lint` for php-cs-fixer  
`composer test` for phpunit and coverage  