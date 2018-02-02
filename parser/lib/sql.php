<?php

class SQL
{
	private static $instance; // экземпляр класса SQL
	private $db; // экземпляр класса PDO

	public static function app()
	{
		if (self::$instance == null) {
			self::$instance = new SQL();
		}

		return self::$instance;
	}

	private function __construct()
	{
		setlocale(LC_ALL, 'ru_RU.UTF8');
		$this->db = new PDO('mysql:host=' . MYSQL_SERVER . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
		$this->db->exec('SET NAMES UTF8');
		$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}

    /**
     * Вытягивает данные
     * 
     * @param string $query
     * Строка запроса
     * 
     * @param array $params
     * Массив с параметрами, если запрос динамический
     */
	public function select($query, $params = array())
	{
		$q = $this->db->prepare($query);
		$q->execute($params);

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $q->fetchAll();
	}

    /**
     * Кладет данные
     * 
     * @param string $table
     * Название таблицы
     * 
     * @param array $arr
     * Массив с данными
     */
	public function insert($table, $arr)
	{
		$columns = array();

		foreach ($arr as $key => $value) {

			$columns[] = $key;
			$masks[] = ":$key";

			if ($value === null) {
				$arr[$key] = 'NULL';
			}
		}

		$columns_s = implode(',', $columns);
		$masks_s = implode(',', $masks);

		$query = "INSERT INTO $table ($columns_s) VALUES ($masks_s)";

		$q = $this->db->prepare($query);
		$q->execute($arr);

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $this->db->lastInsertId();
	}

    /**
     * Обновляет данные
     * 
     * @param string $table
     * Название таблицы
     * 
     * @param array $arr
     * Массив с данными
     * 
     * @param string $where
     * Условие
     * 
     * @param array $params
     * Массив с параметрами, если запрос динамический
     */
	public function update($table, $arr, $where, $params = array())
	{
		$sets = array();

		foreach ($arr as $key => $value) {

			$sets[] = "$key=:$value";

			if ($value === NULL) {
				$arr[$key]='NULL';
			}
		 }

		$sets_s = implode(',',$sets);
		$query = "UPDATE $table SET $sets_s WHERE $where";

		$q = $this->db->prepare($query);
		$q->execute(array_merge($arr, $params));

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $q->rowCount();
	}

    /**
     * Удаляет данные
     * 
     * @param string $table
     * Название таблицы
     * 
     * @param string $where
     * Условие
     * 
     * @param array $params
     * Массив с параметрами, если запрос динамический
     */
	public function delete($table, $where, $params = array())
	{
		$query = "DELETE FROM $table WHERE $where";
		$q = $this->db->prepare($query);
		$q->execute($params);

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $q->rowCount();
	}
}