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
if(!defined('IN_QISHI'))
{
 die('Access Denied!');
}
function get_simple_list($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT {$offset},{$perpage} ";
	$result = $db->query("SELECT * FROM ".table('simple')." {$sql} {$limit}");
	while($row = $db->fetch_array($result))
	{
	$row_arr[] = $row;
	}
	return $row_arr;
}
//微简历列表
function get_simple_resume_list($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT {$offset},{$perpage} ";
	$result = $db->query("SELECT * FROM ".table('simple_resume')." {$sql} {$limit}");
	while($row = $db->fetch_array($result))
	{
	$row_arr[] = $row;
	}
	return $row_arr;
}
function simple_del($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('simple_resume')." WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		//填写管理员日志
		write_log("后台删除id为".$sqlin."的微简历 , 共删除".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
function simple_refresh($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('simple_resume')." SET refreshtime='".time()."'  WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		//填写管理员日志
		write_log("后台刷新id为".$sqlin."的微简历 , 共刷新".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
//微简历审核
function simple_resume_audit($id,$audit)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$return=0;
	$sqlin=implode(",",$id);	
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('simple_resume')." SET audit='".intval($audit)."'  WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		//填写管理员日志
		write_log("后台审核id为".$sqlin."的微简历 , 共审核".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
//设置微简历置顶
function simple_resume_hot($id,$is_hot)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$return=0;
	$sqlin=implode(",",$id);	
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('simple_resume')." SET is_hot='".intval($is_hot)."'  WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		//填写管理员日志
		write_log("后台置顶id为".$sqlin."的微简历 , 共置顶".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
//增加微简历意向职位
function add_simple_resume_jobs($pid,$str)
{
	global $db;
	$db->query("Delete from ".table('simple_resume_jobs')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode("-",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $a)
		{
		$a=explode(".",$a);
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['category']=intval($a[0]);
		$setsqlarr['subclass']=intval($a[1]);
			if (!$db->inserttable(table('simple_resume_jobs'),$setsqlarr))return false;
		}
		//填写管理员日志
		write_log("后台增加微简历意向职位", $_SESSION['admin_name'],3);
	}
	return true;
}
//增加微简历意向行业
function add_simple_resume_trade($pid,$str)
{
	global $db;
	$db->query("Delete from ".table('simple_resume_trade')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode(",",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $a)
		{
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['trade']=intval($a);
			if (!$db->inserttable(table('simple_resume_trade'),$setsqlarr))return false;
		}
		//填写管理员日志
		write_log("后台增加微简历意向行业", $_SESSION['admin_name'],3);
	}
	return true;
}
?>