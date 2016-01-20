<?php
/**
 * Created by PhpStorm.
 * User: leven
 * Date: 16/1/13
 * Time: 下午1:47
 */


define('IN_QISHI', true);
$alias="QS_leven_list";
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once("lib.php");
function get_resume_list($offset,$perpage,$get_sql= '')
{
    global $db;
    $limit=" LIMIT ".$offset.','.$perpage;
    $result = $db->query($get_sql.$limit);

    while($row = $db->fetch_array($result))
    {
        $row['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$row['id']));
        $row_arr[] = $row;
    }
    return $row_arr;
}
require_once(QISHI_ROOT_PATH.'include/page.class.php');

$perpage=10;
$wheresql=" ";
$total_sql="SELECT COUNT(*) AS num FROM ".table('resume_temp').$wheresql;
$total_val=$db->get_total($total_sql);
$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
$currenpage=$page->nowindex;
$offset=($currenpage-1)*$perpage;

$getsql="SELECT * FROM ".table('resume_temp').$wheresql;

$resumelist = get_resume_list($offset,$perpage,$getsql);
//echo "<pre>";
//var_dump($resumelist);
//echo "</pre>";
//exit;
$smarty->assign('pageheader',"简历列表");
$smarty->assign('resumelist',$resumelist);
$smarty->assign('page',$page->show(3));
$smarty->assign('total_val',$total_val);
$smarty->display('admin_personal_resume.htm');