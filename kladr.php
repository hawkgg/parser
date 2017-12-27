<?php

include_once 'lib/curl.php';
include_once 'lib/simple_html_dom.php';
include_once 'lib/parser.php';
include_once 'lib/sql.php';

ini_set('max_execution_time', 0);

define(MYSQL_SERVER, 'localhost');
define(MYSQL_DB, 'kladr');
define(MYSQL_USER, 'root');
define(MYSQL_PASSWORD, '');

// $data = array(
//     'login' => '',
//     'password' => '',
//     );

// header('Content-Type: text/html; charset=windows-1251');

$c = Curl::app()
        ->headers(1)
        // ->post($data)
        ->ssl(1)
        // ->set_cookie('parser/cookies/2')
        ;

$db = SQL::app();

$data = $c->request('https://kladr-rf.ru/');
// $data = $c->post(false)->request('https://vk.com/');

$html = str_get_html($data['html']);
$cols = $html->find('.row-fluid .span4');

// ID для построения связей
$id_region = 1;
$id_city = 1;
$id_street = 1;

foreach ($cols as $col) {
    $i = 0;
    $region = [];
    $nums = parse_nums($col);

    $links = $col->find('p a');

    foreach($links as $link){
        if (strpos($link, '#c0c0c0')) {
            continue;
        }

        // Заносим регион в базу
        $region['num_region'] = $nums[$i++];
        $region['name'] = $link->plaintext;
        $db->insert('regions', $region);


        // Если регион является городом - также заносим его в таблицу городов
        if (mb_stripos($link->plaintext, 'город', 0, 'UTF-8')) {
            add_city($db, $link->plaintext, $id_region);
        }

        // Заходим в регион/город
        $data = $c->request($link->href);
        $data = str_get_html($data['html']);
        $region_html = Parser::app($data);

        // Ищем районы в городе/регионе
        if ($region_html->moveto('<h4>Районы:</h4>') !== -1) {
            $inside_id_city = $id_city;
            $cols = str_get_html($region_html->subtag('<div class="row-fluid"', 'div'))->find('.span4');

            foreach ($cols as $col) {
                $links = $col->find('p a');

                foreach ($links as $link) {
                    if (strpos($link, '#c0c0c0')) {
                        continue;
                    }

                    // Заходим в район
                    $data = $c->request($link->href);
                    $data = str_get_html($data['html']);
                    $district_html = Parser::app($data);


                    // Ищем города в районе
                    if ($district_html->moveto('<h4>Города:</h4>') !== -1) {
                        $cols = str_get_html($district_html->subtag('<div class="row-fluid"', 'div'))->find('.span4');

                        foreach ($cols as $col) {
                            $links = $col->find('p a');

                            foreach($links as $link){
                                if (strpos($link, '#c0c0c0')) {
                                    continue;
                                }

                                // Добавляем город
                                add_city($db, $link->plaintext, $id_region);

                                // Заходим в город
                                $data = $c->request($link->href);
                                $data = str_get_html($data['html']);
                                $city_html = Parser::app($data);

                                // Парсим улицы
                                if ($city_html->moveto('<a name="street">Улицы:</a>') !== -1) {
                                    parse_streets($db, $city_html, $id_city);
                                }

                                $id_city++;
                                curl_unset($data);
                            }
                        }
                    }

                    // Парсим улицы в районе
                    if ($district_html->moveto('<a name="street">Улицы:</a>') !== -1) {
                        parse_streets($db, $district_html, $inside_id_city);
                    }

                    $inside_id_city++;

                    curl_unset($data);
                }
            }
        }

        // Ищем города в регионе/городе
        if ($region_html->moveto('<h4>Города:</h4>') !== -1) {
            $id_parent = $id_city;
            $cols = str_get_html($region_html->subtag('<div class="row-fluid"', 'div'))->find('.span4');

            foreach ($cols as $col) {
                $links = $col->find('p a');

                foreach ($links as $link) {
                    if (strpos($link, '#c0c0c0')) {
                        continue;
                    }

                    add_city($db, $link->plaintext, $id_region, $id_parent);

                    // Заходим в город
                    $data = $c->request($link->href);
                    $data = str_get_html($data['html']);
                    $city_html = Parser::app($data);

                    // Ищем улицы в городе
                    if ($city_html->moveto('<a name="street">Улицы:</a>') !== -1) {
                        parse_streets($db, $city_html, $id_city);
                    }

                    $id_city++;
                    curl_unset($data);
                }
            }
        }

        // Ищем улицы в городе/регионе
        if ($region_html->moveto('<a name="street">Улицы:</a>') !== -1) {
            parse_streets($db, $region_html, $id_region);
        }

        // Если данный регион является городом - плюсуем счетчик
        if (mb_stripos($link->plaintext, 'город', 0, 'UTF-8')) {
            $id_city++;
        }
        
        $id_region++;
        curl_unset($data);
    }
}


curl_unset($data);


// Получить номера регионов
function parse_nums($col)
{
    $nums = [];
    $spans = $col->find('p span');

    foreach ($spans as $span) {
        if (!strpos($span, 'badge-info')) {
            continue;
        }

        $nums[] = $span->plaintext;
    }

    return $nums;
}

// Занести город в базу
function add_city($db, $name, $id_region, $id_parent = 0)
{
    $city = [];
    $city['name'] = $name;
    $city['id_region'] = $id_region;
    $city['id_parent'] = $id_parent;

    return $db->insert('cities', $city);
}



// Ищем улицы
function parse_streets($db, $parse_data, $id_city)
{
    $cols = str_get_html($parse_data->subtag('<div class="row-fluid"', 'div'))->find('.span4');

    foreach ($cols as $col) {
        // Првоеряем пагинацию
        if ($paglinks = $col->find('.nav.nav-list.alert.alert-info')) {

            foreach ($paglinks as $paglink) {
                // Заходим на отдельную страницу с улицами
                $data = $c->request($paglink);
                $data = str_get_html($data['html']);
                $streets_html = str_get_html(Parser::app($data)->subtag('<div class="row-fluid"', 'div'));

                // Ищем улицы на странице улиц
                if ($streets_html->moveto('<a name="street">Улицы:</a>') !== -1) {
                    $cols = str_get_html($streets_html->subtag('<div class="row-fluid"', 'div'))->find('.span4');

                    foreach ($cols as $col) {
                        $links = $col->find('p a');

                        foreach($links as $link){
                            if (strpos($link, '#c0c0c0')) {
                                continue;
                            }

                            add_street($db, $link->plaintext, $id_city);
                            $id_street++;
                        }
                    }
                }
                curl_unset($data);
            }
        } else {
            $links = $col->find('p a');

            foreach($links as $link){
                if (strpos($link, '#c0c0c0')) {
                    continue;
                }

                add_street($db, $link->plaintext, $id_city);
                $id_street++;
            }
        }
    }
}




// Занести улицу в базу
function add_street($db, $name, $id_city)
{
    $street = [];
    $street['name'] = $name;
    $street['id_city'] = $id_city;

    return $db->insert('streets', $street);
}

function curl_unset($data)
{
    if (isset($data)) {
        $data->clear();
        unset($data);
    }

    return true;
}