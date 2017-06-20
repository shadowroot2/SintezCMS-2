<?php
# ОБЪЕКТ ПОЛЬЗОВАТЕЛЬ v.2.0
# Coded by ShadoW (с) 2013
defined('_CORE_') or die('Доступ запрещен');

class _User extends Singleton
{
    private $_container		= 10;
	private $_class			= 10;
	private $_passwd_field	= 171;
	private $_passwd_length	= 6;

	public $_authed = false;
	public $_data	= array();

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct()
	{
		$this->check_auth();
	}

	# ВХОД
	public function sign_in($email, $password, $remember=false)
	{
		# Проверка данных
		if (!$this->_authed && ($email = filter_var($email, FILTER_VALIDATE_EMAIL)) && (mb_strlen($password) >= $this->_passwd_length))
		{
			# Получаем данные пользователя
			if ($user = Model::getObjects($this->_container, $this->_class, array('Имя', 'Фамилия'), "o.active='1' AND o.name='$email' AND (c.f_".$this->_passwd_field."='".md5(_SALT_.$password)."' OR c.f_".$this->_passwd_field."='".Helpers::escape($password)."') LIMIT 1"))
			{
				# Обновляем дату входа
				Model::editObject(array('id'=>$user['id'], 'm_date'=>time()));

				# Составляем пользователя
				$user = array_merge(array(
					'id'		=> $user['id'],
					'sess'		=> md5(time()._SALT_),
					'name'		=> $user['Имя']
				), $this->_data);

				# Записываем данные в RAM-таблицу
				if (Model::$_db->select('md_site_users_ram', "WHERE `id`='".$user['id']."' LIMIT 1", "`id`"))
				{
					Model::$_db->update('md_site_users_ram', $user, "WHERE `id`='".$user['id']."' LIMIT 1");
				}
				else Model::$_db->insert('md_site_users_ram', $user);

				# Записываем в SESSION
				$_SESSION['site_user'] = array('id'=>$user['id'], 'sess'=>$user['sess']);

				# Запоминаем e-mail
				setcookie('email', $email, time()+32140800, '/');

				# Запомнить?
				if ($remember) setcookie('site_user', $user['id'].':'.$user['sess'], time()+32140800, '/');

				return true;
			}
		}

		return false;
	}

	# ВЫХОД
	public function sign_out()
	{
		if ($this->_authed) Model::$_db->update('md_site_users_ram', array('sess'=>md5(time())), "WHERE `id`='".$this->_data['id']."' LIMIT 1");

		setcookie('site_user', '', 0, '/');
		unset($_SESSION['site_user']);
	}

	# ПРОВЕКРА АВТОРИЗАЦИИ
	private function check_auth()
	{
		# Авторизация по SESSION
		if (isset($_SESSION['site_user']) && is_array($_SESSION['site_user']) && isset($_SESSION['site_user']['id']) && isset($_SESSION['site_user']['sess']) && ($user_data = $this->get_user_ram_data($_SESSION['site_user']['id'], $_SESSION['site_user']['sess'])))
		{
			$this->_authed 	= true;
			$this->_data	= $user_data;
		}

		# Авторизация по COOKIE
		else if (isset($_COOKIE['site_user']) && ($cookie = explode(':', $_COOKIE['site_user'])) && (count($cookie) == 2) && ($user_data = $this->get_user_ram_data($cookie[0], $cookie[1])))
		{
			# ДАТА ВХОДА
			Model::editObject(array('id'=>$user_data['id'], 'm_date'=>time()));

			$this->_authed			= true;
			$this->_data			= $user_data;
			$_SESSION['site_user']	= array('id'=>$user_data['id'], 'sess'=>$user_data['sess']);
		}

		# Данные не авторизованного пользователя из SESSION
		else if (isset($_SESSION['site_user']['data'])) $this->_data = $_SESSION['site_user']['data'];

		return $this->_authed;
	}

	# ЗАДАНИЕ ПЕРЕМЕННОЙ
	public function setVar($var, $val)
	{
		$this->_data[$var] = $val;

		if ($this->_authed) return Model::$_db->update('md_site_users_ram', array($var=>$val), "WHERE `id`='".$this->_data['id']."' LIMIT 1", ($var == 'basket' ? false : true));
		else $_SESSION['site_user']['data'][$var] = $val;

		return true;
	}

	# ПОЛУЧЕНИЕ ДАННЫХ ПОЛЬЗОВАТЕЛЯ ИЗ RAM ТАБЛИЦЫ
	private function get_user_ram_data($user_id, $sess)
	{
		if (($user_id = filter_var($user_id, FILTER_VALIDATE_INT)) && ($sess = filter_var($sess, FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[0-9a-z]{32}$/')))))
			return Model::$_db->select('md_site_users_ram', "WHERE `id`='$user_id' AND `sess`='$sess' LIMIT 1");

		return false;
	}

	# ПОЛУЧЕНИЕ ИНФОРМАЦИИ ПО ПОЛЬЗОВАТЕЛЮ
	public function getUserInfo()
	{
		if ($this->_authed) return Model::getObject($this->_data['id'], true, false)->asArray();
		return false;
	}

	# УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ ИЗ RAM ТАБЛИЦЫ
	private function delete_user_ram_data($user_id)
	{
		return Model::$_db->delete('md_site_users_ram', "WHERE `id`='$user_id'");
	}
}