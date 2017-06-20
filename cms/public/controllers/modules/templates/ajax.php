<?php
# AJAX методы модуля ШАБЛОНЫ v.3.0
# Coded by ShadoW (c) 2013
class cnt_Ajax extends Controller
{
	# ТИПЫ ДЕЙСТВИЙ
	private $_actions = array(
		'add',
		'edit',
		'remove',
		'getlink'
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (!Core::$_data['authed']) exit;

		# Неумерайко
		ignore_user_abort(1);
		set_time_limit(0);
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
		if (isset($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ДОБАВЛЕНИЕ
				case 'add':
					if (
						$this->act_index() &&
						isset($_POST['additional']) && is_numeric($additional = $_POST['additional']) && in_array($additional, array(0, 1)) &&
						isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
						isset($_POST['desc']) &&
						isset($_POST['fields']) && is_array($fields = $_POST['fields']) && count($_POST['fields']))
					{
						# Добавляем шаблон
						if ($class_id = Model::addClass(array(
								'additional' => $additional,
								'name'		 => $name,
								'desc'  	 => Helpers::escape($_POST['desc'])
							),
							$fields
						))
						{
							# Логирование
							if (Core::$_log_actions) Logger::add(1, 'Шаблон', $name, 0, $class_id);

							# ОК
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно создать шаблон, возможно шаблон с таким названием уже существует';
					}
					else $answer['msg'] = 'Проверьте правильность данных';
				break;

				# РЕДАКТИРОВАНИЕ
				case 'edit':
					if (
						$this->act_index() &&
						isset($_POST['id']) && ($class_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)) && ($class = Model::getClass($class_id)) &&
						isset($_POST['additional']) && is_numeric($additional = $_POST['additional']) && in_array($additional, array(0, 1)) &&
						isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
						isset($_POST['desc']) &&
						isset($_POST['fields']) && is_array($fields = $_POST['fields']) && count($fields)
					)
					{
						# Шаблон не является системным
						if ($class['protected'] == 0)
						{
							# Сохраняем шаблон
							if (Model::editClass(array(
									'id'		 => $class_id,
									'additional' => $additional,
									'name'		 => $name,
									'desc'  	 => Helpers::escape($_POST['desc'])
								),
								$fields
							))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(2, 'Шаблон', $name, 0, $class_id);

								# ОК
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно сохранить шаблон, шаблон с таким названием уже существует';
						}
						else $answer['msg'] = 'Шаблон встроен и не может быть изменеен';
					}
					else $answer['msg'] = 'Проверьте правильность данных';
				break;

				# УДАЛЕНИЕ
				case 'remove':
					if ($this->act_index() && isset($this->_params[1]) && ($class_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($class = Model::getClass($class_id)))
					{
						# Удаляем шаблон
						if ($class['protected'] == 0)
						{
							if (Model::removeClass($class_id))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(3, 'Шаблон', $class['name'], 0, $class['id']);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно удалить шаблон';
						}
						else $answer['msg'] = 'Шаблон встроен и не может быть удален';
					}
					else $answer['msg'] = 'Шаблон не найден';
				break;

				# ПРОВЕРКА СВЯЗИ
				case 'getlink':
					if ($this->act_index() && isset($this->_params[1]) && ($object_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($obj = Model::getObject($object_id)))
					{
						# OK
						$answer = array(
							'status' =>'ok',
							'id'	 => $obj['id'],
							'name'	 => $obj['name'].' ('.$obj['inside'].')'
						);
					}
					else $answer['msg'] = 'Объект не найден';
				break;

				# ДЕЙСТВИЕ ПО УМОЛЧАНИЮ
				default :
					Helpers::redirect(_CMS_);
				break;
			}
			exit(Helpers::JSON($answer));
		}
		else Helpers::redirect(_CMS_);
	}
}