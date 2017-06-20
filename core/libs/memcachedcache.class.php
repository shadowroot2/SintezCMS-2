<?php
# MEMCACHED КЭШИРОВАНИЕ v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class MemcachedCache extends Singleton
{
	private static $_init		= false;
	private static $_memcache	= null;

	# ИНИЦИАЛИЗАЦИЯ
	public static function _init()
	{
		if (self::$_init) return;

		self::$_init = true;
		self::$_memcache = new Memcache;
		self::$_memcache->connect(MEMCACHED_HOST, MEMCACHED_PORT) or die('Can not connect to Memcached server.');
		self::$_memcache->setCompressThreshold(20000, 0.2);
	}

	# СОХРАНИТЬ КЭШ
	public static function save($name, $data, $time=false)
	{
		if (!$result = self::$_memcache->replace($name, $data)) $result = self::$_memcache->set($name, $data, false, intval($time));
		return $result;
	}

	# ЗАГРУЗИТЬ КЭШ
	public static function load($name)
	{
		return self::$_memcache->get($name);
	}

	# УДАЛИТЬ КЭШ
	public static function delete($name)
	{
		return self::$_memcache->delete($name);
	}

	# ЗАКРЫТЬ СОЕДИНЕНИЕ
	public static function close()
	{
		self::$_memcache->close();
	}

	# ОЧИСТИТЬ ВЕСЬ КЭШ
	public static function clear()
	{
		self::$_memcache->flush();
	}

	# СТАТИСТИКА КЭША
	public static function stat()
	{
		return self::$_memcache->getStats();
	}

	# ВЕРСИЯ
	public static function version()
	{
		return self::$_memcache->getVersion();
	}
}