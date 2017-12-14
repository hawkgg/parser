<?php

include_once 'lib/curl.php';

$data = array(
    'login' => '89268744229',
    'password' => '',
    );

$c = curl::app('https://vk.com/')
        ->headers(1)
        ->follow(1)
        ->post($data)
        ->set_cookie($_SERVER['DOCUMENT_ROOT'] . '/2.txt');

$data = $c->request('');
$data = $c->post(false)->request('https://vk.com/');

var_dump($data);

