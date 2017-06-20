<?php
# ГЛАВНЫЙ КОНТРОЛЛЕР v.2.0
# Coded by ShadoW (c) 2013
class cnt_Index extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth/');

		# Подключение модулей
		$this->scan_modules();
	}

	# РЕДИРЕКТ В ОБЪЕКТЫ
	public function act_index()
	{
		Helpers::redirect(_MODULES_.'objects/');
	}

	# АВТОПОДКЛЮЧЕНИЕ МОДУЛЕЙ
	private function scan_modules()
	{
		$modules_list = array();
		foreach(scandir(_ABS_CONTROLLERS_.'modules') as $module_dir)
		{
			# Модуль определен правильно
			if (
				!in_array($module_dir, array('.','..')) &&
				is_dir(_ABS_CONTROLLERS_.'modules'._SEP_.$module_dir) &&
				is_file(_ABS_CONTROLLERS_.'modules'._SEP_.$module_dir._SEP_.'module.info') &&
				($module_info = explode("\n", file_get_contents(_ABS_CONTROLLERS_.'modules'._SEP_.$module_dir._SEP_.'module.info')))
			)
			{
				$module = array('route'=>$module_dir);
				foreach($module_info as $conf_str)
				{
					# Строка конфигурации
					list($name, $conf) = explode(':', $conf_str);

					# Действия
					if ($name == 'actions')
					{
						$actions = array();
						foreach(explode(';', $conf) as $action)
						{
							if (strstr($action, '->'))
							{
								list($act_name, $act_route) = explode('->', $action);
								$actions[] = array(
									'name'  => $act_name,
									'route' => $act_route
								);
							}
						}
						$conf = $actions;
					}

					# Записываем параметр
					$module[$name] = $conf;
				}
				$modules_list[$module['sort']] = $module;
			}
		}
		ksort($modules_list, SORT_NUMERIC);
		file_put_contents(_ABS_CACHE_.'modules.cache', serialize($modules_list));
	}
}