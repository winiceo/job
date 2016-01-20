<?php
 /*
 * 74cms 触屏版企业会员中心账号安全
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'include/fun_company.php');
require_once(QISHI_ROOT_PATH.'include/fun_weixin.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'verify_mobile';
if ($_SESSION['uid']=='' || $_SESSION['username']==''||intval($_SESSION['utype'])==2)
{
	header("Location: ../login.php");
}
elseif ($act == 'verify_mobile')
{
	$uid = intval($_SESSION['uid']);
	$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
	$userinfo=$db->getone($sql);
	$smarty->assign('userinfo',$userinfo);
	$smarty->assign('mobile',$userinfo['mobile']);
	$smarty->assign('email',$userinfo['email']);
	$_SESSION['send_mobile_key']=mt_rand(100000, 999999);
	$smarty->assign('send_mobile_key',$_SESSION['send_mobile_key']);
	$smarty->display("m/company/m-securityaccount.html");
}
elseif ($act=="send_code")
{
	$send_key=trim($_POST['send_key']);
	if (empty($send_key) || $send_key<>$_SESSION['send_mobile_key'])
	{
		exit("链接错误");
	}
	$mobile=trim($_POST['mobile']);
	if (empty($mobile) || !preg_match("/^(13|15|14|17|18)\d{9}$/",$mobile))
	{
		exit("手机号错误");
	}
	$sql = "select * from ".table('members')." where mobile = '{$mobile}' LIMIT 1";
	$userinfo=$db->getone($sql);
	if ($userinfo && $userinfo['uid']<>$_SESSION['uid'])
	{
		exit("手机号已经存在！请填写其他手机号码");
	}
	elseif(!empty($userinfo['mobile']) && $userinfo['mobile_audit']=="1" && $userinfo['mobile']==$mobile)
	{
		exit("你的手机号 {$mobile} 已经通过验证！");
	}
	else
	{
		if ($_SESSION['send_time'] && (time()-$_SESSION['send_time'])<180)
		{
			exit("请180秒后再进行验证！");
		}
		$rand=mt_rand(100000, 999999);	
		$r=captcha_send_sms($mobile,"感谢您使用{$_CFG['site_name']}手机认证,验证码为:{$rand}");
		if ($r=="success")
		{
			$_SESSION['mobile_rand']=$rand;
			$_SESSION['send_time']=time();
			$_SESSION['verify_mobile']=$mobile;
			exit("success");
		}
		else
		{
			exit("SMS配置出错，请联系网站管理员");
		}
	} 
}
elseif ($act=="verify_code")
{
	$send_key=trim($_POST['send_key']);
	if (empty($send_key) || $send_key<>$_SESSION['send_mobile_key'])
	{
		exit("链接错误");
	}
	$verifycode=trim($_POST['verifycode']);
	if (empty($verifycode) || empty($_SESSION['mobile_rand']) || $verifycode<>$_SESSION['mobile_rand'])
	{
		exit("验证码错误");
	}
	else
	{
		$uid=intval($_SESSION['uid']);
		if (empty($uid))
		{
			exit("系统错误，UID丢失！");
		}
		else
		{
			$setsqlarr['mobile']=$_SESSION['verify_mobile'];
			$setsqlarr['mobile_audit']=1;
			$db->updatetable(table('members'),$setsqlarr," uid='{$uid}'");
			unset($setsqlarr,$_SESSION['verify_mobile'],$_SESSION['mobile_rand']);
			if($_CFG['operation_mode']=='1' || $_CFG['operation_mode']=='3')
			{
				$rule=get_cache('points_rule');
				if ($rule['verifymobile']['value']>0)
				{
					$info=$db->getone("SELECT uid FROM ".table('members_handsel')." WHERE uid ='{$_SESSION['uid']}' AND htype='verifymobile' LIMIT 1");
					if(empty($info))
					{
					$time=time();			
					$db->query("INSERT INTO ".table('members_handsel')." (uid,htype,addtime) VALUES ('{$_SESSION['uid']}', 'verifymobile','{$time}')");
					require_once(QISHI_ROOT_PATH.'include/fun_comapny.php');
					report_deal($_SESSION['uid'],$rule['verifymobile']['type'],$rule['verifymobile']['value']);
					$user_points=get_user_points($_SESSION['uid']);
					$operator=$rule['verifymobile']['type']=="1"?"+":"-";
					$_SESSION['handsel_verifymobile']=$_CFG['points_byname'].$operator.$rule['verifymobile']['value'];
					write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username']," 手机通过验证，{$_CFG['points_byname']}({$operator}{$rule['verifymobile']['value']})，(剩余:{$user_points})",1,1016,"手机认证通过","{$operator}{$rule['verifymobile']['value']}","{$user_points}");
					}
				}
			}
			exit("success");
		}
	}
}
elseif ($act == 'edit_email')
{
	global $QS_pwdhash;
	$uid=intval($_SESSION['uid']);
	$send_key=trim($_POST['send_key']);
	if (empty($send_key) || $send_key<>$_SESSION['send_mobile_key'])
	{
		exit("链接错误");
	}
	$email=trim($_POST['email']);
	$password=trim($_POST['password']);
	if (empty($email) || !preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]w+)*$/",$email))
	{
		exit("邮箱格式错误");
	}
	$sql = "select * from ".table('members')." where email = '{$email}' LIMIT 1";
	$userinfo=$db->getone($sql);
	if ($userinfo && $userinfo['uid']<>$_SESSION['uid'])
	{
		exit("邮箱已经存在！请填写其他邮箱");
	}
	else
	{
		//验证密码是否正确
		$sql = "select * from ".table('members')." where uid = '{$uid}' LIMIT 1";
		$member_info=$db->getone($sql);
		$pwd=md5(md5($password).$member_info['pwd_hash'].$QS_pwdhash);
		if ($member_info['password']!=$pwd)
		{
			exit("密码错误！");
		}
		$setsqlarr['email']=$email;
		$setsqlarr['email_audit']=0;
		$db->updatetable(table('members'),$setsqlarr," uid='{$uid}'");
		$infoarr['email']=$setsqlarr['email'];
		$db->updatetable(table('company_profile'),$infoarr," uid='{$uid}'");
		exit("success");
	}
}
elseif ($act == 'save_password')
{	
	require_once(QISHI_ROOT_PATH.'include/fun_user.php');
	$arr['username']=$_SESSION['username'];
	$arr['oldpassword']=trim($_POST['oldpassword'])?trim($_POST['oldpassword']):exit('请输入旧密码！');
	$arr['password']=trim($_POST['password'])?trim($_POST['password']):exit('请输入新密码！');
	if ($arr['password']!=trim($_POST['password1'])) exit('两次输入密码不相同，请重新输入！');
	//edit_password()修改密码的方法
	$info=edit_password($arr);
	if ($info==-1) exit('旧密码输入错误，请重新输入！');
	if ($info==$_SESSION['username'])
	{
		//发送邮件
		$mailconfig=get_cache('mailconfig');
		if ($mailconfig['set_editpwd']=="1" && $user['email_audit']=="1")
		{
		dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid=".$_SESSION['uid']."&key=".asyn_userkey($_SESSION['uid'])."&act=set_editpwd&newpassword=".$arr['password']);
		}
		//邮件发送完毕
		//sms
		$sms=get_cache('sms_config');
		if ($sms['open']=="1" && $sms['set_editpwd']=="1"  && $user['mobile_audit']=="1")
		{
		dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_sms.php?uid=".$_SESSION['uid']."&key=".asyn_userkey($_SESSION['uid'])."&act=set_editpwd&newpassword=".$arr['password']);
		}
		//sms
		if(defined('UC_API'))
		{
		include_once(QISHI_ROOT_PATH.'uc_client/client.php');
		uc_user_edit($arr['username'],$arr['oldpassword'], $arr['password']);
		}
		//微信提醒
		set_editpwd($_SESSION['uid'],$_SESSION['username'],$arr['password']);
		//往会员日志表里记录
		write_memberslog($_SESSION['uid'],2,1004 ,$_SESSION['username'],"修改密码");
		exit('密码修改成功！');
	 }
}

?>