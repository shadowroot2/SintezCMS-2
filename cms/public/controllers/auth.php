<?php
# ����������� v.2.0
# Coded by ShadoW (c) 2013
class cnt_Auth extends Controller
{
	# �������������
	public function init()
	{
		if (Core::$_data['authed']) Helpers::redirect(_CMS_);
	}

	# ����� �����������
	public function act_index()
	{
		View::$_template = false;
		View::render('user/auth_template');
	}
}