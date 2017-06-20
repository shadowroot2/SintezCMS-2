<?php
# ОШИБКА 404 - СТРАНИЦА НЕ НАЙДЕНА v.1.0
# Coded by ShadoW (c) 2013
class cnt_404 extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		header('HTTP/1.0 404 Not Found');
	}

	# ВЫВОД
	public function act_index()
	{
		View::assign('%page_title', 'Ошибка 404 - страница не найдена');
		View::render('404');
	}
}