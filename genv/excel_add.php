<?php
/*
* 74cms 微招聘
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
define('IN_QISHI', true);
$alias="QS_leven_list";
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once("lib.php");
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
require_once 'Classes/PHPExcel/Reader/Excel5.php';



//以上三步加载phpExcel的类
//$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
$file="qs_job_field.csv";//指定excel文件



require('spreadsheet-reader/php-excel-reader/excel_reader2.php');

require('spreadsheet-reader/SpreadsheetReader.php');


$reader = new SpreadsheetReader($file);
// error_reporting(E_ALL ^ E_NOTICE);
$data=array();
$cols = array();//存储字段信息；

foreach ($reader as $item)
{
    $data[]=$item;
}

dump($data);

exit;
$objReader = new PHPExcel_Reader_Excel5(); //use excel2007



$objReader = new PHPExcel_Reader_Excel5(); //use excel2007
$PHPExcel = $objReader->load($file_path); //指定的文件

 $currentSheet = $PHPExcel->getSheet(0);//当前活动sheet
$allColumn = $currentSheet->getHighestColumn();//excel最高列值
PHPExcel_Cell::columnIndexFromString($allColumn);//excel文件总列数
$allRow = $currentSheet->getHighestRow();//excel总行数,这里要注意空白行的影响
$inMonth = $currentSheet->getCellByColumnAndRow(0,2);//这里用数字代表行列 eg,0,2代表第二行第一列 //可以用for循环读取单元格的值,构造我们需要的数组
$inMonth = PHPExcel_Style_NumberFormat::toFormattedString($inMonth->getCalculatedValue(), 'YYYY-MM');//将读取的时间值转换为我们需要的格式
//eg:
//一般第一行是表头,我们可以用数组来存储该值
//$KeyArr = array('month','name','sex','age');//同时作二维数组下标值
//后面的行数可以用for嵌套循环读取

$cols=array();
for ($j = 65; $j > 32; $j--) {//列循环 65为A对应ASCII码 32是根据excel表列数,根据第一列是A[65]计算而得
    $selcolumn = ord('A') - $j;//列数

    $value=Leven::utf8_gbk(trim($currentSheet->getCellByColumnAndRow($selcolumn, 1)->getValue()));

    if($value){

        $colinfo=\ORM::for_table(table('relation_title'))->where_equal("name", $value)->find_one()->as_array();

        if($colinfo){
            $cols[$selcolumn]=$colinfo;
        }

    }


}


for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {//行循环 && $allRow 行数
    $obj = \ORM::for_table(table('resume_temp'))->create();
    for ($j = 65; $j > 32; $j--) {//列循环 65为A对应ASCII码 32是根据excel表列数,根据第一列是A[65]计算而得
        $selcolumn = ord('A') - $j;//列数
        $col=$cols[$selcolumn]["value"];
       // dump($col);
        $regfunc=$cols[$selcolumn]["regfunc"]==""?"trim":$cols[$selcolumn]["regfunc"];
        if($col){
            $colvalue=Leven::utf8_gbk($currentSheet->getCellByColumnAndRow($selcolumn, $currentRow)->getValue());

            $obj[$col]=call_user_func_array(array("Leven",$regfunc  ), array($colvalue));
            dump( $obj[$col]);

        }
    }
    $obj->save();

}


exit;


?>