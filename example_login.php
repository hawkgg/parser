<?php

include_once 'lib/curl.php';
include_once 'lib/simple_html_dom.php';
include_once 'lib/parser.php';
include_once 'lib/sql.php';

// Убираем ограничение по времени исполнения скрипта
ini_set('max_execution_time', 0);

// Параметры нашей БД
define(MYSQL_SERVER, 'localhost');
define(MYSQL_DB, 'db');
define(MYSQL_USER, 'root');
define(MYSQL_PASSWORD, '');

$data = array(
    'login' => 'shipilov.alexei2011',
    'passwd' => '123QWE#',
    );

// Поменять заголовки. Не понял, по какому принципу, потому что работает только 1 заголовок.
// $headers = array(
//     'Accept-Encoding' => 'Accept-Encoding: gzip, deflate'
// );


// Бывает, что кодировку нужно менять
// header('Content-Type: text/html; charset=windows-1251');

// Настраиваем соединение
$c = Curl::app()
        ->headers(1) // отображение заголовков в запросе
        // ->set_headers($headers) // добавить/изменить заголовки
        ->post($data) // отправляем данные методом POST
        // ->set_user_agent() // указывается при надобности. если пусто, то агент рандомный
        ->ssl(1) // включаем ssl
        ->follow(1) // поддержка редиректов
        ->set_cookie('parser/cookies/cookie') // сохраняем куки для сохранения авторизации
        ;

// Конфиг
$c->config_save('cfg/1.cfg');

$data = $c->request('https://passport.yandex.ru/passport?mode=auth');

echo $data['html'];