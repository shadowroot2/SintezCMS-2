<?php
# MAILER v.3.2 (with SMTP)
# Coded by ShadoW (c) 2015
defined('_CORE_') or die('Доступ запрещен');

class Mailer extends Singleton
{
	public static $_from;
	public static $_to;
	public static $_subject;
	public static $_content;

	private static $_headers;
	private static $_parts;

	private static $_from_smtp;
	private static $_smtp_conn;
	private static $_smtp_timeout = 30;

	# СБРОС
	public static function reset()
	{
		self::$_to			= '';
		self::$_from		= 'CLOCKERS <noreply@clockers.kz>';
		self::$_from_smtp	= 'noreply@clockers.kz';
		self::$_subject		= '';
		self::$_content		= '';
		self::$_headers 	= array();
		self::$_parts 		= array();

		if (is_resource(self::$_smtp_conn)) self::smtp_close();
	}

	# ОБРАБОТКА ЧАТИ
	private static function build_part($part)
	{
		return 'Content-Type: '.$part['type'].($part['name'] ? '; name="'.$part['name'].'"' : '')._RNL_.'Content-Transfer-Encoding: base64'._RNL_._RNL_.chunk_split(base64_encode($part['content']))._RNL_;
	}

	# СОЗДАНИЕ МУЛЬТИЧАСТИ
	private static function build_multipart($boundary)
	{
		$multipart = _RNL_.'--'.$boundary;;

		# Вложения
		foreach(self::$_parts as $p)
			$multipart .= _RNL_.self::build_part($p).'--'.$boundary;

		# Текст
		if (self::$_content != '')
		{
			$multipart .= _RNL_
				.self::build_part(array(
					'content'	=> self::$_content,
					'name'		=> '',
					'type'		=> 'text/html; charset=utf-8'
				))
				.'--'.$boundary;
		}

		return $multipart .=  '--'._RNL_;
	}

	# КОДИРОВАНИЕ MIME
	private static function mime_encode($str, $data_charset='utf-8', $send_charset='utf-8')
	{
		if ($data_charset != $send_charset)
				$str = iconv($data_charset, $send_charset, $str);
		
		return '=?'.$send_charset.'?B?'.base64_encode($str).'?=';
	}

	# ДОБАВЛЕНИЕ АТТАЧА
	public static function attach($content, $file_name='', $type='image/jpeg')
	{
		self::$_parts[] = array (
			'content' 	=> $content,
			'name' 		=> self::mime_encode($file_name),
			'type' 		=> $type
		);
	}

	# ПРОВЕРКА ПОДКЛЮЧЕНИЯ К SMTP
	private static function smtp_connected()
	{
        if (is_resource(self::$_smtp_conn))
		{
            $sock_status = stream_get_meta_data(self::$_smtp_conn);

			if ($sock_status['eof']) self::smtp_close();
			else return true;
        }

        return false;
	}

	# ЗАКРЫТИЕ SMTP СОЕДИНЕНИЯ
	private static function smtp_close()
	{
        if (is_resource(self::$_smtp_conn))
		{
            fclose(self::$_smtp_conn);
            self::$_smtp_conn = 0;
        }
	}

	# ЧТЕНИЕ ОТВЕТА SMTP
	private static function smtp_read($timeout=300)
	{
        if (!is_resource(self::$_smtp_conn)) return '';

        stream_set_timeout(self::$_smtp_conn, $timeout);

		$data = '';
        while(is_resource(self::$_smtp_conn) && !feof(self::$_smtp_conn))
		{
            $data .= $str = @fgets(self::$_smtp_conn, 515);

			if ((isset($str[3]) and $str[3] == ' ')) break;

            $info = stream_get_meta_data(self::$_smtp_conn);
            if ($info['timed_out']) break;
        }

        return $data;
	}

	# ОТПРАВКА КОМАНДЫ SMTP
	private static function smtp_write($string, $need_code)
    {
	    fwrite(self::$_smtp_conn, $string._RNL_);
        $reply	= self::smtp_read();
        $code	= substr($reply, 0, 3);

		# DEBUG
		#echo htmlspecialchars($string).' : '.$reply.'<br />';

        if (!in_array($code, (array)$need_code)) return false;

        return true;
    }

	# ОТПРАВКА ПИСЬМА
	public static function send()
	{
		$boundary = 'b'.md5(uniqid(time()));
		$mime = 'From: '.self::$_from._RNL_
			   .'Reply-To: '.self::$_from._RNL_
			   .'Return-Path: '.self::$_from._RNL_
			   .(is_array(self::$_headers) ? join(_RNL_, self::$_headers) : '')
			   .'MIME-Version: 1.0'._RNL_
			   .'Content-Type: multipart/mixed; boundary = '.$boundary._RNL_;


		# Адресаты
		$to_list = array();
		if (is_string(self::$_to) && filter_var(self::$_to, FILTER_VALIDATE_EMAIL)) $to_list[] = self::$_to;
		else if (is_array(self::$_to) && count(self::$_to))
		{
			foreach(self::$_to as $to)
			{
				if (filter_var($to, FILTER_VALIDATE_EMAIL) && !in_array($to, $to_list)) $to_list[] = $to;
			}
		}

		# Тема письма в MIME
		$subject = self::mime_encode(self::$_subject);

		# ОТПРАВКА ФУНКЦИЕЙ MAIL
		if (!defined('MAIL_SENDER') || (MAIL_SENDER == 'MAIL'))
		{
			# Отправка адресатам
			if (count($to_list))
			{
				$sent = 0;
				foreach($to_list as $to)
				{
					if (mail($to, $subject, self::build_multipart($boundary), $mime))
						$sent++;
				}

				# Отправлены
				if ($sent == count($to_list)) return true;
			}
		}

		# ОТПРАВКА ПО SMTP
		else if (defined('MAIL_SENDER') && (MAIL_SENDER == 'SMTP'))
		{
			# Подключение
			if (!self::smtp_connected()) self::$_smtp_conn = @stream_socket_client(SMTP_HOST.':'.SMTP_PORT, $errno, $errstr, self::$_smtp_timeout, STREAM_CLIENT_CONNECT, stream_context_create(array()));

			# Проверяем соединение
			if (!self::smtp_connected()) exit('Невозможно подключиться к SMTP-серверу: '.SMTP_HOST.':'.SMTP_PORT);

			# Проверка max_execution_time
			if (substr(PHP_OS,0,3) != 'WIN')
			{
				$max_ex_time = ini_get('max_execution_time');
				if ($max_ex_time != 0 && self::$_smtp_timeout > $max_ex_time) @set_time_limit(self::$_smtp_timeout);
				stream_set_timeout(self::$_smtp_conn, self::$_smtp_timeout, 0);
			}

			# Читаем первый ответ сервера
			self::smtp_read();

			# Приветствие
			if (!self::smtp_write('HELO '.SMTP_HOST, 250) && !self::smtp_write('EHLO '.SMTP_HOST, 250)) exit('SMTP-сервер не отвечает на HELO');

			# Авторизация
			if (SMTP_AUTH)
			{
				switch(SMTP_AUTH_TYPE)
				{
					case 'PLAIN':
						if (!self::smtp_write('AUTH PLAIN', 334)) exit('SMTP-сервер не поддерживает PLAIN авторизацию');
						if (!self::smtp_write(base64_encode("\0".SMTP_USER."\0".SMTP_PASS), 235)) exit('SMTP-сервер не принял PLAIN логин и пароль');
					break;

					case 'LOGIN':
						if (!self::smtp_write('AUTH LOGIN', 334)) exit('SMTP-сервер не поддерживает LOGIN авторизацию');
						if (!self::smtp_write(base64_encode(SMTP_USER), 334)) exit('SMTP-сервер не принял LOGIN логин');
						if (!self::smtp_write(base64_encode(SMTP_PASS), 235)) exit('SMTP-сервер не принял LOGIN пароль');
					break;
				}
			}

			# Отправка адресатам
			if (count($to_list))
			{
				$sent = 0;
				foreach($to_list as $to)
				{
					if (!self::smtp_write('MAIL FROM: <'.self::$_from_smtp.'>', 250)) exit('SMTP-сервер не принял отправителя '.self::$_from_smtp);
					if (!self::smtp_write('RCPT TO: <'.$to.'>', 250)) exit('SMTP-сервер не принял получателя '.$to);
					if (!self::smtp_write('DATA', 354)) exit('SMTP-сервер не принял команду DATA');

					# SMTP MIME
					$smtp_mime = 'Date: '.date('D, d M Y H:i:s').' UT'._RNL_
								.'To: '.$to._RNL_
								.'Subject: '.$subject._RNL_
								.$mime
								.self::build_multipart($boundary);

					# Отправляем письмо
					if (!self::smtp_write($smtp_mime._RNL_.'.', 250)) exit('SMTP-сервер не принял текст письма');
				}

				# Отправлены
				if ($sent == count($to_list)) return true;
			}

			# Выход
			self::smtp_write('QUIT', 221);
			self::smtp_close();

			return true;
		}

		return false;
	}
}