<?php
// Пример работы с классом SxGeo v2.2
header('Content-type: text/plain; charset=utf8');

// Подключаем SxGeo.php класс
include("SxGeo.php");
// Создаем объект
// Первый параметр - имя файла с базой (используется оригинальная бинарная база SxGeo.dat)
// Второй параметр - режим работы: 
//     SXGEO_FILE   (работа с файлом базы, режим по умолчанию); 
//     SXGEO_BATCH (пакетная обработка, увеличивает скорость при обработке множества IP за раз)
//     SXGEO_MEMORY (кэширование БД в памяти, еще увеличивает скорость пакетной обработки, но требует больше памяти)
$SxGeo = new SxGeo('SxGeoCity.dat');
//$SxGeo = new SxGeo('SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY); // Самый производительный режим, если нужно обработать много IP за раз

$ip = $_SERVER['REMOTE_ADDR'];

var_export($SxGeo->getCityFull($ip)); // Вся информация о городе
var_export($SxGeo->get($ip));         // Краткая информация о городе или код страны (если используется база SxGeo Country)
var_export($SxGeo->about());          // Информация о базе данных
