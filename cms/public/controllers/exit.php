<?php
# ВЫХОД ИЗ CMS v.2.0
# Coded by ShadoW (c) 2013
class cnt_Exit extends Controller
{
	public function init() {}

	public function act_index()
	{
		# Проверка авторищации
		if (Core::$_data['authed'] && isset($_SESSION['cms_auth']['id']) && ($user_id = filter_var($_SESSION['cms_auth']['id'], FILTER_VALIDATE_INT)))
		{
			# Удаляем из RAM-таблициы
			Model::$_db->delete('md_users_ram', "WHERE `user_id`='$user_id'");
		}

		# Удаляем сессию
		unset($_SESSION['cms_auth']);
		setcookie('cms_auth', '', time(), '/');

		# Перенаправляем
		Helpers::redirect('/');
	}
}