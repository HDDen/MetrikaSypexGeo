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
## блок по подсети 1.2.3.*
# RewriteCond %{REMOTE_ADDR} ^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})
# RewriteCond %{DOCUMENT_ROOT}/madmen-includ/MetrikaSypexGeo/firewall/%1/%1\.%2/%1\.%2\.%3 -d
# RewriteRule . - [F]
## блок конкретных диапазонов вручную
# RewriteCond %{REMOTE_ADDR} ^1\.2\.3\. [OR]
# RewriteCond %{REMOTE_ADDR} ^1\.2\.
# RewriteRule . - [F]
 */
function check_block_by_isp($isp, $workdir = ''){
    // define abspath
    if ($workdir === ''){
        $workdir = rtrim(getcwd(), '/');
    }
    $workdir_module = rtrim(dirname(__FILE__), '/');

    // load block rules
    $rules = '';
    if (file_exists($workdir_module . '/block_by_isp.txt')){
        $rules = file_get_contents($workdir_module . '/block_by_isp.txt');
        $rules = explode(PHP_EOL, $rules);
    } else {
        return false;
    }

    // check our isp
    if (!in_array($isp, $rules)) return false;

    // form path
    $ip_blocks = explode('.', $_SERVER['REMOTE_ADDR']);

    $blockfile_dir = $workdir . '/firewall/' . $ip_blocks[0] . '/' . $ip_blocks[0].'.'.$ip_blocks[1] . '/' . $ip_blocks[0].'.'.$ip_blocks[1].'.'.$ip_blocks[2] . '/';
    $blockfile = $_SERVER['REMOTE_ADDR'];

    // create file
    if (!file_exists($blockfile_dir . $blockfile)){
        mkpath($blockfile_dir);
        file_put_contents($blockfile_dir . $blockfile, '');
    }

    return true;
}
