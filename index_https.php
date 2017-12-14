<?php

	include_once 'lib/curl.php';

	$c = curl::app('https://en.wikipedia.org')
					->headers(1)
					->ssl(0);

	$data = $c->request('wiki/S%C3%A3o_Lu%C3%ADs');
	var_dump($data);