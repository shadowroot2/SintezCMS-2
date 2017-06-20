<?php
# ГЛОБАЛЬНЫЙ КОНТРОЛЛЕР (ПОДКЛЮЧАЕТСЯ ВЕЗДЕ КРОМЕ ИСКЛЮЧЕНИЙ) v.2.0
# Coded by ShadoW (c) 2013
class cnt_Default
{
	final function run()
	{
		# Константы
		define('_CMS_', _ROOT_.'cms/');
		define('_ABS_CMS_', _ABS_ROOT_.'cms'._SEP_);
		define('_MODULES_', _CMS_.'modules/');
		define('_MODULE_TEMPLATES_', _CMS_.'public/templates/modules/');
		define('_SALT_', 'salt :)');

		# Сессия в запросе
		if (isset($_POST['PHPSESSID'])) session_id($_POST['PHPSESSID']);

		# Стартуем сессию
		session_start();

		# АВТОРИЗАЦИЯ ПО СЕССИИ
		Core::$_data['authed'] = false;
		if (
			isset($_SESSION['cms_auth']) && is_array($_SESSION['cms_auth']) &&
			isset($_SESSION['cms_auth']['id']) && isset($_SESSION['cms_auth']['gid']) &&
			($uid = filter_var($_SESSION['cms_auth']['id'], FILTER_VALIDATE_INT)) && filter_var($_SESSION['cms_auth']['gid'], FILTER_VALIDATE_INT) &&
			isset($_SESSION['cms_auth']['name']) && ($_SESSION['cms_auth']['name'] != '') &&
			isset($_SESSION['cms_auth']['gname']) && ($_SESSION['cms_auth']['gname'] != '') &&
			isset($_SESSION['cms_auth']['access']) && ($_SESSION['cms_auth']['access'] != '') &&
			isset($_SESSION['cms_auth']['sess']) && ($sess = $_SESSION['cms_auth']['sess']) && preg_match('#[0-9a-z]{32}#', $sess) &&
			Model::$_db->select('md_users_ram', "WHERE `user_id`='$uid' AND `sess`='$sess' LIMIT 1")
		)
		{
			Core::$_data['authed'] 	 = true;
			Core::$_data['cms_auth'] = &$_SESSION['cms_auth'];
		}

		# АВТОРИЗАЦИЯ ПО COOKIE
		else if (
			isset($_COOKIE['cms_auth']) &&
			($cookie_parts = explode(':', $_COOKIE['cms_auth'])) && (count($cookie_parts) == 2) &&
			($user_id = filter_var($cookie_parts[0], FILTER_VALIDATE_INT)) &&
			($sess = $cookie_parts[1]) && preg_match('#^[0-9a-z]{32}$#', $sess) &&
			($user = Model::$_db->select('md_users', "as u LEFT JOIN `md_user_groups` as g ON g.id=u.gid WHERE u.active='1' AND g.active='1' AND g.id='$user_id' LIMIT 1", "u.id, u.gid, u.name, g.name as gname, g.access"))
		)
		{
			# Меняем сессию
			$new_sess = md5(_SALT_.time());

			# Время входа
			Model::$_db->update('md_users', array('login_ts'=>time()), "WHERE `id`='$user_id' LIMIT 1");

			# Обновляем сессию
			if (!Model::$_db->select('md_users_ram', "WHERE `user_id`='$user_id' AND `sess`='$sess' LIMIT 1")) Model::$_db->insert('md_users_ram', array('user_id'=>$user_id, 'sess'=>$new_sess));
			else Model::$_db->update('md_users_ram', array('sess'=>$new_sess), "WHERE `user_id`='$user_id' LIMIT 1");

			# Доступы
			if ($user['access'] != '*') $user['access'] = (array)unserialize($user['access']);

			# Пишем в SESSION
			$_SESSION['cms_auth'] = array_merge($user, array('sess'=>$new_sess));

			# Меням куку
			setcookie('cms_auth', $user_id.':'.$new_sess, time()+86400*30, '/');

			Core::$_data['authed']	 = true;
			Core::$_data['cms_auth'] = $_SESSION['cms_auth'];
		}

		# АВТОРИЗОВАН
		if (Core::$_data['authed'])
		{
			# Асоциируем глобалки
			View::assign('%site', array(
				'title'			=> 'Sintez CMS',
				'description'	=> '',
				'keywords'		=> ''
			));

			# Меню сайта
			$cms_menu = Core::$_data['cms_modules'] = array();
			if (file_exists(_ABS_CACHE_.'modules.cache') && ($menu_list = Core::$_data['cms_modules'] = (array)unserialize(file_get_contents(_ABS_CACHE_.'modules.cache'))))
			{
				# Активное меню
				foreach($menu_list as $k=>$m)
				{
					$menu_list[$k]['set'] = false;

					# Бегаем по действиям
					foreach($m['actions'] as $key=>$a)
					{
						$set = ('modules/'.$m['route'].($a['route'] != '' ? '/'.$a['route'] : '') == Router::$_route ? true : false);
						$menu_list[$k]['actions'][$key]['set'] = $set;
						if ($set) $menu_list[$k]['set'] = true;
					}

					# Ищем в пути
					if (preg_match('#^modules/'.$m['route'].'/(.*)#i', Router::$_route)) $menu_list[$k]['set'] = true;

					# Проверям доступ и записываем в меню
					if ((Core::$_data['cms_auth']['access'] == '*')	|| isset(Core::$_data['cms_auth']['access'][$m['route']]))
					{
						$cms_menu[] = array(
							'name' 		=> $menu_list[$k]['name'],
							'route' 	=> $menu_list[$k]['route'],
							'set'		=> $menu_list[$k]['set'],
							'actions' 	=> $menu_list[$k]['actions']
						);
					}
				}

				# Записываме меню
				View::assign('%cms_menu', $cms_menu);
			}

			# Проверка доступа к модулю
			if (Core::$_data['cms_auth']['access'] == '*') Core::$_data['module_access'] = 'rw';
			else if (
				($route_parts = explode('/', Router::$_route)) &&
				(count($route_parts) >= 2) &&
				($route_parts[0] == 'modules')
			)
			{
				if (!isset(Core::$_data['cms_auth']['access'][$route_parts[1]]) || !is_array(Core::$_data['cms_auth']['access'][$route_parts[1]])) Helpers::redirect(_CMS_);
				Core::$_data['module_access'] = Core::$_data['cms_auth']['access'][$route_parts[1]]['access'];
				Core::$_data['module_params'] = Core::$_data['cms_auth']['access'][$route_parts[1]]['params'];
			}
		}
	}
}