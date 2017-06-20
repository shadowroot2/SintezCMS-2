<?php
# КОНТРОЛЛЕР СТРАНИЦ v.1.0
# Coded by ShadoW (c) 2012
class cnt_Page extends Controller
{
	private $_page = 1;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Страница
		if (($pg_pos = array_search('pg', $this->_params)) && !empty($this->_params[$pg_pos+1]) && ($page = filter_var($this->_params[$pg_pos+1], FILTER_VALIDATE_INT)))
		{
			$this->_page = $page;
		}
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		exit(header('Location: /'));
	}

	# ОТОБРАЖЕНИЕ СТРАНИЦЫ
	public function act_show()
	{
		if (!empty($this->_params[0]) && ($page_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT)) && ($page = new _Page($page_id, $this->_page)))
		{		
			# META
			View::assign('%page_title', $page['title']);
			if (!empty($item['keywords']))		View::reassign('%site.keywords', $page['keywords']);
			if (!empty($item['description']))	View::reassign('%site.description', $page['description']);

			View::render('page', TRUE, array('page'=>$page->asArray()));
		}
		else $this->act_index();
	}
}