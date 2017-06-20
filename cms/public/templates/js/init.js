// Проверка e-mail
email_check = /^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i;

// Ввод латинских букв
function latInput(input)
{
	$(input).val($(input).val().replace(/[^\w]/g, ''));
}

// Ввод только цифр
function digInput(input)
{
	$(input).val($(input).val().replace(/[^\d,]/g, ''));
}

// Ввод букв, дифр и _
function loginInput(input)
{
	$(input).val($(input).val().replace(/[^\d\w_]/g, ''));
}

// Вывод ошибок
function error_alert(msg)
{
	alert('Ошибка: '+msg);
}

// in_array()
function in_array(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

// DOM
$(function()
{
	// Активация меню
	var menu_index = parseInt($(".left_menu").find('h3.set:first').attr('alt'));

	// Левое меню
	$("nav.left_menu").accordion(
	{
		heightStyle	: 'content',
		collapsible	: true,
		animate		: 200,
		active		: menu_index
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

	// FancyBox
	$('a.fancy').fancybox({
	   	openEffect	: 'elastic',
    	closeEffect	: 'elastic'	
	});
});

