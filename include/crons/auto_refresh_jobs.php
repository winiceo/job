<?php
 /*
 * 74cms 计划任务 自动刷新职位
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
	global $_CFG;
	$row=$db->getall("SELECT j.id,j.uid FROM ".table('jobs')." as j inner join ".table('jobs_appointment_refresh')." as r on j.id=r.jobs_id WHERE r.appointment_time_available>0 and j.auto_refresh=1 ");
	foreach ($row as $key => $value)
	{
		$id_str.=",".$value['id'];
		$uid_str.=",".$value['uid'];
	}
	refresh_jobs($id_str,$uid_str);
	// 清除 过期的自动刷新记录
	$row_del=$db->getall("select * from ".table('jobs_appointment_refresh')." where appointment_time_available=0");
	foreach ($row_del as $key => $value)
	{
		$db->query("Delete from ".table('jobs_appointment_refresh')." WHERE id=$value[id] and appointment_time_available=0 ");
		$db->query("update ".table("jobs")." set auto_refresh=0 where id=$value[jobs_id] ");
	}
	function refresh_jobs($id,$uid)
	{
		global $db,$_CFG;
		$id=ltrim($id,",");
		$uid=ltrim($uid,",");
		$time=time();
		$deadline=strtotime("".intval($_CFG['company_add_days'])." day");
		if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$id) && preg_match("/^(\d{1,10},)*(\d{1,10})$/",$uid))
		{
			if (!$db->query("update  ".table('company_profile')."  SET refreshtime='{$time}' WHERE uid IN ({$uid}) ")) return false;
			if (!$db->query("update  ".table('jobs')."  SET refreshtime='{$time}',deadline='{$deadline}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_tmp')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_hot')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_key')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_rtime')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_scale')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_stickrtime')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_search_wage')."  SET refreshtime='{$time}' WHERE id IN ({$id}) ")) return false;
			if (!$db->query("update  ".table('jobs_appointment_refresh')."  SET appointment_time_available=appointment_time_available-1,execute_time=$time WHERE jobs_id IN ({$id}) ")) return false;
			return true;
		}
		return false;
	}

	//更新任务时间表
	if ($crons['weekday']>=0)
	{
	$weekday=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$nextrun=strtotime("Next ".$weekday[$crons['weekday']]);
	}
	elseif ($crons['day']>0)
	{
	$nextrun=strtotime('+1 months'); 
	$nextrun=mktime(0,0,0,date("m",$nextrun),$crons['day'],date("Y",$nextrun));
	}
	else
	{
	$nextrun=time();
	}
	if ($crons['hour']>=0)
	{
	$nextrun=strtotime('+1 days',$nextrun); 
	$nextrun=mktime($crons['hour'],0,0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
	}
	if (intval($crons['minute'])>0)
	{
	$nextrun=strtotime('+1 hours',$nextrun); 
	$nextrun=mktime(date("H",$nextrun),$crons['minute'],0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
	}
	$setsqlarr['nextrun']=$nextrun;
	$setsqlarr['lastrun']=time();
	$db->updatetable(table('crons'), $setsqlarr," cronid ='".intval($crons['cronid'])."'");
?>