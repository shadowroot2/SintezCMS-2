<?php
# ПРЕДСТАВЛЕНИЕ (ШАБЛОНИЗАТОР) v.2.4
# Coded by ShadoW (C) 2014
defined('_CORE_') or die('Доступ запрещен');

class View extends Singleton
{
	protected static $_init			= false;

	public static $_debug 			= false;
	public static $_template 		= '_';

	private static $_vars  			= array();
	private static $_global_vars 	= array();

	private static $_styles 		= array();
	private static $_jscripts		= array();

	private static $_content		= '';

	# ИНИЦИАЛИЗАЦИЯ
	public static function _init()
	{
		# Проверка инициализации
		if (self::$_init) return;
		self::$_init = true;

		# Дебаг
		self::$_debug = (bool)Core::$_debug;

		# Папка скомпилированных шаблонов
		if (!is_dir(_ABS_CACHE_.'templates'))
		{
			try
			{
				if (!@mkdir(_ABS_CACHE_.'templates', 0777, true))
				{
					throw new Exception('Невозможно создать папку скомпилированных шаблонов <b>'._ABS_CACHE_.'templates'._SEP_.'</b>');
				}

			} catch(Exception $e) { Errors::exception_handler($e); die; }
		}
	}

	# ЗАДАНИЕ ПЕРЕМЕННЫХ
	public static function assign($key, $value=false)
	{
		if (is_array($key))
		{
			foreach($key as $k=>$v) self::assign($k,$v);
		}
		else if (substr($key,0,1) == '%') self::$_global_vars[substr($key,1)] = $value;
		else self::$_vars[$key] = $value;
	}

	# ПЕРЕНАЗНАЧЕНИЕ ПЕРЕМЕННЫХ
	public static function reassign($key, $value = false)
	{
		$str_exec	= '';
		$key		= explode('.', $key);
		foreach($key as $k) $str_exec .= "['$k']";
		if (substr($key[0],0,1) == '%') eval('self::$_global_vars'.str_replace('%', '', $str_exec).' = $value;');
		else eval('self::$_vars'.$str_exec.' = $value;');
	}

	# УДАЛЕНИЕ ПЕРЕМЕННЫХ
	public static function unassign($key)
	{
		if (substr($key,0,1) == '%') unset(self::$_global_vars[substr($key,1)]);
		else unset(self::$_vars[$key]);
	}

	# ОЧИСТКА ВРЕМЕННЫХ ПЕРЕМЕННЫХ
	private static function clear_vars()
	{
		self::$_vars = array();
	}

	# ПУТЬ К СКОМПИЛИРОВАНОМУ ШАБЛОНУ
	private static function compiledPath($path)
	{
		return str_replace(array('\\', '/'), _SEP_, _ABS_CACHE_.'templates'._SEP_.md5($path).'.cache');
	}

	# ПРОВЕРКА СКОМПИЛИРОВАНОСТИ ШАБЛОНА
	private static function is_compiledTemplate($path)
	{
		# Путь
		$compiled_path = self::compiledPath($path);

		# Проверяем наличие файла и изменялся ли шаблон
		if (!is_readable($compiled_path) || (filemtime($compiled_path) < filemtime(_ABS_TPL_.$path))) return false;

		return $compiled_path;
	}

	# КОМПИЛИРОВАНИЕ (ПАРСИНГ ШАБЛОНА И ЗАПИСЬ В ФАЙЛ)
	private static function compileTemplate($template)
	{
		# Содержимое файла
		$template_content = file_get_contents(_ABS_TPL_.$template);

		# Убираем PHP вставки
		$template_content = preg_replace('#<\?.*\?>#isU', '', $template_content);

		# Массивы через точку
		$template_content = preg_replace_callback('#(\$[\w->\[\]\'"]+\.[\w.]+)#i', array(__CLASS__, 'fetch_dot_array'), $template_content);

		# Вывод не пустой переменной
		$template_content = preg_replace_callback('#\[(.[^\[\]]*){(\$.[^}]+)}(.[^\[\]]*)\]#iU',array(__CLASS__, 'fetch_optional'),$template_content);

		# Модификаторы (не работает)
		# $template_content = preg_replace_callback('#(\$[\w.\[\]\'"->]+)\|(.[^}]+)#i', array(__CLASS__, 'fetch_modificators'), $template_content);

		# Инклуды (не работает)
		# $template_content = preg_replace_callback('#{\s*include\s*file=["|\']?(.*)["|\']?}#iU',array(__CLASS__, 'fetch_include'),$template_content);

		# ЗАМЕНЫ
		$search = array(

			# Комментарии
			'#\s*<!--[^\[if].*-->#isU',

			# Переменные
			'#{\$(\S[^()+=/%]+)}#iU',

			# Обычные условия
			'#({switch.*})\s+({case)#msU',
			'#{\s*if\s*\(?\s*(\$\S*)\s*\)?\s*}#isU',
			'#{\s*if\s*\(?\s*!(\$\S*)\s*\)?\s*}#isU',
			'#{\s*else\s*if\s*\(?\s*(\$\S*)\s*\)?\s*}#isU',
			'#{\s*else\s*if\s*\(?\s*!(\$\S*)\s*\)?\s*}#isU',

			# if, foreach, for, switch
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*\((.*)\)\s*}(.*){\s*/\\1\s*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{\s*(if|foreach|for|switch)\s*(.*)\s*}(.+){\s*/\\1\*}#imsU',
			'#{else\s*if\s*\((.*)\)}#iU',
			'#{else\s*if\s*(.*)}#iU',
			'#{else}#iU',

			'#{case\t*(.*)}#iU',
			'#{break}#iU',
			'#{default}#iU',

			'#{\?(.*)}#U',
			'#{!(.*)}#U',
			'#{_\s*!?(.*)}#U',
		);
		$replace = array(

			# Комментарии
			'',

			# Переменные
			'<?=(isset($\\1) ? $\\1 : \'\')?>',

			# Switch
			'$1$2',
			'{if(isset(\\1) && \\1)}',
			'{if(isset(\\1) && !\\1)}',
			'{elseif(isset(\\1))}',
			'{elseif(isset(\\1) && !\\1)}',

			# if, foreach, for, switch
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<? $1 ($2){ ?>$3<? } ?>',
			'<?} elseif (\\1) { ?>',
			'<?} elseif (\\1) { ?>',
			'<?} else { ?>',

			'<? case $1:?>',
			'<? break;?>',
			'<? default:?>',

			'<?=\\1?>',
			'<?\\1;?>',
			'<?=t(\'\\1\');?>',
		);
		$template_content = preg_replace($search, $replace, $template_content);

		# СОХРАНЯЕМ
		$compile_path = self::compiledPath($template);
		try
		{
			if (@file_put_contents($compile_path, _FILE_SECURITY_.PHP_EOL.$template_content, LOCK_EX))
			{
				@chmod($compile_path, 0777);
				return $compile_path;
			}
			else throw new Exception('Невозможно записать шаблон <b>'.$template.'</b> в файл <b>'.$compile_path.'</b>');

		} catch(Exception $e) { Errors::exception_handler($e); die; }
	}

	# МАССИВЫ ЧЕРЕЗ ТОЧКУ
	private static function fetch_dot_array($matches)
	{
		$pieces  = explode('.', $matches[1]);
		$result  = array_shift($pieces);
		$pieces  = array_map(create_function('$a','return is_numeric($a) ? $a : "\'$a\'";'), $pieces);
		$result .= '['.join('][',$pieces).']';

		return $result;
	}

	# МОДИФИКАТОРЫ (НЕ РАБОТАЕТ)
	/*private static function fetch_modificators($matches)
	{
		$mods = explode('|',$matches[2]);
		$var = $matches[1];
		foreach($mods as $mod)
		{
			$params = explode(':',$mod);

			if (count($params) > 1) $mod = array_shift($params);
			else $params = false;

			switch($mod)
			{
				case 'default':
				 return '? isset('.$var.') ? '.$var.' : '.$params[0];
				break;

				default:
				 if(!is_array($params)) $params[] = $var;
				 else array_unshift($params, $var);
				break;
			}

			$var = $mod.'(';
			if ($params) $var .= implode(',',$params);
			$var .= ')';
		}
		return '? '.$var;
	}*/

	# ИНКЛУД ШАБЛОНОВ (НЕ РАБОТАЕТ)
	/*private static function fetch_include($matches)
	{
		$path = trim($matches[1], '\'"');
		$ext  = explode('.', $path);
		if (!in_array($ext[sizeof($ext)-1], array('html','php'))) return 'Тип файла шаблона "<b>'.$ext[sizeof($ext)-1].'</b>" запрещен!';
		if (!is_file(_ABS_TPL_.$path)) return 'Файл шаблона <b>'._ABS_TPL_.$path.'</b> не найден!';

		return '<? View::getTemplate(\''.$ext[0].'\'); ?>';
	}*/

	# ВЫВОД НЕ ПУСТОЙ ПЕРЕМЕНОЙ []
	private static function fetch_optional($matches)
	{
		$start	= addslashes(str_replace('\'','"', $matches[1]));
		$end	= addslashes(str_replace('\'','"', $matches[3]));
		$var	= $matches[2];

		return '<?=(!empty('.$var.') ? " '.$start.'".'.$var.'."'.$end.'" : \'\')?>';
	}

	# СКАНИРОВАНИЕ ПАПКИ ШАБЛОНА НА НАЛИЧИЕ СТИЛЕЙ И СКРИПТОВ
	private static function scanTemplateFolder($template)
	{
		# Папка шаблона
		$template_dir = dirname(_ABS_TPL_.$template)._SEP_;

		# CSS - файлы
		if (($css_dir = $template_dir.'css'._SEP_) && is_dir($css_dir))
		{
			if (is_array($found = Core::find_file($css_dir, NULL, 'css')))
			{
				$add_css = array();
				foreach($found as $path)
				{
					$path = str_replace(array(_ABS_TPL_, '\\'), array(_TPL_, '/'), $path);
					if (!in_array($path, self::$_styles)) $add_css[] = $path;
				}
				self::$_styles = array_merge($add_css, self::$_styles);
			}
		}

		# JS - файлы
		if (($js_dir = $template_dir.'js'._SEP_) && is_dir($js_dir))
		{
			if (is_array($found = Core::find_file($js_dir, NULL, 'js')))
			{
				$add_js = array();
				foreach($found as $path)
				{
					$path = str_replace(array(_ABS_TPL_, '\\'), array(_TPL_, '/'), $path);
					if (!in_array($path, self::$_jscripts)) $add_js[] = $path;
				}
				self::$_jscripts = array_merge($add_js, self::$_jscripts);
			}
		}
	}

	# ДОБАВЛЕНИЕ СТИЛЕЙ
	public static function addCSS($url)
	{
		if (is_array($url))
		{
			foreach($url as $u) self::addCSS($u);
		}
		else if (!in_array($url, self::$_styles))
		{
			self::$_styles[] = $url;
			return true;
		}

		return false;
	}

	# ДОБАВЛЕНИЕ СКРИПТОВ
	public static function addJS($url)
	{
		if (is_array($url))
		{
			foreach($url as $u) self::addJS($u);
		}
		else if (!in_array($url, self::$_jscripts))
		{
			self::$_jscripts[] = $url;
			return true;
		}

		return false;
	}

	# ЗАМЕНА ЗАГОЛОВКОВ В ОСНОВНОМ ШАБЛОНЕ
	private static function replace_Head($content)
	{
		extract(self::$_global_vars);

		# Заголовки по умолчанию
		$primary_head = array(
			'	<meta charset="utf-8" />',
			'	<meta name="viewport" content="width=device-width" />',
			#'	<meta http-equiv="content-type" content="text/html; charset=utf-8" />',
			'	<title>'.(isset($page_title) ? $page_title : '').' | '.(isset($site['title']) ? $site['title'] : '').'</title>',
			'	<meta name="Author" content="Dmitry Trigub" />',
			'	<meta name="Keywords" content="'.(!empty($site['keywords']) ? $site['keywords'] : '').'"  />',
			'	<meta name="Description" content="'.(!empty($site['description']) ? $site['description'] : '').'"  />',
			'	<link rel="shortcut icon" type="image/png" href="'._TPL_.'favicon.png" />',
			#'	<link rel="shortcut icon" href="'._TPL_.'favicon.ico" type="image/x-icon" />',
			'	<script type="text/javascript" src="'._TPL_.'jquery.js?v=1.10.2"></script>',
			'	<script type="text/javascript" src="'._TPL_.'js/fancybox/jquery.mousewheel.pack.js?v=3.0.6"></script>',
			'	<script type="text/javascript" src="'._TPL_.'js/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>',
			'	<link rel="stylesheet" type="text/css" href="'._TPL_.'js/fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />',
		);

		# CSS
		foreach(self::$_styles as $style)		$primary_head[] = '	<link href="'.$style.'" rel="stylesheet" type="text/css" />';

		# JS
		foreach(self::$_jscripts as $script)	$primary_head[] = '	<script type="text/javascript" src="'.$script.'"></script>';

		# Шапка CMS на сайте
		if (
			!defined('_CMS_') && isset($_SESSION['cms_auth']) && is_array($_SESSION['cms_auth']) &&
			preg_match('#<body.*>#i', $content, $body_tag) && ($cms_head_tpl = _ABS_ROOT_.'cms'._SEP_.'public'._SEP_.'templates'._SEP_.'site'._SEP_.'cms_head.html') && is_file($cms_head_tpl)
		)
		{
			$primary_head = array_merge($primary_head, array(
				'	<link rel="stylesheet" type="text/css" href="/cms/public/templates/site/css/head.css" />',
				'	<script type="text/javascript" src="/cms/public/templates/site/js/head.js"></script>'
			));

			# Фронтэнд включен в ядре
			if (Core::$_frontend)
			{
				# Фронтэнд включен кнопкой
				if (isset($_SESSION['cms_auth']['front_end']) && ($_SESSION['cms_auth']['front_end'] == true))
				{
					$primary_head = array_merge($primary_head, array(
						'	<script type="text/javascript" src="/cms/public/templates/js/ckeditor/ckeditor.js"></script>',
						'	<script type="text/javascript" src="/cms/public/templates/js/ckeditor/adapters/jquery.js"></script>',
						'	<script type="text/javascript" src="/cms/public/templates/js/ckfinder/ckfinder.js"></script>',
						'	<script type="text/javascript" src="/cms/public/templates/js/jquery-ui-1.10.3.custom.min.js"></script>',
						'	<link rel="stylesheet" type="text/css" href="/cms/public/templates/css/jquery-ui-1.10.3.custom.css" />',
						'	<script type="text/javascript" src="/cms/public/templates/modules/objects/js/colorpicker.js"></script>',
						'	<script type="text/javascript" src="/cms/public/templates/modules/objects/js/gmapfield.js"></script>',
						'	<link rel="stylesheet" type="text/css" href="/cms/public/templates/site/css/frontend.css" />',
						'	<script type="text/javascript" src="/cms/public/templates/site/js/frontend.js"></script>'
					));
				}
			}

			# Шапка
			$content = str_replace($body_tag[0], $body_tag[0].str_replace(array('#user_name#', '#frontend_status#'), array($_SESSION['cms_auth']['name'], (Core::$_frontend ? ((isset($_SESSION['cms_auth']['front_end']) && $_SESSION['cms_auth']['front_end']) ? 'on' : 'off') : 'off')), file_get_contents($cms_head_tpl)), $content);
		}

		return str_replace('<head>', "<head>\n".join("\n", $primary_head), $content);
	}

	# ВСТАВКА #CONTENT#
	private static function flush_buffer($buffer)
	{
		$buffer = explode('#CONTENT#', $buffer);
		return self::replace_Head($buffer[0]).self::$_content.(isset($buffer[1]) ? $buffer[1] : '');
	}

	# ПОЛУЧЕНИЕ ШАБЛОНА
	public static function getTemplate($template, $save_content=true, $vars=false, $cache=false, $cache_time=false)
	{
		# HTML шаблон
		$template = str_replace(array('\\', '/'), _SEP_, $template).'.html';

		# Проверяем наличие шаблона
		try
		{
			if (!is_readable(_ABS_TPL_.$template)) throw new Exception('Файл шаблона <b>'._ABS_TPL_.$template.'</b> не найден или не может быть прочитан');
		}
		catch(Exception $e) { Errors::exception_handler($e); die; }

		# Сканируем папку шаблона JS и CSS
		self::scanTemplateFolder($template);

		# Проверяем наличие кэша если включено
		if ((Core::$_caching === false) || ($cache === false) || (!$output = Cache::load($template, $cache_time)))
		{
			# Переменные переданы
			if (is_array($vars))
			{
				self::clear_vars();
				self::assign($vars);
			}

			# Дебаг
			if (self::$_debug)
			{
				echo '
				<hr/>
				<div><h2>Шаблон: <i>'.$template.'</i></h2></div><br/>
				<div><b>Глобальные переменные:</b></div>';
				foreach(self::$_global_vars as $k=>$v) { echo '<div><b style="color:#239B09;">$'.$k.'</b> = '; if (!is_array($v) && !is_object($v)) {  echo "'$v'"; } else { echo '<pre>'; print_r($v); echo '</pre>'; } echo '</div>'; }
				echo '<br/><div><b>Обычные переменные:</b></div>';
				foreach(self::$_vars as $k=>$v) { echo '<div><b style="color:#239B09;">$'.$k.'</b> = '; if (!is_array($v) && !is_object($v)) {  echo "'$v'"; } else { echo '<pre>'; print_r($v); echo '</pre>'; } echo '</div>'; }
				echo '<hr/>';
			}

			# Компиляция шаблона
			if (!is_string($compiled_path = self::is_compiledTemplate($template))) $compiled_path = self::compileTemplate($template);

			# Обработка шаблона в буфере
			ob_start();
				extract(self::$_vars);
				extract(self::$_global_vars);
				include($compiled_path);
			$output = ob_get_clean();

			# Кэширование
			if (Core::$_caching && $cache) Cache::save($template, $output);
		}

		# Удаляем CMS вставки
		if (Core::$_frontend && (!isset($_SESSION['cms_auth']) || !isset($_SESSION['cms_auth']['front_end']) || ($_SESSION['cms_auth']['front_end'] == false)))
		{
			$output = preg_replace(array(
				'#\s?cms_add#isU',
				'#\s?cms_edit#isU',
				'#\s?cms_remove#isU',
				'#\s?data-cms-id=".*"#isU',
				'#\s?data-cms-add-type=".*"#isU',
				'#\s?data-cms-add-class=".*"#isU',
				'#\s?data-cms-add-fields=".*"#isU',
				'#\s?data-cms-edit-type=".*"#isU',
				'#\s?data-cms-edit-fields=".*"#isU'
			), '', $output);
		}

		# Запоминаем в конент если включено
		if ($save_content) self::$_content = $output;

		# Чистим переменные
		self::clear_vars();

		return $output;
	}

	# ОТОБРАЖЕНИЕ СТРАНИЦЫ
	public static function render($template=false, $save_content=true, $vars=false, $cache=false, $cache_time=false)
	{
		# UTF-8
		@header('Content-Type: text/html; charset=utf-8');

		# Если передан шаблон
		if (is_string($template)) self::getTemplate($template, $save_content, $vars, $cache, $cache_time);

		# Вставка в корневой шаблон
		if (is_string(self::$_template))
		{
			ob_start(array(__CLASS__, 'flush_buffer'));

				# Получаем основной шаблон
				echo self::getTemplate(self::$_template, false);

				# Заменяем #СONTENT#
				ob_start();
					echo self::$_content;
				self::$_content = ob_get_clean();

			ob_get_flush();
		}
		else echo self::$_content;
	}
}