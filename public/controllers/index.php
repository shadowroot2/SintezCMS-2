<?php
# КОНТРОЛЛЕР ГЛАВНОЙ СТРАНЦЫ v.2.0
# Coded by ShadoW (c) 2014
class cnt_Index extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		View::assign('%is_main_page', 1);
	}

	# ГЛАВНАЯ
	public function act_index()
	{
		# ВЫВОД
		View::assign('%page_title', 'Главная');
		View::render('index');
	}
}