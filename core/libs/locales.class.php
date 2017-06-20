<?php
# ЛОКАЛИ v.2.0
# Coded by ShadoW (c) 2014
defined('_CORE_') or die('Доступ запрещен');
class Locales extends Singleton
{
	# ПОДГРУЗКА ЛОКАЛИ
	public static function load($locale)
	{
		try
		{
			if (
				is_file($locale_file = _ABS_PUBLIC_.'locale/'.Core::$_lang.'/'.mb_strtolower($locale).'.json') &&
				is_readable($locale_file) && ($contents = file_get_contents($locale_file))
			)
			{
				if (is_array($locale = @json_decode($contents, true)) && count($locale)) return $locale;
				else throw new Exception('В локали <b>'.$locale.'</b> нет данных');
			}
			else throw new Exception('Файл локали <b>'.$locale.'</b> не найден в библиотеке <b>'._ABS_PUBLIC_.'locale/'.Core::$_lang.'/</b>');

			return false;
		}
		catch(Exception $e) { Errors::exception_handler($e); }
	}
}