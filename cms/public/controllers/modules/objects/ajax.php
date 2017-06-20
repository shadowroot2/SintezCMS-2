<?php
# AJAX методы модуля ОБЪЕКТЫ v.3.0
# Coded by ShadoW (c) 2013
class cnt_Ajax extends Controller
{
	private $_actions = array(
		'setlang',
		'search',
		'perpage',
		'sort',
		'clone',
		'collection',
		'clearclipboard',
		'template_fields',
		'additional_template_fields',
		'clonefields',
		'mupload'
	);

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Проверка авторизации
		if (!Core::$_data['authed']) exit;

		# Неумерайко
		ignore_user_abort(1);
		set_time_limit(0);
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
		if (isset($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ВЫБОР ЯЗЫКА
				case 'setlang':
					if (
						$this->act_index() &&
						isset($this->_params[1]) && ($lang_code = Helpers::escape($this->_params[1])) && preg_match('#^[a-z]{2}$#', $lang_code) &&
						isset(Core::$_langs[$lang_code]) && is_array(Core::$_langs[$lang_code])
					)
					{
						# Запоминаем язык
						$_SESSION['cms_lang'] = $lang_code;
						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Данный языка не активен или не существует';
					exit(Helpers::JSON($answer));
				break;

				# АВТОПОИСК
				case 'search':
					$answer = array();
					if ($this->act_index() && isset($_GET['term']) && ($search = Helpers::escape($_GET['term'])) && (mb_strlen($search) > 3))
					{
						$answer = (array)Model::$_db->select('objects', "WHERE INSTR(`name`, '".$search."') ORDER BY `id` DESC", 'name');
					}
					exit(Helpers::JSON($answer));
				break;

				# КОЛИЧЕСТВО ОБЪЕКТОВ НА СТРАНИЦУ
				case 'perpage':
					if (
						$this->act_index() &&
						isset($this->_params[1]) && ($perpage = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						in_array($perpage, array(30, 50, 100, 200, 500))
					)
					{
						# OK
						$_SESSION['cms_data']['objects']['perpage'] = $perpage;
						$answer = array('status'=>'ok');
					}
					exit(Helpers::JSON($answer));
				break;

				# КЛОНИРОВАНИЕ
				case 'clone':
					if (
						$this->act_index() &&
						isset($this->_params[1]) && ($object_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) &&
						isset($this->_params[2]) && ($count = filter_var($this->_params[2], FILTER_VALIDATE_INT)) && ($count < 1000) &&
						($object = Model::getObject($object_id))
					)
					{
						# Клонируем
						$cloned = 0;
						for($i=0; $i<$count; $i++)
						{
							if ($clone_id = Model::copyObject($object_id))
							{
								# Логирование
								if (Core::$_log_actions) Logger::add(1, $object['class'], $object['name'], $clone_id, $object['class_id']);
								$cloned++;
							}
						}

						# Клонировано
						if ($cloned > 0) $answer = array('status'=>'ok');
						else $answer['msg'] = 'Клонирование не удалось';
					}
					else $answer['msg'] = 'Проверьте параметры';
					exit(Helpers::JSON($answer));
				break;

				# СОРТИРОВКА
				case 'sort':
					if ($this->act_index() && isset($_POST['id']) && ($object_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)) && ($obj = Model::getObject($object_id)))
					{
						# Послан следующий объект (вверх)
						if (isset($_POST['next']) && ($next_id = filter_input(INPUT_POST, 'next', FILTER_VALIDATE_INT)) && ($next_obj = Model::getObject($next_id)))
						{
							$new_sort = $next_obj['sort'];
							Model::$_db->query("UPDATE `objects` SET `sort`=sort+1 WHERE `mother`='".$obj['mother']."' AND `sort`>='$new_sort'");
						}

						# Послан предыдущий объект (вниз)
						else if (isset($_POST['prev']) && ($prev_id = filter_input(INPUT_POST, 'prev', FILTER_VALIDATE_INT)) && ($prev_obj = Model::getObject($prev_id)))
						{
							$new_sort = $prev_obj['sort'];
							Model::$_db->query("UPDATE `objects` SET `sort`=sort-1 WHERE `mother`='".$obj['mother']."' AND `sort`<='$new_sort'");
						}

						# Сортировка поменялась
						if (isset($new_sort)) Model::$_db->update('objects', array('sort'=>$new_sort), "WHERE `id`='$object_id' LIMIT 1");

						# ОК
						$answer = array('status'=>'ok');
					}
					else $anser['msg'] = 'Объект не найден';
					exit(Helpers::JSON($answer));
				break;

				# ДЕЙСТВИЕ НАД КОЛЛЕКЦИЕЙ ОБЪЕКТОВ
				case 'collection':
					if (
						$this->act_index() &&
						isset($this->_params[1]) && ($action = Helpers::escape($this->_params[1])) && in_array($action, array('on','off','cut','copy','paste','remove')) &&
						isset($this->_params[2]) && ($objects_ids = explode(',', $this->_params[2])) && count($objects_ids)
					)
					{
						# Проверяем ID объектов
						$objects = array();
						foreach($objects_ids as $id)
						{
							if (is_int($id = filter_var($id, FILTER_VALIDATE_INT)) && ($id >= 0)) $objects[] = $id;
						}

						# Получаем объекты или контейнер
						if (count($objects) && (($objects[0] == 0) || ($objects_list = Model::getObjects(-1, false, false, "o.id IN(".join(',', array_values($objects)).")"))))
						{
							switch ($action)
							{
								# Включение
								case 'on':
									if (Model::$_db->update('objects', array('active'=>1), "WHERE `id` IN(".join(',', $objects).")"))
									{
										# Логирование
										if (Core::$_log_actions)
										{
											foreach($objects_list as $o) Logger::add(4, $o['class'], $o['name'], $o['id'], $o['class_id']);
										}

										# OK
										$answer = array('status'=>'ok');
									}
									else $answer['msg'] = 'Невозможно включить объекты';
								break;

								# Отключение
								case 'off':
									if (Model::$_db->update('objects', array('active'=>0), "WHERE `id` IN(".join(',', $objects).")"))
									{
										# Логирование
										if (Core::$_log_actions)
										{
											foreach($objects_list as $o) Logger::add(5, $o['class'], $o['name'], $o['id'], $o['class_id']);
										}

										# OK
										$answer = array('status'=>'ok');
									}
									else $answer['msg'] = 'Невозможно отключить объекты';
								break;

								# Копировать
								case 'copy':
									$_SESSION['cms_clipboard'] = array('objects'=>$objects, 'action'=>'copy');
									$answer = array('status'=>'ok');
								break;

								# Вырезать
								case 'cut':
									$_SESSION['cms_clipboard'] = array('objects'=>$objects, 'action'=>'cut');
									$answer = array('status'=>'ok');
								break;

								# Вставить
								case 'paste':
									if (
										isset($_SESSION['cms_clipboard']['objects']) && is_array($action_objects = $_SESSION['cms_clipboard']['objects']) && count($action_objects) &&
										isset($_SESSION['cms_clipboard']['action']) && ($action = $_SESSION['cms_clipboard']['action']) && in_array($action, array('cut', 'copy')) &&
										($action_objects_list = Model::getObjects(-1, false, false, "o.id IN(".join(',', array_values($action_objects)).")")) &&
										is_int($dst_id = $objects[0])
									)
									{
										# Бегаем по объектам
										foreach($action_objects_list as $o)
										{
											switch ($action)
											{
												# Копирование
												case 'copy':
													if (Model::copyObject($o['id'], $dst_id))
													{
														# Логирование
														if (Core::$_log_actions) Logger::add(1, $o['class'], $o['name'], $o['id'], $o['class_id']);

														# OK
														$answer['status'] = 'ok';
													}
													else
													{
														$answer['msg'] = 'Невозможно скопировать объект "'.$o['name'].'", проверьте путь';
														break;
													}
												break;

												# Перенос
												case 'cut':
													if (Model::moveObject($o['id'], $dst_id))
													{
														# Логирование
														if (Core::$_log_actions) Logger::add(2, $o['class'], $o['name'], $o['id'], $o['class_id']);

														# OK
														$answer['status'] = 'ok';
													}
													else
													{
														$answer['msg'] = 'Невозможно перенести объект "'.$o['name'].'", проверьте путь';
														break;
													}
												break;
											}
										}

										# Если все ok - очищаем буфер
										if ($answer['status'] == 'ok') unset($_SESSION['cms_clipboard']);
									}
									else $answer['msg'] = 'Буффер обмена пуст';
								break;

								# Удаление
								case 'remove':
									foreach($objects_list as $o)
									{
										if (Model::deleteObject($o['id']))
										{
											$answer = array('status'=>'ok');
											if (Core::$_log_actions) Logger::add(3, $o['class'], $o['name'], $o['id'], $o['class_id']);
										}
										else
										{
											$answer['msg'] = 'Невозможно удалить "'.$o['name'].'"';
 											break;
										}
									}
									unset($objects_list, $o);
								break;
							}
						}
						else $answer['msg'] = 'Объекты не найдены';
					}
					else $answer['msg'] = 'Невозможно выполнить операцию';
					exit(Helpers::JSON($answer));
				break;

				# ОЧИСТКА БУФФЕРА ОБМЕНА
				case 'clearclipboard':
					if (isset($_SESSION['cms_clipboard']))
					{
						unset($_SESSION['cms_clipboard']);
						$answer = array('status'=>'ok');
					}
					else $answer['msg'] = 'Буфер обмена пуст';
					exit(Helpers::JSON($answer));
				break;

				# ПОЛУЧЕНИЕ ПОЛЕЙ ШАБЛОНА
				case 'template_fields':
					if ($this->act_index() && isset($this->_params[1]) && ($class_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && is_object($class = Model::getClass($class_id)) && ($class['additional'] == 0))
					{
						exit(View::getTemplate('modules/objects/template_fields', false, array(
							'template'		=> $class->asArray(),
							'add_fields'	=> true
						)));
					}
					else exit('<span class="error">Шаблон не найден</span>');
				break;

				# ПОЛУЧЕНИЕ ПОЛЕЙ ДОПОЛНИТЕЛЬНОГО ШАБЛОНА
				case 'additional_template_fields':
					if ($this->act_index() && isset($this->_params[1]) && ($class_id = filter_var($this->_params[1], FILTER_VALIDATE_INT)) && is_object($class = Model::getClass($class_id)) && ($class['additional'] == 1))
					{
						exit(View::getTemplate('modules/objects/template_fields', false, array(
							'template'		=> $class->asArray(),
							'add_fields'	=> true
						)));
					}
					else exit('<span class="error">Шаблон не найден</span>');
				break;

				# КЛОНИРОВАНИЕ ПОЛЕЙ ОБЪЕКТА
				case 'clonefields':
					if (
						$this->act_index() &&
						isset($this->_params[1]) && (preg_match('#^[a-z]{2}$#', $this->_params[1])) && ($lang = $this->_params[1]) &&
						isset(Core::$_langs[$lang]) && is_array(Core::$_langs[$lang]) &&
						isset($this->_params[2]) && ($object_id = filter_var($this->_params[2], FILTER_VALIDATE_INT)) &&
						($object = Model::getObject($object_id)) && ($object['class_id'] > 0) && is_object($class = Model::getClass($object['class_id']))
					)
					{
						# Русская версия полей
						if ($object_fields = Model::$_db->select('class_'.$object['class_id'], "WHERE `object_id`='$object_id' AND `lang`='ru' LIMIT 1"))
						{
							# Указанная версия
							if (Model::$_db->count('class_'.$object['class_id'], "WHERE `object_id`='$object_id' AND `lang`='$lang' LIMIT 1", "`id`") == 0)
							{
								# Клонируем поля основного шаблона
								$object_fields['lang'] = $lang;
								unset($object_fields['id']);

								if (Model::$_db->insert('class_'.$object['class_id'], $object_fields))
								{
									# Ищем доп.шаблоны в полях
									foreach($class['fields'] as $field)
									{
										if (
											($field['type'] == 'template') && isset($object_fields['f_'.$field['id']]) &&
											($ad_class_id = filter_var($object_fields['f_'.$field['id']], FILTER_VALIDATE_INT)) &&
											($ad_class_fields = Model::$_db->select('class_'.$ad_class_id, "WHERE `object_id`='$object_id' AND `lang`='ru' LIMIT 1")) &&
											(Model::$_db->count('class_'.$ad_class_id, "WHERE `object_id`='$object_id' AND `lang`='$lang' LIMIT 1", "`id`") == 0)
										)
										{
											# Клонируем поля доп.шаблона
											$ad_class_fields['lang'] = $lang;
											unset($ad_class_fields['id']);
											Model::$_db->insert('class_'.$ad_class_id, $ad_class_fields);
										}
									}

									# Логируем
									if (Core::$_log_actions) Logger::add(2, $object['class'], $object['name'], $object['id'],  $object['class_id']);

									# OK
									$answer = array('status'=>'ok');
								}
								else $answer['msg'] = 'Невозможно скопировать поля, попробуйте позже';
							}
							else $answer['msg'] = 'Языковая версия уже есть в базе';
						}
						else $answer['msg'] = 'Русская версия объекта не заполнена';
					}
					else $answer['msg'] = 'Объект не найден или не имеет шаблона';
					exit(Helpers::JSON($answer));
				break;

				# МАССОВАЯ ЗАГРУЗКА
				case 'mupload':
					if (
						isset($this->_params[1]) && ($object_type = Helpers::escape($this->_params[1])) && in_array($object_type, array('photos', 'files')) &&
						isset($this->_params[2]) && ($mother_id = filter_var($this->_params[2], FILTER_VALIDATE_INT)) && ($mother = Model::getObject($mother_id))
					)
					{
						# Читаем параметры сервера
						$SERVER_POST_MAX_SIZE = ini_get('post_max_size');
						$unit				  = strtoupper(substr($SERVER_POST_MAX_SIZE, -1));
						$multiplier 		  = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

						# Проверка размера посланных данных
						if (((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$SERVER_POST_MAX_SIZE) && $SERVER_POST_MAX_SIZE)
						{
							header("HTTP/1.1 500 Internal Server Error");
							exit('Максимальный размер POST '.$SERVER_POST_MAX_SIZE);
						}

						# Ошибки загрузки
						$upload_errors = array(
							1 => 'Превышен размер файла upload_max_filesize',
							2 => 'Превышен размер файла',
							3 => 'Файл загружен не полностью',
							4 => 'Файл не передан',
							6 => 'Ошибка временной папки'
						);

						# Проверяем посланный файл
						if (!isset($_FILES['mfile']) || $_FILES['mfile'] == '')	echo $upload_errors[4];
						else if ($_FILES['mfile']['error'] != 0)				echo $upload_errors[$_FILES['mfile']["error"]];
						else if ($_FILES['mfile']['name'] == '')				echo 'У файла нет имени';
						else if ($_FILES['mfile']['size'] == 0)					echo 'Файл пустой';
						else
						{
							# ВЫБРАННЫЙ ЯЗЫК
							if (
								isset($_SESSION['cms_lang']) && preg_match('#^[a-z]{2}$#', $_SESSION['cms_lang']) &&
								isset(Core::$_langs[$_SESSION['cms_lang']]) && is_array(Core::$_langs[$_SESSION['cms_lang']])
							) Core::$_lang = $_SESSION['cms_lang'];

							# Тип объекта
							switch($object_type)
							{
								# Фотографии
								case 'photos':
									$_FILES['field_14'] = &$_FILES['mfile'];
									if ($obj_id = Model::addObject(
										array(
											'name'		=> $_FILES['field_14']['name'],
											'mother'	=> $mother_id,
											'class_id'	=> 3
										),
										array('Название' => $_FILES['field_14']['name'])
									))
									{
										# Логирование
										if (Core::$_log_actions) Logger::add(1, 'Фотография', $_FILES['field_14']['name'], $obj_id, 3);
										echo 'OK';
									}
									else 'Ошибка: Объект не добавлен';
								break;

								# Файлы
								case 'files':
									$_FILES['field_17'] = &$_FILES['mfile'];
									if ($obj_id = Model::addObject(
										array(
											'name'		=> $_FILES['field_17']['name'],
											'mother'	=> $mother_id,
											'class_id'	=> 6
										),
										array('Название' => $_FILES['field_17']['name'])
									))
									{
										# Логирование
										if (Core::$_log_actions) Logger::add(1, 'Файл', $_FILES['field_17']['name'], $obj_id, 6);
										echo 'OK';
									}
									else 'Ошибка: Объект не добавлен';
								break;
							}
						}
					}
					else echo 'Не послан тип объекта или контейнер';
					exit;
				break;
			}
		}
		Helpers::redirect(_CMS_);
	}
}