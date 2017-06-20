<?php
# КАТАЛОГ v.1.0
# Coded by ShadoW (с) 2012
class cnt_Catalog extends Controller
{
	private $_title				= 'Каталог';
	private $_controller		= 'catalog';

	private $_container			= 46;

	private $_catalogs_class	= 7;
	private $_class				= 10;

	private $_perpage			= 10;
	private $_page				= 1;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Страница
		if (is_numeric($pg_pos = array_search('pg', $this->_params)) && !empty($this->_params[$pg_pos+1]) && ($page = filter_var($this->_params[$pg_pos+1], FILTER_VALIDATE_INT)))
		{
			$this->_page = $page;
		}
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		exit(header('Location: /'));
	}

	# РАЗДЕЛ
	public function act_cat()
	{
		if (
			isset($this->_params[0]) && ($cat_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT))
			&& ($cat = Model::getObject($cat_id, TRUE)) && ($cat['class_id'] == $this->_catalogs_class)
		)
		{
			# Путь
			$path = array();
			if ($mothers = Model::getMothers($cat['mother'], $this->_container))
			{
				foreach($mothers as $m)
				{
					$path[$m['name']] = array(
						'name'	=> $m['name'],
						'title'	=> str_replace('"', '', $m['name']),
						'url'	=> '/'.Core::$_lang.'/'.$this->_controller.'/cat/'.Helpers::hurl_encode($m['id'], $m['name']),
					);
				}
			}

			# Подкаталоги
			$subcats = new _Catalog_Cats(Model::getObjects($cat_id, $this->_catalogs_class, TRUE));

			# Страницы элементов
			Paginator::setPaginator(Model::getObjectsCount($cat_id, $this->_class), $this->_page, $this->_perpage);

			# Страница элементов
			$items = new _Catalog_Items(Model::getObjects($cat_id, $this->_class, array('Название', 'Изображение', 'Цена', 'Анонс'), "o.active='1' ORDER BY o.sort DESC LIMIT ".Paginator::$_start.", ".$this->_perpage));

			# Вывод
			View::assign('%page_title', $this->_title.(!empty($path) ? ' &mdash; '.join(' &mdash; ', array_keys($path)) : '' ).' &mdash; '.$cat['Значение']);
			View::render('catalog/list', TRUE, array(
				'cat_name'		=> $cat['Значение'],
				'path'			=> $path,
				'cats'			=> !empty($subcats) ? $subcats->asArray() : '',
				'items'			=> !empty($items) ? $items->asArray() : '',
				'pagination'	=> Paginator::$_pages,
				'page_prefix'	=> '/'.Core::$_lang.'/'.$this->_controller.'/cat/'.Helpers::hurl_encode($cat_id, $cat['Значение']).'/pg/',
			));
		}
		else $this->act_index();
	}

	# ТОВАР
	public function act_item()
	{
		if (
			!empty($this->_params[0]) && ($item_id = filter_var(Helpers::hurl_decode($this->_params[0]), FILTER_VALIDATE_INT))
			&& ($item = new _Catalog_Item($item_id))
			&& ($mothers = Model::getMothers($item['mother'], $this->_container))
		)
		{
			# Добавляем просмотр
			$item->addView();

			# Путь
			$path = array();
			foreach($mothers as $m)
			{
				$path[$m['name']] = array(
					'name'	=> $m['name'],
					'title'	=> str_replace('"', '', $m['name']),
					'url'	=> '/'.Core::$_lang.'/'.$this->_controller.'/cat/'.Helpers::hurl_encode($m['id'], $m['name']),
				);
			}

			# Вывод
			View::assign('%page_title', $this->_title.' &mdash; '.join(' &mdash; ', array_keys($path)).' &mdash; '.$item['name']);
			View::render('catalog/item', TRUE, array(
				'path'	=> $path,
				'item'	=> $item->asArray()
			));
		}
		else $this->act_index();
	}
}