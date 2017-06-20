<?php
# ОБЪЕКТ БАННЕР v.2.0
# Coded by ShadoW (c) 2014
defined('_CORE_') or die('Доступ запрещен');
class _Banner extends Array_Object
{
	private $_container				= 249;
	private $_class					= 26;
	private $_start_field			= 274;
	private $_end_field				= 275;
	private $_shows_limit_field		= 276;
	private $_shows_field			= 278;
	private $_clicks_limit_field	= 277;
	private $_clicks_field			= 279;

	public function __construct($container_name)
	{
		# Контейнеры
		if (!isset(Core::$_data['site']['banner_containers']))
		{
			if ($containers = Model::getObjects($this->_container, 0, "o.active='1' AND o.inside>'0' ORDER BY o.sort ASC"))
			{
				foreach($containers as $c) Core::$_data['site']['banner_containers'][$c['name']] = $c['id'];
			}
		}

		# Контейнер найден
		if (
			isset(Core::$_data['site']['banner_containers'][$container_name]) &&
			($banner = Model::getObjects(Core::$_data['site']['banner_containers'][$container_name], $this->_class, TRUE, "o.active='1' AND c.f_".$this->_start_field."<='".date('Y-m-d')."' AND c.f_".$this->_end_field.">='".date('Y-m-d')."' AND (c.f_".$this->_shows_limit_field."='0' OR c.f_".$this->_shows_field."<c.f_".$this->_shows_limit_field.") AND (c.f_".$this->_clicks_limit_field."='0' OR c.f_".$this->_clicks_field."<c.f_".$this->_clicks_limit_field.") ORDER BY RAND() LIMIT 1"))
		)
		{
			$this->_array = array(
				'id'		=> $banner['id'],
				'content'	=> '<div class="banner" rel="'.$banner['id'].'">'.(mb_strlen($banner['HTML']) >= 10 ? $banner['HTML'] : '<a href="'.$banner['Ссылка'].'" title="'.$banner['Название'].'"'.($banner['В новом окне'] == 1 ? ' target="_blank"' : '').'><img src="'._UPLOADS_.$banner['Изображение'].'" alt="'.$banner['Название'].'" /></a>').'</div>'
			);

			# Добавляем просмотр
			Banner_Logger::addShow($banner['id']);
			Model::$_db->update('class_'.$this->_class, array('f_'.$this->_shows_field => ((int)$banner['Показов']+1)), "WHERE `object_id`='".$banner['id']."' AND `lang`='ru' LIMIT 1");

			return true;
		}

		return false;
	}
}