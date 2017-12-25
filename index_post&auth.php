<?php
	include_once 'lib/curl.php';

	$post = array(
		'email' => 'dmitrylavr@gmail.com',
		'password' => 'XkJT8a7',
		'remember' => 'on'
	);

	$c = curl::app('http://vk.com')
					->headers(1)
					->post($post)
					->set_cookie('cookies/1.txt');

	$data = $c->request('/');
	$data = $c->request('/');

	var_dump($data);