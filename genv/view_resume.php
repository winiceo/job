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
$id=$_GET["id"];
//$resume=  \ORM::for_table(table('resume_temp'))->find_one($id)->as_array();
//获取简历基本信息
function get_resume_basic($id)
{
    global $db;
    $id=intval($id);
    $uid=intval($uid);
    $info=$db->getone("select * from ".table('resume_temp')." where id='{$id}'    LIMIT 1 ");

    if (empty($info))
    {
        return false;
    }
    else
    {
        $info['age']=date("Y")-$info['birthdate'];
        $info['number']="N".str_pad($info['id'],7,"0",STR_PAD_LEFT);
        $info['lastname']=$info['fullname'];
        return $info;
    }
}
$resume=get_resume_basic($id);
$smarty->assign('resume',$resume);
 $smarty->display('admin_personal_resume_show.htm');