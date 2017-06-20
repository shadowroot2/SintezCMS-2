<?php
# При разработке E_ALL | E_STRICT
# На продакшене error_reporting(0);
# При использовании PHP >= 5.3, рекомендуется отключить
# предупреждения об устаревших функциях с помощью: E_ALL & ~E_DEPRECATED
error_reporting(E_ALL | E_STRICT);

# ЯДРО
require_once dirname(__FILE__).'/core/core.php';

# НАСТРОЙКА ЯДРА
Core::run(array(
	'debug'			=> false,
	'profiling'		=> false,
	'caching'		=> false,
	'memcached'		=> false,
	'errors' 		=> true,
	'log_errors'	=> true,
	'hurl'			=> true,
	'frontend'		=> true
));
?>