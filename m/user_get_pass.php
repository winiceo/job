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
define('IN_QISHI', true);
$alias="QS_login";
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'include/fun_user.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
unset($dbhost,$dbuser,$dbpass,$dbname);
$smarty->cache = false;
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'enter';
if ($act=='enter')
{
	$smarty->assign('title','找回密码 - '.$_CFG['site_name']);
	$captcha=get_cache('captcha');
	$smarty->assign('verify_getpwd',$captcha['verify_getpwd']);
	$smarty->assign('sms',get_cache('sms_config'));
	$smarty->assign('step',"1");
	$_SESSION['send_key']=mt_rand(100000, 999999);
	$smarty->assign('send_key',$_SESSION['send_key']);
	$smarty->display('m/password.html');
}
//发送手机验证码
elseif($act=='pw_send_code')
{
	$mobile=trim($_POST['mobile']);
	$_POST=array_map("utf8_to_gbk", $_POST);
	if (empty($mobile) || !preg_match("/^(13|15|14|17|18)\d{9}$/",$mobile))
	{
		exit("手机号错误");
	}
	$sql = "select * from ".table('members')." where mobile = '{$mobile}' LIMIT 1";
	$userinfo=$db->getone($sql);
	if (!$userinfo)
	{
		exit("手机号不存在！请填写其他手机号码");
	}
	if ($_SESSION['send_time'] && (time()-$_SESSION['send_time'])<60)
	{
		exit("请60秒后重新验证！");
	}
	$rand=mt_rand(100000, 999999);	
	$r=captcha_send_sms($mobile,"感谢您使用{$_CFG['site_name']}手机验证,验证码为:{$rand}");
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
//验证手机验证码
elseif($act=='pw_validate_code')
{
	$_POST=array_map("utf8_to_gbk", $_POST);
	require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
	$mobile = trim($_POST['mobile']);
	if(empty($mobile))
	{
		exit("3");
	}
	//验证验证码是否正确
	$verifycode=trim($_POST['verifycode']);
	if (empty($verifycode) || empty($_SESSION['mobile_rand']) || $verifycode<>$_SESSION['mobile_rand'])
	{
		exit("2");
	}
	else
	{
		//组合加密字段
		$userinfo=get_user_inmobile($mobile);
		$token=md5($userinfo['uid'].$userinfo['username'].$userinfo['reg_time']);
		exit("?act=set_pass&type=1&username=".$mobile."&token=".$token);
	}
}
//发送邮箱验证码
elseif($act=='pw_send_email_code')
{
	$email=trim($_POST['email']);
	$_POST=array_map("utf8_to_gbk", $_POST);
	if (empty($email) || !preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]w+)*$/",$email))
	{
		exit("邮箱格式错误");
	}
	$sql = "select * from ".table('members')." where email = '{$email}' LIMIT 1";
	$userinfo=$db->getone($sql);
	if (!$userinfo)
	{
		exit("邮箱不存在！请填写其他邮箱账号");
	}
	if ($_SESSION['send_email_time'] && (time()-$_SESSION['send_email_time'])<60)
	{
		exit("请60秒后重新验证！");
	}
	$rand=mt_rand(100000, 999999);
	if (smtp_mail($email,"{$_CFG['site_name']}邮件认证","{$QISHI['site_name']}提醒您：<br>您正在进行邮箱验证，验证码为:<strong>{$rand}</strong>"))
	{
		$_SESSION['email_rand']=$rand;
		$_SESSION['send_email_time']=time();
		$_SESSION['verify_email']=$email;
		exit("success");
	}
	else
	{
		exit("邮箱配置出错，请联系网站管理员");
	}
}
//验证邮箱码
elseif($act=='pw_email_validate_code')
{
	$_POST=array_map("utf8_to_gbk", $_POST);
	require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
	$email = trim($_POST['email']);
	if(empty($email))
	{
		exit("3");
	}
	//验证验证码是否正确
	$emverifycode=trim($_POST['emverifycode']);
	if (empty($emverifycode) || empty($_SESSION['email_rand']) || $emverifycode<>$_SESSION['email_rand'])
	{
		exit("2");
	}
	else
	{
		//组合加密字段
		$userinfo=get_user_inemail($email);
		$token=md5($userinfo['uid'].$userinfo['username'].$userinfo['reg_time']);
		exit("?act=set_pass&type=2&username=".$email."&token=".$token);
	}
}
//找回密码第2步 -> 重置密码
elseif ($act=='set_pass')
{
	$send_key = trim($_GET['send_key']);
	$token = trim($_GET['token']);
	$username = trim($_GET['username']);
	if (preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$username))
	{
		$userinfo=get_user_inemail($username);
	}
	elseif (preg_match("/^(13|14|15|17|18)\d{9}$/",$username))
	{
		$userinfo=get_user_inmobile($username);
	}
	$token_two=md5($userinfo['uid'].$userinfo['username'].$userinfo['reg_time']);
	if (empty($send_key) || $send_key<>$_SESSION['send_key'] || $token_two<>$token)
	{
		$smarty->display('m/password-err.html');
	}
	else
	{
		$type = intval($_GET['type']);
		$smarty->assign('type',$type);
		$smarty->assign('username',$username);
		$smarty->display('m/password-set-new.html');
	}
}
elseif ($act=='set_pass_save')
{
	global $QS_pwdhash;
	$_POST=array_map("utf8_to_gbk", $_POST);
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	$password_two = trim($_POST['password_two']);
	if(empty($username) || empty($password) || empty($password_two))
	{
		exit('信息丢失！');
	}
	if($password <>$password_two )
	{
		exit('两次输入的密码不同！');
	}
	if (preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$username))
	{
		$userinfo=get_user_inemail($username);
	}
	elseif (preg_match("/^(13|14|15|17|18)\d{9}$/",$username))
	{
		$userinfo=get_user_inmobile($username);
	}
	$password_hash=md5(md5($password).$userinfo['pwd_hash'].$QS_pwdhash);
	$setsqlarr['password']=$password_hash;
	$rst=$db->updatetable(table('members'),$setsqlarr,array("uid"=>$userinfo['uid']));
	if($rst)
	{
		exit('ok');
	}	
	else
	{
		exit('设置新密码失败！');
	}
}
unset($smarty);
?>