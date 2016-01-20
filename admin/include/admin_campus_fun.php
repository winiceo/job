<?php
 /*
 * 74cms 管理中心 广告广利函数
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
//获取合作院校列表
function get_cooperate_campus_list($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('cooperate_campus').$get_sql." order BY addtime DESC,id DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
		//统计注册毕业生数
		$sql = "SELECT COUNT(distinct(pid)) AS num FROM ".table('resume_education')." as e INNER JOIN ".table('resume')." as r on r.id=e.pid WHERE e.school='".trim($row['campusname']). "'  AND r.display=1 ";
		$graduate_num = $db->get_total($sql);
		$row['graduate'] = intval($graduate_num);
		$row_arr[] = $row;
	}
	return $row_arr;
}
//获取院校信息(单个)
function get_campus_one($val)
{
	global $db;
	$sql = "select * from ".table('cooperate_campus')." where id=".intval($val). " LIMIT 1";
	$arr=$db->getone($sql);
	return $arr;
}
//获取院校风采图片
function get_campus_img($val)
{
	global $db;
	$sql = "select * from ".table('cooperate_campus_img')." where campus_id=".intval($val). " ";
	$arr=$db->getall($sql);
	return $arr;
}
//统计院校风采图片总数
function count_campus_img($campus_id)
{
	global $db;
	$sql = "SELECT COUNT(*) AS num FROM ".table('cooperate_campus_img')." WHERE campus_id=".intval($campus_id). " ";
	$num=$db->get_total($sql);
	return $num;
}
//删除院校风采图片
function del_campus_img($id,$campus_id)
{
	global $db;
	if (!$db->query("Delete from ".table('cooperate_campus_img')." WHERE id = {$id} AND campus_id = {$campus_id}")) return false;
	return true;
}
//删除院校
function del_campus($id)
{
	global $db;
	$return=0;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('cooperate_campus')." WHERE id IN (".$sqlin.") ")) return false;
		$return=$return+$db->affected_rows();
		//删除风采图片
		if (!$db->query("Delete from ".table('cooperate_campus_img')." WHERE campus_id IN ({$sqlin})")) return false;
		//填写管理员日志
		write_log("后台成功删除合作院校", $_SESSION['admin_name'],3);
		return $return;
	}
	else
	{
		return false;
	}
}

?>