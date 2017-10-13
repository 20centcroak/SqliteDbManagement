# SqliteDbManagement
php classes to manage sqlite databases using Croak/DbManagement

This is an implementation of DbManagement (https://github.com/20centcroak/DBManagement) to be used with sqlite database. See this package for more information.

To install this package, use Composer : 

**composer require croak-sqlite-dbmanagement/sqlite-dbmanagement**

This package is composed of a single class DbManagementSqlite and requires DbManagement

DbDbManagementSqlite implement DbManagement with the sqlite syntax and then allows quering very easily in sqlite database.

For querying with the GET verb, use the following keywords:

    [fieldName]-up pour trier les données par ordre ascendant sur le champ [fieldName]
    [fieldName]-down pour trier les données par ordre descendant sur le champ [fieldName]
    [fieldName]-min=XXX pour sélectionner les données telles que [fieldName]>=XXX
    [fieldName]-max=XXX pour sélectionner les données telles que [fieldName]<=XXX
    [fieldName]=XXX pour sélectionner les données telles que [fieldName]=XXX

Let assume that the route localhost:8080/measures is defined in your app and the fields named "value" and "flag" are defined in the database table, here is an example of a GET request :

localhost:8080/measures?value-min=12&value-up&flag-down

An example of use can be found in https://github.com/20centcroak/api-iot
