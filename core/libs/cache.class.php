<?php
# КЭШИРОВАНИЕ v.2.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

# MEMCACHED
if (Core::$_memcached == true)
{
  class Cache extends MemcachedCache {}
}
# ФАЙЛОВОЕ КЭШИРОВАНИЕ
else
{
  class Cache extends FileCache {}
}