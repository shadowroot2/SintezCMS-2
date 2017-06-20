<?php
# РЕАЛИЗАЦИЯ ПАТТЕРНА ОДИНОЧКА v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

abstract class Singleton
{
	protected static $_instance;

	protected function __construct() {}

	final private function __clone() {}

	# ПОЛУЧЕНИЕ ИНСТАНЦИИ
	final public static function getInstance()
	{
		$class = get_called_class();

		if ($class != __CLASS__)
		{
			if (!isset(self::$_instance[$class])) self::$_instance[$class] = new $class;

			return self::$_instance[$class];
		}
	}
}