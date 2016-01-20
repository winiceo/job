<?php
 /*
 * 74cms ajax 联系方式
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(dirname(__FILE__)).'/include/plus.common.inc.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : ''; 

if($act == 'jobs_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$jobs_one=$db->getone("select * from ".table("jobs")." where id=$id ");
		$jobs_tmp=$db->getone("select * from ".table("jobs_tmp")." where id=$id ");
		$jobs = empty($jobs_one)?$jobs_tmp:$jobs_one;
		$show=false;
		if($_CFG['showjobcontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showjobcontact']=='1')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>个人在注册并登录后才能查看企业的联系方式，现在<a href="javascript:void(0);" class="ajax_user_login" style="color:#ff7400">[立即登录]</a>或者<a href="'.$_CFG['site_dir'].'user/user_reg.php" style="color:#ff7400">[免费注册]</a></p>
								</div>';
			}
		}
		elseif($_CFG['showjobcontact']=='2')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
				$val=$db->getone("select uid from ".table('resume')." where uid='{$_SESSION['uid']}' LIMIT 1");
			 	if (!empty($val))
				{
				$show=true;
				}
				else
				{
				$show=false;
				$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>您没有发布简历或者简历无效，发布简历后才可以查看联系方式。<a href="'.get_member_url($_SESSION['utype'],true).'personal_resume.php?act=resume_list">[查看我的简历]</a></p>
								</div>';
				}
			}
			else
			{
			$show=false;
			$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>个人在注册并登录后才能查看企业的联系方式，现在<a href="javascript:void(0);" class="ajax_user_login" style="color:#ff7400">[立即登录]</a>或者<a href="'.$_CFG['site_dir'].'user/user_reg.php" style="color:#ff7400">[免费注册]</a></p>
								</div>';
			}
		}
		if($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1' && $show==false)
		{
			if($jobs['uid']==$_SESSION['uid'])
			{
				$show=true;
			}
			else
			{
				$show=false;
			}
		}
		if ($show)
		{
		$sql = "select * from ".table('jobs_contact')." where pid='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_job']=='2')
			{
			$hashstr=$_GET['hashstr'];
			$token=md5($val['contact'].$id.$val['telephone']);
			$contact=$val['contact_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=jobs_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></li>":"企业设置不对外公开";
			$telephone=$val['telephone_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=jobs_contact&type=2&id={$id}&token={$token}&hashstr={$hashstr}\"  border=\"0\" align=\"absmiddle\"/><a style=\"color:#017fcf\" id=\"tel_show_pic\" href=\"javascript:;\" >[查看]</a> <span  id=\"show_detail\" style='color:#666;display:none'>[联系我时请说明是在&nbsp;".$_CFG['site_name']."&nbsp;上看到的]</span>":"企业设置不对外公开";
			$landline_tel="<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=jobs_contact&type=6&id={$id}&token={$token}&hashstr={$hashstr}\"  border=\"0\" align=\"absmiddle\"/>";
			$email=$val['email_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=jobs_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/>":"企业设置不对外公开";

			$html='<div class="c-contact">
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联 系 人：</div>
										<div class="contact-content f-left">'.$contact.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系手机：</div>
										<div class="contact-content f-left">'.$telephone.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">固定电话：</div>
										<div class="contact-content f-left">'.$landline_tel.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系邮箱：</div>
										<div class="contact-content f-left">'.$email.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">公司地址：</div>
										<div class="contact-content f-left">'.$val['address'].'</div>
									</div>
								</div>';
			}
			else
			{
				//对座机进行分隔
				$telarray = explode('-',$val['landline_tel']);
				if(intval($telarray[0]) > 0)
				{
					$landline_tel = $telarray[0];
				}
				if(intval($telarray[1]) > 0)
				{
					$landline_tel = empty($landline_tel)?$telarray[1]:$landline_tel."-".$telarray[1];
				}
				if(intval($telarray[2]) > 0)
				{
					$landline_tel = empty($landline_tel)?$telarray[2]:$landline_tel."-".$telarray[2];
				}
			$contact=$val['contact_show']=='1'?"{$val['contact']}":"企业设置不对外公开";
			$telephone=$val['telephone_show']=='1'?"{$val['telephone']}":"企业设置不对外公开</li>";
			$email=$val['email_show']=='1'?"{$val['email']}":"企业设置不对外公开</li>";
						$html='<div class="c-contact">
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联 系 人：</div>
										<div class="contact-content f-left">'.$contact.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系手机：</div>
										<div class="contact-content f-left"><span>'.$telephone.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">固定电话：</div>
										<div class="contact-content f-left"><span>'.$landline_tel.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系邮箱：</div>
										<div class="contact-content f-left"><span>'.$email.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">公司地址：</div>
										<div class="contact-content f-left">'.$val['address'].'</div>
									</div>
								</div>';
			}
		exit($html);
		}
		else
		{		
		exit($html);
		}
	}
		
}
elseif($act == 'company_contact')
{
	$id=intval($_GET['id']);
	$company_profile = $db->getone("SELECT uid FROM ".table('company_profile')." WHERE id=$id");
	if ($id>0)
	{
		$show=false;
		if($_CFG['showjobcontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showjobcontact']=='1')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>个人在注册并登录后才能查看企业的联系方式，现在<a href="javascript:void(0);" class="ajax_user_login" style="color:#ff7400">[立即登录]</a>或者<a href="'.$_CFG['site_dir'].'user/user_reg.php" style="color:#ff7400">[免费注册]</a></p>
								</div>';
			}
		}
		elseif($_CFG['showjobcontact']=='2')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
				$val=$db->getone("select uid from ".table('resume')." where uid='{$_SESSION['uid']}' LIMIT 1");
			 	if (!empty($val))
				{
				$show=true;
				}
				else
				{
				$show=false;
				$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>您没有发布简历或者简历无效，发布简历后才可以查看联系方式。<a href="'.get_member_url($_SESSION['utype'],true).'personal_resume.php?act=resume_list">[查看我的简历]</a></p>
								</div>';
				}
			}
			else
			{
			$show=false;
			$html='<div class="no-login">
									<span class="border-item topleft"></span>
									<span class="border-item topright"></span>
									<span class="border-item bottomleft"></span>
									<span class="border-item bottomright"></span>
									<p>个人在注册并登录后才能查看企业的联系方式，现在<a href="javascript:void(0);" class="ajax_user_login" style="color:#ff7400">[立即登录]</a>或者<a href="'.$_CFG['site_dir'].'user/user_reg.php" style="color:#ff7400">[免费注册]</a></p>
								</div>';
			}
		}
		if($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1' && $show==false)
		{
			if($company_profile['uid']==$_SESSION['uid'])
			{
				$show=true;
			}
			else
			{
				$show=false;
			}
		}
		if ($show)
		{
		$sql = "select contact,contact_show,telephone,telephone_show,email,email_show,address,address_show,website,landline_tel FROM ".table('company_profile')." where id='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_com']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$contact=$val['contact_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=company_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></li>":"企业设置不对外公开";
			$telephone=$val['telephone_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=company_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/>":"企业设置不对外公开";
			$landline_tel="<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=company_contact&type=5&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/>";
			$email=$val['email_show']=='1'?"<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=company_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/>":"企业设置不对外公开";

			$html='<div class="c-contact">
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联 系 人：</div>
										<div class="contact-content f-left">'.$contact.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系手机：</div>
										<div class="contact-content f-left">'.$telephone.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">固定电话：</div>
										<div class="contact-content f-left">'.$landline_tel.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系邮箱：</div>
										<div class="contact-content f-left">'.$email.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">公司地址：</div>
										<div class="contact-content f-left">'.$val['address'].'</div>
									</div>
								</div>';
			}
			else
			{
				//对座机进行分隔
				$telarray = explode('-',$val['landline_tel']);
				if(intval($telarray[0]) > 0)
				{
					$landline_tel = $telarray[0];
				}
				if(intval($telarray[1]) > 0)
				{
					$landline_tel = empty($landline_tel)?$telarray[1]:$landline_tel."-".$telarray[1];
				}
				if(intval($telarray[2]) > 0)
				{
					$landline_tel = empty($landline_tel)?$telarray[2]:$landline_tel."-".$telarray[2];
				}
			$contact=$val['contact_show']=='1'?"{$val['contact']}":"企业设置不对外公开";
			$telephone=$val['telephone_show']=='1'?"{$val['telephone']}":"企业设置不对外公开</li>";
			$email=$val['email_show']=='1'?"{$val['email']}":"企业设置不对外公开</li>";
						$html='<div class="c-contact">
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联 系 人：</div>
										<div class="contact-content f-left">'.$contact.'</div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系手机：</div>
										<div class="contact-content f-left"><span>'.$telephone.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">固定电话：</div>
										<div class="contact-content f-left"><span>'.$landline_tel.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">联系邮箱：</div>
										<div class="contact-content f-left"><span>'.$email.'</span></div>
									</div>
									<div class="contact-item clearfix">
										<div class="contact-type f-left">公司地址：</div>
										<div class="contact-content f-left">'.$val['address'].'</div>
									</div>
								</div>';
			}
			exit($html);
		}
		else
		{		
		exit($html);
		}
	}
}
elseif($act == 'resume_contact')
{
		$id=intval($_GET['id']);
		$show=false;
		$resume_sql="select * from ".table("resume")." where id=$id ";
		$resume_one=$db->getone($resume_sql);
		if($_SESSION["utype"]==2 && $_SESSION["uid"]==$resume_one["uid"]){
			$show=true;
		}
		if($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1' && $_CFG['showapplycontact']=='1'){
			$has = $db->getone("select 1 from ".table('personal_jobs_apply')." where company_uid=".intval($_SESSION['uid'])." and resume_id=".$id);
			if($has){
				$show = true;
			}
		}
		if($show==false){
			if($_CFG['showresumecontact']=='0')
			{
			$show=true;
			}
			elseif($_CFG['showresumecontact']=='1')
			{
				if ($_SESSION['uid'] && $_SESSION['username'] && ($_SESSION['utype']=='1' || $_SESSION['utype']=='3'))
				{
				$show=true;
				}
				else
				{
				$show=false;
				$html="<div class=\"contact-box\"><input type=\"button\" value=\"查看联系方式\" class=\"contact-btn download\" /></div>";
				}
			}
			elseif($_CFG['showresumecontact']=='2')
			{
				if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='1')
				{
					$sql = "select did from ".table('company_down_resume')." WHERE company_uid = {$_SESSION['uid']} AND resume_id='{$id}' LIMIT 1";
					$info=$db->getone($sql);
					if (!empty($info))
					{
					$show=true;
					}
					else
					{
					$show=false;
					$html="<div class=\"contact-box\"><input type=\"button\" value=\"查看联系方式\" class=\"contact-btn download\" /></div>";
					}
				}
				elseif ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='3')
				{
					$sql = "select did from ".table('hunter_down_resume')." WHERE hunter_uid = {$_SESSION['uid']} AND resume_id='{$id}' LIMIT 1";
					$info=$db->getone($sql);
					if (!empty($info))
					{
					$show=true;
					}
					else
					{
					$show=false;
					$html="<div class=\"contact-box\"><input type=\"button\" value=\"查看联系方式\" class=\"contact-btn download\" /></div>";
					}
				}
				else
				{
				$show=false;
				$html="<div class=\"contact-box\"><input type=\"button\" value=\"查看联系方式\" class=\"contact-btn download\" /></div>";
				}
			}
		}
		if ($show)
		{
			$val=$db->getone("select fullname,telephone,email,word_resume from ".table('resume')." WHERE  id='{$id}'  LIMIT 1");
			/*
				简历被标记状态
			*/
			if($_SESSION['uid'] && $_SESSION['utype']==1)
			{
				$resume_state=$db->getone("select resume_state from ".table("company_label_resume")." where resume_id=$id and uid=$_SESSION[uid]");
				switch ($resume_state['resume_state']) {
					case 1:
						$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state selected\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state\" state=\"2\" resume_id=\"{$id}\"/>";
						break;
					case 2:
						$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state selected\" state=\"2\" resume_id=\"{$id}\"/>";
						break;
					case 3:
						$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state selected\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state\" state=\"2\" resume_id=\"{$id}\"/>";
						break;
					case 4:
						$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state selected\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state\" state=\"2\" resume_id=\"{$id}\"/>";
						break;
					default:
						$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state\" state=\"2\" resume_id=\"{$id}\"/>";
						break;
				}
			}
			else
			{
				$state_htm="<input type=\"button\" value=\"暂未接通\" class=\"interview-state\" state=\"4\" resume_id=\"{$id}\"/><input type=\"button\" value=\"待定\" class=\"interview-state\" state=\"3\" resume_id=\"{$id}\"/><input type=\"button\" value=\"合适\" class=\"interview-state\" state=\"1\" resume_id=\"{$id}\"/><input type=\"button\" value=\"不合适\" class=\"interview-state\" state=\"2\" resume_id=\"{$id}\"/>";
			}
			// word 简历
			if($val['word_resume'])
			{
				$word_resume="<a class=\"word_resume\" href=\"".$_CFG['site_dir']."data/word/".$val['word_resume']."\"><img src=\"".$_CFG['site_template']."/images/word_resume.png\"> word简历</a>";
			}
			if ($_CFG['contact_img_resume']=='2')
			{
			$token=md5($val['fullname'].$id.$val['telephone']);
			$html="<div class=\"contact-interview\">";
			$html.="<div class=\"contact-text\">联系方式：<span><img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=resume_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/><em>|</em><img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=resume_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/><em>|</em><img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=resume_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/>".$word_resume."</span></div>";
			$html.="<div class=\"interview-block\">";
			$html.="<input id=\"invited\" type=\"button\" value=\"发送面试邀请\" class=\"contact-btn\" resume_id=\"{$id}\" />".$state_htm;
			$html.="</div>"; 
			$html.="</div>";
			}
			else
			{
			$html="<div class=\"contact-interview\">";
			$html.="<div class=\"contact-text\">联系方式：<span>".$val['fullname']."<em>|</em>".$val['telephone']."<em>|</em>".$val['email'].$word_resume."</span></div>";
			$html.="<div class=\"interview-block\">";
			$html.="<input id=\"invited\" type=\"button\" value=\"发送面试邀请\" class=\"contact-btn\" resume_id=\"{$id}\" />".$state_htm;
			$html.="</div>"; 
			$html.="</div>";
			}
			exit($html);
		}
		else
		{		
		exit($html);
		}
}
//3.4
elseif($act == 'course_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$show=false;
		if($_CFG['showcoursecontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showcoursecontact']=='1')
		{
		//登录后，查看权限
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该机构联系方式。</div>";
			}
		}
		if ($show)
		{
		$sql = "select * from ".table('course_contact')." where pid='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_course']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$ul="<ul>";
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=course_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联 系 人：机构设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=course_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系电话：机构设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=course_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系邮箱：机构设置不对外公开</div>";
			$ull="</ul>";
			$html=$ul.$contact.$telephone.$email.$ull;
			}
			else
			{
			$ul="<ul>";
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：{$val['contact']}</div>":"<div class=\"c\">联 系 人：机构设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：{$val['telephone']}</div>":"<div class=\"c\">联系电话：机构设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：{$val['email']}</div>":"<div class=\"c\">联系邮箱：机构设置不对外公开</div>";
			$ull="</ul>";
			$html=$ul.$contact.$telephone.$email.$ull;
			}
		exit($html);
		}
		else
		{		
		exit($html);
		}
	}
}
elseif($act == 'train_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$show=false;
		if($_CFG['showcoursecontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showcoursecontact']=='1')
		{
		//需要注意
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该机构联系方式。</div>";
			}
		}
		if ($show)
		{
		$sql = "select contact,contact_show,telephone,telephone_show,email,email_show,address,address_show,website FROM ".table('train_profile')." where id='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_train']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$ul="<ul>";
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=train_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联 系 人：机构设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=train_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系电话：机构设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=train_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系邮箱：机构设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=train_contact&type=4&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系地址：机构设置不对外公开</div>";
			$website.="<div class=\"c\">机构网址：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=train_contact&type=5&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>";		
			$ull="</ul>";
			$html=$ul.$contact.$telephone.$email.$address.$website.$ull;
			}
			else
			{
			$ul="<ul>";
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：{$val['contact']}</div>":"<div class=\"c\">联 系 人：机构设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：{$val['telephone']}</div>":"<div class=\"c\">联系电话：机构设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：{$val['email']}</div>":"<div class=\"c\">联系邮箱：机构设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：{$val['address']}</div>":"<div class=\"c\">联系地址：机构设置不对外公开</div>";
			$website=$val['website']==""?"":"<div class=\"c\">机构网址：{$val['website']}</div>";
			$ull.="</ul>";
			$html=$ul.$contact.$telephone.$email.$address.$website.$ull;
			}
			exit($html);
		}
		else
		{		
		exit($html);
		}
	}
}
elseif($act == 'teacher_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$show=false;
		if($_CFG['showcoursecontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showcoursecontact']=='1')
		{
		//需要注意
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该讲师联系方式。</div>";
			}
		}
		if ($show)
		{
		$sql = "select telephone,telephone_show,email,email_show,address,address_show,qq,qq_show,website FROM ".table('train_teachers')." where id='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_train']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$ul="<ul>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=teacher_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系电话：讲师设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=teacher_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系邮箱：讲师设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=teacher_contact&type=4&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系地址：讲师设置不对外公开</div>";
			$ull="</ul>";
			$html=$ul.$contact.$telephone.$email.$address.$ull;
			}
			else
			{
			$ul="<ul>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：{$val['telephone']}</div>":"<div class=\"c\">联系电话：讲师设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：{$val['email']}</div>":"<div class=\"c\">联系邮箱：讲师设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：{$val['address']}</div>":"<div class=\"c\">联系地址：讲师设置不对外公开</div>";
			$ull.="</ul>";
			$html=$ul.$contact.$telephone.$email.$address.$ull;
			}
			exit($html);
		}
		else
		{		
		exit($html);
		}
	}
}
//高级职位
elseif($act == 'hunterjobs_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$show=false;
		if($_CFG['showhunterjobcontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showhunterjobcontact']=='1')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该猎头联系方式。</div>";
			}
		}
		//是否创建高级经理人简历
		elseif($_CFG['showhunterjobcontact']=='2')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
				$val=$db->getone("select uid from ".table('manager_resume')." where uid='{$_SESSION['uid']}' and complete=1 and audit=1 LIMIT 1");
			 	if (!empty($val))
				{
				$show=true;
				}
				else
				{
				$show=false;
				$html="<p>您没有发布经理人简历或者简历无效，发布经理人简历后才可以查看联系方式。<a href=\"".get_member_url($_SESSION['utype'],true)."personal_managerresume.php?act=resume_list\">[查看我的经理人简历]</a></p>";
				}
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该猎头联系方式。</div>";
			}
		}
		
		if ($show)
		{
		$sql = "select telephone,telephone_show,email,email_show,address,address_show,contact,contact_show,qq,qq_show from ".table('hunter_jobs')." where id='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_hunterjob']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联 系 人：企业设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系电话：企业设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系邮箱：企业设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=4&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系地址：企业设置不对外公开</div>";
			$html=$contact.$telephone.$email.$address;
			}
			else
			{
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：{$val['contact']}</div>":"<div class=\"c\">联 系 人：企业设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：{$val['telephone']}</div>":"<div class=\"c\">联系电话：企业设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：{$val['email']}</div>":"<div class=\"c\">联系邮箱：企业设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：{$val['address']}</div>":"<div class=\"c\">联系地址：企业设置不对外公开</div>";
			$html=$contact.$telephone.$email.$address;
			}
		exit($html);
		}
		else
		{		
		exit($html);
		}
	}
		
}
elseif($act == 'hunter_contact')
{
	$id=intval($_GET['id']);
	if ($id>0)
	{
		$show=false;
		if($_CFG['showhunterjobcontact']=='0')
		{
		$show=true;
		}
		elseif($_CFG['showhunterjobcontact']=='1')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
			$show=true;
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"".$_CFG['site_dir']."user/login.php\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该猎头联系方式。</div>";
			}
		}
		//是否创建高级简历
		elseif($_CFG['showhunterjobcontact']=='2')
		{
			if ($_SESSION['uid'] && $_SESSION['username'] && $_SESSION['utype']=='2')
			{
				$val=$db->getone("select uid from ".table('resume')." where uid='{$_SESSION['uid']}' and complete=1 and audit=1 and talent=2 LIMIT 1");
			 	if (!empty($val))
				{
				$show=true;
				}
				else
				{
				$show=false;
				$html="<p>您没有发布高级简历或者简历无效，发布高级简历后才可以查看联系方式。<a href=\"".get_member_url($_SESSION['utype'],true)."personal_managerresume.php?act=resume_list\">[查看我的经理人简历]</a></p>";
				}
			}
			else
			{
			$show=false;
			$html="<div class=\"c\">请 <a href=\"javascript:;\" class=\"ajax_user_login\"> 登录 </a>或<a href=\"".$_CFG['site_dir']."user/user_reg.php\"> 注册 </a> 个人会员，查看该猎头联系方式。</div>";
			}
		}
		
		if ($show)
		{
		$sql = "select telephone,telephone_show,email,email_show,address,address_show,contact,contact_show,qq,qq_show from ".table('hunter_jobs')." where id='{$id}' LIMIT 1";
		$val=$db->getone($sql);
			if ($_CFG['contact_img_hunterjob']=='2')
			{
			$token=md5($val['contact'].$id.$val['telephone']);
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=1&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联 系 人：企业设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=2&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系电话：企业设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=3&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系邮箱：企业设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：<img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=4&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系地址：企业设置不对外公开</div>";
			$qq=$val['qq_show']=='1'?"<div class=\"c\">联系Q Q： <img src=\"{$_CFG['site_dir']}plus/contact_img.php?act=hunterjobs_contact&type=5&id={$id}&token={$token}\"  border=\"0\" align=\"absmiddle\"/></div>":"<div class=\"c\">联系Q Q：企业设置不对外公开 </div>";
			$html=$contact.$telephone.$email.$address.$qq;
			}
			else
			{
			$contact=$val['contact_show']=='1'?"<div class=\"c\">联 系 人：{$val['contact']}</div>":"<div class=\"c\">联 系 人：企业设置不对外公开</div>";
			$telephone=$val['telephone_show']=='1'?"<div class=\"c\">联系电话：{$val['telephone']}</div>":"<div class=\"c\">联系电话：企业设置不对外公开</div>";
			$email=$val['email_show']=='1'?"<div class=\"c\">联系邮箱：{$val['email']}</div>":"<div class=\"c\">联系邮箱：企业设置不对外公开</div>";
			$address=$val['address_show']=='1'?"<div class=\"c\">联系地址：{$val['address']}</div>":"<div class=\"c\">联系地址：企业设置不对外公开</div>";
			$qq=$val['qq_show']=='1'?"<div class=\"c\">联系Q Q：{$val['qq']}</div>":"<div class=\"c\">联系Q Q：企业设置不对外公开</div>";
			$html=$contact.$telephone.$email.$address.$qq;
			}
		exit($html);
		}
		else
		{		
		exit($html);
		}
	}
}
?>