<?php
# ШАБЛОН В ВИДЕ МАССИВА v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class _Template implements ArrayAccess
{
	private $_template = false;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($template)
	{
		if (is_array($template)) $this->_template = $template;
		else return false;
	}

	# ПРОВЕРИТЬ НАЛИЧИЕ КЛЮЧА
	public function offsetExists($key)
	{
		return isset($this->_template[$key]);
	}

	# ПОЛУЧИТЬ КЛЮЧ
	public function offsetGet($key)
	{
		if ($this->offsetExists($key)) return $this->_template[$key];
		return false;
	}

	# ЗАДАТЬ КЛЮЧ
	public function offsetSet($key, $value)
	{
		$this->_template[$key] = $value;
		return TRUE;
	}

	# УДАЛИТЬ КЛЮЧ
	public function offsetUnset($key)
	{
		unset($this->_template[$key]);
	}

	# ПОЛУЧЕНИЕ ИМЕНИ
	public function name()
	{
		return $this->_template['name'];
	}

	# ПОЛУЧЕНИЕ ВСЕГО ШАБЛОНА
	public function asArray()
	{
		return $this->_template;
	}
}