<?php
# ОБЪЕКТ ЭЛЕМЕНТОВ КОРЗИНЫ v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');
class _Cart_Items_List extends Array_Object
{
	private $_class  = 12;

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct($cart_items, $full_items)
	{
		if (is_array($cart_items) && is_array($full_items))
		{
			# Копируем корзину в массив
			$this->_array = $cart_items;

			# Создаем массив текущих товаров
			foreach($full_items as $i)
			{
				if (isset($cart_items[$i['id']]) && ($i['class_id'] == $this->_class))
				{
					# Перезаписываем товар полной версией
					$this->_array[$i['id']] = array(
						'id'			=> $i['id'],
						'image'			=> '/image/s/w/72/square/'.$i['Изображение'],
						'name'  		=> $i['Название'],
						'url'			=> '/'.Core::$_lang.'/catalog/item/'.Helpers::hurl_encode($i['id'], ($i['ЧПУ'] != '' ? $i['ЧПУ'] : $i['Название'])),
						'count'			=> (int)$cart_items[$i['id']][0],
						'price'			=> (int)$cart_items[$i['id']][1],
						'total_price'	=> ceil((int)$cart_items[$i['id']][1] * (int)$cart_items[$i['id']][0]),
						'weight'		=> (int)$i['Вес БРУТТО'],
						'total_weight'	=> (int)$cart_items[$i['id']][0] * (int)$i['Вес БРУТТО'],
						'stock'			=> (int)$i['Остаток'],
					);

					# Цена товара поменялась
					if ((int)$cart_items[$i['id']][1] != $i['Цена']) Core::$_data['basket']->change($i['id'], $cart_items[$i['id']][0], $i['Цена']);

					# Убираем из массива (для удаление товаров которых нет)
					unset($cart_items[$i['id']]);
				}
			}

			# Удаляем товары которых нет
			if (count($cart_items) && is_array($remove_items_ids = array_keys($cart_items)))
			{
				foreach($remove_items_ids as $id) unset($this->_array[$id]);
				Core::$_data['basket']->remove($remove_items_ids);
			}

			# Сбрасываем ключи
			$this->_array = array_values($this->_array);

			return true;
		}

		return false;
	}

	# КОЛИЧЕСТВО ТОВАРОВ
	public function count()
	{
		return count($this->_array);
	}
}