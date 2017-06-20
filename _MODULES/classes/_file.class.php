<?php
# ОБЪЕКТ ФАЙЛ v.1.0
# Coded by ShadoW (C) 2012
defined('_CORE_') or die('Доступ запрещен');

class _File extends Array_Object
{
	private $_class	= 6;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($file_id)
	{
		if (is_numeric($file_id) && ($file = Model::getObject($file_id, TRUE)) && ($file['class_id'] == $this->_class) && file_exists(_ABS_UPLOADS_.$file['Файл']))
		{
			$file_parts = explode('.', $file['Файл']);
			$file_ext	= mb_strtolower(end($file_parts));

			# Собираем
			$this->_array = array(
				'id'		=> $file['id'],
				'mother'	=> $file['mother'],
				'name'		=> $file['Название'],
				'title'		=> str_replace('"', '', $file['Название']),
				'file'		=> $file['Файл'],
				'ext'		=> $file_ext,
				'ico'		=> file_exists(_ABS_ROOT_.'cms'._SEP_.'images'._SEP_.'ext'._SEP_.$file_ext.'.png') ? '/cms/images/ext/'.$file_ext.'.png' : '',
				'downloads'	=> intval($file['Скачиваний'])
			);
			unset($photo);

			return TRUE;
		}

		return FALSE;
	}

	# ДОБАВИТЬ ПРОСМОТР
	public function addDownload()
	{
		return Model::$_db->update('class_'.$this->_class, array('f_18'=>($this->_array['downloads']+1)), "WHERE `object_id`='".$this->_array['id']."' AND `lang`='".Core::$_lang."' LIMIT 1");
	}
}