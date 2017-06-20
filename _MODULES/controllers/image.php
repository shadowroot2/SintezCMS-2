<?php
# ИЗОБРАЖЕНИЯ v.1.0
# Coded by ShadoW (c) 2012
class cnt_Image extends Controller
{
	private $_types = array(
		'square',
	);

	private $_width  	= FALSE;
	private $_height 	= FALSE;
	private $_type	 	= FALSE;
	private $_watermark = FALSE;

	# ИНИЦИАЛИЗАЦИЯ
	public function init()
	{
		# Ширина
		if (($w_pos = array_search('w', $this->_params)) && !empty($this->_params[$w_pos+1]) &&	is_numeric($this->_params[$w_pos+1]))
		{
			$this->_width = $this->_params[$w_pos+1];
		}

		# Высота
		if (($h_pos = array_search('h', $this->_params)) && !empty($this->_params[$h_pos+1]) &&	is_numeric($this->_params[$h_pos+1]))
		{
			$this->_height = $this->_params[$h_pos+1];
		}

		# Тип
		foreach($this->_types as $type)
		{
			if (array_search($type, $this->_params)) { $this->_type = $type; break;}
		}

		# Водяной знак
		if (array_search('water', $this->_params)) $this->_watermark = TRUE;
	}

	public function act_index(){ header('Location: /404'); }

	# ОТОБРАЖЕНИЕ
	public function act_s()
	{
		if (!empty($this->_params[0]))
		{
			$src_img = $this->_params[0];
			exit(Image::getImage(_ABS_UPLOADS_.$this->_params[0], $this->_width, $this->_height, TRUE, Core::$_caching, $this->_type, $this->_watermark));
		}
		else $this->act_index();
	}
}