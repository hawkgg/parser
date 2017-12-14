<?php

	include_once 'lib/curl.php';

	$headers = array(
		'Accept' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language' => 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	);


	$c = curl::app('http://ntschool.ru')
					->headers(1)
					->set_headers($headers)
					->set_header('Accept-Encoding', 'Accept-Encoding: gzip, deflate')
					->set_user_agent()
					->follow(1)
					;


	$data = $c->request('courses');
	var_dump($data);