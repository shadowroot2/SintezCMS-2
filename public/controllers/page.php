<?php
# КОНТРОЛЛЕР СТРАНИЦ v.2.1
# Coded by ShadoW (c) 2014
class cnt_Page extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init(){}

	# РЕДИРЕКТ
	public function act_index()
	{
		Helpers::redirect('/'.Core::$_lang.'/404');
	}

	# ОТОБРАЖЕНИЕ СТРАНИЦЫ
	public function act_show()
	{
		if (
			isset($this->_params[0]) && ($page_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT)) && ($page_id > 0) &&
			($page = new _Page($page_id)) && $page->count() && ($page = $page->asArray())
		)
		{
			# Выбираем в меню
			if (isset(Core::$_data['top_menu'][$page_id]))
			{
				Core::$_data['top_menu'][$page_id]['set'] = true;
				View::reassign('%site.top_menu', Core::$_data['top_menu']);
			}
			else if (isset(Core::$_data['top_menu'][$page['mother']]))
			{
				Core::$_data['top_menu'][$page['mother']]['set'] = true;
				View::reassign('%site.top_menu', Core::$_data['top_menu']);
			}

			# META
			View::assign('%page_title', $page['name']);
			if ($page['keywords'] != '')	View::reassign('%site.keywords', $page['keywords']);
			if ($page['description'] != '')	View::reassign('%site.description', $page['description']);

			# ВЫВОД
			View::render('page', true, array(
				'page' => $page
			));
		}
		else $this->act_index();
	}
}