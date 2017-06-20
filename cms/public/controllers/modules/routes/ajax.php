<?php
# AJAX МЕТОДЫ МОДУЛЯ ЧПУ МАРШРУТЫ v.2.0
# Coded by ShadoW (c) 2013
class cnt_Ajax extends Controller
{
	private $_actions = array(
		'add',
		'edit',
		'sort',
		'switch',
		'remove'
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (!Core::$_data['authed']) exit;
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax()) Helpers::redirect(_CMS_);
		return true;
	}

	# ДЕЙСТВИЯ
	public function act_a()
	{
		if ($this->act_index() && isset($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ДОБАВЛЕНИЕ
				case 'add':
					if (
						isset($_POST['route']) && ($route = Helpers::escape($_POST['route'])) && !empty($route) &&
						isset($_POST['replace']) && ($replace = Helpers::escape($_POST['replace'])) && !empty($replace)
					)
					{
						# Проверяем наличие маршрута
						if (!Model::$_db->select('md_routes', "WHERE `route`='$route'"))
						{
							# Добавляем маршрут
							if (Model::$_db->insert('md_routes', array(
								'route'		=> $route,
								'replace'	=> $replace,
								'sort'		=> number_format(microtime(true), 2, '', '')
							)))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(1, 'ЧПУ маршрут', $route);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно добавить маршрут';
						}
						else $answer['msg'] = 'Такой маршрут уже существует';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# РЕДАКТИРОВАНИЕ
				case 'edit':
					if (
						isset($this->_params[1]) && ($route_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						(Model::$_db->select('md_routes', "WHERE `id`='$route_id' LIMIT 1", "`id`")) &&
						isset($_POST['route']) && ($route = Helpers::escape($_POST['route'])) && !empty($route) &&
						isset($_POST['replace']) && ($replace = Helpers::escape($_POST['replace'])) && !empty($replace)
					)
					{
						# Проверяем наличие маршрута
						if (!Model::$_db->select('md_routes', "WHERE `id`!='$route_id' AND `route`='$route'"))
						{
							# Сохраняем маршрут
							if (Model::$_db->update('md_routes', array(
								'route'		=> $route,
								'replace'	=> $replace
							), "WHERE `id`='$route_id' LIMIT 1"))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(2, 'ЧПУ маршрут', $route);

								# OK
								$answer = array('status'=>'ok');
							}
							else $answer['msg'] = 'Невозможно сохранить маршрут';
						}
						else $answer['msg'] = 'Такой маршрут уже существует';
					}
					else $answer['msg'] = 'Проверьте правильность введеных данных';
				break;

				# ВКЛЮЧЕНИЕ/ВЫКЛЮЧЕНИЕ
				case 'switch':
					if (
						isset($this->_params[1]) && ($route_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						($route = Model::$_db->select('md_routes', "WHERE `id`='$route_id' LIMIT 1", "`route`, `active`"))
					)
					{
						# Меняем активность
						if (Model::$_db->update('md_routes', array('active'=>($route['active'] == 0 ? 1 : 0)), "WHERE `id`='$route_id' LIMIT 1"))
						{

							# Логирование
							if (Core::$_log_actions) Logger::add(($route['active'] = 0 ? 4 : 5), 'ЧПУ маршрут', $route['route']);

							# OK
							$answer = array(
								'status'	  => 'ok',
								'active'	  => $route['active'] == 0 ? 'on' : 'off',
								'active_text' => $route['active'] == 0 ? 'Отключить' : 'Включить',
							);
						}
						else $answer['msg'] = 'Невозможно сохранить состояние маршрута, попробуйте позже';
					}
					else $answer['msg'] = 'Запись не найдена';
				break;

				# УДАЛЕНИЕ
				case 'remove':
					if (
						isset($this->_params[1]) && ($route_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						($route = Model::$_db->select('md_routes', "WHERE `id`='$route_id' LIMIT 1", "`route`"))
					)
					{
						# Удаляем запись
						if (Model::$_db->delete('md_routes', "WHERE `id`='$route_id' LIMIT 1"))
						{
							# Логирование
							if (Core::$_log_actions) Logger::add(3, 'ЧПУ маршрут', $route['route']);

							# OK
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно удалить маршрут';
					}
					else $answer['msg'] = 'Маршрут не найден';
				break;

				# СОРТИРОВКА
				case 'sort':
					if (
						isset($_POST['id']) && ($route_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)) &&
						($route = Model::$_db->select('md_routes', "WHERE `id`='$route_id' LIMIT 1", "`sort`"))
					)
					{
						# Послан следующий объект
						if (
							isset($_POST['next']) && ($next_id = filter_input(INPUT_POST, 'next', FILTER_VALIDATE_INT)) &&
							($next_route = Model::$_db->select('md_routes', "WHERE `id`='$next_id' LIMIT 1", "`sort`"))
						)
						{
							$new_sort = $next_route['sort'];
							Model::$_db->query("UPDATE `md_routes` SET `sort`=sort+1 WHERE`sort`>='$new_sort'");
						}

						# Послан предыдущий объект
						else if (
							isset($_POST['prev']) && ($prev_id = filter_input(INPUT_POST, 'prev', FILTER_VALIDATE_INT)) &&
							($prev_route = Model::$_db->select('md_routes', "WHERE `id`='$prev_id' LIMIT 1", "`sort`"))
						)
						{
							# Пробуем получить следующий объект после предыдущего
							if ($next_route = Model::$_db->select('md_routes', "WHERE `sort`>'".$prev_route['sort']."' LIMIT 1", "`sort`"))
							{
								$new_sort = $next_route['sort'];
								Model::$_db->query("UPDATE `md_routes` SET `sort`=sort+1 WHERE `sort`>='$new_sort'");
							}
							else $new_sort = $prev_route['sort']+1;
						}

						# Сортировка поменялась
						if (!empty($new_sort)) Model::$_db->update('md_routes', array('sort'=>$new_sort), "WHERE `id`='$route_id' LIMIT 1");

						$answer = array('status'=>'ok');
					}
					else $anser['msg'] = 'Маршрут не найден';
				break;

				default :
					$answer['msg'] = 'Действие не найдено';
				break;
			}
			exit(Helpers::JSON($answer));
		}
		else Helpers::redirect(_CMS_);
	}
}