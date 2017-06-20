<?php
# ИСПОЛЬЗУЕМЫЕ РЕСУРСЫ НА СТАРТЕ
define('_CORE_START_TIME_', microtime(true));
define('_CORE_START_MEMORY_', memory_get_usage(true));

# ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ
define('_SITE_', 'http://www.'.str_replace('www.', '', $_SERVER['HTTP_HOST']));
define('_NL_', "\n");
define('_RNL_', "\r\n");
define('_EXT_', '.php');
define('_SEP_', DIRECTORY_SEPARATOR);
define('_LANG_', 'ru');
define('_CONNECTOR_', 'mysql');

# ОТНОСИТЕЛЬНЫЕ ПУТИ
define('_ROOT_', '/');
define('_CORE_', _ROOT_.'core/');

# АБСОЛЮТНЫЕ ПУТИ
define('_ABS_ROOT_', dirname(dirname(__FILE__))._SEP_);
define('_ABS_CORE_', _ABS_ROOT_.'core'._SEP_);
define('_ABS_VIEWS_', _ABS_CORE_.'views'._SEP_);
define('_ABS_LIBS_', _ABS_CORE_.'libs'._SEP_);

# ПАРАМЕТРЫ ПОДКЛЮЧЕНИЯ К БД
define('DB_HOST', 'localhost');
define('DB_BASE', 'clockers');
define('DB_USER', 'root');
define('DB_PASS', '');

# ПАРАМЕТРЫ MEMCACHED
define('MEMCACHED_HOST', 'localhost');
define('MEMCACHED_PORT', 11211);

# ПАРАМЕТРЫ ОТПРАВКИ ПОЧТЫ
define('MAIL_SENDER', 'MAIL');
define('SMTP_HOST', 'mail.host.kz');
define('SMTP_PORT', 25);
define('SMTP_AUTH', true);
define('SMTP_AUTH_TYPE', 'PLAIN');
define('SMTP_USER', 'user@host.kz');
define('SMTP_PASS', 'passwd');

# ПРОВЕРКА БЕЗОПАСНОСТИ
define('_FILE_SECURITY_', '<?php defined(\'_CORE_\') or die(\'Доступ запрещен\'); ?>');