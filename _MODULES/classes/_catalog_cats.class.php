<?php
# ОБЪЕКТ ПОДКАТАЛОГОВ КАТАЛОГА v.1.0
# Coded by ShadoW (C) 2012
defined('_CORE_') or die('Доступ запрещен');

class _Catalog_Cats extends Array_Object
{
	private $_class = 7;

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
						'name'  	=> $i['Значение'],
						'title' 	=> str_replace('"', '', $i['Значение']),
						'url'		=> '/'.Core::$_lang.'/'.$controller.'/cat/'.Helpers::hurl_encode($i['id'], $i['Значение'])
					);
				}
			}

			return TRUE;
		}

		return FALSE;
	}
}