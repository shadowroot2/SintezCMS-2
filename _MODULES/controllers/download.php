<?php
# СКАЧИВАНИЕ ФАЙЛА v.1.0
# Coded by ShadoW (c) 2012
class cnt_Download extends Controller
{
	# ИНИЦИАЛИЯ
	public function init(){}

	# РЕДИРЕКТ
	public function act_index()
	{
		exit(header('Location: /'));
	}

	# ФАЙЛ
	public function act_f()
	{
		if (!empty($this->_params[0]) && ($file_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) && ($file = new _File($file_id)))
		{
			# Добавляем скачивание
			$file->addDownload();

			# Перенаправляем на файл
			exit(header('Location: '._UPLOADS_.$file['file']));
		}
		else $this->act_index();
	}
}