<?php
# AJAX методы модуля ПОЛЬЗОВАТЕЛИ v.2.0
# Coded by ShadoW (c) 2013
class cnt_Ajax extends Controller
{
	private $_actions = array(
		'gadd',
		'gedit',
		'gswitch',
		'gremove',
		'uadd',
		'uedit',
		'uremove',
		'uswitch'
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
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
		if (isset($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ДОБАВЛЕНИЕ ГРУППЫ
				case 'gadd':
					if (
						$this->act_index() &&
						isset($_POST['name']) &&
						($group_name = Helpers::escape($_POST['name'])) && ($group_name != '') &&
						isset($_POST['access']) && is_array($access_vals = $_POST['access']) && count($access_vals)
					)
					{
						# Проверяем имя группы
						if (!Model::$_db->select('md_user_groups', "WHERE `name`='$group_name' LIMIT 1", 'id'))
						{
							# Формируем доступ
							$access = array();
							foreach($access_vals as $a)
							{
								if (in_array($a['access'], array('r', 'rw')))
								{
									$access[$a['module']] = array(
										'module' => Helpers::escape($a['module']),
										'access' => $a['access'],
										'params' => $a['params'],
									);
								}
							}
							unset($access_vals);

							# Добавляем группу
							if (Model::$_db->query("INSERT INTO `md_user_groups` (`name`, `access`) VALUES ('$group_name', '".serialize($access)."')"))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(1, 'Группу пользователей', $group_name);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно добавить группу, внутренняя ошибка сервера';
						}
						else $answer['msg'] = 'Группа с таким названием уже существует';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# РЕДАКТИРОВАНИЕ ГРУППЫ
				case 'gedit':
					if (
						$this->act_index() &&
						isset($_POST['gid']) && ($group_id = filter_input(INPUT_POST, 'gid', FILTER_VALIDATE_INT)) &&
						($group = Model::$_db->select('md_user_groups', "WHERE `id`='$group_id' AND `protected`='0' LIMIT 1", 'id')) &&
						isset($_POST['name']) && ($group_name = Helpers::escape($_POST['name'])) && ($group_name != '') &&
						isset($_POST['access']) && is_array($access_vals = $_POST['access']) && count($access_vals)
					)
					{
						# Проверяем имя группы
						if (!Model::$_db->select('md_user_groups', "WHERE `name`='$group_name' AND `id`!='$group_id' LIMIT 1", 'id'))
						{
							# Формируем доступ
							$access = array();
							foreach($access_vals as $a)
							{
								if (in_array($a['access'], array('r', 'rw')))
								{
									$access[$a['module']] = array(
										'module' => Helpers::escape($a['module']),
										'access' => $a['access'],
										'params' => $a['params'],
									);
								}
							}
							unset($access_vals);

							# Сохраняем группу
							if (Model::$_db->query("UPDATE `md_user_groups` SET `name`='$group_name', `access`='".serialize($access)."' WHERE `id`='$group_id' LIMIT 1"))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(2, 'Группу пользователей', $group_name);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно сохранить группу, внутренняя ошибка сервера';
						}
						else $answer['msg'] = 'Группа с таким названием уже существует';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# ВКЛ/ВЫКЛ ГРУППЫ
				case 'gswitch':
					if ($this->act_index() && isset($this->_params[1]) && ($group_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($group = Model::$_db->select('md_user_groups', "WHERE `id`='$group_id' AND `protected`='0' LIMIT 1", "`name`, `active`")))
					{
						# Меняем статус
						Model::$_db->update('md_user_groups', array('active'=>($group['active'] == 1 ? 0 : 1)), "WHERE `id`='$group_id' LIMIT 1");

						# Логирование
						if (Core::$_log_actions) Logger::add(($group['active'] == 1 ? 5 : 4), 'Группу пользователей', $group['name']);

						# OK
						$answer = array(
							'status' => 'ok',
							'active' => $group['active'] == 1 ? 0 : 1
						);
					}
					else $answer['msg'] = 'Группа не найдена';
				break;

				# УДАЛЕНИЕ ГРУППЫ
				case 'gremove':
					if ($this->act_index() && isset($this->_params[1]) && ($group_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($group = Model::$_db->select('md_user_groups', "WHERE `id`='$group_id' AND `protected`='0' LIMIT 1", "`name`")))
					{
						# Удаляем пользователей группы
						Model::$_db->delete('md_users', "WHERE `gid`='$group_id'");

						# Удаляем группу
						Model::$_db->delete('md_user_groups', "WHERE `id`='$group_id' LIMIT 1");

						# Логируем
						if (Core::$_log_actions) Logger::add(3, 'Группу пользователей', $group['name']);

						# OK
						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Группа не найдена';

				break;


				# ДОБАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯ
				case 'uadd':
					if (
						$this->act_index() &&
						isset($_POST['gid']) && ($group_id = filter_input(INPUT_POST, 'gid', FILTER_VALIDATE_INT)) &&
						($group = Model::$_db->select('md_user_groups', "WHERE `id`='$group_id' LIMIT 1", 'id')) &&
						isset($_POST['name'])	&& ($user_name = Helpers::escape($_POST['name'])) && preg_match('#.{1,50}#', $user_name) &&
						isset($_POST['login'])	&& ($user_login = Helpers::escape($_POST['login'])) && preg_match('#^[a-z0-9_]{3,20}$#i', $user_login) &&
						isset($_POST['pass'])	&& ($user_pass = $_POST['pass']) && preg_match('#.{6,30}#', $user_pass)
					)
					{
						# Проверяем логин и имя
						if (!$check_user = Model::$_db->select('md_users', "WHERE `login`='$user_login' OR `name`='$user_name' LIMIT 1", '`login`, `name`'))
						{
							# Добавляем пользователя
							if ($user_id = Model::$_db->insert('md_users', array(
									'gid'		=> $group_id,
									'name'		=> $user_name,
									'login'		=> $user_login,
									'pass'		=> md5(_SALT_.$user_pass),
									'add_ts' 	=> time()
							))
							)
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(1, 'Пользователя', $user_name);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно добавить пользователя, попробуйте позже';
						}
						else if ($check_user['name'] == $user_name) $answer['msg'] = 'Имя уже занято';
						else $answer['msg'] = 'Логин уже занят';

					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# РЕДАКТИРОВАНИЕ ПОЛЬЗОВАТЕЛЯ
				case 'uedit':
					if (
						isset($_POST['uid']) && ($user_id = filter_input(INPUT_POST, 'uid', FILTER_VALIDATE_INT)) &&
						isset($_POST['gid']) && ($group_id = filter_input(INPUT_POST, 'gid', FILTER_VALIDATE_INT)) &&
						($user	= Model::$_db->select('md_users', "WHERE `id`='$user_id' AND `protected`='0' LIMIT 1", 'id')) &&
						($group	= Model::$_db->select('md_user_groups', "WHERE `id`='$group_id' LIMIT 1", 'id'))&&
						isset($_POST['name'])	&& ($user_name = Helpers::escape($_POST['name'])) && preg_match('#.{1,50}#', $user_name) &&
						isset($_POST['login'])	&& ($user_login = Helpers::escape($_POST['login'])) && preg_match('#^[a-z0-9]{3,16}$#i', $user_login) &&
						isset($_POST['pass'])
					)
					{
						# Проверяем логин и имя
						if (!$check_user = Model::$_db->select('md_users', "WHERE `id`!='$user_id' AND (`login`='$user_login' OR `name`='$user_name') LIMIT 1", '`login`, `name`'))
						{
							# Массив обновления
							$update = array(
								'gid'		=> $group_id,
								'name'		=> Helpers::escape($_POST['name']),
								'login'		=> $user_login,
							);

							# Смена пароля?
							if (preg_match('#.{6,30}#', $_POST['pass'])) $update['pass'] = md5(_SALT_.$_POST['pass']);

							# Сохраняем пользователя
							if (Model::$_db->update('md_users', $update, "WHERE `id`='$user_id' LIMIT 1"))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(2, 'Пользователя', $_POST['name']);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно сохранить пользователя, попробуйте позже';
						}
						else if ($check_user['name'] == $user_name) $answer['msg'] = 'Имя уже занято';
						else $answer['msg'] = 'Логин уже занят';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# ВКЛ/ВЫКЛ ПОЛЬЗОВАТЕЛЯ
				case 'uswitch':
					if ($this->act_index() && isset($this->_params[1]) && ($user_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($user = Model::$_db->select('md_users', "WHERE `id`='$user_id' AND `protected`='0' LIMIT 1", "`name`, `active`")))
					{
						# Меняем статус
						Model::$_db->update('md_users', array('active'=>($user['active'] == 1 ? 0 : 1)), "WHERE `id`='$user_id' LIMIT 1");

						# Логирование
						if (Core::$_log_actions) Logger::add(($user['active'] == 1 ? 5 : 4), 'Пользователя', $user['name']);

						# OK
						$answer = array(
							'status' => 'ok',
							'active' => $user['active'] == 1 ? 0 : 1
						);
					}
					else $answer['msg'] = 'Пользователь не найден';
				break;

				# УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ
				case 'uremove':
					if ($this->act_index() && isset($this->_params[1]) && ($user_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && ($user = Model::$_db->select('md_users', "WHERE `id`='$user_id' AND `protected`='0' LIMIT 1", "`name`")))
					{
						# Удаляем пользователя
						Model::$_db->delete('md_users', "WHERE `id`='$user_id' LIMIT 1");

						# Логирование
						if (Core::$_log_actions) Logger::add(3, 'Пользователя', $user['name']);

						# OK
						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Пользователь не найден';
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