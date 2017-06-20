<?php
# ДЕБАГЕР ОШИБОК v.2.0
# Coded by ShadoW (с) 2013
defined('_CORE_') or die('Доступ запрещен');

class Errors extends Exception
{
	# ТИПЫ ОШИБОК
	const ERROR    = 'ERROR';
	const DEBUG    = 'DEBUG';
	const INFO     = 'INFO';
	const CRITICAL = 'CRITICAL';
	const STRACE   = 'STRACE';
	const ALERT    = 'ALERT';

	# ФАТАЛЬНЫЕ ОШИБКИ
	public static $_shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);

	# ЧИТАБЕЛЬНЫЙ ФОРМАТ ОЩИБОК
	public static $_php_errors = array(
		E_ERROR              => 'Фатальная ошибка',
		E_USER_ERROR         => 'Пользовательская ошибка',
		E_PARSE              => 'Ошибка синтаксического анализа',
		E_WARNING            => 'Предупреждение',
		E_USER_WARNING       => 'Пользовательское предупреждение',
		E_STRICT             => 'Строгий',
		E_NOTICE             => 'Уведомление',
		E_RECOVERABLE_ERROR  => 'Ошибка восстановления',
	);

	# ОБРАБОТЧИК ИСКЛЮЧЕНИЙ
	public static function exception_handler(Exception $e)
	{
		# Очищаем буфер
		ob_get_level() and ob_clean();

		# Пробуем сгенерировать ошибку
		try
		{
			# Получаем отладочную информацию
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			# Читабельный формат ошибки
			if (isset(self::$_php_errors[$code])) $code = self::$_php_errors[$code];

			# Текстовая версия исключения
			$text_error = self::exception_text($e);

			# Логирование
			if (Core::$_log_errors === true) ErrorLog::add('[ '.date('d.m.Y H:i:s').' ] [ Клиент: '.$_SERVER['REMOTE_ADDR'].' ] '.self::ERROR, $text_error);

			# AJAX
			if (Core::isAjax()) exit($text_error);

			# Путь до ошибки
			$trace = $e->getTrace();
			if (version_compare(PHP_VERSION, '5.3', '<'))
			{
				for ($i=count($trace) - 1; $i > 0; --$i)
				{
					if (isset($trace[$i - 1]['args']))
					{
						# Позиционирование аргумента
						$trace[$i]['args'] = $trace[$i - 1]['args'];

						# Удаление агрументов
						unset($trace[$i - 1]['args']);
					}
				}
			}
			# Заголовок
			if (!headers_sent()) header('Content-Type: text/html; charset=utf-8', true, 500);

			# Выводим дебагер
			ob_start();
				include(_ABS_VIEWS_.'error'._EXT_);
			exit(ob_get_clean());
		}
		catch (Exception $e)
		{
			exit(self::exception_text($e)."\n");
		}
	}

	# КОНВЕРТАЦИЯ ОБЫЧНЫХ ОШИБОК В ИСКЛЮЧЕНИЯ
	public static function error_handler($code, $error, $file=NULL, $line=NULL)
	{
		if (error_reporting() & $code) throw new ErrorException($error, $code, 0, $file, $line);
		return true;
	}

	# ТЕКСТОВОЕ ИСКЛЮЧЕНИЕ
	public static function exception_text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [line: %d ]', get_class($e), $e->getCode(), strip_tags($e->getMessage()), $e->getFile(), $e->getLine());
	}

	# ПОЛУЧИТЬ ЛИНИИ ОЩИБКИ В ФАЙЛЕ
	public static function debug_source($file, $line_number, $padding=5)
	{
		if (!is_readable($file)) return false;

		# Открываем файл
		$line = 0;
		$file = fopen($file, 'r');

		# Диапозон кода
		$range = array('start'=>($line_number - $padding), 'end'=>($line_number+$padding));
		$format = '% '.strlen($range['end']).'d';
		$source = array();
		while ($row = fgets($file))
		{
			# Конец диапозона
			if (++$line > $range['end']) break;

			# Начало диапозона
			if ($line >= $range['start'])
			{
				$row = '<span class="number">'.sprintf($format, $line).'</span> '.htmlspecialchars($row, ENT_NOQUOTES);

				# Указанная строка
				if ($line === $line_number) $row = '<span class="line highlight">'.$row.'</span>';
				else $row = '<span class="line">'.$row.'</span>';

				$source[] = $row;
			}
		}
		fclose($file);

		return '<pre class="source"><code>'.join("\n", $source).'</code></pre>';
	}
}