<?php

	include_once 'lib/curl.php';

	$c = curl::app('http://ntschool.ru')
					->headers(1)
                    ->follow(1);


    $c->config_save('cfg/1');
    $c->config_load('cfg/1');
	$data = $c->request('courses');

    var_dump($data);