<?php
# ЯДРО MVC v.2.4
# Coded by ShadoW (c) 2013
if (!@require_once('cfg.php'))				 exit('Невозможно загрузить файл настроек <b>'.$_SERVER['DOCUMENT_ROOT'].'/core/cfg.php</b>');
if (!@require_once(_ABS_CORE_.'init'._EXT_)) exit('Невозможно загрузить файл инициализации <b>'._ABS_CORE_.'init.php</b>');

# КЛАСС ЯДРА
class Core
{
	# Назвеие ядра
	const CORE_NAME = 'CoreMVC';

	# Версия ядра
	const VERSION = '2.4';

	# Автор
	const AUTOR = 'Дмитрий Тригуб';

	# E-mail
	const EMAIL = 'dm.trigub@gmail.com';

	# Статичный класс
	private function __construct() {}

	# Ключ проверки инициализации ядра
	protected static $_init = false;

	# Дебаг
	public static $_debug = false;

	# Рабочая директория проекта
	public static $_work_dir = 'public';

	# Папка хранения загруженных файлов
	public static $_uploads_dir = false;

	# Индексный файл точки входа
	public static $_index_file = 'index.php';

	# Статус MagicQuotes
	protected static $_magic_quotes = false;

	# Вывод ошибок
	public static $_errors = false;

	# Запись ошибок
	public static $_log_errors = false;

	# Запись действий в CMS
	public static $_log_actions = false;

	# Кэширование
	public static $_caching = true;

	# Memcached кэширование
	public static $_memcached = false;

	# Время жизни кэша в секундах
	public static $_cache_life = 600;

	# Профилирование
	public static $_profiling = false;

	# Языки системы
	public static $_langs = false;

	# Язык по умолчанию
	public static $_lang = _LANG_;

	# ЧПУ
	public static $_hurl = true;

	# Фронтэнд
	public static $_frontend = false;


	# Шина данных
	public static $_data = array();

	# Кэш файлов
	public static $_files = array();

	# Статус изменения кэша файлов
	public static $_files_changed = false;

	# Системные модули
	protected static $_modules = array();

	# -----------------------------------------------------------------------------------------------------
	# ЗАПУСК ЯДРА
	public static function run($settings = array())
	{
		# Проверка повторной инициализации
		if (self::$_init) return;
		else self::$_init = true;

		# Дебаг
		if (isset($settings['debug']))			self::$_debug = (bool) $settings['debug'];

		# Профилирование
		if (isset($settings['profiling'])) 		self::$_profiling = (bool) $settings['profiling'];

		# Кэширование
		if (isset($settings['caching'])) 		self::$_caching = (bool) $settings['caching'];

		# Memcached кэширование
		if (isset($settings['memcached'])) 		self::$_memcached = (bool) $settings['memcached'];

		# Время жизни кэша
		if (isset($settings['cache_life']))		self::$_cache_life = (int) $settings['cache_life'];

		# Ошибки
		if (isset($settings['errors']))			self::$_errors = (bool) $settings['errors'];

		# Логирование ошибок
		if (isset($settings['log_errors']))		self::$_log_errors = (bool) $settings['log_errors'];

		# Логирование дейсвий в CMS
		if (isset($settings['log_actions']))	self::$_log_actions = (bool) $settings['log_actions'];

		# ЧПУ
		if (isset($settings['hurl']))			self::$_hurl = (bool) $settings['hurl'];

		# Фронтэнд
		if (isset($settings['frontend']))		self::$_frontend = (int) $settings['frontend'];

		# Рабочая директория
		if (isset($settings['work_dir']))		self::$_work_dir = trim($settings['work_dir'], '/');

		# Индексный файл
		if (isset($settings['index_file']))		self::$_index_file = trim($settings['index_file'], '/');

		# Папка загрузки файлов
		if (isset($settings['uploads_dir']))	self::$_uploads_dir = trim($settings['uploads_dir'], '/');

		# Настройки рабочей папки проекта
		define('_PUBLIC_', _ROOT_.self::$_work_dir.'/');
		define('_TPL_', _PUBLIC_.'templates/');
		define('_ABS_PUBLIC_', _ABS_ROOT_.str_replace(array('\\', '/'), _SEP_, self::$_work_dir)._SEP_);
		define('_ABS_CONTROLLERS_', _ABS_PUBLIC_.'controllers'._SEP_);
		define('_ABS_TPL_', _ABS_PUBLIC_.'templates'._SEP_);
		define('_ABS_CACHE_', _ABS_PUBLIC_.'cache'._SEP_);
		define('_ABS_LOG_', _ABS_PUBLIC_.'errors.log');

		# Папка загрузки файлов
		if (self::$_uploads_dir != false)
		{
			define('_UPLOADS_', _ROOT_.self::$_uploads_dir.'/');
			define('_ABS_UPLOADS_', _ABS_ROOT_.str_replace(array('\\', '/'), _SEP_, self::$_uploads_dir)._SEP_);
		}
		else
		{
			define('_UPLOADS_', _PUBLIC_.'uploads/');
			define('_ABS_UPLOADS_', _ABS_PUBLIC_.'uploads'._SEP_);
		}

		# Способ кэширования
		if (self::$_caching && self::$_memcached) MemcachedCache::_init();

		# Кэш найденых файлов
		if (self::$_caching) self::$_files = Cache::load('Core::$_files', true);

		# Callback выключения (отлавливает фатальные ошибки)
		register_shutdown_function(array('Core', 'shutdown_handler'));

		# Обработчик ошибок
		if (self::$_errors)
		{
			# Тип ошибки E_DEPRECATED только в PHP 5.3.0 и выше
			if (defined('E_DEPRECATED')) Errors::$_php_errors[E_DEPRECATED] = 'Deprecated';

			# Обработчик исключений
			set_exception_handler(array('Errors', 'exception_handler'));

			# Обработчик ошибок
			set_error_handler(array('Errors', 'error_handler'));

			ob_start();
		}

		# Чистка запросов
		self::$_magic_quotes = (bool)get_magic_quotes_gpc();
		$_GET    = self::request_clean($_GET);
		$_POST   = self::request_clean($_POST);
		$_COOKIE = self::request_clean($_COOKIE);

		# Инициализируем модель и представление
		Model::_init();
		View::_init();

		# Запускаем контроллер
		Router::runController();
	}

	# ФАБРИКА КЛАССОВ
	public static function auto_load($class)
	{
		try
		{
			if (is_string($path = self::find_file(_ABS_LIBS_, strtolower($class).'.class'._EXT_)))
			{
				require_once($path);
				return true;
			}
			else throw new Exception('Класс <b>'.$class.'</b> не найден в библиотеке <b>'._ABS_LIBS_.'</b>');

			return false;
		}
		catch(Exception $e) { Errors::exception_handler($e); }
	}

	# ЧИСТКА ЗАПРОСОВ
	public static function request_clean($value)
	{
		if (is_array($value) || is_object($value))
		{
			foreach ($value as $key=>$val) $value[$key] = self::request_clean($val);
		}
		elseif (is_string($value))
		{
			if (self::$_magic_quotes)			$value = stripslashes($value);
			if (strpos($value, "\r") != false)	$value = str_replace(array("\r\n", "\r"), "\n", $value);
		}

		return $value;
	}

	# ПОИСК ФАЙЛОВ С КЭШИРОВАНИЕМ В ПАМЯТИ
	public static function find_file($dir, $file='', $ext='')
	{
		# Путь
		$path = $dir.$file.$ext;

		# Хэш пути
		$path_hash = md5($path);

		# Обычный поиск файла
		if (!empty($file))
		{
			# Проверка в кэше
			if (isset(self::$_files[$path_hash]))
			{
				# Файл действительно есть?
				if (file_exists(self::$_files[$path_hash]))	return self::$_files[$path_hash];
				else
				{
					unset(self::$_files[$path_hash]);
					self::$_files_changed = true;
				}
			}

			# Обычная проверка наличия файла
			else if (file_exists($path))
			{
				self::$_files[$path_hash] = $path;
				if (!in_array($file, array('cache.class.php', 'filecache.class.php', 'singleton.class.php'))) self::$_files_changed = true;

				return $path;
			}

			# Ищем во вложенных папках
			else if (is_dir($dir))
			{
				foreach(scandir($dir) as $sdir)
				{
					if (in_array($sdir, array('.', '..'))) continue;
					if (is_dir($dir.$sdir) && ($sub_dir_path = $dir.$sdir._SEP_.$file.$ext) && file_exists($sub_dir_path))
					{
						self::$_files[$path_hash] = $sub_dir_path;
						self::$_files_changed = true;
						return $sub_dir_path;
					}
				}
			}
		}

		# Поиск по типу файла
		else if (!empty($ext))
		{
			$ext   = ".{$ext}";
			$found_files = array();
			foreach(preg_grep('/(\w+).'.$ext.'$/i', scandir($dir)) as $f) $found_files[] = $dir.$f;
			if (count($found_files) > 0) return $found_files;
		}

		return false;
	}

	# AJAX
	public static function isAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) return true;
		return false;
	}

	# ВЫКЛЮЧЕНИЕ
	public static function shutdown_handler()
	{
		# Кэширование найденых файлов
		if (self::$_caching && self::$_files_changed) Cache::save('Core::$_files', Core::$_files);

		# Профилирование
		if (self::$_profiling && !self::isAjax())
		{
			$benchmark = Profiler::total();
			echo '<div style="margin:20px; text-align:center;">Execution time: '.round(($benchmark['stop_time'] - $benchmark['start_time']), 3).' sec. Used memory: '.round((($benchmark['stop_mem'] - $benchmark['start_mem']) / 1024), 1).' Kb. DB requests: '.Model::$_db->_count.'</div>';
		}

		# Фатальные ошибки
		if (self::$_errors && ($error = error_get_last()) && in_array($error['type'], Errors::$_shutdown_errors))
		{
			Errors::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}
}