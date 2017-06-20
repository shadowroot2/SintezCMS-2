<?php
define('_NL_', "\n");
define('_RNL_', "\r\n");
define('_SEP_', DIRECTORY_SEPARATOR);
define('_EXT_', '.php');

# Относительные пути
define('_ROOT_', '/');
define('_UPLOADS_', _ROOT_.'uploads/');

# Абсолютные пути
define('_ABS_ROOT_', dirname(dirname(dirname(realpath(__FILE__))))._SEP_);
define('_ABS_APP_', _ABS_ROOT_.'app'._SEP_);
define('_ABS_CONFIG_', _ABS_APP_.'config'._SEP_);
define('_ABS_CORE_', _ABS_ROOT_.'core'._SEP_);
define('_ABS_LIBS_', _ABS_CORE_.'lib'._SEP_);
define('_ABS_STORAGE_', _ABS_ROOT_.'storage'._SEP_);
define('_ABS_PUBLIC_', _ABS_ROOT_.'public'._SEP_);
define('_ABS_UPLOADS_', _ABS_PUBLIC_.'uploads'._SEP_);

# ПАРАМЕТРЫ ОТПРАВКИ ПОЧТЫ
define('MAIL_SENDER', 'MAIL');
define('SMTP_HOST', 'mail.host.kz');
define('SMTP_PORT', 25);
define('SMTP_AUTH', true);
define('SMTP_AUTH_TYPE', 'PLAIN');
define('SMTP_USER', 'user@host.kz');
define('SMTP_PASS', 'passwd');
