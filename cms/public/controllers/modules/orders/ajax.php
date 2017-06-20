<?php
# AJAX методы модуля ЗАЯВКИ
# Coded by ShadoW (c) 2012
class cnt_Ajax extends Controller
{
	private $_container			= 12;
	private $_class				= 18;
	private $_users_container	= 7;
	private $_users_class		= 13;
	private $_status_field		= 107;
	private $_statuses			= array();

	# ТИПЫ ДЕЙСТВИЙ
	private $_actions = array('changepaytype', 'changepayed', 'changestatus', 'changegroupstatus', 'remove', 'referals');

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (Core::$_data['authed'] == false) exit;

		# СТАТУСЫ ЗАЯВОК
		if (($statuses = Model::$_db->select('fields', "WHERE `id`='".$this->_status_field."' LIMIT 1", "`atribute`")) && !empty($statuses['atribute']) && ($statuses = explode("\n", $statuses['atribute'])))
		{
			foreach($statuses as $s)
			{
				if (!empty($s)) $this->_statuses[$s] = true;
			}
		}
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax()) exit(header('Location: '._CMS_));
		return TRUE;
	}

	# ДЕЙСТВИЯ
	public function act_a()
	{
		if (!empty($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# СМЕАНА СПОСОБА ОПЛАТЫ
				case 'changepaytype':
					if (
						$this->act_index() &&
						!empty($_GET['id']) && is_numeric($order_id = $_GET['id']) && ($order = Model::getObject($order_id)) && ($order['mother'] == $this->_container) && ($order['class_id'] == $this->_class) &&
						!empty($_GET['type'])
					)
					{
						# Меняем способ оплаты
						if (Model::editObject($order['id'], array('Способ оплаты'=>trim($_GET['type']), 'Оплачен'=>0, 'Дата оплаты'=>'')))
						{
							# Логируем
							Logger::add(2, 'Заявку', 'Заявка №'.$order['id'], $order['id'], 18);
							$answer = array('status'=>'ok');
						}
					}
					else $answer['msg'] = 'Заявка не найдена';
					exit(Helpers::JSON($answer));
				break;

				# СМЕАНА СТАТУСА ОПЛАТЫ
				case 'changepayed':
					if (
						$this->act_index() &&
						!empty($_GET['id']) && is_numeric($order_id = $_GET['id']) && ($order = Model::getObject($order_id)) && ($order['mother'] == $this->_container) && ($order['class_id'] == $this->_class) &&
						isset($_GET['status']) && is_numeric($status = $_GET['status'])
					)
					{
						# Меняем статус оплаты
						if (Model::editObject($order['id'], array('Оплачен'=>intval($status), 'Дата оплаты'=>($status == 1 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00'))))
						{
							# Логируем
							Logger::add(2, 'Заявку', 'Заявка №'.$order['id'], $order['id'], 18);
							$answer = array('status'=>'ok');
						}
					}
					else $answer['msg'] = 'Заявка не найдена';
					exit(Helpers::JSON($answer));
				break;

				# СМЕАНА СТАТУСА ЗАЯВКИ
				case 'changestatus':
					if (
						$this->act_index() &&
						!empty($_GET['id']) && is_numeric($order_id = $_GET['id']) && ($order = Model::getObject($order_id)) && ($order['mother'] == $this->_container) && ($order['class_id'] == $this->_class) &&
						!empty($_GET['status']) && isset($this->_statuses[$_GET['status']])
					)
					{
						# Меняем статус
						if (Model::editObject($order['id'], array('Статус'=>$_GET['status'])))
						{
							# Логируем
							Logger::add(2, 'Заявку', 'Заявка №'.$order['id'], $order['id'], 18);
							$answer = array('status'=>'ok');
						}
					}
					else $answer['msg'] = 'Заявка не найдена';
					exit(Helpers::JSON($answer));
				break;

				# ГРУППОВАЯ СМЕНА САТАТУСА
				case 'changegroupstatus':
					if ($this->act_index() && !empty($_POST['orders']) && is_array($orders_ids = $_POST['orders']) && !empty($_POST['status']) && isset($this->_statuses[$_POST['status']]))
					{
						# Меняем статус у заявок
						foreach($orders_ids as $order_id)
						{
							if (is_numeric($order_id) && (Model::editObject($order_id, array('Статус'=>$_POST['status']))))
							{
								# Логируем
								Logger::add(2, 'Заявку', 'Заявка №'.$order_id, $order_id, 18);
							}
						}

						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Выберите заявки';
					exit(Helpers::JSON($answer));
				break;

				# УДАЛЕНИЕ ЗАЯВКИ
				case 'remove':
					if ($this->act_index() && !empty($this->_params[1]) && is_numeric($order_id = $this->_params[1]) && ($order = Model::getObject($order_id, TRUE)) && ($order['mother'] == $this->_container) && ($order['class_id'] == $this->_class))
					{
						# Удаляем заявку
						if (Model::deleteObject($order['id']))
						{
							# Удаляем статистику
							_OrderStat::remove($order['id'], unserialize($order['Товары']));

							# Логируем
							Logger::add(3, 'Заявку', $order['name'], '', $order['class_id']);
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно удалить заявку';
					}
					else $answer['msg'] = 'Заявка не найдена';
					exit(Helpers::JSON($answer));
				break;

				# РЕФЕРАЛЫ ПОЛЬЗОВАТЕЛЯ
				case 'referals':
					if (
						$this->act_index() &&
						!empty($this->_params[1]) && is_numeric($user_id = $this->_params[1]) &&
						($user = Model::getObject($user_id, FALSE)) && ($user['mother'] == $this->_users_container) && ($user['class_id'] == $this->_users_class)
					)
					{
						# Получаем рефералов
						if ($referals_list = Model::getObjects($this->_users_container, $user['class_id'], array('Имя'), "o.active='1' AND c.f_123='$user_id' ORDER BY o.c_date ASC"))
						{
							$referals = array();
							foreach($referals_list as $r)
							{
								# Количество выполненых заявок
								$ref_orders = Model::getObjectsCount($this->_container, $this->_class, "o.active='1' AND c.f_100='".$r['id']."' AND c.f_107='Выполнена'");

								$referals[] = array(
									'id'		=> $r['id'],
									'name'		=> $r['Имя'],
									'regdate'	=> Helpers::date('d mounth Y', $r['c_date']),
									'orders'	=> number_format($ref_orders, 0, '.', ' ').' '.Helpers::ndec($ref_orders, array('заказ', 'заказа', 'заказов'))
								);
							}

							# OK
							$answer = array('status'=>'ok', 'referals'=>$referals);
						}
						else $answer['msg'] = 'У пользователя нет рефералов';
					}
					exit(Helpers::JSON($answer));
				break;

				default :
					header('Location: '._CMS_);
				break;
			}
		}
		else header('Location: '._CMS_);
	}
}