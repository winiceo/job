<?php
 /*
 * 74cms 个人会员函数
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
function get_resume_list($wheresql,$titlele=12,$countinterview=false,$countdown=false,$countapply=false)
{
		global $db;
		$result = $db->query("{$wheresql} LIMIT 30");
		while($row = $db->fetch_array($result))
		{
			$row['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$row['id']));
			$row['title']=cut_str($row['title'],$titlele,0,"...");
			$row['number']="N".str_pad($row['id'],7,"0",STR_PAD_LEFT);
			$row['lastname']=cut_str($row['fullname'],1,0,"**");
			if ($countinterview)
			{
			$wheresql=" WHERE resume_uid='{$row['uid']}' AND resume_id= '{$row['id']}'";
			$row['countinterview']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('company_interview').$wheresql);
			}
			if ($countdown)
			{
			$wheresql=" WHERE resume_uid='{$row['uid']}' AND resume_id= '{$row['id']}'";
			$row['countdown']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('company_down_resume').$wheresql);
			}
			if ($countapply)
			{
			$wheresql=" WHERE personal_uid='{$row['uid']}' AND resume_id= '{$row['id']}'";
			$row['countapply']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('personal_jobs_apply').$wheresql);
			}
			$interest_jobs_id = get_interest_jobs_id_by_resume($row['uid'],$row['id']);
			$interest_jobs_id = explode("-",$interest_jobs_id);
			$interest_jobs_id = implode(",",$interest_jobs_id);
			if($interest_jobs_id){
				$cate = $db->getall("select id,parentid from ".table('category_jobs')." where id in (".$interest_jobs_id.")");
				foreach($cate as $k=>$v){
					$arr[] = $v['parentid'].".".$v['id'].".0";
				}
				$row['interestjobs'] = implode("_",$arr);
			}else{
				$row['interestjobs'] = "";
			}
			$row_arr[] = $row;
		}
		return $row_arr;
}
function get_auditresume_list($uid,$titlele=12)
{
		global $db;
		$uid=intval($uid);
		$result = $db->query("SELECT * FROM ".table('resume')." WHERE uid='{$uid}' and display!=3 ");
		while($row = $db->fetch_array($result))
		{
			$row['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$row['id']));
			$row['title_'] = $row['title'];
			$row['title']=cut_str($row['title'],$titlele,0,"...");
			$row['number']="N".str_pad($row['id'],7,"0",STR_PAD_LEFT);
			$row['lastname']=cut_str($row['fullname'],1,0,"**");
			$row_arr[] = $row;
		}
		return $row_arr;
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
		//$info['lastname']=cut_str($info['fullname'],1,0,"**");
		if($info['sex']==1){
			$info['lastname']=cut_str($info['fullname'],1,0,"先生");
		}elseif($info['sex'] == 2){
			$info['lastname']=cut_str($info['fullname'],1,0,"女士");
		}else{
			$info['lastname']=cut_str($info['fullname'],1,0,"**");
		}	
		return $info;
	}
}
// 获取简历附件图片 
function get_resume_img($uid,$pid)
{
	global $db;
	$sql = "SELECT * FROM ".table('resume_img')." WHERE  resume_id='".intval($pid)."' AND uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取教育经历列表
function get_resume_education($uid,$pid)
{
	global $db;
	if (intval($uid)!=$uid) return false;
	$sql = "SELECT * FROM ".table('resume_education')." WHERE  pid='".intval($pid)."' AND uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取 单条 教育经历
function get_resume_education_one($uid,$pid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_education')." where id='".intval($id)."' AND uid='".intval($uid)."' AND pid='".intval($pid)."' LIMIT 1";
	return $db->getone($sql);
}
//获取：工作经历
function get_resume_work($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where pid='".$pid."' AND uid=".intval($uid)."" ;
	return $db->getall($sql);
}
//获取 单条 工作经历
function get_resume_work_one($uid,$pid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where id='".intval($id)."' AND uid='".intval($uid)."' AND pid='".intval($pid)."' LIMIT 1 ";
	return $db->getone($sql);
}
//获取：培训经历列表
function get_resume_training($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where pid='".intval($pid)."' AND  uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取 单条 培训经历
function get_resume_training_one($uid,$pid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where id='".intval($id)."' AND uid='".intval($uid)."'  AND pid='".intval($pid)."'  LIMIT 1 ";
	return $db->getone($sql);
}
//获取：语言能力列表
function get_resume_language($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_language')." where pid='".intval($pid)."' AND  uid='".intval($uid)."' "; 
	return $db->getall($sql);
}
//获取 单条 语言能力
function get_resume_language_one($uid,$pid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_language')." where id='".intval($id)."' AND uid='".intval($uid)."'  AND pid='".intval($pid)."'  LIMIT 1 ";
	return $db->getone($sql);
}
//获取：证书列表
function get_resume_credent($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_credent')." where pid='".intval($pid)."' AND  uid='".intval($uid)."' ";
	return $db->getall($sql);
}
//获取 单条 证书能力
function get_resume_credent_one($uid,$pid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_credent')." where id='".intval($id)."' AND uid='".intval($uid)."'  AND pid='".intval($pid)."'  LIMIT 1 ";
	return $db->getone($sql);
}

//获取意向职位
function get_resume_jobs($pid)
{
	global $db;
	$pid=intval($pid);
	$sql = "select * from ".table('resume_jobs')." where pid='{$pid}'  LIMIT 20" ;
	return $db->getall($sql);
}
//增加意向职位
function add_resume_jobs($pid,$uid,$str)
{
	global $db;
	$db->query("Delete from ".table('resume_jobs')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode(",",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $a)
		{
		$a=explode(".",$a);
		$setsqlarr['uid']=intval($uid);
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['topclass']=intval($a[0]);
		$setsqlarr['category']=intval($a[1]);
		$setsqlarr['subclass']=intval($a[2]);
			if (!$db->inserttable(table('resume_jobs'),$setsqlarr))return false;
		}
	}
	return true;
}
//获取意向地区
function get_resume_district($pid)
{
	global $db;
	$pid=intval($pid);
	$sql = "select * from ".table('resume_district')." where pid='{$pid}'  LIMIT 20" ;
	return $db->getall($sql);
}
//增加意向地区
function add_resume_district($pid,$uid,$str)
{
	global $db;
	$db->query("Delete from ".table('resume_district')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode(",",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $a)
		{
		$a=explode(".",$a);
		$setsqlarr['uid']=intval($uid);
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['district']=intval($a[0]);
		$setsqlarr['sdistrict']=intval($a[1]);
			if (!$db->inserttable(table('resume_district'),$setsqlarr))return false;
		}
	}
	return true;
}
//增加意向行业
function add_resume_trade($pid,$uid,$str)
{
	global $db;
	$db->query("Delete from ".table('resume_trade')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode(",",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $k=>$a)
		{
		$setsqlarr['uid']=intval($uid);
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['trade']=intval($a);
			if (!$db->inserttable(table('resume_trade'),$setsqlarr))return false;
		}
	}
	return true;
}
//增加特长标签
function add_resume_tag($pid,$uid,$str)
{
	global $db;
	$db->query("Delete from ".table('resume_tag')." WHERE pid='".intval($pid)."'");
	$str=trim($str);
	$arr=explode(",",$str);
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $k=>$a)
		{
		$setsqlarr['uid']=intval($uid);
		$setsqlarr['pid']=intval($pid);
		$setsqlarr['tag']=intval($a);
			if (!$db->inserttable(table('resume_tag'),$setsqlarr))return false;
		}
	}
	return true;
}
function get_user_info($uid)
{
	global $db;
	$sql = "select * from ".table('members')." where uid = ".intval($uid)." LIMIT 1";
	return $db->getone($sql);
}
function get_resumetpl()
{
	global $db;
	$sql = "select * from ".table('tpl')." where tpl_type =2 AND tpl_display=1";
	return $db->getall($sql);
}
function get_userprofile($uid)
{
	global $db;
	$sql = "select * from ".table('members_info')." where uid = ".intval($uid)." LIMIT 1";
	return $db->getone($sql);
}
function refresh_resume($pid,$uid)
{
	global $db,$_CFG;
	$time=time();
	$uid=intval($uid);
	if (!$db->query("update  ".table('resume')."  SET refreshtime='{$time}'  WHERE id='{$pid}' AND uid='{$uid}'")) return false;
	if (!$db->query("update  ".table('resume_search_rtime')."  SET refreshtime='{$time}'  WHERE id='{$pid}' AND uid='{$uid}'")) return false;
	if (!$db->query("update  ".table('resume_search_key')."  SET refreshtime='{$time}'  WHERE id='{$pid}' AND uid='{$uid}'")) return false;

	// 查看操作记录表 统计刷新简历所奖励积分  判断是否超过上限   若没超过上限 则继续添加积分
	$today=mktime(0, 0, 0,date('m'), date('d'), date('Y'));
	$info=$db->getone("SELECT sum(points) as num FROM ".table('members_handsel')." WHERE uid ='{$_SESSION['uid']}' AND htype='refreshresume' AND addtime>{$today} ");
	if(intval($info['num']) >= intval($_CFG['resume_refresh_points_max']))
	{
		write_memberslog($_SESSION['uid'],2,1102,$_SESSION['username'],"刷新了id为{$pid}的简历");
	}
	else
	{
		$points_rule=get_cache('points_rule');
		$user_points=get_user_points($_SESSION['uid']);
		if ($points_rule['resume_refresh']['value']>0)
		{
			$time=time();
			$members_handsel_arr['uid']=$_SESSION['uid'];
			$members_handsel_arr['htype']="refreshresume";
			$members_handsel_arr['points']=$points_rule['resume_refresh']['value'];
			$members_handsel_arr['addtime']=$time;
			$db->inserttable(table("members_handsel"),$members_handsel_arr);
			report_deal($_SESSION['uid'],$points_rule['resume_refresh']['type'],$points_rule['resume_refresh']['value']);
			$user_points=get_user_points($_SESSION['uid']);
			$operator=$points_rule['resume_refresh']['type']=="1"?"+":"-";
			write_memberslog($_SESSION['uid'],2,9001,$_SESSION['username'],"刷新了id为{$pid}的简历，({$operator}{$points_rule['resume_refresh']['value']})，(剩余:{$user_points})",2,1102,"刷新了id为{$pid}的简历","{$operator}{$points_rule['resume_refresh']['value']}","{$user_points}");
		}
		else
		{
			write_memberslog($_SESSION['uid'],2,1102,$_SESSION['username'],"刷新了id为{$pid}的简历");
		}
	}
	write_refresh_log($_SESSION['uid'],2001);		
	return true;
}
//删除简历
function del_resume($uid,$pid)
{
	global $db;
	$uid=intval($uid);
	if (!$db->query("Delete from ".table('resume')." WHERE id='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_jobs')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_district')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_trade')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_tag')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_education')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_training')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_work')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_credent')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_language')." WHERE pid='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_search_rtime')." WHERE id='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('resume_search_key')." WHERE id='{$pid}' AND uid='{$uid}' ")) return false;
	if (!$db->query("Delete from ".table('view_resume')." WHERE resumeid='{$pid}'")) return false;
	$db->query("delete from ".table('resume_entrust')." where id=".$pid);
	// 积分操作 和 写日志
	$points_rule=get_cache('points_rule');
	$user_points=get_user_points($_SESSION['uid']);
	if ($points_rule['delete_resume']['value']>0)
	{
		report_deal($_SESSION['uid'],$points_rule['delete_resume']['type'],$points_rule['delete_resume']['value']);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['delete_resume']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],2,9001,$_SESSION['username'],"删除简历id:({$pid})，({$operator}{$points_rule['delete_resume']['value']})，(剩余:{$user_points})",2,1101,"删除简历id:({$pid})","{$operator}{$points_rule['delete_resume']['value']}","{$user_points}");
	}
	else
	{
		write_memberslog($_SESSION['uid'],2,9001,$_SESSION['username'],"删除简历id:({$pid})");
	}
	return true;
}
//修改简历照片显示设置
function edit_photo_display($uid,$pid,$display)
{
	global $db;
	$db->query("update  ".table('resume')."  SET photo_display='".intval($display)."' WHERE uid='".intval($uid)."' AND id='".intval($pid)."' LIMIT 1");
	return true;
}
//检查简历的完成程度
function check_resume($uid,$pid)
{
	global $db,$timestamp,$_CFG;
	$uid=intval($uid);
	$pid=intval($pid);
	$percent=0;
	$resume_basic=get_resume_basic($uid,$pid);
	$resume_education=get_resume_education($uid,$pid);
	$resume_work=get_resume_work($uid,$pid);
	$resume_training=get_resume_training($uid,$pid);
	$resume_tag=$resume_basic['tag'];
	$resume_specialty=$resume_basic['specialty'];
	$resume_photo=$resume_basic['photo_img'];
	$resume_language=get_resume_language($uid,$pid);
	$resume_credent=get_resume_credent($uid,$pid);
	$resume_img=get_resume_img($uid,$pid);
	if (!empty($resume_basic))$percent=$percent+35;
	if (!empty($resume_education))$percent=$percent+15;
	if (!empty($resume_work))$percent=$percent+15;
	if (!empty($resume_training))$percent=$percent+5;
	if (!empty($resume_tag))$percent=$percent+5;
	if (!empty($resume_specialty))$percent=$percent+5;
	if (!empty($resume_photo))$percent=$percent+5;
	if (!empty($resume_language))$percent=$percent+5;//语言
	if (!empty($resume_credent))$percent=$percent+5;//证书
	if (!empty($resume_img))$percent=$percent+5;//附件

	if ($resume_basic['photo_img'] && $resume_basic['photo_audit']=="1"  && $resume_basic['photo_display']=="1")
	{
	$setsqlarr['photo']=1;
	}
	else
	{
	$setsqlarr['photo']=0;
	}
	$setsqlarr['complete_percent']=$percent;
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']=addslashes($resume_basic['intention_jobs']).addslashes($resume_basic['recentjobs']).addslashes($resume_basic['specialty']);		
	$setsqlarr['key']=addslashes($resume_basic['fullname']).$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=str_replace(","," ",addslashes($resume_basic['intention_jobs']))." {$setsqlarr['key']} ".addslashes($resume_basic['education_cn']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);	
	if (!empty($resume_education))
	{
		foreach($resume_education as $li)
		{
		$setsqlarr['key']=addslashes($li['school'])." {$setsqlarr['key']} ".addslashes($li['speciality']);
		}
	}
	if (!empty($resume_work))
	{
		foreach($resume_work as $li)
		{
		$setsqlarr['key']=addslashes($li['companyname'])." {$setsqlarr['key']} ".addslashes($li['speciality']);
		}
	}
	if (!empty($resume_training))
	{
		foreach($resume_training as $li)
		{
		$setsqlarr['key']=addslashes($li['agency'])." {$setsqlarr['key']} ".addslashes($li['speciality']);
		}
	}
	$setsqlarr['refreshtime']=$timestamp;
	if($setsqlarr['complete_percent']<60){
		$setsqlarr['level'] = 1;
	}elseif($setsqlarr['complete_percent']>=60 && $setsqlarr['complete_percent']<80){
		$setsqlarr['level'] = 2;
	}elseif($setsqlarr['complete_percent']>=80){
		$setsqlarr['level'] = 3;
	}
	$db->updatetable(table('resume'),$setsqlarr,"uid='{$uid}' AND id='{$pid}'");
	// distribution_resume($pid,$uid);
	$j=get_resume_basic($uid,$pid);
	$j = array_map("addslashes", $j);
	$searchtab['sex']=$j['sex'];
	$searchtab['nature']=$j['nature'];
	$searchtab['marriage']=$j['marriage'];
	$searchtab['experience']=$j['experience'];
	$searchtab['district']=$j['district'];
	$searchtab['sdistrict']=$j['sdistrict'];
	$searchtab['wage']=$j['wage'];
	$searchtab['education']=$j['education'];
	$searchtab['current']=$j['current'];
	$searchtab['major']=$j['major'];
	$searchtab['photo']=$j['photo'];
	$searchtab['refreshtime']=$j['refreshtime'];
	$searchtab['talent']=$j['talent'];
	$searchtab['audit']=$j['audit'];
	$db->updatetable(table('resume_search_rtime'),$searchtab,"uid='{$uid}' AND id='{$pid}'");
	$searchtab['key']=$j['key'];
	$searchtab['likekey']=$j['intention_jobs'].','.$j['trade_cn'].','.$j['specialty'].','.$j['fullname'];
	$db->updatetable(table('resume_search_key'),$searchtab,"uid='{$uid}' AND id='{$pid}'");
	unset($searchtab);
}
function get_com_downresume($offset,$perpage,$get_sql='')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	$select="d.*,c.id,c.companyname,c.addtime,c.district_cn,c.trade_cn,c.nature_cn";
	$sql="SELECT {$select} from ".table('company_down_resume')." AS d {$get_sql} ORDER BY did DESC {$limit}";
	$result = $db->query($sql);
	while($row = $db->fetch_array($result))
	{
	$row['company_url']=url_rewrite('QS_companyshow',array('id'=>$row['id']));
	$row_arr[] = $row;
	}
	return $row_arr;
}
//面试邀请
function get_invitation($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	$select="i.*,j.addtime,j.companyname,j.company_addtime,j.district_cn,j.wage_cn,j.deadline,j.refreshtime,j.click";
	$sql="SELECT {$select} from ".table('company_interview')." AS i {$get_sql} ORDER BY did DESC {$limit}";
	$result = $db->query($sql);
	while($row = $db->fetch_array($result))
	{
		if (empty($row['companyname']))
		{
			$jobs=$db->getone("select * from ".table('jobs_tmp')." WHERE id='{$row['jobs_id']}' LIMIT 1");
			if($jobs)
			{
				$row['jobs_name']=$jobs['jobs_name'];
				$row['addtime']=$jobs['addtime'];
				$row['companyname']=$jobs['companyname'];
				$row['company_addtime']=$jobs['company_addtime'];
				$row['company_id']=$jobs['company_id'];
				$row['wage_cn']=$jobs['wage_cn'];
				$row['district_cn']=$jobs['district_cn'];
				$row['deadline']=$jobs['deadline'];
				$row['refreshtime']=$jobs['refreshtime'];
				$row['click']=$jobs['click'];
			}
		}
	$row['belong_name'] = $row['companyname'];
	$row['jobs_name_'] = cut_str($row['jobs_name'],5,0,"...");
	$row['belong_url']=url_rewrite('QS_companyshow',array('id'=>$row['company_id']));
	$row['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$row['jobs_id']));
	$row['notes'] = "对方于 ".date('Y-m-d',$row['interview_addtime'])." 对您发起面试邀请<br />面试时间为：".$row['interview_time']."<br /><br />具体详情：<br />".($row['notes']==""?"暂无":$row['notes']);
	$row_arr[] = $row;
	}
	return $row_arr;
}
//高级职位 面试邀请
function get_hunter_invitation($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	//申请的高级职位
	$hunter_jobs_sql="SELECT i.*,j.district_cn,j.companyname,j.wage_cn FROM ".table('hunter_interview')." AS i {$get_sql} ORDER BY did DESC ".$limit;
	$hunter_jobs_sresult = $db->query($hunter_jobs_sql);
	while($row = $db->fetch_array($hunter_jobs_sresult))
	{
		$row['jobs_url']=url_rewrite('QS_hunter_jobsshow',array('id'=>$row['jobs_id']));
		$row['belong_url']=url_rewrite('QS_hunter_show',array('id'=>$row['hunter_id']));
		$row['belong_name'] = $row['hunter_name'];
		$resume = $db->getone("select title from ".table('resume')." where id=".$row['resume_id']);
		$row['resume_name'] = $resume['title'];
		$row['notes'] = "对方于 ".date('Y-m-d',$row['interview_addtime'])." 对您发起面试邀请，面试时间为：".$row['interview_time']."，具体详情：".$row['notes'];
		$row_arr[] = $row;
	}
	return $row_arr;
}
function del_interview($id,$uid)
{
	global $db;
	$return=0;
	$uidsql=" AND resume_uid=".intval($uid)."";
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	$sql="Delete from ".table('company_interview')." WHERE did IN (".$sqlin.") ".$uidsql."";
	write_memberslog($_SESSION['uid'],2,1502,$_SESSION['username'],"删除了面试邀请($sqlin)");
	$db->query($sql);
	$return=$return+$db->affected_rows();
	return $return;
}
function del_hunter_interview($id,$uid)
{
	global $db;
	$return=0;
	$uidsql=" AND resume_uid=".intval($uid)."";
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	$sql="Delete from ".table('hunter_interview')." WHERE did IN (".$sqlin.") ".$uidsql."";
	write_memberslog($_SESSION['uid'],2,1502,$_SESSION['username'],"删除了高级职位面试邀请($sqlin)");
	$db->query($sql);
	$return=$return+$db->affected_rows();
	return $return;
}
function set_invitation($id,$uid,$setlook)
{
	global $db;
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	$setsqlarr['personal_look']=intval($setlook);
	$wheresql=" did IN (".$sqlin.") AND resume_uid=".intval($uid)."";
	foreach($id as $aid)
	{
		$members=$db->getone("select m.username from ".table('company_interview')." AS i JOIN ".table('members')." AS m ON i.company_uid=m.uid WHERE i.did='{$aid}' LIMIT 1");
		$members=array_map("addslashes", $members);
		write_memberslog($_SESSION['uid'],2,1108,$_SESSION['username'],"查看了 {$members['username']} 的邀请面试");
	}	
	return $db->updatetable(table('company_interview'),$setsqlarr,$wheresql);
}
function set_hunter_invitation($id,$uid,$setlook)
{
	global $db;
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	$setsqlarr['personal_look']=intval($setlook);
	$wheresql=" did IN (".$sqlin.") AND resume_uid=".intval($uid)."";
	foreach($id as $aid)
	{
		$members=$db->getone("select m.username from ".table('hunter_interview')." AS i JOIN ".table('members')." AS m ON i.hunter_uid=m.uid WHERE i.did='{$aid}' LIMIT 1");
		$members=array_map("addslashes", $members);
		write_memberslog($_SESSION['uid'],2,1108,$_SESSION['username'],"查看了 {$members['username']} 的邀请面试");
	}	
	return $db->updatetable(table('hunter_interview'),$setsqlarr,$wheresql);
}
function add_favorites($id,$uid)
{
	global $db,$timestamp;
		if (strpos($id,"-"))
		{
			$id=str_replace("-",",",$id);
			if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$id)) return false;
		}
		else
		{
		$id=intval($id);
		}
	$sql = "select * from ".table('jobs')." WHERE id IN ({$id}) ";
	$jobs=$db->getall($sql);
	$i=0;
	foreach($jobs as $list)
	{
		$sql1 = "select jobs_id from ".table('personal_favorites')." where jobs_id=".$list['id']." AND personal_uid=".$uid."  LIMIT 1";
		if ($db->getone($sql1))
		{
		continue;
		}
		$setsqlarr['personal_uid']=$uid;
		$setsqlarr['jobs_id']=$list['id'];
		$setsqlarr['jobs_name']=addslashes($list['jobs_name']);
		$setsqlarr['addtime']=$timestamp;
		$db->inserttable(table('personal_favorites'),$setsqlarr);
		$i=$i+1;
	}
	return $i;
}
function get_favorites($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT {$offset},{$perpage}";
	$select=" f.*,j.addtime as jobs_addtime,j.companyname,j.company_addtime,j.company_id,j.wage_cn,j.district_cn,j.deadline,j.refreshtime,j.click";
	$result = $db->query("SELECT {$select} FROM ".table('personal_favorites')." AS f {$get_sql} ORDER BY f.did DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		if (empty($row['companyname']))
		{
			$jobs=$db->getone("select * from ".table('jobs_tmp')." WHERE id='{$row['jobs_id']}' LIMIT 1");
			if($jobs)
			{
				$row['jobs_name']=$jobs['jobs_name'];
				$row['jobs_addtime']=$jobs['addtime'];
				$row['companyname']=$jobs['companyname'];
				$row['company_addtime']=$jobs['company_addtime'];
				$row['company_id']=$jobs['company_id'];
				$row['wage_cn']=$jobs['wage_cn'];
				$row['district_cn']=$jobs['district_cn'];
				$row['deadline']=$jobs['deadline'];
				$row['refreshtime']=$jobs['refreshtime'];
				$row['click']=$jobs['click'];
			}
		}
	$row['company_url']=url_rewrite('QS_companyshow',array('id'=>$row['company_id']));
	$row['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$row['jobs_id']));
	$row['wap_jobs_url']=m_url_rewrite("jobs-show",array("id"=>$row['jobs_id']));
	$row_arr[] = $row;
	}
	return $row_arr;
}
function del_favorites($id,$uid)
{
	global $db;
	$return=0;
	$uidsql=" AND personal_uid=".intval($uid)."";
	if (!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	$sql="Delete from ".table('personal_favorites')." WHERE did IN (".$sqlin.") ".$uidsql."";
	write_memberslog($_SESSION['uid'],2,1202,$_SESSION['username'],"删除了职位收藏($sqlin)");
	$db->query($sql);
	$return=$return+$db->affected_rows();
	return $return;
}
function check_jobs_apply($jobs_id,$resume_id,$p_uid)
{
	global $db;
	$sql = "select did from ".table('personal_jobs_apply')." WHERE personal_uid = '".intval($p_uid)."' AND jobs_id='".intval($jobs_id)."'  AND resume_id='".intval($resume_id)."' LIMIT 1";
	return $db->getone($sql);
}
function check_hunter_jobs_apply($jobs_id,$resume_id,$p_uid)
{
	global $db;
	$sql = "select did from ".table('personal_hunter_jobs_apply')." WHERE personal_uid = '".intval($p_uid)."' AND jobs_id='".intval($jobs_id)."'  AND resume_id='".intval($resume_id)."' LIMIT 1";
	return $db->getone($sql);
}
function get_now_applyjobs_num($uid)
{
	global $db;
	$uid=intval($uid);
	$now = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$wheresql=" WHERE personal_uid = '{$uid}' AND apply_addtime>{$now} ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_jobs_apply').$wheresql;
	$total_sql2="SELECT COUNT(*) AS num FROM ".table('personal_hunter_jobs_apply').$wheresql;
	$total = $db->get_total($total_sql)+$db->get_total($total_sql2);
	return $total;
}
//获取  申请的普通职位 列表
function get_apply_jobs($offset,$perpage,$joinsql,$wheresql)
{
	global $db;
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	$get_sql= $joinsql.$wheresql;
	//申请的普通职位
	$select=" a.*,j.addtime,j.company_id,j.companyname,j.company_addtime,j.wage_cn,j.district_cn,j.deadline,j.refreshtime,j.click";
	$sql="SELECT {$select} FROM ".table('personal_jobs_apply')." AS a {$get_sql} ORDER BY a.did DESC ".$limit;
	$result = $db->query($sql);
	while($row = $db->fetch_array($result))
	{
		//标识一下普通职位
		$row['is_senior_job'] = '0';
		if (empty($row['companyname']))
		{
			$jobs=$db->getone("select * from ".table('jobs_tmp')." WHERE id='{$row['jobs_id']}' LIMIT 1");
			$row['addtime']=$jobs['addtime'];
			$row['companyname']=$jobs['companyname'];
			$row['company_addtime']=$jobs['company_addtime'];
			$row['company_id']=$jobs['company_id'];
			$row['wage_cn']=$jobs['wage_cn'];
			$row['district_cn']=$jobs['district_cn'];
			$row['deadline']=$jobs['deadline'];
			$row['refreshtime']=$jobs['refreshtime'];
			$row['click']=$jobs['click'];
		}
		$resume = $db->getone("select title from ".table('resume')." where id=".$row['resume_id']);
		if($resume)
		{
			$row['resume_name'] = $resume['title'];
		}
		else
		{
			$row['resume_name'] = "该简历已经删除";
		}
		$row['company_url']=url_rewrite('QS_companyshow',array('id'=>$row['company_id']));
		$row['belong_name'] = $row['company_name'];
		$row['belong_url'] = $row['company_url'];
		$row['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$row['jobs_id']));
		$row['wap_jobs_url']=m_url_rewrite("jobs-show",array("id"=>$row['jobs_id']));
		//答复状态
		if($row['personal_look']=='1')
		{
			$row['reply_status'] = "企业未查看";
		}
		else
		{
			if($row['is_reply']=='0')
			{
				$row['reply_status'] = "待反馈";
			}
			elseif($row['is_reply']=='1')
			{
				$row['reply_status'] = "合格";
			}
			elseif($row['is_reply']=='2')
			{
				$row['reply_status'] = "不合格";
			}
			elseif($row['is_reply']=='3')
			{
				$row['reply_status'] = "待定";
			}
			elseif($row['is_reply']=='4')
			{
				$row['reply_status'] = "未接通";
			}
		}
		$row_arr[] = $row;
	}
	return $row_arr;
}
//获取 申请的高级职位 列表
function get_apply_hunter_jobs($offset,$perpage,$joinsql,$wheresql)
{
	global $db;
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	$get_sql= $joinsql.$wheresql;
	//申请的高级职位
	$hunter_jobs_sql="SELECT a.*,j.district_cn,j.companyname FROM ".table('personal_hunter_jobs_apply')." AS a {$get_sql} ORDER BY did DESC ".$limit;
	$hunter_jobs_sresult = $db->query($hunter_jobs_sql);
	while($row = $db->fetch_array($hunter_jobs_sresult))
	{
		//标识一下高级职位
		$row['is_senior_job'] = '1';
		$row['jobs_url']=url_rewrite('QS_hunter_jobsshow',array('id'=>$row['jobs_id']));
		$row['belong_url']=url_rewrite('QS_hunter_show',array('id'=>$row['huntet_id']));
		$row['belong_name'] = $row['huntet_name'];
		$resume = $db->getone("select title from ".table('resume')." where id=".$row['resume_id']);
		$row['resume_name'] = $resume['title'];
		//答复状态
		if($row['personal_look']=='1')
		{
			$row['reply_status'] = "企业未查看";
		}
		else
		{
			if($row['is_reply']=='0')
			{
				$row['reply_status'] = "待反馈";
			}
			elseif($row['is_reply']=='1')
			{
				$row['reply_status'] = "合格";
			}
			elseif($row['is_reply']=='2')
			{
				$row['reply_status'] = "不合格";
			}
			elseif($row['is_reply']=='3')
			{
				$row['reply_status'] = "待定";
			}
			elseif($row['is_reply']=='4')
			{
				$row['reply_status'] = "未接通";
			}
		}
		$row_arr[] = $row;
	}
	return $row_arr;
}
function app_get_jobs($id)
{
	global $db;
	if (strpos($id,"-"))
	{
		$id=str_replace("-",",",$id);
		if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$id)) return false;
	}
	else
	{
	$id=intval($id);
	}
	$sql = "select * from ".table('jobs')." WHERE id IN ({$id}) ";
	return $db->getall($sql);
}
//得到猎头职位信息
function app_get_hunter_jobs($id)
{
	global $db;
	if (strpos($id,"-"))
	{
		$id=str_replace("-",",",$id);
		if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$id)) return false;
	}
	else
	{
	$id=intval($id);
	}
	$sql = "select * from ".table('hunter_jobs')." WHERE id IN ({$id}) ";
	return $db->getall($sql);
}
function del_jobs_apply($del_id,$uid)
{
	global $db;
	$return = 0;
	$uidsql=" AND personal_uid=".intval($uid)." ";
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('personal_jobs_apply')." WHERE did IN (".$sqlin.") ".$uidsql."")) return false;
	write_memberslog($_SESSION['uid'],2,1302,$_SESSION['username'],"删除了职位申请($sqlin)");
	$return=$return+$db->affected_rows();
	return $return;
}
function del_hunter_jobs_apply($del_id,$uid)
{
	global $db;
	$return = 0;
	$uidsql=" AND personal_uid=".intval($uid)." ";
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('personal_hunter_jobs_apply')." WHERE did IN (".$sqlin.") ".$uidsql."")) return false;
	write_memberslog($_SESSION['uid'],2,1302,$_SESSION['username'],"删除了高级职位申请($sqlin)");
	$return=$return+$db->affected_rows();
	return $return;
}
function count_resume($uid)
{
	global $db;
	$wheresql=" WHERE uid='".intval($uid)."' ";
	$total=$db->get_total("SELECT COUNT(*) AS num FROM ".table('resume').$wheresql);
	return $total;
}
function count_interview($uid,$jobs_type,$look=NULL)
{
	global $db;
	$uid=intval($uid);
	$wheresql=" WHERE  resume_uid='{$uid}' ";
	if (intval($look)>0) $wheresql.=" AND  personal_look=".intval($look);
	if($jobs_type==1)
	{
		$total_sql="SELECT COUNT(*) AS num FROM ".table('hunter_interview').$wheresql;
	}
	else
	{
		$total_sql="SELECT COUNT(*) AS num FROM ".table('company_interview')." {$wheresql}";
	}
	$total = $db->get_total($total_sql);
	return $total;
}
//统计某段时间内，个人收藏的职位
function count_favorites($personal_uid,$time=0)
{
	global $db;
	$personal_uid=intval($personal_uid);
	$time=intval($time);
	$before_time = 0;
	if($time>0){
		$before_time = intval(time() - $time*86400);
	}
	$wheresql=" WHERE  personal_uid='{$personal_uid}'  and addtime>{$before_time}";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_favorites')." {$wheresql}";
	return $db->get_total($total_sql);
}
//统计某段时间内，谁在关注我
function count_view_resume($resumeid,$time=0)
{
	global $db;
	$resumeid=trim($resumeid);
	$time=intval($time);
	$before_time = 0;
	if($time>0){
		$before_time = intval(time() - $time*86400);
	}
	$wheresql=" WHERE  resumeid in ({$resumeid})  and addtime>{$before_time}";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('view_resume')." {$wheresql}";
	return $db->get_total($total_sql);
}
function count_personal_jobs_apply($jobs_type,$uid,$look=NULL)
{
	global $db;
	$wheresql=" WHERE personal_uid='{$_SESSION['uid']}' ";
	if(intval($look)>0)	$wheresql.=" AND personal_look='{$look}' ";
	if($jobs_type == 1)
	{
		$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_hunter_jobs_apply').$wheresql;
	}
	else
	{
		$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_jobs_apply').$wheresql;
	}
	$total = $db->get_total($total_sql);
	return $total;
}
function count_jobs_library($uid,$days=NULL)
{
	global $db;
	$wheresql=" WHERE personal_uid=".intval($uid)." ";
	if (intval($days)>0)
	{
	$settr_val=strtotime("-".$days." day");
	$wheresql.=" AND addtime>".$settr_val;
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_favorites').$wheresql;
	return $db->get_total($total_sql);
}
function get_feedback($uid)
{
	global $db;
	$sql = "select * from ".table('feedback')." where uid='".intval($uid)."' ORDER BY id desc";
	return $db->getall($sql);
}
function del_feedback($del_id,$uid)
{
	global $db;
	if (!$db->query("Delete from ".table('feedback')." WHERE id='".intval($del_id)."' AND uid='".intval($uid)."'  ")) return false;
	write_memberslog($_SESSION['uid'],2,7002,$_SESSION['username'],"删除反馈信息($del_id)");
	return true;
}
function get_interest_jobs_id($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select id from ".table('resume')." where   uid='{$uid}' LIMIT 3 ";
	$info=$db->getall($sql);
	if (is_array($info))
	{
		foreach($info as $s)
		{
			$jobsid=get_resume_jobs($s['id']);
			if(is_array($jobsid))
			{
			foreach($jobsid as $cid)
			 {
			 $interest_id[]=$cid['category'];
			 }
			}
		}
		if (is_array($interest_id)) return implode("-",array_unique($interest_id));
	}
	return "";	
}
function get_interest_jobs_id_by_resume($uid,$pid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select id from ".table('resume')." where   uid='{$uid}' AND id='{$pid}' LIMIT 3 ";
	$info=$db->getone($sql);
	$interest_id = array();
	$jobsid=get_resume_jobs($info['id']);
	if(is_array($jobsid))
	{
		foreach($jobsid as $cid)
	    {
	    	$interest_id[]=$cid['category'];
	    }
	}
	if (!empty($interest_id)) return implode("-",array_unique($interest_id));
	return "";
}
function get_interest_jobs_list($cid){
	global $db;
	$orderbysql = " order by refreshtime desc ";
	$limitsql = " limit 3 ";
	$list=array();
	if($cid){
		if(strpos("-", $cid)){
			$wheresql = "";
			$arr = explode("-", $cid);
			foreach ($arr as $key => $value) {
				$wheresql .= "OR category=".$value." ";
			}
			$wheresql = $wheresql?" WHERE ".trim($wheresql,"OR"):"";
			$list = $db->getall("select * from ".table('jobs').$wheresql.$orderbysql.$limitsql);
		}else{
			$list = $db->getall("select * from ".table('jobs')." where category=".$cid.$orderbysql.$limitsql);
		}
	}
	if(empty($list)){
		$list = $db->getall("select * from ".table("jobs").$orderbysql.$limitsql);
	}
	return $list;
}
function check_jobs_report($uid,$jobs_id)
{
	global $db;
	$sql = "select id from ".table('report')." WHERE uid = '".intval($uid)."' AND jobs_id='".intval($jobs_id)."' LIMIT 1";
	return $db->getone($sql);
}
function get_pms($offset,$perpage,$get_sql= '')
{
	global $db;
	if(isset($offset)&&!empty($perpage))
	{
	$limit=" LIMIT {$offset},{$perpage}";
	}
	$result = $db->query($get_sql.$limit);
	while($row = $db->fetch_array($result))
	{
		$row_arr[] = $row;
	}
	return $row_arr;
}
//3.5
function get_pms_no_num(){	//获取PMS 未读取的数量
	global $db,$QS_cookiepath,$QS_cookiedomain;
	$pmscount=$db->get_total("SELECT COUNT(*) AS num FROM ".table('pms')." WHERE (msgfromuid='{$_SESSION['uid']}' OR msgtouid='{$_SESSION['uid']}') AND `new`='1' AND `replyuid`<>'{$_SESSION['uid']}'");
	setcookie('QS[pmscount]',$pmscount, false,$QS_cookiepath,$QS_cookiedomain);
	return $pmscount;
}
//3.5
function update_pms_read($offset,$perpage,$get_sql= '')
{
	global $db;
	if(isset($offset)&&!empty($perpage))
	{
	$limit=" LIMIT {$offset},{$perpage}";
	}
	$result = $db->query($get_sql.$limit);
	$return_id = '';
	while($row = $db->fetch_array($result))
	{
		$return_id .= $row['pmid'].',';
	}
	$return_id = rtrim($return_id,',');
	return $return_id;
}
 
//3.4
function app_get_course($id)
{
	global $db;
	$id=intval($id);
	$sql = "select * from ".table('course')." WHERE id ={$id} limit 1";
	return $db->getone($sql);
}
//3.4
function get_now_applycour_num($uid)
{
	global $db;
	$uid=intval($uid);
	$now = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$wheresql=" WHERE personal_uid = '{$uid}' AND apply_addtime>{$now} ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_course_apply').$wheresql;
	return $db->get_total($total_sql);
}
//3.4
function check_course_apply($courseid,$uid)
{
	global $db;
	$sql = "select did from ".table('personal_course_apply')." WHERE personal_uid = '".intval($uid)."' AND course_id='".intval($courseid)."' LIMIT 1";
	return $db->getone($sql);
}
function get_apply_course($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT {$offset},{$perpage}";
	$result = $db->query("SELECT * FROM ".table('personal_course_apply')."  {$get_sql} ORDER BY did DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
	$row['course_url']=url_rewrite('QS_train_curriculumshow',array('id'=>$row['course_id']));
	$row['train_url']=url_rewrite('QS_train_agencyshow',array('id'=>$row['train_id']));
	$row_arr[] = $row;
	}
	return $row_arr;
}
function count_personal_cour_apply($uid,$look=NULL)
{
	global $db;
	$wheresql=" WHERE personal_uid='{$_SESSION['uid']}' ";
	if(intval($look)>0)	$wheresql.=" AND personal_look='{$look}' ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_course_apply').$wheresql;
	return $db->get_total($total_sql);
}
function del_apply($del_id,$uid)
{
	global $db;
	$uidsql=" AND personal_uid=".intval($uid)."";
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('personal_course_apply')." WHERE did IN ({$sqlin}) {$uidsql}")) return false;
	write_memberslog($_SESSION['uid'],2,1402,$_SESSION['username'],"删除课程申请({$sqlin})");
	return true;
}

function count_personal_resume_down($uid)
{
	global $db;
	$wheresql=" WHERE resume_uid='{$uid}' ";
	$num=$db->get_total("SELECT COUNT(*) AS num FROM ".table('company_down_resume').$wheresql);
	return $num;
}
function get_view_jobs($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT {$offset},{$perpage}";
	$selectstr=" a.*,r.jobs_name,r.uid as company_uid,r.audit,r.deadline,r.display ";
	$result = $db->query("SELECT {$selectstr} FROM ".table('view_jobs')." as a {$get_sql} ORDER BY a.id DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		if (empty($row['jobs_name']))
		{
			$jobs=$db->getone("select * from ".table('jobs_tmp')." WHERE `id`='{$row['jobsid']}' LIMIT 1");
			$row['jobs_name']=$jobs['jobs_name'];
			$row['jobsid']=$jobs['id'];
			$row['company_uid'] = $jobs['uid'];
			$row['audit'] = $jobs['audit'];
			$row['deadline'] = $jobs['deadline'];
			$row['display'] = $jobs['display'];
		}
		if (!empty($row['jobs_name'])){
			$company_profile = $db->getone("select `id`,`companyname` from ".table('company_profile')." where `uid`=".$row['company_uid']);
			$row['companyname'] = $company_profile['companyname'];
			$row['company_url'] = url_rewrite("QS_companyshow",array('id'=>$company_profile['id']));
		}
		if($row['audit']==3){
			$row['status'] = 4;
			$row['status_cn'] = '未通过';
		}
		elseif($row['audit']==2){
			$row['status'] = 3;
			$row['status_cn'] = '审核中';
		}
		elseif($row['deadline']<time()){
			$row['status'] = 5;
			$row['status_cn'] = '已过期';
		}
		elseif($row['display']==2){
			$row['status'] = 2;
			$row['status_cn'] = '暂停中';
		}else{
			$row['status'] = 1;
			$row['status_cn'] = '发布中';
		}
		$row['url'] = url_rewrite("QS_jobsshow",array('id'=>$row['jobsid']));
		//判读 是否申请过该职位
		$is_apply = $db->getone("select did from ".table('personal_jobs_apply')." where personal_uid={$_SESSION['uid']} and jobs_id=".$row['jobsid']);
		if($is_apply)
		{
			$row['is_apply'] = '1';
		}
		//判读 是否收藏过该职位
		$is_favorites = $db->getone("select did from ".table('personal_favorites')." where personal_uid={$_SESSION['uid']} and jobs_id=".$row['jobsid']);
		if($is_favorites)
		{
			$row['is_favorites'] = '1';
		}
		$row_arr[] = $row;
	}
	return $row_arr;
}
function del_view_jobs($del_id,$uid)
{
	global $db;
	$return = 0;
	$uidsql=" AND uid=".intval($uid)."";
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('view_jobs')." WHERE id IN ({$sqlin}) {$uidsql}")) return false;
	$return=$return+$db->affected_rows();
  	return $return;
}
function get_view_resume($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT {$offset},{$perpage}";
	$selectstr=" a.*,r.id as resume_id,r.uid as resume_uid,r.title ";
	$result = $db->query("SELECT {$selectstr} FROM ".table('view_resume')." as a {$get_sql} ORDER BY a.id DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		if($row['resume_uid']!=intval($_SESSION['uid'])){
			continue;
		}
		$row['title'] = cut_str($row['title'],13,0,"..");
		$company_profile = $db->getone("select `id`,`companyname` from ".table('company_profile')." where `uid`=".$row['uid']);
		$row['companyname'] = $company_profile['companyname'];
		$row['company_url'] = url_rewrite("QS_companyshow",array('id'=>$company_profile['id']));
		$row['url'] = url_rewrite("QS_resumeshow",array('id'=>$row['resumeid']));
	
		$jobs = $db->getall("select * from ".table('jobs')." where `uid`=".$row['uid'].$wheresql);
		foreach ($jobs as $key1 => $value1) {
			$row['jobslist'][$key1]['jobsname'] = $value1['jobs_name'];
			$row['jobslist'][$key1]['jobs_url'] = url_rewrite("QS_jobsshow",array('id'=>$value1['id']));
		}
		if($row['resume_uid']){
			$downlog = $db->getone("select did from ".table('company_down_resume')." where resume_id={$row['resumeid']} and resume_uid={$row['resume_uid']} and company_uid={$row['uid']}");
			if(intval($downlog)){
				$row['hasdown'] = 1;
			}else{
				$row['hasdown'] = 0;
			}
		}
		
		$row_arr[] = $row;
	}
	return $row_arr;
}
function get_my_resume($uid){
	global $db;
	$wheresql = " where uid=".$uid." ";
	$sql="SELECT id FROM ".table('resume').$wheresql;
	$my_resume = $db->getall($sql);
	foreach ($my_resume as $key => $value) {
		$idarr[] = $value['id'];
	}
	$idstr = implode(",",$idarr);
	return $idstr;
}
function del_view_resume($del_id)
{
	global $db;
	$return = 0;
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('view_resume')." WHERE id IN ({$sqlin}) ")) return false;
	$return=$return+$db->affected_rows();
  	return $return;
}
function count_personal_attention_me($uid){
	global $db;
	$id_arr = array();
	$id_str = "";
	$total = 0;
	$personal_resume = $db->getall("select id from ".table('resume')." where uid=".$uid);
	if($personal_resume){
		foreach ($personal_resume as $key => $value) {
			$id_arr[] = $value["id"];
		}
		$id_str = implode(",", $id_arr);
		$total = $db->get_total("select count(*) as num from ".table("view_resume")." where resumeid in (".$id_str.")");
	}
	return $total;
}
//获取简历屏蔽企业关键字
function get_com_keyword($uid,$pid){
	global $db;
	$result = $db->getall("select * from ".table('personal_shield_company')." where uid=".$uid." and pid=".$pid);
	return $result;
}
function del_shield_company($uid,$pid,$keyword_id){
	global $db;
	if(!$db->query("delete from ".table('personal_shield_company')." where uid=".$uid." and pid=".$pid." and id=".$keyword_id)) return false;
	return true;
}
function count_com_keyword($uid,$pid){
	global $db;
	$count = $db->get_total("select count(*) as num from ".table('personal_shield_company')." where uid=".$uid." and pid=".$pid);
	return $count;
}
function set_resume_entrust($resume_id,$setsqlarr){
	global $db;
	$resume = $db->getone("select audit,uid,fullname,addtime from ".table('resume')." where id=".$resume_id);
	if($resume["audit"]=="1"){
		$has = $db->getone("select 1 from ".table('resume_entrust')." where id=".$resume_id);
		//查看这份简历是否是委托过
		if(!$has){
			$setsqlarr['id'] = $resume_id;
			$setsqlarr['uid'] = $resume['uid'];
			$setsqlarr['fullname'] = $resume['fullname'];
			$setsqlarr['resume_addtime'] = $resume['addtime'];
			if(!$db->inserttable(table('resume_entrust'),$setsqlarr)){
				return false;
			}
		}
	}
	else
	{
		$db->query("delete from ".table('resume_entrust')." where id=".$resume_id);
	}
	return true;
}
//获取 面试邀请的 邀请简历
function get_interview_resumes($uid)
{
	global $db;
	$uid=intval($uid);
	return $db->getall( "SELECT distinct resume_id,resume_name FROM ".table('company_interview')." WHERE resume_uid={$uid}");
}
//获取 已申请职位的 简历
function get_apply_jobs_resumes($uid)
{
	global $db;
	$uid=intval($uid);
	$resume_id =  $db->getall( "SELECT distinct resume_id FROM ".table('personal_jobs_apply')." WHERE personal_uid={$uid}");
	//根据简历id 得出简历的title(不能是简历的名字  因为不同的简历名字是一样 所以筛选下拉话可能是一样的)
	$arr=array();
	foreach ($resume_id as $key => $value) 
	{
		$arr[] = $value['resume_id'];
	}
	$resume_id = implode(',',$arr);
	if($resume_id)
	{
		$resume =  $db->getall( "SELECT id, title FROM ".table('resume')." WHERE id in ({$resume_id})");
		return $resume;
	}
	else
	{
		return null;
	}
}
//获取 外发简历的 邀请简历
function get_outward_resumes($uid)
{
	global $db;
	$uid=intval($uid);
	return $db->getall( "SELECT distinct resume_id,resume_title FROM ".table('resume_outward')." WHERE uid={$uid}");
}
//外发简历 模板
function get_outward_resumes_tpl($uid,$resume_id)
{
	global $_CFG;
	$uid=intval($uid);
	$resume_id=intval($resume_id);
	$resume_basic=get_resume_basic($uid,$resume_id);
	if($resume_basic['tag_cn'])
	{
		$resume_tag=explode(',',$resume_basic['tag_cn']);
		$tag_str='<p>';
		foreach ($resume_tag as $value)
		{
			$tag_str.='<span style="color: #656565;display:inline-block;background-color: #f2f4f7; border: 1px solid #d6d6d7;text-align: center;height:30px;line-height: 30px;margin-right:10px;padding:0 10px">'.$value.'</span>';
		}
		$tag_str.='</p>';
	}
	$resume_work=get_resume_work($uid,$resume_id);
	$show_contact = false;
	if($_CFG['showapplycontact']=='1' || $_CFG['showresumecontact']=='0')
	{
		$show_contact = '<p>手机号码：'.$resume_basic["telephone"].' 电子邮箱：'.$resume_basic["email"].'</p>';
	}
	else
	{
		$show_contact = '<p>联系方式：<a href='.url_rewrite('QS_resumeshow',array('id'=>$resume_id)).'>点击查看</a></p>';
	}	
	$htm='<div style="width: 900px;margin: 0 auto;font-size: 14px;">
		<div style="margin-bottom:10px">
			<div style="float: left;"><a href="'.$_CFG['site_domain'].$_CFG['site_dir'].'"><img src="'.$_CFG['site_domain'].$_CFG['upfiles_dir'].$_CFG['web_logo'].'" alt="'.$_CFG['site_name'].'" border="0" align="absmiddle" width=180 height=50 /></div>
			<div style="float: right;padding-top:10px;">'.$templates.'更新时间：'.date("Y-m-d",$resume_basic["refreshtime"]).'</div>
			<div style="clear:both"></div>
		</div>
		<div style="padding-bottom: 10px;">
			<span style="font-size: 18px;font-weight: 700;">'.$resume_basic["fullname"].'</span><span>（'.$resume_basic["sex_cn"].'，'.$resume_basic["age"].'）</span>
			<p>学历：'.$resume_basic["education_cn"].' | 专业：'.$resume_basic["major_cn"].' | 工作经验：'.$resume_basic["experience_cn"].'年 | 现居住地：'.$resume_basic["residence"].'</p>

			'.$show_contact.$tag_str.'

		</div>
		<div style="padding-bottom: 10px;">
			<p style="font-size: 16px;font-weight: 700;">求职意向</p>
			<p>期望职位：'.$resume_basic["intention_jobs"].'</p>
			<p>期望薪资：'.$resume_basic["wage_cn"].'</p>
			<p>期望地区：'.$resume_basic["district_cn"].'</p>
		</div>
		<div style="padding-bottom: 10px;">
			<p style="font-size: 16px;font-weight: 700;">工作经验</p>';
				if(!empty($resume_work))
				{
					foreach ($resume_work as $value)
					{
						$htm.='<div>
								<p style="font-size: 14px;font-weight: 700;">'.$value["companyname"].'</p>
								<p>'.$value["startyear"].'年'.$value["startmonth"].'月-'.$value["endyear"].'年'.$value["endmonth"].'月 '.$value["jobs"].'</p>
								<div style="float: left;width: 100px;">工作内容：</div>
								<div style="float: right;width: 800px;">'.$value["achievements"].'</div>
								<div style="clear:both"></div>
							</div>'	;
					}
				}
				else
				{
					$htm.='<div>
								没有填写工作经历
							</div>'	;
				}
				
		$htm.='</div>';
		if($resume_basic["specialty"])
		{
			$htm.='<div style="padding-bottom: 10px;">
				<p style="font-size: 16px;font-weight: 700;">自我描述</p>
				<p>'.$resume_basic["specialty"].'</p>
			</div>';
		}
		$htm.='<div style="text-align: center;margin-top:20px">
				该简历来自<a href="'.$_CFG["site_domain"].$_CFG["site_dir"].'">'.$_CFG["site_name"].'</a>
			</div>
		</div>';
		return $htm;
}
//删除 简历外发记录
function del_outward($del_id)
{
	global $db;
	$return = 0;
	if (!is_array($del_id)) $del_id=array($del_id);
	$sqlin=implode(",",$del_id);
	if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return false;
	if (!$db->query("Delete from ".table('resume_outward')." WHERE id IN ({$sqlin}) ")) return false;
	$return=$return+$db->affected_rows();
  	return $return;
}

function do_import_resume($info,$uid){
	global $db,$_CFG,$timestamp;
	if($info&&$uid){
		require_once(QISHI_ROOT_PATH.'/include/fun_import.php');
		if(empty($info['basicinfo'])){
			return false;
		}
		$info['basicinfo']['fullname'] = unicode_decode($info['basicinfo']['fullname']);
		$setsqlarr = iconv_to_gbk($info['basicinfo']);
		$setsqlarr['uid']=intval($uid);
		$setsqlarr['title']="未命名简历";
		$setsqlarr['display_name']=1;
		$setsqlarr['sex']=$setsqlarr['sex_cn']=='男'?1:2;
		$experience = match_experience($setsqlarr['experience_cn']);
		$setsqlarr['experience_cn']=$experience[1];
		$setsqlarr['experience']=$experience[0];
		$setsqlarr['email_notify']=1;
		$setsqlarr['marriage']=$setsqlarr['marriage_cn']=='已婚'?2:1;
		$intentionjobsarr = explode('、',$setsqlarr['intention_jobs']);
		$match_jobs_id_arr = array();
		$match_jobs_cn_arr = array();
		foreach ($intentionjobsarr as $key => $value) {
			$match_jobs_arr = match_jobs_category($value);
			if($match_jobs_arr){
				$match_jobs_id_arr[] = $match_jobs_arr['topclass'].'.'.$match_jobs_arr['category'].'.'.$match_jobs_arr['subclass'];
				$match_jobs_cn_arr[] = $match_jobs_arr['category_cn'];
			}
		}
		$setsqlarr['intention_jobs'] = !empty($match_jobs_cn_arr)?implode(',',$match_jobs_cn_arr):'';
		$intention_jobs_id = !empty($match_jobs_id_arr)?implode(',',$match_jobs_id_arr):'';

		$tradearr = explode('、',$setsqlarr['trade_cn']);
		$match_trade_id_arr = array();
		foreach ($tradearr as $key => $value) {
			$match_trade_arr = match_trade($value);
			if($match_trade_arr){
				$match_trade_id_arr[] = $match_jobs_arr['id'];
			}
		}
		$intentiontrade = !empty($match_trade_id_arr)?implode(',',$match_trade_id_arr):'';

		$districtarr = explode('、',$setsqlarr['district_cn']);
		$match_district_id_arr = array();
		foreach ($districtarr as $key => $value) {
			$match_district_arr = match_district($value);
			if($match_district_arr){
				$match_district_id_arr[] = $match_district_arr['district'].'.'.$match_district_arr['sdistrict'];
			}
		}
		$intentiondistrict = !empty($match_district_id_arr)?implode(',',$match_district_id_arr):'';

		$match_current_arr = match_current($setsqlarr['current_cn']);
		if($match_current_arr){
			$setsqlarr['current']=$match_current_arr['id'];
			$setsqlarr['current_cn']=$match_current_arr['cn'];
		}

		$match_nature_arr = match_nature($setsqlarr['nature_cn']);
		if($match_nature_arr){
			$setsqlarr['nature']=$match_nature_arr['id'];
			$setsqlarr['nature_cn']=$match_nature_arr['cn'];
		}
		
		$match_wage_arr = match_wage($setsqlarr['wage_cn']);
		if($match_wage_arr){
			$setsqlarr['wage']=$match_wage_arr['id'];
			$setsqlarr['wage_cn']=$match_wage_arr['cn'];
		}
		$setsqlarr['refreshtime']=$timestamp;
		$setsqlarr['audit']=intval($_CFG['audit_resume']);
		$setsqlarr['resume_from_pc']=1;
		$setsqlarr['addtime']=$timestamp;
		$pid=$db->inserttable(table('resume'),$setsqlarr,1);
		if($pid){
			$searchtab['id'] = $pid;
			$searchtab['uid'] = $uid;
			$db->inserttable(table('resume_search_key'),$searchtab);
			$db->inserttable(table('resume_search_rtime'),$searchtab);
			add_resume_jobs($pid,$uid,$intention_jobs_id)?"":showmsg('保存失败！',0);
			add_resume_district($pid,$uid,$intentiondistrict)?"":showmsg('保存失败！',0);
			add_resume_trade($pid,$uid,$intentiontrade)?"":showmsg('保存失败！',0);
			
			if(!get_userprofile($uid)){
				$infoarr['realname']=$setsqlarr['fullname'];
				$infoarr['sex']=$setsqlarr['sex'];
				$infoarr['sex_cn']=$setsqlarr['sex_cn'];
				$infoarr['birthday']=$setsqlarr['birthdate'];
				$infoarr['residence']=$setsqlarr['residence'];
				$infoarr['experience']=$setsqlarr['experience'];
				$infoarr['experience_cn']=$setsqlarr['experience_cn'];
				$infoarr['householdaddress']=$setsqlarr['householdaddress'];
				$infoarr['marriage']=$setsqlarr['marriage'];
				$infoarr['marriage_cn']=$setsqlarr['marriage_cn'];
				$infoarr['phone']=$setsqlarr['telephone'];
				$infoarr['email']=$setsqlarr['email'];
				$infoarr['uid']=intval($uid);
				$db->inserttable(table('members_info'),$infoarr);
			}
			//教育经历
			if(!empty($info['eduinfo'])){
				foreach ($info['eduinfo'] as $key => $value) 
				{
					$eduarrsql = iconv_to_gbk($value);
					$eduarrsql['pid'] = $pid;
					$eduarrsql['uid'] = $uid;
					$match_education_arr = match_education($eduarrsql['education_cn']);
					if($match_education_arr){
						$eduarrsql['education']=$match_education_arr['id'];
						$eduarrsql['education_cn']=$match_education_arr['cn'];
					}
					$db->inserttable(table("resume_education"),$eduarrsql);
				}
			}
			//工作经历
			if(!empty($info['workinfo'])){
				foreach ($info['workinfo'] as $key => $value) 
				{
					$workarrsql = iconv_to_gbk($value);
					$workarrsql['pid'] = $pid;
					$workarrsql['uid'] = $uid;
					$db->inserttable(table("resume_work"),$workarrsql);
				}
			}
			//培训经历
			if(!empty($info['traininginfo'])){
				foreach ($info['traininginfo'] as $key => $value) 
				{
					$trainingarrsql = iconv_to_gbk($value);
					$trainingarrsql['pid'] = $pid;
					$trainingarrsql['uid'] = $uid;
					$db->inserttable(table("resume_training"),$trainingarrsql);
				}
			}
			//语言能力
			if(!empty($info['languageinfo'])){
				foreach ($info['languageinfo'] as $key => $value) 
				{
					$languagearrsql = iconv_to_gbk($value);
					$languagearrsql['pid'] = $pid;
					$languagearrsql['uid'] = $uid;
					$match_language_arr = match_language($languagearrsql['language_cn']);
					if($match_language_arr){
						$languagearrsql['language']=$match_language_arr['id'];
						$languagearrsql['language_cn']=$match_language_arr['cn'];
					}
					$match_language_level_arr = match_language_level($languagearrsql['level_cn']);
					if($match_language_level_arr){
						$languagearrsql['level']=$match_language_level_arr['id'];
						$languagearrsql['level_cn']=$match_language_level_arr['cn'];
					}
					$db->inserttable(table("resume_language"),$languagearrsql);
				}
			}
			//证书
			if(!empty($info['credentinfo'])){
				foreach ($info['credentinfo'] as $key => $value) 
				{
					$credentarrsql = iconv_to_gbk($value);
					$credentarrsql['pid'] = $pid;
					$credentarrsql['uid'] = $uid;
					$db->inserttable(table("resume_credent"),$credentarrsql);
				}
			}
			check_resume($uid,$pid);
			write_memberslog($uid,2,1101,$_SESSION['username'],"导入了简历");
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
function get_user_points($uid)
{
	global $db;
	$uid=intval($uid);
	$points=$db->getone("select points from ".table('members_points')." where uid ='{$uid}' LIMIT 1");
	return $points['points'];
}
function report_deal($uid,$i_type=1,$points=0)
{
	global $db,$timestamp;
	$points=intval($points);
	$uid=intval($uid);
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
	$sql = "UPDATE ".table('members_points')." SET points= '{$points_val}' WHERE uid='{$uid}' LIMIT 1";
	if (!$db->query($sql))return false;
	return true;
}
//修改简历 积分日志操作 方法
function perfect_resume($uid,$username,$pid,$type="1")
{
	$uid = intval($_SESSION['uid']);
	$username = trim($_SESSION['username']);
	$type = intval($type);
	$points_rule=get_cache('points_rule');
	$user_points=get_user_points($uid);
	if ($points_rule['perfect_resume']['value']>0)
	{
		report_deal($uid,$type,$points_rule['perfect_resume']['value']);
		$user_points=get_user_points($uid);
		$operator=$type=="1"?"+":"-";
		write_memberslog($uid,2,9001,$username,"修改了id为".intval($pid)."的简历，({$operator}{$points_rule['perfect_resume']['value']})，(剩余:{$user_points})",2,1105,"修改了id为".intval($pid)."的简历","{$operator}{$points_rule['perfect_resume']['value']}","{$user_points}");
	}
	else
	{
		write_memberslog($uid,2,1105,$username,"修改了id为".intval($pid)."的简历");
	}
}
function get_payment()
{
	global $db;
	$sql = "select * from ".table('payment')." where p_install='2' ORDER BY listorder desc";
	$list=$db->getall($sql);
	return $list;
}
//获取指点会员订单
function get_user_order($uid,$is_paid)
{
	global $db;
	$sql = "select * from ".table('order')." WHERE uid=".intval($uid)." AND  is_paid='".intval($is_paid)."' ORDER BY id desc";
	return $db->getall($sql);
}
function get_payment_info($typename,$name=false)
{
	global $db;
	if($typename == 'points')
	{
		return '积分兑换';
	}
	$sql = "select * from ".table('payment')." where typename ='".$typename."' AND p_install='2' LIMIT 1";
	$val=$db->getone($sql);
	if ($name)
	{
	return $val['byname'];
	}
	else
	{
	return $val;
	}
}
//增加订单
function add_order($uid,$pay_type,$oid,$amount,$payment_name,$description,$addtime,$points='',$setmeal='',$utype='1')
{
	global $db;
	$setsqlarr['uid']=intval($uid);
	$setsqlarr['pay_type']=$pay_type;
	$setsqlarr['oid']=$oid;
	$setsqlarr['amount']=$amount;
	$setsqlarr['payment_name']=$payment_name;
	$setsqlarr['description']=$description;
	$setsqlarr['addtime']=$addtime;
	$setsqlarr['points']=$points;
	$setsqlarr['setmeal']=$setmeal;
	$setsqlarr['utype']=$utype;
	write_memberslog($uid,1,3001,$_SESSION['username'],"添加订单，编号{$oid}，金额{$amount}元");
	$userinfo=get_user_info($uid);
		//sendemail
		$mailconfig=get_cache('mailconfig');
		if ($mailconfig['set_order']=="1" && $userinfo['email_audit']=="1" && $amount>0)
		{
		global $_CFG;
		$paymenttpye=get_payment_info($payment_name);
		dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_mail.php?uid={$uid}&key=".asyn_userkey($uid)."&act=set_order&oid={$oid}&amount={$amount}&paymenttpye={$paymenttpye['byname']}");
		}
		//sendemail
		//sms
		$sms=get_cache('sms_config');
		if ($sms['open']=="1" && $sms['set_order']=="1"  && $userinfo['mobile_audit']=="1" && $amount>0)
		{
		global $_CFG;
		$paymenttpye=get_payment_info($payment_name);
		dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_sms.php?uid={$uid}&key=".asyn_userkey($uid)."&act=set_order&oid={$oid}&amount={$amount}&paymenttpye={$paymenttpye['byname']}");
		}
		//微信提醒
		set_order_msg($uid,$oid,$description,$amount);
	return $db->inserttable(table('order'),$setsqlarr,true);
}
//获取单条订单
function get_order_one($uid,$id)
{
	global $db;
	$sql = "select * from ".table('order')." where id =".intval($id)." AND uid = ".intval($uid)."  AND is_paid =1  LIMIT 1 ";
	return $db->getone($sql);
}
//获取充值记录列表
function get_order_all($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	if(isset($offset)&&!empty($perpage))
	{
	$limit=" LIMIT ".$offset.','.$perpage;
	}
	$result = $db->query("SELECT * FROM ".table('order')." ".$get_sql." ORDER BY id DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
	$row['payment_name']=get_payment_info($row['payment_name'],true);
	$row_arr[] = $row;
	}
	return $row_arr;
}
function del_order($uid,$id)
{
	global $db;
	write_memberslog($_SESSION['uid'],2,3002,$_SESSION['username'],"取消订单，订单id{$id}");
	return $db->query("Delete from ".table('order')." WHERE id='".intval($id)."' AND uid=".intval($uid)." AND is_paid=1  LIMIT 1 ");
}
function get_points_rule()
{
	global $db;
	$sql = "select * from ".table('members_points_rule')." WHERE utype='2' ORDER BY id asc";
	return $db->getall($sql);
}
function get_user_report($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('members_log')." ".$get_sql." ORDER BY log_id DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
	$row_arr[] = $row;
	}
	return $row_arr;
}
function m_url_rewrite($alias=NULL,$get=NULL,$rewrite=true)
{
	$url ='';
	if (!empty($get))
	{
		foreach($get as $k=>$v)
		{
		$url .="{$k}={$v}&";
		}
	}
	$url=!empty($url)?"?".rtrim($url,'&'):'';
	return $alias.".php".$url;
	
}
?>