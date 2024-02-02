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
$ipgeolocationIo_tokens = [
    'aaaaaabbbbbbbccccccc',
]; // Пробуем получить провайдера (ipgeolocation.io, 30k запросов в месяц / 1k в день). Можно прописать ключи от разных аккаунтов
$allow_cors = false; // Разрешаем/запрещаем CORS
$detect_isp = false; // Пытаться просто отыскать имя провайдера, без блокировки, для передачи на фронт и в метрику? false если нет или если isp ищем на самом фронте
$block_by_isp = false; // Блокировки по isp. Нужно добавить запись из block_by_isp.php в .htaccess корня сайта!
$block_by_org = true; // То же, но проверка по по организации. Бывает, что одна организация использует разные ISP
$pass_blockCheck_result = false; // false / string. Передавать в Метрику параметр, показывающий, попал ли ip в блэклист.
$log_before_send = true; // Записывать отправляемые параметры в лог
$optimize_log = true; // Записать заголовок лога один раз и больше не дописывать. Лучше всего работает, когда известен порядок ячеек и он неизменен; в этом случае можно вручную один раз прописать в логе заголовки и не трогать
$log_static_header = 'date_time;ip;ip_subnet;ip_subnet_2;city;region;country;forwardedfor_ip;forwardedfor_ip_subnet;forwardedfor_ip_subnet_2;forwardedfor_ip_city;forwardedfor_ip_region;forwardedfor_ip_country;ip_isp;ip_org;blacklisted';
$logfile = 'log.txt';
$log_headers = true;

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
$headers = getallheaders();
$remote_ip = $_SERVER['REMOTE_ADDR'];
$cf_connecting_ip = isset($headers['Cf-Connecting-Ip']) ? $headers['Cf-Connecting-Ip'] : '';
$client_ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '';
$forwardedfor_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

/**
 * Запишем заголовки для дебага
 */
if ($log_headers){
    $logdata = print_r($headers,true);
    date_default_timezone_set( 'Europe/Moscow' );
    $date = date('d/m/Y H:i:s', time());
    file_put_contents('log__headers.txt', $date.': '.PHP_EOL.$remote_ip.PHP_EOL.$logdata.PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Обработка прямого IP
 */
$response['ip'] = $remote_ip;

// подсеть
$remote_ip_splitted = explode('.', $remote_ip);
if (@$remote_ip_splitted[1] && @$remote_ip_splitted[2]){
    $response['ip_subnet'] = $remote_ip_splitted[0] . '.' . $remote_ip_splitted[1] . '.' . $remote_ip_splitted[2] . '.xx';
    $response['ip_subnet_2'] = $remote_ip_splitted[0] . '.' . $remote_ip_splitted[1] . '.xx.xx';
}
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
 * Обработка Cf-Connecting-Ip
 */
if ($cf_connecting_ip){
    $response['cf_conn_ip'] = $cf_connecting_ip;

    $cf_connecting_ip_splitted = explode('.', $cf_connecting_ip);
    if (@$cf_connecting_ip_splitted[1] && $cf_connecting_ip_splitted[2]){
        $response['cf_connecting_ip_subnet'] = $cf_connecting_ip_splitted[0] . '.' . $cf_connecting_ip_splitted[1] . '.' . $cf_connecting_ip_splitted[2] . '.xx';
        $response['cf_connecting_ip_subnet_2'] = $cf_connecting_ip_splitted[0] . '.' . $cf_connecting_ip_splitted[1] . '.xx.xx';
    }
    unset($cf_connecting_ip_splitted);

    $cf_connecting_ip_info = $SxGeo->getCityFull($cf_connecting_ip);
    if (isset($cf_connecting_ip_info['city']) && isset($cf_connecting_ip_info['city']['name_en'])){
        $response['cf_connecting_ip_city'] = $cf_connecting_ip_info['city']['name_en'];
    }
    if (isset($cf_connecting_ip_info['region']) && isset($cf_connecting_ip_info['region']['name_en'])){
        $response['cf_connecting_ip_region'] = $cf_connecting_ip_info['region']['name_en'];
    }
    if (isset($cf_connecting_ip_info['country']) && isset($cf_connecting_ip_info['country']['name_en'])){
        $response['cf_connecting_ip_country'] = $cf_connecting_ip_info['country']['name_en'];
    }
    unset($cf_connecting_ip_info);
    unset($cf_connecting_ip);
}

/**
 * Обработка HTTP_CLIENT_IP
 */
if ($client_ip){
    $response['client_ip'] = $client_ip;

    $client_ip_splitted = explode('.', $client_ip);
    if (@$client_ip_splitted[1] && $client_ip_splitted[2]){
        $response['client_ip_subnet'] = $client_ip_splitted[0] . '.' . $client_ip_splitted[1] . '.' . $client_ip_splitted[2] . '.xx';
        $response['client_ip_subnet_2'] = $client_ip_splitted[0] . '.' . $client_ip_splitted[1] . '.xx.xx';
    }
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

    $forwardedfor_ip_splitted = explode('.', (explode(',', $forwardedfor_ip)[0]));
    if (@$forwardedfor_ip_splitted[1] && $forwardedfor_ip_splitted[2]){
        $response['forwardedfor_ip_subnet'] = $forwardedfor_ip_splitted[0] . '.' . $forwardedfor_ip_splitted[1] . '.' . $forwardedfor_ip_splitted[2] . '.xx';
        $response['forwardedfor_ip_subnet_2'] = $forwardedfor_ip_splitted[0] . '.' . $forwardedfor_ip_splitted[1] . '.xx.xx';
    }
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
if (!empty($ipgeolocationIo_tokens) && ($detect_isp || $block_by_isp || $block_by_org)){

    // получаем случайный ключ
    $ipgeolocationIo_token_index = rand(0, count($ipgeolocationIo_tokens)-1);
    $ipgeolocationIo_token = $ipgeolocationIo_tokens[$ipgeolocationIo_token_index];
    unset($ipgeolocationIo_tokens[$ipgeolocationIo_token_index]);

    // TODO если токены закончились - выбрать другой ключ

    // делаем запрос
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

    // блокировка по организации
    if ($block_by_org && function_exists('check_block_by_org') && isset($ipgeolocationIo_response['organization']) && $ipgeolocationIo_response['organization']){
        $block_by_org_result = check_block_by_org($ipgeolocationIo_response['organization'], $sypex_path);

        if ($pass_blockCheck_result){
            if ($block_by_org_result){
                $response[$pass_blockCheck_result] = 'yes';
            }
        }
    }
}

/**
 * Пишем в лог
 */
if ($log_before_send){
    $log_size = filesize($logfile);

    // проверяем, заполнять ли заголовок лога. Если оптимизируем лог, и размер лога 0, пропишем первую ячейку
    // если оптимизируем лог
    if ($optimize_log){
        
        // если лог не пустой
        if ($log_size) {
            $str_heading = false;
        } else {
            // лог пуст
            // решаем, вставить в заголовок статичное начало или будем дополнять
            if ($log_static_header){
                $str_heading = $log_static_header;
            } else {
                $str_heading = 'date_time';
            }
        }

    } else {
        $str_heading = 'date_time';
    }

    // фиксируем дату
    date_default_timezone_set( 'Europe/Moscow' );
    $str_content = date('d/m/Y H:i:s', time());
    
    // перебор ответа с фиксацией в лог
    foreach ($response as $key => $value){
        // если заголовок лога не пуст, и не равен статическому заголовку, допишем в него название перебираемой ячейки
        if ($str_heading && ($str_heading !== $log_static_header)){
            $str_heading .= ';'.$key;
        }

        $str_content .= ';'.$value;
    }

    $logdata = ($str_heading ? $str_heading.PHP_EOL : '') . $str_content;
    file_put_contents($logfile, $logdata.PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Отдаём ответ
 */
echo json_encode($response);
