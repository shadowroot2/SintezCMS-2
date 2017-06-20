<?
# CRON-МОДУЛЬ ЗАПЛАНИРОВАННАЯ РАССЫЛКА v.1.0
# Coded by ShadoW (C) 2012
class cron_Planed_Mailer
{
	private $_count = 0;
	private $_files = array();

	# ЗАПУСК
	public function run()
	{
		# ПОЛУЧАЕМ ПИСЬМА ДЛЯ ОТПРАВКИ
		if ($mails_list = Model::$_db->select('md_planed_mailer', "ORDER BY `id` ASC"))
		{
			foreach($mails_list as $key=>$mail)
			{
				if (filter_var($mail['to'], FILTER_VALIDATE_EMAIL))
				{
					#if ($key > 5) break;

					# Создаем письмо
					Mailer::reset();
					Mailer::$_to		= $mail['to']; //'pizzakov@mail.ru';
					Mailer::$_subject	= $mail['subject'];
					Mailer::$_content	= $mail['content'];

					# Прикрепление файлов
					if (!empty($mail['files']) && ($files = unserialize($mail['files'])))
					{
						foreach($files as $f)
						{
							if (!empty($f['name']) && !empty($f['file']) && is_file($file_path = _ABS_CACHE_.'mailer'._SEP_.$f['file']))
							{
								Mailer::attach(file_get_contents($file_path), $f['name'], 'application/'.$f['ext']);
								$this->_files[] = $f['file'];
							}
						}
					}

					# ОТПРАВКА
					if (Mailer::send()) $this->_count++;
					else return FALSE;
				}
			}

			# Очищаем таблицу
			Model::$_db->query("TRUNCATE TABLE `md_planed_mailer`");

			# Удаляем файлы
			if (!empty($this->_files))
			{
				foreach($this->_files as $f) @unlink(_ABS_CACHE_.'mailer'._SEP_.$f);
			}
		}

		return TRUE;
	}
}