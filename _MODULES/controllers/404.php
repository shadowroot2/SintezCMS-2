<?php
# КОНТРОЛЛЕР ОШИБКА 404 - СТРАНИЦА НЕ НАЙДЕНА v.1.0
# Coded by ShadoW (c) 2012
class cnt_404 extends Controller
{
	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		header('HTTP/1.0 404 Not Found');
	}

	# 404
	public function act_index()
	{
		View::assign('%page_title', '404 - Страница не найдена');
		View::render('404');
	}
}