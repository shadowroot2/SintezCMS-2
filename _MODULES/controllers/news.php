<?php
# КОНТРОЛЛЕР НОВОСТЕЙ v.1.0
# Coded by ShadoW (с) 2012
class cnt_News extends Controller
{
	private $_title			= 'Новости';
	private $_controller	= 'news';

	private $_container 	= 21;
	private $_class			= 2;

	private	$_page 			= 1;
	private $_perpage		= 10;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		View::assign('%page_title', $this->_title);

		# Страница
		if (is_numeric($pg_pos = array_search('pg', $this->_params)) && !empty($this->_params[$pg_pos+1]) && ($page = filter_var($this->_params[$pg_pos+1], FILTER_VALIDATE_INT)))
		{
			$this->_page = $page;
		}
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		$this->act_archive();
	}

	# АРХИВ
	public function act_archive()
	{
		# Страницы
		Paginator::setPaginator(Model::getObjectsCount($this->_container, $this->_class), $this->_page, $this->_perpage);

		# Страница элементов
		$items = new _News_List(Model::getObjects($this->_container, $this->_class, array('Изображение', 'Дата', 'Заголовок', 'Анонс'), "o.active='1' ORDER BY c.f_7 DESC LIMIT ".Paginator::$_start.", ".$this->_perpage), $this->_controller);

		# Вывод
		View::render('news/list', TRUE, array(
			'items'			=> !empty($items) ? $items->asArray() : false,
			'pagination'	=> Paginator::$_pages,
			'page_prev'		=> ($this->_page > 1 ? '/'.Core::$_lang.'/'.$this->_controller.'/archive/pg/'.($this->_page - 1) :'#'),
			'page_next'		=> ($this->_page < sizeof(Paginator::$_pages) ? '/'.Core::$_lang.'/'.$this->_controller.'/archive/pg/'.($this->_page + 1) :'#'),
		));
	}

	# НОВОСТЬ
	public function act_show()
	{
		if (
			!empty($this->_params[0]) && ($item_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT))
			&& ($item = new _Publication($item_id)) && ($item['mother'] == $this->_container)
		)
		{
			# Добавляем просмотр
			$item->addView();

			# META
			View::assign('%page_title', $this->_title.' &mdash; '.$item['title']);
			if (!empty($item['keywords']))		View::reassign('%site.keywords', $item['keywords']);
			if (!empty($item['description']))	View::reassign('%site.description', $item['description']);

			# Вывод
			View::render('news/item', TRUE, array('item' => $item->asArray()));
		}
		else header('Location: /');
	}
}