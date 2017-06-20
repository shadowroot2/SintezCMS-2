<?php
# ВЫПОЛНЕНИЕ CRON-ЗАДАЧ v.2.0
# Coded by ShadoW (c) 2013.
class cnt_Cron extends Controller
{
	private $_log_file = false;
	private $_task_id  = false;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка ключа
		if (!isset($_GET['key']) || ($_GET['key'] != md5('cron_'.$_SERVER['HTTP_HOST']))) exit('Access Denied!');

		# Лог файл
		$log_file = _ABS_ROOT_.'cron'._SEP_.'cron.log';
		if (is_file($log_file) && is_writable($log_file)) Core::$_data['cron_log_file'] = $this->_log_file = $log_file;
		else exit('Can\'t access to log file '.$log_file);
		unset($log_file);

		# ID задачи (для отладки)
		if (isset($_GET['task']) && ($task_id = filter_input(INPUT_GET, 'task', FILTER_VALIDATE_INT))) $this->_task_id = $task_id;
		
		# Записываем старт
		$this->log_write('Cron started');
	}

	# ЗАПУСК
	public function act_index()
	{
		# Неумерайко
		ignore_user_abort(1);
		set_time_limit(0);
	
		# Запуск одной задачи (для отладки)
		if (!empty($this->_task_id))
		{
			echo 'Run task #'.$this->_task_id.'<br />';
			if (!Cron::runTask($this->_task_id))
			{
				$this->log_write('Can\'t perform task ID#'.$this->_task_id);
				exit('ERROR');
			}
			Cron::endTask($this->_task_id);
		}

		# Запускаем активные задачи по очереди
		elseif ($tasks = Cron::getActiveTasks())
		{
			foreach($tasks as $task_id)
			{
				if (!Cron::runTask($task_id)) $this->log_write('Cant execute task ID#'.$task_id);
				Cron::endTask($task_id);
			}
		}
		exit('OK');
	}

	# ЗАПИСЬ В ЛОГ
	private function log_write($text)
	{
		return file_put_contents($this->_log_file, date('d.m.Y H:i:s').' - '.$text."\n", FILE_APPEND);
	}
}