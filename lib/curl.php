<?php

class curl{
	private $ch;
	private $host;
	private $options = [];

	public static function app($host){
		return new self($host);
	}

	private function __construct($host){
		$this->ch = curl_init();
		$this->host = $host;
		$this->set(CURLOPT_RETURNTRANSFER, true);
	}

	public function __destruct(){
		curl_close($this->ch);
	}

    public function get($option) {
        return $this->options[$option];
    }

	public function set($name, $value){
		$this->options[$name] = $value;
		curl_setopt($this->ch, $name, $value);
		return $this;
	}

	public function ssl($act){
		$this->set(CURLOPT_SSL_VERIFYPEER, $act);
		$this->set(CURLOPT_SSL_VERIFYHOST, $act);
		return $this;
	}

	public function headers($act){
		$this->set(CURLOPT_HEADER, $act);
		return $this;
	}

    public function set_header($name, $value){
        $this->options[CURLOPT_HTTPHEADER][$name] = $value;

        $this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);
        return $this;
    }

	public function set_headers($headers){
        foreach($headers as $key => $header){
            $this->options[CURLOPT_HTTPHEADER][$key] = $header;
        }

		$this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);
		return $this;
	}

    public function clear_headers(){
        $this->set(CURLOPT_HTTPHEADER, array());
        return $this;
    }

    public function set_user_agent($agent = null){
    	if(!$agent){
	        $agents = [
	            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0',
	            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0'
	        ];
	        $this->options[CURLOPT_HTTPHEADER]['User-Agent'] = $agents[rand(0, count($agents)-1)];
	        $this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);
    	} else {
	        $this->options[CURLOPT_HTTPHEADER]['User-Agent'] = $agent;
	        $this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);
    	}
        return $this;
    }

	public function set_cookie($dir){
		$this->set(CURLOPT_COOKIEJAR, $dir);
		$this->set(CURLOPT_COOKIEFILE, $dir);
		return $this;
	}

	public function post($data){
		if($data === false){
			$this->set(CURLOPT_POST, false);
			return $this;
		}

        $this->set(CURLOPT_POST, true);
		$this->set(CURLOPT_POSTFIELDS, http_build_query($data));
		return $this;
	}

	public function follow($act){
		$this->set(CURLOPT_FOLLOWLOCATION, $act);
		return $this;
	}

    public function referer($url) {
        $this->set(CURLOPT_REFERER, $url);
        return $this;
    }

	public function request($url){
		$this->set(CURLOPT_URL, $this->make_url($url));
		$data = curl_exec($this->ch);
		return $this->process_result($data);
	}

	public function config_load($dir){
		$values = json_decode(file_get_contents($dir));
		foreach($values as $name => $value){
			$this->set($name, $value);
		}
		return $this;
	}

	public function config_save($dir){
		$data = json_encode($this->options);
		file_put_contents($dir, $data);
		return $this;
	}

	private function make_url($url){
		if($url[0] != '/')
			$url = '/' . $url;

		return $this->host . $url;
	}

	private function process_result($data){
		var_dump(count($this->options[CURLOPT_HTTPHEADER]));
		if(!isset($this->options[CURLOPT_HEADER])) {
			return array(
				'headers' => array(),
				'html' => $data
			);
		}

		/* Разделяем ответ на headers_part и body_part */
		$info = curl_getinfo($this->ch);
		$headers_part = trim(substr($data, 0, $info['header_size'])); // trim чтобы обрезать перенос в конце
		$body_part = substr($data, $info['header_size']);

		/* Приравниваем символ переноса строки */
		$headers_part = str_replace("\r\n", "\n", $headers_part); // если винда
		$headers = str_replace("\r", "\n", $headers_part); // если мак

		/* Берем последний header */
		$headers = explode("\n\n", $headers);
		$header_last = end($headers);

		$redirects = array();
		foreach ($headers as $value) {
			$start = stripos($value, 'Location:');

			if (!$start) continue;

			$start += strlen('Location:') + 1;
			$end = stripos(substr($value, $start), "\n") ? $end : strlen($value);
			$redirects[] = substr($value, $start, $end);
		}

		/* Освобождаем переменную headers */
		$lines = explode("\n", $header_last);
		$headers = array();

		/* Парсим заголовки */
		$headers['start'] = $lines[0];

		for($i = 1; $i < count($lines); $i++){
			$del_pos = strpos($lines[$i], ':');
			$name = substr($lines[$i], 0, $del_pos);
			$value = substr($lines[$i], $del_pos + 2);
			$headers[$name] = $value;
		}

		return array(
			'headers' => $headers,
			'html' => $body_part
		);
	}
}