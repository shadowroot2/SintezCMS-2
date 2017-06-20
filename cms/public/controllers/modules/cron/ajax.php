<?php
# AJAX МЕТОДЫ МОДУЛЯ CRON v.2.0
# Coded by ShadoW (c) 2013
class cnt_Ajax extends Controller
{
	private $_actions = array(
		'add',
		'edit',
		'switch',
		'remove'
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) exit;
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax()) Helpers::redirect(_CMS_);
		return true;
	}

	# ДЕЙСТВИЯ
	public function act_a()
	{
		if ($this->act_index() && isset($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ДОБАВЛЕНИЕ
				case 'add':
					if (
						isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && !empty($name) &&
						isset($_POST['controller']) && ($controller = Helpers::escape($_POST['controller'])) && !empty($controller) &&
						isset($_POST['start']) && ($start = Helpers::escape($_POST['start'])) && !empty($start) &&
						isset($_POST['interval'])
					)
					{
						# Добавляем задачу
						if (Cron::add($name, $controller, $start, intval($_POST['interval'])))
						{
							# Логируем
							if (Core::$_log_actions) Logger::add(1, 'Задачу', $_POST['name']);

							# OK
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно добавить задачу, попробуйте позже';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# РЕДАКТИРОВАНИЕ
				case 'edit':
					if (
						isset($_POST['id']) && ($task_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)) &&
						isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && !empty($name) &&
						isset($_POST['controller']) && ($controller = Helpers::escape($_POST['controller'])) && !empty($controller) &&
						isset($_POST['start']) && ($start = Helpers::escape($_POST['start'])) && !empty($start) &&
						isset($_POST['interval'])
					)
					{
						# Проверяем наличие задачи
						if ($task = Cron::get($task_id))
						{
							# Сохраняем задачу
							if (Cron::edit($task_id, $name, $controller, $start, intval($_POST['interval'])))
							{
								# Логируем
								if (Core::$_log_actions) Logger::add(2, 'Задачу', $_POST['name']);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно добавить задачу';
						}
						else  $answer['msg'] = 'Задача не найдена';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# ВКЛЮЧЕНИЕ/ВЫКЛЮЧЕНИЕ
				case 'switch':
					if (isset($this->_params[1]) && ($task_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($task = Cron::get($task_id)))
					{
						# Меняем активность
						Cron::status($task_id, (($task['active'] == 0) ? 1 : 0));

						# Логируем
						if (Core::$_log_actions) Logger::add(($task['active'] == 0 ? 4 : 5), 'Задачу', $task['name']);

						# OK
						$answer = array(
							'status'	  => 'ok',
							'active'	  => $task['active'] == 0 ? 'on' : 'off',
							'active_text' => $task['active'] == 0 ? 'Отключить' : 'Включить',
						);
					}
					else $answer['msg'] = 'Задача не найдена';
				break;

				# УДАЛЕНИЕ
				case 'remove':
					if (isset($this->_params[1]) && ($task_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($task = Cron::get($task_id)))
					{
						# Удаляем задачу
						if (Cron::remove($task_id))
						{
							# Логируем
							if (Core::$_log_actions) Logger::add(3, 'Задачу', $task['name']);

							# OK
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно удалить задачу';
					}
					else $answer['msg'] = 'Задача не найдена';
				break;

				default :
					$answer['msg'] = 'Действие не задано';
				break;
			}
			exit(Helpers::JSON($answer));
		}
		else Helpers::redirect(_CMS_);
	}
}