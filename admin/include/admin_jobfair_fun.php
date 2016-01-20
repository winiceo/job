<?php
 /*
 * 74cms 招聘会
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
function get_jobfair($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('jobfair')." ".$sql." ".$limit);
	while($row = $db->fetch_array($result))
	{
	$row_arr[] = $row;
	}
	return $row_arr;
}
function get_jobfair_display()
{
	global $db;
	$info = $db->getall("SELECT * FROM ".table('jobfair')." WHERE display=1  order BY `order` DESC,id DESC");
	return $info;
}
function get_jobfair_audit()
{
	global $db;
	$info = $db->getall("SELECT * FROM ".table('jobfair')." WHERE display=1 AND predetermined_status=1 AND holddate_start>".time()." order BY `order` DESC,id DESC");
	return $info;
}
function del_jobfair($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('jobfair')." WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		write_log("删除招聘会id为".$sqlin."的招聘会,共删除".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
function get_jobfair_exhibitors($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('jobfair_exhibitors')." ".$sql." ".$limit);
	while($row = $db->fetch_array($result))
	{
	if ($row['uid']>0 && $row['company_id']>0)
	{
	$row['company_url']=url_rewrite('QS_companyshow',array('id'=>$row['company_id']));
	}
	else
	{
	$row['company_url']="";
	}
	$row['jobfair_url']=url_rewrite('QS_jobfairshow',array('id'=>$row['jobfairid']));
	$row_arr[] = $row;
	}
	return $row_arr;
}
function del_exhibitors($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('jobfair_exhibitors')." WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		write_log("删除id为".$sqlin."的参会企业,共删除".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
function edit_exhibitors_audit($id,$audit)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$return=0;
	$sqlin=implode(",",$id);	
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('jobfair_exhibitors')." SET audit='".intval($audit)."'  WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		write_log("将id为".$sqlin."的参会企业的审核状态为".intval($audit).",共删除".$return."行", $_SESSION['admin_name'],3);
	}
	return $return;
}
/*
	专场招聘会
*/
// 列表
function get_jobfair_section($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('jobfair_section')." ".$sql." ".$limit);
	while($row = $db->fetch_array($result))
	{
	if($row['is_singleness']==1)
	{
		$row['jobfair_url']=url_rewrite('QS_industry_jobfair',array('id'=>$row['id']),true);
	}
	else
	{
		$row['jobfair_url']=url_rewrite('QS_industrys_jobfair',array('id'=>$row['id']),true);
	}
	$row_arr[] = $row;
	}
	return $row_arr;
}
// 专场招聘会 详细
function get_jobfair_section_one($id)
{
	global $db;
	$id= intval($id);
	$row = $db->getone("SELECT * from ".table("jobfair_section")." where id=$id limit 1");
	if($row['is_singleness']==1)
	{
		$row['jobfair_url']=url_rewrite('QS_industry_jobfair',array('id'=>$row['id']),true);
	}
	else
	{
		$row['jobfair_url']=url_rewrite('QS_industrys_jobfair',array('id'=>$row['id']),true);
	}
	return $row;
}
// 专场招聘会 参会企业列表
function get_jobfair_section_company($offset, $perpage, $sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT j.*,c.companyname,c.trade_cn FROM ".table('jobfair_section_company')." as j ".$sql." ".$limit);
	while($row = $db->fetch_array($result))
	{
	$row_arr[] = $row;
	}
	return $row_arr;
}
//删除专场招聘会 
function del_jobfair_section($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('jobfair_section')." WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		$db->query("Delete from ".table('jobfair_section_company')." WHERE jobfair_id IN (".$sqlin.")");	
	}
	return $return;
}
// 删除专场招聘会 参会企业
function del_jobfair_section_company($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('jobfair_section_company')." WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
	}
	return $return;
}
?>