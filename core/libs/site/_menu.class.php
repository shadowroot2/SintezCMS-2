<?php
# ОБЪЕКТ ЭЛЕМЕНТОВ МЕНЮ v.2.0
# Coded by ShadoW (C) 2014
defined('_CORE_') or die('Доступ запрещен');
class _Menu extends Array_Object
{
	public function __construct($container, $recursive=false)
	{
		if (filter_var($container, FILTER_VALIDATE_INT) && ($items = Model::getObjects($container)))
		{
			foreach($items as $i)
			{
				if (in_array($i['class'], array('Текстовая страница', 'Ссылка')) && ($item = Model::getObject($i['id'], ($i['class'] == 'Текстовая страница' ? array('Заголовок', 'ЧПУ') : true))))
				{
					# Страница
					if ($item['class'] == 'Текстовая страница')
					{
						$this->_array[$i['id']] = array(
							'name'	=> $item['Заголовок'],
							'url'	=> '/'.Core::$_lang.'/page/show/'.Helpers::hurl_encode($item['id'], ($item['ЧПУ'] != '' ? $item['ЧПУ'] : $item['Заголовок'])),
							'blank'	=> false,
							'set'	=> false
						);
					}

					# Ссылка
					else if ($item['class'] == 'Ссылка')
					{
						$this->_array[$i['id']] = array(
							'name'	=> $item['Название'],
							'url'	=> $item['URL'],
							'blank'	=> $item['В новом окне'] == 1 ? true : false,
							'set'	=> false
						);
					}

					# Подменю
					if ($recursive && ($i['inside'] > 0) && ($submenu_obj = new _Menu($i['id'])))
					{
						$this->_array[$i['id']]['sub'] = $submenu_obj->asArray();
					}
				}
			}

			if (count($this->_array) > 0) return true;
		}

		return false;
	}
}