<?
# CRON-МОДУЛЬ СОЗДАНИЕ КАРТЫ САЙТА v.1.0
# Coded by ShadoW (C) 2012
class cron_Sitemap_Builder
{
	private $_site_url = 'http://arbuz.kz/';
	private $_prefix   = 'ru/almaty/';

	private $_containers = array(
		10431 => array('classes' => array(
			1 => array('name' => 'Заголовок', 'cnt' => 'page/show')
		)),
		10444 => array('classes' => array(
			1 => array('name' => 'Заголовок', 'cnt' => 'page/show')
		)),
		10433 => array('classes' => array(
			10 => array('name' => 'Название', 'cnt' => 'catalog/cat'),
			12 => array('name' => 'Название', 'cnt' => 'catalog/item', 'chk_fields'=>array(34))
		)),
		9 => array('classes' => array(
			2 => array('name' => 'Заголовок', 'cnt' => 'news/show'),
		)),
		10 => array('classes' => array(
			2 => array('name' => 'Заголовок', 'cnt' => 'recipes/show')
		)),
		25364 => array('classes' => array(
			2 => array('name' => 'Заголовок', 'cnt' => 'reviews/show')
		))
	);

	# ЗАПУСК
	public function run()
	{
		ob_start();
			echo
			'<?xml version="1.0" encoding="UTF-8"?>'._NL_
			.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'._NL_;

			# Основная ссылка
			echo
			'	<url>'._NL_
			.'		<loc>'.$this->_site_url.'</loc>'._NL_
			.'		<lastmod>'.date('Y-m-d').'</lastmod>'._NL_
			.'		<changefreq>always</changefreq>'._NL_
			.'		<priority>1.0</priority>'._NL_
			.'	</url>'._NL_;

			# Контейнеры
			foreach ($this->_containers as $cont_id=>$cont_params) echo $this->scanObj($cont_id, $cont_params['classes']);

			echo '</urlset>';
		$buffer = ob_get_clean();

		file_put_contents(_ABS_ROOT_.'sitemap.xml', $buffer);
		unset($buffer);

		return TRUE;
	}

	# РЕКУРСИЯ ОБЪЕКТОВ
	private function scanObj($cont_id, $classes, $depth=0)
	{
		$depth++;
		$out = '';
		foreach($classes as $class_id=>$cl)
		{
			# Поля проверки
			if (!empty($cl['chk_fields']))
			{
				$chk_sql = '';
				foreach($cl['chk_fields'] as $chk_field) $chk_sql .= " AND c.f_".$chk_field."='0'";
			}

			# Список объектов
			if ($objects_list = Model::getObjects($cont_id, $class_id, array($cl['name'], 'ЧПУ'), "o.active='1'".(!empty($chk_sql) ? $chk_sql : '')." ORDER BY o.sort ASC"))
			{
				foreach($objects_list as $o)
				{
					switch($o['class_id'])
					{
						# Каталоги
						case 10:
							if ($depth == 3)
							{
								$out .=
								'	<url>'._NL_
								.'		<loc>'.$this->_site_url.$this->_prefix.$cl['cnt'].'/'.(!empty($o['ЧПУ']) ? $o['id'].'-'.$o['ЧПУ'] : Helpers::hurl_encode($o['id'], $o[$cl['name']])).'</loc>'._NL_
								.'		<lastmod>'.date('Y-m-d', $o['m_date']).'</lastmod>'._NL_
								.'		<changefreq>hourly</changefreq>'._NL_
								.'		<priority>0.9</priority>'._NL_
								.'	</url>'._NL_;
							}
						break;

						# Товары
						case 12:
							$out .=
								'	<url>'._NL_
								.'		<loc>'.$this->_site_url.$this->_prefix.$cl['cnt'].'/'.(!empty($o['ЧПУ']) ? $o['id'].'-'.$o['ЧПУ'] : Helpers::hurl_encode($o['id'], $o[$cl['name']])).'</loc>'._NL_
								.'		<lastmod>'.date('Y-m-d', $o['m_date']).'</lastmod>'._NL_
								.'		<changefreq>hourly</changefreq>'._NL_
								.'		<priority>1.0</priority>'._NL_
								.'	</url>'._NL_;
						break;

						# По умолчанию
						default:
							$out .=
							'	<url>'._NL_
							.'		<loc>'.$this->_site_url.$this->_prefix.$cl['cnt'].'/'.(!empty($o['ЧПУ']) ? $o['id'].'-'.$o['ЧПУ'] : Helpers::hurl_encode($o['id'], $o[$cl['name']])).'</loc>'._NL_
							.'		<lastmod>'.date('Y-m-d', $o['m_date']).'</lastmod>'._NL_
							.'		<priority>0.7</priority>'._NL_
							.'	</url>'._NL_;
						break;
					}

					# Вложенные объекты
					if ($o['inside'] > 0) $out .= $this->scanObj($o['id'], $classes, $depth);
				}
			}
		}

		return $out;
	}
}