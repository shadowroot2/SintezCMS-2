// DOM
$(function()
{
	// Даты
	$('#date_s, #date_e').datepicker();

	// Выбор города в заявках
	$('#order_reports_city').change(function()
	{
		$('#order_reports_city_form').submit();
	});

	// Выбор города в фильтре
	$('#report_city').change(function()
	{
		$('#filter_form').submit();
	});
});