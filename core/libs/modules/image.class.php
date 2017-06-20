<?php
# ИЗОБРАЖЕНИЯ v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class Image extends Singleton
{
	private static $_exts = array(
		'jpg',
		'jpeg',
		'gif',
		'png',
		'tmp'
	);
	private static $_types = array(
		'square'
	);

	private static $_width;
	private static $_height;
	private static $_cache;
	private static $_type;
	private static $_watermark;
	private static $_watermark_src;
	private static $_press = 85;

	# ПОЛУЧИТЬ ИЗОБРАЖЕНИЕ
	public static function getImage($src_image, $width=false, $height=false, $header=false, $cache=false, $type=false, $watermark=false)
	{
		self::$_width  			= filter_var($width, FILTER_VALIDATE_INT)  ? (int)$width	: false;
		self::$_height 			= filter_var($height, FILTER_VALIDATE_INT) ? (int)$height	: false;
		self::$_cache			= (bool)$cache;
		self::$_type	 		= (!empty($type) && in_array($type, self::$_types)) ? $type	: false;
		self::$_watermark		= (bool)$watermark;
		self::$_watermark_src	= _ABS_UPLOADS_.'watermark.png';

		# Проверяем файл
		if (!empty($src_image) && file_exists($src_image))
		{
			# Информация о файле
			$path_info		 	= pathinfo($src_image);
			$file_name		 	= $path_info['filename'];
			$file_name_parts	= explode('-', $file_name);
			$file_id_field		= (count($file_name_parts) >= 3) ? $file_name_parts[0].'-'.$file_name_parts[1].'-' : $file_name;
			$file_name_parts	= explode('_', $file_name);
			$file_lang			= end($file_name_parts);
			$file_name			= $file_id_field.(strlen($file_lang) == 2 ? $file_lang : '');
			unset($file_name_parts, $file_id_field, $file_lang);

			# Проверка формата
			if (($img_params = getimagesize($src_image)) && is_array($img_params) && ($real_ext = str_replace('image/', '', $img_params['mime'])) && in_array($real_ext, self::$_exts))
			{
				# Расчеты
				$w = $src_width  = $img_params[0];
				$h = $src_height = $img_params[1];
				$x = $y = 0;

				if (!empty(self::$_width)  && ($w != self::$_width))	{ $w = self::$_width;  $h /= $src_width/$w;  }
				if (!empty(self::$_height) && ($h != self::$_height))	{ $h = self::$_height; $w = $src_width; $w /= $src_height/$h; }
				if (!empty(self::$_type))
				{
					switch(self::$_type)
					{
						case 'square' :
							if($src_width != $src_height)
							{
								if ($src_width > $src_height) $x=round(($src_width - $src_height) / 2);
								else $y=0;
							}
							$h = $w;
							$src_width = $src_height = min($src_width, $src_height);
						break;
					}
				}

				# Проверка наличия кэша
				$cache_file_path = _ABS_UPLOADS_.'resized/'.$file_name.'_'.ceil($w).'x'.ceil($h).'.'.$real_ext;
				if (Core::$_caching && self::$_cache && file_exists($cache_file_path))
				{
					if ($header)
					{
						header('Content-type: '.$img_params['mime']);
						header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
						header('Expires: '.gmdate('D, d M Y H:i:s', time()+2764800).' GMT');
						header('Cache-Control: public');
					}
					return file_get_contents($cache_file_path);
				}
				else
				{
					ob_start();

						# Создаем холст
						$des_img = imagecreatetruecolor($w, $h);

						# Получаем GD изображение
						switch($real_ext)
						{
							case 'gif' :
								$src_img = imagecreatefromgif($src_image);
								imagecopyresampled($des_img, $src_img, 0, 0, $x, $y, $w, $h, $src_width, $src_height);
								imagegif($des_img);
							break;

							case 'png' :
								# Прозрачность
								imageAlphaBlending($des_img, false);
								imageSaveAlpha($des_img, true);
								
								$src_img = imagecreatefrompng($src_image);
								imagecopyresampled($des_img, $src_img, 0, 0, $x, $y, $w, $h, $src_width, $src_height);
								imagepng($des_img);
							break;

							default :
								$src_img = imagecreatefromjpeg($src_image);
								imagecopyresampled($des_img, $src_img, 0, 0, $x, $y, $w, $h, $src_width, $src_height);
								imagejpeg($des_img, NULL, self::$_press);
						}

						# !!!!!!!!! Наложение водяного знака

						# Очещаем память GD
						imagedestroy($src_img);
						imagedestroy($des_img);

					$img_content = ob_get_clean();

					# Сохранить кэш?
					if (Core::$_caching && self::$_cache) file_put_contents($cache_file_path, $img_content);

					# Отдаем
					if ($header)
					{
						header('Content-type: '.$img_params['mime']);
						header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
						header('Expires: '.gmdate('D, d M Y H:i:s', time()+2764800).' GMT');
						header('Cache-Control: public');
					}
					return $img_content;
				}
			}
		}

		return false;
	}
}