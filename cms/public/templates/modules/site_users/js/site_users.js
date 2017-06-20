// DOM
$(function()
{
	var _AJAX_ = '/cms/modules/site_users/ajax/a/';
	var _CONNECT_ERROR_ = 'Невозможно подключиться к серверу';

	// Включение/Выключени
	$('#site_users_table .switch').click(function()
	{
		var $obj = $(this).parent().parent();

		$.getJSON(_AJAX_+'switch/'+$obj.attr('rel'), function(json)
		{
			if (typeof json == 'object')
			{
				if (json.status == 'ok')
				{
					$obj.find('.switch img').attr(
					{
						'src'   : (json.active == 1 ? '/cms/images/on.png' : '/cms/images/off.png'),
						'title' : (json.active == 1 ? 'Включен' : 'Отключен')
					});

					if (json.active == 1) $obj.parent().parent().removeClass('disabled');
					else $obj.parent().parent().addClass('disabled');
				}
				else error_alert(json.msg);
			}
			else error_alert(_CONNECT_ERROR_);
		});

		return false;
	});

	// Удалить пользователя
	$('#site_users_table .remove').click(function()
	{
		var $obj = $(this).parent().parent();

		if (confirm('Удалить пользователя?'))
		{
			$.getJSON(_AJAX_+'remove/'+$obj.attr("rel"), function(json)
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

	// Даты
	$('#date_s, #date_e').datepicker();

	// Автопоиск
	var searchcache = {}, lastXhr;
	$("#search").autocomplete(
	{
		minLength : 3,
		source: function(request, response)
		{
			var term = request.term;

			if (term in searchcache)
			{
				response(searchcache[term]);
				return;
			}

			lastXhr = $.getJSON("/cms/modules/site_users/ajax/a/search/", request, function(data, status, xhr)
			{
				searchcache[term] = data;
				if (xhr === lastXhr) response(data);
			});
		}
	});
});