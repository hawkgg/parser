<?php

	include_once 'lib/curl.php';

	$c = curl::app()
				->headers(1)
				->ssl(1);

	$data = $c->request('https://en.wikipedia.org/wiki/S%C3%A3o_Lu%C3%ADs');
	var_dump($data);