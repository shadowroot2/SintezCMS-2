<?php
# БАЗОВЫЙ КОНТРОЛЛЕР v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

abstract class Controller
{
	private $_params = array();
	
	public function __construct(){}
	
	abstract function init();
	abstract function act_index();
}