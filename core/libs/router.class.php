<?php
# МАРШРУТИЗАТОР v.3.1
# Coded by ShadoW (C) 2015
defined('_CORE_') or die('Доступ запрещен');

class Router extends Singleton
{
	public static $_path		= _ABS_CONTROLLERS_;
	public static $_route		= '';
	public static $_params		= array();

	private static $_defcall	= false;
	private static $_replaces	= false;

	private static $_exceptions	= array('image', 'cron');

	# ПОИСК КОНТРОЛЛЕРА
	private static function findController(&$controller_file, &$controller, &$action)
	{
		# Разбиваем запрос на части
		$route_parts = explode('/', self::$_route);
		foreach ($route_parts as $part)
		{
			# Директория
			if (is_dir(self::$_path.$part))
			{
				self::$_path .= $part._SEP_;
				array_shift($route_parts);
				continue;
			}

			# Файл
			elseif (is_file(self::$_path.$part._EXT_))
			{
				$controller = $part;
				array_shift($route_parts);
				break;
			}
		}

		# Файл контроллера
		if (empty($controller)) $controller = 'index';
		$controller_file = self::$_path.$controller._EXT_;

		# Действие
		$action = array_shift($route_parts);
		if (empty($action)) $action = 'index';

		# Аргументы
		self::$_params = $route_parts;
	}

	# ЗАПУСК КОНТРОЛЛЕРА
	public static function runController($route=false)
	{
		# Маршрут
		if (!empty($route)) self::$_route = trim($route, '/\\');
		else self::$_route = trim(((isset($_GET['route']) && ($_GET['route'] != '')) ? $_GET['route'] : 'index'), '/\\');

		# Язык в маршруте
		preg_match('#^(('.join('|', array_keys(Core::$_langs)).')/?)#i', self::$_route, $lang_match);
		if (sizeof($lang_match) == 3)
		{
			Core::$_lang	= mb_strtolower($lang_match[2]);
			self::$_route	= preg_replace('#^(('.Core::$_lang.')/?)#i', '', self::$_route);
		}

		# Запускаем глобальный контроллер
		if (!preg_match('#^(('.join('|', self::$_exceptions).')/?)#i', self::$_route) && (self::$_defcall == false) && is_readable(_ABS_CONTROLLERS_.'_'._EXT_))
		{
			require_once(_ABS_CONTROLLERS_.'_'._EXT_);
			$_default = new cnt_Default();
			$_default->run();
			self::$_defcall = true;
		}

		# Ищем контроллер
		self::findController($controller_file, $controller, $action);

		# Проверяем контроллер
		if (
			(
				((self::$_route != '') && (self::$_route != '_') && ($controller_file != _ABS_CONTROLLERS_.'index'._EXT_))		# обычный контроллер
				||
				((self::$_route == 'index') && ($controller_file == _ABS_CONTROLLERS_.'index'._EXT_))	# индексный контроллер
			) &&
			is_readable($controller_file)
		)
		{
			# Подключаем контроллер
			require_once($controller_file);
			$class = 'cnt_'.$controller;
			$controller = new $class();
			$controller->_params = (array)self::$_params;

			# Инициализация контроллера
			$controller->init();

			# Запуск действия
			$action = 'act_'.$action;
			if (is_callable(array($controller, $action))) return $controller->$action();
		}

		return self::not_found();
	}

	# КОНТРОЛЛЕР НЕ НАЙДЕН ИЩЕМ В ЗАМЕНАХ
	private static function not_found()
	{
		if (Core::$_hurl && !self::$_replaces && is_array(self::$_replaces = Model::$_db->select('md_routes', "WHERE `active`='1' ORDER BY `sort` ASC", "`route`, `replace`")))
		{
			foreach(self::$_replaces as $r)
			{
				if (preg_match('#^'.$r['route'].'$#i', self::$_route))
				{
					return self::runController(preg_replace('#^'.$r['route'].'$#i', $r['replace'], self::$_route));
					break;
				}
			}
		}

		return self::error_page();
	}

	# Страница 404
	private static function error_page()
	{
		if (is_readable(_ABS_CONTROLLERS_.'404'._EXT_))
		{
			require_once(_ABS_CONTROLLERS_.'404'._EXT_);
			$controller = new cnt_404();
			$controller->init();
			return $controller->act_index();
		}
		else
		{
			header('HTTP/1.0 404 Not Found');
			exit('404 not found');
		}
	}
}