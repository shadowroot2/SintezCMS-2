// ХРАНИЛИЩЕ ФАЙЛОВ
var mupload_html5_files	= new Array();
var mupload_file_id		= 0;

// ПЕРЕКЛЮЧЕНИЕ ТИПОВ ОБЪЕКТОВ
function mupload_swtch_otype(type)
{
	mupload_otype				= type;
	mupload_compress			= false;
	mupload_compress_confirmed	= false;

	// Фотографии
	if (type == 'photos')
	{
		mupload_files				= '*.jpg;*.jpeg;*.gif,*.png';
		mupload_files_text			= 'Изображения';
		$("#mupload_photos_config").slideDown();
	}
	// Файлы
	else if (type == 'files')
	{
		mupload_files				= '*.*';
		mupload_files_text			= 'Все файлы';
		$("#mupload_photos_config").slideUp();
	}

	// Меняем параметры у FLASH загрузчика
	var mupload_flash_inst = mupload_getFlashInstanse();
	if (typeof(mupload_flash_inst) == 'object')
	{
		mupload_flash_inst.setUploadURL(mupload_url+'/'+mupload_otype+'/'+mupload_mother);
		mupload_flash_inst.setFileTypes(mupload_files, mupload_files_text);
	}
}

// ПЕРЕКЛЮЧЕНИЕ ТИПОВ ЗАГРУЗКИ
function mupload_swtch_type(type)
{
	mupload_type = type;

	// Прячем все кнопки и очищаем рабочую зону
	$("#muploadFlahButton, #muploadBrowse, #muploadStart, #muploadClear").hide();
	$("#muploadFiles, #muploadContainer, #muploadInfo").hide().empty();

	// FLASH - ЗАГРУЗКА
	if (mupload_type == 'flash')
	{
		// SWFUpload
		$('#muploadControl').swfupload(
		{
			debug					: false,
			upload_url				: mupload_url+'/'+mupload_otype+'/'+mupload_mother,
			post_params				: { 'PHPSESSID' : mupload_sess_id },
			use_query_string		: false,


			flash_url 				: mupload_flash_url,
			flash9_url				: mupload_flash9_url,

			file_size_limit 		: (mupload_max_file_size/1024),
			file_types				: mupload_files,
			file_types_description	: mupload_files_text,
			file_upload_limit 		: 0,
			file_post_name			: 'mfile',

			button_width 			: 95,
			button_height 			: 30,
			button_placeholder		: $('#muploadFlahButton')[0],
			button_window_mode		: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor			: SWFUpload.CURSOR.HAND,
			custom_settings 		:
			{
				thumbnail_width		: mupload_compress_w,
				thumbnail_height	: mupload_compress_h,
				thumbnail_quality	: 85
			}
		})

		// Инициализация
		.bind('swfuploadLoaded', function(event){})

		// Добавление файлов
		.bind('fileQueued', function(event, file)
		{
			// Размер
			var file_in	  = 'кб'
			var file_size = parseInt(file.size) / 1024;
			if (file_size > 1024)
			{
				file_in = 'мб';
				file_size = file_size / 1024;
			}

			// Добавляем в HTML
			$("#muploadContainer").append('<li class="hidden" id="'+file.id+'"><img src="'+cms_path+'images/file-icon.png" /><div class="mupload_item_name">'+file.name+'<div class="mupload_item_status ok">Готов к загрузке</div><div class="mupload_item_progress"><div></div></div></div> <div class="mupload_file_size">'+file_size.toFixed(1)+' '+file_in+'</div><div class="mupload_item_remove"><a href="#" onclick="return mupload_removeFile(this)"><img src="'+cms_path+'images/del.png" title="Удалить" /></a></div></li>');
		})

		// Ошибки добавления
		.bind('fileQueueError', function(event, file, errorCode, message)
		{
			switch (errorCode)
			{
				case -100:
					message = 'Выбранно максимальное количество файлов';
				break;
				case -110:
					var max_upload_in = 'кб.'
					var max_upload = mupload_max_file_size / 1024;
					if (max_upload >= 1024)
					{
						max_upload_in = 'мб.'
						max_upload = (max_upload / 1024).toFixed(1);
					}
					else max_upload.toFixed(0);
					message = '<b>'+file.name+'</b> превышает разрешимый допустимый размер '+max_upload+'&nbsp;'+max_upload_in;
				break
				case -120:
					message = '<b>'+file.name+'</b> не содержит в себе информации';
				break;
				case -130:
					'<b>'+file.name+'</b> не верный тип файла';
				break;
				default:
					alert(message);
				break
			}

			$("#muploadInfo").append('<div>'+message+'</div>').show();
			setTimeout(function() { $("#muploadInfo").fadeOut(800, function() { $(this).empty(); }); }, 5000);
		})

		// Файлы выбраны
		.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued)
		{
			if (numFilesQueued  > 0)
			{
				$("#muploadClear, #muploadContainer, #muploadStart").show();
				$("#muploadContainer").find('.hidden').each(function()
				{
					$(this).fadeIn('normal', function(){ $(this).removeClass('hidden'); });
				});
			}
		})

		// Начало загрузки
		.bind('uploadStart', function(event, file)
		{
			$("#"+file.id).find('.mupload_item_status').text('').hide();
			$("#"+file.id).find('.mupload_item_progress').fadeIn('fast');
		})

		// Процесс загрузки
		.bind('uploadProgress', function(event, file, bytesLoaded)
		{
			var upload_percents = 1;
			if (bytesLoaded > 0) upload_percents = ((parseInt(bytesLoaded) / parseInt(file.size)) * 100).toFixed(0);
			$("#"+file.id).find('.mupload_item_progress div').css({'width':upload_percents+'%'});
		})

		// Загружен
		.bind('uploadSuccess', function(event, file, serverData)
		{
			if (serverData == 'OK') serverData = 'Загружен!';
			else serverData = '<span class="error">'+serverData+'</span>';

			$("#"+file.id).find('.mupload_item_progress').hide().parent().parent().find('.mupload_item_remove').remove();
			$("#"+file.id).find('.mupload_item_status').html(serverData).fadeIn();
		})

		// Обработан
		.bind('uploadComplete', function(event, file)
		{
			var mupload_inst = mupload_getFlashInstanse();
			var mupload_file = mupload_inst.getQueueFile(0);
			if (mupload_file)
			{
				if (in_array(mupload_file.type.toLowerCase(), mupload_images))
				{
					mupload_inst.startResizedUpload(mupload_file.id, mupload_compress_w, mupload_compress_h, SWFUpload.RESIZE_ENCODING.JPEG, mupload_inst.customSettings.thumbnail_quality, false);
				}
				else mupload_inst.startUpload();
			}
		})

		// Ошибка загрузки
		.bind('uploadError', function(event, file, errorCode, message)
		{
			switch (errorCode)
			{
				case -200:
					message = 'Сервер не отвечает';
				break;
				case -210:
					message = 'Неверный адрес загрузки';
				break;
				case -220:
					message = 'Внутренняя ошибка сервера';
				break;
				case -230:
					message = 'Ошибка доступа';
				break;
				case -240:
					message = 'Лимит загружаемых файлов превышен';
				break;
				case -250:
					message = 'Ошибка загрузки';
				break;
				case -260:
					message = 'Файл не найден';
				break;
				case -270:
					message = 'Не прошел валидацию';
				break;
				case -280:
					message = 'Отменен';
				break;
				case -290:
					message = 'Загрузка остановлена';
				break;
				case -300:
					message = 'Ошибка сжатия изображения';
				break;
				default:
					alert(message);
				break
			}
			$("#"+file.id).find('.mupload_item_progress').hide().parent().parent().find('.mupload_item_remove').remove();
			$("#"+file.id).find('.mupload_item_status').html('<span class="error">'+message+'</span>').show();
		});

		// Показываем кнопки
		$(".swfupload, #muploadBrowse").fadeIn();

		// Проверяем инстанцию
		if (typeof(mupload_getFlashInstanse()) == 'object') return false;
	}

	// HTML5 - ЗАГРУЗКА
	else if (mupload_type == 'html5')
	{
		// Убираем Flash загрузчик
		if (typeof(mupload_getFlashInstanse()) == 'object')  $.swfupload.destroy("#muploadControl");

		// Проверяем наличие FileReader
		if (typeof(FileReader) != 'function')
		{
			$("#muploadInfo").html('Извините, ваш браузер не поддерживает HTML5 загрузку').hide().fadeIn(1500);
			return false;
		}

		// Хранилище файлов
		mupload_html5_files = new Array();
		mupload_file_id 	= 0;

		// Drag&Drop
		var mupload_html5_drag_obj = $('<div class="mupload_html5_dragdrop">Перетащите сюда файлы</div>').bind(
		{
			dragenter: function() {
				$(this).addClass('mupload_dragdrop_light');
				return false;
			},
			dragover: function() {
				return false;
			},
			dragleave: function() {
				$(this).removeClass('mupload_dragdrop_light');
				return false;
			},
			drop: function(e) {
				var dt = e.originalEvent.dataTransfer;
				mupload_addFiles(dt.files);
				$(this).removeClass('mupload_dragdrop_light');
				return false;
			}
		});

		// Обзор
		var mupload_html5_file_obj = $('<input type="file" name="mfile" multiple />').bind(
		{
		    change: function()
			{
				mupload_addFiles(this.files);
			}
		});

		$("#muploadFiles").append(mupload_html5_drag_obj).append('или выберите ').append(mupload_html5_file_obj).fadeIn();
	}
}

// Получение инстанции Flash объекта
function mupload_getFlashInstanse()
{
	return $.swfupload.getInstance('#muploadControl');
}

// Получение инстанции HTML5 объекта
function mupload_getHTML5Instanse()
{
	return $("#muploadFiles").find('input[type="file"]')[0];
}


// Задание размера фотографий
function mupload_set_resize()
{
	mupload_compress_w			= parseInt($("#mupload_compress_w").val());
	mupload_compress_h			= parseInt($("#mupload_compress_h").val());
	mupload_compress			= true;
	mupload_compress_confirmed	= true;

	// Меняем размеры сжатия у Flash загрузчика
	var mupload_flash_inst = mupload_getFlashInstanse();
	if (typeof(mupload_flash_inst) == 'object')
	{
		mupload_flash_inst.thumbnail_width  = mupload_compress_w;
		mupload_flash_inst.thumbnail_height = mupload_compress_h;
	}

	$("#mupload_photos_config").slideUp();
}

// Добавление HTML5 файлов
function mupload_addFiles(files)
{
	// Пробегаемся по файлам
	$.each(files, function(i, file)
	{
		var message		= false;

		// Расширение файла
		var name_parts	= file.name.toString().split('.');
		var file_ext	= name_parts[name_parts.length-1].toLowerCase();

		// Проверяем файлы
		if (parseInt(file.size) <= 0)
		{
			message = '<b>'+file.name+'</b> не содержит в себе информации';
		}
		else if (parseInt(file.size) > parseInt(mupload_max_file_size))
		{
			var max_upload_in = 'кб.'
			var max_upload = mupload_max_file_size / 1024;
			if (max_upload >= 1024)
			{
				max_upload_in = 'мб.'
				max_upload = (max_upload / 1024).toFixed(1);
			}
			else max_upload.toFixed(0);
			message = '<b>'+file.name+'</b> превышает разрешимый допустимый размер '+max_upload+'&nbsp;'+max_upload_in;
		}

		if (message)
		{
			$("#muploadInfo").html(message).hide().fadeIn(1500);
			setTimeout(function() { $("#muploadInfo").fadeOut(800, function() { $(this).empty(); }); }, 5000);
		}
		else
		{
			// Записываем в файлы
			mupload_file_id = mupload_file_id + 1;
			mupload_html5_files.push(new Array(mupload_file_id, file));

			// Размер файла
			var file_in	  = 'кб'
			var file_size = parseInt(file.size) / 1024;
			if (file_size > 1024)
			{
				file_in = 'мб';
				file_size = file_size / 1024;
			}

			// Создаем BOX
			var li = $('<li class="hidden" alt="'+mupload_file_id+'"><img width="41" height="50" src="'+cms_path+'images/file-icon.png" class="preview" /><div class="mupload_item_name">'+file.name+'<div class="mupload_item_status ok">Готов к загрузке</div><div class="mupload_item_progress"><div></div></div></div> <div class="mupload_file_size">'+file_size.toFixed(1)+' '+file_in+'</div><div class="mupload_item_remove"><a href="#" onclick="return mupload_removeFile(this)"><img src="'+cms_path+'images/del.png" title="Удалить" /></a></div></li>');

			// Читаем содержимое изображение
			if (in_array('.'+file_ext, mupload_images))
			{
				// FileReader
				var FReader = new FileReader();
				FReader.onload = (function(li_element)
				{
					return function(e)
					{
						$(li_element).find('img.preview').attr('src', e.target.result);
					}

				})(li);
				FReader.readAsDataURL(file);
			}

			// Пишем в HTML
			$("#muploadContainer").append(li);
		}
	});

	if (mupload_html5_files.length > 0) $("#muploadClear, #muploadContainer, #muploadStart").show();
}

// Удаление файла
function mupload_removeFile(obj)
{
	var obj = $(obj).parent().parent();

	// Flash
	if (mupload_type == 'flash')
	{
		var mupload_inst = mupload_getFlashInstanse();
		mupload_inst.cancelUpload($(obj).attr('id'), false);
	}

	// HTML5
	else if (mupload_type = 'html5')
	{
		var file_id = parseInt($(obj).attr('alt'));
		$(mupload_html5_files).each(function(key, val)
		{
			if (val[0] == file_id) mupload_html5_files.splice(key, 1);
		});
	}

	// Удаляем
	$(obj).fadeOut('normal', function() { $(this).remove(); });

	return false;
}

// Удалить все файлы
function mupload_clear()
{
	// Flash
	if (mupload_type == 'flash')
	{
		var mupload_inst = mupload_getFlashInstanse();
		var mupload_stat = mupload_inst.getStats();
		if (mupload_stat.files_queued > 0)
		{
			for(i=0; i<mupload_stat.files_queued; i++) mupload_inst.cancelUpload(0, false);
		}
	}

	// HTML5
	else if (mupload_type = 'html5')
	{
		mupload_html5_files = new Array();
	}

	$("#muploadClear, #muploadStart").hide();
	$("#muploadContainer").fadeOut('normal', function() { $(this).empty(); });

	return false;
}

// Загрузить файлы
function mupload_send_files()
{
	// Flash
	if (mupload_type == 'flash')
	{
		// Сжать?
		if (mupload_compress_confirmed == false)
		{
			if (confirm('Сжимать изображения до размера '+mupload_compress_w+'x'+mupload_compress_h+'?')) mupload_compress = true;
			mupload_compress_confirmed = true;
		}

		// Получаем инстанцию объекта
		var mupload_inst = mupload_getFlashInstanse();
		var mupload_file = mupload_inst.getQueueFile(0);

		if (mupload_file)
		{
			if (in_array(mupload_file.type.toLowerCase(), mupload_images))
			{
				mupload_inst.startResizedUpload(mupload_file.id, mupload_compress_w, mupload_compress_h, SWFUpload.RESIZE_ENCODING.JPEG, mupload_inst.customSettings.thumbnail_quality, false);
			}
			else mupload_inst.startUpload();
		}
		else $("#muploadInfo").html('Выберите файлы для загрузки').fadeIn(1000);
	}

	// HTML5
	else if (mupload_type = 'html5')
	{
		if (mupload_html5_files.length > 0)
		{
			mupload_html5_startUpload();
		}
		else $("#muploadInfo").html('Выберите файлы для загрузки').fadeIn(1000);
	}

	return false;
}

// Загрузка HTML5
function mupload_html5_startUpload()
{
	// Первый элемент в списке
	if (typeof(mupload_html5_files[0]) == 'object')
	{
		var item_file		= mupload_html5_files[0][1];
		var item_container	= $("#muploadContainer").find('li[alt="'+mupload_html5_files[0][0]+'"]');

		// Показываем прогрессбар
		$(item_container).find('.mupload_item_status').hide().empty();
		$(item_container).find('.mupload_item_progress').fadeIn('fast');

		// Запускаем загрузчик
		new html5uploader(
		{
			file:       item_file,
			url:        mupload_url+'/'+mupload_otype+'/'+mupload_mother,
			fieldName:  'mfile',

			// Прогресс загрузки
			onprogress: function(upload_percents)
			{
				$(item_container).find('.mupload_item_progress div').css({'width':upload_percents+'%'});
			},

			// Загрузка завершена
			oncomplete: function(done, serverData)
			{
				$(item_container).find('.mupload_item_progress div').animate({'width':'100%'}, 1000, function()
				{
					$(item_container).find('.mupload_item_progress').hide().parent().parent().find('.mupload_item_remove').remove();

					// Загружен
					if(done)
					{
						if (serverData == 'OK') serverData = 'Загружен!';
						else serverData = '<span class="error">'+serverData+'</span>';

						$(item_container).find('.mupload_item_status').html(serverData).fadeIn();
					}
					// Ошибка
					else $(item_container).find('.mupload_item_status').html('<span class="error">Ощибка загрузки</span>').fadeIn();

					mupload_html5_files.splice(0, 1);
					mupload_html5_startUpload();
				});
			}
		});
	}
}

// HTML5 загрузчик файлов
var html5uploader = function(params)
{
    if(!params.file || !params.url) {
        return false;
    }

    this.xhr    = new XMLHttpRequest();
    this.reader = new FileReader();

    this.progress = 0;
    this.uploaded = false;
    this.successful = false;
    this.lastError = false;

    var self = this;

    self.reader.onload = function()
	{
        self.xhr.upload.addEventListener("progress", function(e) {
            if (e.lengthComputable) {
                self.progress = (e.loaded * 100) / e.total;
                if(params.onprogress instanceof Function) {
                    params.onprogress.call(self, Math.round(self.progress));
                }
            }
        }, false);

        self.xhr.upload.addEventListener("load", function(){
            self.progress = 100;
            self.uploaded = true;
        }, false);

        self.xhr.upload.addEventListener("error", function(){
            self.lastError = {
                code: 1,
                text: 'Ошибка загрузки на сервер'
            };
        }, false);

        self.xhr.onreadystatechange = function () {
            var callbackDefined = params.oncomplete instanceof Function;
            if (this.readyState == 4) {
                if(this.status == 200) {
                    if(!self.uploaded) {
                        if(callbackDefined) {
                            params.oncomplete.call(self, false);
                        }
                    } else {
                        self.successful = true;
                        if(callbackDefined) {
                            params.oncomplete.call(self, true, this.responseText);
                        }
                    }
                } else {
                    self.lastError = {
                        code: this.status,
                        text: 'HTTP ответ не OK ('+this.status+')'
                    };
                    if(callbackDefined) {
                        params.oncomplete.call(self, false);
                    }
                }
            }
        };

        self.xhr.open("POST", params.url);

        var boundary = "xxxxxxxxx";
        self.xhr.setRequestHeader("Content-Type", "multipart/form-data, boundary="+boundary);
        self.xhr.setRequestHeader("Cache-Control", "no-cache");

        var body = "--" + boundary + "\r\n";
        body += "Content-Disposition: form-data; name='"+(params.fieldName || 'file')+"'; filename='" + params.file.name + "'\r\n";
        body += "Content-Type: application/octet-stream\r\n\r\n";
        body += self.reader.result + "\r\n";
        body += "--" + boundary + "--";

        if(self.xhr.sendAsBinary) {
            // firefox
            self.xhr.sendAsBinary(body);
        } else {
            // chrome (W3C spec.)
            self.xhr.send(body);
        }

    };

    self.reader.readAsBinaryString(params.file);
};