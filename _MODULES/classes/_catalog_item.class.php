<?
# ОБЪЕКТ ЭЛЕМЕНТ КАТАЛОГА v.1.0
# Coded by ShadoW (C) 2011
defined('_CORE_') or die('Доступ запрещен');

class _Catalog_Item extends Array_Object
{
	private $_class = 10;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($item_id)
	{
		if (($item_id = filter_var($item_id, FILTER_VALIDATE_INT)) && ($item = Model::getObject($item_id, TRUE)) && ($item['class_id'] == $this->_class))
		{
			# ЗАДАЕМ МАССИВ
			$this->_array = array(
				'id'			=> $item['id'],
				'mother'		=> $item['mother'],
				'name'			=> $item['Название'],
				'text'			=> $item['Описание'],
				'price'			=> !empty($item['Цена']) ? number_format($item['Цена'], 0, '.', ' ').' '.$item['Цена в'] : '',
				'views_count'	=> intval($item['Просмотров'])
			);

			return TRUE;
		}

		return FALSE;
	}

	# ДОБАВИТЬ ПРОСМОТР
	public function addView()
	{
		return Model::$_db->update('class_'.$this->_class, array('f_129'=>($this->_array['views_count']+1)), "WHERE `object_id`='".$this->_array['id']."' AND `lang`='".Core::$_lang."' LIMIT 1");
	}
}