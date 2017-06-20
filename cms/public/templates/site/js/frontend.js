var _LANG_			= 'ru';
const _CMS_ 		= '/cms/';
const _CMS_AJAX_	= _CMS_+'ajax/';
const _CMS_MODULES_	= _CMS_+'modules/';

// Получение параметра
function cmsGetParam(obj, param)
{
	var cms_data = $(obj).data('cms-'+param);
	if (typeof cms_data != 'undefined') return cms_data;
	return false;
}

// Генерация ID для редактора
function generateEditorId()
{
	var rand_id = 'cms_editor_'+new Date().getTime();
	if ($('#'+rand_id).length == 0) return rand_id;
	else return generateEditorId();
}

// Сохранение панели
function cmsSaveRemovePanel(obj)
{
	$(obj).data('cms-lang', _LANG_);
	$(obj).data('cms-panel', $(obj).find('> .cms_panel').clone(true));
	$(obj).find('> .cms_panel').remove();
}

// Добавление объекта
function cmsAddObject(obj)
{
	var $inst	 	= $(obj);
	var obj_id		= cmsGetParam($inst, 'id');
	var add_type	= cmsGetParam($inst, 'add-type');
	var add_class	= cmsGetParam($inst, 'add-class');
	var add_fields	= cmsGetParam($inst, 'add-fields');

	// Проверяем параметры
	if ((obj_id != false) && (add_type != false) && !isNaN(parseInt(obj_id)) && !isNaN(parseInt(add_type)))
	{
		// Тип добавления
		switch(add_type)
		{
			// Добавление в модальном окне
			case 1:
				if ((add_class != false) && (add_fields != false) && !isNaN(parseInt(add_class)) && (typeof add_fields == 'string'))
				{
					$.get(_CMS_AJAX_+'get_fields/'+_LANG_+'/add/'+obj_id+'/'+add_fields+'/'+add_class, function(html)
					{
						var editor_id = generateEditorId();

						// Создаем диалоговое окно
						$('body').append('<div id="'+editor_id+'" class="cms-modal">'+html+'</div>');
						$('#'+editor_id).dialog(
						{
							modal		: true,
							width		: 585,
							dialogClass : 'cmd-modal-top',
							title		: 'Добавление объекта',
							closeText	: 'Закрыть',
							buttons		:
							{
								'Добавить'	: function() { $(this).find('.cms_object_form').submit(); },
								'Отмена'	: function() { $(this).dialog('close'); }
							},
							close		: function()
							{
								$(this).dialog('destroy');
								$(this).remove();
							}
						});
					});
				}
				else alert('Не указан шаблон или поля');
			break;

			// Мультзагрузка
			case 2:
				// Задаем язык СMS
				$.getJSON(_CMS_MODULES_+'objects/ajax/a/setlang/'+_LANG_, function(json)
				{
					if (json)
					{
						if (json.status == 'ok') top.location = _CMS_MODULES_+'objects/mupload/'+obj_id;
						else alert(json.msg);
					}
					else alert('Невозможно подключиться к серверу');
				});
			break;

			default:
				alert('Неизвестный тип редактирования');
			break;
		}
	}
	else alert('Не все параметры указаны');

	return false;
}

// Редактирование объекта
function cmsEditObject(obj)
{
	var $inst	 	= $(obj);
	var obj_id		= cmsGetParam($inst, 'id');
	var edit_type	= cmsGetParam($inst, 'edit-type');
	var edit_fields	= cmsGetParam($inst, 'edit-fields');

	// Проверяем параметры
	if ((obj_id != false) && (edit_type != false) && (edit_fields != false) && !isNaN(parseInt(obj_id)) && !isNaN(parseInt(edit_type)) && (typeof edit_fields == 'string'))
	{
		// Ключ редактирования
		$inst.addClass('editing');

		// Тип редактирования
		switch(edit_type)
		{
			// Inline HTML редактор текста
			case 1:
				// Делаем копию панели и убираем
				cmsSaveRemovePanel($inst);

				// Генерируем ID-контейнера если нету
				if (typeof $inst.attr('id') == 'undefined')	$inst.attr('id', generateEditorId());

				// Подключаем редактор
				$inst.attr('contenteditable', 'true');
				CKEDITOR.inline($inst.attr('id'),
				{
					toolbar			: 'FRONTEND',
					extraPlugins	: 'sintezsave'
				});
			break;

			// Inline HTML редактор строк
			case 2:
				// Делаем копию панели и убираем
				cmsSaveRemovePanel($inst);

				// Генерируем ID-контейнера если нету
				if (typeof $inst.attr('id') == 'undefined')	$inst.attr('id', generateEditorId());

				// Подключаем редактор
				$inst.attr('contenteditable', 'true');
				CKEDITOR.inline($inst.attr('id'),
				{
					toolbar					: 'SAVEONLY',
					extraPlugins			: 'sintezsave',
					autoParagraph 			: false,
					forcePasteAsPlainText	: true,
					keystrokes				: [[13, 'SaveContent']]
				});
			break;

			// Редактирование полей в модальном окне
			case 3:
				$.get(_CMS_AJAX_+'get_fields/'+_LANG_+'/edit/'+obj_id+'/'+edit_fields, function(html)
				{
					var editor_id = generateEditorId();

					// Создаем диалоговое окно
					$('body').append('<div id="'+editor_id+'" class="cms-modal">'+html+'</div>');
					$('#'+editor_id).dialog(
					{
						modal		: true,
						width		: 585,
						dialogClass : 'cmd-modal-top',
						title		: 'Редактирование полей объекта',
						closeText	: 'Закрыть',
						buttons		:
						{
							'Сохранить' : function() { $(this).find('.cms_object_form').submit(); },
							'Отмена'	: function() { $(this).dialog('close'); }
						},
						close		: function()
						{
							$inst.removeClass('editing');
							$(this).dialog('destroy');
							$(this).remove();
						}
					});
				});
			break;

			default:
				alert('Неизвестный тип редактирования');
			break;
		}
	}
	else alert('Не все параметры указаны');

	return false;
}

// Удалить файл в объекте
function removeObjectFile(obj)
{
	if (confirm('Удалить файл?'))
	{
		var obj = $(obj).parents('.file_field');
		$(obj).html('<input name="fields['+$(obj).attr('alt')+']" type="hidden" value="" />');
	}
	return false;
}

// Диинамическая загрузка GoogleMaps
function gMapsLoaded()
{
	$('.google_map_field').gmapField();
}

// DOM
$(function()
{
	// Установка языка
	if ($('body').attr('lang') != 'undefined') _LANG_ = $('body').attr('lang');

	// Создаем панелей
	$('.cms_add, .cms_edit, .cms_remove').each(function()
	{
		if ($(this).find('> .cms_panel').length == 0)
		{
			var panel = '';
			if ($(this).hasClass('cms_add'))	panel = panel+'<a class="cms_add_btn" href="#" title="Добавть объект"></a>';
			if ($(this).hasClass('cms_edit'))	panel = panel+'<a class="cms_edit_btn" href="#" title="Редактировать"></a>';
			if ($(this).hasClass('cms_remove'))	panel = panel+'<a class="cms_remove_btn" href="#" title="Удалить"></a>';
			$(this).append('<div class="cms_panel">'+panel+'</div>');
			$(this).find('> .cms_panel').css('width', ($(this).find(' > .cms_panel a').length * 26)+'px').append('<div class="clear"></div>');
		}
	});

	// Добавление объектов
	$('.cms_panel .cms_add_btn').on('click', function()
	{
		cmsAddObject($(this).parent().parent());
		return false;
	});

	// Редактирование объектов
	$('.cms_panel .cms_edit_btn').on('click', function()
	{
		if (!$(this).hasClass('editing')) cmsEditObject($(this).parent().parent());
		return false;
	});

	// Редактирование по двойному клику
	$('.cms_edit').on('dblclick', function()
	{
		if (!$(this).hasClass('editing')) cmsEditObject($(this));
	});

	// Удаление объектов
	$('.cms_panel .cms_remove_btn').on('click', function()
	{
		if (confirm('Удалить объект и все вложенные объекты?'))
		{
			var $obj_container	= $(this).parent().parent();
			var obj_id			= cmsGetParam($obj_container, 'id');

			// Удаляем
			$.getJSON(_CMS_MODULES_+'objects/ajax/a/collection/remove/'+obj_id, function(json)
			{
				if (json)
				{
					if (json.status == 'ok') $obj_container.slideUp(300, function() { $(this).remove(); });
					else alert(json.msg);
				}
				else alert('Невозможно подключиться к серверу');
			});
		}

		return false;
	});

	// Отключение ссылок в редактируемых блоках
	$('a.cms_edit, .cms_edit a').click(function()
	{
		return false;
	});

	// Настройки календаря
	$.datepicker.setDefaults(
	{
		dateFormat:			'dd.mm.yy',
		changeMonth:		true,
		changeYear:			true,
		firstDay:			1,
		dayNames: 			['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
		dayNamesShort:		['Вск', 'Пон', 'Втр', 'Срд', 'Чет', 'Пят', 'Суб'] ,
		dayNamesMin:		['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		monthNames: 		['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		monthNamesShort: 	['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сент', 'Окт', 'Ноя', 'Дек'],
		nextText: 			'Вперед',
		prevText: 			'Назад'
	});
});