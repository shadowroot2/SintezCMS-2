<?php
# МОДУЛЬ ЗАЯВКИ И ОТЧЕТЫ v.1.4
# Coded by ShadoW (c)2012
class cnt_Index extends Controller
{
	private $_personal			= false;

	private $_container_id		= 12;
	private $_class				= 18;

	private $_users_container 	= 7;
	private $_users_class		= 13;

	private $_items_class		= 12;

	private $_citys_container	= 11;
	private $_citys				= array();
	private $_city				= 21;

	private $_statuses			= array();
	private $_status			= 'Новая';

	private $_colors			= array();

	private $_mounthes			= array(
		1	=> 'январь',
		2	=> 'февраль',
		3	=> 'март',
		4	=> 'апрель',
		5	=> 'май',
		6	=> 'июнь',
		7	=> 'июль',
		8	=> 'август',
		9	=> 'сентябрь',
		10	=> 'октябрь',
		11	=> 'ноябрь',
		12	=> 'декабрь'
	);

	private $_delivery_types = array(
		1 => 'Категория А бесплатно',
		2 => 'Категория B 500тг',
		3 => 'Категория C 1 000тг',
		4 => 'Категория D 1 500тг',
		5 => 'Категория E 2 000тг',
	);

	private $_search			= '';
	private $_date_s			= '';
	private $_date_e			= '';
	private $_odelivery			= false;
	private $_user_id			= '';

	private $_orders_count		= 0;
	private $_page				= 1;
	private $_perpages			= array(50, 100, 200, 500);
	private $_perpage			= 50;

	private $_reports_menu		= array(
		'users' => array(
			'name'	=> 'Отчет по пользователям',
			'url'	=> 'orders/reports/users',
			'set'	=> false
		),
		'mounthes' => array(
			'name'	=> 'Отчет по месяцам',
			'url'	=> 'orders/reports/mounthes',
			'set'	=> false
		),
		'liders' => array(
			'name'	=> 'Лидеры продаж',
			'url'	=> 'orders/reports/liders',
			'set'	=> false
		),
		'partners' => array(
			'name'	=> 'Отчет по партнерам',
			'url'	=> 'orders/reports/partners',
			'set'	=> false
		)
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth');

		# ГОРДА
		if ($citys = Model::getObjects($this->_citys_container, 0))
		{
			foreach($citys as $c) $this->_citys[$c['id']] = array('id'=>$c['id'], 'name'=>$c['name'], 'set'=>false);
		}
		if (isset($_GET['city']) && isset($this->_citys[$_GET['city']])) $this->_city = $_GET['city'];

		# ГОРОД ЗАДАН В ДОСТУПЕ К МОДУЛЮ
		if (isset(Core::$_data['module_params']) && ($city_id = filter_var(Core::$_data['module_params'], FILTER_VALIDATE_INT)) && isset($this->_citys[$city_id]))
		{
			$this->_city	 = $city_id;
			$this->_personal = true;

		}
		$this->_citys[$this->_city]['set'] = true;

		# ПЕРИОДЫ ДОСТАВКИ ПО ГОРОДУ
		Dtime::getCityPeriods($this->_city, false);

		# СТАТУСЫ
		if (($statuses = Model::$_db->select('fields', "WHERE `id`='107' LIMIT 1", "`atribute`")) && !empty($statuses['atribute']) && ($statuses = explode("\n", $statuses['atribute'])))
		{
			foreach($statuses as $s)
			{
				if (!empty($s)) $this->_statuses[$s] = false;
			}
		}
		if (!empty($_GET['status']) && isset($this->_statuses[$_GET['status']])) $this->_status = $_GET['status'];
		$this->_statuses[$this->_status] = true;

		# ДАТЫ
		if (!empty($_GET['date_s']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_s'])) $this->_date_s = $_GET['date_s'];
		if (!empty($_GET['date_e']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_e'])) $this->_date_e = $_GET['date_e'];

		# УПОРЯДОЧИТЬ ПО ДАТЕ ЗАЯВКИ
		if (!empty($_GET['orderby_delivery'])) $this->_odelivery = true;

		# ПОИСК
		if (!empty($_GET['search'])) $this->_search = Helpers::escape($_GET['search']);

		# ЗАЯВКИ ПОЛЬЗОВАТЕЛЯ
		if (!empty($_GET['u_id']) && filter_input(INPUT_GET, 'u_id', FILTER_VALIDATE_INT)) $this->_user_id = intval($_GET['u_id']);

		# СТРАНИЦА
		if (!empty($_GET['pg']) && filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT) && ($_GET['pg'] > 1)) $this->_page = intval($_GET['pg']);

		# КОЛИЧЕСТВО НА СТРАНИЦУ
		if (!empty($_GET['perpage']) && in_array($_GET['perpage'], $this->_perpages))	$_SESSION['cms_data']['orders']['perpage'] = intval($_GET['perpage']);
		elseif (empty($_SESSION['cms_data']['orders']['perpage']))						$_SESSION['cms_data']['orders']['perpage'] = $this->_perpage;
		$this->_perpage = $_SESSION['cms_data']['orders']['perpage'];
	}

	# СПИСОК ЗАЯВОК
	public function act_index()
	{
		# Строка поиска
		if (!empty($this->_search))
		{
			# По номеру
			if (is_numeric($this->_search)) $search_query = " AND o.id='".intval($this->_search)."'";

			# По e-mail пользователя
			else if (
				(preg_match('/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i', $this->_search)) &&
				($site_user = Model::getObjects($this->_users_container, $this->_users_class, FALSE, "o.name='".$this->_search."' LIMIT 1"))
			) $search_query = " AND c.f_100='".$site_user['id']."'";
		}

		# Количество заявок
		$this->_orders_count = Model::getObjectsCount($this->_container_id, $this->_class, "o.active='1'".(!empty($this->_user_id) ? " AND c.f_100='".$this->_user_id."'" : '').(!empty($search_query) ? $search_query : '').(!empty($this->_date_s) ? " AND c.f_108>='".date('Y-m-d', strtotime($this->_date_s))."'" : '').(!empty($this->_date_e) ? " AND c.f_108<='".date('Y-m-d', strtotime($this->_date_e))."'" : '')." AND c.f_101='".$this->_city."'".(empty($this->_user_id) ? " AND c.f_107='".$this->_status."'" : ''));

		# Пагинатор
		Paginator::setPaginator($this->_orders_count, $this->_page, $this->_perpage);

		# Список заявок
		if ($orders_list = Model::getObjects($this->_container_id, $this->_class, array('Пользователь', 'Товары', 'Общая цена', 'Скидка', 'Адрес', 'Дата доставки', 'Период доставки', 'Стоимость доставки', 'Способ оплаты', 'Оплачен'), "o.active='1'".(!empty($this->_user_id) ? " AND c.f_100='".$this->_user_id."'" : '').(!empty($search_query) ? $search_query : '').(!empty($this->_date_s) ? " AND c.f_108>='".date('Y-m-d', strtotime($this->_date_s))."'" : '').(!empty($this->_date_e) ? " AND c.f_108<='".date('Y-m-d', strtotime($this->_date_e))."'" : '')." AND c.f_101='".$this->_city."'".(empty($this->_user_id) ? " AND c.f_107='".$this->_status."'" : '')." ORDER BY ".($this->_odelivery ?  'c.f_108 ASC, c.f_109 ASC, o.id ASC' : 'o.c_date DESC')." LIMIT ".Paginator::$_start.", ".$this->_perpage)) //c.f_108 ASC, c.f_109 ASC, c.f_100
		{
			$orders 	   = array();
			$orders_users  = array();

			# ЗАЯВКИ
			foreach($orders_list as $o)
			{
				$orders[] = array(
					'id' 				=> $o['id'],
					'address'			=> $o['Адрес'],
					'delivery_date'		=> Helpers::date('j mounth Y', strtotime($o['Дата доставки'])),
					'delivery_period'	=> isset(Dtime::$_periods[$o['Период доставки']]) ? Dtime::$_periods[$o['Период доставки']]['name'] : '<span class="error">не найден</span>',
					'date'				=> Helpers::date('j mounth Y, H:i', $o['c_date']),
					'user_id'			=> $o['Пользователь'],
					'items'				=> unserialize($o['Товары']),
					'old_total_price'	=> number_format(($o['Общая цена'] + $o['Стоимость доставки']), 0, '.', ' '),
					'total_price'		=> number_format(((!empty($o['Скидка']) ?  ($o['Общая цена'] - ($o['Общая цена'] / 100) * $o['Скидка']) : $o['Общая цена']) + $o['Стоимость доставки']), 0, '.', ' '),
					'pay_type'			=> $o['Способ оплаты'],
					'peyed'				=> !empty($o['Оплачен']) ? true : false,
					'discount'			=> intval($o['Скидка'])
				);

				# Добавляем ID пользователя
				$orders_users[$o['Пользователь']] = array();
			}
			unset($orders_list);

			# ЗАКАЗЧИКИ ЗАЯВОК
			if ($orders_users_list = Model::getObjects($this->_users_container, $this->_users_class, array('Имя', 'Дата рождения'), "o.id IN(".join(',', array_keys($orders_users)).") ORDER BY o.id ASC"))
			{
				foreach($orders_users_list as $u)
				{
					$orders_users[$u['id']] = array(
						'email'	=> $u['name'],
						'name'	=> $u['Имя'],
						'birth'	=> (date('d.m', strtotime($u['Дата рождения'])) == date('d.m') ? true : false)
					);
				}
				unset($orders_users_list);
			}
		}

		# ВЫВОД
		View::assign('%page_title', 'Список заявок');
		View::render('modules/orders/list', TRUE, array(
			'personal'		=> $this->_personal,
			'readonly'		=> (Core::$_data['module_access'] != 'rw' ? true : false),
			'date_s'		=> $this->_date_s,
			'date_e'		=> $this->_date_e,
			'odelivery'		=> $this->_odelivery,
			'citys'			=> $this->_citys,
			'statuses'		=> $this->_statuses,
			'search'		=> $this->_search,
			'orders_count'	=> intval($this->_orders_count),
			'orders'		=> (!empty($orders) ? $orders : ''),
			'orders_users'	=> (!empty($orders_users) ? $orders_users : ''),
			'perpages'		=> $this->_perpages,
			'perpage'		=> $this->_perpage,
			'page_prefix'	=> '?date_s='.$this->_date_s.'&date_e='.$this->_date_e.'&orderby_delivery='.($this->_odelivery ? 'on' : '').'&city='.$this->_city.'&status='.$this->_status.'&search='.$this->_search.'&perpage='.$this->_perpage.'&',
			'pages'			=> Paginator::$_pages
		));
	}

	# ЗАЯВКА
	public function act_show()
	{
		if (
			!empty($this->_params[0]) && ($order_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) &&
			($order = Model::getObject($order_id, TRUE)) && ($order['mother'] == $this->_container_id) && ($order['class_id'] == $this->_class) &&
			(!$this->_personal || ($order['Город'] == $this->_city))
		)
		{
			# ЗАКАЗЧИК
			if (($order_user = Model::getObject($order['Пользователь'], array('Реферал', 'Группа', 'Реквизиты', 'Имя', 'Дата рождения', 'Телефон', 'Примечания'))) && ($order_user['mother'] == $this->_users_container) && ($order_user['class_id'] == $this->_users_class))
			{
				# Реферал заказчика
				if (
					!empty($order_user['Реферал']) && ($referal = Model::getObject($order_user['Реферал'], array('Имя'), "AND o.id!=''")) &&
					($referal['mother'] == $this->_users_container) && ($referal['class_id'] == $this->_users_class)
				)
				{
					$referal = array(
						'id'	 => $referal['id'],
						'name'	 => $referal['Имя'],
						'orders' => number_format(Model::getObjectsCount($this->_container_id, $this->_class, "o.active='1' AND c.f_100='".$referal['id']."' AND c.f_107='Выполнена'"), 0, '.', ' ')
					);
				}

				# Сам заказчик
				$order_user = array(
					'id'			=> $order_user['id'],
					'referal'		=> !empty($referal) ? $referal : false,
					'email'			=> $order_user['name'],
					'name'			=> $order_user['Имя'],
					'details'		=> $order_user['Реквизиты'],
					'group'			=> $order_user['Группа'],
					'birth_date'	=> Helpers::date('j mounth Y', strtotime($order_user['Дата рождения'])),
					'birth'			=> (date('d.m', strtotime($order_user['Дата рождения'])) == date('d.m') ? true : false),
					'phone'			=> $order_user['Телефон'],
					'reg_date'		=> Helpers::date('j mounth Y в H:i', $order_user['c_date']),
					'comment'		=> nl2br($order_user['Примечания']),
					'referals'		=> Model::getObjectsCount($this->_users_container, $this->_users_class, "o.active='1' AND c.f_123='".$order_user['id']."'")
				);
			}

			# СТАТУС ЗАЯВКИ
			$this->_statuses[$order['Статус']] = true;

			# СПОСОБЫ ОПЛАТЫ
			$pay_types = array();
			if (($pay_types_list = Model::$_db->select('fields', "WHERE `id`='119' LIMIT 1", "`atribute`")) && ($pay_types_list = explode("\n", $pay_types_list['atribute'])))
			{
				foreach($pay_types_list as $p) $pay_types[] = array('name'=>trim($p), 'set'=>($p == trim($order['Способ оплаты'])) ? true : false);
			}

			# БЛАГОТВОРИТЕЛЬНОСТЬ
			if (!empty($order['Благотворительность']) && ($charity_obj = Model::getObject($order['Благотворительность'], TRUE)) && ($charity_obj['class_id'] == 7))
			{
				$charity = array(
					'id' 		=> $charity_obj['id'],
					'name'		=> $charity_obj['name'],
					'address'	=> $charity_obj['Значение']
				);
				unset($charity_obj);
			}

			# ЦВЕТА
			if ($colors = Model::getObjects(69284, 20))
			{
				foreach($colors as $c) $this->_colors[$c['id']] = $c['name'];
				unset($colors, $c);
			}

			# Координаты
			$coords = explode(',', $order['Координаты']);

			# ЗАЯВКА
			$order = array(
				'id'					=> $order['id'],
				'date'					=> Helpers::date('j mounth Y в H:i', $order['c_date']),
				'city_id'				=> intval($order['Город']),
				'city'					=> $this->_citys[$order['Город']]['name'],
				'address'				=> $order['Адрес'],
				'phone'					=> $order['Городской телефон'],
				'type'					=> (isset($this->_delivery_types[$order['Тип']]) ? $this->_delivery_types[$order['Тип']] : '<i class="error">Категория не найдена</i>'),
				'coords'				=> (count($coords) == 2) ? $coords[1].'%2C'.$coords[0] : false,
				'delivery_date'			=> Helpers::date('j mounth Y', strtotime($order['Дата доставки'])),
				'delivery_period'		=> isset(Dtime::$_periods[$order['Период доставки']]) ? Dtime::$_periods[$order['Период доставки']]['name'] : '<span class="error">не найден</span>',
				'delivery_price'		=> !empty($order['Стоимость доставки']) ? number_format($order['Стоимость доставки'], 0, '.', ' ') : 0,
				'charity'				=> !empty($charity) ? $charity : false,
				'user'					=> !empty($order_user) ? $order_user : '',
				'items'					=> unserialize($order['Товары']),
				'items_old_total_price'	=> number_format($order['Общая цена'], 0, '.', ' '),
				'items_total_price'		=> number_format((!empty($order['Скидка']) ? ($order['Общая цена'] - ($order['Общая цена'] / 100) * $order['Скидка']) : $order['Общая цена']), 0, '.', ' '),
				'total_price'			=> number_format(((!empty($order['Скидка']) ? ($order['Общая цена'] - ($order['Общая цена'] / 100) * $order['Скидка']) : $order['Общая цена']) + $order['Стоимость доставки']), 0, '.', ' '),
				'discount'				=> intval($order['Скидка']),
				'change'				=> !empty($order['Ожидаемая сдача']) ? number_format($order['Ожидаемая сдача'], 0, '.', ' ') : '',
				'pay_types'				=> $pay_types,
				'pay_type'				=> $order['Способ оплаты'],
				'payed_vars'			=> array(
												1 => array('name'=>'Оплачен', 'set'=>($order['Оплачен'] == 1 ? true : false)),
												0 => array('name'=>'Не оплачен', 'set'=>($order['Оплачен'] == 0 ? true : false))
											),
				'payed'					=> !empty($order['Оплачен']) ? 'Оплачен' : 'Не оплачен',
				'pay_date'				=> (!empty($order['Оплачен']) && !empty($order['Дата оплаты'])) ? Helpers::date('j mounth Y в H:i', strtotime($order['Дата оплаты'])) : '',
				'pay_bank_answer'		=> $order['Ответ банка'],
				'comment'				=> nl2br($order['Комментарий']),
				'status'				=> $order['Статус']
			);

			# ВЫВОД
			View::assign('%page_title', 'Заявка №'.$order_id);
			View::render('modules/orders/order', TRUE, array(
				'readonly'		=> (Core::$_data['module_access'] != 'rw' ? true : false),
				'statuses'		=> $this->_statuses,
				'colors'		=> $this->_colors,
				'order'			=> $order,
				'items_count' 	=> count($order['items']),
			));
		}
		else Helpers::redirect(_MODULES_.'orders/');
	}

	# ОТЧЕТЫ
	public function act_reports()
	{
		if (!empty($this->_params[0]) && ($report_type = $this->_params[0]) && (isset($this->_reports_menu[$report_type]) || $report_type == 'user'))
		{
			# Выбераем в меню
			if ($report_type != 'user') $this->_reports_menu[$report_type]['set'] = true;
			else $this->_reports_menu['users']['set'] = true;

			# Тип отчета
			switch($report_type)
			{
				# ПО ПОЛЬЗОВАТЕЛЯМ
				case 'users':

					# Поля сортировки
					$sort_fields = array(
						'count'	=> array('sort'=>''),
						'sum'	=> array('sort'=>''),
						'avg'	=> array('sort'=>'')
					);

					# Сортировка
					$orderby = 'sum';
					$sort	 = 'desc';
					if (!empty($_GET['orderby']) && isset($sort_fields[$_GET['orderby']]) && !empty($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')))
					{
						$orderby = $_GET['orderby'];
						$sort	 = $_GET['sort'];
					}
					$sort_fields[$orderby]['sort'] = $sort;

					if (($users_report_lnk = Model::$_db->query("
						SELECT
							o.id as 'user_id',
							uc.f_56 as 'user_name',
							uc.f_74 as 'user_group',
							COUNT(oc.id) as 'count',
							SUM(oc.f_111-CEIL(oc.f_111/100*oc.f_112)) as 'sum',
							AVG(oc.f_111-CEIL(oc.f_111/100*oc.f_112)) as 'avg'
						FROM
							`objects` as o
						LEFT JOIN
							`class_".$this->_users_class."` as uc
						ON
							uc.object_id=o.id
						LEFT JOIN
							`class_".$this->_class."` oc
						ON
							oc.f_100=o.id
						WHERE
							o.active='1'
							AND o.mother='".$this->_users_container."'
							AND o.class_id='".$this->_users_class."'
							AND oc.id!=''
							AND oc.f_101='".$this->_city."'
							AND oc.f_107='Выполнена'
						GROUP BY
							o.id
						ORDER BY
							$orderby ".mb_strtoupper($sort)."
						")) && (mysql_num_rows($users_report_lnk) > 0)
					)
					{
						$users_report = array();
						while($ur = mysql_fetch_assoc($users_report_lnk)) {
							$users_report[$ur['user_id']] = array(
								'user_id'		=> $ur['user_id'],
								'user_name'		=> $ur['user_name'],
								'user_url'		=> _MODULES_.'orders/reports/user/'.$ur['user_id'],
								'user_group'	=> $ur['user_group'],
								'count'			=> number_format($ur['count'], 0, '.', ' '),
								'sum'			=> number_format($ur['sum'], 0, '.', ' '),
								'avg'			=> number_format($ur['avg'], 0, '.', ' ')
							);
						}
						View::assign('users_sort_fields', $sort_fields);
						View::assign('users_report', $users_report);
						unset($users_report_lnk, $users_report, $ur);
					}
				break;

				# КОЛИЧЕСТВО ЗАКАЗОВ ПО ПОЛЬЗОВАТЕЛЕЮ
				case 'user':
					if (
						isset($this->_params[1]) && ($user_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						($user_obj = Model::getObject($user_id, true)) && ($user_obj['class_id'] == $this->_users_class)
					)
					{
						# Получаем все заказы пользователя
						if ($user_oreders_list = Model::getObjects($this->_container_id, $this->_class, array('Дата доставки', 'Общая цена'), "c.f_100='$user_id' AND c.f_107='Выполнена' ORDER BY c.f_108 ASC"))
						{
							$user_orders = array();
							foreach($user_oreders_list as $o) $user_orders[] = "['".$o['Дата доставки']."', ".intval($o['Общая цена'])."]";
							unset($user_oreders_list, $o);
						}

						# График
						View::addCSS(_TPL_.'js/jqplot/jquery.jqplot.min.css');
						View::addJS(array(
							_TPL_.'js/jqplot/jquery.jqplot.min.js',
							_TPL_.'js/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
							_TPL_.'js/jqplot/plugins/jqplot.cursor.min.js',
							_TPL_.'js/jqplot/plugins/jqplot.highlighter.min.js',
							_TPL_.'modules/orders/reports/js/charts/user.js',
						));
						View::assign('%page_title', 'Периодичность заказов пользоватлея &laquo;'.$user_obj['Имя'].'&raquo;');
						View::assign('user_orders', (!empty($user_orders) ? '['.join(',', $user_orders).']' : false));
					}
					else Helpers::redirect(_MODULES_.'orders/reports/users');
				break;

				# ПО МЕСЯЦАМ
				case 'mounthes':

					# Массив годов
					$mounthes_report = array();
					for($y=2012; $y<=date('Y'); $y++)
					{
						for($m=1; $m<=12; $m++)
						{
							# Правильный месяц
							$cm = mb_strlen($m) == 1 ? '0'.$m : $m;

							# Отчет за месяц
							if (
								($mounth_report_lnk = Model::$_db->query("SELECT COUNT(id) as 'count', SUM(f_111-CEIL(f_111/100*f_112)) as 'sum', AVG(f_111-CEIL(f_111/100*f_112)) as 'avg' FROM `class_".$this->_class."` WHERE (`f_108` BETWEEN '$y-$cm-01' AND '$y-$cm-31') AND `f_101`='".$this->_city."' AND `f_107`='Выполнена'")) &&
								(mysql_num_rows($mounth_report_lnk) == 1) && ($mounth_report = mysql_fetch_assoc($mounth_report_lnk))
							)
							{
								# Добавляем месяц
								$mounthes_report[$y][$this->_mounthes[$m]] = array(
									'cm'		=> $cm,
									'url'		=> _MODULES_.'orders/?date_s=01.'.$cm.'.'.$y.'&date_e=31.'.$cm.'.'.$y.'&status=Выполнена',
									'count'		=> number_format($mounth_report['count'], 0, '.', ' '),
									'sum'		=> intval($mounth_report['sum']),
									'sum_txt'	=> number_format(intval($mounth_report['sum']), 0, '.', ' '),
									'avg'		=> number_format($mounth_report['avg'], 0, '.', ' ')
								);

								# Дошли до текущего месяца
								if ($cm.'.'.$y == date('m.Y')) break;
							}
						}
					}
					View::assign('mounthes_report', $mounthes_report);

					# График
					View::addCSS(_TPL_.'js/jqplot/jquery.jqplot.min.css');
					View::addJS(array(
						_TPL_.'js/jqplot/jquery.jqplot.min.js',
						_TPL_.'js/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
						_TPL_.'js/jqplot/plugins/jqplot.cursor.min.js',
						_TPL_.'js/jqplot/plugins/jqplot.highlighter.min.js',
						_TPL_.'js/jqplot/plugins/jqplot.pointLabels.min.js',
						_TPL_.'modules/orders/reports/js/charts/monthes.js',
					));
					unset($years, $mounth_report_lnk, $mounth_report);
				break;

				# ЛИДЕРЫ ПРОДАЖ
				case 'liders':

					# Даты
					if (!empty($_GET['date_s']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_s'])) $this->_date_s = $_GET['date_s'];
					if (!empty($_GET['date_e']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_e'])) $this->_date_e = $_GET['date_e'];

					# Период
					if (!empty($_GET['period']) && in_array($_GET['period'], array('day', 'week', 'mounth', 'year')))
					{
						switch ($_GET['period'])
						{
							# День
							case 'day':
								$this->_date_s = date('d.m.Y');
								$this->_date_e = date('d.m.Y');
							break;

							# Неделя
							case 'week':
								$this->_date_s = date('d.m.Y', (strtotime(date('d.m.Y'))-7*86400));
								$this->_date_e = date('d.m.Y');
							break;

							# Месяц
							case 'mounth':
								$this->_date_s = date('d.m.Y', (strtotime(date('d.m.Y'))-31*86400));
								$this->_date_e = date('d.m.Y');
							break;

							# Год
							case 'year':
								$this->_date_s = date('d.m.').intval(date('Y')-1);
								$this->_date_e = date('d.m.Y');
							break;

						}
					}

					# Задаем даты
					View::assign('date_s', $this->_date_s);
					View::assign('date_e', $this->_date_e);

					# ID города
					View::assign('city_id', $this->_city);

					# Каталог товаров города
					if ($catalogs_container = Model::getObjects($this->_city, 0, FALSE, "o.active='1' AND o.name='Каталог товаров' LIMIT 1"))
					{
						# Разделы каталога
						if ($categorys_list = Model::getObjects($catalogs_container['id'], 10, TRUE))
						{
							$categorys = array();
							foreach($categorys_list as $c)
							{
								$categorys[$c['id']] = array(
									'id'	=> $c['id'],
									'name'	=> $c['Название'],
									'set'	=> false
								);
							}

							# Категория выбрана
							if (!empty($_GET['category']) && ($category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT)) && isset($categorys[$category_id]))
							{
								# ID категории
								$categorys[$category_id]['set'] = true;
								View::assign('category_id', $category_id);

								# Каталоги категории
								$catalogs_ids = array();
								if ($cats = Model::getObjects($category_id, 10))
								{
									foreach($cats as $c)
									{
										if ($subcats = Model::getObjects($c['id'], 10))
										{
											foreach($subcats as $sc) $catalogs_ids[] = $sc['id'];
										}
									}
								}

								# Сортировка
								$sort_fields = array(
									'orders_count'	=> array('sort'=>''),
									'count'			=> array('sort'=>''),
									'sum'			=> array('sort'=>'')
								);
								$orderby = 'orders_count';
								$sort	 = 'desc';
								if (!empty($_GET['orderby']) && isset($sort_fields[$_GET['orderby']]) && !empty($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')))
								{
									$orderby = $_GET['orderby'];
									$sort	 = $_GET['sort'];
								}
								$sort_fields[$orderby]['sort'] = $sort;
								View::assign('liders_sort_fields', $sort_fields);

								# Запрос
								$query = "
									SELECT
										#what#
									FROM
										`objects` as o
									LEFT JOIN
										`class_".$this->_items_class."` as c
									ON
										c.object_id=o.id
									LEFT JOIN
										`md_orders_stat` as st
									ON
										st.item_id=o.id
									WHERE
										o.class_id='".$this->_items_class."'
										AND o.mother IN(".join(',', $catalogs_ids).")
										".(!empty($this->_date_s) ? "AND st.date_ts>='".strtotime($this->_date_s)."'" : '')."
										".(!empty($this->_date_e) ? "AND st.date_ts<='".(strtotime($this->_date_e)+86399)."'" : '')."
									GROUP BY
										o.id
								";

								# Количество элементов
								$items_count = Model::$_db->query("
									SELECT
										COUNT('id') as 'items_count'
										FROM (".str_replace('#what#', 'o.id', $query).") as t
								");
								$items_count = mysql_result($items_count, 0, 'items_count');
								View::assign('items_count', $items_count);

								# Пагинатор
								Paginator::setPaginator($items_count, $this->_page, $this->_perpage);
								View::assign('perpages', $this->_perpages);
								View::assign('perpage', $this->_perpage);
								View::assign('page_prefix', '?date_s='.$this->_date_s.'&date_e='.$this->_date_e.'&city='.$this->_city.'&category='.$category_id.'&perpage='.$this->_perpage.'&');
								View::assign('pages', Paginator::$_pages);

								# Товары раздела
								if ((count($items_count) > 0) && ($categroy_items = Model::$_db->query(str_replace('#what#', "o.id, c.f_38 as 'name', c.f_39 as 'image', c.f_42 as 'measure', COUNT(st.id) as 'orders_count', SUM(st.count) as 'count', SUM(st.price) as 'sum'", $query)." ORDER BY $orderby ".mb_strtoupper($sort)." LIMIT ".Paginator::$_start.", ".$this->_perpage)))
								{
									$items = array();
									while($i = mysql_fetch_assoc($categroy_items))
									{
										$items[] = array(
											'id'			=> $i['id'],
											'name'			=> $i['name'],
											'url'			=> '/'.Core::$_lang.'/catalog/item/'.Helpers::hurl_encode($i['id'], $i['name']),
											'image'			=> $i['image'],
											'orders_count'	=> number_format($i['orders_count'], 0, '.', ' '),
											'count'			=> (!empty($i['count']) ? ($i['measure'] == 'кг' ? number_format($i['count'], 2, '.', ' ') : number_format($i['count'], 0, '.', ' ')) : 0).' '.$i['measure'],
											'sum'			=> (!empty($i['sum']) ? number_format($i['sum'], 0, '.', ' ') : 0)
										);
									}
									View::assign('items', $items);
								}
							}
							View::assign('categorys', $categorys);
						}
					}
				break;

				# ПАРТНЕРЫ
				case 'partners':

					# Даты
					if (!empty($_GET['date_s']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_s'])) $this->_date_s = $_GET['date_s'];
					if (!empty($_GET['date_e']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $_GET['date_e'])) $this->_date_e = $_GET['date_e'];

					# Период
					if (!empty($_GET['period']) && in_array($_GET['period'], array('day', 'week', 'mounth', 'year')))
					{
						switch ($_GET['period'])
						{
							# День
							case 'day':
								$this->_date_s = date('d.m.Y');
								$this->_date_e = date('d.m.Y');
							break;

							# Неделя
							case 'week':
								$this->_date_s = date('d.m.Y', (strtotime(date('d.m.Y'))-7*86400));
								$this->_date_e = date('d.m.Y');
							break;

							# Месяц
							case 'mounth':
								$this->_date_s = date('d.m.Y', (strtotime(date('d.m.Y'))-31*86400));
								$this->_date_e = date('d.m.Y');
							break;

							# Год
							case 'year':
								$this->_date_s = date('d.m.').intval(date('Y')-1);
								$this->_date_e = date('d.m.Y');
							break;

						}
					}

					# Задаем даты
					View::assign('date_s', $this->_date_s);
					View::assign('date_e', $this->_date_e);

					# ID города
					View::assign('city_id', $this->_city);

					# Каталог партнеров города
					if ($partners_container = Model::getObjects($this->_city, 0, FALSE, "o.active='1' AND o.name='Партнеры' LIMIT 1"))
					{
						# Партнеры
						if ($partners_list = Model::getObjects($partners_container['id'], 19, FALSE, "o.id!=''"))
						{
							$partners = array();
							foreach($partners_list as $p)
							{
								$partners[$p['id']] = array(
									'id'	=> $p['id'],
									'name'	=> $p['name'],
									'set'	=> false
								);
							}

							# Партнер выбран
							if (!empty($_GET['partner']) && ($partner_id = filter_input(INPUT_GET, 'partner', FILTER_VALIDATE_INT)) && isset($partners[$partner_id]))
							{
								# ID партнера
								$partners[$partner_id]['set'] = true;
								View::assign('partner_id', $partner_id);

								# Сортировка
								$sort_fields = array(
									'orders_count'	=> array('sort'=>''),
									'count'			=> array('sort'=>''),
									'sum'			=> array('sort'=>'')
								);
								$orderby = 'orders_count';
								$sort	 = 'desc';
								if (!empty($_GET['orderby']) && isset($sort_fields[$_GET['orderby']]) && !empty($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')))
								{
									$orderby = $_GET['orderby'];
									$sort	 = $_GET['sort'];
								}
								$sort_fields[$orderby]['sort'] = $sort;
								View::assign('partners_sort_fields', $sort_fields);

								# Запрос
								$query = "
									SELECT
										#what#
									FROM
										`objects` as o
									LEFT JOIN
										`class_".$this->_items_class."` as c
									ON
										c.object_id=o.id
									LEFT JOIN
										`md_orders_stat` as st
									ON
										st.item_id=o.id
									WHERE
										o.class_id='".$this->_items_class."'
										".(!empty($this->_date_s) ? "AND st.date_ts>='".strtotime($this->_date_s)."'" : '')."
										".(!empty($this->_date_e) ? "AND st.date_ts<='".(strtotime($this->_date_e)+86399)."'" : '')."
										AND c.f_126='$partner_id'
									GROUP BY
										o.id
								";

								# Количество элементов
								$items_count = Model::$_db->query("
									SELECT
										COUNT('id') as 'items_count'
										FROM (".str_replace('#what#', 'o.id', $query).") as t
								");
								$items_count = mysql_result($items_count, 0, 'items_count');
								View::assign('items_count', $items_count);

								# Пагинатор
								Paginator::setPaginator($items_count, $this->_page, $this->_perpage);
								View::assign('perpages', $this->_perpages);
								View::assign('perpage', $this->_perpage);
								View::assign('page_prefix', '?date_s='.$this->_date_s.'&date_e='.$this->_date_e.'&city='.$this->_city.'&partner='.$partner_id.'&perpage='.$this->_perpage.'&');
								View::assign('pages', Paginator::$_pages);

								# Товары партнера
								if ((count($items_count) > 0) && ($partner_items = Model::$_db->query(str_replace('#what#', "o.id, c.f_38 as 'name', c.f_39 as 'image', c.f_42 as 'measure', COUNT(st.id) as 'orders_count', SUM(st.count) as 'count', SUM(st.price) as 'sum'", $query)." ORDER BY $orderby ".mb_strtoupper($sort)." LIMIT ".Paginator::$_start.", ".$this->_perpage)))
								{
									$items = array();
									while($i = mysql_fetch_assoc($partner_items))
									{
										$items[] = array(
											'id'			=> $i['id'],
											'name'			=> $i['name'],
											'url'			=> '/'.Core::$_lang.'/catalog/item/'.Helpers::hurl_encode($i['id'], $i['name']),
											'image'			=> $i['image'],
											'orders_count'	=> number_format($i['orders_count'], 0, '.', ' '),
											'count'			=> (!empty($i['count']) ? ($i['measure'] == 'кг' ? number_format($i['count'], 2, '.', ' ') : number_format($i['count'], 0, '.', ' ')) : 0).' '.$i['measure'],
											'sum'			=> (!empty($i['sum']) ? number_format($i['sum'], 0, '.', ' ') : 0)
										);
									}
									unset($partner_items);
									View::assign('items', $items);
								}
							}
							View::assign('partners', $partners);
						}
					}
				break;
			}

			# ВЫВОД
			if ($report_type != 'user') View::assign('%page_title', $this->_reports_menu[$report_type]['name']);
			View::assign('personal', $this->_personal);
			View::assign('citys',  $this->_citys);
			View::assign('report_type', $report_type);
			View::assign('reports_menu', $this->_reports_menu);
			View::render('modules/orders/reports/'.$report_type);
		}
		else Helpers::redirect(_MODULES_.'orders/reports/users');
	}
}