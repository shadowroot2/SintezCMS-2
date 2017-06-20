<?php
# РЕСАЙЗЕР ИЗОБРАЖЕНИЙ v.2.0
# Coded by ShadoW (c) 2013
class cnt_Image extends Controller
{
	private $_types = array(
		'square'
	);

	private $_width  	= false;
	private $_height 	= false;
	private $_type	 	= false;
	private $_watermark = false;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Наличие параметров
		if (count($this->_params) < 2) $this->act_index();

		# Ширина
		if (is_numeric($w_pos = array_search('w', $this->_params)) && isset($this->_params[$w_pos+1]) && filter_var($this->_params[$w_pos+1], FILTER_VALIDATE_INT))
		{
			$this->_width = $this->_params[$w_pos+1];
		}

		# Высота
		if (is_numeric($h_pos = array_search('h', $this->_params)) && isset($this->_params[$h_pos+1]) && filter_var($this->_params[$h_pos+1], FILTER_VALIDATE_INT))
		{
			$this->_height = $this->_params[$h_pos+1];
		}

		# Тип
		foreach($this->_types as $type)
		{
			if (array_search($type, $this->_params))
			{
				$this->_type = $type;
				break;
			}
		}

		# Водяной знак
		if (array_search('secure', $this->_params)) $this->_watermark = true;
	}

	# РЕДИРЕКТ
	public function act_index()
	{
		Helpers::redirect('/404');
	}

	# ОТОБРАЖЕНИЕ
	public function act_s()
	{
		$src_img = end($this->_params);
		exit(Image::getImage(_ABS_UPLOADS_.$src_img, $this->_width, $this->_height, TRUE, false, $this->_type, $this->_watermark));
	}
}