<?php
# БАННЕРНЫЙ ЛОГГЕР v.1.0
# Coded by ShadoW (C) 2013
defined('_CORE_') or die('Доступ запрещен');

class Banner_Logger extends Singleton
{
	# ДОБАВИТЬ ПРОСМОТР
	public static function addShow($id)
	{
		$date = date('Y-m-d');

		# Проверяем наличие сегодня
		if ($record = Model::$_db->select('md_banners', "WHERE `date`='$date' AND `banner_id`='$id' LIMIT 1", '`shows`'))
		{
			return Model::$_db->update('md_banners', array('shows'=>((int)$record['shows']+1)), "WHERE `date`='$date' AND `banner_id`='$id' LIMIT 1");
		}
		else return Model::$_db->insert('md_banners', array('date'=>$date, 'banner_id'=>$id));

		return FALSE;
	}

	# ДОБАВИТЬ КЛИК
	public static function addClick($id)
	{
		$date = date('Y-m-d');

		# Проверяем наличие сегодня
		if ($record = Model::$_db->select('md_banners', "WHERE `date`='$date' AND `banner_id`='$id' LIMIT 1", '`clicks`'))
		{
			return Model::$_db->update('md_banners', array('clicks'=>((int)$record['clicks']+1)), "WHERE `date`='$date' AND `banner_id`='$id' LIMIT 1");
		}
		else return Model::$_db->insert('md_banners', array('date'=>$date, 'banner_id'=>$id, 'clicks'=>1));

		return FALSE;
	}

	# ПОЛУЧИТЬ ЗАПИСИ
	public static function get($banners, $date1, $date2)
	{
		if (
			!empty($banners) && is_array($banners) &&
			($result = Model::$_db->query("SELECT `banner_id`, SUM(shows) as 'shows', SUM(clicks) as 'clicks' FROM `md_banners` WHERE (`date` BETWEEN '$date1' AND '$date2') AND `banner_id` IN(".join(',', $banners).") GROUP BY `banner_id` ORDER BY `shows` DESC"))
		)
		{
			$records = array();
			while($record = mysql_fetch_assoc($result)) $records[] = $record;
			return $records;
		}
		return FALSE;

		return FALSE;
	}

	# ОЧИСТКА ТАБЛИЦЫ
	public static function clear()
	{
		return Model::$_db->query("TRUNCATE TABLE `md_banners`");
	}
}