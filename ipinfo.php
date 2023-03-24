<?php
header('Content-Type: application/json; charset=utf-8');

// Подключаем SxGeo.php класс
$sypex_path = ''; // инициализация рабочей папки
// $sypex_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/../www/madmen-includ/MetrikaSypexGeo/'; // если папка находится поодаль
require_once($sypex_path . 'SxGeo.php');

/**
 * Доп. модули
 */
include_once($sypex_path . 'block_by_isp.php');

/**
 * Опции
 */
$ipgeolocationIo_token = ''; // Пробуем получить провайдера (ipgeolocation.io, 30k запросов в месяц)
$allow_cors = false; // Разрешаем/запрещаем CORS
$block_by_isp = true; // Блокировки по isp. Нужно добавить запись из block_by_isp.php в .htaccess корня сайта!
$pass_blockCheck_result = false; // false / string. Передавать в Метрику параметр, показывающий, попал ли ip в блэклист.
$log_before_send = true; // Записывать отправляемые параметры в лог

/**
 * Рабочая зона
 */
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
$SxGeo = new SxGeo($sypex_path . 'SxGeoCity.dat');

// IP
$remote_ip = $_SERVER['REMOTE_ADDR'];
$client_ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '';
$forwardedfor_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

/**
 * Обработка прямого IP
 */
$response['ip'] = $remote_ip;

// подсеть
$remote_ip_splitted = explode('.', $remote_ip);
$response['ip_subnet'] = $remote_ip_splitted[0] . '.' . $remote_ip_splitted[1] . '.' . $remote_ip_splitted[2] . '.xx';
$response['ip_subnet_2'] = $remote_ip_splitted[0] . '.' . $remote_ip_splitted[1] . '.xx.xx';
unset($remote_ip_splitted);

// инфа об IP
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
unset($remote_ip_info);
unset($remote_ip);

/**
 * Обработка HTTP_CLIENT_IP
 */
if ($client_ip){
    $response['client_ip'] = $client_ip;

    $client_ip_splitted = explode('.', $client_ip);
    $response['client_ip_subnet'] = $client_ip_splitted[0] . '.' . $client_ip_splitted[1] . '.' . $client_ip_splitted[2] . '.xx';
    $response['client_ip_subnet_2'] = $client_ip_splitted[0] . '.' . $client_ip_splitted[1] . '.xx.xx';
    unset($client_ip_splitted);

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
    unset($client_ip_info);
    unset($client_ip);
}

/**
 * Обработка HTTP_X_FORWARDED_FOR
 */
if ($forwardedfor_ip){
    $response['forwardedfor_ip'] = $forwardedfor_ip;

    $forwardedfor_ip_splitted = explode('.', $forwardedfor_ip);
    $response['forwardedfor_ip_subnet'] = $forwardedfor_ip_splitted[0] . '.' . $forwardedfor_ip_splitted[1] . '.' . $forwardedfor_ip_splitted[2] . '.xx';
    $response['forwardedfor_ip_subnet_2'] = $forwardedfor_ip_splitted[0] . '.' . $forwardedfor_ip_splitted[1] . '.xx.xx';
    unset($forwardedfor_ip_splitted);

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
    unset($forwardedfor_ip_info);
    unset($forwardedfor_ip);
}

/**
 * Получаем провайдера
 * https://api.ipgeolocation.io/ipgeo?apiKey=API_KEY&ip=8.8.8.8
 */
if ($ipgeolocationIo_token){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.ipgeolocation.io/ipgeo?apiKey='.$ipgeolocationIo_token.'&ip='.$_SERVER['REMOTE_ADDR'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $ipgeolocationIo_response = curl_exec($curl);
    curl_close($curl);

    // достаём isp
    $ipgeolocationIo_response = json_decode($ipgeolocationIo_response, true);
    if (isset($ipgeolocationIo_response['isp'])){
        $response['ip_isp'] = $ipgeolocationIo_response['isp'];
    }
    if (isset($ipgeolocationIo_response['organization'])){
        $response['ip_org'] = $ipgeolocationIo_response['organization'];
    }
    if (isset($ipgeolocationIo_response['asn'])){
        $response['ip_asn'] = $ipgeolocationIo_response['asn'];
    }

    // блокировка по isp
    if ($block_by_isp && function_exists('check_block_by_isp') && isset($ipgeolocationIo_response['isp']) && $ipgeolocationIo_response['isp']){
        $block_by_isp_result = check_block_by_isp($ipgeolocationIo_response['isp'], $sypex_path);

        if ($pass_blockCheck_result){
            if ($block_by_isp_result){
                $response[$pass_blockCheck_result] = 'yes';
            }
        }
    }
}

/**
 * Пишем в лог
 */
if ($log_before_send){
    $str = ';';
    foreach ($response as $key => $value){
        $str .= $value . ';';
    }
    $logdata = $str;
    $logfile = 'log.txt';
    date_default_timezone_set( 'Europe/Moscow' );
    $date = date('d/m/Y H:i:s', time());
    file_put_contents($logfile, $date.': '.$logdata.PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Отдаём ответ
 */
echo json_encode($response);
