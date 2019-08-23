<?php
class Db{
	private static $join;
	private static $commandDatabase = array (
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		PDO::ATTR_EMULATE_PREPARES => false
	);

	public static function connect ($host, $user, $password, $database) {
		if (!isset (self::$join)) {
			self::$join = @new PDO(
				"mysql:host=$host;dbname=$database;charset=utf8",
				$user,
				$password,
				self::$commandDatabase
			);
			self::$join->exec ("set names utf8");
		}
	}

	public static function loadOne ($sql, $values = array (), $numberKey = false) {
		$answer = self::$join->prepare ($sql);
		$answer->execute ($values);
		if (!$numberKey) {
			return $answer->fetch (PDO::FETCH_ASSOC);
		} else {
			return $answer->fetch (PDO::FETCH_NUM);
		}
	}

	public static function command ($sql, $values = array()) {
		$answer = self::$join->prepare ($sql);
		return $answer->execute ($values);
	}

	public static function loadAll ($sql, $values = array(), $numberKey = false) {
		$answer = self::$join->prepare ($sql);
		$answer->execute ($values);
		if (!$numberKey) {
			return $answer->fetchALL (PDO::FETCH_ASSOC);
		} else {
			return $answer->fetchALL (PDO::FETCH_NUM);
		}
	}

	public static function loadAlone ($sql, $values = array()) {
		$answer = self::$join->prepare ($sql);
		$answer->execute ($values);
		return $answer->fetch (PDO::FETCH_NUM)[0];
	}

	public static function add ($table, $values = array()) {
		return self::command (
			"INSERT INTO `$table` (`" .
				implode('`, `', array_keys($values)) .
				"`) VALUES (" .
					str_repeat('?,', (count($values) > 0 ? count($values)-1 : 0)) .
					"?)"
					, array_values ($values));
				}
				// TODO: pokud vlozim prazdne pole tak chyba ??
				public static function addAll ($table, $values = array ()) {
					try {
						foreach ($values as $value) {
							self::add ($table, $value);
						}
					} catch (PDOException $ex) {
						throw new PDOException ($ex->getMessage());
					}
				}

				public static function edit (
					$table,
					$values = array(),
					$conditions,
					$values2 = array()
				) {
					return self::command (
						"UPDATE `$table` SET `" .
						implode('` =?, `', array_keys($values)) .
						"` =? " .
						$conditions
						, array_merge (array_values ($values), $values2));
					}

					public static function insertId () {
						return self::$join->lastInsertId ();
					}

					public static function addId ($lastTable, $lastIdName) {
						$answer = self::$join->prepare ("SELECT `$lastIdName` FROM `$lastTable` ORDER BY `$lastIdName` DESC");
						$answer->execute ();
						return $answer->fetch (PDO::FETCH_NUM)[0];
					}
				}
