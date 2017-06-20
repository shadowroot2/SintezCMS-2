<?php
# ОБРАБОТЧИК ПОЛЕЙ ШАБЛОНА v.1.0
# Coded by ShadoW (c) 2013
defined('_CORE_') or die('Доступ запрещен');

class _TemplateFields extends Singleton
{
	public static function prepare(&$object, $template, $additional=false)
	{
		foreach($template['fields'] as $field)
		{
			$field_value = isset($object[$field['name']]) ? $object[$field['name']] : '';
			switch ($field['type'])
			{
				case 'date':
					$field_value = ($field_value != '' ? date('d.m.Y', strtotime($field_value)) : '');
				break;
				case 'password':
					$field_value = ($field_value != '' ? '********' : '');
				break;
				case 'template':
					if (!$additional && ($add_class_id = filter_var($field_value, FILTER_VALIDATE_INT)) && ($add_template = Model::getClass($add_class_id)) && ($add_template['additional'] == 1))
					{
						if ($field_template = self::prepare($object, $add_template->asArray(), true))
						{
							$template['fields'][$field['id']]['template'] = $field_template;
							unset($field_template);
						}
					}
				break;
			}
			$template['fields'][$field['id']]['value'] = $field_value;
		}

		return View::getTemplate('modules/objects/template_fields', false, array('template'=>$template));
	}
}