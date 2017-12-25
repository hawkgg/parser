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
$span4 = $html->find('.row-fluid .span4');

// ID для построения связей
$id_region = 1;
$id_city = 1;

foreach ($span4 as $value) {
    $region = [];

    // номера регионов
    $nums = [];
    $i = 0;
    $spans = $value->find('p span');
    foreach ($spans as $span) {
        if (!strpos($span, 'badge-info')) {
            continue;
        }
        $nums[] = $span->plaintext;
    }

    $links = $value->find('p a');

    foreach($links as $link){
        if (strpos($link, '#c0c0c0')) {
            continue;
        }

        $region['num_region'] = $nums[$i];
        $region['name'] = $link->plaintext;
        $db->insert('regions', $region);
        $i++;

        $data = $c->request($link->href);
        $data = str_get_html($data['html']);
        $p = Parser::app($data);

        if ($p->moveto('<h4>Районы:</h4>') !== -1) {
            $districts = str_get_html($p->subtag('<div class="row-fluid"', 'div'));
            $span4 = $districts->find('.span4');

            foreach ($span4 as $value) {
                $links = $value->find('p a');

                foreach ($links as $link) {
                    if (strpos($link, '#c0c0c0')) {
                        continue;
                    }

                    $data = $c->request($link->href);
                    $data = str_get_html($data['html']);
                    $p2 = Parser::app($data);

                    if ($p2->moveto('<h4>Города:</h4>') === -1) {
                        continue;
                    }

                    $cities = str_get_html($p2->subtag('<div class="row-fluid"', 'div'));
                    $span4 = $cities->find('.span4');

                    foreach ($span4 as $value) {
                        $links = $value->find('p a');

                        foreach($links as $link){
                            if (strpos($link, '#c0c0c0')) {
                                continue;
                            }

                            $cities = [];
                            $cities['name'] = $link->plaintext;
                            $cities['id_region'] = $id_region;
                            $db->insert('cities', $cities);
                            $id_city++;
                        }
                    }
                    $data->clear();
                    unset($data);
                }
            }
        }

        if ($p->moveto('<h4>Города:</h4>') !== -1) {

            $cities = str_get_html($p->subtag('<div class="row-fluid"', 'div'));
            $span4 = $cities->find('.span4');

            foreach ($span4 as $value) {
                $links = $value->find('p a');

                foreach ($links as $link) {
                    if (strpos($link, '#c0c0c0')) {
                        continue;
                    }
                    $cities = [];
                    $cities['name'] = $link->plaintext;
                    $cities['id_region'] = $id_region;
                    $db->insert('cities', $cities);
                    $id_city++;


                    $data = $c->request($link->href);
                    $data = str_get_html($data['html']);
                    $p2 = Parser::app($data);

                    if ($p2->moveto('<h4>Города:</h4>') === -1) {
                        continue;
                    }

                    $cities = str_get_html($p2->subtag('<div class="row-fluid"', 'div'));
                    $span4 = $cities->find('.span4');

                    foreach ($span4 as $value) {
                        $links = $value->find('p a');

                        foreach($links as $link){
                            if (strpos($link, '#c0c0c0')) {
                                continue;
                            }

                            $cities = [];
                            $cities['name'] = $link->plaintext;
                            $cities['id_region'] = $id_region;
                            $db->insert('cities', $cities);
                            $id_city++;
                        }
                    }
                    $data->clear();
                    unset($data);
                }
            }

        }
        $id_region++;
    }
}

