<?php
# ОБЪЕКТ ПУБЛИКАЦИЯ v.2.0
# Coded by ShadoW (c) 2014
defined('_CORE_') or die('Доступ запрещен');
class _Publication extends Array_Object
{
	private $_class = 2;

	public function __construct($pub_id)
	{
		if (($pub_id = filter_var($pub_id, FILTER_VALIDATE_INT)) && ($pub_obj = Model::getObject($pub_id, true, false)) && ($pub_obj['active'] == 1) && ($pub_obj['class_id'] == $this->_class))
		{
			$date_ts = strtotime($pub_obj['Дата']);

			$this->_array = array(
				'id'			=> $pub_obj['id'],
				'mother'		=> $pub_obj['mother'],
				'name'			=> $pub_obj['Заголовок'],
				'ts'			=> $date_ts,
				'date_stamp'	=> date('Y-m-d', $date_ts),
				'date'			=> Helpers::date('j mounth Y', $date_ts),
				'image'			=> $pub_obj['Изображение'] != '' ? '/image/s/w/894/'.$pub_obj['Изображение'] : false,
				'text'			=> $pub_obj['Текст'],
				'views_count'	=> (int)$pub_obj['Просмотров'],
				'keywords'		=> $pub_obj['keywords']		!= '' ? str_replace('"', '', $pub_obj['keywords'])		: '',
				'description'	=> $pub_obj['description']	!= '' ? str_replace('"', '', $pub_obj['description'])	: ''
			);
			unset($pub_obj);

			return true;
		}

		return false;
	}

	# ДОБАВИТЬ ПРОСМОТР
	public function addView()
	{
		return Model::$_db->query("UPDATE `class_".$this->_class."` SET `f_78`=f_78+1 WHERE `object_id`='".$this->_array['id']."' AND `lang`='".Core::$_lang."' LIMIT 1");
	}
}