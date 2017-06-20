<?php
# ОБЪЕКТ ЭЛЕМЕНТОВ КАТАЛОГА v.1.0
# Coded by ShadoW (C) 2012
defined('_CORE_') or die('Доступ запрещен');

class _Catalog_Items extends Array_Object
{
	private $_class = 10;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($items, $controller='catalog')
	{
		if (is_array($items))
		{
			foreach($items as $i)
			{
				if ($i['class_id'] == $this->_class)
				{
					$this->_array[$i['id']] = array(
						'id'		=> $i['id'],
						'name'  	=> $i['Название'],
						'title' 	=> str_replace('"', '', $i['Название']),
						'url'		=> '/'.Core::$_lang.'/'.$controller.'/item/'.Helpers::hurl_encode($i['id'], $i['Название']),
						'image'		=> !empty($i['Изображение']) ? '/image/s/'.$i['Изображение'].'/w/190/square' : '',
						'desc'		=> nl2br($i['Анонс']),
						'price'		=> !empty($i['Цена']) ? number_format($i['Цена'], 0, '.', ' ') :'',
					);
				}
			}

			return TRUE;
		}

		return FALSE;
	}
}