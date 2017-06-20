<?php
# ОБЪЕКТ СПИСОК ТОВАРОВ v.1.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class _Items_List extends Array_Object
{
	private $_class			= 12;
	private $_items_mothers	= array();

	public function __construct($items, $type=1, $filter_str='')
	{
		if (is_array($items))
		{
			foreach($items as $i)
			{
				if (isset($i['class_id']) && ($i['class_id'] == $this->_class) && isset($i['mother']) && ($i['mother'] > 0))
				{
					# Каталог не указан
					if ($type == 1)
					{
						if (isset($this->_items_mothers[$i['mother']])) $item_catalog =& $this->_items_mothers[$i['mother']];
						else if ($item_catalog_obj = Model::getObject($i['mother'], array('Название', 'ЧПУ'), false))
						{
							$item_catalog = $this->_items_mothers[$i['mother']] = array(
								'id'	=> $i['mother'],
								'name'	=> $item_catalog_obj['Название'],
								'url'	=> '/'.Core::$_lang.'/catalog/cat/'.Helpers::hurl_encode($i['mother'], ($item_catalog_obj['ЧПУ'] != '' ? $item_catalog_obj['ЧПУ'] : $item_catalog_obj['Название']))
							);
							unset($item_catalog_obj);
						}
					}

					$this->_array[$i['id']] = array(
						'id'				=> $i['id'],
						'catalog'			=> isset($item_catalog) ? $item_catalog : false,
						'part_num'			=> $i['Артикул'],
						'compare'			=> $type == 2 ? true : false,
						'compare_status'	=> ($type == 2 && isset($_SESSION['compare'][$i['mother']][$i['id']])) ? 'on' : 'off',
						'name'  			=> (($type == 3) && (mb_strlen($i['Название']) > 30)) ? mb_substr($i['Название'], 0, 30).'...' : $i['Название'],
						'url'				=> '/'.Core::$_lang.'/catalog/item/'.Helpers::hurl_encode($i['id'], ($i['ЧПУ'] != '' ? $i['ЧПУ'] : $i['Название'])).($filter_str != '' ? '?'.$filter_str : ''),
						'image'				=> $type != 3 ? '/image/s/h/140/'.$i['Изображение'] : '/image/s/h/50/'.$i['Изображение'],
						'anounce'			=> nl2br($i['Анонс']),
						'weight'			=> (int)$i['Вес БРУТТО'],
						'price'				=> (int)$i['Цена'],
						'price_txt'			=> number_format((int)$i['Цена'], 0, '.', ' '),
						'old_price'			=> (int)$i['Старая цена'],
						'old_price_txt'		=> number_format((int)$i['Старая цена'], 0, '.', ' ')
					);
				}
			}

			if (count($this->_array)) return true;
		}

		return true;
	}
}