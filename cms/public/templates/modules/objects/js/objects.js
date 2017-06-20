const _AJAX_			= '/cms/modules/objects/ajax/a/';
const _CONNECT_ERROR_	= 'Невозможно связаться с сервером';

// Выделение полей отмеченных галочкой
function markSet()
{
	$('.infotable tr.set').removeClass('set');
	$('.infotable td input[type="checkbox"]:checked').parent().parent().addClass('set');
}

// Диинамическая загрузка GoogleMaps
function gMapsLoaded()
{
	$('.google_map_field').gmapField();
}

// Диинамическая загрузка Яндекс Карт
function yMapsLoaded()
{
	$('.yandex_map_field').gmapField();
}

// DOM
$(function()
{
	// Выбор языка
	$('#objects_lang').change(function()
	{
		var $obj = $(this);

		$obj.prop('disabled', true);
		$.getJSON(_AJAX_+'setlang/'+$obj.val(), function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok')
				{
					if ($obj.data('reload') == 1) self.location.reload();
					else $obj.prop('disabled', false);
				}
				else error_alert(json.msg);
			}
			else error_alert(_CONNECT_ERROR_);
		});
	});

	// Объектов на страницу
	$('#perpage').change(function()
	{
		var $obj = $(this);

		$obj.prop('disabled', true);
		$.getJSON(_AJAX_+'perpage/'+$obj.val(), function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok') self.location.reload();
				else error_alert(json.msg);
				$obj.prop('disabled', false);
			}
			else error_alert(_CONNECT_ERROR_);
		});
	});

	// Автопоиск объектов
	var searchcache = {}, lastXhr;
	$('#search').autocomplete(
	{
		minLength	: 3,
		source		: function(request, response)
		{
			var term = request.term;
			if (term in searchcache)
			{
				response(searchcache[term]);
				return;
			}
			lastXhr = $.getJSON(_AJAX_+'search/', request, function(data, status, xhr)
			{
				searchcache[term] = data;
				if (xhr === lastXhr) response(data);
			});
		}
	});

	// Вставить объекты
	$('#paste_objects').click(function()
	{
		$.getJSON(_AJAX_+'collection/paste/'+parseInt($('#objects_table').attr('container')), function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok') self.location.reload();
				else error_alert(json.msg);
			}
			else error_alert(_CONNECT_ERROR_);
		});

		return false;
	});

	// Очистить буффер
	$('#clear_clipboard').click(function()
	{
		$.getJSON(_AJAX_+'clearclipboard', function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok')
				{
					$('#objects_table tr.marked').removeClass('marked');
					$('#clipboard').fadeOut('fast', function() { $(this).find('b').text('0'); });
				}
				else error_alert(json.msg);
			}
			else error_alert(_CONNECT_ERROR_);
		});
		return false;
	});


	// Сортировка
	$('#objects_table .infotable.sortable').sortable(
	{
		containment	: 'parent',
		items		: 'tbody tr',
		axis		: 'y',
		tolerance	: 'pointer',
		distance	: 10,
		delay		: 200,
		cursor		: 'move',
		opacity		: 0.8,
		update		: function(event, ui)
		{
			var $item 		= ui.item;
			var curr		= $item.attr('rel');
			var prev  		= 0;
			var next		= 0;
			var sort_mass	= [];

			$item.addClass('disabled');

			// Заганяем текущую сортировку в массив
			$('#objects_table tbody tr').each(function(key, obj) { sort_mass[key] = $(obj).attr('rel');	});

			// Находим элемент и его окружение
			$(sort_mass).each(function(key, val)
			{
				if (val == curr)
				{
					if (key > 0) prev = sort_mass[key-1];
					if (key < sort_mass.length-1) next = sort_mass[key+1];
				}
			});

			// Отпраляем
			$.post(_AJAX_+'sort/',
			{
				id	 : curr,
				prev : prev,
				next : next
			},
			function(json)
			{
				if (typeof json == 'object')
				{
					if (json.status == 'ok') $item.removeClass('disabled');
					else error_alert(json.msg);
				}
				else error_alert(_CONNECT_ERROR_);

			}, 'json');
		}
	});

	// Выделить все объекты
	$('#check_all').click(function()
	{
		if ($(this).is(':checked'))
		{
			$('#objects_table .infotable td input[type="checkbox"]').prop('checked', true);
			$(this).attr('title', 'Снять выделение');
		}
		else
		{
			$('#objects_table .infotable td input[type="checkbox"]').prop('checked', false);
			$(this).attr('title', 'Выделить все');
		}
		markSet();
	});

	// Отмечание галочкой объектов
	$('#objects_table .infotable td input[type="checkbox"]').click(function()
	{
		if ($('.infotable td input[type="checkbox"]:checked').length < $('.infotable td input[type="checkbox"]').length)
		{
			$('.infotable th input[type="checkbox"]').prop("checked", false);
		}
		else $('.infotable th input[type="checkbox"]').prop("checked", true);

		markSet();
	});

	// Действия с объектами
	$('#objects_action').change(function()
	{
		var action	= $(this).val();
		var objects = [];

		if (action != '')
		{
			// Выбранные объекты
			var $selected_objects = $('#objects_table td input[type="checkbox"]:checked');

			// Загоняем объекты в массив
			$selected_objects.each(function() { objects.push($(this).val()); });

			// Объекты есть
			if (objects.length > 0)
			{
				// Подтверждение удаления
				if ((action == 'remove') && !confirm('Удалить выбранные объекты?')) return false;

				$.getJSON(_AJAX_+'collection/'+action+'/'+objects, function(json)
				{
					if (typeof json == 'object')
					{
						if (json.status == 'ok')
						{
							var $selected_containers = $selected_objects.parent().parent();

							// Убираем выделения
							$('#objects_table tr').removeClass('set').find('input[type="checkbox"]:checked').prop('checked', false);

							if (action == 'on') 		$selected_containers.removeClass('disabled');
							if (action == 'off') 		$selected_containers.addClass('disabled');
							if (action == 'remove') 	$selected_containers.fadeOut('normal', function() { $(this).remove() });
							if ((action == 'cut') || (action == 'copy'))
							{
								$selected_containers.addClass('marked');
								$('#clipboard').fadeIn('fast').find('b').text(objects.length);
							}

							$('#objects_action option').prop('selected', false).parent().find('option:first').prop('selected', true);
						}
						else error_alert(json.msg);
					}
					else error_alert(_CONNECT_ERROR_);
				});
			}
			else alert('Выберите объекты');
		}
	});


	// Клонировать объект
	$('#objects_table .clone_obj').click(function()
	{
		var $obj = $(this).parent().parent();

		$('#dialiog_clone').dialog(
		{
			title  		: 'Клонировение объекта',
			width		: 250,
			resizable	: false,
			modal		: true,
			buttons		:
			{
				'Клонировать' : function()
				{
					$.getJSON(_AJAX_+'clone/'+$obj.attr('rel')+'/'+$('#clone_count').val(), function(json)
					{
						if (typeof (json) == 'object')
						{
							if (json.status == 'ok') self.location.reload();
							else error_alert(json.msg);
						}
						else error_alert(_CONNECT_ERROR_);
					});
				}
			},
			close : function()
			{
				$('#clone_count').val('1');
				$(this).dialog('destroy');
			}
		});

		return false;
	});

	// Удалить объект
	$('#objects_table .remove_obj').click(function()
	{
		if (confirm('Удалить объект и все вложенные объекты?'))
		{
			var $obj = $(this).parent().parent();
			$.getJSON(_AJAX_+'collection/remove/'+$obj.attr('rel'), function(json)
			{
				if (typeof json == 'object')
				{
					if (json.status == 'ok') $obj.fadeOut('normal', function() { $(this).remove() });
					else error_alert(json.msg);
				}
				else error_alert(_CONNECT_ERROR_);
			});
		}

		return false;
	});


	// Добавление объекта
	$('#object_add').submit(function()
	{
		$('#infobox').hide().removeClass('errorbox').empty();
		$('#object_add_btn').prop('disabled', true);

		if ($('#object_name').val() == '')
		{
			$('#infobox').addClass('errorbox').html('Укажите название объекта').slideDown('fast');
			$('#object_name').focus();
			$('#object_add_btn').prop('disabled', false);
		}
		else return true;

		return false;
	});

	// Редактирование объекта
	$('#object_edit').submit(function()
	{
		$('#infobox').hide().removeClass('errorbox').empty();
		$('#object_edit_btn').prop('disabled', true);

		if ($('#object_name').val() == '')
		{
			$('#infobox').addClass('errorbox').html('Укажите название объекта').slideDown();
			$('#object_name').focus();
			$('#object_edit_btn').prop('disabled', false);
		}
		else return true;

		return false;
	});


	// Подгрузка полей шаблона
	$('#object_template').change(function()
	{
		$('#template_fields').empty();
		if (parseInt($(this).val()) != 0)
		{
			$.get(_AJAX_+'template_fields/'+$(this).val(), function(data)
			{
				$('#template_fields').html(data);
			});
		}
	});

	// Подгрузка полей доп.шаблона
	$('#template_fields').on('change', '.add_template_select', function()
	{
		var $container = $(this).parent().find('.add_template_fields');

		$container.hide().empty();
		if ($(this).val() != '')
		{
			$.get(_AJAX_+'additional_template_fields/'+$(this).val(), function(data)
			{
				$container.html(data).slideDown();
			});
		}
	});

	// Клонировать поля объекта
	$('#clone_object_fields').click(function()
	{
		$.getJSON(_AJAX_+'clonefields/'+$('#object_lang').val()+'/'+$(this).data('object_id'), function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok') top.location.reload();
				else error_alert(json.msg);
			}
			else error_alert(_CONNECT_ERROR_);
		});

		return false;
	});

	// Удалить файл в объекте
	$('#template_fields').on('click', '.remove_attach', function()
	{
		if (confirm('Удалить файл?'))
		{
			var $container = $(this).parents('.file_field');
			$container.html('<input name="fields['+$container.data('field_id')+']" type="hidden" value="" />');
		}

		return false;
	});
});