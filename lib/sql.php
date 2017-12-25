<?php

class SQL
{
	private static $instance;
	private $db;

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


	public function insert($table, $object)
	{
		$columns = array();

		foreach ($object as $key => $value) {

			$columns[] = $key;
			$masks[] = ":$key";

			if ($value === null) {
				$object[$key] = 'NULL';
			}
		}

		$columns_s = implode(',', $columns);
		$masks_s = implode(',', $masks);

		$query = "INSERT INTO $table ($columns_s) VALUES ($masks_s)";

		$q = $this->db->prepare($query);
		$q->execute($object);

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $this->db->lastInsertId();
	}

	public function update($table, $object, $where, $params = array())
	{
		$sets = array();

		foreach ($object as $key => $value) {

			$sets[] = "$key=:$key";

			if ($value === NULL) {
				$object[$key]='NULL';
			}
		 }

		$sets_s = implode(',',$sets);
		$query = "UPDATE $table SET $sets_s WHERE $where";

		$q = $this->db->prepare($query);
		$q->execute(array_merge($object, $params));

		if ($q->errorCode() != PDO::ERR_NONE) {
			$info = $q->errorInfo();
			die($info[2]);
		}

		return $q->rowCount();
	}


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