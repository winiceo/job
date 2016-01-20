<?php
 /*
 * 74cms 触屏版注册模块
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
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'reg';
$smarty->caching = false;
if ($act == 'reg')
{
$smarty->display("m/reg-index.html");
}
elseif ($act=='reg_info')
{
	if ($_CFG['closereg']=='1')WapShowMsg("网站暂停会员注册，请稍后再次尝试！",1);
	$utype = $_GET['utype'];
	if (intval($utype)==0)WapShowMsg("请选择注册类型！",1);
	if(intval($_GET['type'])>2){
		WapShowMsg("会员类型不正确，请重新选择！",1);
	}
	$smarty->assign('utype',$utype);
	$captcha=get_cache('captcha');
	$smarty->assign('verify_userreg',$captcha['verify_userreg']);
	$smarty->display("m/reg.html");
}
//普通注册
elseif ($act == 'do_reg')
{
	require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
	require_once(QISHI_ROOT_PATH.'include/fun_user.php');
	
	$_POST=array_map("utf8_to_gbk", $_POST);
	$username = isset($_POST['username'])?trim($_POST['username']):"";
	$password = isset($_POST['password'])?trim($_POST['password']):"";
	$member_type =intval($_POST['utype']);
	$email = isset($_POST['email'])?trim($_POST['email']):"";
	$agreement = isset($_POST['agreement'])?intval($_POST['agreement']):"";
	if (empty($username)||empty($password)||empty($member_type)||empty($email))
	{
	$err="信息不完整";
	}
	elseif (empty($agreement))
	{
	$err="需要同意注册协议";
	}
	elseif (strlen($username)<6 || strlen($username)>18)
	{
	$err="用户名长度为6-18个字符";
	}
	elseif (strlen($password)<6 || strlen($password)>18)
	{
	$err="密码长度为6-18个字符";
	}
	elseif ($password<>$_POST['password1'])
	{
	$err="两次输入的密码不同";
	}
	elseif (empty($email) || !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$email))
	{
	$err="电子邮箱格式错误";
	}
	if (get_user_inusername($username))
	{
	$err="用户名已经存在";
	}
	if (get_user_inemail($email))
	{
	$err="电子邮箱已经存在";
	}	
	if ($err)
	{
		exit($err);
	}	
	$register=user_register(3,$password,$member_type,$email,$mobile="",true,$username,"");
	if ($register>0)
	{
		$login_js=wap_user_login($username,$password);
		$mailconfig=get_cache('mailconfig');
		if ($mailconfig['set_reg']=="1")
		{
		dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid=".$_SESSION['uid']."&key=".asyn_userkey($_SESSION['uid'])."&sendemail=".$email."&sendusername=".$username."&sendpassword=".$password."&act=reg");
		}
		if ($login_js)
		{
			exit($login_js['qs_login']);
		}
	}
	else
	{
		exit("user_reg.php");
	}
}
//发送手机验证码
elseif ($act == 'reg_send_code')
{
	require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
	require_once(QISHI_ROOT_PATH.'include/fun_user.php');
	$_POST=array_map("utf8_to_gbk", $_POST);
	$mobile=trim($_POST['mobile']);
	if (empty($mobile) || !preg_match("/^(13|15|14|17|18)\d{9}$/",$mobile))
	{
		exit("手机号错误");
	}
	$sql = "select * from ".table('members')." where mobile = '{$mobile}' LIMIT 1";
	$userinfo=$db->getone($sql);
	if ($userinfo)
	{
		exit("手机号已存在！请填写其他手机号码");
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
//手机号注册
elseif ($act == 'phone_reg')
{
	$_POST=array_map("utf8_to_gbk", $_POST);
	require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
	require_once(QISHI_ROOT_PATH.'include/fun_user.php');
	$mobile = trim($_POST['mobile']);
	$password_mobile = isset($_POST['password_mobile'])?trim($_POST['password_mobile']):"";
	$member_type =intval($_POST['utype']);
	$agreement_mobile = isset($_POST['agreement_mobile'])?intval($_POST['agreement_mobile']):"";
	if(empty($mobile) || empty($password_mobile) || empty($member_type))
	{
		exit("信息不完整！");
	}
	elseif (empty($agreement_mobile))
	{
	 	exit("需要同意注册协议");
	}
	//验证验证码是否正确
	$verifycode=trim($_POST['verifycode']);
	if (empty($verifycode) || empty($_SESSION['mobile_rand']) || $verifycode<>$_SESSION['mobile_rand'])
	{
		exit("验证码错误！");
	}
	//手机号注册
	$register=user_register(1,$password_mobile,$member_type,$email="",$mobile,false,$username="","");
	if ($register>0)
	{
		$user_info=get_user_inmobile($mobile);
		$login_js=wap_user_login($user_info['username'],$password_mobile);
		if ($login_js)
		{
			exit($login_js['qs_login']);
		}
	}
	else
	{
		exit("user_reg.php");
	}
}
?>