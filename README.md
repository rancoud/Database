# Database Package

[![Build Status](https://travis-ci.org/rancoud/Database.svg?branch=master)](https://travis-ci.org/rancoud/Database) [![Coverage Status](https://coveralls.io/repos/github/rancoud/Database/badge.svg?branch=master)](https://coveralls.io/github/rancoud/Database?branch=master)

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
### Settings
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

## Database Methods
### General Commands  
* selectAll(sql: string, [parameters: array = []]):array|bool  
* selectRow(sql: string, [parameters: array = []]):array|bool  
* selectCol(sql: string, [parameters: array = []]):array|bool  
* selectVar(sql: string, [parameters: array = []]):mixed|bool  
* insert(sql: string, [parameters: array = []], [getLastInsertId: bool = false]):int|bool  
* update(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]):int|bool  
* delete(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]):int|bool  
* count(sql: string, [parameters: array = []]):int|bool  
* exec(sql: string, [parameters: array = []]):bool  
* select(sql: string, [parameters: array = []]):PDOStatement|null  
* read(statement: \PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]):mixed  
* readAll(statement: \PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]):array  

### Transactions
* startTransaction():bool  
* completeTransaction():bool  
* commitTransaction():bool  
* rollbackTransaction():bool  

### Errors
* hasErrors():bool  
* getErrors():array  
* getLastError():array|null  
* cleanErrors():void  

### Save Queries
* hasSaveQueries():bool  
* enableSaveQueries():void  
* disableSaveQueries():void  
* cleanSavedQueries():void  
* getSavedQueries():array  

### Specific Commands
* truncateTable(table: string):bool  
* truncateTables(tables: array):bool  
* dropTable(table: string):bool  
* dropTables(tables: array):bool  
* useSqlFile(filepath: string):bool  

### Low Level
* connect():void  
* disconnect():void  
* getPdo():PDO  

## How to Dev
`./run_all_commands.sh` for php-cs-fixer and phpunit and coverage  
`./run_php_unit_coverage.sh` for phpunit and coverage  