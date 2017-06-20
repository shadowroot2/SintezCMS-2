<?php
# КОНТРОЛЛЕР НОВОСТЕЙ v.2.2
# Coded by ShadoW (c) 2014
class cnt_News extends Controller
{
	private $_locale;
	private $_menu_id		= 14;

	private $_controller	= 'news';
	private $_container		= 96;
	private $_class			= 2;

	private	$_page			= 1;
	private $_perpage		= 12;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# ЛОКАЛЬ
		$this->_locale = Locales::load('publications');
		View::assign('%locale', $this->_locale);

		# Выбираем в меню
		if (isset(Core::$_data['top_menu'][$this->_menu_id]))
		{
			Core::$_data['top_menu'][$this->_menu_id]['set'] = true;
			View::reassign('%site.top_menu', Core::$_data['top_menu']);
		}

		# Страница
		if (is_int($pg_pos = array_search('pg', $this->_params)) && isset($this->_params[$pg_pos+1]) && ($page = filter_var($this->_params[$pg_pos+1], FILTER_VALIDATE_INT)))
			$this->_page = abs($page);
	}

	# РЕДИРЕКТ В АРХИВ
	public function act_index()
	{
		$this->act_archive();
	}

	# АРХИВ
	public function act_archive()
	{
		# Количество новостей
		if ($items_count = Model::getObjectsCount($this->_container, $this->_class, "o.active='1' AND c.f_7<='".date('Y-m-d')."'"))
		{
			# Пагинатор
			Paginator::setPaginator($items_count, $this->_page, $this->_perpage);

			# Список новостей
			$items_list_obj = new _News_List(Model::getObjects($this->_container, $this->_class, array('Дата', 'Изображение', 'Заголовок', 'ЧПУ', 'Анонс'), "o.active='1' AND c.f_7<='".date('Y-m-d')."' ORDER BY c.f_7 DESC LIMIT ".Paginator::$_start.", ".$this->_perpage), $this->_controller);
		}

		# ВЫВОД
		View::assign('%page_title', $this->_locale['news']);
		View::render('publications/list', true, array(
			'items' 	=> (isset($items_list_obj) && $items_list_obj->count()) ? $items_list_obj->asArray() : false,
			'prefix'	=> '/'.Core::$_lang.'/'.$this->_controller.'/archive',
			'paginator'	=> Paginator::$_pages
		));
	}

	# ПОКАЗАТЬ ПУБЛИКАЦИЮ
	public function act_show()
	{
		if (
			isset($this->_params[0]) && ($item_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT)) &&
			($item = new _Publication($item_id)) && $item->count()
		)
		{
			# Добавляем просмотр
			$item->addView();

			# META
			View::assign('%page_title', $item['name'].' | '.$this->_locale['news']);
			if ($item['keywords'] != '')	View::reassign('%site.keywords', $item['keywords']);
			if ($item['description'] != '')	View::reassign('%site.description', $item['description']);

			# ВЫВОД
			View::render('publications/item', true, array(
				'title'	=> $this->_locale['news'],
				'back'	=> '/'.Core::$_lang.'/'.$this->_controller.'/archive',
				'item'	=> $item->asArray()
			));
		}
		else $this->act_index();
	}
}