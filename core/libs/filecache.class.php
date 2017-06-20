<?php
# ФАЙЛОВЫЙ-КЭШЕР v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class FileCache extends Singleton
{
	private static $_version = '2.0';

	# СОХРАНИТЬ КЭШ
	public static function save($name, $data, $time=false)
	{
		try
		{
			$path = self::getCachePath($name);

			if (!file_put_contents($path, serialize($data), LOCK_EX)) throw new Exception('Невозможно записать кэш <b>'.$name.'</b> по адресу <i>'.$path.'</i>');
			chmod($path, 0777);
			touch($path, time()+intval($time));
		}
		catch(Exception $e)
		{
			Errors::exception_handler($e);
		}

		return true;
	}

	# ЗАГРУЗИТЬ КЭШ
	public static function load($name, $ignore_time=false)
	{
		$path = self::getCachePath($name);
		if (file_exists($path))
		{
			if ($ignore_time || (filemtime($path) >= time()))
			{
				try
				{
					if ($data = unserialize(file_get_contents($path))) return $data;
					else throw new Exception('Невозможно прочитать кэш <b>'.$name.'</b> из файла <i>'.$path.'</i>');
				}
				catch(Exception $e)
				{
					Errors::exception_handler($e);
				}
			}
			else self::delete($name);
		}

		return false;
	}

	# УДАЛИТЬ КЭШ
	public static function delete($name)
	{
		try
		{
			$path = self::getCachePath($name);
			if (!unlink($path)) throw new Exception('Невозможно удалить кэш <b>'.$name.'</b> путь: <i>'.$path.'</i>');
		}
		catch(Exception $e)
		{
			Errors::exception_handler($e);
		}
	}


	# ПУТЬ К КЭШУ
	private static function getCachePath($name)
	{
		return _ABS_CACHE_.'data'._SEP_.self::nameCache($name).'.cache';
	}

	# ИМЯ КЭША
	private static function nameCache($name)
	{
		return md5($name);
	}

	# ВЕРСИЯ
	private static function version()
	{
		return self::$_version;
	}
}