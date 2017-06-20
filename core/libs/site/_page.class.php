<?php
# ОБЪЕКТ СТРАНИЦА v.2.4
# Coded by ShadoW (c) 2015
defined('_CORE_') or die('Доступ запрещен');
class _Page extends Array_Object
{
	private $_class			= 1;
	private $_perpage		= 15;

	private $_map_class		= 10;
	private $_map_field		= 163;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($page_id, $pg=1)
	{
		if (($page_id = filter_var($page_id, FILTER_VALIDATE_INT)) && ($page = Model::getObject($page_id, true)) && ($page['active'] == 1) && ($page['class_id'] == $this->_class))
		{
			# Собираем
			$this->_array = array(
				'id'			=> $page['id'],
				'mother'		=> $page['mother'],
				'name'			=> $page['Заголовок'],
				'text'			=> $page['Текст'],
				'keywords'		=> $page['keywords']	!= '' ? str_replace('"', '', $page['keywords'])		: '',
				'description'	=> $page['description']	!= '' ? str_replace('"', '', $page['description'])	: '',
				'images'		=> false,
				'map'			=> false
			);

			if ($page['inside'] > 0)
			{
				# ГАЛЕРЕЯ
				Helpers::lang_sw('ru');
				/*if (($page['inside'] > 0) && ($images_list = Model::getObjects($page['id'], 3, true, "o.active='1' AND c.f_14!='' ORDER BY o.sort ASC")))
				{
					$this->_array['images'] = array();
					foreach ($images_list as $i)
					{
						$this->_array['images'][] = array(
							'id'	=> $i['id'],
							'name'	=> $i['Название'],
							'url'	=> _UPLOADS_.$i['Изображение'],
							'image'	=> '/image/s/w/200/'.$i['Изображение']
						);
					}
					unset($images_list, $i);
				}*/

				# КАРТА
				if (($page['inside'] > 0) && ($map = Model::getObjects($page['id'], $this->_map_class, true, "o.active='1' AND c.f_".$this->_map_field."!='' ORDER BY o.sort ASC LIMIT 1")))
				{
					$coordszoom = explode(':', $map['Координаты']);
					$this->_array['map'] = array(
						'coords'	=> $coordszoom[0],
						'zoom'		=> isset($coordszoom[1]) ? (int)$coordszoom[1] : 12
					);

					# JS
					View::addJS(array(
						'https://maps.google.com/maps/api/js?sensor=false',
						_TPL_.'google_maps.js'
					));
				}
				Helpers::lang_sw();
			}

			return true;
		}

		return false;
	}
}