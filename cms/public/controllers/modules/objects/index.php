<?php
# МОДУЛЬ ОБЪЕКТОВ v.3.2
# Coded by ShadoW (c) 2014
class cnt_Index extends Controller
{
	private $_root_id	= 0;
	private $_frontend	= false;

	private $_search	= '';

	private $_perpages  = array(30, 50, 100, 200, 500);
	private $_perpage	= 30;
	private $_page		= 1;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) Helpers::redirect(_CMS_.'auth');

		# Запрос с фронтэнда
		if (isset($_POST['frontend'])) $this->_frontend = true;

		# Поиск
		if (isset($_GET['what']) && ($what = Helpers::escape($_GET['what']))) $this->_search = $what;

		# Страница
		if (isset($_GET['pg']) && ($page = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT))) $this->_page = $page;

		# Объектов на страницу
		if (isset($_SESSION['cms_data']['objects']['perpage']) && ($perpage = filter_var($_SESSION['cms_data']['objects']['perpage'], FILTER_VALIDATE_INT)) && in_array($perpage, $this->_perpages))
		{
			$this->_perpage = $perpage;
		}
		else $_SESSION['cms_data']['objects']['perpage'] = $this->_perpage;

		# Языки
		View::assign('%langs', Core::$_langs);

		# Выбранный язык
		if (
			isset($_POST['lang']) && preg_match('#^[a-z]{2}$#', $_POST['lang']) &&
			isset(Core::$_langs[$_POST['lang']]) && is_array(Core::$_langs[$_POST['lang']])
		)
			Core::$_lang = $_SESSION['cms_lang'] = $_POST['lang'];
		else if (
			isset($_SESSION['cms_lang']) && preg_match('#^[a-z]{2}$#', $_SESSION['cms_lang']) &&
			isset(Core::$_langs[$_SESSION['cms_lang']]) && is_array(Core::$_langs[$_SESSION['cms_lang']])
		)
			Core::$_lang = $_SESSION['cms_lang'];

		# Задаем язык
		View::assign('%lang', Core::$_lang);

		# ID корневого раздела
		if (isset(Core::$_data['module_params']) && ($module_params = filter_var(Core::$_data['module_params'], FILTER_VALIDATE_INT))) $this->_root_id = $module_params;
	}

	# СПИСОК ОБЪЕКТОВ КОРНЕВОГО РАЗДЕЛА
	public function act_index()
	{
		# Всего объектов
		$total_objects = Model::getObjectsCount(-1, false, '');

		# Пагинатор
		Paginator::setPaginator(Model::getObjectsCount($this->_root_id, false, ''), $this->_page, $this->_perpage);

		# Список объектов
		$objects_list = Model::getObjects($this->_root_id, false, false, "1=1 ORDER BY o.sort ASC LIMIT ".Paginator::$_start.", ".$this->_perpage);

		# ВЫВОД
		View::assign('%page_title', 'Объекты ('.$total_objects.')');
		View::render('modules/objects/list', true, array(
			'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'clipboard'		=> (isset($_SESSION['cms_clipboard']) && is_array($clipboard = $_SESSION['cms_clipboard'])) ? $clipboard : false,
			'sortable'		=> Core::$_data['module_access'] != 'rw' ? true : false,
			'objects'		=> $objects_list,
			'perpages'		=> $this->_perpages,
			'perpage'		=> $this->_perpage,
			'page_prefix'	=> '?',
			'pages'			=> Paginator::$_pages,
		));
	}

	# СПИСОК ОБЪЕКТОВ ВНУТРИ ОБЪЕКТА
	public function act_s()
	{
		# Получаем объект
		if (isset($this->_params[0]) && ($obj_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($object = Model::getObject($obj_id)))
		{
			# Вложенность
			$mothers	= (array)Model::getMothers($object['mother'], $this->_root_id);
			$mothers[]	= $object->asArray();

			# Пагинатор
			Paginator::setPaginator($object['inside'], $this->_page, $this->_perpage);

			# Список объектов
			$objects_list = Model::getObjects($obj_id, false, false, "1=1 ORDER BY o.sort ASC LIMIT ".Paginator::$_start.", ".$this->_perpage);

			# ВЫВОД
			View::assign('%page_title', $object['name'].($object['inside'] > 0 ? ' ('.$object['inside'].')' : ''));
			View::render('modules/objects/list', true, array(
				'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
				'clipboard'		=> (isset($_SESSION['cms_clipboard']) && is_array($clipboard = $_SESSION['cms_clipboard'])) ? $_SESSION['cms_clipboard'] : false,
				'mothers'		=> $mothers,
				'sortable'		=> Core::$_data['module_access'] != 'rw' ? true : false,
				'object'		=> $object,
				'objects'		=> $objects_list,
				'perpages'		=> $this->_perpages,
				'perpage'		=> $this->_perpage,
				'page_prefix'	=> 's/'.$obj_id.'?',
				'pages'			=> Paginator::$_pages,
			));
		}
		else Helpers::redirect(_MODULES_.'objects/');
	}

	# СПИСОК ОБЪЕКТОВ С ОПРЕДЕЛЕННЫМ ШАБЛОНОМ
	public function act_c()
	{
		# Проверяем шаблон
		if (isset($this->_params[0]) && ($class_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($class = Model::getClass($class_id)))
		{
			# Пагинатор
			$objects_count = Model::$_db->count('class_'.$class_id, "GROUP BY `object_id`", "`id`");
			Paginator::setPaginator($objects_count, $this->_page, $this->_perpage);

			# Список объектов
			if ($objects_count && ($objects_list = Model::$_db->select("`class_$class_id` as c", "LEFT JOIN `objects` as o ON c.object_id=o.id GROUP BY c.object_id ORDER BY o.mother ASC LIMIT ".Paginator::$_start.", ".$this->_perpage, 'o.*')))
			{
				foreach($objects_list as $k=>$o)
				{
					$objects_list[$k]['mothers'] = Model::getMothers($o['mother']);
				}
			}

			# ВЫВОД
			View::assign('%page_title', 'Объекты с шаблоном &laquo;'.$class['name'].'&raquo;');
			View::render('modules/objects/list', true, array(
				'readonly'		=> Core::$_data['module_access'] != 'rw' ? true : false,
				'template_set'	=> true,
				'objects_count'	=> $objects_count,
				'objects'		=> $objects_list,
				'perpages'		=> $this->_perpages,
				'perpage'		=> $this->_perpage,
				'page_prefix'	=> 'c/'.$class_id.'?',
				'pages'			=> Paginator::$_pages,
			));
		}
	}

	# ПОИСК
	public function act_search()
	{
		if ($this->_search != '')
		{
			# По ID или по тексту
			if (filter_var($this->_search, FILTER_VALIDATE_INT)) $query = "o.id='".$this->_search."'";
			else $query = "INSTR(o.name, '".$this->_search."')";

			# Найдено
			$found = Model::$_db->count('objects as o', "WHERE ".$query, "`id`");

			# Пагинатор
			Paginator::setPaginator($found, $this->_page, $this->_perpage);

			# Список объектов
			if ($objects_list = Model::getObjects(-1, false, false, $query." ORDER BY o.id DESC LIMIT ".Paginator::$_start.", ".$this->_perpage))
			{
				$mothers = array();
				foreach($objects_list as $k=>$o)
				{
					if (!isset($mothers[$o['mother']])) $mothers[$o['mother']] = Model::getMothers($o['mother']);
					$objects_list[$k]['mothers'] = $mothers[$o['mother']];
				}
			}

			# ВЫВОД
			View::assign('%page_title', 'Результат поиска по объектам');
			View::render('modules/objects/list', true, array(
				'readonly'		=> ($this->_root_id != 0 ? true : (Core::$_data['module_access'] != 'rw' ? true : false)),
				'search'		=> $this->_search,
				'search_found'	=> $found,
				'objects'		=> $objects_list,
				'perpages'		=> $this->_perpages,
				'perpage'		=> $this->_perpage,
				'page_prefix'	=> 'search?what='.$this->_search.'&',
				'pages'			=> Paginator::$_pages,
			));
		}
		else Helpers::redirect(_MODULES_.'objects/');
	}


	# ДОБАВЛЕНИЕ ОБЪЕКТА
	public function act_add()
	{
		# Название страницы
		View::assign('%page_title', 'Добавление объекта');

		# ПРОВЕРКА ПРАВ ДОСТУПА
		if (Core::$_data['module_access'] != 'rw') exit(View::render('access_denied'));

		# Запрос фронтэнда
		if ($this->_frontend) $_POST['fix_class'] = 0;

		# ДОБАВЛЕНИЕ
		if (
			isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
			isset($_POST['mother']) && is_int($mother_id = filter_input(INPUT_POST, 'mother', FILTER_VALIDATE_INT)) && ($mother_id >= 0) &&
			isset($_POST['class_id']) && is_int($class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT)) && ($class_id >= 0) &&
			(($class_id == 0) || ($class = Model::getClass($class_id))) &&
			isset($_POST['fix_class']) && is_int($fix_class = filter_var($_POST['fix_class'], FILTER_VALIDATE_INT)) && ($fix_class >= 0)
		)
		{
			# ДОБАВЛЯЕМ ОБЪЕКТ
			if ($object_id = Model::addObject(
				array(
					'name'		=> $name,
					'active'	=> isset($_POST['active']) ? 1 : 0,
					'mother'	=> $mother_id,
					'class_id'	=> $class_id,
					'fix_class'	=> $fix_class
				),
				(isset($_POST['fields']) ? $_POST['fields'] : false)
			))
			{
				# Логируем
				if (Core::$_log_actions) Logger::add(1, (isset($class['name']) ? $class['name'] : 'Контейнер'), $_POST['name'], $object_id,  $class_id);

				# Редирект
				if ($this->_frontend) exit('<script type="text/javascript">top.location.reload();</script>');
				else Helpers::redirect(_MODULES_.'objects/s/'.$mother_id);
			}
			else View::assign('%form_error', 'Невозможно добавить объект, проверьте правильность заполнения полей');
		}

		# МАТЕРИНСКИЙ ОБЪЕКТ
		if (isset($this->_params[0]) && ($mother_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($mother_id > 0) && ($mother = Model::getObject($mother_id)) && ($mothers = Model::getMothers($mother_id, $this->_root_id)))
		{
			# Шаблон по умолчанию
			if (($mother['fix_class'] > 0) && ($mother_fix_template = Model::getClass($mother['fix_class'])) && ($mother_fix_template['additional'] == 0))
			{
				View::assign('%template_fields', View::getTemplate('modules/objects/template_fields', false, array('template'=>$mother_fix_template->asArray())));
			}
		}
		elseif (($this->_root_id > 0) && ($mother = Model::getObject($this->_root_id)) && ($mother['fix_class'] > 0) && ($mother_fix_template = Model::getClass($mother['fix_class'])) && ($mother_fix_template['additional'] == 0))
		{
			View::assign('%template_fields', View::getTemplate('modules/objects/template_fields', false, array('template'=>$mother_fix_class->asArray())));
		}
		else $mother = array('id'=>$this->_root_id, 'fix_class'=>'');

		# ВЫВОД
		View::addJS(array(
			_TPL_.'js/ckeditor/ckeditor.js',
			_TPL_.'js/ckeditor/adapters/jquery.js',
			_TPL_.'js/ckfinder/ckfinder.js'
		));
		View::render('modules/objects/add', true, array(
			'mother'	=> $mother,
			'mothers'	=> isset($mothers) ? $mothers : false,
			'templates'	=> Model::getClasses()
		));
	}

	# РЕДАКТИРОВАНИЕ ОБЪЕКТА
	public function act_edit()
	{
		if (isset($this->_params[0]) && ($object_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($object = Model::getObject($object_id, true)) && ($mothers = Model::getMothers($object_id, $this->_root_id)))
		{
			# Название страницы
			View::assign('%page_title', 'Редактирование объекта');

			# ПРОВЕРКА ПРАВ ДОСТУПА
			if (Core::$_data['module_access'] != 'rw') exit(View::render('access_denied'));

			# Запрос фронтэнда
			if ($this->_frontend)
			{
				$_POST['name']		= $object['name'];
				$_POST['class_id']	= $object['class_id'];
				$_POST['fix_class']	= $object['fix_class'];
			}

			# СОХРАНЕНИЕ
			if (
				isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
				isset($_POST['class_id']) && is_int($class_id = filter_var($_POST['class_id'], FILTER_VALIDATE_INT)) && ($class_id >= 0) &&
				isset($_POST['fix_class']) && is_int($fix_class = filter_var($_POST['fix_class'], FILTER_VALIDATE_INT)) && ($fix_class >= 0) &&
				(($class_id == 0) || ($class = Model::getClass($class_id)))
			)
			{
				# Отключение чекбоксов и мультиселектов
				if ($class_id > 0)
				{
					foreach($class['fields'] as $f_id=>$f)
					{
						if (($f['type'] == 'checkbox')		&& !isset($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = 0;
						if (($f['type'] == 'multi-select')	&& !isset($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = '';
						if (($f['type'] == 'multi-link')	&& !isset($_POST['fields'][$f_id])) $_POST['fields'][$f_id] = '';
					}
				}

				# СОХРАНЯЕМ ОБЪЕКТ
				if (Model::editObject(
					array(
						'id'		=> $object_id,
						'name'		=> $name,
						'active'	=> isset($_POST['active']) ? 1 : 0,
						'class_id'	=> $class_id,
						'fix_class'	=> $fix_class
					),
					(isset($_POST['fields']) ? $_POST['fields'] : false)
				))
				{
					# Логируем
					if (Core::$_log_actions) Logger::add(2, (isset($class['name']) != '' ? $class['name'] : 'Контейнер'), $_POST['name'], $object['id'],  $class_id);

					# Перенаправляем
					if ($this->_frontend) exit('<script type="text/javascript">top.location.reload();</script>');
					else Helpers::redirect(_MODULES_.'objects/s/'.$object['mother']);
				}
				else View::assign('%form_error', 'Невозможно сохранить объект, проверьте правильность заполнения полей');
			}

			# ДАТЫ ОБЪЕКТА
			$object['c_date'] = Helpers::date('j mounth Y at H:i', $object['c_date']);
			$object['m_date'] = Helpers::date('j mounth Y at H:i', $object['m_date']);

			# ПОЛЯ ШАБЛОНА
			if (($object['class_id'] > 0) && ($template = Model::getClass($object['class_id'])) && ($template['additional'] == 0))
			{
				View::assign('%template_fields', _TemplateFields::prepare($object, $template->asArray()));
			}

			# ВЫВОД
			View::addJS(array(
				_TPL_.'js/ckeditor/ckeditor.js',
				_TPL_.'js/ckeditor/adapters/jquery.js',
				_TPL_.'js/ckfinder/ckfinder.js'
			));
			View::render('modules/objects/edit', true, array(
				'mothers'		=> Model::getMothers($object['mother']),
				'object'		=> $object,
				'templates'		=> Model::getClasses(),
				'clone_fields'	=> ((Core::$_lang != 'ru') && ($object['class_id'] > 0) && ($object->fieldsCount() == 11)) ? true : false
			));
		}
		else Helpers::redirect(_MODULES_.'objects');
	}

	# МАССОВАЯ ЗАГРУЗКА ФАЙЛОВ
	public function act_mupload()
	{
		if (isset($this->_params[0]) && ($object_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($object = Model::getObject($object_id)))
		{
			View::assign('%page_title', 'Массовая загрузка файлов');

			# ПРОВЕРКА ПРАВ ДОСТУПА
			if (Core::$_data['module_access'] != 'rw') exit(View::render('access_denied'));

			# ВЫВОД
			View::addJS(array(_TPL_.'js/swfupload/swfupload.js', _TPL_.'js/swfupload/jquery.swfupload.js'));
			View::render('modules/objects/mupload', true, array('object'=>$object));
		}
		else Helpers::redirect(_MODULES_.'objects');
	}
}