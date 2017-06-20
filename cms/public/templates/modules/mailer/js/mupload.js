var total_files_size	= 0;
var total_bytes_loaded	= 0;

$(function()
{
	// SWFUpload
	$('#muploadControl').swfupload(
	{
		debug					: false,
		upload_url				: mupload_url,
		post_params				: { 'PHPSESSID' : mupload_sess_id },
		use_query_string		: false,


		flash_url 				: mupload_flash_url,
		flash9_url				: mupload_flash9_url,

		file_size_limit 		: (mupload_max_file_size/1024),
		file_types				: mupload_files,
		file_types_description	: mupload_files_text,
		file_upload_limit 		: 0,
		file_post_name			: 'attach',

		button_width 			: 125,
		button_height 			: 30,
		button_placeholder		: $('#muploadFlashButton')[0],
		button_window_mode		: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor			: SWFUpload.CURSOR.HAND
	})

	// Инициализация
	.bind('swfuploadLoaded', function(event){ total_files_size = 0; total_bytes_loaded = 0; })

	// Добавление файлов
	.bind('fileQueued', function(event, file){ total_files_size = total_files_size + file.size; })

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
	.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){
		var mupload_inst = mupload_getFlashInstanse();
		var mupload_file = mupload_inst.getQueueFile(0);
		if (mupload_file)
		{
			$("#attach_btn .progress").css('width', '1%').fadeIn('fast');
			mupload_inst.startUpload();
		}
	})

	.bind('uploadStart', function(event, file){})
	.bind('uploadProgress', function(event, file, bytesLoaded){})

	// Загружен
	.bind('uploadSuccess', function(event, file, serverData)
	{
		// Размер
		var file_in	  = 'кб'
		var file_size = parseInt(file.size) / 1024;
		if (file_size > 1024)
		{
			file_in = 'мб';
			file_size = file_size / 1024;
		}

		if (serverData == 'OK') file_name = '<b>'+file.name+'</b>';
		else file_name = '<b class="error">'+file.name+' '+serverData+'</b>';

		// Добавляем в HTML
		$("#attach_list").append('<li id="'+file.id+'" title="'+serverData+'"><input type="checkbox" name="attach[]" value="'+file.name+'" checked /> '+file_name+' ('+file_size.toFixed(1)+' '+file_in+')</li>');
	})

	// Обработан
	.bind('uploadComplete', function(event, file)
	{
		total_bytes_loaded = total_bytes_loaded + file.size
		upload_percents = ((parseInt(total_bytes_loaded) / parseInt(total_files_size)) * 100).toFixed(0);
		$("#attach_btn .progress").css({ 'width' : upload_percents+'%'});

		var mupload_inst = mupload_getFlashInstanse();
		var mupload_file = mupload_inst.getQueueFile(0);
		if (mupload_file) mupload_inst.startUpload();
		else
		{
			total_files_size = 0;
			total_bytes_loaded = 0;
			$("#attach_btn .progress").fadeOut(3000);
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

		$('#'+file.id).attr('title', message);
	});
});

// Получение инстанции Flash объекта
function mupload_getFlashInstanse()
{
	return $.swfupload.getInstance('#muploadControl');
}