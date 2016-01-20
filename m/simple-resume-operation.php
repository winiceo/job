<?php
 /*
 * 74cms 触屏版微简历操作模块
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
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'add';
if ($act == 'add')
{
	$smarty->cache = false;
	$smarty->display('m/simple-resume-add.html');
}
elseif($act == 'add_save')
{
	$captcha=get_cache('captcha');
	$_POST=array_map("utf8_to_gbk", $_POST);
	$setsqlarr['audit']=intval($_CFG['simple_add_audit']);
	$setsqlarr['uname']=trim($_POST['uname'])?trim($_POST['uname']):exit('您没有填写姓名！');
	$setsqlarr['age']=intval($_POST['age'])?intval($_POST['age']):exit('您没有填写年龄！');
	$setsqlarr['sex']=intval($_POST['sex']);
	switch($setsqlarr['sex']){
		case 1:$setsqlarr['sex_cn']="男";break;
		case 2:$setsqlarr['sex_cn']="女";break;
	}
	$setsqlarr['category']=trim($_POST['category'])?trim($_POST['category']):exit('您没有填写意向职位！');
	$setsqlarr['experience']=intval($_POST['experience'])?intval($_POST['experience']):exit('您没有选择工作经验！');
	$experience = $db->getone("select c_name from ".table('category')." where c_id=".$setsqlarr['experience']);
	$setsqlarr['experience_cn']=$experience['c_name'];
	$setsqlarr['tel']=trim($_POST['tel'])?trim($_POST['tel']):exit('您没有填写联系电话！');
	if(preg_match("/^\d*$/",$setsqlarr['tel']))
	{
		if ($captcha['simple_tel_repeat']=='0')
		{
			$sql = "select id from ".table('simple_resume')." where tel = '{$setsqlarr['tel']}' LIMIT 1";
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
	$setsqlarr['district']=intval($_POST['district'])?intval($_POST['district']):exit('您没有选择工作地区！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['sdistrict_cn']=trim($_POST['sdistrict_cn']);
	$setsqlarr['detailed']=trim($_POST['detailed'])?trim($_POST['detailed']):exit('您没有填写个人简介！');
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
	$setsqlarr['likekey']=$setsqlarr['uname'].",".$setsqlarr['category'].",".$setsqlarr['address'].",".$setsqlarr['detailed'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']=$setsqlarr['uname'].$setsqlarr['address'].$setsqlarr['detailed'];
	$setsqlarr['key']="{$setsqlarr['uname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if($resumepid = $db->inserttable(table('simple_resume'),$setsqlarr,1))
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
	$row=simple_resume_one($_GET['id']);
	$smarty->assign('show',$row);
	$smarty->display('m/simple-resume-edit.html');
}
elseif($act == "edit_save")
{
	$captcha=get_cache('captcha');
	$_POST=array_map("utf8_to_gbk", $_POST);
	$id=intval($_POST['id']);
	$pwd=trim($_POST['pwd']);
	$info=$db->getone("select * from ".table('simple_resume')." where id = '{$id}' LIMIT 1");
	$thispwd=md5(md5($pwd).$info['pwd_hash'].$QS_pwdhash);
	if ($_CFG['simple_edit_audit']!="-1")
	{
	$setsqlarr['audit']=intval($_CFG['simple_edit_audit']);
	}
	$setsqlarr['uname']=trim($_POST['uname'])?trim($_POST['uname']):exit("您没有填写姓名！");
	$setsqlarr['age']=intval($_POST['age'])?intval($_POST['age']):exit("您没有填写年龄！");
	$setsqlarr['sex']=intval($_POST['sex'])?intval($_POST['sex']):exit("您没有选择性别！");
	switch($setsqlarr['sex']){
		case 1:$setsqlarr['sex_cn']="男";break;
		case 2:$setsqlarr['sex_cn']="女";break;
	}
	$setsqlarr['category']=trim($_POST['category'])?trim($_POST['category']):exit("您没有填写意向职位！");
	$setsqlarr['experience']=intval($_POST['experience'])?intval($_POST['experience']):exit("您没有选择工作经验！");
	$experience = $db->getone("select c_name from ".table('category')." where c_id=".$setsqlarr['experience']);
	$setsqlarr['experience_cn']=$experience['c_name'];
	if ($_CFG['simple_tel_edit']=="1")
	{
		$setsqlarr['tel']=trim($_POST['tel'])?trim($_POST['tel']):exit("您没有填写联系电话！");
		if(preg_match("/^\d*$/",$setsqlarr['tel']))
		{
			if ($captcha['simple_tel_repeat']=='0')
			{
				$sql = "select id from ".table('simple_resume')." where tel = '{$setsqlarr['tel']}' AND id<>'{$id}' LIMIT 1";
				$info=$db->getone($sql);
				if (!empty($info))
				{
					exit("电话号码已经存在！");
				}
			}
		}	
	}
	$setsqlarr['district']=intval($_POST['district'])?intval($_POST['district']):exit("您没有填写联系电话！");
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['sdistrict_cn']=trim($_POST['sdistrict_cn']);
	$setsqlarr['detailed']=trim($_POST['detailed'])!=""?trim($_POST['detailed']):exit("您没有填写个人简介！");
	$setsqlarr['refreshtime']=time();
	$days=intval($_POST['days']);
	if ($days>0)
	{
	$time=$info['deadline']>time()?$info['deadline']:time();
	$setsqlarr['deadline']=strtotime("{$days} day",$time);
	}
	$setsqlarr['likekey']=$setsqlarr['uname'].",".$setsqlarr['category'].",".$setsqlarr['address'].",".$setsqlarr['detailed'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']=$setsqlarr['uname'].$setsqlarr['address'].$setsqlarr['detailed'];
	$setsqlarr['key']="{$setsqlarr['uname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if($db->updatetable(table('simple_resume'),$setsqlarr," id='{$id}' "))
	{
		exit("ok");
	}
	else
	{
		exit("修改失败！");
	}
}
// 刷新微简历
elseif($act == "resume_refresh")
{
	$resumeid = intval($_GET['resumeid'])?intval($_GET['resumeid']):exit("参数错误！");
	$pass = trim($_GET['pass'])?trim($_GET['pass']):exit("参数错误！");
	$sql = "select * from ".table('simple_resume')." where id = '{$resumeid}' LIMIT 1";
	$info=$db->getone($sql);
	$thispwd=md5(md5($pass).$info['pwd_hash'].$QS_pwdhash);
	if ($thispwd==$info['pwd'])
	{
		$db->query("update ".table('simple_resume')."  SET refreshtime='".time()."' WHERE id = '{$resumeid}'");
		exit("刷新成功！");
	}
	else
	{
		exit("管理密码错误！");
	}
}
// 删除微简历
elseif($act == "resume_delete")
{
	$resumeid = intval($_GET['resumeid'])?intval($_GET['resumeid']):exit("参数错误！");
	$pass = trim($_GET['pass'])?trim($_GET['pass']):exit("参数错误！");
	$sql = "select * from ".table('simple_resume')." where id = '{$resumeid}' LIMIT 1";
	$info=$db->getone($sql);
	$thispwd=md5(md5($pass).$info['pwd_hash'].$QS_pwdhash);
	if ($thispwd==$info['pwd'])
	{
		$db->query("Delete from ".table('simple_resume')." WHERE id = '{$resumeid}'");
		exit("删除成功！");
	}
	else
	{
		exit("管理密码错误！");
	}
}

?>