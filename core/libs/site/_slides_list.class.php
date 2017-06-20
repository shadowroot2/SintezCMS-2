<?php
# ОБЪЕКТ СПИСОК СЛАЙДОВ v.2.0
# Coded by ShadoW (C) 2014
defined('_CORE_') or die('Доступ запрещен');

class _Slides_List extends Array_Object
{
	private $_class			= 10;
	private $_image_field	= 161;

	public function __construct($container)
	{
		Helpers::lang_sw('ru');
		if ($slides_list = Model::getObjects($container, $this->_class, "o.active='1' AND c.f_".$this->_image_field."!='' ORDER BY o.sort ASC"))
		{
			foreach($slides_list as $s)
			{
				$this->_array[] = array(
					'id'	=> $s['id'],
					'name'  => $s['Название'],
					'image'	=> _UPLOADS_.$s['Изображение']
				);
			}
			unset($slides_list, $s);

			if (count($this->_array)) return true;
		}
		Helpers::lang_sw();

		return false;
	}
}