<?php
# МОДУЛЬ СТАТИСТИКА ПО БАННЕРАМ v.2.0
# Coded by ShadoW (c) 2014
class cnt_Index extends Controller
{
	private $_container	= 249;
	private $_class		= 26;

	private $_date_s	= '';
	private $_date_e	= '';

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth');

		# ПЕРИОД
		$this->_date_s = $this->_date_e = date('d.m.Y');
		if (
			isset($_GET['date_s']) && isset($_GET['date_e']) &&
			($date_s = filter_input(INPUT_GET, 'date_s', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'#[0-9]{2}\.[0-9]{2}\.[0-9]{4}#')))) &&
			($date_e = filter_input(INPUT_GET, 'date_e', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'#[0-9]{2}\.[0-9]{2}\.[0-9]{4}#'))))
		)
		{
			$this->_date_s = $date_s;
			$this->_date_e = $date_e;
		}
	}

	# СТАТИСТИКА
	public function act_index()
	{
		# ДАТЫ
		$date_s = date('Y-m-d', strtotime($this->_date_s));
		$date_e = date('Y-m-d', strtotime($this->_date_e));

		# ПОЛУЧАЕМ СПИСОК БАННЕРОВ
		$banners = array();
		if (($containers_list = Model::getObjects($this->_container, 0, false, "o.active='1' AND o.inside>'0' ORDER BY o.sort ASC")))
		{
			# Контейнеры
			$containers = array();
			foreach($containers_list as $c) $containers[] = $c['id'];
			unset($containers_list, $c);

			# Баннеры
			if ($banners_list = Model::getObjects(-1, $this->_class, false, "o.mother IN(".join(',', $containers).") ORDER BY o.name ASC"))
			{
				foreach($banners_list as $b)
				{
					$banners[$b['id']] = array(
						'name'	 => $b['name'],
						'active' => ($b['active'] == 1) ? true : false
					);
				}
				unset($banners_list);
			}
		}

		# СТАТИСТИКА
		$banners_stat = Banner_Logger::get(array_keys($banners), $date_s, $date_e);

		# ВЫВОД
		View::assign('%page_title', 'Статистика по баннерам');
		View::render('modules/banners/stat', true, array(
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'date_s'		=> $this->_date_s,
			'date_e'		=> $this->_date_e,
			'total_count'	=> count($banners_stat),
			'banners'		=> $banners,
			'banners_stat'	=> $banners_stat
		));
	}

	# ОЧИСТКА БАЗЫ
	public function act_clear()
	{
		View::assign('%page_title', 'Очистка базы статистики по баннерам');
		if (isset($this->_params[0]) && ($this->_params[0] == 'yes'))
		{
			Banner_Logger::clear();
			View::render('modules/banners/cleared');
		}
		else View::render('modules/banners/confirm_clear');
	}
}