<?
// Уникальный ID ошибки
$error_id = uniqid('error');
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<style type="text/css">
#core_error { background: #ddd; font-size: 16px; font-family:Arial; text-align: left; color: #111; }
#core_error h1, #core_error h2 { margin: 0; padding: 10px; font-size: 16; font-weight: normal; background: #911; color: #fff; }
#core_error h1 a, #core_error h2 a { color: #fff; }
#core_error h2 { background: #222; }
#core_error h3 { margin: 0; padding: 4px 0 0; font-size: 16px; font-weight: normal; }
#core_error p { margin: 0; padding: 5px; }
#core_error a { color: #1b323b; }
#core_error div.content { padding: 5px 20px 20px; overflow: hidden; }
#core_error pre.source { margin-bottom: 10px; padding: 5px; background:#fff; border:dotted 1px #b7c680; font-size:12px; }
#core_error pre.source span.line { display: block; line-height:12px; height:12px; padding:3px; margin:0; }
#core_error pre.source span.highlight { background: #f0eb96; color:#991111; }
#core_error pre.source span.line span.number { color: #929292; }
#core_error table { width: 100%; display: block; margin-top: 5px; padding: 0; border-collapse: collapse; background: #fff; }
#core_error table td { border: solid 1px #ddd; text-align: left; vertical-align:top; padding: 5px; }
#core_error ol.trace { display: block; margin-left: 20px; padding: 0; list-style: decimal; }
#core_error ol.trace li { margin: 0; padding: 0; }
.js .collapsed { display: none; }
</style>
<script type="text/javascript">
document.documentElement.className = 'js';
function koggle(elem)
{
	elem = document.getElementById(elem);

	if (elem.style && elem.style['display'])
		var disp = elem.style['display'];
	else if (elem.currentStyle)
		var disp = elem.currentStyle['display'];
	else if (window.getComputedStyle)
		var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
	elem.style.display = disp == 'block' ? 'none' : 'block';
	return false;
}
</script>
<div id="core_error">
	<h1><span class="type"><?=$type ?> [ <?=$code ?> ]:</span> <span class="message"><?=$message ?></span></h1>
	
	<div id="<?=$error_id ?>" class="content">
		<p><span class="file"><?=realpath($file) ?> [ строка : <?=$line ?> ]</span></p>
		<?=Errors::debug_source($file, $line)?>
		<ol class="trace">
		<? foreach ($trace as $i=>$step):?>
			<li>
				<p>
					<span class="file">
						<? if (!empty($step['file']) && realpath($step['file'])): $source_id = $error_id.'source'.$i; ?>
							<a href="#<?=$source_id ?>" onclick="return koggle('<?=$source_id ?>')"><?=$step['file'] ?> [ строка : <?=$step['line'] ?> ]</a>
						<? else: ?>
							{ внутренний вызов PHP }
						<? endif ?>
					</span>
					&raquo;
					<?=$step['function'] ?>(<? if (!empty($step['args'])): $args_id = $error_id.'args'.$i; ?><a href="#<?=$args_id ?>" onclick="return koggle('<?=$args_id ?>')">Аргументы</a><? endif ?>)
				</p>
				<? if (isset($args_id)) : ?>
				<div id="<?=$args_id ?>" class="collapsed">
					<table cellspacing="0">
					<? foreach ($step['args'] as $name=>$arg): ?>
						<tr>
							<td><code><?=$name ?></code></td>
							<td><?=var_dump($arg) ?></td>
						</tr>
					<? endforeach ?>
					</table>
				</div>
				<? endif ?>
				<? if (isset($source_id)): ?>
					<div id="<?=$source_id ?>" class="source collapsed"><?=Errors::debug_source(realpath($step['file']), $step['line'], 3)?></div>
				<? endif ?>
			</li>
			<? unset($args_id, $source_id); ?>
		<? endforeach ?>
		</ol>
	</div>
	
	
	<h2><a href="#<?=$env_id = $error_id.'environment' ?>" onclick="return koggle('<?=$env_id ?>')">Окружающая среда</a></h2>
	<div id="<?=$env_id ?>" class="content collapsed">
		<? $included = get_included_files() ?>
		<h3><a href="#<?=$env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?=$env_id ?>')">Подключеные файлы</a> (<?=count($included) ?>)</h3>
		<div id="<?=$env_id ?>" class="collapsed">
			<table cellspacing="0">
				<? foreach ($included as $file): ?>
				<tr>
					<td><code><?=$file?></code></td>
					<td><code><?=round(filesize($file)/1024, 2)?> Кб</code></td>
				</tr>
				<? endforeach ?>
			</table>
		</div>
		<? $included = get_loaded_extensions() ?>
		<h3><a href="#<?=$env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?=$env_id ?>')">Загруженные расширения</a> (<?=count($included) ?>)</h3>
		<div id="<?=$env_id ?>" class="collapsed">
			<table cellspacing="0">
				<? foreach ($included as $file): ?>
				<tr>
					<td><code><?=$file?></code></td>
				</tr>
				<? endforeach ?>
			</table>
		</div>
		<? foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		<? if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?=$env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?=$env_id ?>')">$<?=$var ?></a></h3>
		<div id="<?=$env_id ?>" class="collapsed">
			<table cellspacing="0">
				<? foreach ($GLOBALS[$var] as $key => $value): ?>
				<tr>
					<td><code><?=$key?></code></td>
					<td><pre><?=var_dump($value)?></pre></td>
				</tr>
				<? endforeach ?>
			</table>
		</div>
		<? endforeach ?>
	</div>
</div>
</body>
</html>