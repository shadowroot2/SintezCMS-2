<?php
# Проверка версии PHP
if (version_compare(phpversion(), '5.4.0', '<')) die('Требуется PHP версии 5.4 и выше!');

# Временная зона
date_default_timezone_set('Asia/Almaty');

# Настройка MB
mb_internal_encoding('utf-8');
mb_substitute_character('none');
