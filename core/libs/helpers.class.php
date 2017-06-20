<?php
# ПОМОШНИКИ v.2.1
# Coded by ShadoW (c) 2015
defined('_CORE_') or die('Доступ запрещен');

class Helpers extends Singleton
{
	# РЕДИРЕКТ
	public static function redirect($url)
	{
		exit(header('Location: '.$url));
	}

	# ПЕРЕКЛЮЧАТЕЛЬ ЯЗЫКА
	public static function lang_sw($lang=false)
	{
		if (($lang != false) && isset(Core::$_langs[$lang]))
		{
			Core::$_data['sw_lang'] = Core::$_lang;
			Core::$_lang = $lang;
		}
		elseif (isset(Core::$_data['sw_lang']))
		{
			Core::$_lang = Core::$_data['sw_lang'];
			unset(Core::$_data['sw_lang']);
		}

		return true;
	}

	# ВЫВОД МАССИВА
	public static function sm($mass=array(), $title='Содержание массива', $color='#000')
	{
		echo '<hr><h2>'.$title.'</h2><pre style="color:'.$color.'; background-color:#fff; border-left:3px solid red; margin-left:20px; padding-left:20px;">';
		print_r($mass);
		echo '</pre>';
	}

	# ДАТЫ
	public static function date($format, $date)
	{
		# Локализации
		$locals = Array
		(
			# Русский
			'ru'=>Array
			(
				'at'=>'в',
				'mounth'=>Array(
							'01'=>'января',
							'02'=>'февраля',
							'03'=>'марта',
							'04'=>'апреля',
							'05'=>'мая',
							'06'=>'июня',
							'07'=>'июля',
							'08'=>'августа',
							'09'=>'сентября',
							'10'=>'октября',
							'11'=>'ноября',
							'12'=>'декабря'),
				'day'=>Array(
							'воскресенье',
							'понедельник',
							'вторник',
							'среда',
							'четверг',
							'пятница',
							'суббота')
			),

			# Английский
			'en'=>Array
			(
				'at'=>'',
				'mounth'=>Array(
							'01'=>'F',
							'02'=>'F',
							'03'=>'F',
							'04'=>'F',
							'05'=>'F',
							'06'=>'F',
							'07'=>'F',
							'08'=>'F',
							'09'=>'F',
							'10'=>'F',
							'11'=>'F',
							'12'=>'F'),
				'day'=>Array(
							'L',
							'L',
							'L',
							'L',
							'L',
							'L',
							'L')
			),

			# Казахский
			'kz'=>Array(
				'at'=>'',
				'mounth'=>Array(
							'01'=>'қаңтар',
							'02'=>'ақпан',
							'03'=>'наурыз',
							'04'=>'сәуір',
							'05'=>'мамыр',
							'06'=>'маусым',
							'07'=>'шілде',
							'08'=>'тамыз',
							'09'=>'қыркүйек',
							'10'=>'қазан',
							'11'=>'қараша',
							'12'=>'желтоқсан'),
				'day'=>Array(
							'жексенбі',
							'дүйсенбі',
							'сейсенбі',
							'жағдай',
							'бейсенбі',
							'жұма',
							'сенбі')
			)
		);

		return date(str_replace(
			array(
				'at',
				'mounth',
				'day'
			),
			array(
				$locals[Core::$_lang]['at'],
				$locals[Core::$_lang]['mounth'][date('m', $date)],
				$locals[Core::$_lang]['day'][date('w', $date)],
			),
		$format), $date);
	}

	# СКЛОНЕНИЕ
	public static function ndec($num, $words=array('элемент', 'элемента', 'элементов'))
	{
		$cases = array (2, 0, 1, 1, 1, 2);
		return $words[(($num%100>4 && $num%100<20) ? 2 : $cases[min($num%10, 5)])];
	}

	# ГЕНЕРАТОР ПАРОЛЕЙ
	public static function passgen($length=7)
	{
		return substr(md5(time()+rand(1000, 9999)), 0, $length);
	}


	# ОЧИСТКА ДЛЯ SQL
	public static function escape($data)
	{
		if (is_array($data))
		{
			foreach($data as $k=>$v) $data[$k] = addslashes(htmlspecialchars(stripslashes($v)));
		}
		else $data = addslashes(htmlspecialchars(stripslashes($data)));

		return $data;
	}

	# ОЧИСТКА ДЛЯ HTML
	public static function escape_html($data)
	{
		if (is_array($data))
		{
			foreach($data as $k=>$v) $data[$k] = str_replace("'", "\'", (stripslashes($v)));
		}
		else $data =  str_replace("'", "\'", (stripslashes($data)));

		return $data;
	}

	# ТРАНСЛИТ
	public static function translit($str)
	{
		return strtr(
			mb_strtolower( 
				strip_tags(
					preg_replace('/[^0-9a-zа-я-]+/iu', '_', $str)
				)
			),
			array(
				'а'=>'a',
				'б'=>'b',
				'в'=>'v',
				'г'=>'g',
				'д'=>'d',
				'е'=>'e',
				'ё'=>'yo',
				'ж'=>'zh',
				'з'=>'z',
				'и'=>'i',
				'й'=>'i',
				'к'=>'k',
				'л'=>'l',
				'м'=>'m',
				'н'=>'n',
				'о'=>'o',
				'п'=>'p',
				'р'=>'r',
				'с'=>'s',
				'т'=>'t',
				'у'=>'u',
				'ф'=>'f',
				'х'=>'h',
				'ц'=>'c',
				'ч'=>'ch',
				'ш'=>'sh',
				'щ'=>'sh',
				'ъ'=>'',
				'ы'=>'y',
				'ь'=>'',
				'э'=>'e',
				'ю'=>'yu',
				'я'=>'ya'
			)
		);
	}
	
	# ЧПУ КОДИРОВАНИЕ
	public static function hurl_encode($id, $name='')
	{
		$name = mb_substr(strtr(
			mb_strtolower(str_replace('%', 'percents', strip_tags(preg_replace('/[^0-9a-zа-я-]+/iu', '_', $name)))),
			array(
				'а'=>'a',
				'б'=>'b',
				'в'=>'v',
				'г'=>'g',
				'д'=>'d',
				'е'=>'e',
				'ё'=>'yo',
				'ж'=>'zh',
				'з'=>'z',
				'и'=>'i',
				'й'=>'i',
				'к'=>'k',
				'л'=>'l',
				'м'=>'m',
				'н'=>'n',
				'о'=>'o',
				'п'=>'p',
				'р'=>'r',
				'с'=>'s',
				'т'=>'t',
				'у'=>'u',
				'ф'=>'f',
				'х'=>'h',
				'ц'=>'c',
				'ч'=>'ch',
				'ш'=>'sh',
				'щ'=>'sh',
				'ъ'=>'',
				'ы'=>'y',
				'ь'=>'',
				'э'=>'e',
				'ю'=>'yu',
				'я'=>'ya'
			)), 0, 100);

		return $id.'-'.$name;
	}

	# ЧПУ ДЕКОДИРОВАНИЕ
	public static function hurl_decode($string)
	{
		if (preg_match('/^([0-9]{1,20})-.*$/', $string, $match) && (sizeof($match) == 2))
		{
			return intval($match[1]);
		}
		else if(is_numeric($string)) return intval($string);

		return false;
	}

	# ФОРМИРОВАНИЕ JSON
	public static function json($a=false)
	{
		if(is_null($a))		return 'null';
		if($a === false)	return 'false';
		if($a === true)		return 'true';
		if(is_scalar($a))
		{
			if(is_float($a)) return floatval(str_replace(",", ".", strval($a)));
			else if(is_numeric($a)) return $a;
			else
			{
				$jsonReplaces = array("\\"=>'\\\\', "/"=>'\\/', "\n"=>'\\n', "\t"=>'\\t', "\r"=>'\\r', "\b"=>'\\b', "\f"=>'\\f', '"'=>'\"');
				return '"'.strtr($a, $jsonReplaces).'"';
			}
		}
		else if(!is_array($a)) return false;
		$isList		= true;
		$checkIndex	= 0;
		foreach($a as $k=>$v)
		{
			if(!is_numeric($k) || $k!=$checkIndex++)
			{
				$isList = false;
				break;
			}
		}
		$result = array();
		if($isList)
		{
			foreach ($a as $v) $result[] = self::json($v);
			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($a as $k=>$v) $result[] = self::json($k).':'.self::json($v);
			return '{' . join(',', $result) . '}';
		}
	}
}