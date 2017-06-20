// DOM
$(function()
{
	// Ограничение на ввод логина
	$('#login').keyup(function()
	{
		$(this).val($(this).val().replace(/[^\d\w_]/g, ''));
	});

	// Авторизация
	$('#login_form').submit(function()
	{
		var error 	= false;
		var focused = false;

		$('#infobox').hide().removeClass('errorbox').removeClass('goodbox').empty();
		$('.required').removeClass('.required');
		$('#login_btn').attr('disabled', true);

		if ($('#login').val() == '') { error = true; $('#login').addClass('.required').focus(); focused = true; }
		if ($('#pass').val() == '')  { error = true; $('#pass').addClass('.required'); if (!focused) { $('#pass').focus(); } }

		if (!error)
		{
			$.post('/cms/ajax/auth/',
			{
				login	: $('#login').val(),
				pass	: $('#pass').val(),
				save	: $('#remember').is(':checked') ? 1 : 0
			},
			function(json)
			{
				if (json)
				{
					if (json.status == 'ok')
					{
						$('#infobox').addClass('goodbox').text('Авторизация выполнена').fadeIn('fast');
						self.location = '/cms/';
					}
					else $('#infobox').addClass('errorbox').text(json.msg).fadeIn('fast');
				}
				else $('#infobox').addClass('errorbox').text('Невозможно подключиться к серверу').fadeIn('fast');

			}, 'json');
		}
		$('#login_btn').attr('disabled', false);

		return false;
	});

	// Редактирование профиля
	$('#profile_form').submit(function()
	{
		$('#infobox').hide().removeClass('errorbox').removeClass('goodbox').empty();
		$('#profile_save_btn').attr('disabled', true);

		if ($('#user_name').val() == '')
		{
			$('#infobox').addClass('errorbox').text('Укажите ваше имя').fadeIn('fast');
			$('#user_name').focus();
		}
		else if (($('#user_new_password').val() != '') && ($('#user_password').val() == ''))
		{
			$('#infobox').addClass('errorbox').text('Укажите текущий пароль').fadeIn('fast');
			$('#user_password').focus();
		}
		else if (($('#user_password').val() != '') && (($('#user_new_password').val() == '') || ($('#user_new_password_conf').val() != $('#user_new_password').val())))
		{
			$('#infobox').addClass('errorbox').text('Неверный новый пароль или подтверждение').fadeIn('fast');
			$('#user_new_password').focus();

		}
		else
		{
			$.post('/cms/ajax/editprofile',
				{
					name 	 : $('#user_name').val(),
					pass 	 : $('#user_password').val(),
					new_pass : $('#user_new_password').val()
				},
				function(json)
				{
					if (json)
					{
						if (json.status == 'ok')
						{
							$('#user_password, #user_new_password, #user_new_password_conf').val('');
							$('#infobox').addClass('goodbox').text('Профиль успешно сохранен!').fadeIn('fast');
							setTimeout(function() { $('#infobox').fadeOut(1000, function() { $(this).removeClass('goodbox').empty(); }) }, 5000);
						}
						else $('#infobox').addClass('errorbox').text(json.msg).fadeIn('fast');
					}
					else $('#infobox').addClass('errorbox').text('Невозможно подключиться к серверу').fadeIn('fast');

				}, 'json');
		}
		$('#profile_save_btn').attr('disabled', false);

		return false;
	});
});