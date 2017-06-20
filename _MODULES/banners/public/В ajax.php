# ПЕРЕХОД ПО БАННЕРУ
public function act_banner_click()
{
	if (
		$this->act_index() &&
		isset($this->_params[0]) && ($banner_id = filter_var($this->_params[0], FILTER_VALIDATE_INT)) &&
		($banner = Model::getObject($banner_id, array('Переходов'))) && ($banner['active'] == 1) && ($banner['class_id'] == 56) &&
		Model::$_db->query("UPDATE `class_".$banner['class_id']."` SET `f_368`=f_368+1 WHERE `object_id`='$banner_id' LIMIT 1") &&
		Banner_Logger::addClick($banner_id)
	)
	{
		$answer = array('status'=>'ok');
	}
	else $answer['msg'] = 'Баннер не найден';
}