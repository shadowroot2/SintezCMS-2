<?php
# AJAX методы модуля РАССЫЛКА
# Coded by ShadoW (c) 2012
class cnt_Ajax extends Controller
{
	# ТИПЫ ДЕЙСТВИЙ
	private $_actions = array('removetemplate', 'attach');

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		if (Core::$_data['authed'] == false) exit;
	}

	# AJAX
	public function act_index()
	{
		if (!Core::isAjax()) exit(header('Location: '._CMS_));
		return TRUE;
	}

	# ДЕЙСТВИЯ
	public function act_a()
	{
		if (!empty($this->_params[0]) && in_array($this->_params[0], $this->_actions))
		{
			$answer = array('status'=>'error', 'msg'=>'Неизвестная ошибка');
			switch($this->_params[0])
			{
				# ЗАГРУЗКА ФАЙЛОВ
				case 'attach':

					# Читаем параметры сервера
					$SERVER_POST_MAX_SIZE = ini_get('post_max_size');
					$unit				  = strtoupper(substr($SERVER_POST_MAX_SIZE, -1));
					$multiplier 		  = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

					# Проверка размера посланных данных
					if (((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$SERVER_POST_MAX_SIZE) && $SERVER_POST_MAX_SIZE)
					{
						header("HTTP/1.1 500 Internal Server Error");
						exit('Максимальный размер POST '.$SERVER_POST_MAX_SIZE);
					}
					
					# Ошибки загрузки
					$upload_errors = array(
						1=>'Превышен размер файла upload_max_filesize',
						2=>'Превышен размер файла',
						3=>'Файл загружен не полностью',
						4=>'Файл не передан',
						6=>'Ошибка временной папки'
					);

					# Проверяем посланный файл
					if (empty($_FILES['attach']))				echo $upload_errors[4];
					else if ($_FILES['attach']['error'] != 0)	echo $upload_errors[$_FILES['attach']["error"]];
					else if (empty($_FILES['attach']['name']))	echo 'У файла нет имени';
					else if (empty($_FILES['attach']['size']))	echo 'Файл пустой';
					else
					{
						# Файл кэша
						$cache_file = md5(time());						
						@move_uploaded_file($_FILES['attach']['tmp_name'], _ABS_CACHE_.'mailer'._SEP_.$cache_file);
						$_SESSION['cms_mailer']['files'][] = array('name'=>$_FILES['attach']['name'], 'file'=>$cache_file);

						echo 'OK';
					}
					exit;
				break;

				# УДАЛЕНИЕ ШАБЛОНА РАССЫЛКИ
				case 'removetemplate':
					if ($this->act_index() && !empty($this->_params[1]) && is_numeric($template_id = $this->_params[1]) && ($template = Model::getObject($template_id)))
					{
						if (Model::deleteObject($template_id))
						{
							Logger::add(3, 'Шаблон рассылки', $template['name']);
							$answer = array('status'=>'ok');
						}
						else $answer['msg'] = 'Невозможно удалить шаблон рассылки';
					}
					else $answer['msg'] = 'Шаблон рассылки не найден';
					exit(Helpers::JSON($answer));
				break;

				default :
					header('Location: '._CMS_);
				break;
			}
		}
		else header('Location: '._CMS_);
	}
}