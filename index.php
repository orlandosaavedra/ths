<?php
/**
 * Main file
 */

ini_set('php-gtk.codepage', 'utf-8');
date_default_timezone_set('America/Santiago');
ini_set('memory_limit', '512M');
set_include_path(__DIR__ . '/lib' . PATH_SEPARATOR . __DIR__ . '/lib/fpdf17');
error_reporting(E_ALL);

define('__APP__', 'THS');
define('__WD__', __DIR__);
define('THS_LOGO_FILENAME', __DIR__ . '/img/logo.png');
function __autoload($class){
    $back = debug_backtrace();
    if (key_exists('file', $back[0])){
        print('[INFO]: Loading class '.$class.' at '.$back[0]['file'].':'.$back[0]['line'].PHP_EOL);
    }else{
        print('[WARN]: Loading class '.$class.' at unknown'.PHP_EOL);
    }
    require_once $class . '.class.php';
}

$config = parse_ini_file(__DIR__ . '/etc/config.ini');

THSModel::$host = $config['host'];
THSModel::$dbname = $config['database'];
THSModel::$username = $config['username'];
THSModel::$password = $config['password'];


$main = new Main();