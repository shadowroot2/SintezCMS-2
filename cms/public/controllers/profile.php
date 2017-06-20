<?php
# ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ v.2.0
# Coded by ShadoW (c) 2013
class cnt_Profile extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth');
	}

	# ФОРМА
	public function act_index()
	{
		View::assign('%page_title', 'Редактирование профиля');
		View::addJS(_TPL_.'user/user.js');
		View::render('user/profile', TRUE, array(
			'user' => array(
				'name'	=> $_SESSION['cms_auth']['name'],
				'group'	=> $_SESSION['cms_auth']['gname'],
			)
		));
	}
}