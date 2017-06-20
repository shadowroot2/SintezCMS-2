<?php
# ФОРМА ОБРАТНОЙ СВЯЗИ v.2.0
# Coded by ShadoW (c) 2013
class cnt_Feedback extends Controller
{
	private $_emails = array();

	# AJAX
	public function init()
	{
		if (!Core::isAjax()) Helpers::redirect('/');

		# E-mail'ы администратора
		if (($emails = Model::getObject(5, true, false)->field('Значение')) && is_array($emails = explode(_NL_, $emails)))
		{
			foreach($emails as $email)
			{
				$email = trim($email);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) $this->_emails[] = $email;
			}
		}
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		Helpers::redirect('/');
	}

	# ОТПРАВКА
	public function act_send()
	{
		$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');

		# Проверка данных
		if (
			isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
			isset($_POST['email']) && ($email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) &&
			isset($_POST['phone']) && ($phone = filter_input(INPUT_POST, 'phone', FILTER_VALIDATE_INT)) &&
			isset($_POST['text']) && ($text = nl2br(Helpers::escape($_POST['text']))) && ($_POST['text'] != '')
		)
		{
			# Собираем письмо
			if (count($this->_emails))
			{
				Mailer::reset();
				Mailer::$_to 	  = $this->_emails;
				Mailer::$_subject = 'Сообщение с сайта';
				Mailer::$_content = 'Здравствуйте,<br />'
				.Helpers::date('d mounth Y в H:i', time()).' пользователь сайта c IP:'.$_SERVER['REMOTE_ADDR'].' отправил сообщение с формы обратной связи.<br />'
				.'<br />'
				.'<b>Имя:</b> '.$name.'<br />'
				.'<b>E-mail:</b> <a href="mailto:'.$email.'">'.$email.'</a><br />'
				.'<b>Телефон:</b> '.$phone.'<br />'
				.'<b>Текст сообщения:</b><br />'.$text.'<br />'
				.'<br />'
				.'С уважением, система сайта <a href="http://'._SITE_URL_.'" target="_blank">'._SITE_URL_.'</a>';

				# Отправка
				if (Mailer::send()) $answer = array('status'=>'ok');
				else $answer['msg'] = 'Невозможно отправить сообщение, попробуйте позже';
			}
			else $answer['msg'] = 'Некому отправить письмо';
		}
		else $answer['msg'] = 'Проверьте правильность данных';

		exit(Helpers::JSON($answer));
	}
}