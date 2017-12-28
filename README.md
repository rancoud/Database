# Database Package

[![Build Status](https://travis-ci.org/rancoud/Database.svg?branch=fix-better-exception)](https://travis-ci.org/rancoud/Database) [![Coverage Status](https://coveralls.io/repos/github/rancoud/Database/badge.svg?branch=fix-better-exception)](https://coveralls.io/github/rancoud/Database?branch=fix-better-exception)

Request Database with PDO without write it.  

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
* selectAll(sql: string, [parameters: array = []]):array|null  
* selectRow(sql: string, [parameters: array = []]):array|null  
* selectCol(sql: string, [parameters: array = []]):array|null  
* selectVar(sql: string, [parameters: array = []]):string|null  
* insert(sql: string, [parameters: array = []], [getLastInsertId: bool = false]):int|null|bool  
* update(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]):int|null|bool  
* delete(sql: string, [parameters: array = []], [getCountRowAffected: bool = false]):int|null|bool  
* count(sql: string, [parameters: array = []]):int|bool  
* exec(sql: string, [parameters: array = []]):bool  
* select(sql: string, [parameters: array = []]):PDOStatement|null  
* read(statement: \PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]):mixed  
* readAll(statement: \PDOStatement, [fetchType: int = PDO::FETCH_ASSOC]):array  

### Transactions
* startTransaction():bool  
* completeTransaction():bool  

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
* optimizeTable(table: string):bool  
* optimizeTables(tables: array):bool  
* useSqlFile(filepath: string):bool  

### Low Level
* connect():void  
* disconnect():void  
* getPdo():PDO  

## How to Dev
### Linux
#### Coding Style
./vendor/bin/phpcbf  
./vendor/bin/phpcs  
./vendor/bin/php-cs-fixer fix --diff  

#### Unit Testing
./vendor/bin/phpunit --colors  

#### Code Coverage
##### Local
./vendor/bin/phpunit --colors --coverage-html ./coverage
##### Coverwall
./vendor/bin/phpunit --colors --coverage-text --coverage-clover build/logs/clover.xml  

### Windows
#### Coding Style
"vendor/bin/phpcbf.bat"  
"vendor/bin/phpcs.bat"  
"vendor/bin/php-cs-fixer.bat" fix --diff   

#### Unit Testing
"vendor/bin/phpunit.bat" --colors  
  
#### Code Coverage
##### Local
"vendor/bin/phpunit.bat" --colors --coverage-html ./coverage

##### Coverwall
"vendor/bin/phpunit.bat" --colors --coverage-text --coverage-clover build/logs/clover.xml  