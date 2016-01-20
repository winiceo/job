<?php
 /*
 * 74cms 生成word
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
unset($dbhost,$dbuser,$dbpass,$dbname);
$uid=intval($_GET['uid']);//简历所属会员的uid
$id=intval($_GET['resume_id']);
if ($_SESSION['uid']=='' || $_SESSION['username']=='')
{
	$resume_url=url_rewrite('QS_resumeshow',array('id'=>$id));
    header("Location:".url_rewrite('QS_login')."?url={$resume_url}");
	exit();
}
if(($_SESSION['utype']=='2' && $_SESSION['uid']==$uid) || $_SESSION['utype']=='1'){
	$flag=true;
}else{
	$flag=false;
}
if(!$flag) {showmsg('您没有权限！只有个人用户和企业用户可以转换简历',1);exit();}
$wheresql=" WHERE  id='{$id}'  AND uid='{$uid}' ";
$sql = "select * from ".table('resume').$wheresql." LIMIT  1";
$val=$db->getone($sql);
if ($val)
{
	$val['education_list']=get_this_education($val['uid'],$val['id']);
	$val['work_list']=get_this_work($val['uid'],$val['id']);
	$val['training_list']=get_this_training($val['uid'],$val['id']);
	$val['age']=date("Y")-$val['birthdate'];
	$val['tagcn']=preg_replace("/\d+/", '',$val['tag']);
	$val['tagcn']=preg_replace('/\,/','',$val['tagcn']);
	$val['tagcn']=preg_replace('/\|/','&nbsp;&nbsp;&nbsp;',$val['tagcn']);
 	
	if(intval($_GET['apply'])==1){
		$set_apply = 1;
		$apply = $db->getone("select * from ".table('personal_jobs_apply')." where `resume_id`=".$val['id']);
		$val['jobs_name'] = $apply['jobs_name'];
	}else{
		$set_apply = 0;
	}
	// 最近登录时间
	$userinfo=$db->getone("select last_login_time from ".table('members')." where uid=$val[uid]");
	$last_login_time=date('Y-m-d',$userinfo['last_login_time']);
	$htm='<!doctype html>
<html lang="en">
<head>
	<meta charset="gbk">
	<title>Document</title>
</head>
<body>
<table class="MsoNormalTable"  align="center" style="width:426.4pt;border-collapse: collapse;" cellpadding="8" cellspacing="0">
	<tbody>
	<table width="700" border="0" align="center" cellpadding="8" cellspacing="0">
		<tr>
			<td width="99" align="center"  valign="center" >简历标题</td>
			<td width="146" align="center"  valign="center" >'.$val['title'].'</td>
			<td width="94" align="center"  valign="center" >最近登录</td>
			<td width="102" align="center"  valign="center" >'.$last_login_time.'</td>
			<td width="126"align="center" valign="center" ><img width="130" height="40" src="'.$_CFG["site_domain"].$_CFG["upfiles_dir"].$_CFG["web_logo"].'" alt="{#$QISHI.site_name#}" border="0" align="absmiddle"></td>
		</tr>
	</table>';
// if($set_apply==1){
// 	if($val['jobs_name']!=""){
// 		$htm .= '<tr>
// 	    <td colspan="1">申请职位：'.$val["jobs_name"].'</td>
// 	  </tr>';
// 	}
// }
//企业查看收到的简历无需下载  0->关闭  1->开启
$show=false;
if($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1' && $_CFG['showapplycontact']=='1'){
	$has = $db->getone("select 1 from ".table('personal_jobs_apply')." where company_uid=".intval($_SESSION['uid'])." and resume_id=".$val['id']);
	if($has){
		$show = true;
	}
}
if($show==false)
{
	if ($val['display_name']=="2")
	{
		$val['fullname_']=$val['fullname'];	
		$val['fullname']="N".str_pad($val['id'],7,"0",STR_PAD_LEFT);	
	}
	elseif($val['display_name']=="3")
	{
		$val['fullname_']=$val['fullname'];	
		if($val['sex']==1)
		{
			$val['fullname']=cut_str($val['fullname'],1,0,"先生");
		}
		elseif($val['sex']==2)
		{
			$val['fullname']=cut_str($val['fullname'],1,0,"女士");
		}
	}
	else
	{
		$val['fullname_']=$val['fullname'];
		$val['fullname']=$val['fullname'];
	}
	// 个人信息 联系方式
	if($_CFG['showresumecontact']=='1' || $_CFG['showresumecontact']=='0')
	{
		$val['fullname']=$val['fullname'];
		$val['telephone']=$val['telephone'];
		$val['email']=$val['email'];
	}
	elseif($_CFG['showresumecontact']=='2')//联系方式：会员下载后可见
	{
		if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1')
		{
			$sql = "select did from ".table('company_down_resume')." WHERE company_uid = {$_SESSION['uid']} AND resume_id='{$val[id]}' LIMIT 1";
			$info=$db->getone($sql);
			if (!empty($info))
			{
				$val['fullname']=$val['fullname_'];
				$val['telephone']=$val['telephone'];
				$val['email']=$val['email'];
			}
			else
			{
				$val['fullname']=$val['fullname'];
				$val['telephone']="下载后可见";
				$val['email']="下载后可见";
			}
		}elseif($_SESSION['utype']=='2' && $_SESSION['uid']==$val['uid'])
		{
			$val['fullname']=$val['fullname_'];
			$val['telephone']=$val['telephone'];
			$val['email']=$val['email'];
		}else{
				$val['fullname']=$val['fullname'];
				$val['telephone']="下载后可见";
				$val['email']="下载后可见";
		}
	}
}
if ($val['photo']=="1")
{
$val['photosrc']=$_CFG["site_domain"].$_CFG['resume_photo_dir_thumb'].$val['photo_img'];
}
else
{
$val['photosrc']=$_CFG["site_domain"].$_CFG['resume_photo_dir_thumb']."no_photo.gif";
}
$htm.='
	<table width="700" align="center" cellpadding="8" cellspacing="0" class="MsoNormalTable">
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">姓&nbsp;&nbsp;&nbsp;&nbsp;名</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="146" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['fullname'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="94" valign="center" colspan="2" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">性&nbsp;&nbsp;&nbsp;别</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['sex_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="126" valign="center" rowspan="5" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"><img width="98" height="130" src="'.$val['photosrc'].'" /></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">&nbsp;</span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">年&nbsp;&nbsp;&nbsp;龄</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="146" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['age'].'岁</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="94" valign="center" colspan="2" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">婚姻状况</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['marriage_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">籍&nbsp;&nbsp;&nbsp;&nbsp;贯</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="146" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['householdaddress'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="94" valign="center" colspan="2" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">学&nbsp;&nbsp;&nbsp;历</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['education_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">专&nbsp;&nbsp;&nbsp;&nbsp;业</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="146" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['major_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="94" valign="center" colspan="2" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">电&nbsp;&nbsp;&nbsp;话</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['telephone'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">目前所在地</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="342" valign="center" colspan="4" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['residence'].'</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">期望行业</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="240" valign="center" colspan="3" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['trade_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;">计算机</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">期望职位</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="126" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['intention_jobs'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">期望地区</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="240" valign="center" colspan="3" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['district_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">期望薪资</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="126" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['wage_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
				<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">工作经验</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="240" valign="center" colspan="3" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['experience_cn'].'</span><span style="font-family:宋体;font-size:9.0000pt;">计算机</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="102" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">电子邮箱</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="126" valign="center" style="border:1px solid windowtext;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$val['email'].'</span><span style="font-family:宋体;font-size:9.0000pt;"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">自我评价</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="469" valign="center" colspan="5" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9pt;">'.nl2br($val['specialty']).'</span>
				</p>
			</td>
		</tr>
		';
if($val['work_list'])
{	
	$work_num =count($val['work_list']);
	$i=0;
	foreach ($val['work_list'] as $wli)
	{  
		$htm.='<tr>';
		if($i==0)
		{
			$htm.='<td width="99" valign="center" rowspan="'.$work_num.'" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">工作经历</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>';
		}
		$htm.='
		<td width="187" valign="center" align="center" colspan="2" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$wli['startyear'].'.'.$wli['startmonth'].'-'.$wli['endyear'].'.'.$wli['endmonth'].'</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$wli['companyname'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$wli['jobs'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$wli['achievements'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td></tr>';
			$i++;
	}
}
// 培训经历
if($val['training_list'])
{
	$train_num =count($val['training_list']);
	$i=0;
	foreach ($val['training_list'] as $tli)
	{
		$htm.='<tr>';
		if($i==0)
		{
			$htm.='<td width="99" valign="center" rowspan="'.$train_num.'" style="border:1px solid windowtext;background:#E5E5E5;">
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">培训经历</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>';
		}
		$htm.='
		<td width="187" valign="center" align="center" colspan="2" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$tli['startyear'].'.'.$tli['startmonth'].'-'.$tli['endyear'].'.'.$tli['endmonth'].'</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$tli['agency'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$tli['course'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$tli['description'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td></tr>';
			$i++;
	}
	
}
// 教育经历
if($val['education_list']){
	$edu_num =count($val['education_list']);
	$i=0;
	foreach ($val['education_list'] as $eli)
	{
		$htm.='
		<tr>';
		if($i==0){$htm.='
		<td width="99" valign="center" style="border:1px solid windowtext;background:#E5E5E5;" rowspan='.$edu_num.'>
				<p class="MsoNormal" style="text-align:center;">
					<span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;">教育经历</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
		</td>';}

		$htm.='<td width="187" valign="center"align="center" colspan="2" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$eli['startyear'].'.'.$eli['startmonth'].'-'.$eli['endyear'].'.'.$eli['endmonth'].'</span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$eli['school'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$eli['speciality'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td>
			<td width="281" valign="center" align="center" colspan="1" style="border:1px solid windowtext;">
				<p class="MsoNormal">
					<span style="font-family:宋体;font-size:9.0000pt;">'.$eli['education_cn'].'</span></span><span style="font-family:宋体;font-weight:bold;font-size:9.0000pt;"></span>
				</p>
			</td></tr>';
			$i++;
	}
}

$htm.="</tbody></table><div align=\"center\"><br />
	<a title=\"{$_CFG['site_name']}\" href=\"{$_CFG['site_domain']}{$_CFG['site_dir']}\">{$_CFG['site_name']}</a>
</div>
</body>
</html>";
header("Cache-Control: no-cache, must-revalidate"); 
header("Pragma: no-cache");   
header("Content-Type: application/doc"); 
header("Content-Disposition:attachment; filename={$val['fullname']}的个人简历.doc"); 
echo $htm;
}
else
{
 showmsg('简历不存在！',1);
 exit();
}
function get_this_education($uid,$pid)
{
	global $db;
	$sql = "SELECT * FROM ".table('resume_education')." WHERE uid='".intval($uid)."' AND pid='".intval($pid)."' ";
	return $db->getall($sql);
}
function get_this_work($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_work')." where uid=".intval($uid)." AND pid='".$pid."' " ;
	return $db->getall($sql);
}
function get_this_training($uid,$pid)
{
	global $db;
	$sql = "select * from ".table('resume_training')." where uid='".intval($uid)."' AND pid='".intval($pid)."'";
	return $db->getall($sql);
}
function get_user_setmealt($uid)
{
	global $db;
	$sql = "select * from ".table('members_setmeal')."  WHERE uid=".intval($uid)." AND  effective=1 LIMIT 1";
	return $db->getone($sql);
}
?>