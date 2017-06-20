<?php
# CMS-CRON v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class Cron extends Singleton
{
	private static $_controllers;

	# ПОЛУЧЕНИЕ КОНТРОЛЛЕРОВ
	public static function getControllers()
	{
		try
		{
			# Уже есть данные
			if (count(self::$_controllers) > 0) return self::$_controllers;

			# Проверка папки контроллеров
			$cron_dir = _ABS_ROOT_.'cron';
			if (is_dir($cron_dir))
			{
				# Поиск контроллеров
				foreach(scandir($cron_dir) as $f)
				{
					if (in_array($f, array('.', '..'))) continue;
					elseif (preg_match('/^(.*)\.cron'._EXT_.'$/i', $f, $match)) self::$_controllers[] = $match[1];
				}

				# Найденые контроллеры
				if (count(self::$_controllers) > 0) return self::$_controllers;
				else throw new Exception('В директории <b>'.$cron_dir.'</b> нет ни одного cron-контроллера');
			}
			else throw new Exception('Директория <b>'.$cron_dir.'</b> не существует');
		}
		catch(Exception $e) { Errors::exception_handler($e); }

		return false;
	}

	# ПОЛУЧЕНИЕ ЗАДАЧИ
	public static function get($task_id)
	{
		if ($task_id = filter_var($task_id, FILTER_VALIDATE_INT)) return Model::$_db->select('md_cron', "WHERE `id`='$task_id' LIMIT 1");
		return false;
	}

	# ПОЛУЧЕНИЕ СПИСКА АКТИВНЫХ ЗАДАЧ
	public static function getActiveTasks()
	{
		return Model::$_db->select('md_cron', "WHERE `active`='1' AND `run`='0' AND `start_date`<='".time()."' AND (`last_start`='0' OR ((`last_start`+`interval`)<='".time()."' AND `interval`!='0')) ORDER BY `id` ASC", "`id`");
	}

	# ЗАПУСК ЗАДАЧИ
	public static function runTask($task_id)
	{
		if (($task_id = filter_var($task_id, FILTER_VALIDATE_INT)) && ($task = self::get($task_id)) && ($task['active'] == 1) && ($task['run'] == 0))
		{
			# Проверяем CRON-контроллер
			if (
				self::getControllers() &&
				in_array($task['controller'], self::$_controllers) && ($controller = $task['controller']) &&
				($controller_path = _ABS_ROOT_.'cron'._SEP_.$controller.'.cron'._EXT_) && is_readable($controller_path)
			)
			{
				# Подключаем CRON-контроллер
				require_once($controller_path);
				$class_name = 'cron_'.$controller;
				$controller = new $class_name();
				if (is_callable(array($controller, 'run')))
				{
					# Обновлеяем дату запуска и меняем статус
					Model::$_db->update('md_cron', array('run'=>1, 'last_start'=>time()), "WHERE `id`='$task_id' LIMIT 1");

					# Запускаем задачу
					return $controller->run();
				}
				else self::status($task_id, 0);
			}
			else self::status($task_id, 0);
		}

		return false;
	}

	# ЗАВАЕРШЕНИЕ ЗАДАЧИ
	public static function endTask($task_id)
	{
		if (($task_id = filter_var($task_id)) && ($task = self::get($task_id)) && ($task['active'] == 1) && ($task['run'] == 1))
		{
			return Model::$_db->update('md_cron', array('run'=>0), "WHERE `id`='$task_id' LIMIT 1");
		}

		return false;
	}


	# ДОБАВЛЕНИЕ ЗАДАЧИ
	public static function add($name, $controller, $start=false, $interval=0)
	{
		# Получаем контроллеры
		self::getControllers();

		# Проверяем контроллер
		if (in_array($controller, self::$_controllers))
		{
			$task = array(
				'name'			=> $name,
				'controller'	=> $controller,
				'start_date'	=> $start ? (mb_strlen($start) == 10 ? $start : strtotime($start)) : time(),
				'interval'		=> ($interval = filter_var($interval, FILTER_VALIDATE_INT)) ? ($interval * 60) : 0
			);

			# Добавляем
			if (Model::$_db->insert('md_cron', $task, false)) return true;
		}

		return false;
	}

	# РЕДАКТИРОВАНИЕ ЗАДАЧИ
	public static function edit($task_id, $name, $controller, $start=false, $interval=0)
	{
		if (($task_id = filter_var($task_id, FILTER_VALIDATE_INT)) && self::get($task_id))
		{
			# Получаем контроллеры
			self::getControllers();

			# Проверяем контроллер
			if (in_array($controller, self::$_controllers))
			{
				$task = array(
					'name'			=> $name,
					'controller'	=> $controller,
					'start_date'	=> $start ? (strlen($start) == 10 ? $start : strtotime($start)) : time(),
					'interval'		=> ($interval = filter_var($interval, FILTER_VALIDATE_INT)) ? ($interval * 60) : 0,
					'last_start'	=> 0,
				);

				# Сохраняем
				return Model::$_db->update('md_cron', $task, "WHERE `id`='$task_id'",  false);
			}
		}

		return false;
	}

	# ВКЛЮЧЕНИЕ / ВЫКЛЮЧЕНИЕ ЗАДАЧИ
	public static function status($task_id, $active=1)
	{
		if (($task_id = filter_var($task_id, FILTER_VALIDATE_INT)) && in_array($active, array(0,1)))
		{
			return Model::$_db->update('md_cron', array('active'=>$active), "WHERE `id`='$task_id' LIMIT 1");
		}
		return false;
	}

	# УДАЛЕНИЕ ЗАДАЧИ
	public static function remove($task_id)
	{
		if ($task_id = filter_var($task_id, FILTER_VALIDATE_INT))
		{
			return Model::$_db->delete('md_cron', "WHERE `id`='$task_id'");
		}
		return false;
	}
}