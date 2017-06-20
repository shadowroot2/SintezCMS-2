<?php
# MySQLi КОННЕКТОР v.1.0
# Coded by ShadoW (c) 2013
class DB
{
	public $_connector_name = 'MySQLi';

	private $_DB_HOST;
	private $_DB_BASE;
	private $_DB_USER;
	private $_DB_PASS;

	private $_mysqli;

	public $_debug;
	public $_count;

	function __construct($host=DB_HOST, $db=DB_BASE, $user=DB_USER, $pass=DB_PASS)
	{
		$this->_DB_HOST	= $host;
		$this->_DB_BASE	= $db;
		$this->_DB_USER	= $user;
		$this->_DB_PASS	= $pass;

		$this->_mysqli 	= NULL;
		$this->_debug 	= true;
		$this->_count	= 0;
	}

	function __destruct()
	{
		if ($this->_debug) echo 'Итого: <b>'.$this->_count.'</b> обращений к базе<hr />'._NL_;
		$this->disconnect();
	}

	# ОБРАБОТКА ДАННЫХ ПЕРЕД ЗАПРОСОМ
	public function prepare($str)
	{
		if (!is_numeric($str)) $str = $this->_mysqli->real_escape_string(htmlspecialchars($str));
		return $str;
	}

	# ПОДЛЮЧЕНИЕ
	private function connect()
	{
		if (is_object($this->_mysqli) && $this->_mysqli->ping()) return true;

		try
		{
			$this->_mysqli = @new mysqli($this->_DB_HOST, $this->_DB_USER, $this->_DB_PASS, $this->_DB_BASE);

			if (!$this->_mysqli->connect_errno)
			{
				$this->_mysqli->set_charset('utf8');
				return true;
			}
			else throw new Exception('MYSQLi '.$this->_mysqli->connect_errno.': Невозможно подключиться к базе данных <b>'.$this->_DB_HOST.':'.$this->_DB_BASE.'</b> c логином <b>'.$this->_DB_USER.'</b> и паролем <b>'.$this->_DB_PASS.'</b>');
		}
		catch(Extention $e) { Errors::exception_handler($e); }

		return false;
	}

	# ОТКЛЮЧЕНИЕ
	private function disconnect()
	{
		if(is_object($this->_mysqli)) $this->_mysqli->close();
		$this->_mysqli = NULL;
		return true;
	}

	# ЗАПРОС
	public function query($query)
	{
		if($this->connect())
		{
			try
			{
				$this->_count++;
				if ($this->_debug) echo '<b>'.$this->_count.'</b> - '.$query."<br />\n";

				if (($result = $this->_mysqli->query($query)) && !$this->_mysqli->errno) return $result;
				else throw new Exception('SQLi '.$this->_mysqli->errno.': "<b>'.$query.'</b>" '.$this->_mysqli->error);
			}
			catch(Exception $e) { Errors::exception_handler($e); }
		}

		return false;
	}

	# СЧЕТЧИК
	public function count($table, $where='', $what='*')
	{
		if (is_object($count = $this->query("SELECT COUNT($what) FROM ".(preg_match('# as #i', $table) ? $table : "`$table`").($where != '' ? ' '.$where : ''))))
		{
			if (preg_match('#GROUP BY#i', $where)) return $count->num_rows;
			else
			{
				$row = $count->fetch_row();
				return $row[0];
			}
		}

		return 0;
	}

	# ВЫБОРКА
	public function select($table, $where='', $what='*')
	{
		if (
			is_object($result = $this->query("SELECT ".$what." FROM ".(preg_match('# as #i', $table) ? $table : "`$table`")." ".$where)) &&
			$result->num_rows
		)
		{
			$out = array();

			if (preg_match("/limit\s+1$/i", $where))
				$out = $result->fetch_assoc();
			else if (!strstr($what, '*') && ($result->field_count == 1))
			{
				while($row = $result->fetch_array(MYSQLI_NUM)) $out[] = $row[0];
			}
			else $out = $result->fetch_all(MYSQLI_ASSOC);

			$result->free();

			return $out;
		}

		return false;
	}

	# ВСТАВКА
	public function insert($table, $insert, $prepare=true)
	{
		$rows = $vals = array();
		foreach($insert as $key=>$value)
		{
			$rows[]= "`".($prepare ? $this->prepare($key)	: $key)."`";
			$vals[]= "'".($prepare ? $this->prepare($value)	: $value)."'";
		}

		if ($this->query("INSERT INTO `$table` (".join(", ", $rows).") VALUES (".join(", ", $vals).")"))
			return $this->_mysqli->insert_id;

		return false;
	}

	# ОБНОВЛЕНИЕ
	public function update($table, $update, $where='', $prepare=true)
	{
		$parsed_update = array();
		foreach($update as $key=>$value) $parsed_update[]= "`".($prepare ? $this->prepare($key) : $key)."`='".($prepare ? $this->prepare($value) : $value)."'";

		if ($this->query("UPDATE `$table` SET ".join(', ', $parsed_update)." ".$where))
			return true;

		return false;
	}

	# УДАЛЕНИЕ
	public function delete($table, $where)
	{
		if ($this->query("DELETE FROM ".(strstr($table, ' as ') ? $table : "`$table`")." ".$where))
			return true;

		return false;
	}

	# ИНФОРМАЦИЯ О СЕРВЕРЕ
	public function info()
	{
		if ($this->connect()) return $this->_mysqli->server_info;
	}

	# СТАТУС БД
	public function stat()
	{
		if ($this->connect()) return $this->_mysqli->stat();
	}
}