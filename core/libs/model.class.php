<?php
# МОДЕЛЬ v.3.4
# Coded by ShadoW (c) 2015
defined('_CORE_') or die('Доступ запрещен');

class Model extends Singleton
{
	protected static $_init = false;

	public static $_db;

	public static $_ofields;
	public static $_tfields;

	private static $_classes;

	private static $_files_fields;
	private static $_images_ext;
	private static $_audios_ext;
	private static $_videos_ext;

	private static $_mothers		= array();
	private static $_mothers_path	= array();


	# ИНИЦИАЛИЗАЦИЯ
	public static function _init()
	{
		# Проверка инициализации
		if (self::$_init) return;
		self::$_init = true;

		# Коннектор БД
		try
		{
			$connector_file = _ABS_CORE_.'connectors'._SEP_._CONNECTOR_._EXT_;
			if (require_once($connector_file)) self::$_db = new DB;
			else throw new Exception('Невозможно подключить коннектор <b>'.$connector_file.'</b>');
		}
		catch(Exception $e) { Errors::exception_handler($e); }

		# Дебаг
		self::$_db->_debug = Core::$_debug;
		if (self::$_db->_debug) echo '<h2>DB debug:</h2>';

		# Настройка объектов и полей
		self::$_ofields	= array('id', 'mother', 'class_id', 'fix_class', 'active', 'name', 'inside', 'a_date', 'm_date', 'sort');
		self::$_tfields = array(
			'text'			=> array('name'=>'Строка текста',				'sql'=>"VARCHAR(255) NOT NULL"),
			'integer'		=> array('name'=>'Целое число',					'sql'=>"INT(10) NOT NULL DEFAULT '0'"),
			'textarea'		=> array('name'=>'Блок текста',					'sql'=>"TEXT NOT NULL"),
			'html'			=> array('name'=>'HTML-код',					'sql'=>"LONGTEXT"),
			'date'			=> array('name'=>'Дата',						'sql'=>"DATE NOT NULL"),
			'time'			=> array('name'=>'Время',						'sql'=>"TIME NOT NULL"),
			'datetime'		=> array('name'=>'Дата и время',				'sql'=>"DATETIME NOT NULL"),
			'checkbox'		=> array('name'=>'Галочка (CHECKBOX)',			'sql'=>"tinyint(1) NOT NULL DEFAULT '0'"),
			'radio'			=> array('name'=>'Выбор (RADIO)',				'sql'=>"VARCHAR(100) NOT NULL"),
			'select'		=> array('name'=>'Выбор (SELECT)',				'sql'=>"VARCHAR(100) NOT NULL"),
			'multi-select'	=> array('name'=>'Мульти выбор (SELECT)',		'sql'=>"TEXT NOT NULL"),
			'link'			=> array('name'=>'Связь (SELECT)',				'sql'=>"INT(10) NOT NULL"),
			'multi-link'	=> array('name'=>'Мульти связь (SELECT)',		'sql'=>"TEXT NOT NULL"),
			'multi-val'		=> array('name'=>'Мульти значения (MULTI)',		'sql'=>"TEXT NOT NULL"),
			'color'			=> array('name'=>'Цвет (HEX)',					'sql'=>"VARCHAR(6) NOT NULL"),
			'password'		=> array('name'=>'Пароль (MD5+SALT)',			'sql'=>"CHAR(32) NOT NULL"),
			'image'			=> array('name'=>'Загрузка изображения',		'sql'=>"VARCHAR(250) NOT NULL"),
			'audio'			=> array('name'=>'Загрузка аудио (MP3)',		'sql'=>"VARCHAR(250) NOT NULL"),
			'video'			=> array('name'=>'Загрузка видео (FLV, MP4)',	'sql'=>"VARCHAR(250) NOT NULL"),
			'file'			=> array('name'=>'Загрузка файла',				'sql'=>"VARCHAR(250) NOT NULL"),
			'template'		=> array('name'=>'Дополнительный шаблон',		'sql'=>"SMALLINT(4) NOT NULL"),
			'gmap-coords'	=> array('name'=>'Координаты Google Maps',		'sql'=>"VARCHAR(50) NOT NULL"),
			'ymap-coords'	=> array('name'=>'Координаты Яндекс Карт',		'sql'=>"VARCHAR(50) NOT NULL")
		);

		# Нулевой шаблон КОНТЕЙНЕР
		self::$_classes[0] = new _Template(array('id'=>0, 'name'=>'Контейнер', 'fields'=>array()));

		# Файловые типы полей
		self::$_files_fields = array('image', 'audio', 'video', 'file');

		# Типы файлов
		self::$_images_ext = array(
			'jpg',
			'jpeg',
			'gif',
			'png'
		);
		self::$_audios_ext = array(
			'mp3'
		);
		self::$_videos_ext = array(
			'flv',
			'mp4'
		);

		# Языки
		if ($langs = self::$_db->select('md_languages', "WHERE `active`='1' ORDER BY `id` ASC", "`id`, `set`, `code`, `name`"))
		{
			foreach($langs as $l)
			{
				Core::$_langs[$l['code']] = array('id'=>$l['id'], 'code'=>$l['code'], 'name'=>$l['name']);
				if ($l['set'] == 1) Core::$_lang = $l['code'];
			}
			unset($langs, $l);
		}
	}


	# ОБНОВЛЕНИЕ СЧЕТЧИКА ВНУТРИ ОБЪЕКТА
	private static function freshCounter($mother, $action='+', $count=1)
	{
		if ($mother > 0) return self::$_db->query("UPDATE `objects` SET `inside`=inside".$action.$count." WHERE id='$mother' LIMIT 1");
		return false;
	}

	# КОЛИЧЕСТВО ОБЪЕКТОВ
	public static function getObjectsCount($mother, $class_id=false, $sql="o.active='1'")
	{
		$query		= array();
		$class_set	= false;

		if (is_int(filter_var($mother, FILTER_VALIDATE_INT)) && ($mother >= 0)) $query[] = "o.mother='$mother'";
		if (is_int(filter_var($class_id, FILTER_VALIDATE_INT)) && ($class_id >= 0))
		{
			$query[] = "o.class_id='$class_id'";

			if ($class_id > 0)
			{
				$query[]	= "c.lang='".Core::$_lang."'";
				$class_set	= true;
			}
		}
		if ($sql != '') $query[] = $sql;


		if (count($query))
		{
			$sql = ($class_set ? "LEFT JOIN `class_$class_id` as c ON o.id=c.object_id " : '')
				.'WHERE '
				.join(' AND ', $query);
		}

		return (int)self::$_db->count('objects as o', $sql, 'o.id');
	}

	# ВЛОЖЕННОСТЬ ОБЪЕКТА
	public static function getMothers($object_id, $stop_id=0, $first=true)
	{
		if ($first) self::$_mothers_path = array();

		if ($object_id == $stop_id) return array_reverse(self::$_mothers_path);
		else if ($object_id == 0) return false;

		if (isset(self::$_mothers[$object_id])) self::$_mothers_path[] = self::$_mothers[$object_id];
		else if ($obj = self::$_db->select('objects', "WHERE `id`='$object_id' LIMIT 1", "`id`, `mother`, `name`, `inside`"))
		{
			self::$_mothers_path[] = self::$_mothers[$object_id] = $obj;
		}

		if (isset(self::$_mothers[$object_id])) return self::getMothers(self::$_mothers[$object_id]['mother'], $stop_id, false);
	}



	# ПОЛУЧЕНИЕ ОБЪЕКТА
	public static function getObject($object_id, $fields=false, $additional=true)
	{
		if ((!$object_id = filter_var($object_id, FILTER_VALIDATE_INT)) || ($object_id <= 0) || (!$object = self::$_db->select("`objects` as o", "LEFT JOIN `classes` as cs ON cs.id=o.class_id WHERE o.id='$object_id' LIMIT 1", "o.*, cs.name as class"))) return false;

		# Получить поля объекта?
		if ($fields && ($object['class_id'] > 0) && ($class_fields = self::getClassFields($object['class_id'])))
		{
			$requered_fields = $template_fields = array();

			# Поля указаны
			if (is_array($fields) && count($fields))
			{
				foreach($class_fields as $field)
				{
					if (in_array($field['name'], $fields) || in_array($field['id'], $fields))
					{
						$requered_fields[] = "`f_".$field['id']."` as '".$field['name']."'";
						if ($field['type'] == 'template') $template_fields[] = $field['name'];
					}
				}
			}

			# Иначе все поля шаблона
			else
			{
				foreach($class_fields as $field)
				{
					$requered_fields[] = "`f_".$field['id']."` as '".$field['name']."'";
					if ($field['type'] == 'template') $template_fields[] = $field['name'];
				}
			}

			# Дописываем поля в объект
			if (count($requered_fields) && ($object_fields = self::$_db->select('class_'.$object['class_id'], "WHERE `object_id`='".$object['id']."' AND `lang`='".Core::$_lang."' LIMIT 1", join(', ', $requered_fields))))
			{
				# Склеиваем объект с основным шаблоном
				$object = array_merge($object, $object_fields);

				# Доп шаблоны
				if ($additional && count($template_fields))
				{
					foreach($object_fields as $f_name=>$f_value)
					{
						if (
							in_array($f_name, $template_fields) && ($add_class_id = filter_var($f_value, FILTER_VALIDATE_INT)) &&
							($add_class_fields = self::getClassFields($add_class_id))
						)
						{
							$requered_fields = array();

							# Поля указаны
							if (is_array($fields) && count($fields))
							{
								foreach($add_class_fields as $field)
								{
									if (in_array($field['name'], $fields) || in_array($field['id'], $fields))
									{
										$requered_fields[] = "`f_".$field['id']."` as '".$field['name']."'";
									}
								}
							}

							# Иначе все поля доп.шаблона
							else
							{
								foreach($add_class_fields as $field) $requered_fields[] = "`f_".$field['id']."` as '".$field['name']."'";
							}

							# Получем поля доп.шаблона и дописываем объекту
							if ($add_object_fields = self::$_db->select('class_'.$add_class_id, "WHERE `object_id`='".$object['id']."' AND `lang`='".Core::$_lang."' LIMIT 1", join(', ', $requered_fields)))
							{
								$object = array_merge($object, $add_object_fields);
							}
						}
					}
				}

			}
		}
		unset($class_fields, $field, $template_fields, $requered_fields, $object_fields, $add_object_fields);

		return new _Object($object);
	}

	# ПОЛУЧЕНИЕ СПИСКА ОБЪЕКТОВ
	public static function getObjects($mother=0, $class=false, $fields=false, $sql="o.active='1' ORDER BY o.sort ASC")
	{
		$query = array();

		# Родительский объект
		if (is_int(filter_var($mother, FILTER_VALIDATE_INT)) && ($mother >= 0)) $query[] = "o.mother='$mother'";

		# Основной шаблон
		if (is_int($class_id = filter_var($class, FILTER_VALIDATE_INT)) && ($class_id >= 0) && (($class_id == 0) || (($class_obj = self::getClass($class_id)) && ($class_obj['additional'] == 0))))
			$query[] = "o.class_id='$class_id'";
		else if (is_array($class) && isset($class[0]) && is_int($class_id = filter_var($class[0], FILTER_VALIDATE_INT)) && ($class_obj = self::getClass($class_id)) && ($class_obj = self::getClass($class_id)) && ($class_obj['additional'] == 0))
			$query[] = "o.class_id='$class_id'";
		else $class_id = false;

		# Поля основного шаблона для получения
		$requered_classes	= array("LEFT JOIN `classes` as cs ON cs.id=o.class_id");
		$requered_fields	= array('o.*', 'cs.name as class');
		if ($fields && isset($class_obj['fields']))
		{
			$requered_classes[] = "LEFT JOIN `class_".$class_id."` as c ON c.object_id=o.id";
			$query[] = "c.lang='".Core::$_lang."'";

			# Поля перечислены
			if (is_array($fields))
			{
				foreach($class_obj['fields'] as $field)
				{
					if (in_array($field['name'], $fields) || in_array($field['id'], $fields))  $requered_fields[] = "c.f_".$field['id']." as '".$field['name']."'";
				}
			}

			# Все поля шаблона
			else
			{
				foreach($class_obj['fields'] as $field) $requered_fields[] = "c.f_".$field['id']." as '".$field['name']."'";
			}

			# Поля доп.шаблонов для получения
			if (is_array($class) && isset($class[1]) && is_array($class[1]))
			{
				foreach ($class[1] as $add_class_field=>$add_class_id)
				{
					# Проверяем доп.шаблон
					if (
						is_int($add_class_field = filter_var($add_class_field, FILTER_VALIDATE_INT)) && isset($class_obj['fields'][$add_class_field]) && ($class_obj['fields'][$add_class_field]['type'] == 'template') &&
						is_int($add_class_id = filter_var($add_class_id, FILTER_VALIDATE_INT)) && ($add_class_obj = self::getClass($add_class_id)) && ($add_class_obj['additional'] == 1)
					)
					{
						$requered_classes[] = "LEFT JOIN `class_".$add_class_id."` as c".$add_class_id." ON c".$add_class_id.".object_id=o.id";
						$query[] = "c.f_".$add_class_field."='$add_class_id'";
						$query[] = "c".$add_class_id.".lang='".Core::$_lang."'";

						# Поля перечислены
						if (is_array($fields))
						{
							foreach($add_class_obj['fields'] as $field)
							{
								if (in_array($field['name'], $fields) || in_array($field['id'], $fields)) $requered_fields[] = "c".$add_class_id.".f_".$field['id']." as '".$field['name']."'";
							}
						}

						# Все поля доп.шаблона
						else
						{
							foreach($add_class_obj['fields'] as $field) $requered_fields[] = "c".$add_class_id.".f_".$field['id']." as '".$field['name']."'";
						}
					}
				}
			}
		}
		else $fields = false;

		# Запрос
		if ($sql != '') $query[] = $sql;

		# ПОЛУЧАЕМ ОБЪЕКТЫ
		return self::$_db->select(
			'objects as o',
			join(' ', $requered_classes).(count($query) ? " WHERE ".join(' AND ', $query) : ''),
			join(', ', $requered_fields)
		);
	}



	# ДОБАВЛЕНИЕ ОБЪЕКТА
	public static function addObject($object, $fields=false)
	{
		# Валидация данных
		if (!isset($object['name']) || ($object['name'] == '')) return false;

		# Создаем объект
		$new_object = array(
			'name'	 => Helpers::escape_html($object['name']),
			'c_date' => time(),
			'm_date' => time(),
			'sort'	 => number_format(microtime(true), 2, '', '')
		);
		if (isset($object['active']) && ($object['active'] == 0))
			$new_object['active'] = 0;
		if (isset($object['mother']) && ($object['mother'] = filter_var($object['mother'], FILTER_VALIDATE_INT)) && ($object['mother'] > 0) && (self::getObject($object['mother'], false)))
			$new_object['mother'] = $object['mother'];
		if (isset($object['class_id']) && ($object['class_id'] = filter_var($object['class_id'], FILTER_VALIDATE_INT)) && ($class = self::getClass($object['class_id'])) && ($class['additional'] == 0))
			$new_object['class_id'] = $object['class_id'];
		if (isset($object['fix_class']) && ($object['fix_class'] = filter_var($object['fix_class'], FILTER_VALIDATE_INT)) && ($fix = self::getClass($object['fix_class'])) && ($fix['additional'] == 0))
			$new_object['fix_class'] = $object['fix_class'];
		if (isset($object['sort']) && preg_match('#^[0-9]{12}$#', $object['sort']))
			$new_object['sort'] = $object['sort'];

		# Объект собран
		$object = $new_object;
		unset($new_object);

		# Добавляем объект
		$object['id'] = self::$_db->insert('objects', $object, false);

		# Обновляем счетчик вложенности
		if (isset($object['mother'])) self::freshCounter($object['mother']);

		# Шаблон задан
		if (isset($object['class_id']) && ($object['class_id'] > 0) && is_object($class))
		{
			if (!self::addObjectClassFields($object, $class, $fields))
				return false;
		}

		return $object['id'];
	}

	# ДОБАВЛЕНИЕ ПОЛЕЙ ОБЪЕКТА В ШАБЛОН
	private static function addObjectClassFields($object, $class, $fields=false, $additional=false)
	{
		# Поля на добавление
		$insert_fields = array('object_id'=>$object['id'], 'lang'=>Core::$_lang);

		# Посланые поля шаблона
		if (is_array($fields))
		{
			foreach($class['fields'] as $field)
			{
				# ОБЫЧНЫЕ ПОЛЯ
				if (isset($fields[$field['name']]) || isset($fields[$field['id']]))
				{
					if (isset($fields[$field['name']])) $value = $fields[$field['name']];
					else $value = $fields[$field['id']];

					# HTML
					if ($field['type'] == 'html') $value = Helpers::escape_html($value);
					else $value = Helpers::escape($value);

					# Доработка значений полей
					switch ($field['type'])
					{
						case 'checkbox':
							$value = ($value != 1 ? 0 : $value);
						break;
						case 'multi-select':
							$value = is_array($value) ? join(_NL_, $value) : '';
						break;
						case 'multi-link':
							$value = is_array($value) ? join(_NL_, $value) : '';
						break;
						case 'multi-val':
							if (is_array($value))
							{
								$joined_values = array();
								foreach($value as $k=>$v) $joined_values[] = $k.'::'.$v;
								$value = join("\n", $joined_values);
							}
							else $value = '';
						break;
						case 'date':
							$value = date('Y-m-d', strtotime($value));
						break;
						# !!!!!!!! ВРЕМЯ
						# !!!!!!!! ДАТА И ВРЕМЯ
						case 'template':
							if (!$additional && is_int($add_class_id = filter_var($value, FILTER_VALIDATE_INT)) && ($add_template = self::getClass($add_class_id)) && ($add_template['additional'] == 1))
							{
								if (!self::addObjectClassFields($object, $add_template, $fields, true)) return false;
							}
						break;
						case 'password':
							$value = md5($field['atribute'].$value);
						break;
					}

					$insert_fields['f_'.$field['id']] = $value;
				}

				# ЗАГРУЗКА ФАЙЛОВ
				if (in_array($field['type'], self::$_files_fields))
				{
					if (
						isset($_FILES['field_'.$field['id']]) &&
						is_array($field_file = $_FILES['field_'.$field['id']]) &&
						($field_file['error'] == 0) && ($field_file['size'] > 0)
					)
					{
						# Параметры файла
						$tmp_file	= $field_file['tmp_name'];
						$name_parts = explode('.', mb_strtolower($field_file['name']));
						$file_ext	= end($name_parts);
						$file_name	= mb_substr(Helpers::hurl_encode($object['id'].'-'.$field['id'], $object['name']), 0, 30).'_'.Core::$_lang.'.'.$file_ext;

						switch($field['type'])
						{
							# Изображение
							case 'image':
								if (
									in_array($file_ext, self::$_images_ext) &&
									is_array($img_params = getimagesize($tmp_file)) &&
									in_array(str_replace('image/', '', $img_params['mime']), self::$_images_ext)
								)
								{
									# Ресайз
									if ($field['atribute'] != '')
									{
										@list($img_w, $img_h) = explode('x', $field['atribute']);
										if ($resized_img = Image::getImage($tmp_file, $img_w, $img_h)) file_put_contents(_ABS_UPLOADS_.$file_name, $resized_img);
									}
									else move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
									$insert_fields['f_'.$field['id']] = $file_name;
								}
							break;

							# Аудио
							case 'audio':
								if (in_array($file_ext, self::$_audios_ext) && ($field_file['type'] == 'audio/mpeg'))
								{
									move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
									$insert_fields['f_'.$field['id']] = $file_name;
								}
							break;

							# Видео
							case 'video':
								if (in_array($file_ext, self::$_videos_ext) && ($field_file['type'] == 'application/octet-stream'))
								{
									move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
									$insert_fields['f_'.$field['id']] = $file_name;
								}
							break;

							# Файл
							case 'file':
								if (($field['atribute'] != '') && ($allowed_exts = explode(',', $field['atribute'])) && in_array($file_ext, $allowed_exts))
								{
									move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
									$insert_fields['f_'.$field['id']] = $file_name;
								}
							break;
						}
					}
				}
			}
		}

		# Добавляем поля объекта
		return self::$_db->insert('class_'.$class['id'], $insert_fields, false);
	}


	# РЕДАКТИРОВАНИЕ ОБЪЕКТА
	public static function editObject($object, $fields=false)
	{
		# В каком виде пришел объект
		if (!is_array($object) && (!$object = filter_var($object, FILTER_VALIDATE_INT))) return false;
		if (is_int($object)) $object = array('id'=>$object);
		if (!isset($object['id']) || (!$edit_obj = self::getObject($object['id'], true))) return false;

		# Собираем объект
		$new_obj = array('m_date'=>time());
		if (isset($object['name']) && ($object['name'] != $edit_obj['name']) && is_string($object['name']) && ($object['name'] != ''))
			$new_obj['name'] = Helpers::escape_html($object['name']);
		if (isset($object['active']) && ($object['active'] != $edit_obj['active']) && in_array($object['active'], array(0,1)))
			$new_obj['active'] = (int)$object['active'];
		if (isset($object['mother']) && ($object['mother'] != $edit_obj['mother']) && is_int(filter_var($object['mother'], FILTER_VALIDATE_INT)) && ($object['mother'] >= 0) && (($object['mother'] == 0) || self::getObject($object['mother'], false)))
			$new_obj['mother'] = (int)$object['mother'];
		if (isset($object['class_id']) && ($object['class_id'] != $edit_obj['class_id']) && is_int(filter_var($object['class_id'], FILTER_VALIDATE_INT)) && ($object['class_id'] >= 0))
		{
			if (($object['class_id'] > 0) && ((!$class = self::getClass($object['class_id'])) || ($class['additional'] == 1))) return false;
			$new_obj['class_id'] = (int)$object['class_id'];
		}
		if (isset($object['fix_class']) && ($object['fix_class'] != $edit_obj['fix_class']) && is_int(filter_var($object['fix_class'], FILTER_VALIDATE_INT)) && ($object['fix_class'] >= 0))
		{
			if (($object['fix_class'] > 0) && ((!$fix = self::getClass($object['fix_class'])) || ($fix['additional'] == 1))) return false;
			$new_obj['fix_class'] = (int)$object['fix_class'];
		}
		if (isset($object['m_date']) && filter_var($object['m_date'], FILTER_VALIDATE_INT) && (strlen($object['m_date']) == 10))
			$new_obj['m_date'] = $object['m_date'];
		if (isset($object['sort']) && ($object['sort'] != $edit_obj['sort']) && preg_match('#^[0-9]{12}$#', $object['sort']))
			$new_obj['sort'] = $object['sort'];

		# ОБНОВЛЯЕМ ОБЪЕКТ
		if (!self::$_db->update('objects', $new_obj, "WHERE `id`='".$object['id']."' LIMIT 1", false)) return false;

		# Склеиваем старый с новым
		$object = array_merge($edit_obj->mainFields(), $new_obj);
		unset($new_obj);

		# Смена владельца
		if ($object['mother'] != $edit_obj['mother'])
		{
			self::freshCounter($edit_obj['mother'], '-');
			self::freshCounter($object['mother']);
		}

		# Смена шаблона
		if ($object['class_id'] != $edit_obj['class_id'])
		{
			# Старый шаблон был задан
			if (($edit_obj['class_id'] > 0) && ($edit_obj_fields = Model::getClassFields($edit_obj['class_id'])))
			{
				# Удаляем файлы объекта
				self::removeObjectFiles($object['id']);
				self::removeObjectFiles($object['id'], 'resized'._SEP_);

				# Удаляем поля объекта из доп.шаблонов
				foreach($edit_obj_fields as $field)
				{
					if (($field['type'] == 'template') && ($add_class_id = filter_var($edit_obj[$field['name']], FILTER_VALIDATE_INT)) && (self::getClassFields($add_class_id)))
						self::$_db->delete('class_'.$add_class_id, "WHERE `object_id`='".$object['id']."'");
				}

				# Удаляем из текущего шаблона
				self::$_db->delete('class_'.$edit_obj['class_id'], "WHERE `object_id`='".$object['id']."'");
			}

			# Добавляем в новый шаблон
			if ($object['class_id'] > 0) self::$_db->insert('class_'.$object['class_id'], array('object_id'=>$object['id'], 'lang'=>Core::$_lang));
		}

		# Сохранение полей объекта в шаблон
		if (is_array($fields) && count($fields) && (isset($class) || ($class = self::getClass($object['class_id']))))
		{
			if (!self::editObjectClassFields($object, $edit_obj, $class, $fields))
				return false;
		}

		return true;
	}

	# РЕДАКТИРОВАНИЕ ПОЛЕЙ ОБЪЕКТА В ШАБЛОНE
	private static function editObjectClassFields($object, $edit_obj, $class, $fields=false, $additional=false)
	{
		# Название объекта
		$object_name = isset($object['name']) ? $object['name'] : $edit_obj['name'];

		# Поля на обновление
		$update_fields = array();
		foreach($class['fields'] as $field)
		{
			# ОБЫЧНЫЕ ПОЛЯ
			if (isset($fields[$field['name']]) || isset($fields[$field['id']]))
			{
				if (isset($fields[$field['name']])) $value = $fields[$field['name']];
				if (isset($fields[$field['id']]))	$value = $fields[$field['id']];

				# HTML
				if ($field['type'] == 'html') $value = Helpers::escape_html($value);
				else $value = Helpers::escape($value);

				# Замена файловых полей
				if (in_array($field['type'], self::$_files_fields) && isset($edit_obj[$field['name']]) && ($edit_obj[$field['name']] != ''))
				{
					if ($field['type'] == 'image') self::removeObjectImage($edit_obj[$field['name']]);
					else self::removeObjectFile($edit_obj[$field['name']]);
				}

				# Доработка значений полей
				switch ($field['type'])
				{
					case 'checkbox':
						$value = ($value != 1 ? 0 : $value);
					break;
					case 'multi-select':
						$value = is_array($value) ? join(_NL_, $value) : '';
					break;
					case 'multi-link':
						$value = is_array($value) ? join(_NL_, $value) : '';
					break;
					case 'multi-val':
						if (is_array($value))
						{
							$joined_values = array();
							foreach($value as $k=>$v) $joined_values[] = $k.'::'.$v;
							$value = join("\n", $joined_values);
						}
						else $value = '';
					break;
					case 'date':
						$value = date('Y-m-d', strtotime($value));
					break;
					# !!!!!!!! ВРЕМЯ
					# !!!!!!!! ДАТА И ВРЕМЯ
					case 'template':
						if (!$additional)
						{
							# Доп.шаблон поменялся?
							if (($value != $edit_obj[$field['name']]) && ($old_add_class_id = filter_var($edit_obj[$field['name']], FILTER_VALIDATE_INT)) && ($old_add_class = self::getClass($old_add_class_id)) && ($old_add_class['additional'] == 1))
							{
								# Ищем файловые поля
								foreach($old_add_class['fields'] as $ad_field)
								{
									if (in_array($ad_field['type'], self::$_files_fields))
									{
										# Удаляем файлы
										self::removeObjectFiles($object['id'].'-'.$ad_field['id']);
										if ($ad_field['type'] == 'image') self::removeObjectFiles($object['id'].'-'.$ad_field['id'], 'resized'._SEP_);
									}
								}

								# Удаляем из текущего шаблона
								if (!self::$_db->delete('class_'.$old_add_class_id, "WHERE `object_id`='".$object['id']."'")) return false;
							}

							# Обновление или добавление полей доп.шаблона
							if (is_int($add_class_id = filter_var($value, FILTER_VALIDATE_INT)) && ($add_class = self::getClass($add_class_id)) && ($add_class['additional'] == 1))
							{
								if (!self::editObjectClassFields($object, $edit_obj, $add_class, $fields, true))
									return false;
							}
						}
					break;
					case 'password':
						if ($value != '********') $value = md5($field['atribute'].$value);
						else continue;
					break;
				}
				$update_fields['f_'.$field['id']] = $value;
			}

			# ЗАГРУЗКА ФАЙЛОВ
			if (in_array($field['type'], self::$_files_fields))
			{
				if (
					isset($_FILES['field_'.$field['id']]) &&
					is_array($field_file = $_FILES['field_'.$field['id']]) &&
					($field_file['type'] ==  mb_strtolower($field_file['type'])) &&
					($field_file['error'] == 0) && ($field_file['size'] > 0)
				)
				{
					# Параметры файла
					$tmp_file	= $field_file['tmp_name'];
					$name_parts = explode('.', mb_strtolower($field_file['name']));
					$file_ext	= end($name_parts);
					$file_name	= mb_substr(Helpers::hurl_encode($object['id'].'-'.$field['id'], $object_name), 0, 30).'_'.Core::$_lang.'.'.$file_ext;

					# Удаляем старое изображение
					if (isset($edit_obj[$field['name']]) && ($edit_obj[$field['name']] != ''))
					{
						if ($field['type'] == 'image') self::removeObjectImage($edit_obj[$field['name']]);
						else self::removeObjectFile($edit_obj[$field['name']]);
					}

					switch($field['type'])
					{
						# Изображение
						case 'image':

							# Удаляем старое изображение
							if (isset($edit_obj[$field['name']]) && ($edit_obj[$field['name']] != '')) self::removeObjectImage($edit_obj[$field['name']]);

							if (
								in_array($file_ext, self::$_images_ext) &&
								is_array($img_params = getimagesize($tmp_file)) &&
								in_array(str_replace('image/', '', $img_params['mime']), self::$_images_ext)
							)
							{
								# Ресайз
								if ($field['atribute'] != '')
								{
									@list($img_w, $img_h) = explode('x', $field['atribute']);
									if ($resized_img = Image::getImage($tmp_file, $img_w, $img_h)) file_put_contents(_ABS_UPLOADS_.$file_name, $resized_img);
								}
								else move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);

								# Записываем в поле
								$update_fields['f_'.$field['id']] = $file_name;
							}
						break;

						# Аудио
						case 'audio':
							if (in_array($file_ext, self::$_audios_ext) && ($field_file['type'] == 'audio/mpeg'))
							{
								move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
								$update_fields['f_'.$field['id']] = $file_name;
							}
						break;

						# Видео
						case 'video':
							if (in_array($file_ext, self::$_videos_ext) && ($field_file['type'] == 'application/octet-stream'))
							{
								move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
								$update_fields['f_'.$field['id']] = $file_name;
							}
						break;

						# Файл
						case 'file':
							if (($field['atribute'] != '') && ($allowed_exts = explode(',', $field['atribute'])) && in_array($file_ext, $allowed_exts))
							{
								move_uploaded_file($tmp_file, _ABS_UPLOADS_.$file_name);
								$update_fields['f_'.$field['id']] = $file_name;
							}
						break;
					}
				}
			}
		}

		# Обновляем поля
		if (count($update_fields))
		{
			# Языковая версия или смена доп шаблона
			if (self::$_db->count('class_'.$class['id'], "WHERE `object_id`='".$object['id']."' AND `lang`='".Core::$_lang."' LIMIT 1", '`id`') == 0)
			{
				self::$_db->insert('class_'.$class['id'], array('object_id'=>$object['id'], 'lang'=>Core::$_lang));
			}

			# ОБНОВЛЯЕМ
			self::$_db->update('class_'.$class['id'], $update_fields, "WHERE `object_id`='".$object['id']."' AND `lang`='".Core::$_lang."'", false);
		}

		return true;
	}



	# КОПИРОВАНИЕ ОБЪЕКТА
	public static function copyObject($object_id, $dst_id=false, $dst_checked=false)
	{
		# Проверяем объект
		if ((!$object_id = filter_var($object_id, FILTER_VALIDATE_INT)) || ($object_id < 0) || ($object_id == $dst_id) || (!$object = self::$_db->select('objects', "WHERE `id`='$object_id' LIMIT 1"))) return false;

		# Проверяем путь
		if (is_int($dst_id = filter_var($dst_id, FILTER_VALIDATE_INT)) && ($dst_id >= 0))
		{
			if (
				!$dst_checked && ($dst_id > 0) &&
				(!Model::$_db->select('objects', "WHERE `id`='$dst_id' LIMIT 1", "`id`") || is_array(self::getMothers($dst_id, $object_id)))
			) return false;
		}
		else $dst_id = false;

		# Составляем копию
		$clone_obj = array(
			'mother'	=> (is_int($dst_id) ? $dst_id : $object['mother']),
			'class_id' 	=> $object['class_id'],
			'fix_class'	=> $object['fix_class'],
			'active'	=> $object['active'],
			'name'	   	=> (!is_int($dst_id) ? 'Копия ':'').$object['name'],
			'c_date'	=> time(),
			'm_date'	=> time(),
			'sort'		=> number_format(microtime(true), 2, '', '')
		);

		# Добавляем копию объекта
		if (!$clone_obj['id'] = self::$_db->insert('objects', $clone_obj)) return false;

		# Обновляем счетчик материнского объекта
		self::freshCounter($clone_obj['mother']);

		# Поля объекта
		if (
			($object['class_id'] > 0) && ($class_fields = self::getClassFields($object['class_id'])) &&
			($object_fields = self::$_db->select('class_'.$object['class_id'], "WHERE `object_id`='$object_id' ORDER BY `id` ASC"))
		)
		{
			# Языковые версии
			foreach($object_fields as $lang_version)
			{
				# Меняем ID объекта
				unset($lang_version['id']);
				$lang_version['object_id'] = $clone_obj['id'];

				# Прбегаемся по полям основного шаблона
				foreach($class_fields as $field)
				{
					# HTML
					if ($field['type'] == 'html') $lang_version['f_'.$field['id']] = Helpers::escape_html($lang_version['f_'.$field['id']]);
					else $lang_version['f_'.$field['id']] = Helpers::escape($lang_version['f_'.$field['id']]);

					# Файловый тип поля и поле заполнено
					if (in_array($field['type'], self::$_files_fields) && ($file_name = $lang_version['f_'.$field['id']]) && ($file_name != ''))
					{
						$new_file_name = $lang_version['f_'.$field['id']] = preg_replace('#^'.$object_id.'-'.$field['id'].'-#', $clone_obj['id'].'-'.$field['id'].'-', $file_name);
						copy(_ABS_UPLOADS_.$file_name, _ABS_UPLOADS_.$new_file_name);
					}

					# Доп.шаблон
					if (
						($field['type'] == 'template') && ($ad_class_id = filter_var($lang_version['f_'.$field['id']], FILTER_VALIDATE_INT)) &&
						($ad_class_fields = self::getClassFields($ad_class_id)) &&
						($ad_object_fields = Model::$_db->select('class_'.$ad_class_id, "WHERE `object_id`='$object_id'"))
					)
					{
						foreach($ad_object_fields as $ad_lang_version)
						{
							# Меняем ID объекта
							unset($ad_lang_version['id']);
							$ad_lang_version['object_id'] = $clone_obj['id'];

							# Прбегаемся по полям доп.шаблона
							foreach($ad_class_fields as $ad_field)
							{
								# Файловый тип поля и поле заполнено
								if (in_array($ad_field['type'], self::$_files_fields) && ($file_name = $ad_lang_version['f_'.$ad_field['id']]) && ($file_name != ''))
								{
									$new_file_name = $ad_lang_version['f_'.$ad_field['id']] = preg_replace('#^'.$object_id.'-'.$ad_field['id'].'-#', $clone_obj['id'].'-'.$ad_field['id'].'-', $file_name);
									copy(_ABS_UPLOADS_.$file_name, _ABS_UPLOADS_.$new_file_name);
								}
							}

							# Добавляем языковую версию в основной шаблон
							self::$_db->insert('class_'.$ad_class_id, $ad_lang_version, false);
						}

					}
				}

				# Добавляем языковую версию в основной шаблон
				self::$_db->insert('class_'.$object['class_id'], $lang_version, false);
			}
		}

		# Клонируем вложенные объекты
		if (($object['inside'] > 0) && ($inside_objects = self::$_db->select('objects', "WHERE `mother`='$object_id' ORDER BY `sort` ASC", "`id`")))
		{
			foreach($inside_objects as $o_id)
			{
				if (!self::copyObject($o_id, $clone_obj['id'], true)) return false;
			}
		}

		return $clone_obj['id'];
	}

	# ПЕРЕНОС ОБЪЕКТА
	public static function moveObject($object_id, $dst_id, $dst_checked=false)
	{
		# Валидация данных
		if (
			(!$object_id = filter_var($object_id, FILTER_VALIDATE_INT)) || ($object_id < 0) ||
			!is_int($dst_id = filter_var($dst_id, FILTER_VALIDATE_INT)) || ($dst_id < 0) ||
			($dst_id == $object_id) ||
			(!$src_object = self::$_db->select('objects', "WHERE `id`='$object_id' LIMIT 1", "`mother`")) ||
			(($dst_id > 0) && (!self::$_db->select('objects', "WHERE `id`='$dst_id' LIMIT 1", "`id`") || is_array(Model::getMothers($dst_id, $object_id)))) ||
			($dst_id == $src_object['mother'])
		) return false;

		# Меняем материнский ID
		Model::$_db->update('objects', array('mother'=>$dst_id), "WHERE `id`='$object_id' LIMIT 1");

		# Обновляем счетчики
		self::freshCounter($src_object['mother'], '-');
		self::freshCounter($dst_id);

		return true;
	}

	# УДАЛЕНИЕ ОБЪЕКТА
	public static function deleteObject($object_id)
	{
		# Проверка объекта
		if ((!$object_id = filter_var($object_id, FILTER_VALIDATE_INT)) || ($object_id <= 0) || !is_object($object = self::getObject($object_id, true, false))) return false;

		# Рекурсивно удаляем вложенные объекты
		if (($object['inside'] > 0) && ($inside_objects = self::$_db->select('objects', "WHERE `mother`='$object_id'", "`id`")))
		{
			foreach($inside_objects as $obj_id) self::deleteObject($obj_id);
		}

		# Удаляем объект из таблицы объектов
		self::$_db->delete('objects', "WHERE `id`='$object_id'");

		# Обновляем счетчики
		self::freshCounter($object['mother'], '-');

		# Шаблон задан
		if (($object['class_id'] > 0) && ($class_fields = self::getClassFields($object['class_id'])))
		{
			# Удаляем из таблицы шаблона
			self::$_db->delete('class_'.$object['class_id'], "WHERE `object_id`='$object_id'");

			# Ищем наличие файловых полей и доп.шаблонов
			$file_fields_exist = false;
			foreach($class_fields as $field)
			{
				# Файловое поле
				if (in_array($field['type'], self::$_files_fields)) $file_fields_exist = true;

				# Поле доп.шаблона
				else if (
					($field['type'] == 'template') && isset($object[$field['name']]) &&
					($add_class_id = filter_var($object[$field['name']], FILTER_VALIDATE_INT)) && ($add_class_id > 0) && ($add_class_fields = self::getClassFields($add_class_id))
				)
				{
					# Удаляем из таблицы доп.шаблона
					self::$_db->delete('class_'.$add_class_id, "WHERE `object_id`='$object_id'");

					# Если еще не найдены файловые поля ищем в доп.шаблоне
					if (!$file_fields_exist)
					{
						foreach ($add_class_fields as $ad_field)
						{
							if (in_array($ad_field['type'], self::$_files_fields))
							{
								$file_fields_exist = true;
								break;
							}
						}
					}
				}
			}

			# Если есть файловые поля удаляем файлы объекта
			if ($file_fields_exist)
			{
				self::removeObjectFiles($object_id);
				self::removeObjectFiles($object_id, 'resized'._SEP_);
			}
		}

		return true;
	}



	# СПИСОК ШАБЛОНОВ БЕЗ ПОЛЕЙ
	public static function getClasses($all=false, $additional=false)
	{
		if ($classes = self::$_db->select('classes', ($all == false ? "WHERE `additional`='".($additional ? 1 : 0)."' " : '')."ORDER BY `protected` DESC, `additional` ASC, `id` ASC")) return $classes;
		return false;
	}

	# ПОЛУЧЕНИЕ ШАБЛОНА С ПОЛЯМИ
	public static function getClass($class_id)
	{
		if (!filter_var($class_id, FILTER_VALIDATE_INT) || ($class_id <= 0)) return false;
		else if (isset(self::$_classes[$class_id]['name'])) return self::$_classes[$class_id];

		if ($class = self::$_db->select('classes', "WHERE `id`='$class_id' LIMIT 1"))
		{
			# Полей нет
			if (!isset(self::$_classes[$class_id]['fields']))
			{
				 self::$_classes[$class_id] = $class;
				 self::getClassFields($class_id);
			}
			else self::$_classes[$class_id] = array_merge($class, self::$_classes[$class_id]);

			# Кэшируем как объект
			self::$_classes[$class_id] = new _Template(self::$_classes[$class_id]);

			# Отдаем
			return self::$_classes[$class_id];
		}

		return false;
	}

	# ПОЛУЧЕНИЕ ПОЛЕЙ ШАБЛОНА
	private static function getClassFields($class_id)
	{
		if (!filter_var($class_id, FILTER_VALIDATE_INT) || ($class_id <= 0)) return false;
		else if (isset(self::$_classes[$class_id]['fields'])) return self::$_classes[$class_id]['fields'];
		else if ($class_fields = self::$_db->select('fields', "WHERE `class_id`='$class_id' ORDER BY `sort` ASC", "`id`, `name`, `type`, `atribute`"))
		{
			# Сохраняем в кэш по ID
			foreach($class_fields as $f) self::$_classes[$class_id]['fields'][$f['id']] = $f;
			unset($class_fields, $f);

			return self::$_classes[$class_id]['fields'];
		}
		return false;
	}



	# ДОБАВЛЕНИЕ ШАБЛОНА
	public static function addClass($class, $fields)
	{
		# Валидация данных
		if (!isset($class['name']) || ($class['name'] == '') || (self::$_db->count('classes', "WHERE `name`='".self::$_db->prepare($class['name'])."'", 'id') > 0) || !is_array($fields)) return false;

		# Обрабатываем шаблон
		$add_class = array(
			'additional'	=> (isset($class['additional']) && in_array($class['additional'], array(0,1))) ? $class['additional'] : 0,
			'name'			=> $class['name'],
			'desc'			=> isset($class['desc']) ? $class['desc'] : ''
		);

		# Обрабатываем поля
		$class_fields = array();
		foreach($fields as $sort=>$field)
		{
			if (isset($field['name']) && ($field['name'] != '') && isset($field['type']) && isset(self::$_tfields[$field['type']]))
			{
				$class_fields[] = array(
					'name'		=> $field['name'],
					'type'		=> $field['type'],
					'atribute'	=> isset($field['atribute']) ? $field['atribute'] : '',
					'sort'		=> ($sort+1)
				);
			}
		}
		unset($fields, $sort, $field);
		if (count($class_fields) == 0) return false;

		# Добавляем в таблицу шаблонов
		$class_id = self::$_db->insert('classes', $add_class);

		# Добавляем в таблицу полей
		foreach($class_fields as $k=>$field)
		{
			$class_fields[$k]['class_id'] = $class_id;
			$class_fields[$k]['id'] = self::$_db->insert('fields', $class_fields[$k]);
		}

		# Создаем таблицу шаблона
		$query = array(
			"`id` int(10) NOT NULL AUTO_INCREMENT",
			"`object_id` int(10) NOT NULL",
			"`lang` char(2) NOT NULL DEFAULT 'ru'"
		);
		foreach($class_fields as $field) $query[] = "`f_".$field['id']."` ".self::$_tfields[$field['type']]['sql'];
		$query[] = "PRIMARY KEY (`id`), KEY `object` (`object_id`,`lang`)";
		$query 	 = "CREATE TABLE IF NOT EXISTS `class_".$class_id."` (".join(", \n", $query).") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		# ДОБАВЛЯЕМ ТАБЛИЦУ ШАБЛОНА
		self::$_db->query($query);

		return $class_id;
	}

	# РЕДАКТИРОВАНИЕ ШАБЛОНА
	public static function editClass($class, $fields=false)
	{
		# В каком виде пришел шаблон
		if (!is_array($class) && (!$class = filter_var($class, FILTER_VALIDATE_INT))) return false;
		if (is_int($class)) $class = array('id'=>$class);

		# Получаем текущий шаблон
		if (!isset($class['id']) || (!$edit_class = self::getClass($class['id']))) return false;

		# Валидация шаблона
		$new_class = array();
		if (isset($class['additional']) && in_array($class['additional'], array(0,1)) && ($class['additional'] != $edit_class['additional']))
			$new_class['additional'] = (int)$class['additional'];
		if (isset($class['name']) && ($class['name'] != '') && ($class['name'] != $edit_class['name']))
		{
			$new_class['name'] = self::$_db->prepare($class['name']);
			if (self::$_db->count('classes', "WHERE `id`!='".$class['id']."' AND `name`='".$new_class['name']."'", 'id') > 0) return false;
		}
		if (isset($class['desc']) && ($class['desc'] != $edit_class['desc']))
			$new_class['desc'] = $class['desc'];

		# ОБНОВЛЯЕМ ШАБЛОН
		if (count($new_class))
		{
			self::$_db->update('classes', $new_class, "WHERE `id`='".$class['id']."' LIMIT 1");

			# Поменялся тип шаблона
			if (isset($new_class['additional']) && ($new_class['additional'] != $edit_class['additional']))
			{
				# Старый тип - обычный
				if ($edit_class['additional'] == 0)
				{
					# Удаляем объекты
					if ($objects = self::getObjects(-1, $edit_class['id'], true, ''))
					{
						foreach ($objects as $o) self::deleteObject($o['id']);
					}
					unset($objects, $o);
				}

				# Старый тип - дополнительный
				else
				{
					foreach($edit_class['fields'] as $field)
					{
						# Файловый тип
						if (in_array($field['type'], self::$_files_fields))
						{
							# Удаляем файлы
							self::removeObjectFiles('-'.$field['id']);
							if ($field['type'] == 'image') self::removeObjectFiles('-'.$field['id'], 'resized'._SEP_);
						}
					}
				}

				# Очищаем таблицу
				self::$_db->query("TRUNCATE TABLE `class_".$edit_class['id']."`");
			}
		}

		# ПОЛЯ ШАБЛОНА
		if (is_array($fields))
		{
			# Поля на удаление
			$remove_fields = $edit_class['fields'];

			# Пробегаемся по полям
			foreach($fields as $k=>$field)
			{
				if (isset($field['name']) && ($field['name'] != '') && isset($field['type']) && ($field['type'] != '') && isset(self::$_tfields[$field['type']]))
				{
					# НОВОЕ ПОЛЕ
					if (!isset($field['id']) || (!$field['id'] = filter_var($field['id'], FILTER_VALIDATE_INT)))
					{
						# Добавляем поле
						if ($add_field_id = self::$_db->insert('fields', array(
							'class_id'	=> $class['id'],
							'name'		=> $field['name'],
							'type'		=> $field['type'],
							'atribute'	=> $field['atribute'],
							'sort'		=> ($k+1)
						)))
						{
							# Добавляем в таблицу шаблона
							self::$_db->query("ALTER TABLE `class_".$class['id']."` ADD `f_".$add_field_id."` ".self::$_tfields[$field['type']]['sql']);
						}
					}

					# СУЩЕСТВУЮЩЕЕ ПОЛЕ
					else if (isset($edit_class['fields'][$field['id']]) && ($edit_field = $edit_class['fields'][$field['id']]))
					{
						# Поле изменилось
						if (($field['name'] != $edit_field['name']) || ($field['type'] != $edit_field['type']) || ($field['atribute'] != $edit_field['atribute']))
						{
							# Меняем в полях
							if (self::$_db->update('fields', array(
								'name'		=> $field['name'],
								'type'		=> $field['type'],
								'atribute'	=> $field['atribute'],
								'sort'		=> ($k+1)
							),
							"WHERE `id`='".$field['id']."' LIMIT 1"))
							{
								# Поменялся тип
								if ($field['type'] != $edit_field['type'])
								{
									# Если старый тип-файловый а новый нет
									if (in_array($edit_field['type'], self::$_files_fields) && !in_array($field['type'], self::$_files_fields))
									{
										# Удаляем файлы
										self::removeObjectFiles('-'.$edit_field['id']);
										if ($edit_field['type'] == 'image') self::removeObjectFiles('-'.$edit_field['id'], 'resized'._SEP_);
									}

									# Меняем тип поля в таблице шаблона
									self::$_db->query("ALTER TABLE `class_".$class['id']."` CHANGE `f_".$field['id']."` `f_".$field['id']."` ".self::$_tfields[$field['type']]['sql']);
								}
							}
						}

						# Поле не поменялось обновляем сортировку
						else self::$_db->update('fields', array('sort'=>($k+1)), "WHERE `id`='".$field['id']."' LIMIT 1");

						# Удаляем из списка на удаление
						unset($remove_fields[$field['id']]);
					}
				}
			}

			# Удаление полей
			foreach($remove_fields as $field)
			{
				# Файловый тип
				if (in_array($field['type'], self::$_files_fields))
				{
					# Удаляем файлы
					self::removeObjectFiles('-'.$field['id']);
					if ($field['type'] == 'image') self::removeObjectFiles('-'.$field['id'], 'resized'._SEP_);
				}

				# Удаляем поле
				self::$_db->query("ALTER TABLE `class_".$class['id']."` DROP `f_".$field['id']."`");
				self::$_db->delete('fields', "WHERE `id`='".$field['id']."'");
			}
		}

		return true;
	}

	# УДАЛЕНИЕ ШАБЛОНА
	public static function removeClass($class_id)
	{
		# Проверка шаблона
		if (!filter_var($class_id, FILTER_VALIDATE_INT) || (!$class = self::getClass($class_id)) || ($class['protected'] == 1)) return false;

		# ОБЫЧНЫЙ ШАБЛОН
		if ($class['additional'] == 0)
		{
			# Удалеяем объекты с указанным шаблоном
			if ($class_objects = Model::getObjects(-1, $class['id'], false, "1=1"))
			{
				foreach($class_objects as $o) self::deleteObject($o['id']);
			}
			unset($class_objects, $o);
		}

		# ДОПОЛНИТЕЛЬНЫЙ ШАБЛОН
		else
		{
			# Ищем файловые поля
			foreach($class['fields'] as $field)
			{
				if (in_array($field['type'], self::$_files_fields))
				{
					# Удаляем файлы
					self::removeObjectFiles('-'.$field['id']);
					if ($field['type'] == 'image') self::removeObjectFiles('-'.$field['id'], 'resized'._SEP_);
				}
			}
		}

		# Удаляем шаблон из таблицы шаблонов
		self::$_db->delete('classes', "WHERE `id`='".$class['id']."' LIMIT 1");

		# Удаляем поля шаблона
		self::$_db->delete('fields', "WHERE `class_id`='".$class['id']."'");

		# Удаляем таблицу шаблона
		self::$_db->query("DROP TABLE `class_".$class['id']."`");

		return true;
	}



	# УДАЛЕНИЕ ФАЙЛА
	private static function removeObjectFile($file)
	{
		if (preg_match('#^[0-9]{1,10}-[0-9]{1,5}-#', $file) && is_file(_ABS_UPLOADS_.$file))
		{
			unlink(_ABS_UPLOADS_.$file);
			return true;
		}

		return false;
	}

	# УДАЛЕНИЕ ИЗОБРАЖЕНИЯ
	private static function removeObjectImage($file)
	{
		if (preg_match('#^[0-9]{1,10}-[0-9]{1,5}-#', $file, $match) && is_file(_ABS_UPLOADS_.$file))
		{
			# Удаляем кэши
			if (is_dir($resized_dir_path = _ABS_UPLOADS_.'resized'._SEP_))
			{
				foreach(scandir($resized_dir_path) as $f)
				{
					if (in_array($f, array('.','..'))) continue;
					else if (preg_match('#^'.$match[0].'#', $f))
					{
						unlink($resized_dir_path.$f);
					}
				}
			}

			# Удаляем основное изображение
			unlink(_ABS_UPLOADS_.$file);

			return true;
		}

		return false;
	}

	# УДАЛЕНИЕ ФАЙЛОВ ОБЪЕКТА ПО ПРЕФИКСУ
	private static function removeObjectFiles($prefix, $dir='')
	{
		if (!is_dir($dir = _ABS_UPLOADS_.$dir))						return false;
		else if (filter_var($prefix, FILTER_VALIDATE_INT) > 0)		$prefix .= '-';
		else if (preg_match('#^[0-9]{1,10}-[0-9]{1,5}$#', $prefix))	$prefix .= '-';
		else if (preg_match('#^-[0-9]{1,5}$#', $prefix))			$prefix  = '[0-9]{1,10}'.$prefix.'-';
		else return false;

		foreach(scandir($dir) as $f)
		{
			if (in_array($f, array('.','..'))) continue;
			else if (preg_match('#^'.$prefix.'(.*)#', $f)) unlink($dir.$f);
		}

		return true;
	}
}