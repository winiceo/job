<?php
/**
 * Created by PhpStorm.
 * User: leven
 * Date: 16/1/13
 * Time: ионГ10:18
 */
define('IN_QISHI', true);
$alias="QS_leven_list";
require_once(dirname(__FILE__).'/../include/common.inc.php');

require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
require_once 'Classes/PHPExcel/Reader/Excel5.php';

require_once("lib.php");

$str="23╧гм╥";
dump( strtotime("1999-12") );
echo Leven::old_year($str);
