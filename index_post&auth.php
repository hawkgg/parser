<?php
	include_once 'lib/curl.php';

	$post = array(
		'email' => 'dmitrylavr@gmail.com',
		'password' => 'XkJT8a7',
		'remember' => 'on'
	);

	$c = curl::app('http://yknow.ru')
					->headers(1)
					->post(http_build_query($post))
					->set_cookie($_SERVER['DOCUMENT_ROOT'] . '/cookies/1.txt');

	$data = $c->request('clients/login');
	$data = $c->request('clients/office');

	var_dump($data);