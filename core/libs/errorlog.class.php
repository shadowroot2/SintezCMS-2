<?php
# ЛОГГЕР ОШИБОК v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class ErrorLog extends Singleton
{
	# ДОБАВЛЕНИЕ ОШИБКИ В ФАЙЛ
	public static function add($error, $text)
	{
		try
		{
			if (!file_put_contents(_ABS_LOG_, $error.' - '.$text._NL_, FILE_APPEND)) throw new Exception('Лог-файл ошибок <b>'._ABS_LOG_.'</b> не существует или защищен от записи');
		}
		catch(Exception $e)
		{
			Errors::exception_handler($e);
		}
	}

	# ОЧИСТКА ФАЙЛА ОШИБОК
	public static function cleen()
	{
		try
		{
			if (!file_put_contents(_ABS_LOG_, '')) throw new Exception('Лог-файл ошибок <b>'._ABS_LOG_.'</b> не существует или защищен от записи');
		}
		catch(Exception $e)
		{
			Errors::exception_handler($e);
		}
	}
}