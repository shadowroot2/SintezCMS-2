<?php
# Контроллер по умолчанию (подключается везде) v.2.0
# Coded by ShadoW (c) 2014
class cnt_Default
{
	final function run()
	{
		# Сайт
		define('_SITE_URL_', 'site.dev');

		# СТАРТ СЕССИИ
		if (session_id() == '')	session_start();

		# ЯЗЫКИ САЙТА
		$langs = array(
			'ru' => array(
				'name'	=> 'Русский',
				'short'	=> 'Рус',
				'set'	=> Core::$_lang == 'ru' ? true : false
			),
			'kz' => array(
				'name'	=> 'Қазақша',
				'short'	=> 'Қаз',
				'set'	=> Core::$_lang == 'kz' ? true : false,
			),
			'en' => array(
				'name'	=> 'English',
				'short'	=> 'Eng',
				'set'	=> Core::$_lang == 'en' ? true : false,
			)
		);

		# ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ
		Core::$_data['defs'] = array();

		# -------------------------------------------------------------------------------------------------------
		# НЕ AJAX
		if (!Core::isAjax())
		{
			# IE 6-8
			if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/msie [1-8]{1}\./i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/ms-office/i', $_SERVER['HTTP_USER_AGENT'])) Helpers::redirect('/ie6/');

			# ГЛАВНОЕ МЕНЮ
			/*Core::$_data['main_menu'] = array();
			if (Core::$_caching && ($main_menu_data = Cache::load('main_menu_'.Core::$_lang)))
				Core::$_data['main_menu'] = (array)$main_menu_data;
			else if (($main_menu_pdo = new _Menu(Core::$_data['defs']['main_menu_id'])) && $main_menu_pdo->count())
			{
				Core::$_data['main_menu'] = $main_menu_pdo->asArray();
				unset($main_menu_pdo);

				# Кэширование
				if (Core::$_caching) Cache::save('main_menu_'.Core::$_lang, Core::$_data['main_menu'], 10800);
			}*/

			# ПЕРЕМЕННЫЕ ОСНОВНОМУ ШАБЛОНУ
			View::assign('%site', array(
				'route'			=> Router::$_route,
				'langs'			=> $langs,
				'lang'			=> Core::$_lang,
				'title'			=> Model::getObject(2, true)->field('Значение'),
				'description'	=> Model::getObject(3, true)->field('Значение'),
				'keywords'		=> Model::getObject(4, true)->field('Значение')
			));
		}
	}
}