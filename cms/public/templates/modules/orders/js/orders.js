// Выбрать все
function selectOrdersAll(obj)
{
	if ($(obj).is(":checked") == true) $(".infotable td").find('input[type="checkbox"]').attr("checked", true);
	else $(".infotable td").find('input[type="checkbox"]').attr("checked", false);
	checksSet();
}

// Выделение полей отмеченных галочкой
function checksSet()
{
	$(".infotable tr").removeClass('set');
	$(".infotable td").find('input[type="checkbox"]:checked').parent().parent().addClass('set');
}

// DOM
$(function()
{
	// Даты
	$('#date_s, #date_e').datepicker();

	// Отмечание галочкой объектов
	$(".infotable td").find('input[type="checkbox"]').click(function()
	{
		if ($(".infotable td").find('input[type="checkbox"]:checked').length < $(".infotable td").find('input[type="checkbox"]').length)
		{
			$(".infotable th").find('input[type="checkbox"]').attr("checked", false);
		}
		else $(".infotable th").find('input[type="checkbox"]').attr("checked", true);

		checksSet();
	});

	// Удаеление заявки
	$('.remove_order').click(function()
	{
		var obj = $(this).parent().parent();

		if (confirm('Удалить заявку?'))
		{
			$.getJSON('/cms/modules/orders/ajax/a/remove/'+$(obj).attr('alt'), function(json)
			{
				if (json)
				{
					if (json.status == 'ok')
					{
						$(obj).fadeOut('fast', function() { $(this).remove(); })
					}
					else alert(json.msg);
				}
				else alert('Невозможно связаться с сервером');
			});
		}

		return false;
	});

	// Смена способа оплаты
	$('#pay_type').change(function()
	{
		if (confirm('Вы уверены, что хотите поменять способ оплаты?'))
		{
			$(this).attr('disabled', true);
			$.getJSON('/cms/modules/orders/ajax/a/changepaytype',
			{
				id	 : $(this).attr('alt'),
				type : $(this).val()
			},
			function(json)
			{
				if (json.status == 'ok') top.location.reload();
				else alert('Ошибка: '+json.msg);
			});
			$(this).attr('disabled', false);
		}
	});

	// Смена статуса оплаты
	$('#payed').change(function()
	{
		if (confirm('Подтвержаете что счет оплачен?'))
		{
			$(this).attr('disabled', true);
			$.getJSON('/cms/modules/orders/ajax/a/changepayed',
			{
				id	 	: $(this).attr('alt'),
				status	: $(this).val()
			},
			function(json)
			{
				if (json.status == 'ok') top.location.reload();
				else alert('Ошибка: '+json.msg);
			});
			$(this).attr('disabled', false);
		}
	});

	// Смена статуса заявки
	$('#order_status').change(function()
	{
		$(this).attr('disabled', true);
		$.getJSON('/cms/modules/orders/ajax/a/changestatus',
		{
			id		: $(this).attr('alt'),
			status	: $(this).val()
		},
		function(json)
		{
			if (json.status != 'ok') alert('Ошибка: '+json.msg);
		});
		$(this).attr('disabled', false);
	});

	// Группавая смена статуса
	$('#orders_status').change(function()
	{
		if (($(this).val() != '') && ($(this).val() != $('#filter_status').val()))
		{
			var orders = [];
			$('#orders_table').find('.orders_selelect:checked').each(function()
			{
				orders.push($(this).parent().parent().attr('alt'));
			});

			if (orders.length > 0)
			{
				$('#orders_status').attr('disabled', true);
				$.post('/cms/modules/orders/ajax/a/changegroupstatus', { orders : orders, status : $(this).val() },
					function(json)
					{
						if (json)
						{
							if (json.status == 'ok')
							{
								$('#orders_table').find('.orders_selelect:checked').parent().parent().fadeOut('fast', function() { $(this).remove(); });
							}
							else alert('Ошибка: '+json.msg);
						}
						else alert('Невозможно связаться с сервером');

					},
					'json'
				);
				$('#orders_status').attr('disabled', false);
			}
			else  alert('Выберите заявки');
		}

		$('#orders_status').find('option:first').attr('selected', true);
	});

	// Рефералы пользователя
	$('#order_user_referals').click(function()
	{
		$.getJSON('/cms/modules/orders/ajax/a/referals/'+$(this).attr('alt'),
		function (json)
		{
			if ((json.status == 'ok') && json.referals)
			{
				$('#order_user_referals_win .formhead').html('<h3>Всего рефералов: '+$(json.referals).length+'</h3>');
				$('#order_user_referals_win .formfield').html('<ul></ul>');

				$(json.referals).each(function(key, ref)
				{
					$('#order_user_referals_win ul').append('<li><a href="/cms/modules/site_users/edit/'+ref.id+'"><b>'+ref.name+'</b></a> (<i>'+ref.regdate+'</i>, '+ref.orders+')</li>');
				});

				$("#order_user_referals_win").dialog(
				{
					modal		: true,
					resizable	: false,
					title		: 'Список рефералов пользователя',
					width		: 500,
					buttons :
					{
						'Закрыть' : function() { $(this).dialog('destroy'); }
					}
				});
			}
			else error_alert(json.msg);
		});

		return false;
	});
});