<?php
 /*
 * 74cms 投诉与建议相关函数
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
function get_feedback_list($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT ".$offset.','.$perpage;
	$sql = "select * from ".table('feedback')." ".$get_sql.$limit;
	$val=$db->getall($sql);
	return $val;
}
function get_feedback_one($id)
{
	global $db;
	$sql = "select * from ".table('feedback')." where id=".intval($id);
	$val=$db->getone($sql);
	return $val;
}
function del_feedback($id)
{
	global $db;
	$return=0;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
	if (!$db->query("Delete from ".table('feedback')." WHERE id IN (".$sqlin.")")) return false;
	$return=$return+$db->affected_rows();
	}
	return $return;
}
function get_report_list($offset,$perpage,$get_sql= '',$type)
{
	global $db;
	$limit=" LIMIT ".$offset.','.$perpage;
	if($type==1){
		$result = $db->query("SELECT r.*,m.username FROM ".table('report')." AS r ".$get_sql.$limit);
		while($row = $db->fetch_array($result))
		{
		$row['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$row['jobs_id']));
		$row_arr[] = $row;
		}
	}else{
		$result = $db->query("SELECT r.*,m.username FROM ".table('report_resume')." AS r ".$get_sql.$limit);
		while($row = $db->fetch_array($result))
		{
		$row['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$row['resume_id']));
		$row_arr[] = $row;
		}
	}
	
	return $row_arr;
}
//反馈审核
function report_audit($id,$audit,$type,$rid)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$return=0;
	$sqlin=implode(",",$id);	
	$sqlrin=implode(",",$rid);
	$rule=get_cache('points_rule');
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{	
		if($type==1) {
			if (!$db->query("update  ".table('report')." SET audit='".intval($audit)."'  WHERE id IN (".$sqlin.")")) return false;
		} else {
			if (!$db->query("update  ".table('report_resume')." SET audit='".intval($audit)."'  WHERE id IN (".$sqlin.")")) return false;
		}
		$return=$return+$db->affected_rows();
		//发送站内信
		if($type==1) {
			$result = $db->query("SELECT * FROM ".table('report')." WHERE id IN ({$sqlin})");
		} else {
			$result = $db->query("SELECT * FROM ".table('report_resume')." WHERE id IN ({$sqlin})");
		}
		while($list = $db->fetch_array($result))
		{
			$user_info=get_user($list['uid']);
			$timestring = date("Y年m月d日",time());
			// $type->1  个人举报职位
			if($type==1) 
			{
				// 职位信息 企业会员信息
				$jobsurl=url_rewrite('QS_jobsshow',array('id'=>$list['jobs_id']));
				$jobsinfo = $db->getone("SELECT * FROM ".table('jobs')." WHERE id=".intval($list['jobs_id'])." UNION ALL SELECT * FROM ".table('jobs_tmp')." WHERE id=".$list['jobs_id']);
				if(!$jobsinfo)
				{
					continue;
				}
				$user_info_com=get_user($jobsinfo['uid']);
				// 若属实
				if($audit==2)
				{
					//个人积分操作
					report_deal($user_info['uid'],$rule['report_jobs']['type'],$rule['report_jobs']['value']);
					$user_points=get_user_points($user_info['uid']);
					$operator=$rule['report_jobs']['type']=="1"?"+":"-";
					write_memberslog($user_info['uid'],2,9001,$user_info_com['username']," 举报职位审核通过，{$_CFG['points_byname']}({$operator}{$rule['report_jobs']['value']})，(剩余:{$user_points})",2,7003,"举报职位审核通过","{$operator}{$rule['report_jobs']['value']}","{$user_points}");

					$msg_p = "，奖励".$rule['report_jobs']['value']."积分，感谢您对".$_CFG['site_name']."的支持！";
					$setsqlarr_p['message']="您于".$timestring."举报企业【".$jobsinfo['companyname']."】发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】,经平台核实情况属实".$msg_p;
					$setsqlarr_p['msgtype']=1;
					$setsqlarr_p['msgtouid']=$user_info['uid'];
					$setsqlarr_p['msgtoname']=$user_info['username'];
					$setsqlarr_p['dateline']=time();
					$setsqlarr_p['replytime']=time();
					$setsqlarr_p['new']=1;
					$db->inserttable(table('pms'),$setsqlarr_p);

					//扣除企业积分操作
					report_deal($jobsinfo['uid'],$rule['company_jobs_report_points']['type'],$rule['company_jobs_report_points']['value']);
					$user_points=get_user_points($jobsinfo['uid']);
					$operator=$rule['company_jobs_report_points']['type']=="1"?"+":"-";
					write_memberslog($jobsinfo['uid'],1,9001,$user_info_com['username']," 企业发布职位被举报，{$_CFG['points_byname']}({$operator}{$rule['company_jobs_report_points']['value']})，(剩余:{$user_points})",1,1016,"企业发布职位被举报","{$operator}{$rule['company_jobs_report_points']['value']}","{$user_points}");

					$msg_c = "，扣除".$rule['company_jobs_report_points']['value']."积分，如再有此类情况发生将作封号处理！";
					$setsqlarr_c['message']="您发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】于".$timestring."被举报，经平台核实情况属实".$msg_c;
					$setsqlarr_c['msgtype']=1;
					$setsqlarr_c['msgtouid']=$user_info_com['uid'];
					$setsqlarr_c['msgtoname']=$user_info_com['username'];
					$setsqlarr_c['dateline']=time();
					$setsqlarr_c['replytime']=time();
					$setsqlarr_c['new']=1;
					$db->inserttable(table('pms'),$setsqlarr_c);
				}
				else
				{
					$setsqlarr['message']="您于".$timestring."举报企业【".$jobsinfo['companyname']."】发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】,经平台核实情况不属实";
					$setsqlarr['msgtype']=1;
					$setsqlarr['msgtouid']=$user_info['uid'];
					$setsqlarr['msgtoname']=$user_info['username'];
					$setsqlarr['dateline']=time();
					$setsqlarr['replytime']=time();
					$setsqlarr['new']=1;
					$db->inserttable(table('pms'),$setsqlarr);
				}
			} 
			// 企业举报简历
			else 
			{
				// 简历信息  会员信息
				$resumeurl=url_rewrite('QS_resumeshow',array('id'=>$list['resume_id']));
				$resumeinfo = $db->getone("SELECT * FROM ".table('resume')." WHERE id=".intval($list['resume_id']));
				if(!$resumeinfo)
				{
					continue;
				}
				$user_info_per=get_user($resumeinfo['uid']);
				// 若属实
				if ($audit==2)
				{
					// 企业举报简历 获得积分
					report_deal($user_info['uid'],$rule['company_report_resume_points']['type'],$rule['company_report_resume_points']['value']);
					$user_points=get_user_points($user_info['uid']);
					$operator=$rule['company_report_resume_points']['type']=="1"?"+":"-";
					write_memberslog($user_info['uid'],1,9001,$user_info['username']," 企业举报简历，{$_CFG['points_byname']}({$operator}{$rule['company_report_resume_points']['value']})，(剩余:{$user_points})",1,7003,"企业举报简历","{$operator}{$rule['company_report_resume_points']['value']}","{$user_points}");
					$msg = "，奖励".$rule['company_report_resume_points']['value']."积分，感谢您对".$_CFG['site_name']."的支持！";
					$setsqlarr_c['message']="您于".$timestring."举报的简历：【<a href=\"{$resumeurl}\" target=\"_blank\">{$list['title']}</a>】，经平台核实情况属实".$msg;
					$setsqlarr_c['msgtype']=1;
					$setsqlarr_c['msgtouid']=$user_info['uid'];
					$setsqlarr_c['msgtoname']=$user_info['username'];
					$setsqlarr_c['dateline']=time();
					$setsqlarr_c['replytime']=time();
					$setsqlarr_c['new']=1;
					$db->inserttable(table('pms'),$setsqlarr_c);

					//个人扣除积分操作
					report_deal($user_info_per['uid'],$rule['resume_report']['type'],$rule['resume_report']['value']);
					$user_points=get_user_points($user_info_per['uid']);
					$operator=$rule['resume_report']['type']=="1"?"+":"-";
					write_memberslog($user_info_per['uid'],2,9001,$user_info_per['username']," 简历被举报审核通过，{$_CFG['points_byname']}({$operator}{$rule['resume_report']['value']})，(剩余:{$user_points})",2,7003,"简历被举报审核通过","{$operator}{$rule['resume_report']['value']}","{$user_points}");

					$msg_p = "，扣除".$rule['resume_report']['value']."积分，如再有此类情况发生将作封号处理！";
					$setsqlarr_p['message']="您发布的简历【".$resumeinfo['title']."】于".$timestring."被举报，经平台核实情况属实".$msg_p;
					$setsqlarr_p['msgtype']=1;
					$setsqlarr_p['msgtouid']=$user_info_per['uid'];
					$setsqlarr_p['msgtoname']=$user_info_per['username'];
					$setsqlarr_p['dateline']=time();
					$setsqlarr_p['replytime']=time();
					$setsqlarr_p['new']=1;
					$db->inserttable(table('pms'),$setsqlarr_p);
				}
				else
				{
					$setsqlarr['message']="您于".$timestring."举报的简历：【<a href=\"{$resumeurl}\" target=\"_blank\">{$list['title']}</a>】，经平台核实情况不属实";
					$setsqlarr['msgtype']=1;
					$setsqlarr['msgtouid']=$user_info['uid'];
					$setsqlarr['msgtoname']=$user_info['username'];
					$setsqlarr['dateline']=time();
					$setsqlarr['replytime']=time();
					$setsqlarr['new']=1;
					$db->inserttable(table('pms'),$setsqlarr);
				}
			}
		 }
	}
	return $return;
}
function get_user($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	return $db->getone($sql);
}
function del_report($id)
{
	global $db;
	$return=0;
	if (!is_array($id))$del_id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
	if (!$db->query("Delete from ".table('report')." WHERE id IN (".$sqlin.")")) return false;
	$return=$return+$db->affected_rows();
	}
	return $return;
}
function del_report_resume($id)
{
	global $db;
	$return=0;
	if (!is_array($id))$del_id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
	if (!$db->query("Delete from ".table('report_resume')." WHERE id IN (".$sqlin.")")) return false;
	$return=$return+$db->affected_rows();
	}
	return $return;
}
function report_deal($uid,$i_type=1,$points=0)
{
		global $db,$timestamp;
		$points_val=get_user_points($uid);
		if ($i_type==1)
		{
		$points_val=$points_val+$points;
		}
		if ($i_type==2)
		{
		$points_val=$points_val-$points;
		$points_val=$points_val<0?0:$points_val;
		}
		$sql = "UPDATE ".table('members_points')." SET points= '{$points_val}' WHERE uid='{$uid}'  LIMIT 1 ";
		return $db->query($sql);
}
function get_user_points($uid)
{
	global $db;
	$sql = "select * from ".table('members_points')." where uid = ".intval($uid)."  LIMIT 1 ";
	$points=$db->getone($sql);
	return $points['points'];
}
?>