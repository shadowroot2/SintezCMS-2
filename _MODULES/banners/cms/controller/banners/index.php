<?php
# МОДУЛЬ СТАТИСТИКА ПО БАННЕРАМ v.1.0
# Coded by ShadoW (c) 2013
class cnt_Index extends Controller
{
	private $_personal			= FALSE;

	private $_class				= 17;

	private $_citys_container	= 11;
	private $_citys				= array();
	private $_city				= 21;

	private $_date_s			= '';
	private $_date_e			= '';

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (Core::$_data['authed'] == false) Helpers::redirect(_CMS_.'auth');

		# ГОРДА
		if ($citys = Model::getObjects($this->_citys_container, 0))
		{
			foreach($citys as $c) $this->_citys[$c['id']] = array('id'=>$c['id'], 'name'=>$c['name'], 'set'=>false);
		}
		if (!empty($_GET['city']) && isset($this->_citys[$_GET['city']])) $this->_city = $_GET['city'];

		# ГОРОД ЗАДАН В ДОСТУПЕ К МОДУЛЮ
		if (!empty(Core::$_data['module_params']) && ($city_id = filter_var(Core::$_data['module_params'], FILTER_VALIDATE_INT)) && isset($this->_citys[$city_id]))
		{
			$this->_personal = true;
			$this->_city = $city_id;
		}
		$this->_citys[$this->_city]['set'] = true;

		# ПЕРИОД
		$this->_date_s = $this->_date_e = date('d.m.Y');
		if (
			!empty($_GET['date_s']) && !empty($_GET['date_s']) &&
			($date_s = filter_input(INPUT_GET, 'date_s', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'#[0-9]{2}\.[0-9]{2}\.[0-9]{4}#')))) &&
			($date_e = filter_input(INPUT_GET, 'date_e', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'#[0-9]{2}\.[0-9]{2}\.[0-9]{4}#'))))
		)
		{
			$this->_date_s = $date_s;
			$this->_date_e = $date_e;
			unset($date_s, $date_e);
		}

		# НА СТРАНИЦУ
		if (!empty($_GET['perpage']) && ($perpage = filter_input(INPUT_GET, 'perpage', FILTER_VALIDATE_INT)) && in_array($perpage, $this->_perpages))
		{
			$this->_perpage = $perpage;
			unset($perpage);
		}
	}

	# СТАТИСТИКА
	public function act_index()
	{
		# ПОЛУЧАЕМ СПИСОК БАННЕРОВ
		$banners = array();
		if ($root_container = Model::getObjects($this->_city, 0, FALSE, "o.active='1' AND o.name='Баннеры' LIMIT 1"))
		{
			if (($root_container['inside'] > 0) && ($containers_list = Model::getObjects($root_container['id'], 0, FALSE, "o.active='1' AND o.inside>0 ORDER BY o.sort ASC")))
			{
				$containers = array();
				foreach($containers_list as $c) $containers[] = $c['id'];
				unset($root_container, $containers_list, $c);

				if ($banners_list = Model::getObjects(-1, $this->_class, FALSE, "o.mother IN(".join(',', $containers).") ORDER BY o.name ASC"))
				{
					foreach($banners_list as $b)
					{
						$banners[$b['id']] = array(
							'name'		=> $b['name'],
							'active'	=> !empty($b['active']) ? true : false
						);
					}
					unset($banners_list);
				}
			}
		}

		# ДАТЫ
		$date_s = date('Y-m-d', strtotime($this->_date_s));
		$date_e = date('Y-m-d', strtotime($this->_date_e));

		# СТАТИСТИКА
		$banners_stat = Banner_Logger::get(array_keys($banners), $date_s, $date_e);

		# ВЫВОД
		View::assign('%page_title', 'Статистика по баннерам');
		View::render('modules/banners/stat', TRUE, array(
			'personal'		=> $this->_personal,
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'citys'			=> $this->_citys,
			'city'			=> $this->_city,
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
		if (!empty($this->_params[0]) && ($this->_params[0] == 'yes'))
		{
			Banner_Logger::clear();
			View::render('modules/banners/cleared');
		}
		else View::render('modules/banners/confirm_clear');
	}

}