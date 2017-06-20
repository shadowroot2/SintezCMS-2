// jQuery плагин Яндекс карты координаты v.1.0
// Coded by ShdoW (c)2013
(function($)
{
	jQuery.fn.yamapField = function(options)
	{
		var cfg = $.extend(
		{
			'width'		: 550,
			'height'	: 350,
			'center'	: [43.248552, 76.917605],
			'zoom'		: 10,
			'controls'	: ['zoomControl', 'geolocationControl'],
			'flying'	: true

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
						$lat = parseFloat($coords_latlng[0]);
						$lng = parseFloat($coords_latlng[1]);
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
			var setMarker = function(coords)
			{
				if (typeof coords == 'array' || typeof coords == 'object')
				{
					// Маркер есть - удаляем
					if (typeof $marker == 'object') removeMarker();

					$marker = new ymaps.Placemark(coords, {}, {
						preset		: 'islands#redDotIcon',
						draggable	: true
					});
					$map.geoObjects.add($marker);

					// Перетаскиваение маркера
					$marker.events.add('dragend', function(e)
					{
						var coords = $marker.geometry.getCoordinates();
						setMarker(coords);
					});

					// Удаление маркера
					$marker.events.add('click', function()
					{
						if (confirm('Удалить маркер?')) removeMarker();
					});

					// Центруем карту по маркеру
					$map.setCenter(coords, $zoom);

					// Пишем координаты и текущий зум в поле
					$_inst.val(coords[0].toFixed(6)+','+coords[1].toFixed(6)+':'+$map.getZoom());
				}
				else removeMarker();
			}

			// УДАЛЕНИЕ МАРКЕРА
			var removeMarker = function()
			{
				if (typeof $marker == 'object')
				{
					$map.geoObjects.remove($marker);
					$marker = false;
					$_inst.val('');
				}

				return true;
			}

			// ИЗМЕНЕНИЕ ПОЛЯ
			$_inst.change(function()
			{
				parseValue();
				setMarker([$lat, $lng]);
			});

			// ДОБАВЛЕНИЕ КОНТЕЙНЕРА КАРТЫ И СЛУШАЕМ КЛИКИ
			$_inst.after('<div id="yandex-map-'+token+'" class="yandex-map-container" style="width:'+cfg.width+'px; height:'+cfg.height+'px;"></div>');
			$map = new ymaps.Map('yandex-map-'+token, {
				center	 : [parseFloat($lat != '' ? $lat : cfg.lat), parseFloat($lng != '' ? $lng : cfg.lng)],
				zoom	 : parseInt($zoom != '' ? $zoom : cfg.zoom),
				controls : ['zoomControl', 'geolocationControl'],
				flying	 : true
			});
			$map.events.add('click', function(e)
			{
				setMarker(e.get('coords'));
			});
			$map.events.add('actiontickcomplete', function(e)
			{
				var tick = e.get('tick');
				if ($_inst.val() != '')
				{
					var coords_zoom = $_inst.val().split(':');
					$zoom = tick.zoom;
					$_inst.val(coords_zoom[0]+':'+tick.zoom);
				}
			});

			// МАРКЕР ЗАДАН В ПОЛЕ
			if ($set) setMarker([$lat, $lng]);
		});
	}
})(jQuery);