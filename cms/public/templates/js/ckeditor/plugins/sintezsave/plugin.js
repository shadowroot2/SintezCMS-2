/**
 * Плагин сохранения контена v.1.0
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_intro
 */

// Регистрируем плагин
CKEDITOR.plugins.add('sintezsave',
{
	icons	: 'sintezsave',
	init	: function(editor)
	{
		// Добавляем команду
		editor.addCommand('SaveContent',
		{
			exec: function(editor)
			{
				var $editor_container	= $('#'+editor.element.getAttribute('id'));
				var obj_id				= $editor_container.data('cms-id');
				var edit_field			= $editor_container.data('cms-edit-fields');
				var	lang				= $editor_container.data('cms-lang');

				// Проверяем параметры
				if ((typeof obj_id == 'number') && (typeof edit_field == 'string') && (typeof lang == 'string'))
				{
					// Сохраняем поле
					$.post('/cms/ajax/edit_field',
					{
						id		: obj_id,
						lang	: lang,
						field	: edit_field,
						content	: editor.getData()
					},
					function(json)
					{
						if (json)
						{
							// Данные записаны
							if (json.status == 'ok')
							{
								// Удаляем редактор
								$editor_container.removeClass('editing').attr('contenteditable', false);
								editor.destroy();

								// Восстанавливаем панель
								if (typeof $editor_container.data('cms-panel') != 'undefined')
								{
									$editor_container.append($editor_container.data('cms-panel'));
								}
							}
							else alert(json.msg);
						}
						else alert('Невозможно связаться с сервером');

					}, 'json');
				}
				else alert('Не корректные параметры');
			}
		});

		// Добавляем кнопку
		editor.ui.addButton('SintezSave', {
			label: 'Сохранить',
			command: 'SaveContent',
			toolbar: 'insert'
		});
	}
});