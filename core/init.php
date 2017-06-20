<?php
# ИНИЦИАЛИЗАЦИЯ v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

# ПРОВЕРКА ВЕРСИИ PHP
if (version_compare(phpversion(), '5.3.0', '<')) die('Требуется PHP версии 5.3 и выше!');

# ВРЕМЕННАЯ ЗОНА
date_default_timezone_set('Asia/Almaty');

# UTF-8 по умолчанию
mb_internal_encoding('UTF-8');

# ФАБРИКА КЛАССОВ ПЕРЕНАПРАВЛЕНИЕ __auto_load()
spl_autoload_register(array('Core', 'auto_load'));