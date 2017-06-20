// DOM
$(function()
{
	$('.google_map').each(function(key, obj)
	{
		var latlng = $(obj).data('coords').split(',');
			latlng = new google.maps.LatLng(latlng[0], latlng[1]);

		var $map = new google.maps.Map(document.getElementById($(obj).attr('id')),
		{
			mapTypeId	: google.maps.MapTypeId.ROADMAP,
			center		: latlng,
			zoom		: parseInt($(obj).data('zoom'))
		});

		var $marker = new google.maps.Marker({
			map			: $map,
			position	: latlng,
			/*icon		: '/public/templates/images/icon.png',*/
			animation	: google.maps.Animation.DROP
		});
	});
});