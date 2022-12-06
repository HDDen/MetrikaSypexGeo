<?php
header('Content-Type: application/json; charset=utf-8');

// Подключаем SxGeo.php класс
// $sypex_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/../www/madmen-includ/SypexGeo/SxGeo.php'; // если файл лежит поодаль
$sypex_path = 'SxGeo.php';
require_once($sypex_path);

// Разрешаем/запрещаем CORS
$allow_cors = false;


if ($allow_cors){
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
        // whitelist of safe domains
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}

// Поместим сюда ответ
$response = array();

// Создаем объект
// Первый параметр - имя файла с базой (используется оригинальная бинарная база SxGeo.dat)
// Второй параметр - режим работы: 
//     SXGEO_FILE   (работа с файлом базы, режим по умолчанию); 
//     SXGEO_BATCH (пакетная обработка, увеличивает скорость при обработке множества IP за раз)
//     SXGEO_MEMORY (кэширование БД в памяти, еще увеличивает скорость пакетной обработки, но требует больше памяти)
$SxGeo = new SxGeo('SxGeoCity.dat');

// IP
$remote_ip = $_SERVER['REMOTE_ADDR'];
$client_ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '';
$forwardedfor_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

// обработка прямого IP
$response['ip'] = $remote_ip;
$remote_ip_info = $SxGeo->getCityFull($remote_ip);
if (isset($remote_ip_info['city']) && isset($remote_ip_info['city']['name_en'])){
    $response['city'] = $remote_ip_info['city']['name_en'];
}
if (isset($remote_ip_info['region']) && isset($remote_ip_info['region']['name_en'])){
    $response['region'] = $remote_ip_info['region']['name_en'];
}
if (isset($remote_ip_info['country']) && isset($remote_ip_info['country']['name_en'])){
    $response['country'] = $remote_ip_info['country']['name_en'];
}

// обработка HTTP_CLIENT_IP
if ($client_ip){
    $response['client_ip'] = $client_ip;
    $client_ip_info = $SxGeo->getCityFull($client_ip);
    if (isset($client_ip_info['city']) && isset($client_ip_info['city']['name_en'])){
        $response['client_ip_city'] = $client_ip_info['city']['name_en'];
    }
    if (isset($client_ip_info['region']) && isset($client_ip_info['region']['name_en'])){
        $response['client_ip_region'] = $client_ip_info['region']['name_en'];
    }
    if (isset($client_ip_info['country']) && isset($client_ip_info['country']['name_en'])){
        $response['client_ip_country'] = $client_ip_info['country']['name_en'];
    }
}

// обработка HTTP_X_FORWARDED_FOR
if ($forwardedfor_ip){
    $response['forwardedfor_ip'] = $forwardedfor_ip;
    $forwardedfor_ip_info = $SxGeo->getCityFull($forwardedfor_ip);
    if (isset($forwardedfor_ip_info['city']) && isset($forwardedfor_ip_info['city']['name_en'])){
        $response['forwardedfor_ip_city'] = $forwardedfor_ip_info['city']['name_en'];
    }
    if (isset($forwardedfor_ip_info['region']) && isset($forwardedfor_ip_info['region']['name_en'])){
        $response['forwardedfor_ip_region'] = $forwardedfor_ip_info['region']['name_en'];
    }
    if (isset($forwardedfor_ip_info['country']) && isset($forwardedfor_ip_info['country']['name_en'])){
        $response['forwardedfor_ip_country'] = $forwardedfor_ip_info['country']['name_en'];
    }
}

// отдаём ответ
echo json_encode($response);
