<?php
# МОДУЛЬ ПОЛЬЗОВАТЕЛИ САЙТА v.2.0
# Coded by ShadoW (c) 2014
class cnt_Index extends Controller
{
	private $_personal			= false;

	private $_container			= 10;
	private $_class				= 10;
	private $_name_field		= 164;

	private $_users_count		= 0;

	private $_search			= '';

	private $_citys_container	= 36;
	private $_citys_class		= 36;
	private $_city_field		= 168;
	private $_city_name_field	= 'Название';
	private $_citys				= array();
	private $_city				= '';

	private $_statuses			= array(array('name'=>'Не активные', 'set'=>false), array('name'=>'Активные', 'set'=>false));
	private $_status			= false;

	private $_date_s			= false;
	private $_date_e			= false;

	private $_page				= 1;
	private $_perpages			= array(50, 100, 200, 500);
	private $_perpage			= 50;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth');

		# Контейнер
		if (($this->_container = Model::getObject($this->_container, false, false)) && ($this->_container['class_id'] == 0)) View::assign('%page_title', $this->_container['name']);
		else exit('Контейнер не пользователей найден');

		# Поск
		if (isset($_GET['search'])) $this->_search = Helpers::escape($_GET['search']);

		# Города
		if ($citys_list = Model::getObjects($this->_citys_container, $this->_citys_class, true))
		{
			foreach($citys_list as $c) $this->_citys[$c['id']] = array('id'=>$c['id'], 'name'=>$c[$this->_city_name_field]);
		}
		if (isset($_GET['city']) && ($city_id = filter_input(INPUT_GET, 'city', FILTER_VALIDATE_INT)) && isset($this->_citys[$city_id]))
		{
			$this->_city = $city_id;
		}

		# СТАТУС
		if (isset($_GET['status']) && is_int($status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT)) && isset($this->_statuses[$status])) $this->_status = $status;
		if (is_int($this->_status)) $this->_statuses[$this->_status]['set'] = true;

		# Даты отчета по регистрациям
		if (isset($_GET['date_s']) && ($date_s = filter_input(INPUT_GET, 'date_s', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/')))))
			$this->_date_s = date('d.m.Y', strtotime($date_s));
		else $this->_date_s = date('d.m.Y');
		if (isset($_GET['date_e']) && ($date_e = filter_input(INPUT_GET, 'date_e', FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/')))))
			$this->_date_e = date('d.m.Y', strtotime($date_e));
		else $this->_date_e = date('d.m.Y');

		# Страница
		if (isset($_GET['pg']) && ($pg = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT)) && ($pg > 0)) $this->_page = $pg;

		# Кол-во на страницу
		if (isset($_GET['perpage']) && ($perpage = filter_input(INPUT_GET, 'perpage', FILTER_VALIDATE_INT)) && in_array($perpage, $this->_perpages))
			$this->_perpage = $_SESSION['cms_data']['site_users']['perpage'] = $perpage;
		elseif (!isset($_SESSION['cms_data']['site_users']['perpage']))
			$_SESSION['cms_data']['site_users']['perpage'] = $this->_perpage;
	}

	# СПИСОК ПОЛЬЗОВАТЕЛЕЙ
	public function act_index()
	{
		# Фильтрация
		if (($this->_search != '') || ($this->_city != '') || is_int($this->_status))
		{
			# Запрос
			$query = array();
			if (is_int($this->_status))	$query[] = "o.active='".$this->_status."'";
			if ($this->_search != '')	$query[] = (filter_var($this->_search, FILTER_VALIDATE_INT) ? "o.id='".$this->_search."'" : "(o.name LIKE '%".$this->_search."%' OR c.f_".$this->_name_field." LIKE '%".$this->_search."%')");
			if ($this->_city != '')		$query[] = "c.f_".$this->_city_field."='".$this->_city."'";
		}

		# Количество найденых
		$this->_users_count = Model::getObjectsCount($this->_container, $this->_class, (isset($query) ? join(' AND ', $query) : '1=1'));

		# Пагинатор
		Paginator::setPaginator($this->_users_count, $this->_page, $this->_perpage);

		# Список пользователей
		$users = array();
		if ($this->_users_count && $users_list = Model::getObjects($this->_container, $this->_class, array('Имя', 'Фамилия'), (isset($query) ? join(' AND ', $query) : '1=1')." ORDER BY o.c_date DESC LIMIT ".Paginator::$_start.", ".$this->_perpage))
		{
			foreach($users_list as $u)
			{
				$users[] = array(
					'id' 			=> $u['id'],
					'active'		=> $u['active'],
					'email'			=> $u['name'],
					'name'			=> $u['Имя'].' '.$u['Фамилия'],
					'status'		=> $u['active'] == 1 ? 'on' : 'off',
					'status_text'	=> $u['active'] == 1 ? 'Включен' : 'Отключен',
				);
			}
			unset($users_list, $u);
		}

		# ВЫВОД
		View::render('modules/site_users/list', TRUE, array(
			'personal'		=> $this->_personal,
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'search'		=> $this->_search,
			'statuses'		=> $this->_statuses,
			'status'		=> $this->_status,
			'citys'			=> $this->_citys,
			'city'			=> $this->_city,
			'users_count'	=> $this->_users_count,
			'users'			=> $users,
			'perpages'		=> $this->_perpages,
			'perpage'		=> $this->_perpage,
			'prefix'		=> '?search='.$this->_search.'&city='.$this->_city.'&status='.$this->_status.'&perpage='.$this->_perpage.'&',
			'pages'			=> Paginator::$_pages,
		));
	}

	# РЕДАКТИРОВАНИЕ ПОЛЬЗОВАТЕЛЯ
	public function act_edit()
	{
		if (!empty($this->_params[0]) && ($site_user = Model::getObject($this->_params[0], TRUE, TRUE)) && ($site_user['mother'] == $this->_container) && ($site_user['class_id'] == $this->_class))
		{
			# ПРОВЕРКА ПРАВ ДОСТУПА
			if (Core::$_data['module_access'] != 'rw') exit(View::render('access_denied'));

			# СОХРАНЕНИЕ
			if (!empty($_POST['name']) && ($class = Model::getClass($this->_class)))
			{
				# Отключение чекбоксов и мультиселектов
				foreach($class['fields'] as $f_id=>$f)
				{
					if (($f['type'] == 'checkbox')		&& empty($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = 0;
					if (($f['type'] == 'multi-select')	&& empty($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = '';
					if (($f['type'] == 'multi-link')	&& empty($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = '';
				}

				# СОХРАНЯЕМ ОБЪЕКТ
				if (Model::editObject(
					array(
						'id'		=> $site_user['id'],
						#'active'	=> (!empty($_POST['active']) ? 1 : 0),
						'name'		=> $_POST['name']
					),
					(isset($_POST['fields']) ? $_POST['fields'] : false)
				))
				{
					Logger::add(2, 'Пользователя', $_POST['name'], $site_user['id'],  $site_user['class_id']);
					Helpers::redirect(_MODULES_.'site_users');
				}
				else View::assign('%form_error', 'Невозможно сохранить пользователя, проверьте правильность заполнения полей');
			}

			# ДАТЫ
			$site_user['c_date'] = Helpers::date('j mounth Y at H:i', $site_user['c_date']);
			$site_user['m_date'] = Helpers::date('j mounth Y at H:i', $site_user['m_date']);

			# ПОЛЯ ШАБЛОНА
			$template_HTML = '';
			if (!empty($site_user['class_id']) && ($template = Model::getClass($site_user['class_id'])->asArray()))
			{
				# Обработка значений полей
				foreach($template['fields'] as $f_id=>$v)
				{
					$field_value = (!empty($site_user[$v['name']]) ? $site_user[$v['name']] : '');

					switch ($v['type'])
					{
						case 'date':
							$field_value = (!empty($field_value) ? date('d.m.Y', strtotime($field_value)) : '');
						break;
						case 'password':
							$field_value = (!empty($field_value) ? '********':'');
						break;
					}
					$template['fields'][$f_id]['value'] = $field_value;
				}
				$template_HTML = View::getTemplate('modules/objects/template_fields', FALSE, array('template' => $template));
			}

			# ВЫВОД
			View::assign('%page_title', 'Редактирование пользователя');
			View::render('modules/site_users/edit', TRUE, array(
				'user'				=>	$site_user,
				'template_fields'	=>	$template_HTML
			));
		}
		else Helpers::redirect(_MODULES_.'objects');
	}

	# ПОСЕТИТИЕЛИ
	/*public function act_logins()
	{
		$users = array();
		if ($users_list = Model::getObjects($this->_container, $this->_class, array('Имя', 'Фамилия', 'Город'), "o.active='1'".(is_int($this->_city) ? " AND c.f_168='".$this->_city."'" : '')." AND (o.m_date BETWEEN '".date('Y-m-d', strtotime($this->_date_s))."' AND '".date('Y-m-d', strtotime($this->_date_e))."') ORDER BY o.m_date ASC"))
		{
			foreach($users_list as $u)
			{
				$users[] = array(
					'id' 		=> $u['id'],
					'active'	=> $u['active'],
					'name'		=> $u['Имя'].' '.$u['Фамилия'],
					'email'		=> $u['name'],
					'city'		=> $u['Город']
				);
			}
			unset($users_list, $u);
		}

		# ВЫВОД
		View::assign('%page_title', 'Посетители сайта');
		View::render('modules/site_users/logins', true, array(
			'personal'		=> $this->_personal,
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'citys'			=> $this->_citys,
			'date_s'		=> $this->_date_s,
			'date_e'		=> $this->_date_e,
			'users_count'	=> count($users),
			'users'			=> $users
		));
	}*/

	# ОТЧЕТ ПО РЕГИСТРАЦИЯМ
	public function act_registrations()
	{
		# Количество найденых
		$this->_users_count = Model::getObjectsCount($this->_container, $this->_class, "(o.c_date BETWEEN '".strtotime($this->_date_s.' 00:00:00')."' AND '".(strtotime($this->_date_e)+86399)."')".(is_int($this->_city) ? " AND c.f_168='".$this->_city."'" : ''));

		# Пагинатор
		Paginator::setPaginator($this->_users_count, $this->_page, $this->_perpage);

		# Список пользователей
		$users = array();
		if ($users_list = Model::getObjects($this->_container, $this->_class, array('Имя', 'Фамилия', 'Дата активации'), "(o.c_date BETWEEN '".strtotime($this->_date_s.' 00:00:00')."' AND '".(strtotime($this->_date_e)+86399)."')".(is_int($this->_city) ? " AND c.f_168='".$this->_city."'" : '')." ORDER BY o.c_date ASC LIMIT ".Paginator::$_start.", ".$this->_perpage))
		{
			foreach($users_list as $u)
			{
				$users[] = array(
					'id' 			=> $u['id'],
					'active'		=> $u['active'],
					'date'			=> date('d.m.Y в H:i', $u['c_date']),
					'email'			=> $u['name'],
					'name'			=> $u['Имя'].' '.$u['Фамилия']
				);
			}
		}

		# ВЫВОД
		View::assign('%page_title', 'Отчет по регистрациям пользователей');
		View::render('modules/site_users/registrations', true, array(
			'personal'		=> $this->_personal,
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'form_prefix'	=> 'registrations',
			'citys'			=> $this->_citys,
			'city'			=> $this->_city,
			'date_s'		=> $this->_date_s,
			'date_e'		=> $this->_date_e,
			'users_count'	=> $this->_users_count,
			'users'			=> $users,
			'perpages'		=> $this->_perpages,
			'perpage'		=> $this->_perpage,
			'page_prefix'	=> '?city='.$this->_city.'&date_s='.$this->_date_s.'&date_e='.$this->_date_e.'&perpage='.$this->_perpage.'&',
			'pages'			=> Paginator::$_pages,
		));
	}
}