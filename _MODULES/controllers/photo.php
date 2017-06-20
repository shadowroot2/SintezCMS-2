<?php
# ФОТОГРАФИЯ v.1.0
# Coded by ShadoW (c) 2012
class cnt_Photo extends Controller
{
	# ИНИЦИАЛИЯ
	public function init(){}

	# РЕДИРЕКТ
	public function act_index()
	{
		exit(header('Location: /'));
	}

	# ПОКАЗАТЬ ФОТОГРАФИЮ
	public function act_s()
	{
		if (!empty($this->_params[0]) && ($photo_id = filter_var(str_replace('.jpg', '', $this->_params[0]), FILTER_VALIDATE_INT)) && ($photo = new _Photo($photo_id)))
		{
			# Добавляем просмотр
			$photo->addView();

			# Перенаправляем на фото
			header('Location: '._UPLOADS_.$photo['file']);
		}
		else $this->act_index();
	}
}