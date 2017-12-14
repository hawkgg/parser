<?php

	include_once 'lib/curl.php';

	$c = curl::app('http://ntschool.ru')
					->headers(1);

	$data = $c->request('home');
	var_dump($data);