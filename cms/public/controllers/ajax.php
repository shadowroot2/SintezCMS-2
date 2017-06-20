<?php
# ПОЛЬЗОВАТЕЛЬСКИЙ AJAX v.2.1
# Coded by ShadoW (c) 2014
class cnt_Ajax extends Controller
{
	private $_not_authed = array('status'=>'error', 'msg'=>'Вы не авторизованы');

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверяем Ajax
		if (!Core::isAjax()) $this->act_index();
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		Helpers::redirect(_CMS_);
	}

	# АВТОРИЗАЦИЯ
	public function act_auth()
	{
		$answer = array(
			'status'	=> 'error',
			'msg'		=> 'Неверный логин или пароль'
		);
		if (
			isset($_POST['login']) && preg_match('#^[a-z0-9_]{3,20}$#i', $_POST['login']) &&
			isset($_POST['pass']) && ($_POST['pass'] != '') &&
			isset($_POST['save'])
		)
		{
			$login = Helpers::escape($_POST['login']);
			$pass  = md5(_SALT_.$_POST['pass']);

			# Получаем пользователя
			if ($user = Model::$_db->select('md_users', " as u LEFT JOIN `md_user_groups` as g ON g.id=u.gid WHERE u.active='1' AND g.active='1' AND u.login='$login' AND u.pass='$pass' LIMIT 1", "u.id, u.gid, u.name, g.name as gname, g.access"))
			{
				# Время входа
				Model::$_db->update('md_users', array('login_ts'=>time()), "WHERE `id`='".$user['id']."'");

				# Генерируем сессию
				$sess = md5(_SALT_.time());

				# Записываем в RAM-таблицу
				if (Model::$_db->select('md_users_ram', "WHERE `user_id`='".$user['id']."' LIMIT 1")) Model::$_db->update('md_users_ram', array('sess'=>$sess), "WHERE `user_id`='".$user['id']."'");
				else Model::$_db->insert('md_users_ram', array('user_id'=>$user['id'], 'sess'=>$sess));

				# Доступы
				if ($user['access'] != '*') $user['access'] = (array)unserialize($user['access']);

				# Пишем в сессию
				$_SESSION = array('cms_auth'=>array_merge($user, array('sess'=>$sess)));

				# Запоминание на месяц
				if ($_POST['save'] == 1) setcookie('cms_auth', $user['id'].':'.$sess, time()+86400*30, '/');

				$answer = array('status'=>'ok');
			}
		}
		exit(Helpers::json($answer));
	}

	# РЕДАКТИРОВАНИЕ ПРОФИЛЯ
	public function act_editprofile()
	{
		$answer = array(
			'status'	=> 'error',
			'msg'		=> 'Проверьте правильность данных'
		);
		if (
			Core::$_data['authed'] &&
			isset($_POST['name']) && preg_match('#^[0-9a-zа-я]+$#iu', $_POST['name']) &&
			isset($_POST['pass']) &&
			isset($_POST['new_pass'])
		)
		{
			# Проверка наличия пользователя
			if (
				isset($_SESSION['cms_auth']['id']) && ($user_id = filter_var($_SESSION['cms_auth']['id'], FILTER_VALIDATE_INT)) &&
				($user = Model::$_db->select('md_users', " as u LEFT JOIN `md_user_groups` as g ON g.id=u.gid WHERE u.active='1' AND g.active='1' AND u.id='$user_id' LIMIT 1"))
			)
			{
				# Имя пользователя
				$edit_user = array('name'=>$_POST['name']);

				# Смена пароля
				$checked = true;
				if ($_POST['pass'] != '')
				{
					if (md5(_SALT_.$_POST['pass']) == $user['pass'])
					{
						if ($_POST['new_pass'] != '') $edit_user['pass'] =  md5(_SALT_.$_POST['new_pass']);
						else
						{
							$checked = false;
							$answer['msg'] = 'Не указан новый пароль';
						}
					}
					else {
						$checked = false;
						$answer['msg'] = 'Неверный текущий пароль';
					}
				}

				# Обновляем данные
				if ($checked)
				{
					if (Model::$_db->update('md_users', $edit_user, "WHERE `id`='$user_id'"))
					{
						$_SESSION['cms_auth']['name'] = Helpers::escape_html($_POST['name']);
						$answer = array('status'=>'ok');
						if (Core::$_log_actions) Logger::add(2, 'Профиль', $_POST['name']);
					}
					else $answer['msg'] = 'Не возможно обновить данные';
				}
			}
			else $answer['msg'] = 'Войдите в систему заново';
		}
		exit(Helpers::json($answer));
	}


	# ВКЛ/ВЫКЛ ФРОНТЭНДА
	public function act_frontend()
	{
		# Проверка в аторизации
		$answer = $this->_not_authed;
		if (Core::$_data['authed'])
		{
			# Проверка праметров
			if (isset($this->_params[0]) && ($status = Helpers::escape($this->_params[0])) && in_array($status, array('on', 'off')))
			{
				# Проверка ядра
				if (Core::$_frontend)
				{
					# Меняем статус
					$_SESSION['cms_auth']['front_end'] = $status == 'on' ? true : false;
					$answer = array('status'=>'ok');
				}
				else $answer['msg'] = 'Возможность не поддерживается на вашем сайте';
			}
			else $answer['msg'] = 'Проверьте правильность данных';
		}
		exit(Helpers::json($answer));
	}

	# РЕДАКТИРОВАНИЕ ПОЛЯ
	public function act_edit_field()
	{
		# Проверка в аторизации
		$answer = $this->_not_authed;
		if (Core::$_data['authed'] && isset($_SESSION['cms_auth']['front_end']) && (bool)$_SESSION['cms_auth']['front_end'])
		{
			# Проверка праметров
			if (
				Core::$_frontend &&
				isset($_POST['id']) && ($obj_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)) &&
				isset($_POST['lang']) && ($lang = filter_input(INPUT_POST, 'lang', FILTER_VALIDATE_REGEXP,  array('options'=>array('regexp'=>'/^[a-z]{2}$/')))) && isset(Core::$_langs[$lang]) &&
				isset($_POST['field']) && ($field = Helpers::escape($_POST['field'])) &&
				isset($_POST['content'])
			)
			{
				# Переключаем язык
				Core::$_lang = $lang;

				# Получаем поле объекта
				if (($object = Model::getObject($obj_id, true)->asArray()) && array_key_exists($field, $object))
				{
					# Вытаскиваем тип поля и если не HTML декодим его
					if (($object['class_id'] != 0) && ($object_class = Model::getClass($object['class_id'])) && ($object_class = $object_class->asArray()))
					{
						foreach($object_class['fields'] as $f)
						{
							if (($f['name'] == $field) && ($f['name'] != ''))
							{
								if ($f['type'] != 'html') $_POST['content'] = str_replace(array('&laquo;', '&raquo;'), array('«', '»'), htmlspecialchars_decode($_POST['content']));
								break;
							}
						}
					}

					# Сохраняем
					if (Model::editObject($obj_id, array($field=>$_POST['content'])))
					{
						# Логируем
						if (Core::$_log_actions) Logger::add(2, $object['class'], $object['name'], $object['id'],  $object['class_id']);

						# OK
						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Невозможно сохранить объект, попробуйте позже';
				}
				else $answer['msg'] = 'Объект не найден';
			}
			else $answer['msg'] = 'Неверные параметры';
		}
		exit(Helpers::json($answer));
	}

	# ПОЛУЧЕНИЕ ПОЛЕЙ ДЛЯ ДОБАВЛЕНИЯ / РЕДАКТИРОВАНИЯ
	public function act_get_fields()
	{
		# Проверка в аторизации
		if (Core::$_data['authed'] && isset($_SESSION['cms_auth']['front_end']) && (bool)$_SESSION['cms_auth']['front_end'])
		{
			# Проверка основных праметров
			if (
				Core::$_frontend &&
				isset($this->_params[0]) && ($lang = filter_var($this->_params[0], FILTER_VALIDATE_REGEXP,  array('options'=>array('regexp'=>'/^[a-z]{2}$/')))) && isset(Core::$_langs[$lang]) &&
				isset($this->_params[1]) && ($type = $this->_params[1]) && in_array($type, array('add', 'edit')) &&
				isset($this->_params[2]) && ($obj_id = filter_var($this->_params[2], FILTER_VALIDATE_INT)) &&
				isset($this->_params[3]) && ($fields = explode(',', Helpers::escape($this->_params[3])))
			)
			{
				# Переключаем язык
				Core::$_lang = $lang;

				# Получаем объект
				if ($object = Model::getObject($obj_id, ($type == 'add' ?  false : ($fields[0] == 'all' ? true : $fields))))
				{
					# Шаблон в зависимости от типа
					if (($type == 'add') && isset($this->_params[4]) && filter_var($this->_params[4], FILTER_VALIDATE_INT)) $class_id = $this->_params[4];
					else $class_id = $object['class_id'];

					# Получем шаблон
					if (!empty($class_id) && ($template = Model::getClass($class_id)->asArray()))
					{
						# Обработка значений полей
						foreach($template['fields'] as $f_id=>$f)
						{
							if (($fields[0] == 'all') || in_array($f['name'], $fields))
							{
								$field_value = isset($object[$f['name']]) ? $object[$f['name']] : '';
								switch ($f['type'])
								{
									case 'date':
										$field_value = ($field_value != '' ? date('d.m.Y', strtotime($field_value)) : '');
									break;
									case 'password':
										$field_value = ($field_value != '' ? '********' : '');
									break;
								}
								$template['fields'][$f_id]['value'] = $field_value;
							}
							else unset($template['fields'][$f_id]);
						}

						# Поля шаблона
						if (count($template['fields'])) $template_fields = View::getTemplate('modules/objects/template_fields', false, array('template'=>$template, 'frontend'=>1));
					}

					# Создаем форму
					$form = '<iframe name="cms_frame" width="0" height="0" frameborder="0" scrolling="no"></iframe>'
						.'<form class="cms_object_form" action="'._MODULES_.'objects/'.$type.'/'.$obj_id.'" method="post" target="cms_frame" enctype="multipart/form-data">'
						.'<input type="hidden" name="frontend" value="1" />'
						.'<input type="hidden" name="lang" value="'.$lang.'" />'
						.($type == 'add' ? '<input type="hidden" name="mother" value="'.$obj_id.'" />' : '')
						.($type == 'add' ? '<input type="hidden" name="class_id" value="'.$class_id.'" />' : '')
						.($type == 'add' ? '<input type="hidden" name="active" value="1" />' : '<div class="formfield"><label class="formtitle"><input type="checkbox" name="active" value="1"'.($object['active'] == 1 ? ' checked="checked"' : '').' /> Активен</label></div>')
						.($type == 'add' ? '<div class="formfield"><div class="formtitle">Название в CMS:</div><input type="text" name="name" value="" maxlength="100" required /></div><hr />' : '')
						.(!empty($template_fields) ? $template_fields : '')
						.'</form>'
						.($type == 'edit' ? '<div align="right"><a href="'._CMS_.'modules/objects/edit/'.$obj_id.'" target="_blank">Редактировать объект в CMS</a></div>' : '');
					exit($form);
				}
				else echo 'Объект не найден';
			}
			else echo 'Неверные параметры';
		}
		else echo 'Вы не авторизованы';
	}
}