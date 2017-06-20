// jQuery плагин Google Maps координаты v.1.0
// Coded by ShdoW (c)2013
(function($)
{
	jQuery.fn.gmapField = function(options)
	{
		var cfg = $.extend(
		{
			'width'		: 550,
			'height'	: 350,
			'lat'		: 43.255112,
			'lng'		: 76.912622,
			'zoom'		: 10

		}, options),

		token = 0;
		return this.each(function()
		{
			token = token + 1;

			var
				$_inst			= $(this),
				$map			= false,
				$marker	  		= false,
				$lat			= '',
				$lng			= '',
				$zoom			= '',
				$set			= false;

			// ОБРАБОТКА ТЕКЩУЕГО ЗНАЧЕНИЯ
			function parseValue()
			{
				if ($_inst.val() != '')
				{
					var $coords_zoom 	= $_inst.val().split(':');
					var $coords_latlng	= $coords_zoom[0].split(',');
					if ($coords_latlng.length == 2)
					{
						$lat = $coords_latlng[0];
						$lng = $coords_latlng[1];
						$set = true;

						if ($coords_zoom.length == 2) $zoom = parseInt($coords_zoom[1]);
					}
					else $_inst.val('').change();
				}
				else
				{
					$lat	= '';
					$lng	= '';
					$zoom	= '';
					$set	= false;
				}
			}
			parseValue();

			// УСТАНОВКА МАРКЕРА
			var setMarker = function(marker_lat, marker_lng)
			{
				if ((marker_lat != '') && (marker_lng !=''))
				{
					var $marker_latLng = new google.maps.LatLng(marker_lat, marker_lng);

					// Создаем или двигаем маркер
					if (!$marker)
					{
						$marker = new google.maps.Marker(
						{
							position	: $marker_latLng,
							map			: $map,
							draggable	: true,
							animation	: google.maps.Animation.DROP,
							title		: 'Ваш маркер'
						});

						// Перетаскиваение маркера
						google.maps.event.addListener($marker, 'dragend', function(event)
						{
							setMarker(event.latLng.lat(), event.latLng.lng());
						});

						// Удаление маркера
						google.maps.event.addListener($marker, 'click', function()
						{
							if (confirm('Удалить маркер?')) removeMarker();
						});
					}
					else $marker.setPosition($marker_latLng);

					// Центруем карту по маркеру
					$map.setCenter($marker_latLng);

					// Пишем координаты и текущий зум в поле
					$_inst.val($marker_latLng.toUrlValue()+':'+$map.getZoom());
				}
				else removeMarker();
			}

			// УДАЛЕНИЕ МАРКЕРА
			var removeMarker = function()
			{
				if ($marker)
				{
					$marker.setMap(null);
					$marker = false;
					$_inst.val('');
				}

				return true;
			}

			// ИЗМЕНЕНИЕ ПОЛЯ
			$_inst.change(function()
			{
				parseValue();
				setMarker($lat, $lng);
			});

			// ДОБАВЛЕНИЕ КОНТЕЙНЕРА КАРТЫ И СЛУШАЕМ КЛИКИ
			$_inst.after('<div id="google-map-'+token+'" class="google-map-container" style="width:'+cfg.width+'px; height:'+cfg.height+'px;"></div>');
			$map = new google.maps.Map(document.getElementById('google-map-'+token),
			{
				mapTypeId	: google.maps.MapTypeId.ROADMAP,
				center		: new google.maps.LatLng(($lat != '' ? $lat : cfg.lat), ($lng != '' ? $lng : cfg.lng)),
				zoom		: ($zoom != '' ? $zoom : cfg.zoom)
			});
			google.maps.event.addListener($map, 'click', function(event)	{ setMarker(event.latLng.lat(), event.latLng.lng()); });
			google.maps.event.addListener($map, 'zoom_changed', function()	{ if (typeof($marker) == 'object') setMarker($marker.getPosition().lat(), $marker.getPosition().lng()); });

			// МАРКЕР ЗАДАН В ПОЛЕ
			if ($set) setMarker($lat, $lng);
		});
	}
})(jQuery);