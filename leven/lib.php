<?php
/**
 * Created by PhpStorm.
 * User: leven
 * Date: 16/1/13
 * Time: ÉÏÎç9:17
 */

require_once(dirname(__FILE__).'/../vendor/autoload.php');

require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);

require  'idiorm.php';

ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES gbk'));

\ORM::configure(sprintf('mysql:host=%s;dbname=%s', $dbhost,$dbname));

\ORM::configure('username', $dbuser);
\ORM::configure('password', $dbpass);
\ORM::configure('return_result_sets', true);
\ORM::configure('logging', true);

\ORM::configure('logger', function ($log_string) use ($app) {

    $logpath="/data/logs/";

    error_log($log_string . "\n", 3, $logpath . '74cms.log');
    //echo $logpath . 'police_sql.log';
});

date_default_timezone_set("prc");
function dump($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {

            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . " : " . print_r($var, true);
        }
    } else {

        ob_start();
        var_dump($var);
        $output = ob_get_clean();


         if (!extension_loaded('xdebug')) {

            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);

            $output = '<pre>'
                . $label
                . htmlspecialchars($output,ENT_QUOTES,"GB2312")
                . '</pre>';
       }
    }
    if ($echo) {

        echo($output);
        return null;
    } else {
        return $output;
    }
}



class Leven{
    public static function trim($str){
        return trim($str);
    }
    public static function db(){
        global $db;
        return $db;
    }
    public static function  birthdate($value){
        $value=str_replace("/","-",$value);
        $is_date=strtotime($value)?strtotime($value):false;
        if($is_date){
            return date("Y",$is_date);
        }
        return date("Y")-intval($value);
    }
    public static function utf8_gbk($value){
       return iconv("UTF-8", "GB2312//IGNORE",$value);

    }
}
