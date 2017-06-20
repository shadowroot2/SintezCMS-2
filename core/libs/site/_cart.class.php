<?php
# ОБЪЕКТ КОРЗИНА v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class _Cart extends Singleton
{
	public $_data = array();

	# ИНИЦИАЛИЗАЦИЯ
	public function __construct()
	{
		if (isset(Core::$_data['user']->_data['basket']) && (Core::$_data['user']->_data['basket'] != ''))
			$this->_data = unserialize(Core::$_data['user']->_data['basket']);
	}

	# СВОДНАЯ ИНФОРМАЦИЯ
	public function info()
	{
		$count			= count($this->_data);
		$total_price	= 0;

		foreach($this->_data as $i) $total_price += $i[0]*$i[1];

		return array(
			'count'	=> $count,
			'price'	=> number_format(ceil($total_price), 0, '.', ' ')
		);
	}

	# ПРОВЕРКА НАЛИЧИЯ В КОРЗИНЕ
	public function check($id)
	{
		if (isset($this->_data[(int)$id])) return true;
		return false;
	}

	# ПОЛУЧЕНИЕ СПИСКА ЭЛЕМЕНТОВ КОРЗИНЫ
	public function get_list()
	{
		return $this->_data;
	}

	# ДОБАВЛЕНИЕ
	public function add($id, $quantiti, $price)
	{
		if (count($this->_data) < 100)
		{
			# Добавляем
			$this->_data[(int)$id] = array((int)$quantiti, (int)$price);

			# Сохраняем
			if (Core::$_data['user']->setVar('basket', serialize($this->_data))) return true;
		}

		return false;
	}

	# ИЗМЕНЕНИЕ
	public function change($id, $quantiti, $price=false)
	{
		if ($this->check($id))
		{
			# Меняем
			$this->_data[$id][0] = (int)$quantiti;
			if (is_numeric($price)) $this->_data[$id][1] = (int)$price;

			# Сохраняем
			if (Core::$_data['user']->setVar('basket', serialize($this->_data))) return true;
		}

		return false;
	}

	# УДАЛЕНИЕ
	public function remove($item)
	{
		if (is_array($item))
		{
			foreach($item as $i)
			{
				if (filter_var($i, FILTER_VALIDATE_INT) && isset($this->_data[(int)$i])) unset($this->_data[(int)$i]);
			}
		}
		else if (filter_var($item, FILTER_VALIDATE_INT) && $this->check($item)) unset($this->_data[$item]);

		# Сохраняем в ползователе
		if (Core::$_data['user']->setVar('basket', serialize($this->_data))) return true;

		return false;
	}

	# ОЧИСТКА КОРЗИНЫ
	public function clear()
	{
		$this->_data = array();

		# Сохраняем в ползователе
		if (Core::$_data['user']->setVar('basket', '')) return true;

		return false;
	}
}