# routerDb
routerDb - One simple interface for working with any number of databases at the same time
## routerDb — Один интерфейс для работы с любым количеством баз данных

Поддерживаются следующие системы хранения и управления данными через роутер [routerDb\Router](https://github.com/pllano/api-shop/blob/master/app/classes/Database/Router.php):
- работа через API транзитом через клас [ApiDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/ApiDb.php)
- позволяет работать напрямую с [jsonDB](https://github.com/pllano/json-db) транзитом через клас [JsonDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/JsonDb.php)
- jsonapiDb - Вы можете хранить данные в [jsonDB](https://github.com/pllano/json-db) в любом месте (даже на удаленном сервере) и работать с ней через API интерфейс, транзитом через клас [JsonapiDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/JsonapiDb.php)
- позволяет работать напрямую с MySQL транзитом через клас [MysqlDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/MysqlDb.php)
- позволяет работать с Elasticsearch с использованием [Elasticsearch-PHP](https://github.com/elastic/elasticsearch-php) транзитом через клас [ElasticsearchDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/ElasticsearchDb.php)
- Без особых сложностей возможно написать клас для работы с любой другой базой данных.

### Использовать несколько баз данных
`routerDb` — позволяет одновременно работать с любым количеством баз данных и писать один код для всех баз данных, а всю интеграцию вывести в отдельный класс для каждой базы данных.
### Один стантарт запросов ко всем базам
Для унификации работы с базами данных используется наш собственный стандарт [APIS-2018](https://github.com/pllano/APIS-2018/)
### Переключатся между базами данных на лету
`routerDb` — Имеет встроенный роутер переключения между базами, он может переключатся между базами данных на лету, если основная база данных недоступна. Для этого необходимо в конфигурации указать названия обоих баз. Он контролирует состояние баз данных `master` и `slave`. Если база указанная в конфигурации `$resource` недоступна, подключит `master` или `slave` базу.
#### Получение данных `GET`
```php
use routerDb\Db;
use routerDb\Router;

// Ресурс (таблица) к которому обращаемся
$resource = "price";
// Массив с данными запроса
$getArr = [
    "limit" => 10,
    "offset" => 0,
    "order" => "DESC",
    "sort" => "created",
    "state" => 1,
    "relations" => base64_encode('{
        "product": ["type_id","brand_id","serie_id","articul"],
        "user": "all",
        "address": "all"
    }')
];
// Отдаем конфигурацию
// Подробности формирования конфигурации ниже
$db = new Db($config);
// Получаем название базы для указанного ресурса
$db_name = $db->get($resource);
// Подключаемся к базе
$router = new Router($db_name);
// Отправляем запрос для получения списка
$router->get($resource, $getArr);
// или
// Получить данные записи по id и дополнительные данные с других связанных ресурсов по параметрам relations
$router->get($resource, ["relations" => "address,cart,user:user_id:iname:oname:phone:email"], $id);
```
Обратите внимание на очень важный параметр запроса [`relations`](https://github.com/pllano/APIS-2018/blob/master/structure/relations.md) позволяющий получать в ответе необходимые данные из других связанных ресурсов.
#### Создание `POST`
```php
use routerDb\Db;
use routerDb\Router;

// Ресурс (таблица) к которому обращаемся
$resource = "user";
// Массив с данными запроса чуть по другому
$postArr["name"] = "Admin";
$postArr["user_id"] = 2;
$postArr["email"] = "admin@example.com";
// Отдаем конфигурацию
// Подробности формирования конфигурации ниже
$db = new Db($config);
// Получаем название базы для указанного ресурса
$db_name = $db->get($resource);
// Подключаемся к базе
$router = new Router($db_name);
// Вернет id созданной записи или null при ошибке
$new_id = $router->post($resource, $postArr);
 
```
#### Обновление `PUT`
```php
use routerDb\Db;
use routerDb\Router;

// Ресурс (таблица) к которому обращаемся
$resource = "user";
// id записи
$id = 1;
// Массив с данными запроса
$putArr["name"] = "Admin2";
$putArr["email"] = "admin2@example.com";
// Отдаем конфигурацию
// Подробности формирования конфигурации ниже
$db = new Db($config);
// Получаем название базы для указанного ресурса
$db_name = $db->get($resource);
// Подключаемся к базе
$router = new Router($db_name);
// Вернет 1 если все ок, или null при ошибке
$response = $router->put($resource, $postArr, $id);
 
```
#### Удаление `DELETE`
```php
use routerDb\Db;
use routerDb\Router;

// Ресурс (таблица) к которому обращаемся
$resource = "user";
// id записи
$id = 1;
// Пустой массив параметров
$delArr = [];
// Отдаем конфигурацию
// Подробности формирования конфигурации ниже
$db = new Db($config);
// Получаем название базы для указанного ресурса
$db_name = $db->get($resource);
// Подключаемся к базе
$router = new Router($db_name);
// Вернет 1 если все ок, или null при ошибке
$response = $router->delete($resource, $delArr, $id);
 
```

### Глобальная конфигурация

```php
// Название основной базы данных. По умолчанию api
$config["db"]["master"] = "api";
// Название резервной базы данных. По умолчанию jsonapi
$config["db"]["slave"] = "json";
```
### Конфигурация ресурсов
Индивидуально по каждому ресурсу (таблице)
```php
// Цены получать через API
$config["resource"]["price"]["db"] = "api";
// Там где нужен поиск храним в Elasticsearch
$config["resource"]["params"]["db"] = "elasticsearch";
$config["resource"]["product"]["db"] = "elasticsearch";
$config["resource"]["type"]["db"] = "elasticsearch";
$config["resource"]["brand"]["db"] = "elasticsearch";
$config["resource"]["serie"]["db"] = "elasticsearch";
$config["resource"]["article"]["db"] = "elasticsearch";
$config["resource"]["article_category"]["db"] = "elasticsearch";
// Локализацию и валюты получать от jsonapi
$config["resource"]["language"]["db"] = "jsonapi";
$config["resource"]["currency"]["db"] = "jsonapi";
$config["resource"]["category"]["db"] = "jsonapi";
// Платежи хранить в Oracle
$config["resource"]["pay"]["db"] = "oracle";
// Другие данные хранить в MySQL
$config["resource"]["user"]["db"] = "mysql";
$config["resource"]["site"]["db"] = "mysql";
$config["resource"]["user"]["db"] = "mysql";
$config["resource"]["cart"]["db"] = "mysql";
$config["resource"]["order"]["db"] = "mysql";
$config["resource"]["address"]["db"] = "mysql";
$config["resource"]["images"]["db"] = "mysql";
$config["resource"]["seo"]["db"] = "mysql";
$config["resource"]["description"]["db"] = "mysql";
$config["resource"]["contact"]["db"] = "mysql";
$config["resource"]["role"]["db"] = "mysql";
```
### Конфигурация баз данных
#### jsonDb
Подключить с помощью Composer
```php
"require": {
	"pllano/json-db": "^1.0.5"
}
```
Настройки подключения к jsonDb напрямую
```php
// Директория для хранения файлов json базы данных.
$config["db"]["json"]["dir"] = __DIR__ . "/../../json-db/db/";
// Кеширование запросов
$config["db"]["json"]["cached"] = false; // true|false
// Время жизни кеша
$config["db"]["json"]["cache_lifetime"] = 60;
// Очередь на запись
$config["db"]["json"]["temp"] = false;
// Работает через API
$config["db"]["json"]["api"] = false;
// Шифруем базу
$config["db"]["json"]["crypt"] = false;
```
Настройки подключения к jsondb через API
```php
// URL API jsondb
$config["db"]["jsonapi"]["url"] = "https://xti.com.ua/json-db/";
// Доступные методы аутентификации: null, CryptoAuth, QueryKeyAuth, HttpTokenAuth, LoginPasswordAuth
$config["db"]["jsonapi"]["auth"] = null;
// Публичный ключ аутентификации
$config["db"]["jsonapi"]["public_key"] = "";
// Приватный ключ шифрования
$config["db"]["jsonapi"]["private_key"] = "";
```
Настройки подключения к RESTful API
```php
// Если работает через API будет брать часть конфигурации из api
$config["db"]["api"]["config"] = true; // true|false
// URL API
$config["db"]["api"]["url"] = "";
// Доступные методы аутентификации: CryptoAuth, QueryKeyAuth, HttpTokenAuth, LoginPasswordAuth
$config["db"]["api"]["auth"] = "QueryKeyAuth";
// Публичный ключ аутентификации
$config["db"]["api"]["public_key"] = "";
// Приватный ключ шифрования
$config["db"]["api"]["private_key"] = "";
```
Настройки подключения к базе MySQL
```php
$config["db"]["mysql"]["host"] = "localhost";
$config["db"]["mysql"]["dbname"] = "";
$config["db"]["mysql"]["port"] = "";
$config["db"]["mysql"]["charset"] = "utf8";
$config["db"]["mysql"]["connect_timeout"] = 15;
$config["db"]["mysql"]["user"] = "";
$config["db"]["mysql"]["password"] = "";
```
#### Elasticsearch PHP
Подключить с помощью Composer
```php
"require": {
"elasticsearch/elasticsearch": "~6.0"
}
```
Настройки подключения к Elasticsearch
```php
// По умолчанию http://localhost:9200/
$config["db"]["elasticsearch"]["host"] = "localhost";
$config["db"]["elasticsearch"]["port"] = 9200;
// Учитывая то что в следующих версиях Elasticsearch не будет type
// вы можете отключить type поставив false
// в этом случае index будет формироватся так index_type
$config["db"]["elasticsearch"]["type"] = true; // true|false
$config["db"]["elasticsearch"]["index"] = "elastic";
// Если подключение к elasticsearch требует логин и пароль установите auth=true
$config["db"]["elasticsearch"]["auth"] = false; // true|false
$config["db"]["elasticsearch"]["user"] = "elastic";
$config["db"]["elasticsearch"]["password"] = "elastic_password";
```
 
