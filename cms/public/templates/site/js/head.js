// DOM
$(function()
{
	// Вкл/выкл фронтэнда
	$('#cms_header .cms_toggle').on('click', function()
	{
		if (!$(this).hasClass('processing'))
		{
			var $inst		= $(this);
			var new_status	= $inst.hasClass('off') ? 'on' : 'off'
			var checked		= true;

			// Проверка не закрытых редакторов
			if ((new_status == 'off') && ($('.cms_edit.editing').length > 0))
			{
				if (!confirm("Не сохраненные данные будут утеряны.\nВы действительно хотите подолжить?")) checked = false;
			}

			if (checked)
			{
				$inst.addClass('processing').css('opacity', 0.7);
				$.getJSON('/cms/ajax/frontend/'+new_status, function(json)
				{
					if (json.status == 'ok')
					{
						$inst.removeClass(status).addClass(new_status).css('opacity', 1.0);
						self.location.reload();
					}
					else
					{
						alert(json.msg);
						$inst.removeClass('processing').css('opacity', 1);
					}
				});
			}
		}
		return false;
	});
	
	// Очистить кэш сайта
	$('#cms_header .cms_clear_cache').on('click', function()
	{
		var $_inst = $(this);
		
		if (confirm('Очистить кэш сайта?'))
		{
			$_inst.text('Идет очистка кэша...');
			$.getJSON('/cms/modules/caching/clear/yes', function(json)
			{
				if (json.status == 'ok')
				{
					alert('Кэш сайта успешно очищен!');
					self.location.reload();
				}
				else alert(json.msg);
			});
			$_inst.text($_inst.attr('title'));
		}
	
		return false;
	});
});