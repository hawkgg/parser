<?php

include_once '../parser/lib/curl.php';
include_once '../parser/lib/simple_html_dom.php';
include_once '../parser/lib/parser.php';
include_once '../parser/lib/sql.php';

// Убираем ограничение по времени исполнения скрипта
ini_set('max_execution_time', 0);

// Параметры нашей БД
define(MYSQL_SERVER, 'localhost');
define(MYSQL_DB, 'db');
define(MYSQL_USER, 'root');
define(MYSQL_PASSWORD, '');

$c = Curl::app()
        ->headers(1) // отображение заголовков в запросе
        ;

// Загружаем настройки соединения
$c->config_load('../parser/cfg/1.cfg');

$data = $c->request('https://pdd.yandex.ru/');

echo '<pre>';
print_r($data);
echo '</pre>';