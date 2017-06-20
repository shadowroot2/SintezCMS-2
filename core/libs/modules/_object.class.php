<?php
# ОБЪЕКТ В ВИДЕ МАССИВА v.3.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class _Object implements ArrayAccess
{
	private $_object = false;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($object)
	{
		if (is_array($object)) $this->_object = $object;
		else return false;
	}

	# ПРОВЕРИТЬ НАЛИЧИЕ КЛЮЧА
	public function offsetExists($key)
	{
		return isset($this->_object[$key]);
	}

	# ПОЛУЧИТЬ КЛЮЧ
	public function offsetGet($key)
	{
		if ($this->offsetExists($key)) return $this->_object[$key];
		return false;
	}

	# ЗАДАТЬ КЛЮЧ
	public function offsetSet($key, $value)
	{
		$this->_object[$key] = $value;
		return true;
	}

	# УДАЛИТЬ КЛЮЧ
	public function offsetUnset($key)
	{
		unset($this->_object[$key]);
	}


	# ПОЛУЧЕНИЕ ПОЛЯ
	public function field($field_name)
	{
		return $this->offsetGet($field_name);
	}

	# КОЛИЧЕСТВО ПОЛЕЙ
	public function fieldsCount()
	{
		return (is_array($this->_object) ? count($this->_object) : 0);
	}

	# ОСНОВНЫЕ ПОЛЯ
	public function mainFields()
	{
		if (is_array($this->_object))
		{
			$main = array();
			foreach($this->_object as $k=>$v)
			{
				$main[$k] = $v;
				if ($k == 'sort') break;
			}
			return $main;
		}

		return false;
	}

	# МАССИВ
	public function asArray()
	{
		return $this->_object;
	}
}