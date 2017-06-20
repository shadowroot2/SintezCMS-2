<?php
# AJAX методы модуля ЯЗЫКИ
# Coded by ShadoW (c) 2011
class cnt_Ajax extends Controller
{
	# ТИПЫ ДЕЙСТВИЙ
	private $_actions = array('add', 'edit', 'set', 'switch', 'remove');

	public function init()
	{
		if (Core::$_data['authed'] == false) exit;
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax())
		{
			exit(header('Location: '._CMS_));
		}

		return true;
	}

	# ДЕЙСТВИЯ
	public function act_a()
	{
		if (!empty($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ДОБАВЛЕНИЕ
				case 'add':
					if (!empty($_POST['name']) && !empty($_POST['code']) && ($code = helpers::escape($_POST['code'])) && (mb_strlen($code) == 2))
					{
						if (!in_array($code, Core::$_langs))
						{
							# Добавляем язык
							if (Model::$_db->insert('md_languages', array('code'=>$code, 'name'=>$_POST['name'])))
							{
								Logger::add(1, 'Язык', $_POST['name']);
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно добавить язык';
						}
						else $answer['msg'] = 'Язык с таким кодом уже есть в системе';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
					exit(Helpers::JSON($answer));
				break;

				# РЕДАКТИРОВАНИЕ
				case 'edit':
					if (
						!empty($_POST['id']) && is_numeric($lang_id = $_POST['id'])
						&& ($lang = Model::$_db->select('md_languages', "WHERE `id`='$lang_id' LIMIT 1", "`id`"))
						&& !empty($_POST['name'])
						&& !empty($_POST['code'])
						&& ($code = helpers::escape($_POST['code']))
						&& (mb_strlen($code) == 2)
					)
					{
						# Проверяем код языка
						if (!Model::$_db->select('md_languages', "WHERE `id`!='$lang_id' AND `code`='$code'", "`id`"))
						{
							# Обновляем
							if (Model::$_db->update('md_languages', array('name'=>$_POST['name'], 'code'=>$code), "WHERE `id`='$lang_id' LIMIT 1"))
							{
								Logger::add(2, 'Язык', $_POST['name']);
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно обновить язык';
						}
						else $answer['msg'] = 'Язык с таким кодом уже существует в системе';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
					exit(Helpers::JSON($answer));
				break;

				# ВЫБОР ЯЗЫКА ПО УМОЛЧАНИЮ
				case 'set':
					if ($this->act_index() && !empty($this->_params[1]) && is_numeric($lang_id = $this->_params[1]) && ($lang = Model::$_db->select('md_languages', "WHERE `id`='$lang_id' LIMIT 1", "`name`, `set`, `active`")))
					{
						if ($lang['active'] == 1)
						{
							# Снимаем сет у текущего языка
							Model::$_db->update('md_languages', array('set'=>0), "WHERE `set`='1'");

							# Ставим сет у выбранного языка
							Model::$_db->update('md_languages', array('set'=>1), "WHERE `id`='$lang_id' LIMIT 1");

							Logger::add(6, 'Язык', $lang['name']);
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Язык отключен';
					}
					else $answer['msg'] = 'Язык не найден';
					exit(Helpers::JSON($answer));
				break;

				# ВКЛЮЧЕНИЕ/ВЫКЛЮЧЕНИЕ ЯЗЫКА ПО УМОЛЧАНИЮ
				case 'switch':
					if ($this->act_index() && !empty($this->_params[1]) && is_numeric($lang_id = $this->_params[1]) && ($lang = Model::$_db->select('md_languages', "WHERE `id`='$lang_id' LIMIT 1", "`name`, `set`, `active`")))
					{
						if (($lang['set'] == 1) && ($lang['active'] == 1)) $answer['msg'] = 'Язык выбран по умолчанию и не может быть отключен';
						else
						{
							# Меняем статус
							if (Model::$_db->update('md_languages', array('active'=>($lang['active'] == 1 ? 0 : 1)), "WHERE `id`='$lang_id' LIMIT 1"))
							{
								Logger::add(($lang['active'] == 1 ? 5 : 4), 'Язык', $lang['name']);
								$answer = array(
									'status'		=> 'ok',
									'active'		=> ($lang['active'] == 1 ? 'off' : 'on'),
									'active_text'	=> ($lang['active'] == 1 ? 'Включить' : 'Отключить'),
								);
							}
							else $answer['msg'] = 'Невозможно сменить статус';
						}
					}
					else $answer['msg'] = 'Язык не найден';
					exit(Helpers::JSON($answer));
				break;

				# УДАЛЕНИЕ
				case 'remove':
					if ($this->act_index() && !empty($this->_params[1]) && is_numeric($lang_id = $this->_params[1]) && ($lang = Model::$_db->select('md_languages', "WHERE `id`='$lang_id' LIMIT 1", "`name`,`set`")))
					{
						if ($lang['set'] == 0)
						{
							# Удаляем язык
							if (Model::$_db->delete('md_languages', "WHERE `id`='$lang_id' LIMIT 1"))
							{
								Logger::add(3, 'Язык', $lang['name']);
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно удалить язык';
						}
						else $answer['msg'] = 'Язык выбран по умолчанию и не может быть удален';
					}
					else $answer['msg'] = 'Язык не найден';
					exit(Helpers::JSON($answer));
				break;

				default :
					header('Location: '._CMS_);
				break;
			}
		}
		else header('Location: '._CMS_);
	}
}