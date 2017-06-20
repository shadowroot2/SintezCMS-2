<?php
# ОБЪЕКТ ПОЛЬЗОВАТЕЛЬ v.1.0
# Coded by ShadoW (C) 2011
defined('_CORE_') or die('Доступ запрещен');

class _User extends Singleton
{
	public $_authed = FALSE;
	public $_data	= array();

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct()
	{
		$this->check_auth();
	}

	# ПРОВЕКРА АВТОРИЗАЦИИ
	public function check_auth()
	{
		# Авторизация по SESSION
		if (!empty($_SESSION['site_user']) && is_array($_SESSION['site_user']) && !empty($_SESSION['site_user']['id']) && !empty($_SESSION['site_user']['sess']) && ($user_data = $this->get_user_ram_data($_SESSION['site_user']['id'], $_SESSION['site_user']['sess'])))
		{
			$this->_authed 	= TRUE;
			$this->_data	= $user_data;
		}

		# Авторизация по COOKIE
		else if (!empty($_COOKIE['site_user']) && ($cookie = explode(':', $_COOKIE['site_user'])) && (sizeof($cookie) == 2) && ($user_data = $this->get_user_ram_data($cookie[0], $cookie[1])))
		{
			# ДАТА ВХОДА
			Model::editObject(array('id'=>$user_data['id'], 'm_date'=>time()));

			$this->_authed			= TRUE;
			$this->_data			= $user_data;
			$_SESSION['site_user']	= array('id'=>$user_data['id'], 'sess'=>$user_data['sess']);
		}

		# Данные не авторизованного пользователя из SESSION
		else if (!empty($_SESSION['site_user']['data'])) $this->_data = $_SESSION['site_user']['data'];

		return $this->_authed;
	}

	# ВХОД
	public function login($email, $pass, $save=FALSE)
	{
		# Проверка данных
		if (!$this->_authed && filter_var($email, FILTER_VALIDATE_EMAIL) && (mb_strlen($pass) >= 6))
		{
			# Получаем данные пользователя
			if ($user = Model::getObjects(12, 10, array('Имя'), "o.active='1' AND o.name='$email' AND (c.f_175='".md5(_SALT_.$pass)."' OR c.f_175='$pass') LIMIT 1"))
			{
				# ДАТА ВХОДА
				Model::editObject(array('id'=>$user['id'], 'm_date'=>time()));

				# ПОЛЬЗОВАТЕЛЬ
				$user = array_merge(array(
					'id'		=> $user['id'],
					'sess'		=> md5(time()._SALT_),
					'name'		=> $user['Имя']
				), $this->_data);

				# БЕРЕМ ИЗ ИЛИ ЗАПИСЫВАЕМ В RAM-ТАБЛИЦУ
				if ($user_data = Model::$_db->select('md_site_users_ram', "WHERE `id`='".intval($user['id'])."' LIMIT 1", "`id`"))
				{
					Model::$_db->update('md_site_users_ram', $user, "WHERE `id`='".$user['id']."' LIMIT 1");
				}
				else Model::$_db->insert('md_site_users_ram', $user);

				# ЗАПИСЫВАЕМ В СЕССИЮ
				$_SESSION['site_user'] = array('id'=>$user['id'], 'sess'=>$user['sess']);

				# ЗАПОМИНАЕМ ЛОГИН
				setcookie('login', $email, time()+32140800, '/');

				# ЗАПОМНИТЬ В КУКИ
				if ($save) setcookie('site_user', $user['id'].':'.$user['sess'], time()+32140800, '/');

				return TRUE;
			}
		}

		return FALSE;
	}

	# ВЫХОД
	public function logout()
	{
		if ($this->_authed)
		{
			# МЕНЯЕМ СЕССИЮ
			Model::$_db->update('md_site_users_ram', array('sess'=>md5(time())), "WHERE `id`='".$this->_data['id']."' LIMIT 1");

			# УДАЛЯЕМ СЕССИЮ
			unset($_SESSION['site_user']);

			# УДАЛЯЕМ КУКУ
			setcookie('site_user', '', 0, '/');

			return TRUE;
		}

		return FALSE;
	}


	# ПОЛУЧЕНИЕ ИНФОРМАЦИИ ПО ПОЛЬЗОВАТЕЛЮ
	public function getUserInfo()
	{
		if ($this->_authed) return Model::getObject($this->_data['id'], TRUE);
		return FALSE;
	}

	# ЗАДАНИЕ ПЕРЕМЕННОЙ
	public function setVar($var, $val)
	{
		$this->_data[$var] = $val;

		if ($this->_authed) return Model::$_db->update('md_site_users_ram', array($var=>$val), "WHERE `id`='".$this->_data['id']."' LIMIT 1", ($var == 'basket' ? false : true));
		else
		{
			$_SESSION['site_user']['data'][$var] = $val;
			return TRUE;
		}

		return FALSE;
	}

	# ПОЛУЧЕНИЕ ДАННЫХ ПОЛЬЗОВАТЕЛЯ ИЗ RAM ТАБЛИЦЫ
	private function get_user_ram_data($id, $sess)
	{
		if (
			filter_var($id, FILTER_VALIDATE_INT) && filter_var($sess, FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[0-9a-z]{32}$/'))) &&
			($user_data = Model::$_db->select('md_site_users_ram', "WHERE `id`='".intval($id)."' AND `sess`='".helpers::escape($sess)."' LIMIT 1"))
		)
		{
			return $user_data;
		}
		return FALSE;
	}

	# УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ ИЗ RAM ТАБЛИЦЫ
	private function delete_user_ram_data($id)
	{
		return Model::$_db->delete('md_site_users_ram', "WHERE `id`='".intval($id)."'");
	}
}