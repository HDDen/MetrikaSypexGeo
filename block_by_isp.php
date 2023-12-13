<?php

if (!function_exists('mkpath')){
    function mkpath($path) {
        if(@mkdir($path) or file_exists($path)) return true;
        return (mkpath(dirname($path)) and mkdir($path));
    }
}

/**
 * Based on https://serverfault.com/questions/605534/deny-from-htaccess-with-banned-ip-list-from-stopforumspam-com-not-working
 *
 * Need to add to .htaccess
RewriteEngine On
RewriteBase /
RewriteCond %{REMOTE_ADDR} ^([0-9]{1,3})\.
RewriteCond %{DOCUMENT_ROOT}/madmen-includ/MetrikaSypexGeo/firewall/%1/%{REMOTE_ADDR} -f
RewriteRule . - [F]
## блок по подсети 1.2.3.* - вариант на файлах (активен)
# RewriteCond %{REMOTE_ADDR} ^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})
# RewriteCond %{DOCUMENT_ROOT}/madmen-includ/MetrikaSypexGeo/firewall/%1/%1\.%2\.%3 -f
# RewriteRule . - [F]
## блок по подсети 1.2.3.* - вариант на папках (не активен)
# RewriteCond %{REMOTE_ADDR} ^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})
# RewriteCond %{DOCUMENT_ROOT}/madmen-includ/MetrikaSypexGeo/firewall/%1/%1\.%2/%1\.%2\.%3 -d
# RewriteRule . - [F]
## блок конкретных диапазонов вручную
# RewriteCond %{REMOTE_ADDR} ^1\.2\.3\. [OR]
# RewriteCond %{REMOTE_ADDR} ^1\.2\.
# RewriteRule . - [F]
 */
/**
 * Принимает вычисленное имя провайдера, 
 * если он отсутствует в блоклисте - вернёт false,
 * если есть - создаст метку для блокировки и вернёт true
 */
function check_block_by_isp($isp, $workdir = ''){
    $debug = false;
    $logfile = 'log__check_block_by_isp.txt';
    date_default_timezone_set( 'Europe/Moscow' );

    $debug ? file_put_contents($logfile, 
    date(PHP_EOL.'d/m/Y H:i:s', time()) . ': Зашли, $isp = '.$isp.', ip = '.$_SERVER['REMOTE_ADDR']
    .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

    // define abspath
    if ($workdir === ''){
        $workdir = rtrim(getcwd(), '/');
    }
    $workdir_module = rtrim(dirname(__FILE__), '/');

    // load block rules
    $rules = '';
    if (file_exists($workdir_module . '/block_by_isp.txt')){
        $rules = file_get_contents($workdir_module . '/block_by_isp.txt');
        $rules = preg_split('/\r\n|\r|\n/', $rules);

        $debug ? file_put_contents($logfile, 
        'Правила:'.PHP_EOL.print_r($rules, true)
        .PHP_EOL, FILE_APPEND | LOCK_EX) : '';
    } else {

        $debug ? file_put_contents($logfile, 
        $workdir_module . '/block_by_isp.txt не существует, уходим'
        .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

        return false;
    }

    // check our isp
    if (!in_array($isp, $rules)){
        
        $debug ? file_put_contents($logfile, 
        $isp. ' не в списке запрещённых, уходим'
        .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

        return false;
    }

    // form path
    $ip_blocks = explode('.', $_SERVER['REMOTE_ADDR']);

    $blockfile_dir = $workdir . '/firewall/' . $ip_blocks[0] . '/';
    $blockfile = $_SERVER['REMOTE_ADDR'];

    // variant based on folder structure. deprecated
    // $blockfile_dir = $workdir . '/firewall/' . $ip_blocks[0] . '/' . $ip_blocks[0].'.'.$ip_blocks[1] . '/' . $ip_blocks[0].'.'.$ip_blocks[1].'.'.$ip_blocks[2] . '/';
    // $blockfile = $_SERVER['REMOTE_ADDR'];

    // create file
    if (!file_exists($blockfile_dir . $blockfile)){
        mkpath($blockfile_dir);
        $creating_blockfile_result = file_put_contents($blockfile_dir . $blockfile, $isp);

        $debug ? file_put_contents($logfile, 
        'Результат создания блокировочного файла '.$blockfile_dir . $blockfile . ' = '.$creating_blockfile_result
        .PHP_EOL, FILE_APPEND | LOCK_EX) : '';
    }

    // also create for subnets
    $blockfile = $ip_blocks[0].'.'.$ip_blocks[1];
    if (!file_exists($blockfile_dir . $blockfile)){
        file_put_contents($blockfile_dir . $blockfile, $isp);
    }

    $blockfile = $ip_blocks[0].'.'.$ip_blocks[1].'.'.$ip_blocks[2];
    if (!file_exists($blockfile_dir . $blockfile)){
        file_put_contents($blockfile_dir . $blockfile, $isp);
    }
    // created full array, end

    $debug ? file_put_contents($logfile, 
    'Отработали, уходим'
    .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

    return true;
}

/**
 * Прокси для проверки по организации
 * В будущем - копия функции, только с проверкой по организации
 */
function check_block_by_org($isp, $workdir = ''){
    $debug = true;
    $logfile = 'log__check_block_by_isp.txt'; // так как это прокси, лог используем общий
    date_default_timezone_set( 'Europe/Moscow' );

    $debug ? file_put_contents($logfile, 
    date(PHP_EOL.'d/m/Y H:i:s', time()) . ': check_block_by_org(): Зашли, $isp = '.$isp.', ip = '.$_SERVER['REMOTE_ADDR']
    .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

    $result = check_block_by_isp($isp, $workdir);

    $debug ? file_put_contents($logfile, 
    'check_block_by_org(): Результат = '.$result.', уходим'
    .PHP_EOL, FILE_APPEND | LOCK_EX) : '';

    return $result;
}