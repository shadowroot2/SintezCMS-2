<?php
# AJAX методы модуля ПОЛЬЗОВАТЕЛИ САЙТА v.2.0
# Coded by ShadoW (c) 2014
class cnt_Ajax extends Controller
{
	private $_container		= 10;
	private $_class			= 10;
	private $_name_field	= 164;

	# ТИПЫ ДЕЙСТВИЙ
	private $_actions = array('search', 'switch', 'remove');

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) exit;

		# Русский язык по умолячанию
		Helpers::lang_sw('ru');
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
				# АВТОПОИСК
				case 'search':
					$answer = array();
					if ($this->act_index() && isset($_GET['term']) && ($search = Helpers::escape($_GET['term'])) && (mb_strlen($search) >= 3))
					{
						$answer = (array)Model::$_db->select('objects as o', "LEFT JOIN `class_".$this->_class."` as c ON c.object_id=o.id WHERE o.mother='".$this->_container."' AND o.class_id='".$this->_class."' AND (o.name LIKE '%$search%' OR c.f_".$this->_name_field." LIKE '%$search%' OR c.f_170='$search') ORDER BY o.id DESC", 'o.name');
					}
					exit(Helpers::JSON($answer));
				break;

				# ВКЛЮЧЕНИЕ ВЫКЛЮЧЕНИЕ
				case 'switch':
					if (
						$this->act_index() && isset($this->_params[1]) && ($user_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						($user = Model::getObject($user_id, false)) && ($user['mother'] == $this->_container) && ($user['class_id'] == $this->_class) && ($user = $user->asArray())
					)
					{
						# Простая смена статуса
						if (Model::editObject(array('id'=>$user_id, 'active'=>($user['active'] == 1 ? 0 : 1))))
						{
							Logger::add(($user['active'] == 1 ? 5 : 4), 'Пользователя сайта', $user['name'], $user['id'], $user['class_id']);
							$answer = array('status'=>'ok', 'active'=>($user['active'] == 1 ? 0 : 1));
						}
						else $answer['msg'] = 'Невозможно изменить статус';
					}
					else $answer['msg'] = 'Пользователь не найден';
					exit(Helpers::JSON($answer));
				break;

				# УДАЛЕНИЕ
				case 'remove':
					if (
						$this->act_index() && isset($this->_params[1]) &&
						($user_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						($user = Model::getObject($user_id, false)) && ($user['mother'] == $this->_container) && ($user['class_id'] == $this->_class)
					)
					{
						# Удаляем
						if (Model::deleteObject($user_id))
						{
							Logger::add(3, 'Пользователя сайта', $user['name'], '',  $user['class_id']);
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно удалить пользователя, попробуйте позже';
					}
					else $answer['msg'] = 'Пользователь не найден';
					exit(Helpers::JSON($answer));
				break;

				default :
					Helpers::redirect(_CMS_);
				break;
			}
		}
		else Helpers::redirect(_CMS_);
	}
}