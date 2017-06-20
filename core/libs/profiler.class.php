<?php
# ПРОФАЙЛЕР v.2.0
# Coded by ShadoW (с) 2013
defined('_CORE_') or die('Доступ запрещен');

class Profiler extends Singleton
{
	protected static $_tokens = array();

	# СТАРТ
	public static function start()
	{
		$count = 0;
		++$count;
		$token = 'поток::'.$count.'::';

		self::$_tokens[$token] = array
		(
			'name'  => (string) $token,
			'start_time'=> microtime(true),
			'start_mem'	=> memory_get_usage(),
			'stop_time'	=> false,
			'stop_mem'	=> false,
		);

		return $token;
	}

	# СТОП
	public static function stop($token)
	{
		self::$_tokens[$token]['stop_time']	= microtime(true);
		self::$_tokens[$token]['stop_mem'] 	= memory_get_usage();

		return self::$_tokens[$token];
	}

	# УДАЛЕНИЕ
	public static function delete($token)
	{
		unset(self::$_tokens[$token]);
	}

	# ИТОГО
	public static function total()
	{
		return array
		(
			'name'		=> 'total',
			'start_time'=> _CORE_START_TIME_,
			'start_mem'	=> _CORE_START_MEMORY_,
			'stop_time'	=> microtime(true),
			'stop_mem'	=> memory_get_usage(true)
		);
	}
}