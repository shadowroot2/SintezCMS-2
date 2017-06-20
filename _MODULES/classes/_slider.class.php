<?php
# СЛАЙДЕР v.1.0
# Coded by ShadoW (c) 2012
defined('_CORE_') or die('Доступ запрещен');

class _Slider extends Array_Object
{
	private $_class = 9;

	public function __construct($container)
	{
		if (filter_var($container, FILTER_VALIDATE_INT) && ($items = Model::getObjects($container, $this->_class, TRUE)))
		{
			foreach($items as $i)
			{
				if (!empty($i['Изображение']))
				{
					$this->_array[] = array(
						'img'		=> _UPLOADS_.$i['Изображение'],
						'header'	=> $i['Заголовок'],
						'text'		=> nl2br($i['Текст']),
						'url'		=> $i['Ссылка']
					);
				}
			}

			if (sizeof($this->_array) > 0) return TRUE;
		}

		return FALSE;
	}
}