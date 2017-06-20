<?
# CRON-МОДУЛЬ УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЕЙ НЕ ПОДТВЕРДИВШИХ РЕГИСТРАЦИЮ v.1.0
# Coded by ShadoW (C) 2011
class cron_Users_Cleaner
{
	private $_container = 7;
	private $_class		= 13;

	# ЗАПУСК
	public function run()
	{
		# Получаем не активированных пользователей
		if ($not_activated_users = Model::getObjects($this->_container, $this->_class, array('Код активации'), "o.active='0' AND o.c_date<='".(time()-2592000)."' AND c.f_63!=''"))
		{
			# Удаляем
			foreach($not_activated_users as $u) Model::deleteObject($u['id']);
		}

		return TRUE;
	}
}