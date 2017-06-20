<?php
# МySQL CONNECTOR v.2.0
# Coded by ShadoW (c) 2013
class DB
{
	public $_connector_name = 'MySQL';

	private $_DB_HOST;
	private $_DB_BASE;
	private $_DB_USER;
	private $_DB_PASS;

	private $_link;

	public $_debug;
	public $_count;

	function __construct($host=DB_HOST, $db=DB_BASE, $user=DB_USER, $pass=DB_PASS)
	{
		$this->_DB_HOST	= $host;
		$this->_DB_BASE	= $db;
		$this->_DB_USER	= $user;
		$this->_DB_PASS	= $pass;

		$this->_link 	= NULL;
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
		if (!is_numeric($str)) $str = mysql_real_escape_string(htmlspecialchars($str));
		return $str;
	}

	# ПОДЛЮЧЕНИЕ
	private function connect()
	{
		if (is_resource($this->_link) && mysql_ping($this->_link)) return true;

		try
		{
			if (is_resource($this->_link = @mysql_connect($this->_DB_HOST, $this->_DB_USER, $this->_DB_PASS)))
			{
				if (@mysql_select_db($this->_DB_BASE, $this->_link))
				{
					mysql_query("SET NAMES 'utf8'", $this->_link);
					return true;
				}
				else throw new Exception('MySQL '.mysql_errno().': Невозможно выбрать базу данных <b>'.$this->_DB_BASE.'</b>');
			}
			else throw new Exception('MySQL '.mysql_errno().': Невозможно подключиться к базе данных <b>'.$this->_DB_HOST.'</b> c логином <b>'.$this->_DB_USER.'</b> и паролем <b>'.$this->_DB_PASS.'</b>');
		}
		catch(Extention $e) { Errors::exception_handler($e); }

		return false;
	}

	# ОТКЛЮЧЕНИЕ
	private function disconnect()
	{
		if(is_resource($this->_link)) mysql_close($this->_link);
		$this->_link = NULL;

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

				if ($result = @mysql_query($query, $this->_link)) return $result;
				else throw new Exception('SQL '.mysql_errno($this->_link).': "<b>'.$query.'</b>" '.mysql_error($this->_link));
			}
			catch(Exception $e) { Errors::exception_handler($e); }
		}

		return false;
	}

	# СЧЕТЧИК
	public function count($table, $where='', $what='*')
	{
		$count = $this->query("SELECT COUNT(".$what.") FROM ".(preg_match('# as #i', $table) ? $table : "`$table`").(!empty($where) ? ' '.$where : ''));
		if (preg_match('#GROUP BY#i', $where)) return mysql_num_rows($count);
		else return mysql_result($count, 0);
	}

	# ВЫБОРКА
	public function select($table, $where='', $what='*')
	{
		if (
			($result = $this->query("SELECT ".$what." FROM ".(preg_match('# as #i', $table) ? $table : "`$table`")." ".$where)) &&
			($num_rows = mysql_num_rows($result))
		)
		{
			$out = array();

			if (preg_match("/limit\s+1$/i", $where)) $out = mysql_fetch_array($result, MYSQL_ASSOC);
			else if (!strstr($what, '*') && (mysql_num_fields($result) == 1))
			{
				for($i=0; $i<$num_rows; $i++) $out[] = mysql_result($result, $i);
			}
			else
			{
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) $out[] = $row;
			}

			mysql_free_result($result);

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
			$rows[]= "`".($prepare ? $this->prepare($key) : $key)."`";
			$vals[]= "'".($prepare ? $this->prepare($value) : $value)."'";
		}

		if ($this->query("INSERT INTO `$table` (".join(", ", $rows).") VALUES (".join(", ", $vals).")"))
			return mysql_insert_id($this->_link);

		return false;
	}

	# ОБНОВЛЕНИЕ
	public function update($table, $update, $where='', $prepare=true)
	{
		$parsed_update = array();
		foreach($update as $key=>$value) $parsed_update[]= "`".($prepare ? $this->prepare($key) : $key)."`='".($prepare ? $this->prepare($value) : $value)."'";

		if ($this->query("UPDATE `$table` SET ".join(", ", $parsed_update)." ".$where))
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
		if ($this->connect()) return mysql_get_server_info();
	}

	# СТАТУС БД
	public function stat()
	{
		if ($this->connect()) return mysql_stat($this->_link);
	}
}