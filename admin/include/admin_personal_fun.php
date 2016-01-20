<?php
 /*
 * 74cms 管理中心 个人用户相关函数
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
  function get_user_info($uid)
{
	global $db;
	$sql = "select * from ".table('members')." where uid = ".intval($uid)." LIMIT 1";
	return $db->getone($sql);
}
 //检查该简历是否投递过该职位
 function check_jobs_apply($jobs_id,$resume_id,$p_uid)
{
	global $db;
	$sql = "select did from ".table('personal_jobs_apply')." WHERE personal_uid = '".intval($p_uid)."' AND jobs_id='".intval($jobs_id)."'  AND resume_id='".intval($resume_id)."'";
	return $db->getall($sql);
}
 //获取职位信息列表
function get_jobs($offset,$perpage,$get_sql= '')
{
	global $db,$timestamp;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query($get_sql.$limit);
	while($row = $db->fetch_array($result))
	{
	$row['jobs_name']=cut_str($row['jobs_name'],12,0,"...");
	if (!empty($row['highlight']))
	{
	$row['jobs_name']="<span style=\"color:{$row['highlight']}\">{$row['jobs_name']}</span>";
	}
	$row['companyname']=cut_str($row['companyname'],18,0,"...");
	$row['company_url']=url_rewrite('QS_companyshow',array('id'=>$row['company_id']));
	$row['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$row['id']));
	$get_resume_nolook = $db->getone("select count(*) from ".table('personal_jobs_apply')." where personal_look=1 and jobs_id=".$row['id']);
	$get_resume_all = $db->getone("select count(*) from ".table('personal_jobs_apply')." where jobs_id=".$row['id']);
	$row['get_resume'] = "( ".$get_resume_nolook['count(*)']." / ".$get_resume_all['count(*)']." )";
	$row_arr[] = $row;
	}
	return $row_arr;
}
 //******************************简历部分**********************************
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
function del_resume($id)
{
	global $db;
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
	if (!$db->query("Delete from ".table('resume')." WHERE id IN ({$sqlin})")) return false;
	$return=$return+$db->affected_rows();
	if (!$db->query("Delete from ".table('resume_jobs')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_district')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_trade')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_tag')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_education')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_training')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_work')." WHERE pid IN ({$sqlin}) ")) return false;
	if (!$db->query("Delete from ".table('resume_search_rtime')." WHERE id IN ({$sqlin})")) return false;
	if (!$db->query("Delete from ".table('resume_search_key')." WHERE id IN ({$sqlin})")) return false;
	if (!$db->query("Delete from ".table('view_resume')." WHERE resumeid IN ({$sqlin})")) return false;
	$db->query("delete from ".table('resume_entrust')." where id IN (".$sqlin.")");
	//填写管理员日志
	write_log("删除简历id为".$id."的简历 , 共删除".$return."行", $_SESSION['admin_name'],3);
	return $return;
	}
	return $return;
}
function del_resume_for_uid($uid)
{
	global $db;
	if (!is_array($uid)) $uid=array($uid);
	$sqlin=implode(",",$uid);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		$result = $db->query("SELECT id FROM ".table('resume')." WHERE uid IN (".$sqlin.")");
		while($row = $db->fetch_array($result))
		{
		$rid[]=$row['id'];
		}
		if (empty($rid))
		{
		return true;
		}
		else
		{
		return del_resume($rid);
		}		
	}
}
function edit_resume_audit($id,$audit,$reason,$pms_notice)
{
	global $db,$_CFG;
	$audit=intval($audit);
	if (!is_array($id))  $id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('resume')." SET audit='{$audit}'  WHERE id IN ({$sqlin}) ")) return false;
		if (!$db->query("update  ".table('resume_search_key')." SET audit='{$audit}'  WHERE id IN ({$sqlin}) ")) return false;
		if (!$db->query("update  ".table('resume_search_rtime')." SET audit='{$audit}'  WHERE id IN ({$sqlin}) ")) return false;
		foreach ($id as $key => $value) {
			set_resume_entrust($value);
		}
		// distribution_resume($id);
		//填写管理员日志
		write_log("修改简历id为".$sqlin."的审核状态为".$audit, $_SESSION['admin_name'],3);
		//发送站内信
		if ($pms_notice=='1')
		{
				$result = $db->query("SELECT  fullname,title,uid  FROM ".table('resume')." WHERE id IN ({$sqlin})");
				$reason=$reason==''?'原因：未知':'原因：'.$reason;
				while($list = $db->fetch_array($result))
				{
					$user_info=get_user($list['uid']);
					$setsqlarr['message']=$audit=='1'?"您创建的简历：{$list['title']},真实姓名：{$list['fullname']},成功通过网站管理员审核！":"您创建的简历：{$list['title']},真实姓名：{$list['fullname']},未通过网站管理员审核,{$reason}";
					$setsqlarr['msgtype']=1;
					$setsqlarr['msgtouid']=$user_info['uid'];
					$setsqlarr['msgtoname']=$user_info['username'];
					$setsqlarr['dateline']=time();
					$setsqlarr['replytime']=time();
					$setsqlarr['new']=1;
					$db->inserttable(table('pms'),$setsqlarr);
				 }
		}
		//审核未通过增加原因
		if($audit=='3'){
			foreach($id as $list){
				$auditsqlarr['resume_id']=$list;
				$auditsqlarr['reason']=$reason;
				$auditsqlarr['addtime']=time();
				$db->inserttable(table('audit_reason'),$auditsqlarr);
			}
		}
			
			//发送邮件
				$mailconfig=get_cache('mailconfig');//获取邮件规则
				$sms=get_cache('sms_config');
				if ($audit=="1" && $mailconfig['set_resumeallow']=="1")//审核通过
				{
						$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
						while($list = $db->fetch_array($result))
						{
						dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid=".$list['uid']."&key=".asyn_userkey($list['uid'])."&act=set_resumeallow");
						}
				}
				if ($audit=="3" && $mailconfig['set_resumenotallow']=="1")//审核未通过
				{
					$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
						while($list = $db->fetch_array($result))
						{
						dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid=".$list['uid']."&key=".asyn_userkey($list['uid'])."&act=set_resumenotallow");
						}
				}
				//sms		
				if ($audit=="1" && $sms['open']=="1" && $sms['set_resumeallow']=="1" )
				{
					$mobilearray = array();
					$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
					while($list = $db->fetch_array($result))
					{
						$user_info=get_user($list['uid']);
						if ($user_info['mobile_audit']=="1" && !is_array($list['uid'],$mobilearray))
						{
							//将同一会员放到数组中 并且确保该会员唯一的
							$mobilearray[] = $list['uid'];
						}
					}
					foreach ($mobilearray as $key => $value) 
					{
						dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_sms.php?uid=".$value."&key=".asyn_userkey($value)."&act=set_resumeallow");
					}
				}
				//sms
				if ($audit=="3" && $sms['open']=="1" && $sms['set_resumenotallow']=="1" )//认证未通过
				{
					$mobilearray = array();
					$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
					while($list = $db->fetch_array($result))
					{
						$user_info=get_user($list['uid']);
						if ($user_info['mobile_audit']=="1" && !is_array($list['uid'],$mobilearray))
						{
							//将同一会员放到数组中 并且确保该会员唯一的
							$mobilearray[] = $list['uid'];
						}
					}
					foreach ($mobilearray as $key => $value) 
					{
						dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_sms.php?uid=".$value."&key=".asyn_userkey($value)."&act=set_resumenotallow");
					}
				}
				//微信通知
				$weixinconfig=get_cache('weixin_config');
				if($audit=="1")
				{
					$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
					while($list = $db->fetch_array($result))
					{
						set_resumeallow($list['uid'],$weixinconfig['set_resumeallow'],$list['title'],"通过",$reason);
					}
				}
				if($audit=="3")
				{
					$result = $db->query("SELECT * FROM ".table('resume')." WHERE id IN ({$sqlin}) ");
					while($list = $db->fetch_array($result))
					{
						set_resumeallow($list['uid'],$weixinconfig['set_resumenotallow'],$list['title'],"未通过",$reason);
					}
				}
	return true;
	}
	return false;
}
//修改照片审核状态
function edit_resume_photoaudit($id,$audit,$is_del_img)
{
	global $db,$_CFG;
	$audit=intval($audit);
	$is_del_img=intval($is_del_img);
	if (!is_array($id)) $id=array($id);
	if (!empty($id))
	{
		foreach($id as $i)
		{
			$i=intval($i);
			$tb1=$db->getone("select photo_img,photo_audit,photo_display from ".table('resume')." WHERE id='{$i}' LIMIT  1");
			if (!empty($tb1))
			{
				if($is_del_img==1 && $audit==3)
				{
					$photo=0;
					@unlink(QISHI_ROOT_PATH.'data/photo/'.$tb1['photo_img']);
					@unlink(QISHI_ROOT_PATH.'data/photo/thumb/'.$tb1['photo_img']);
					$db->query("update  ".table('resume')." SET photo_img='',photo_audit='{$audit}',photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
					$db->query("update  ".table('resume_search_rtime')." SET photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
					$db->query("update  ".table('resume_search_key')." SET photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
				}
				else
				{
					if ($tb1['photo_img'] && $audit=="1" && $tb1['photo_display']=="1")
					{
					$photo=1;
					}
					else
					{
					$photo=0;
					}	
					$db->query("update  ".table('resume')." SET photo_audit='{$audit}',photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
					$db->query("update  ".table('resume_search_rtime')." SET photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
					$db->query("update  ".table('resume_search_key')." SET photo='{$photo}' WHERE id='{$i}' LIMIT 1 ");
				}
			}
		}
		//填写管理员日志
		write_log("修改简历id为".$id."的照片审核状态为".$audit, $_SESSION['admin_name'],3);
	}
	return true;
}
//修改人才等级
function edit_resume_talent($id,$talent)
{
	global $db;
	$talent=intval($talent);
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('resume')." SET talent={$talent}  WHERE id IN ({$sqlin})")) return false;
		if (!$db->query("update  ".table('resume_search_rtime')." SET talent={$talent}  WHERE id IN ({$sqlin})")) return false;
		if (!$db->query("update  ".table('resume_search_key')." SET talent={$talent}  WHERE id IN ({$sqlin})")) return false;
		//填写管理员日志
		write_log("修改简历id为".$sqlin."的人才等级为".$talent, $_SESSION['admin_name'],3);
		return true;
	}
	return false;
}
//从UID获取所有简历
function get_resume_uid($uid)
{
	global $db;
	$uid=intval($uid);
	$result = $db->query("select * FROM ".table('resume')." where uid='{$uid}'");
	while($row = $db->fetch_array($result))
	{ 
	$row['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$row['id']));
	$row_arr[] = $row;
	}
	return $row_arr;	
}
function refresh_resume($id)
{
	global $db;
	$return=0;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("update  ".table('resume')." SET refreshtime='".time()."'  WHERE id IN (".$sqlin.")")) return false;
		$return=$return+$db->affected_rows();
		if (!$db->query("update  ".table('resume_search_rtime')." SET refreshtime='".time()."'  WHERE id IN (".$sqlin.")")) return false;
		if (!$db->query("update  ".table('resume_search_key')." SET refreshtime='".time()."'  WHERE id IN (".$sqlin.")")) return false;
	}
	//填写管理员日志
	write_log("刷新简历id为".$sqlin."的简历 , 共刷新".$return."行", $_SESSION['admin_name'],3);
	return $return;
}
//**************************个人会员列表
function get_member_list($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;	
	$result = $db->query("SELECT * FROM ".table('members')." as m ".$get_sql.$limit);
		while($row = $db->fetch_array($result))
		{
			$address = $db->getone("select log_address,log_id,log_uid from ".table("members_log")." where log_type = '1000' and log_uid = ".$row['uid']." order by log_id asc limit 1");
			$row['ipAddress'] = $address['log_address'];
			$row_arr[] = $row;
		}
	return $row_arr;
}
function delete_member($uid)
{
	global $db;
	if (!is_array($uid)) $uid=array($uid);
	$sqlin=implode(",",$uid);
		if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
		{
					if(defined('UC_API'))
					{
						include_once(QISHI_ROOT_PATH.'uc_client/client.php');
						foreach($uid as $tuid)
						{
						$userinfo=get_user($tuid);
						$uc_user=uc_get_user($userinfo['username']);
						$uc_uid_arr[]=$uc_user[0];
						}
						uc_user_delete($uc_uid_arr);
					} 
		if (!$db->query("Delete from ".table('members')." WHERE uid IN (".$sqlin.")")) return false;
		if (!$db->query("Delete from ".table('members_info')." WHERE uid IN (".$sqlin.")")) return false;
		//填写管理员日志
		write_log("删除uid为".$sqlin."的会员", $_SESSION['admin_name'],3);
		return true;
		}
	return false;
}
function get_member_one($memberuid)
{
	global $db;
	$sql = "select * from ".table('members')." where uid=".intval($memberuid)." LIMIT 1";
	$val=$db->getone($sql);
	return $val;
}
function get_user($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	return $db->getone($sql);
}
//获取简历的审核日志
function get_resumeaudit_one($resume_id){
	global $db;
	$sql = "select * from ".table('audit_reason')."  WHERE resume_id='".intval($resume_id)."' ORDER BY id DESC";
	return $db->getall($sql);
}
//获取简历基本信息
function get_resume_basic($uid,$id)
{
	global $db;
	$id=intval($id);
	$uid=intval($uid);
	$info=$db->getone("select * from ".table('resume')." where id='{$id}'  AND uid='{$uid}' LIMIT 1 ");
	
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
//获取教育经历列表
function get_resume_education($uid,$pid)
{
	global $db;
	if (intval($uid)!=$uid) return false;
	$sql = "SELECT * FROM ".table('resume_education')." WHERE  pid='".intval($pid)."' AND uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取：工作经历
function get_resume_work($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where pid='".$pid."' AND uid=".intval($uid)."" ;
	return $db->getall($sql);
}
//获取：培训经历列表
function get_resume_training($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where pid='".intval($pid)."' AND  uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取意向职位
function get_resume_jobs($pid)
{
	global $db;
	$pid=intval($pid);
	$sql = "select * from ".table('resume_jobs')." where pid='{$pid}'  LIMIT 20" ;
	return $db->getall($sql);
}
function reasonaudit_del($id)
{
	global $db;
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('audit_reason')." WHERE id IN ({$sqlin})")) return false;
	//填写管理员日志
	write_log("后台删除日志id为".$sqlin."的日志", $_SESSION['admin_name'],3);
	return $db->affected_rows();
}
function export_resume($yid){
	global $db;
	$yid_str = implode(",", $yid);
	$oederbysql=" order BY refreshtime desc ";
	$wheresql = empty($wheresql)?" id in ({$yid_str}) ":" and id in ({$yid_str}) ";
	if (!empty($wheresql))
	{
	$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
	}
	$data = $db->getall("select * from ".table('resume').$wheresql);
	
	if(!empty($data)){
		$result = $data;
	}
	if(!empty($result)){
		foreach ($result as $key => $value) {
			$arr[$key]['num'] = $key;
			$arr[$key]['title'] = $value['title'];
			$arr[$key]['fullname'] = $value['fullname'];
			$arr[$key]['sex_cn'] = $value['sex_cn'];
			$arr[$key]['birthdate'] = $value['birthdate'];
			$arr[$key]['height'] = $value['height'];
			$arr[$key]['householdaddress'] = $value['householdaddress'];
			$arr[$key]['marriage_cn'] = $value['marriage_cn'];
			$arr[$key]['experience_cn'] = $value['experience_cn'];
			$arr[$key]['education_cn'] = $value['education_cn'];
			$arr[$key]['natrue_cn'] = $value['natrue_cn'];
			$arr[$key]['trade_cn'] = $value['trade_cn'];
			$arr[$key]['district_cn'] = $value['district_cn'];
			$arr[$key]['wage_cn'] = $value['wage_cn'];
			$arr[$key]['tag']=$value['tag_cn'];
			/*$arr[$key]['tag']=preg_replace("/\d+/", '',$value['tag']);
			$arr[$key]['tag']=preg_replace('/\,/','',$arr[$key]['tag']);
			$arr[$key]['tag']=preg_replace('/\|/','&nbsp;&nbsp;&nbsp;',$arr[$key]['tag']);*/
			$arr[$key]['school'] = "";
			$school = $db->getall("select * from ".table('resume_education')." where pid=".$value['id']." order by id desc");
			if(!empty($school)){
				foreach ($school as $key1 => $value1) {
					if(intval($value1['todate']) == 1)
					{
						$endtime = "至今";
					}
					else
					{
						$endtime = $value1['endyear']."年".$value1['endmonth']."月";
					}
					$arr[$key]['school'] .= $value1['startyear']."年".$value1['startmonth']."月"."-".$endtime."就读于".$value1['school'].",所学专业：".$value1['speciality'].",学历：".$value1['education_cn'].";&nbsp;";
				}
			}
			$arr[$key]['work'] = "";
			$work = $db->getall("select * from ".table('resume_work')." where pid=".$value['id']." order by id desc");
			if(!empty($work)){
				foreach ($work as $key1 => $value1) {
					if(intval($value1['todate']) == 1)
					{
						$endtime = "至今";
					}
					else
					{
						$endtime = $value1['endyear']."年".$value1['endmonth']."月";
					}
					$arr[$key]['work'] .= $value1['startyear']."年".$value1['startmonth']."月"."-".$endtime."就职于".$value1['companyname'].",任职：".$value1['jobs'].";&nbsp;";
				}
			}
			$arr[$key]['train'] = "";
			$train = $db->getall("select * from ".table('resume_training')." where pid=".$value['id']." order by id desc");
			if(!empty($train)){
				foreach ($train as $key1 => $value1) {
					if(intval($value1['todate']) == 1)
					{
						$endtime = "至今";
					}
					else
					{
						$endtime = $value1['endyear']."年".$value1['endmonth']."月";
					}
					$arr[$key]['train'] .= $value1['startyear']."年".$value1['startmonth']."月"."-".$endtime."在".$value1['agency']."培训".$value1['course']."课程;&nbsp;";
				}
			}
			$arr[$key]['telephone'] = $value['telephone'];
			$arr[$key]['email'] = $value['email'];
			$arr[$key]['qq'] = $value['qq'];
			$arr[$key]['address'] = $value['address'];
			$arr[$key]['website'] = $value['website'];
			$arr[$key]['recentjobs'] = $value['recentjobs'];
			$arr[$key]['intention_jobs'] = $value['intention_jobs'];
			$arr[$key]['specialty'] = str_replace("\n","",str_replace("\r", "", $value['specialty']));
			$arr[$key]['addtime'] = date("Y-m-d",$value['addtime']);
			$arr[$key]['refreshtime'] = date("Y-m-d",$value['refreshtime']);
			$arr[$key]['talent'] = $value['talent']==1?"普通":"高级";
			$arr[$key]['complete_percent'] = $value['complete_percent'];
		}
		$top_str = "序号\t简历名称\t姓名\t性别\t出生年月\t身高\t户籍所在地\t婚姻状况\t工作经验\t学历\t意向职位性质\t意向行业\t意向工作地区\t意向薪资\t标签\t教育经历\t工作经历\t培训经历\t手机\t邮箱\tQQ\t地址\t个人主页\t最近从事工作\t意向职位\t技能特长\t添加时间\t刷新时间\t简历等级\t完整度\t\n";
		create_excel($top_str,$arr);
		//填写管理员日志
		write_log("导出简历id为".$yid_str."的简历", $_SESSION['admin_name'],3);
		return true;
	}else{
		return false;
	}
	
}
function set_resume_entrust($resume_id){
	global $db;
	$resume = $db->getone("select audit,uid,fullname,addtime,entrust from ".table('resume')." where id=".$resume_id);
	if($resume["audit"]=="1" && $resume["entrust"]=="1"){
		$has = $db->getone("select 1 from ".table('resume_entrust')." where id=".$resume_id);
		if(!$has){
			$setsqlarr['id'] = $resume_id;
			$setsqlarr['uid'] = $resume['uid'];
			$setsqlarr['fullname'] = $resume['fullname'];
			$setsqlarr['resume_addtime'] = $resume['addtime'];
			$db->inserttable(table('resume_entrust'),$setsqlarr);
			$db->updatetable(table('resume'),array("entrust"=>"0")," id=".$resume_id." ");
		}
	}
	else
	{
		$db->query("delete from ".table('resume_entrust')." where id=".$resume_id);
	}
	return true;
}
//修改用户状态
function set_user_status($status,$uid)
{
	global $db;
	$status=intval($status);
	$uid=intval($uid);
	if (!$db->query("UPDATE ".table('members')." SET status= {$status} WHERE uid={$uid} LIMIT 1")) return false;
	//填写管理员日志
	write_log("后台将uid为".$uid."会员的用户状态修改为".$status, $_SESSION['admin_name'],3);
	// if (!$db->query("UPDATE ".table('company_profile')." SET user_status= {$status} WHERE uid={$uid} ")) return false;
	// if (!$db->query("UPDATE ".table('jobs')." SET user_status= {$status} WHERE uid={$uid} ")) return false;
	// if (!$db->query("UPDATE ".table('jobs_tmp')." SET user_status= {$status} WHERE uid={$uid} ")) return false; 
	return true;
}
//导入简历时的注册会员
function import_user_register($username,$password,$member_type=0,$email,$mobile,$uc_reg=true)
{
	global $db,$timestamp,$_CFG,$online_ip,$QS_pwdhash;
	$member_type=intval($member_type);
	$ck_username=get_user_inusername_import($username);
	$ck_email=get_user_inemail_import($email);
	$ck_mobile=get_user_inmobile_import($mobile);
	if ($member_type==0) 
	{
	return -1;
	}
	elseif (!empty($ck_username))
	{
	return $ck_username['uid'];
	}
	elseif ($email!=""&&!empty($ck_email))
	{
	return $ck_email['uid'];
	}
	elseif ($mobile!=""&&!empty($ck_mobile))
	{
	return $ck_mobile['uid'];
	}
	$pwd_hash=randstr_import();
	$password_hash=md5(md5($password).$pwd_hash.$QS_pwdhash);
	$setsqlarr['username']=$username;
	$setsqlarr['password']=$password_hash;
	$setsqlarr['pwd_hash']=$pwd_hash;
	$setsqlarr['email']=$email;
	$setsqlarr['mobile']=$mobile;
	$setsqlarr['utype']=intval($member_type);
	$setsqlarr['reg_time']=$timestamp;
	$setsqlarr['reg_ip']=$online_ip;
	$insert_id=$db->inserttable(table('members'),$setsqlarr,true);
	if(defined('UC_API') && $uc_reg)
	{
		include_once(QISHI_ROOT_PATH.'uc_client/client.php');
		$uc_reg_uid=uc_user_register($username,$password,$email);
	}
	return $insert_id;
}
function get_user_inemail_import($email)
{
	global $db;
	return $db->getone("select * from ".table('members')." where email = '{$email}' LIMIT 1");
}
function get_user_inusername_import($username)
{
	global $db;
	$sql = "select * from ".table('members')." where username = '{$username}' LIMIT 1";
	return $db->getone($sql);
}
function get_user_inid($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	return $db->getone($sql);
}
function get_user_inmobile_import($mobile)
{
	global $db;
	$sql = "select * from ".table('members')." where mobile = '{$mobile}' LIMIT 1";
	return $db->getone($sql);
}
//获取随机字符串
function randstr_import($length=6)
{
$hash='';
$chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz@#!~?:-='; 
$max=strlen($chars)-1;   
mt_srand((double)microtime()*1000000);   
for($i=0;$i<$length;$i++)   {   
$hash.=$chars[mt_rand(0,$max)];   
}   
return $hash;   
}
//匹配要求学历
function resume_education($str=NULL)
{
	global $db,$locoyspider;
	$sql = "select c_id,c_name from ".table('category')." where c_alias='QS_education'  and c_id=".intval($locoyspider['jobs_education'])." LIMIT 1";
	$info=$db->getone($sql);
	$default=array("id"=>$info['c_id'],"cn"=>$info['c_name']);
	if (empty($str))
	{
		return $default;
	}
	else
	{
		$sql = "select c_id,c_name from ".table('category')."  where c_alias='QS_education'";
		$info=$db->getall($sql);
		$return=resume_search_str($info,$str,"c_name");
		if ($return)
		{
		return array("id"=>$return['c_id'],"cn"=>$return['c_name']);
		}
		else
		{
		return $default;
		}
	}
}
//匹配要求工作经验
function resume_experience($str=NULL)
{
	global $db,$locoyspider;
	$sql = "select c_id,c_name from ".table('category')." where c_alias='QS_experience'  and c_id=".intval($locoyspider['jobs_experience'])." LIMIT 1";
	$info=$db->getone($sql);
	$default=array("id"=>$info['c_id'],"cn"=>$info['c_name']);
	if (empty($str))
	{
		return $default;
	}
	else
	{
		$sql = "select c_id,c_name from ".table('category')."  where c_alias='QS_experience'";
		$info=$db->getall($sql);
		$return=resume_search_str($info,$str,"c_name");
		if ($return)
		{
		return array("id"=>$return['c_id'],"cn"=>$return['c_name']);
		}
		else
		{
		return $default;
		}
	}
}
//匹配简历意向行业
function resume_trade($str=NULL)
{	
	global $db,$locoyspider;
	$sql = "select c_id,c_name from ".table('category')." where  c_alias='QS_trade' AND  c_id=".intval($locoyspider['company_trade'])." LIMIT 1";
	$info=$db->getone($sql);
	$default=array("id"=>$info['c_id'],"cn"=>$info['c_name']);
	if (empty($str))
	{
		return $default;
	}
	else
	{
		$sql = "select c_id,c_name from ".table('category')." where  c_alias='QS_trade'";
		$info=$db->getall($sql);
		$return=resume_search_str($info,$str,"c_name");
		if ($return)
		{
		return array("id"=>$return['c_id'],"cn"=>$return['c_name']);
		}
		else
		{
		return $default;
		}
	}
}
//修改后的匹配薪资待遇
function resume_wage($str=NULL)
{
	global $db,$locoyspider;
	$sql = "select  c_id,c_name from ".table('category')." where  c_alias='QS_wage' and c_id=".intval($locoyspider['jobs_wage'])." LIMIT 1";
	$info=$db->getone($sql);
	$default=array("id"=>$info['c_id'],"cn"=>$info['c_name']);
	if (empty($str))
	{
		return $default;
	}
	else
	{
		$str = trim($str);
		$wage_array = explode('~', $str);
		$wage_min = intval($wage_array[0]);
		if($wage_min>=10000){
			return array('id'=>61,'cn'=>'一万以上/月');
		}elseif($wage_min>=5000){
			return array('id'=>60,'cn'=>'5000~10000元/月');
		}elseif($wage_min>=3000){
			return array('id'=>59,'cn'=>'3000~5000元/月');
		}elseif($wage_min>=2000){
			return array('id'=>58,'cn'=>'2000~3000元/月');
		}elseif($wage_min>=1500){
			return array('id'=>57,'cn'=>'1500~2000元/月');
		}elseif($wage_min>=1000){
			return array('id'=>56,'cn'=>'1000~1500元/月');
		}else{
			return array('id'=>$info['c_id'],'cn'=>$info['c_name']);
		}
	}
}
//匹配要求学历
function resume_work_education($str=NULL)
{
	global $db,$locoyspider;
	$sql = "select c_id,c_name from ".table('category')." where c_alias='QS_education'  and c_id=".intval($locoyspider['jobs_education'])." LIMIT 1";
	$info=$db->getone($sql);
	$default=array("id"=>$info['c_id'],"cn"=>$info['c_name']);
	if (empty($str))
	{
		return $default;
	}
	else
	{
		$sql = "select c_id,c_name from ".table('category')."  where c_alias='QS_education'";
		$info=$db->getall($sql);
		$return=resume_search_str($info,$str,"c_name");
		if ($return)
		{
		return array("id"=>$return['c_id'],"cn"=>$return['c_name']);
		}
		else
		{
		return $default;
		}
	}
}
//模糊搜索
function resume_search_str($arr,$str,$arrinname)
{
	global $locoyspider;

	foreach ($arr as $key =>$list)
	{
		similar_text($list[$arrinname],$str,$percent);
		$od[$percent]=$key;
	}
	krsort($od);
	foreach ($od as $key =>$li)
	{
		if ($key>=$locoyspider['search_threshold'])
		{
		return $arr[$li];
		}
		else
		{
		return false;
		}
	}	
}
//获取积分规则
function get_points_rule()
{
	global $db;
	$sql = "select * from ".table('members_points_rule')." WHERE utype='2' order BY operation asc,value asc";
	$list=$db->getall($sql);
	return $list;
}
//获取会员信息，返回用户名等相关信息
function get_user_points($uid)
{
	global $db;
	$sql = "select * from ".table('members_points')." where uid = ".intval($uid)."  LIMIT 1 ";
	$points=$db->getone($sql);
	return $points['points'];
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
?>