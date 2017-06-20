<?php
# ОБЪЕКТ БАННЕР v.1.2
# Coded by ShadoW (C) 2012
defined('_CORE_') or die('Доступ запрещен');

class _Banner extends Array_Object
{
	private $_class = 17;

	public function __construct($container_name)
	{
		# Контейнеры
		if (empty(Core::$_data['site']['banner_containers']))
		{
			if ($containers = Model::getObjects(Core::$_data['site']['banners'], 0))
			{
				foreach($containers as $c) Core::$_data['site']['banner_containers'][$c['name']] = $c['id'];
			}
		}

		# Контейнер найден
		if (
			isset(Core::$_data['site']['banner_containers'][$container_name]) &&
			($banner = Model::getObjects(Core::$_data['site']['banner_containers'][$container_name], $this->_class, TRUE, "o.active='1' AND c.f_134<='".date('Y-m-d')."' AND c.f_135>='".date('Y-m-d')."' AND (c.f_136='0' OR c.f_132<c.f_136) AND (c.f_137='0' OR c.f_133<c.f_137) ORDER BY RAND() LIMIT 1"))
		)
		{
			$this->_array = array(
				'id'		=> $banner['id'],
				'content'	=> '<div class="banner" rel="'.$banner['id'].'">'.(empty($banner['HTML']) ? '<a href="'.$banner['Ссылка'].'" title="'.str_replace('"', '', $banner['Название']).'"'.(!empty($banner['В новом окне']) ? ' target="_blank"' : '').'><img src="'._UPLOADS_.$banner['Изображение'].'" /></a>' : $banner['HTML']).'</div>'
			);

			# Добавляем просмотр
			Banner_Logger::addShow($banner['id']);
			Model::$_db->update('class_'.$this->_class, array('f_132'=>($banner['Показов']+1)), "WHERE `object_id`='".$banner['id']."' AND `lang`='ru' LIMIT 1");

			return TRUE;
		}

		return FALSE;
	}
}