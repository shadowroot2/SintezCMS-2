<?php
# КОНТРОЛЛЕР ПОИСК v.1.0
# Coded by ShadoW (c) 2012
class cnt_Search extends Controller
{
	private $_title		= 'Результаты поиска';

	private $_what		= '';
	private $_found		= 0;
	private $_places	= array();

	private $_page		= 1;
	private $_perpage	= 10;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# ЧТО ИСКАТЬ
		if (!empty($_GET['search']) && ($what = Helpers::escape($_GET['search'])) && (mb_strlen($what) >= 3)) $this->_what = $what;
		View::assign('%search', $this->_what);

		# ГДЕ ИСКАТЬ
		$this->_places = array(
			'Страницы сайта'=>array(
				'container' => -1,
				'class'		=> 1,
				'fields'	=> array(1,2),
				'sql'		=> '',
				'header'	=> 'Заголовок',
				'text'		=> 'Текст',
				'chars' 	=> 150,
				'url'		=> 'page/show',
			),
			'Новости'=>array(
				'container' => 21,
				'class'		=> 2,
				'fields'	=> array(5,9),
				'sql'		=> '',
				'header'	=> 'Заголовок',
				'text'		=> 'Анонс',
				'chars' 	=> 0,
				'url'		=> 'news/show',
			),
			'Товары каталога'=>array(
				'container' => -1,
				'class'		=> 10,
				'fields'	=> array(121,125),
				'sql'		=> '',
				'header'	=> 'Название',
				'text'		=> 'Анонс',
				'chars' 	=> 0,
				'url'		=> 'catalog/item',
			)
		);
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		$this->act_show();
	}

	# ОТОБРАЖЕНИЕ РЕЗУЛЬТАТОВ
	public function act_show()
	{
		if (!empty($this->_what))
		{
			$results = array();
			foreach($this->_places as $place=>$place_cfg)
			{
				# Поля поиска
				$fields_sql = array();
				foreach($place_cfg['fields'] as $f_id) $fields_sql[] = "c.f_".$f_id." LIKE '%".$this->_what."%'";

				# Ищем...
				if ($items_list = Model::getObjects($place_cfg['container'], $place_cfg['class'], array($place_cfg['header'], $place_cfg['text']), "o.active='1' ".$place_cfg['sql']." AND (".join(' OR ', $fields_sql).")  ORDER BY o.sort DESC"))
				{
					$this->_found += count($items_list);

					if (!isset($results[$place])) $results[$place] = array('fount'=>count($items_list), 'items'=>array());
					foreach ($items_list as $i)
					{
						$results[$place]['items'][] = array(
							'id'		=> $i['id'],
							'mother'	=> $i['mother'],
							'name'		=> $i[$place_cfg['header']],
							'title'		=> str_replace('"', '', $i[$place_cfg['header']]),
							'url'		=> '/'.Core::$_lang.'/'.$place_cfg['url'].'/'.Helpers::hurl_encode($i['id'], $i[$place_cfg['header']]),
							'text'		=> !empty($i[$place_cfg['chars']]) ? mb_substr(strip_tags($i[$place_cfg['text']]), 0, $i[$place_cfg['chars']]) : strip_tags($i[$place_cfg['text']])
						);
					}
					unset($fields_sql, $items_list);
				}
			}
		}

		# ВЫВОД
		View::assign('%page_title', $this->_title);
		View::render('search/list', TRUE, array(
			'found'				=> $this->_found,
			'results'			=> !empty($results) ? $results : false,
			'pagination_prefix'	=> '/'.Core::$_lang.'/search/show?what='.$this->_what
		));
	}
}