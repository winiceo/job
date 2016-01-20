<?php
 /*
 * 74cms WAP
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
function company_one($id)
{
	global $db;
	$id=intval($id);
	$wheresql=" WHERE id=".$id;
	$sql = "select * from ".table('company_profile').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['contents'] = htmlspecialchars_decode($val['contents'],ENT_QUOTES);
	$jobslist = $db->getall("select * from ".table('jobs')." where `company_id`=".$id." limit 5");
	foreach ($jobslist as $key => $value) {
		$jobslist[$key]['url'] = wap_url_rewrite("jobs-show",array("id"=>$value['id']));
		$jobslist[$key]['r_time']=daterange(time(),$value['refreshtime'],'Y-m-d',"#FF3300");
	}
	$val['jobslist'] = $jobslist;
	return $val;
}
function hunter_one($uid)
{
	global $db;
	$wheresql=" WHERE uid=".intval($uid);
	$sql = "select * from ".table('hunter_profile').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$jobslist = $db->getall("select * from ".table('hunter_jobs')." where `uid`=".$val['uid']." limit 5");
	foreach ($jobslist as $key => $value) {
		$jobslist[$key]['url'] = wap_url_rewrite("hunter-jobs-show",array("id"=>$value['id']));
	}
	$val['jobslist'] = $jobslist;
	return $val;
}
function news_one($id)
{
	global $db;
	$wheresql=" WHERE id=".intval($id);
	$sql = "select * from ".table('article').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['content'] = htmlspecialchars_decode($val['content'],ENT_QUOTES);
	return $val;
}
function notice_one($id)
{
	global $db;
	$wheresql=" WHERE id=".intval($id);
	$sql = "select * from ".table('notice').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['content'] = htmlspecialchars_decode($val['content'],ENT_QUOTES);
	return $val;
}
function jobfair_one($id)
{
	global $db;
	$wheresql=" WHERE id=".intval($id);
	$sql = "select * from ".table('jobfair').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['introduction'] = htmlspecialchars_decode($val['introduction'],ENT_QUOTES);
	return $val;
}
function jobs_one($id)
{
	global $db;
	$id=intval($id);
	$db->query("update ".table('jobs')." set click=click+1 WHERE id='{$id}'  LIMIT 1");
	$db->query("update ".table('jobs_search_hot')." set click=click+1 WHERE id='{$id}'  LIMIT 1");
	$wheresql=" WHERE id='".$id."'";
	$sql = "select * from ".table('jobs').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	if(empty($val)){
		$sql = "select * from ".table('jobs_tmp').$wheresql." LIMIT 1";
		$val=$db->getone($sql);
	}
	if(intval($_SESSION['uid'])>0 && intval($_SESSION['utype'])==2){
		//检查是否看过该职位
		$check = check_viewjobs_log(intval($_SESSION['uid']),$val['id']);
		if(!$check){
			add_viewjobs_log(intval($_SESSION['uid']),$val['id']);
		}
		//检查该职位是否对此会员发起面试邀请,并且此会员没看
		$check_int = wap_check_interview(intval($_SESSION['uid']),$val['id']);
		if($check_int){
			wap_update_interview(intval($_SESSION['uid']),$val['id']);
		}
		//检查是否收藏过
		$check_per_favorites = $db->getone("SELECT * FROM ".table('personal_favorites')." WHERE  personal_uid=".intval($_SESSION['uid'])." AND jobs_id=".intval($val['id']." LIMIT 1 "));
		if ($check_per_favorites)
		{
			$val['is_favorites']=1;
		}
		else
		{
			$val['is_favorites']=0;
		}
	}
	$val['amount']=$val['amount']=="0"?'若干':$val['amount'];
	$profile=company_one($val['company_id']);
	$val['company']=$profile;
	$val['company_nature']=$profile;
	$val['contents'] = htmlspecialchars_decode($val['contents'],ENT_QUOTES);
	$val['company_url']=wap_url_rewrite("company-show",array("id"=>$val['company_id']));
	$sql = "select * from ".table('jobs_contact')." where pid='{$id}' LIMIT 1";
	$contact=$db->getone($sql);
	$val['contact']=$contact;
	if ($val['tag_cn'])
	{
		$tag_cn=explode(',',$val['tag_cn']);
		$val['tag_cn']=$tag_cn;
	}
	else
	{
	$val['tag_cn']=array();
	}
	return $val;
}
function simple_jobs_one($id)
{
	global $db;
	$id=intval($id);
	$wheresql=" WHERE id='".$id."'";
	$sql = "select * from ".table('simple').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['detailed']=strip_tags($val['detailed']);
	return $val;
}
function simple_resume_one($id)
{
	global $db;
	$id=intval($id);
	$wheresql=" WHERE id='".$id."'";
	$sql = "select * from ".table('simple_resume').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['detailed']=strip_tags($val['detailed']);
	return $val;
}
function interest_jobs($topclass,$category,$subclass,$id)
{
	global $db;
	$wheresql = " WHERE id !=".intval($id);
	if(intval($subclass)!=0)
	{
		$wheresql .= " and subclass =".$subclass;
	}
	elseif(intval($category)!=0)
	{
		$wheresql .= " and  category =".$category;
	}
	elseif(intval($topclass)!=0)
	{
		$wheresql .= " and  topclass =".$topclass;
	}
	$list = array();
	$orderbysql = " ORDER BY  refreshtime  desc ";
	$limit = " LIMIT 5 ";
	$result = $db->query("SELECT id,jobs_name,companyname,wage_cn,district_cn,refreshtime FROM ".table('jobs').$wheresql.$orderbysql.$limit);
	while($row = $db->fetch_array($result))
	{
		//刷新时间
		$row['refreshtime_cn']=daterange(time(),$row['refreshtime'],'Y-m-d',"#FF3300");
		$row['url'] = wap_url_rewrite("jobs-show",array("id"=>$row['id']));
		$list[] = $row;
	}
	return $list;
}
function hunter_jobs_one($id)
{
	global $db;
	$id=intval($id);
	$db->query("update ".table('hunter_jobs')." set click=click+1 WHERE id='{$id}'  LIMIT 1");
	$wheresql=" WHERE id='{$id}'";
	$sql = "select * from ".table('hunter_jobs').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	$val['amount']=$val['amount']=="0"?'若干':$val['amount'];
	$profile=hunter_one($val['uid']);
	$val['company']=$profile;
	return $val;
}
function resume_one($id)
{
	global $db;
	$id=intval($id);
	if(intval($_SESSION['uid'])>0 && intval($_SESSION['utype'])==1){
		$check = check_view_log(intval($_SESSION['uid']),$id);
		if(!$check){
			add_view_log(intval($_SESSION['uid']),$id);
		}
	}
	$db->query("update ".table('resume')." set click=click+1 WHERE id='{$id}'  LIMIT 1");
	$wheresql=" WHERE id='{$id}'";
	$sql = "select * from ".table('resume').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	if ($val['display_name']=="2")
	{
		$val['fullname']="N".str_pad($val['id'],7,"0",STR_PAD_LEFT);
		$val['fullname_']=$val['fullname'];		
	}
	elseif($val['display_name']=="3")
	{
		if($val['sex']==1)
		{
			$val['fullname']=cut_str($val['fullname'],1,0,"先生");
		}
		elseif($val['sex']==2)
		{
			$val['fullname']=cut_str($val['fullname'],1,0,"女士");
		}
		$val['fullname_']=$val['fullname'];	
	}
	else
	{
		$val['fullname_']=$val['fullname'];
		$val['fullname']=$val['fullname'];
	}
	if($val['talent']==1){
		$val['talent_'] = "1";
		$val['talent'] = "普通";
	}elseif($val['talent']==2){
		$val['talent_'] = "2";
		$val['talent'] = "高级";
	}
	$val['fullname_3']=cut_str($val['fullname'],1,0,"先生/女士");
	$val['age']=date("Y")-$val['birthdate'];
	$val['education_list']=get_this_education_all($val['uid'],$val['id']);
	//统计教育经历数
	$education_num = 0;
	foreach ($val['education_list'] as $key => $value) 
	{
		$education_num++;
	}
	$val['education_num'] = $education_num;
	$val['work_list']=get_this_work_all($val['uid'],$val['id']);
	//统计工作经历数 和 每份工作时间
	$work_num = 0;
	foreach ($val['work_list'] as $key => $value) 
	{
		$work_num++;
		if($value['todate']==1)
		{
			$value['work_time'] = intval((time() - strtotime($value['startyear']."-".$value['startmonth']."-"."01"))/2592000);
		}
		else
		{
			$value['work_time'] = intval((strtotime($value['endyear']."-".$value['endmonth']."-"."01") - strtotime($value['startyear']."-".$value['startmonth']."-"."01"))/2592000);
		}
		$val['work_list'][$key] = $value;
	}
	$val['work_num'] = $work_num;
	$val['training_list']=get_this_training_all($val['uid'],$val['id']);
	//统计培训经历数
	$training_num = 0;
	foreach ($val['training_list'] as $key => $value) 
	{
		$training_num++;
	}
	$val['training_num'] = $training_num;
	if ($val['tag_cn'])
	{
		$tag_cn=explode(',',$val['tag_cn']);
		$val['tag_cn']=$tag_cn;
		foreach ($val['tag_cn'] as $key => $value) 
		{
			$tag_one['cn'] = $value;
			$tag_one['key'] = $key+1;
			$val['tag_cn_list'][] = $tag_one;
		}
	}
	else
	{
	$val['tag_cn']=array();
	}
	$two_week_time = strtotime("-2 week");
	//两周内被浏览的次数
	$val['click_num']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('view_resume')." WHERE resumeid=".$val['id']." and addtime > ".$two_week_time);
	//两周内被下载的次数
	$val['down_num']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('company_down_resume')." WHERE resume_id=".$val['id']." and down_addtime > ".$two_week_time);
	//两周内投递的次数
	$val['apply_num']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('personal_jobs_apply')." WHERE resume_id=".$val['id']." and apply_addtime > ".$two_week_time);
	//该简历会员信息
	$sql="select mobile_audit from ".table("members")." where uid=$val[uid] ";
	$members_info=$db->getone($sql);
	$val['mobile_audit'] = $members_info['mobile_audit'];
	//判断是否是自己预览
	//个人自己预览
	if($_SESSION['utype'] == '2' && $_SESSION['uid'] == $val['uid'] ){
		$val['isminesee'] = '1';
	}
	return $val;
}
function WapShowMsg($msg_detail, $msg_type = 0, $links = array())
{
	global $smarty;
    if (count($links) == 0)
    {
        $links[0]['text'] = '返回上一页';
        $links[0]['href'] = 'javascript:history.go(-1)';
    }
   $smarty->assign('ur_here',     '系统提示');
   $smarty->assign('msg_type',    $msg_type);
   $smarty->assign('msg_detail',  $msg_detail);
   $smarty->assign('links',       $links);
   $smarty->assign('default_url', $links[0]['href']);
   $smarty->display('wap/wap-showmsg.html');
	exit();
}
function wapmulti($num, $perpage, $curpage, $mpurl)
{
	$lang['home_page']="首页";
	$lang['last_page']="上一页";
	$lang['next_page']="下一页";
	$lang['end_page']="尾页";
	$lang['page']="页";
	$lang['turn_page']="翻页";
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	if($num > $perpage) {
		$page = 5;
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1">'.$lang['home_page'].'</a>' : '').
			($curpage > 1 ? ' <a href="'.$mpurl.'page='.($curpage - 1).'">'.$lang['last_page'].'</a>' : '');

		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? ' '.$i : ' <a href="'.$mpurl.'page='.$i.'">'.$i.'</a>';
		}

		$multipage .= ($curpage < $pages ? ' <a href="'.$mpurl.'page='.($curpage + 1).'">'.$lang['next_page'].'</a>' : '').
			($to < $pages ? ' <a href="'.$mpurl.'page='.$pages.'">'.$lang['end_page'].'</a>' : '');

		$multipage .= $realpages > $page ?
			'<br />'.$curpage.'/'.$realpages.$lang['page']: '';

	}
	return $multipage;
}
function get_this_education_all($uid,$id)
{
	global $db;
	$sql = "SELECT * FROM ".table('resume_education')." WHERE uid='".intval($uid)."' AND pid='".intval($id)."' ";
	return $db->getall($sql);
}
function get_this_work_all($uid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where uid=".intval($uid)." AND pid='".$id."' " ;
	return $db->getall($sql);
}
function get_this_training_all($uid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where uid='".intval($uid)."' AND pid='".intval($id)."'";
	return $db->getall($sql);
}
function get_this_education($uid,$id)
{
	global $db;
	$sql = "SELECT * FROM ".table('resume_education')." WHERE uid='".intval($uid)."' AND id='".intval($id)."' ";
	return $db->getone($sql);
}
function get_this_work($uid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where uid=".intval($uid)." AND id='".$id."' " ;
	return $db->getone($sql);
}
function get_this_training($uid,$id)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where uid='".intval($uid)."' AND id='".intval($id)."'";
	return $db->getone($sql);
}
function check_view_log($uid,$resumeid){
	global $db;
	$result = $db->getone("select * from ".table("view_resume")." where `uid`=".$uid." and `resumeid`=".$resumeid);
	return $result;
}
function add_view_log($uid,$resumeid){
	global $db;
	$setsqlarr['uid'] = $uid;
	$setsqlarr['resumeid'] = $resumeid;
	$setsqlarr['addtime'] = time();
	$db->inserttable(table("view_resume"),$setsqlarr);
}
function wap_get_user_info($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	return $db->getone($sql);
}
function wap_url_rewrite($alias=NULL,$get=NULL,$rewrite=true)
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
function get_member_wap_url($type,$dirname=false)
{
	global $_CFG;
	$type=intval($type);
	if ($type===0) 
	{
	return "";
	}
	elseif ($type===1)
	{
	$return="company/user.php";
	}
	elseif ($type===2) 
	{
	$return="personal/user.php";
	}
	if ($dirname)
	{
	return dirname($return).'/';
	}
	else
	{
	return $return;
	}
}
function wap_get_user_inusername($username){
	global $db;
	$sql = "select * from ".table('members')." where username = '$username' LIMIT 1";
	return $db->getone($sql);
}
function wap_get_user_type($uid)
{
	global $db;
	$sql = "select * from ".table('members_type')." where uid =".intval($uid)." LIMIT 1";
	$user_info=$db->getone($sql);
	return $user_info['utype'];
}
//注册会员
function wap_user_register($username,$password,$member_type=0,$email,$uc_reg=true)
{
	global $db,$timestamp,$_CFG,$online_ip,$QS_pwdhash;
	$member_type=intval($member_type);
	$ck_username=get_user_inusername($username);
	$ck_email=get_user_inemail($email);
	if ($member_type==0) 
	{
	return -1;
	}
	elseif (!empty($ck_username))
	{
	return -2;
	}
	elseif (!empty($ck_email))
	{
	return -3;
	}
	$pwd_hash=randstr();
	$password_hash=md5(md5($password).$pwd_hash.$QS_pwdhash);
	$setsqlarr['username']=$username;
	$setsqlarr['password']=$password_hash;
	$setsqlarr['pwd_hash']=$pwd_hash;
	$setsqlarr['email']=$email;
	$setsqlarr['utype']=intval($member_type);
	$setsqlarr['reg_time']=$timestamp;
	$setsqlarr['reg_ip']=$online_ip;
	$setsqlarr['reg_type']=2;	//来源于WAP
	$insert_id=$db->inserttable(table('members'),$setsqlarr,true);
			if($member_type=="1")
			{
				$setarr["uid"]=$insert_id;
				$db->inserttable(table("members_points"),$setarr);
				$db->inserttable(table("members_setmeal"),$setarr);
			}
			elseif($member_type=="2")
			{
				$setarr["uid"]=$insert_id;
				$db->inserttable(table("members_points"),$setarr);
			}
return $insert_id;
}
function wap_user_login($account,$password,$account_type=1,$uc_login=true,$expire=NULL)
{
	global $timestamp,$online_ip,$QS_pwdhash;
	$usinfo = $login = array();
	$success = false;
	if (preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$account))
	{
		$account_type=2;
	}elseif (preg_match("/^(13|14|15|18)\d{9}$/",$account))
	{
		$account_type=3;
	}
	if ($account_type=="1")
	{
		$usinfo=get_user_inusername($account);
	}
	elseif ($account_type=="2")
	{
		$usinfo=get_user_inemail($account);
	}
	elseif ($account_type=="3")
	{
		$usinfo=get_user_inmobile($account);
	}
	if (!empty($usinfo))
	{
		$pwd_hash=$usinfo['pwd_hash'];
		$usname=$usinfo['username'];
		$pwd=md5(md5($password).$pwd_hash.$QS_pwdhash);
		if ($usinfo['password']==$pwd)
		{
		wap_update_user_info($usinfo['uid'],true,true,$expire);
		$login['qs_login']=get_member_wap_url($usinfo['utype']);
		$success=true;
		}
		else
		{
		$usinfo='';
		$success=false;
		}
	}
	return $login;	
}
//手机登录
function user_login_new($account,$account_type=1,$uc_login=true,$expire=NULL)
{
	global $timestamp,$online_ip,$QS_pwdhash;
	$usinfo = $login = array();
	$success = false;
	if ($account_type=="1")
	{
		$usinfo=get_user_inusername($account);
	}
	elseif ($account_type=="2")
	{
		$usinfo=get_user_inemail($account);
	}
	elseif ($account_type=="3")
	{
		$usinfo=get_user_inmobile($account);
	}
	if (!empty($usinfo))
	{
		wap_update_user_info($usinfo['uid'],true);
		return true;
	}
	return false;
}
function wap_update_user_info($uid,$record=true,$setcookie=true,$cookie_expire=NULL)
 {
 	global $timestamp, $online_ip,$db,$QS_cookiepath,$QS_cookiedomain,$_CFG;//3.4升级修改 引入变量$_CFG
	$user = wap_get_user_inid($uid);
	if (empty($user))
	{
	return false;
	}
	else
	{
	 	$_SESSION['uid'] = intval($user['uid']);
	 	$_SESSION['username'] = $user['username'];
		$_SESSION['utype']=intval($user['utype']);
		if(intval($user['utype'])==2 && $user['avatars']!='')
		{
			$_SESSION['avatars'] = $_CFG['site_dir']."data/avatar/100/".$user['avatars'];
		}
	}
	if ($setcookie)
	{
		$expire=intval($cookie_expire)>0?time()+3600*24*$cookie_expire:0;
		setcookie('QS[uid]',$user['uid'],$expire,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[username]',$user['username'],$expire,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[password]',$user['password'],$expire,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[utype]',$user['utype'], $expire,$QS_cookiepath,$QS_cookiedomain);
	}
	if ($record)
	{
    	$last_login_time = $timestamp;
    	$remind_email_time = strtotime("+".$_CFG['user_unlogin_time']." day");
		$last_login_ip = $online_ip;
		$sql = "UPDATE ".table('members')." SET last_login_time = '$last_login_time', remind_email_time = '$remind_email_time', last_login_ip = '$last_login_ip' WHERE uid='{$_SESSION['uid']}'  LIMIT 1";
		$db->query($sql);
	}
	return true;
 }
 function wap_get_user_inid($uid)
{
	global $db;
	$uid=intval($uid);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	return $db->getone($sql);
}
// 获取个人简历列表
function wap_get_user_resume($uid)
{
	global $db;
	$uid=intval($uid);
	$sql="select * from ".table("resume")." where uid=$uid";
	return $db->getall($sql);
}
//增加意向职位
function wap_add_resume_jobs($pid,$uid,$str)
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
//增加意向地区
function wap_add_resume_district($pid,$uid,$str)
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
// 添加意向行业
function wap_add_resume_trade($pid,$uid,$str)
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
// 绑定帐号自动登录
function wap_weixin_logon($fromUsername,$expire=null)
{
	global $db;
	if($fromUsername){
	$sql ="select uid from ".table('members')." where weixin_openid = '{$fromUsername}' LIMIT 1";
	$usinfo = $db->getone($sql);
	wap_update_user_info($usinfo['uid'],true,true,$expire);
	unset($fromUsername);
	}
}
function check_viewjobs_log($uid,$jobsid){
	global $db;
	$result = $db->getone("select id from ".table("view_jobs")." where `uid`=".$uid." and `jobsid`=".$jobsid);
	return $result;
}
function wap_check_interview($uid,$jobsid){
	global $db;
	$result = $db->getone("select did from ".table("company_interview")." where `personal_look`=1 and  `resume_uid`=".$uid." and `jobs_id`=".$jobsid);
	return $result;
}
function wap_update_interview($uid,$jobsid){
	global $db;
	$setsqlarr['personal_look'] = 2;
	$db->updatetable(table("company_interview"),$setsqlarr," `resume_uid`=".$uid." and `jobs_id`=".$jobsid );
}
function add_viewjobs_log($uid,$jobsid){
	global $db;
	$setsqlarr['uid'] = $uid;
	$setsqlarr['jobsid'] = $jobsid;
	$setsqlarr['addtime'] = time();
	$db->inserttable(table("view_jobs"),$setsqlarr);
}
function wap_get_apply_jobs($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT {$offset},{$perpage}";
	$selectstr=" a.*,r.id,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.major_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.nature_cn,r.birthdate,r.addtime,r.refreshtime,r.display";
	$result = $db->query("SELECT {$selectstr} FROM ".table('personal_jobs_apply')." as a {$get_sql} ORDER BY a.personal_look ASC , a.did DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		if ($row['display_name']=="2")
		{
		$row['fullname']="N".str_pad($row['id'],7,"0",STR_PAD_LEFT);
		}
		elseif ($row['display_name']=="3")
		{
			if($row['sex']==1){
				$row['fullname']=cut_str($row['fullname'],1,0,"先生");
			}
			elseif($row['sex']==2){
				$row['fullname']=cut_str($row['fullname'],1,0,"女士");
			}
		}
		$row['jobs_name_']=cut_str($row['jobs_name'],7,0,"...");
		$row['resume_url']=wap_url_rewrite("resume-show",array("id"=>$row['resume_id']));
		$row['jobs_url']=wap_url_rewrite("jobs-show",array("id"=>$row['id']));
		$y=date("Y");
		$row['age']=$y-$row['birthdate'];
		/*
			获取简历标记
		*/
		$row_state=get_resume_state($_SESSION['uid'],$row['resume_id']);
		$row['resume_state']=$row_state['resume_state'];
		$row['resume_state_cn']=$row_state['resume_state_cn'];
		$row_arr[] = $row;
	}
	return $row_arr;
}
//邀请记录列表
function wap_get_interview($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	if(isset($offset)&&!empty($perpage)) $limit=" LIMIT ".$offset.','.$perpage;
	$selectstr="i.*,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.major_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.nature_cn,r.birthdate,r.addtime,r.refreshtime";
	$result = $db->query("SELECT  {$selectstr}  FROM ".table('company_interview')." as i {$get_sql} ORDER BY  i.did DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
		$row['fullname_']=$row['fullname'];
		$row['fullname']=cut_str($row['fullname'],5,0,"...");
		$row['jobs_name_']=$row['jobs_name'];
		$row['jobs_name']=cut_str($row['jobs_name'],10,0,"...");
		$row['resume_url']=wap_url_rewrite("resume-show",array("id"=>$row['resume_id']));
		$row['intention_jobs']=cut_str($row['intention_jobs'],30,0,"...");
		$y=date("Y");
		$row['age']=$y-$row['birthdate'];
		/* 教育经历 培训经历 */
		$row['resume_education_list']=get_resume_education($row['ruid'],$row['resume_id']);
		$row['resume_work_list']=get_resume_work($row['ruid'],$row['resume_id']);
		$row_arr[] = $row;
	}
	return $row_arr;
}
//已下载的简历列表
function wap_get_down_resume($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT ".intval($offset).','.intval($perpage);
	$selectstr=" d.*,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.major_cn,r.nature_cn,r.birthdate,r.addtime,r.refreshtime ";
	$result = $db->query("SELECT ".$selectstr." FROM ".table('company_down_resume')." as d {$get_sql} ORDER BY d.down_addtime DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
		$row['fullname_']=$row['fullname'];
		$row['fullname']=cut_str($row['fullname'],4,0,"...");
		$row['resume_url']=wap_url_rewrite("resume-show",array("id"=>$row['resume_id']));
		$row['intention_jobs_']=$row['intention_jobs'];
		$row['intention_jobs']=cut_str($row['intention_jobs'],30,0,"...");
		$y=date("Y");
		$row['age']=$y-$row['birthdate'];
		/* 教育经历 培训经历 */
		$row['resume_education_list']=get_resume_education($row['ruid'],$row['resume_id']);
		$row['resume_work_list']=get_resume_work($row['ruid'],$row['resume_id']);
		/*
			获取简历标记
		*/
		$row_state=get_resume_state($_SESSION['uid'],$row['resume_id']);
		$row['resume_state']=$row_state['resume_state'];
		$row['resume_state_cn']=$row_state['resume_state_cn'];
		$row_arr[] = $row;
	}
	return $row_arr;
}
//获取企业人才库
function wap_get_favorites($offset,$perpage,$get_sql= '')
{
	global $db;
	$row_arr = array();
	if(isset($offset)&&!empty($perpage)) $limit=" LIMIT ".$offset.','.$perpage;
	$selectstr="f.*,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.nature_cn,r.birthdate,r.major_cn,r.addtime,r.refreshtime";
	$result = $db->query("SELECT ".$selectstr."  FROM ".table('company_favorites')." AS f ".$get_sql." ORDER BY f.did DESC ".$limit);
	while($row = $db->fetch_array($result))
	{
		$row['intention_jobs_']=$row['intention_jobs'];
		$row['intention_jobs']=cut_str($row['intention_jobs'],30,0,"...");
		$row['resume_url']=wap_url_rewrite("resume-show",array("id"=>$row['resume_id']));
		if ($row['display_name']=="2")
		{
		$row['fullname']="N".str_pad($row['resume_id'],7,"0",STR_PAD_LEFT);
		}
		elseif ($row['display_name']=="3")
		{
			if($row['sex']==1){
				$row['fullname']=cut_str($row['fullname'],1,0,"先生");
			}
			elseif($row['sex']==2){
				$row['fullname']=cut_str($row['fullname'],1,0,"女士");
			}
		}
		$y=date("Y");
		$row['age']=$y-$row['birthdate'];
		/*
			获取简历标记
		*/
		$row_state=get_resume_state($_SESSION['uid'],$row['resume_id']);
		$row['resume_state']=$row_state['resume_state'];
		$row['resume_state_cn']=$row_state['resume_state_cn'];
		$row_arr[] = $row;
	}
	return $row_arr;
}
//查询"谁看过我的职位"信息
function wap_get_view_users($offset,$perpage,$get_sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT {$offset},{$perpage}";
	$result = $db->query("SELECT * FROM ".table('view_jobs')." {$get_sql} ORDER BY `id` DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		$personal_userinfo = $db->getone("select `username` from ".table('members')." where `uid`=".$row['uid']);
		$row['username'] = $personal_userinfo['username'];
		$jobsname = $db->getone("select `jobs_name` from ".table('jobs')." where `id`=".$row['jobsid']);
		if(empty($jobsname)){
			$jobsname = $db->getone("select `jobs_name` from ".table('jobs_tmp')." where `id`=".$row['jobsid']);
		}
		$row['jobs_name'] = $jobsname['jobs_name'];
		$resumes = $db->getall("select * from ".table('resume')." where `uid`=".$row['uid'].$wheresql);
		if(empty($resumes)){
			continue;
		}
		//将谁看过我的职位数据的id/addtime循环简历的时候要保存一份
		$did=$row['id'];
		$addtime=date('Y-m-d',$row['addtime']);
		foreach ($resumes as $key1 => $value1) {
			$value1['resume_url']=wap_url_rewrite("resume-show",array("id"=>$value1['id']));
			$value1["jobs_name"]=$row['jobs_name'];
			$value1["fullname"]=$value1['fullname'];
			$value1["age"]=date('Y')-$value1['birthdate'];
			//判断是否收藏过
			if (check_favorites($value1['id'],$_SESSION['uid']))
			{
			$value1['is_favorites']=1;
			}
			/*
			获取简历标记
			*/
			$row_state=get_resume_state($_SESSION['uid'],$value1['id']);
			$value1['resume_state']=$row_state['resume_state'];
			$value1['resume_state_cn']=$row_state['resume_state_cn'];
			$row=$value1;
		}
		$row["did"]=$did;
		$row["addtime"]=$addtime;
		$row_arr[] = $row;
	}
	return $row_arr;
}
//查询"浏览过的简历"信息
function wap_get_my_attention($offset,$perpage,$get_sql= '')
{
	global $db;
	$limit=" LIMIT {$offset},{$perpage}";
	$selectstr=" a.*,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.nature_cn,r.major_cn,r.birthdate,r.refreshtime";
	$result = $db->query("SELECT {$selectstr} FROM ".table('view_resume')." as a {$get_sql} ORDER BY a.id DESC {$limit}");
	while($row = $db->fetch_array($result))
	{
		$row['resume_url'] = wap_url_rewrite("resume-show",array('id'=>$row['resumeid']));
		if($row['display_name']=="3")
		{
			if($row['sex']==1)
			{
				$row['fullname']=cut_str($row['fullname'],1,0,"先生");
			}
			elseif($row['sex']==2)
			{
				$row['fullname']=cut_str($row['fullname'],1,0,"女士");
			}
			
		}elseif($row['display_name']=="2"){
			$row['fullname']="N".str_pad($row['resumeid'],7,"0",STR_PAD_LEFT);
		}
		//判断是否收藏过
		if (check_favorites($row['resumeid'],$_SESSION['uid']))
		{
			$row['is_favorites']=1;
		}
		$y=date("Y");
		$row['age']=$y-$row['birthdate'];
		$row['addtime']=date('Y-m-d',$row['addtime']);
		/*
			获取简历标记
		*/
		$row_state=get_resume_state($_SESSION['uid'],$row['resumeid']);
		$row['resume_state']=$row_state['resume_state'];
		$row['resume_state_cn']=$row_state['resume_state_cn'];
		$row_arr[] = $row;
	}
	return $row_arr;
}

?>