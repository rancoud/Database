# Database Package

[![Build Status](https://travis-ci.org/rancoud/Database.svg?branch=master)](https://travis-ci.org/rancoud/Database) [![Coverage Status](https://coveralls.io/repos/github/rancoud/Database/badge.svg?branch=master)](https://coveralls.io/github/rancoud/Database?branch=master)

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
```

## Configurator
### Settings
'engine' => 'mysql', check with PDO::getAvailableDrivers
'host'          => '127.0.0.1',
'user'          => 'root',
'password'      => '',
'database'      => 'test_database',
'save_queries'  => true, save all queries with time
'permanent_connection' => false, setup permanent connection
'report_error'  => 'silent', silent or exception for reporting errors
'charset'       => 'utf8', charset
'parameters'    => []

### Methods

## Database methods
* select  
* read  
* insert  
* update  
* delete  
* count  
* exec  
* getDriver  
* selectAll  
* selectRow  
* selectCol  
* selectVar  
* beginTransaction  
* commit  
* rollback  
* hasError  
* getError  
* truncate  
* dropTable  
* optimize  
* useSqlFile  

## How to Dev
### Linux
#### Coding Style
./vendor/bin/phpcbf  
./vendor/bin/phpcs  
./vendor/bin/php-cs-fixer fix --diff  

#### Unit Testing
./vendor/bin/phpunit --colors  

#### Code Coverage
"vendor/bin/phpunit.bat" --colors --coverage-text --coverage-clover build/logs/clover.xml  

### Windows
#### Coding Style
"vendor/bin/phpcbf.bat"  
"vendor/bin/phpcs.bat"  
"vendor/bin/php-cs-fixer.bat" fix --diff   

#### Unit Testing
"vendor/bin/phpunit.bat" --colors  
  
#### Code Coverage
"vendor/bin/phpunit.bat" --colors --coverage-text --coverage-clover build/logs/clover.xml  