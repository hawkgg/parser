<?php

	include_once 'lib/curl.php';

	$c = curl::app('https://ntschool.ru')
					->headers(1)
                    ;



	$data = $c->request('courses');
	var_dump($data);