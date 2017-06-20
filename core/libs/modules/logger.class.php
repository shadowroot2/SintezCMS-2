<?php
# CMS-ЛОГГЕР v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class Logger extends Singleton
{
	# ДОБАВИТЬ ЗАПИСЬ
	public static function add($action=1, $class_name, $object_name, $object_id=false,  $class_id=false)
	{
		if (defined('_CMS_') && Core::$_data['authed'])
		{
			$log = array(
				'date'		  => time(),
				'user_id'	  => (int)$_SESSION['cms_auth']['id'],
				'user_name'	  => $_SESSION['cms_auth']['name'],
				'action'	  => (int)$action,
				'object_name' => $object_name,
				'class_name'  => $class_name
			);
	
			if (filter_var($object_id, FILTER_VALIDATE_INT)) $log['object_id'] = (int)$object_id;
			if (filter_var($class_id, FILTER_VALIDATE_INT))	 $log['class_id']  = (int)$class_id;
	
			return Model::$_db->insert('md_logs', $log);
		}
	}

	# ПОЛУЧИТЬ ЗАПИСИ
	public static function get($count=50, $date1=false, $date2=false, $action=false, $user_id=false)
	{
		$query = array();
		if ($date1) 									$query[] = "`date`>='".strtotime($date1)."'";
		if ($date2) 									$query[] = "`date`<='".(strtotime($date2)+86399)."'";
		if (filter_var($user_id, FILTER_VALIDATE_INT)) 	$query[] = "`user_id`='$user_id'";
		if (filter_var($action, FILTER_VALIDATE_INT)) 	$query[] = "`action`='$action'";

		if ($result = Model::$_db->select('md_logs', (count($query) ? "WHERE ".join(' AND ', $query) : '')." ORDER BY `date` DESC LIMIT ".intval($count)))
		{
			return $result;
		}

		return false;
	}

	# ОЧИСТКА ТАБЛИЦЫ
	public static function clear()
	{
		if (Model::$_db->query("TRUNCATE TABLE `md_logs`")) return true;
		return false;
	}
}