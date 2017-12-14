<?php
	include_once 'lib/curl.php';

	$c = curl::app('http://yknow.ru')
					->headers(1)
					->set_cookie($_SERVER['DOCUMENT_ROOT'] . '/cookies/1.txt')
                    ;

	$data = $c->request('clients/office');
	var_dump($data);