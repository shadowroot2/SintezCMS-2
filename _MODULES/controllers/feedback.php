<?php
# ФОРМА ОБРАТНОЙ СВЯЗИ v.1.0
# Coded by ShadoW (c) 2012

class cnt_Feedback extends Controller
{
	private $_emails = array();

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		$this->_emails[] = Model::getObject(5, TRUE)->field('Значение');
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax()) exit(header('Location: /'));
		return TRUE;
	}

	# ОТПРАВКА
	public function act_send()
	{
		$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');

		# Проверка данных
		if (
			$this->act_index()
			&& !empty($_POST['name'])
			&& !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
			&& !empty($_POST['text'])
		)
		{
			# Собираем письмо
			Mailer::reset();
			Mailer::$_to 	  = $this->_emails;
			Mailer::$_subject = 'Письмо с сайта '._SITE_URL_;
			Mailer::$_content = '<html>'
			.'<body>'
			.'Здравствуйте,<br />'
			.date('d.m.Y в H:i').' пользователь сайта [IP:'.$_SERVER['REMOTE_ADDR'].'] отправил письмо с формы обратной связи:<br />'
			.'<br />'
			.'<b>Имя:</b> '.Helpers::escape($_POST['name']).'<br />'
			.'<b>E-mail:</b> <a href="mailto:'.$_POST['email'].'">'.$_POST['email'].'</a><br />'
			.'<b>Текст сообщения:</b><br />'.nl2br(Helpers::escape($_POST['text'])).'<br />'
			.'<br />'
			.'С уважением, система сайта <a href="http://'._SITE_URL_.'" target="_blank">'._SITE_URL_.'</a>'
			.'</body>'
			.'</html>';

			# Отправка
			if (Mailer::send()) $answer = array('status'=>'ok');
			else $answer['msg'] = 'Невозможно отправить письмо, попробуйте позже';
		}
		else $answer['msg'] = 'Проверьте правильность данных';

		exit(Helpers::JSON($answer));
	}
}