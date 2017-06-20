<?php
# ПАГИНАТОР v.2.1
# Coded by ShadoW (C) 2014
defined('_CORE_') or die('Доступ запрещен');

class Paginator extends Singleton
{
	public static $_pages_count = 1;
	public static $_page		= 1;
	public static $_start		= 0;
	public static $_pages		= array();
	public static $_navi		= array('prev'=>false, 'next'=>false);

	# СОЗДАТЬ ПАГИНАТОР
	public static function setPaginator($elements_count, $page=1, $perpage=10, $navi=false, $range=false)
	{
		if (
			($elements_count = filter_var($elements_count, FILTER_VALIDATE_INT)) &&
			($page = filter_var($page, FILTER_VALIDATE_INT)) &&
			($perpage = filter_var($perpage, FILTER_VALIDATE_INT))
		)
		{
			# Количество страниц
			self::$_pages_count = ceil($elements_count / $perpage);
			if (self::$_pages_count < 2) return false;

			# Текущая страница
			if ($page <= self::$_pages_count) self::$_page = $page;

			# Начало запроса
			self::$_start = ((self::$_page * $perpage) - $perpage);
			if (self::$_start < 0) self::$_start = 0;

			# Навигация
			if ($navi)
			{
				self::$_navi['prev'] = self::$_page > 1						? self::$_page - 1 : 1;
				self::$_navi['next'] = self::$_page < self::$_pages_count	? self::$_page + 1 : self::$_pages_count;
			}

			# Диапозон
			if (is_int($range) && (self::$_pages_count > $range))
			{
				self::$_pages = array();

				# Первая страница
				if ((self::$_page >= 1) && (self::$_page <= $range-1))
				{
					for ($i=1; $i<=$range; $i++)
					{
						self::$_pages[$i] = array(
							'set' => $i == self::$_page ? true : false
						);
					}
					self::$_pages[] = '...';
					self::$_pages[self::$_pages_count] = array(
						'set' => false
					);
				}
				# Последняя страница
				else if ((self::$_page >= self::$_pages_count-$range+1) && (self::$_page <= self::$_pages_count))
				{
					self::$_pages[1] = array(
						'set' => false
					);
					self::$_pages[2] = '...';
					for ($i=(self::$_pages_count-$range); $i<=self::$_pages_count; $i++)
					{
						self::$_pages[$i] = array(
							'set' => $i == self::$_page ? true : false
						);
					}
				}
				else
				{
					self::$_pages[1] = array(
						'set' => false
					);
					self::$_pages[] = '...';
					for($i=self::$_page-ceil($range/2); $i<=self::$_page+ceil($range/2); $i++)
					{
						self::$_pages[$i] = array(
							'set' => $i == self::$_page ? true : false
						);
					}
					self::$_pages[] = '...';
					self::$_pages[self::$_pages_count] = array(
						'set' => false
					);
				}
			}
			else
			{
				# Страницы
				for($i=1; $i<=self::$_pages_count; $i++)
				{
					self::$_pages[$i] = array(
						'set' => $i == self::$_page ? true : false
					);
				}
			}

			return true;
		}

		return false;
	}
}