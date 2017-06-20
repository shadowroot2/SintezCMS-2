<?php
# ОБЪЕКТ СПИСОК ПУБЛИКАЦИЙ v.2.1
# Coded by ShadoW (c) 2014
defined('_CORE_') or die('Доступ запрещен');

class _News_List extends Array_Object
{
	private $_class = 2;

	public function __construct($items, $controller='news', $on_main=false)
	{
		if (is_array($items))
		{
			foreach($items as $i)
			{
				if (isset($i['class_id']) && ($i['class_id'] == $this->_class))
				{
					$date_ts = strtotime($i['Дата']);
					$this->_array[] = array(
						'id'		 => $i['id'],
						'date_stamp' => date('Y-m-d', $date_ts),
						'date'		 => Helpers::date('j mounth Y', $date_ts),
						'image'		 => $i['Изображение'] != '' ? _UPLOADS_.$i['Изображение'] : '',
						'name'  	 => $i['Заголовок'],
						'url'		 => '/'.Core::$_lang.'/'.$controller.'/show/'.Helpers::hurl_encode($i['id'], ($i['ЧПУ'] != '' ? $i['ЧПУ'] : $i['Заголовок'])),
						'anounce'	 => nl2br($i['Анонс'])
					);
				}
			}

			if (count($this->_array)) return true;
		}

		return true;
	}
}