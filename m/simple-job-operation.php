<?php
 /*
 * 74cms 触屏版微招聘操作模块
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
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
require_once(dirname(__FILE__).'/weixin_share.php');
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'add';
if ($act == 'add')
{
	$smarty->cache = false;
	$smarty->display('m/simple-add.html');
}
elseif($act == 'add_save')
{
	$captcha=get_cache('captcha');
	$_POST=array_map("utf8_to_gbk", $_POST);
	$setsqlarr['audit']=intval($_CFG['simple_add_audit']);
	$setsqlarr['jobname']=trim($_POST['jobname'])?trim($_POST['jobname']):exit('您没有填写职位名称！');
	$setsqlarr['amount']=intval($_POST['amount']);
	$setsqlarr['comname']=trim($_POST['comname'])?trim($_POST['comname']):exit('您没有填写公司名称！');
	$setsqlarr['contact']=trim($_POST['contact'])?trim($_POST['contact']):exit('您没有填写联系人！');
	$setsqlarr['tel']=trim($_POST['tel'])?trim($_POST['tel']):exit('您没有填写联系电话！');
	if(preg_match("/^\d*$/",$setsqlarr['tel']))
	{
		if ($captcha['simple_tel_repeat']=='0')
		{
			$sql = "select id from ".table('simple')." where tel = '{$setsqlarr['tel']}' LIMIT 1";
			$info=$db->getone($sql);
			if (!empty($info))
			{
				exit('电话号码已经存在！');
			}
		}
	}
	else
	{
		exit('电话号码格式错误！');
	}
	$setsqlarr['district']=intval($_POST['district'])?intval($_POST['district']):exit('您没有选择地区！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn'])?trim($_POST['district_cn']):exit('您没有选择地区！');
	$setsqlarr['sdistrict_cn']=trim($_POST['sdistrict_cn']);
	$setsqlarr['detailed']=trim($_POST['detailed'])?trim($_POST['detailed']):exit('您没有填写具体要求！');
	$setsqlarr['addtime']=time();
	$setsqlarr['refreshtime']=time();
	$setsqlarr['deadline']=0;
	$validity=intval($_POST['validity']);
	if ($validity>0)
	{
	$setsqlarr['deadline']=strtotime("{$validity} day");
	}
	$setsqlarr['pwd']=trim($_POST['pwd'])?trim($_POST['pwd']):exit('您没有填写管理密码！');
	$setsqlarr['pwd_hash']=substr(md5(uniqid().mt_rand()),mt_rand(0,6),6);
	$setsqlarr['pwd']=md5(md5($setsqlarr['pwd']).$setsqlarr['pwd_hash'].$QS_pwdhash);
	$setsqlarr['addip']=$online_ip;
	$setsqlarr['likekey']=$setsqlarr['jobname'].",".$setsqlarr['comname'].",".$setsqlarr['address'].",".$setsqlarr['detailed'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']=$setsqlarr['jobname'].$setsqlarr['comname'].$setsqlarr['address'].$setsqlarr['detailed'];
	$setsqlarr['key']="{$setsqlarr['jobname']} {$setsqlarr['comname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if($db->inserttable(table('simple'),$setsqlarr))
	{
		exit("ok");
	}
	else
	{
		exit("添加失败！");
	}
}
elseif($act == 'edit')
{
	$smarty->cache = false;
	$row=simple_jobs_one($_GET['id']);
	$smarty->assign('show',$row);
	$smarty->display('m/simple-edit.html');
}
elseif($act == "edit_save")
{
	$captcha=get_cache('captcha');
	$_POST=array_map("utf8_to_gbk", $_POST);
	$id=intval($_POST['id']);
	$pwd=trim($_POST['pwd']);
	$info=$db->getone("select * from ".table('simple')." where id = '{$id}' LIMIT 1");
	$thispwd=md5(md5($pwd).$info['pwd_hash'].$QS_pwdhash);
	if ($thispwd!=$info['pwd'])
	{
		exit("管理密码错误");
	}
	if ($_CFG['simple_edit_audit']!="-1")
	{
	$setsqlarr['audit']=intval($_CFG['simple_edit_audit']);
	}
	$setsqlarr['jobname']=trim($_POST['jobname'])?trim($_POST['jobname']):exit('您没有填写职位名称！');
	$setsqlarr['amount']=intval($_POST['amount']);
	$setsqlarr['comname']=trim($_POST['comname'])?trim($_POST['comname']):exit('您没有填写公司名称！');
	$setsqlarr['contact']=trim($_POST['contact'])?trim($_POST['contact']):exit('您没有填写联系人！');
	if ($_CFG['simple_tel_edit']=="1")
	{
		$setsqlarr['tel']=trim($_POST['tel'])?trim($_POST['tel']):exit('您没有填写联系电话！');
		if(preg_match("/^\d*$/",$setsqlarr['tel']))
		{
			if ($captcha['simple_tel_repeat']=='0')
			{
				$sql = "select id from ".table('simple')." where tel = '{$setsqlarr['tel']}' AND id<>'{$id}' LIMIT 1";
				$info=$db->getone($sql);
				if (!empty($info))
				{
					exit('电话号码已经存在！');
				}
			}
		}	
	}
	$setsqlarr['district']=intval($_POST['district'])?intval($_POST['district']):exit('您没有选择地区！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['sdistrict_cn']=trim($_POST['sdistrict_cn']);
	$setsqlarr['detailed']=trim($_POST['detailed'])?trim($_POST['detailed']):exit('您没有填写具体要求！');
	$setsqlarr['refreshtime']=time();
	$days=intval($_POST['days']);
	if ($days>0)
	{
	$time=$info['deadline']>time()?$info['deadline']:time();
	$setsqlarr['deadline']=strtotime("{$days} day",$time);
	}
	$setsqlarr['likekey']=$setsqlarr['jobname'].",".$setsqlarr['comname'].",".$setsqlarr['address'].",".$setsqlarr['detailed'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']=$setsqlarr['jobname'].$setsqlarr['comname'].$setsqlarr['address'].$setsqlarr['detailed'];
	$setsqlarr['key']="{$setsqlarr['jobname']} {$setsqlarr['comname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if($db->updatetable(table('simple'),$setsqlarr," id='{$id}' "))
	{
		exit("ok");
	}
	else
	{
		exit("修改失败！");
	}
}
// 刷新微招聘
elseif($act == "refresh")
{
	$jobid = intval($_GET['jobid'])?intval($_GET['jobid']):exit("参数错误！");
	$pass = trim($_GET['pass'])?trim($_GET['pass']):exit("参数错误！");
	$sql = "select * from ".table('simple')." where id = '{$jobid}' LIMIT 1";
	$info=$db->getone($sql);
	$thispwd=md5(md5($pass).$info['pwd_hash'].$QS_pwdhash);
	if ($thispwd==$info['pwd'])
	{
		$db->query("update ".table('simple')."  SET refreshtime='".time()."' WHERE id = '{$jobid}'");
		exit("刷新成功！");
	}
	else
	{
		exit("管理密码错误！");
	}
}
// 删除微招聘
elseif($act == "delete")
{
	$jobid = intval($_GET['jobid'])?intval($_GET['jobid']):exit("参数错误！");
	$pass = trim($_GET['pass'])?trim($_GET['pass']):exit("参数错误！");
	$sql = "select * from ".table('simple')." where id = '{$jobid}' LIMIT 1";
	$info=$db->getone($sql);
	$thispwd=md5(md5($pass).$info['pwd_hash'].$QS_pwdhash);
	if ($thispwd==$info['pwd'])
	{
		$db->query("Delete from ".table('simple')." WHERE id = '{$jobid}'");
		exit("删除成功！");
	}
	else
	{
		exit("管理密码错误！");
	}
}

?>