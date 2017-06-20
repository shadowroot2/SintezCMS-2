<?
# CRON-МОДУЛЬ ПОЗДРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЕЙ С ДНЕМ РОЖДЕНИЯ v.1.0
# Coded by ShadoW (C) 2012
class cron_Users_Birthday
{
	private $_container = 7;
	private $_class		= 13;

	private $_templates	= array(
		# Алмата
		21 		=> array(
			'main'	=> array('id' => 18588),
			'birth'	=> array('id' => 43713)
		),
		# Астана
		10432 	=> array(
			'main'	=> array('id' => 48319),
			'birth'	=> array('id' => 48320)
		),
		# Шымкент
		10757 	=> array(
			'main'	=> array('id' => 48322),
			'birth'	=> array('id' => 48323)
		)
	);

	# ЗАПУСК
	public function run()
	{
		# Получаем пользователей у которых сегодня день рождения и шаблоны писем
		if ($birthday_users = Model::getObjects($this->_container, $this->_class, array('Имя', 'Дата рождения', 'Город рассылки'), "o.active='0' AND c.f_57 LIKE '%-".date('m')."-".date('d')."' ORDER BY c.f_117 ASC"))
		{
			foreach ($birthday_users as $u)
			{
				# Проверка e-mail и города
				if (filter_var($u['name'], FILTER_VALIDATE_EMAIL) && !empty($this->_templates[$u['Город рассылки']]))
				{
					# Шаблоны города не заданы
					if (empty($this->_templates[$u['Город рассылки']]['main']['template']))
					{
						$this->_templates[$u['Город рассылки']]['main']['template'] 	= Model::getObject($this->_templates[$u['Город рассылки']]['main']['id'], TRUE);
						$this->_templates[$u['Город рассылки']]['birth']['template']	= Model::getObject($this->_templates[$u['Город рассылки']]['birth']['id'], TRUE);
					}

					# Шаблоны города есть
					if (!empty($this->_templates[$u['Город рассылки']]['main']['template']) && !empty($this->_templates[$u['Город рассылки']]['birth']['template']))
					{
						# Создаем письмо
						Mailer::reset();
						Mailer::$_subject 	= trim($this->_templates[$u['Город рассылки']]['birth']['template']['Заголовок']);
						Mailer::$_to		= $u['name']; # 'shadow_root@mail.ru';
						Mailer::$_content	= $this->userFields(str_replace('"/public/', '"http://www.'.str_replace('www.', '', $_SERVER['HTTP_HOST']).'/public/', str_replace('#CONTENT#', $this->_templates[$u['Город рассылки']]['birth']['template']->field('Текст'), $this->_templates[$u['Город рассылки']]['main']['template']->field('Текст'))), $u);
						Mailer::send();
					}
				}
			}
			unset($birthday_users);
		}

		return TRUE;
	}

	# ПОЛЬЗОВАТЕЛЬСКИЕ ПОЛЯ
	private function userFields($text, $user)
	{
		foreach($user as $k=>$v) $text = str_replace("#$k#", $v, $text);
		return $text;
	}
}