<?php
# КОНТАКТЫ v.1.0
# Coded by ShadoW (c) 2014
class cnt_Contacts extends Controller
{
	private $_menu_id	= 14;

	# ИНИЦИАЛИЗАЦИЯ
	public function init(){}

	# СТРАНИЦА
	public function act_index()
	{
		if (($contacts_obj = Model::getObject(Core::$_data['defs']['contacts_id'], true)) && ($contacts_obj['active'] == 1))
		{
			# Выбираем в меню
			if (isset(Core::$_data['main_menu'][$this->_menu_id]))
			{
				Core::$_data['main_menu'][$this->_menu_id]['set'] = true;
				View::reassign('%site.main_menu', Core::$_data['main_menu']);
			}

			# Контакты
			$contacts = array(
				'id'	=> $contacts_obj['id'],
				'name'	=> $contacts_obj['Заголовок'],
				'text'	=> $contacts_obj['Текст'],
				'map'	=> false
			);

			# Карта проезда
			Helpers::lang_sw('ru');
			if (($contacts_obj['inside'] > 0) && ($map_obj = Model::getObjects($contacts['id'], 11, true, "o.active='1' AND c.f_164!='' ORDER BY o.id DESC LIMIT 1")))
			{
				# JS
				View::addJS(array(
					'https://maps.google.com/maps/api/js?sensor=false',
					_TPL_.'google_maps.js'
				));
				$coords_zoom = explode(':', $map_obj['Координаты']);
				$contacts['map'] = array(
					'id'		=> $map_obj['id'],
					'coords'	=> $coords_zoom[0],
					'zoom'		=> isset($coords_zoom[1]) ? intval($coords_zoom[1]) : 12
				);
			}
			Helpers::lang_sw();

			unset($contacts_obj, $map_obj, $coords_zoom);

			# ВЫВОД
			View::assign('%page_title', $contacts['name']);
			View::render('contacts/page', true, array('contacts'=>$contacts));
		}
		else Helpers::redirect('/');
	}

	# ОТПРАВКА
	public function act_send()
	{
		# AJAX?
		if (!Core::isAjax()) Helpers::redirect('/');

		# E-mail'ы администратора
		$emails = array();
		if (($emails_list = Model::getObject(5, true, false)->field('Значение')) && is_array($emails_list = explode(_NL_, $emails_list)))
		{
			foreach($emails_list as $e)
			{
				$e = trim($e);
				if (filter_var($e, FILTER_VALIDATE_EMAIL) && !in_array($e, $emails)) $emails[] = $e;
			}
			unset($emails_list, $e);
		}

		$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');

		# Проверка данных
		if (
			isset($_POST['name']) && ($name = Helpers::escape($_POST['name'])) && ($name != '') &&
			isset($_POST['email']) && ($email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) &&
			isset($_POST['phone']) &&
			isset($_POST['text']) && ($text = nl2br(Helpers::escape($_POST['text']))) && ($text != '')
		)
		{
			# Собираем письмо
			if (count($emails))
			{
				Mailer::reset();
				Mailer::$_to 	  = $emails;
				Mailer::$_subject = 'Сообщение с сайта';
				Mailer::$_content = 'Здравствуйте, Котя и Ди!<br />'
				.Helpers::date('j mounth Y в H:i', time()).' пользователь сайта [IP: '.$_SERVER['REMOTE_ADDR'].'] отправил сообщение с формы обратной связи.<br />'
				.'<br />'
				.'<b>Язык сайта:</b> '.Core::$_data['langs'][Core::$_lang]['name'].'<br />'
				.'<b>Имя:</b> '.$name.'<br />'
				.'<b>E-mail:</b> <a href="mailto:'.$email.'">'.$email.'</a><br />'
				.($_POST['phone'] != '' ? '<b>Телефон:</b> '.Helpers::escape($_POST['phone']).'<br />' : '')
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