<?php
 /*
 * 74cms 计划任务 用户若一个月内没有登录则邮箱提醒
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
	//可以实现当客户端关闭后仍然可以执行PHP代码
	ignore_user_abort(true);
	global $_CFG , $db;
	//判断邮箱是否配置
	$mailconfig=get_cache('mailconfig');
	//最新简历
	$html_resume = '<table width="700" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;color:#555; font:16px/26px \'微软雅黑\',\'宋体\',Arail; ">
	    	<tbody><tr>
	        	<td style="height:62px; background-color:#FCFCFC; padding:10px 0 0 10px;">
	            	<a target="_blank" href="'.$_CFG['site_domain'].$_CFG['site_dir'].'"><img src="'.$_CFG['site_domain'].$_CFG['upfiles_dir'].$_CFG['web_logo'].'" width="200" height="45" style="border:none;"/></a>
	            </td>
	        </tr>
	        <tr style="background-color:#fff;">
	        	<td style="padding:30px 38px;">
	            	<div>亲爱的用户，你好!</div>';
	$html_resume .='<div style="text-indent:2em;">由于您长时间未登录<a style="color:#017FCF;" href="'.$_CFG['site_domain'].$_CFG['site_dir'].'" target="_blank">'.$_CFG['site_name'].'</a>，经我们精心的挑选，现将筛选的最新简历发送给你，希望我们的邮件能够对你有所帮助。祝职业更上一层楼！</div><div style="border-bottom:1px solid #e6e6e6; font-weight:bold; margin:20px 0 0 0; padding-bottom:5px;">以下是最新的简历：</div>
	            	<ul style="list-style:none; margin:0; padding:0;">';
	$resume = $db->getall("select * from ".table('resume')." order by addtime desc limit 5 ");
	if($resume){
		foreach ($resume as $k=>$v) {
			$resume_url = url_rewrite('QS_resumeshow',array('id'=>$v['id']));
			// $avatars = $db->getone("select avatars from ".table('members')." where uid =".$v['uid']);
			if($v['photo_img']==""){
				$company_logo = $_CFG['site_domain'].$_CFG['site_dir']."data/photo/thumb/no_photo.gif";
			}else{
				$company_logo = $_CFG['site_domain'].$_CFG['site_dir']."data/photo/thumb/".$v['photo_img'];
			}
			$html_resume .='<li style="list-style:none;padding:15px 10px 15px 0;border-bottom:1px solid #e6e6e6; overflow:hidden;">
				    <a target="_blank" href="'.$resume_url.'">
				    <img width="80" height="80" style="border:none; float:left; margin-right:15px;" src="'.$company_logo.'">
				    </a>
				    <div>
				    <a target="_blank" style="float:left; color:#017FCF; text-decoration:underline;" href="'.$resume_url.'">
				    '.$v['fullname'].'
				    </a>
				    <a target="_blank" style="float:right; color:#017FCF; text-decoration:underline;" href="'.$resume_url.'">
				    查看详情
				    </a><br>
				    <div>学历：'.$v["education_cn"].'</div><br>
				    <div>工作经验：'.$v["experience_cn"].'</div>
				    </div>
				    </li>';
		}
	}
	$html_resume .='</ul>
	                <a target="_blank" style="float:right; text-decoration:underline; font-weight:700; margin:15px 0;color:#017FCF;" href="'.$_CFG["site_domain"].$_CFG["site_dir"].'resume/resume-list.php">查看所有简历</a>
	            </td>
	        </tr>';
	$html_resume .='
	        <tr>
	        	<td style="text-align:center; color:#c9cbce; font-size:14px; padding:5px 0;">公司网址：<a style="color:#017FCF;" target="_blank" href="'.$_CFG["site_domain"].$_CFG["site_dir"].'">'.$_CFG["site_domain"].$_CFG["site_dir"].'</a>   </td>
	        </tr>
	    </tbody></table>';

	//最新职位
	$html_jobs = '<table width="700" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;color:#555; font:16px/26px \'微软雅黑\',\'宋体\',Arail; ">
	    	<tbody><tr>
	        	<td style="height:62px; background-color:#FCFCFC; padding:10px 0 0 10px;">
	            	<a target="_blank" href="'.$_CFG['site_domain'].$_CFG['site_dir'].'"><img src="'.$_CFG['site_domain'].$_CFG['upfiles_dir'].$_CFG['web_logo'].'" width="200" height="45" style="border:none;"/></a>
	            </td>
	        </tr>
	        <tr style="background-color:#fff;">
	        	<td style="padding:30px 38px;">
	            	<div>亲爱的用户，你好!</div>';
	$html_jobs .='<div style="text-indent:2em;">由于您长时间未登录<a style="color:#017FCF;" href="'.$_CFG['site_domain'].$_CFG['site_dir'].'" target="_blank">'.$_CFG['site_name'].'，经我们精心的挑选，现将筛选的最新职位发送给你，希望我们的邮件能够对你有所帮助。祝职业更上一层楼！</div><div style="border-bottom:1px solid #e6e6e6; font-weight:bold; margin:20px 0 0 0; padding-bottom:5px;">以下是最新的职位：</div>
	            	<ul style="list-style:none; margin:0; padding:0;">';
	$jobs = $db->getall("select * from ".table('jobs')." order by addtime desc limit 5 ");
	if($jobs){
		foreach ($jobs as $k=>$v) {
			$jobs_url = url_rewrite('QS_jobsshow',array('id'=>$v['id']));
			$company_url = url_rewrite('QS_companyshow',array('id'=>$v['company_id']));
			$logo = $db->getone("select logo,district_cn from ".table('company_profile')." where id=".$v['company_id']);
			if($logo['logo']==""){
				$company_logo = $_CFG['site_domain'].$_CFG['site_dir']."data/logo/no_logo.gif";
			}else{
				$company_logo = $_CFG['site_domain'].$_CFG['site_dir']."data/logo/".$logo['logo'];
			}
			$html_jobs .='<li style="list-style:none;padding:15px 10px 15px 0;border-bottom:1px solid #e6e6e6; overflow:hidden;">
				    <a target="_blank" href="'.$company_url.'">
				    <img width="80" height="80" style="border:none; float:left; margin-right:15px;" src="'.$company_logo.'">
				    </a>
				    <div>
				    <a target="_blank" style="float:left; color:#017FCF; text-decoration:underline;" href="'.$jobs_url.'">
				    '.$v['jobs_name'].'
				    </a>
				    <a target="_blank" style="float:right; color:#017FCF; text-decoration:underline;" href="'.$jobs_url.'">
				    查看详情
				    </a><br>
				    <div style="font-weight:700;">'.$v['companyname'].'</div>
				    <div>工作地区：'.$v["district_cn"].'</div>
				    </div>
				    </li>';
		}
	}
	$html_jobs .='</ul>
	                <a target="_blank" style="float:right; text-decoration:underline; font-weight:700; margin:15px 0;color:#017FCF;" href="'.$_CFG["site_domain"].$_CFG["site_dir"].'jobs/jobs-list.php">查看所有职位</a>
	            </td>
	        </tr>';
	$html_jobs .='
	        <tr>
	        	<td style="text-align:center; color:#c9cbce; font-size:14px; padding:5px 0;">公司网址：<a style="color:#017FCF;" target="_blank" href="'.$_CFG["site_domain"].$_CFG["site_dir"].'">'.$_CFG["site_domain"].$_CFG["site_dir"].'</a>   </td>
	        </tr>
	    </tbody></table>';


	$time = intval(time());
	if(empty($_CFG['user_unlogin_time']))
	{
		$_CFG['user_unlogin_time'] = 30;
	}
	$last_time = strtotime("-".$_CFG['user_unlogin_time']." day");
	//查询会员最后一次登录时间情况
	$result = $db->query("SELECT * FROM ".table('members')." WHERE last_login_time < $last_time && remind_email_time < $time ");
	while($row = $db->fetch_array($result))
	{
		$email = $row['email'];
		if($row['email_audit']==0){
			continue;
		}
		//企业
		if($row['utype']=='1'){
			smtp_mail($email,$_CFG['site_name']."向您发送了长时间未登录网站提醒",$html_resume,$mailconfig['smtpfrom'],$_CFG['site_name']);
		}
		//个人
		elseif($row['utype']=='2'){
			continue;
			smtp_mail($email,$_CFG['site_name']."向您发送了长时间未登录网站提醒",$html_jobs,$mailconfig['smtpfrom'],$_CFG['site_name']);
		}
		//更新邮箱提醒时间
		$remind_email_time = strtotime("+".$_CFG['user_unlogin_time']." day");
		$sql = "UPDATE ".table('members')." SET remind_email_time = '$remind_email_time' WHERE uid='{$row['uid']}'  LIMIT 1";
		$db->query($sql);
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