<?php
# ОБЪЕКТ ФОТОГРАФИЯ v.1.0
# Coded by ShadoW (C) 2012
defined('_CORE_') or die('Доступ запрещен');

class _Photo extends Array_Object
{
	private $_class		= 3;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($photo_id)
	{
		if (is_numeric($photo_id) && ($photo = Model::getObject($photo_id, TRUE)) && ($photo['class_id'] == $this->_class) && file_exists(_ABS_UPLOADS_.$photo['Изображение']))
		{
			# Собираем
			$this->_array = array(
				'id'			=> $photo['id'],
				'mother'		=> $photo['mother'],
				'name'			=> $photo['Название'],
				'title'			=> str_replace('"', '', $photo['Название']),
				'file'			=> $photo['Изображение'],
				'views_count'	=> intval($photo['Просмотры'])
			);
			unset($photo);

			return TRUE;
		}

		return FALSE;
	}

	# ДОБАВИТЬ ПРОСМОТР
	public function addView()
	{
		return Model::$_db->update('class_'.$this->_class, array('f_15'=>($this->_array['views_count']+1)), "WHERE `object_id`='".$this->_array['id']."' AND `lang`='".Core::$_lang."' LIMIT 1");
	}
}