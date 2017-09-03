# Database Package

[![Build Status](https://travis-ci.org/Rancoud/Database.svg?branch=master)](https://travis-ci.org/Rancoud/Database) [![Coverage Status](https://coveralls.io/repos/github/Rancoud/Database/badge.svg?branch=master)](https://coveralls.io/github/Rancoud/Database?branch=master)

Request Database with PDO without write it.  

## How to use it?
```
$databaseConf = new DatabaseConfigurator('mysql', 'localhost', 'user', 'password', 'database');  
$database = new Database($databaseConf, new PDODriver());  
```

## DatabaseConfigurator , DatabaseDriver , Database ?
The configurator specify how to setup the driver.  
So you can use the same configurator for database but use mysqli driver insted of PDO.  
By default it's shipped with DatabaseConfiguratorDatabase and PDODriver.  

But you can wrote your own configurator and driver for using xml files intead of database.  
Database is a facade wich made easier to wrote code by abstracting driver used.

## DatabaseDriverPdo methods
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