<?php
// Обновление файла базы данных Sypex Geo
// Настройки
$url = 'https://sypexgeo.net/files/SxGeoCity_utf8.zip';  // Путь к скачиваемому файлу
$dat_file_dir = './'; // Каталог в который сохранять dat-файл
$last_updated_file = __DIR__ . '/SxGeo.upd'; // Файл в котором хранится дата последнего обновления
define('INFO', false); // Вывод сообщений о работе, true заменить на false после установки в cron

// Конец настроек

set_time_limit(600);
//error_reporting(E_ALL);
header('Content-type: text/plain; charset=utf8');

$t = microtime(1);
chdir(__DIR__);
$types = array(
	//'Country' =>  'SxGeo.dat',
	'City' =>  'SxGeoCity.dat',
	//'Max' =>  'SxGeoMax.dat',
);
// Скачиваем архив
preg_match("/(Country|City|Max)/", pathinfo($url, PATHINFO_BASENAME), $m);
$type = $m[1];
$dat_file = $types[$type];
if (INFO) echo "Скачиваем архив с сервера\n";

$fp = fopen(__DIR__ .'/SxGeoTmp.zip', 'wb');
$ch = curl_init($url);
curl_setopt_array($ch, array(
	CURLOPT_FILE => $fp,
	CURLOPT_HTTPHEADER => file_exists($last_updated_file) ? array("If-Modified-Since: " .file_get_contents($last_updated_file)) : array(),
));
if(!curl_exec($ch)) die ('Ошибка при скачивании архива');
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
fclose($fp);
if ($code == 304) {
	@unlink(__DIR__ . '/SxGeoTmp.zip');
	if (INFO) echo "Архив не обновился, с момента предыдущего скачивания\n";
	exit;
}

if (INFO) echo "Архив с сервера скачан\n";
// Распаковываем архив
$fp = fopen('zip://' . __DIR__ . '/SxGeoTmp.zip#' . $dat_file, 'rb');
$fw = fopen($dat_file, 'wb');
if (!$fp) {
	exit("Не получается открыть\n");
}
if (INFO) echo "Распаковываем архив\n";
stream_copy_to_stream($fp, $fw);
fclose($fp);
fclose($fw);
if(filesize($dat_file) == 0) die ('Ошибка при распаковке архива');
@unlink(__DIR__ . '/SxGeoTmp.zip');
rename(__DIR__ . '/' . $dat_file, $dat_file_dir . $dat_file) or die ('Ошибка при переименовании файла');
file_put_contents($last_updated_file, gmdate('D, d M Y H:i:s') . ' GMT');
if (INFO) echo "Перемещен файл в {$dat_file_dir}{$dat_file}\n";
