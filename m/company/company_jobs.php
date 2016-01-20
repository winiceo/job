<?php
 /*
 * 74cms 触屏版职位管理模块
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
require_once(QISHI_ROOT_PATH.'include/fun_company.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'index';
if ($_SESSION['uid']=='' || $_SESSION['username']==''||intval($_SESSION['utype'])==2)
{
	header("Location: ../login.php");
}
$user = get_user_info($_SESSION['uid']);
if($_CFG['login_com_audit_mobile'] && $user['mobile_audit']=="0")
{
	$str= "<script>";
	$str.="alert('请先验证手机！');";
	$str.="window.location.href='account_security.php';";
	$str.= "</script>";
	echo $str;
}
elseif ($act == 'index')
{
	$smarty->cache = false;
	$uid=intval($_SESSION["uid"]);
	$wheresql=" select * from ".table("jobs")." where uid=$_SESSION[uid] union all select * from ".table("jobs_tmp")." where uid=$uid ";
	$row=get_jobs($offset,$perpage,$wheresql,true);
	$smarty->assign('row',$row);
	$smarty->display("m/company/m-job-index.html");	
}
// 发布职位ajax
elseif($act=="ajax_jobs_add")
{
	$company_info=get_company(intval($_SESSION['uid']));
	if($company_info['companyname'])
	{
		if ($_CFG['operation_mode']=="3")
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if (($setmeal['endtime']>time() || $setmeal['endtime']=="0") &&  $setmeal['jobs_ordinary']>0)
			{
				$add_mode = 2;
			}
			elseif($_CFG['setmeal_to_points']=="1")
			{
				$add_mode = 1;
			}
			else
			{
				$add_mode = 2;
			}
		}
		elseif ($_CFG['operation_mode']=="2")
		{
			$add_mode = 2;
		}
		elseif ($_CFG['operation_mode']=="1")
		{
			$add_mode = 1;
		}
		if ($add_mode=='1')
		{
			$points_rule=get_cache('points_rule');
			$user_points=get_user_points($_SESSION['uid']);
			if ($points_rule['jobs_add']['type']=="2" && $points_rule['jobs_add']['value']>0)
			{
				$total=$points_rule['jobs_add']['value'];
				if ($total>$user_points)
				{
					exit("你的".$_CFG['points_byname']."不足，请充值后再发布！");
				}
			}
		}
		elseif ($add_mode=='2')
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
			{					
				exit("您的服务已经到期，请重新开通");
			}
			/*
				显示中的职位(审核通过,审核中,未暂停)
			*/
			$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
			$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit=2 and display=1 ");
			$com_jobs_num=$jobs_num+$jobs_tmp_num;
			if ($com_jobs_num>=$setmeal['jobs_ordinary'])
			{
				exit("当前显示的职位已经超过了最大限制，请升级服务套餐！");
			}
		}
		exit("1");
	}else{
		exit("2");
		//header("Location: user.php?act=company_info_add");
	}
}
// 发布职位
elseif($act=="jobs_add")
{
	$smarty->cache = false;
	$company_info=get_company(intval($_SESSION['uid']));
	if($company_info['companyname'])
	{
		$smarty->assign('company_info',$company_info);
		if ($_CFG['operation_mode']=="3")
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if (($setmeal['endtime']>time() || $setmeal['endtime']=="0") &&  $setmeal['jobs_ordinary']>0)
			{
			$smarty->assign('setmeal',$setmeal);
			$add_mode = 2;
			$smarty->assign('add_mode',2);
			}
			elseif($_CFG['setmeal_to_points']=="1")
			{
			$smarty->assign('points_total',get_user_points($_SESSION['uid']));
			$smarty->assign('points',get_cache('points_rule'));
			$add_mode = 1;
			$smarty->assign('add_mode',1);
			}
			else
			{
			$smarty->assign('setmeal',$setmeal);
			$add_mode = 2;
			$smarty->assign('add_mode',2);
			}
			
		}
		elseif ($_CFG['operation_mode']=="2")
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			$smarty->assign('setmeal',$setmeal);
			$add_mode = 2;
			$smarty->assign('add_mode',2);
		}
		elseif ($_CFG['operation_mode']=="1")
		{
			$smarty->assign('points_total',get_user_points($_SESSION['uid']));
			$smarty->assign('points',get_cache('points_rule'));
			$add_mode = 1;
			$smarty->assign('add_mode',1);
		}
		if ($add_mode=='1')
		{
			$points_rule=get_cache('points_rule');
			$user_points=get_user_points($_SESSION['uid']);
			if ($points_rule['jobs_add']['type']=="2" && $points_rule['jobs_add']['value']>0)
			{
				$total=$points_rule['jobs_add']['value'];
				if ($total>$user_points)
				{
					exit("你的".$_CFG['points_byname']."不足，请充值后再发布！");
				}
			}
		}
		elseif ($add_mode=='2')
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
			{					
				exit("您的服务已经到期，请重新开通");
			}
			/*
				显示中的职位(审核通过,审核中,未暂停)
			*/
			$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
			$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit=2 and display=1 ");
			$com_jobs_num=$jobs_num+$jobs_tmp_num;
			if ($com_jobs_num>=$setmeal['jobs_ordinary'])
			{
				exit("当前显示的职位已经超过了最大限制，请升级服务套餐！");
			}
		}
		$smarty->display("m/company/m-create-job.html");
	}else{
		header("Location: user.php?act=company_info_add");
	}
}
// 保存职位
elseif($act=="jobs_add_save")
{
	$smarty->cache = false;
	$company_info=get_company(intval($_SESSION['uid']));
	$company_info = array_map("addslashes", $company_info);
	$_POST=array_map("utf8_to_gbk", $_POST);
	$add_mode=trim($_POST['add_mode']);
	if ($add_mode=='1')
	{
		$points_rule=get_cache('points_rule');
		$user_points=get_user_points($_SESSION['uid']);
		$total=0;
		if ($points_rule['jobs_add']['type']=="2" && $points_rule['jobs_add']['value']>0)
		{
			$total=$points_rule['jobs_add']['value'];
			if ($total>$user_points)
			{
			exit("你的".$_CFG['points_byname']."不足，请充值后再发布！");
			}
		}
		if($_CFG['operation_mode']=="1")
		{
			$setsqlarr['setmeal_deadline']=0;
		}
		elseif($_CFG['operation_mode']=="3")
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if(empty($setmeal))
			{
				$setsqlarr['setmeal_deadline']=0;
			}
			else
			{
				$setsqlarr['setmeal_deadline']=0;
				$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
				$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
			}
		}
	}
	elseif ($add_mode=='2')
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{					
			exit("您的服务已经到期，请重新开通");
		}
		if ($setmeal['jobs_ordinary']<=0)
		{
			exit("当前发布的职位已经超过了最大限制，请升级服务套餐！");
		}
		/*
			显示中的职位(审核通过,审核中,未暂停)
		*/
		$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
		$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit=2 and display=1 ");
		$com_jobs_num=$jobs_num+$jobs_tmp_num;
		if ($com_jobs_num>=$setmeal['jobs_ordinary'])
		{
			exit("当前显示的职位已经超过了最大限制，请升级服务套餐！");
		}
		$setsqlarr['setmeal_deadline']=$setmeal['endtime'];
		$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
		$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
	}
	$setsqlarr['add_mode']=intval($add_mode);
	$setsqlarr['uid']=intval($_SESSION['uid']);
	$setsqlarr['companyname']=$company_info['companyname'];
	$setsqlarr['company_id']=$company_info['id'];
	$setsqlarr['company_addtime']=$company_info['addtime'];
	$setsqlarr['company_audit']=$company_info['audit'];
	$setsqlarr['jobs_name']=!empty($_POST['jobs_name'])?trim($_POST['jobs_name']):exit('请填写职位名称！');
	$setsqlarr['nature']=62;
	$setsqlarr['nature_cn']="全职";
	$setsqlarr['topclass']=intval($_POST['topclass']);
	$setsqlarr['category']=!empty($_POST['category'])?intval($_POST['category']):exit('请选择职位类别！');
	$setsqlarr['subclass']=intval($_POST['subclass']);
	$setsqlarr['category_cn']=trim($_POST['category_cn']);
	$setsqlarr['amount']=0;
	$setsqlarr['contents']=!empty($_POST['contents'])?trim($_POST['contents']):exit('您没有填写职位描述！');
	$setsqlarr['wage']=intval($_POST['wage'])?intval($_POST['wage']):exit('请选择薪资待遇！');		
	$setsqlarr['wage_cn']=trim($_POST['wage_cn']);
	$setsqlarr['district']=!empty($_POST['district'])?intval($_POST['district']):exit('请选择工作地区！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['tag']=trim($_POST['tag']);		
	$setsqlarr['tag_cn']=trim($_POST['tag_cn']);
	$setsqlarr['sex']=3;
	$setsqlarr['sex_cn']="不限";
	check_word($_CFG['filter'],$_POST['contents'])?exit($_CFG['filter_tips']):'';
	$setsqlarr['trade']=$company_info['trade'];
	$setsqlarr['trade_cn']=$company_info['trade_cn'];
	$setsqlarr['scale']=$company_info['scale'];
	$setsqlarr['scale_cn']=$company_info['scale_cn'];
	$setsqlarr['street']=$company_info['street'];
	$setsqlarr['street_cn']=$company_info['street_cn'];
	$setsqlarr['addtime']=time();
	$setsqlarr['deadline']=strtotime("".intval($_CFG['company_add_days'])." day");
	$setsqlarr['refreshtime']=time();
	$setsqlarr['key']=$setsqlarr['jobs_name'].$company_info['companyname'].$setsqlarr['category_cn'].$setsqlarr['district_cn'].$setsqlarr['contents'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']="{$setsqlarr['jobs_name']} {$company_info['companyname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	$setsqlarr['tpl']=$company_info['tpl'];
	$setsqlarr['map_x']=$company_info['map_x'];
	$setsqlarr['map_y']=$company_info['map_y'];
	if ($company_info['audit']=="1")
	{
	$setsqlarr['audit']=intval($_CFG['audit_verifycom_addjob']);
	}
	else
	{
	$setsqlarr['audit']=intval($_CFG['audit_unexaminedcom_addjob']);
	}
	$setsqlarr_contact['contact']=!empty($_POST['contact'])?trim($_POST['contact']):exit('您没有填写联系人！');
	$setsqlarr_contact['address']=!empty($_POST['address'])?trim($_POST['address']):exit('您没有填写详细地址！');
	$setsqlarr_contact['email']=!empty($_POST['email'])?trim($_POST['email']):exit('您没有填写邮箱！');
	$setsqlarr_contact['telephone']=!empty($_POST['telephone'])?trim($_POST['telephone']):exit('您没有填写联系电话！');
	check_word($_CFG['filter'],$_POST['telephone'])?exit($_CFG['filter_tips']):'';

	$setsqlarr_contact['contact_show']=1;
	$setsqlarr_contact['email_show']=1;
	$setsqlarr_contact['telephone_show']=1;
	$setsqlarr_contact['address_show']=1;
	//添加职位信息
	$pid=$db->inserttable(table('jobs'),$setsqlarr,1);
	if(empty($pid)){
		exit("err");
	}
	//添加联系方式
	$setsqlarr_contact['pid']=$pid;
	if(!$db->inserttable(table('jobs_contact'),$setsqlarr_contact))exit("联系方式出错");
	if ($add_mode=='1')
	{
		if ($points_rule['jobs_add']['value']>0)
		{
		report_deal($_SESSION['uid'],$points_rule['jobs_add']['type'],$points_rule['jobs_add']['value']);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['jobs_add']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"发布了职位：<strong>{$setsqlarr['jobs_name']}</strong>，({$operator}{$points_rule['jobs_add']['value']})，(剩余:{$user_points})",1,1001,"发布职位","{$operator}{$points_rule['jobs_add']['value']}","{$user_points}");
		}
	}	
	elseif ($add_mode=='2')
	{
		//action_user_setmeal($_SESSION['uid'],"jobs_ordinary");
		$setmeal=get_user_setmeal($_SESSION['uid']);
		write_memberslog($_SESSION['uid'],1,9002,$_SESSION['username'],"发布普通职位:<strong>{$_POST['jobs_name']}</strong>，还可以发布普通职位:<strong>{$setmeal['jobs_ordinary']}</strong>条",2,1001,"发布职位","1","{$setmeal['jobs_ordinary']}");
	}
	$searchtab['id']=$pid;
	$searchtab['uid']=$setsqlarr['uid'];
	$searchtab['recommend']=$setsqlarr['recommend'];
	$searchtab['emergency']=$setsqlarr['emergency'];
	$searchtab['nature']=$setsqlarr['nature'];
	$searchtab['sex']=$setsqlarr['sex'];
	$searchtab['topclass']=$setsqlarr['topclass'];
	$searchtab['category']=$setsqlarr['category'];
	$searchtab['subclass']=$setsqlarr['subclass'];
	$searchtab['trade']=$setsqlarr['trade'];
	$searchtab['district']=$setsqlarr['district'];
	$searchtab['sdistrict']=$setsqlarr['sdistrict'];	
	$searchtab['street']=$company_info['street'];
	$searchtab['education']=$setsqlarr['education'];
	$searchtab['experience']=$setsqlarr['experience'];
	$searchtab['wage']=$setsqlarr['wage'];
	$searchtab['refreshtime']=$setsqlarr['refreshtime'];
	$searchtab['scale']=$setsqlarr['scale'];	
	$searchtab['setmeal_id']=$setsqlarr['setmeal_id'];
	//
	$db->inserttable(table('jobs_search_wage'),$searchtab);
	$db->inserttable(table('jobs_search_scale'),$searchtab);
	//
	$searchtab['map_x']=$setsqlarr['map_x'];
	$searchtab['map_y']=$setsqlarr['map_y'];
	$db->inserttable(table('jobs_search_rtime'),$searchtab);

	unset($searchtab['map_x'],$searchtab['map_y']);
	//
	$searchtab['stick']=$setsqlarr['stick'];
	$db->inserttable(table('jobs_search_stickrtime'),$searchtab);

	unset($searchtab['stick']);
	//
	$searchtab['click']=$setsqlarr['click'];
	$db->inserttable(table('jobs_search_hot'),$searchtab);

	unset($searchtab['click']);
	//
	$searchtab['key']=$setsqlarr['key'];
	$searchtab['likekey']=$setsqlarr['jobs_name'].','.$setsqlarr['companyname'];
	$searchtab['map_x']=$setsqlarr['map_x'];
	$searchtab['map_y']=$setsqlarr['map_y'];
	$db->inserttable(table('jobs_search_key'),$searchtab);
	unset($searchtab);
	add_jobs_tag($pid,$_SESSION['uid'],$_POST['tag'])?"":exit('err');
	distribution_jobs($pid,$_SESSION['uid']);
	write_memberslog($_SESSION['uid'],1,2001,$_SESSION['username'],"发布了职位：{$setsqlarr['jobs_name']}");
	baidu_submiturl(url_rewrite('QS_jobsshow',array('id'=>$pid)),'addjob');
	echo $pid;
}
// 职位发布成功页面
elseif($act=="addjobs_save_succeed")
{
	$smarty->cache = false;
	$jobs_id=intval($_GET["id"]);
	$jobs=jobs_one($jobs_id);
	$sql="select * from ".table("resume")." as r left join ".table("resume_jobs")." as rj on rj.pid=r.id where rj.category=$jobs[category] limit 5 ";
	$resume_list=$db->getall($sql);
	foreach ($resume_list as $key => $val) {
		$val['age']=date("Y")-$val['birthdate'];
		$resume_list[$key]=$val;
	}
	$smarty->assign('resume_list',$resume_list);
	$smarty->display("m/company/m-job-index.html");
}
// 职位修改 页面
elseif($act=="jobs_edit")
{
	$smarty->cache = false;
	$jobs=get_jobs_one($_GET['id'],$_SESSION['uid']);
	if($jobs){
		$jobs['contents'] = strip_tags(htmlspecialchars_decode($jobs['contents'],ENT_QUOTES));
	}
	$company_info=get_company($_SESSION['uid']);
	$smarty->assign('company_info',$company_info);
	$smarty->assign('jobs',$jobs);
	$smarty->display("m/company/m-jobs-edit.html");
}
elseif($act=="jobs_edit_save")
{
	$smarty->cache = false;
	$company_info=get_company($_SESSION['uid']);
	$company_info = array_map("addslashes", $company_info);
	$id=intval($_POST['id']);
	$_POST=array_map("utf8_to_gbk", $_POST);
	// var_dump($_POST);die;
	$add_mode=trim($_POST['add_mode']);
	if ($add_mode=='1')
	{
		$points_rule=get_cache('points_rule');
		$user_points=get_user_points($_SESSION['uid']);
		$total=0;
		if($points_rule['jobs_edit']['type']=="2" && $points_rule['jobs_edit']['value']>0)
		{
			$total=$points_rule['jobs_edit']['value'];
			if ($total>$user_points)
			{
			exit("你的".$_CFG['points_byname']."不足，请充值后再发布！");
			}
		}
	}
	elseif ($add_mode=='2')
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{					
			exit("您的套餐已经到期，请重新开通");
		}
	}
	$setsqlarr['jobs_name']=!empty($_POST['jobs_name'])?trim($_POST['jobs_name']):exit('请填写职位名称！');
	$setsqlarr['topclass']=intval($_POST['topclass']);
	$setsqlarr['category']=!empty($_POST['category'])?intval($_POST['category']):exit('请选择职位类别！');
	$setsqlarr['subclass']=intval($_POST['subclass']);
	$setsqlarr['category_cn']=trim($_POST['category_cn']);
	$setsqlarr['amount']=0;
	$setsqlarr['contents']=!empty($_POST['contents'])?trim($_POST['contents']):exit('您没有填写职位描述！');
	$setsqlarr['wage']=intval($_POST['wage'])?intval($_POST['wage']):exit('请选择薪资待遇！');		
	$setsqlarr['wage_cn']=trim($_POST['wage_cn']);
	$setsqlarr['tag']=trim($_POST['tag']);		
	$setsqlarr['tag_cn']=trim($_POST['tag_cn']);
	$setsqlarr['district']=!empty($_POST['district'])?intval($_POST['district']):exit('请选择工作地区！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	check_word($_CFG['filter'],$_POST['contents'])?exit($_CFG['filter_tips']):'';
	if ($add_mode=='1'){
		$setsqlarr['setmeal_deadline']=0;
		$setsqlarr['add_mode']=1;
	}elseif($add_mode=='2'){
		$setmeal=get_user_setmeal($_SESSION['uid']);
		$setsqlarr['setmeal_deadline']=$setmeal['endtime'];
		$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
		$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
		$setsqlarr['add_mode']=2;
	}
	$setsqlarr['deadline']=strtotime("".intval($_CFG['company_add_days'])." day");
	$setsqlarr['key']=$setsqlarr['jobs_name'].$company_info['companyname'].$setsqlarr['category_cn'].$setsqlarr['district_cn'].$setsqlarr['contents'];
	require_once(QISHI_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']="{$setsqlarr['jobs_name']} {$company_info['companyname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if ($company_info['audit']=="1")
	{
	$_CFG['audit_verifycom_editjob']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_verifycom_editjob']):'';
	}
	else
	{
	$_CFG['audit_unexaminedcom_editjob']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_unexaminedcom_editjob']):'';
	}
	$setsqlarr_contact['contact']=!empty($_POST['contact'])?trim($_POST['contact']):exit('您没有填写联系人！');
	$setsqlarr_contact['telephone']=!empty($_POST['telephone'])?trim($_POST['telephone']):exit('您没有填写联系电话！');
	$setsqlarr_contact['email']=!empty($_POST['email'])?trim($_POST['email']):exit('您没有填写联系邮箱！');
	$setsqlarr_contact['address']=!empty($_POST['address'])?trim($_POST['address']):exit('您没有填写详细地址！');
	check_word($_CFG['filter'],$_POST['telephone'])?exit($_CFG['filter_tips']):'';

	$setsqlarr_contact['contact_show']=1;
	$setsqlarr_contact['email_show']=1;
	$setsqlarr_contact['telephone_show']=1;
	$setsqlarr_contact['address_show']=1;

	if (!$db->updatetable(table('jobs'), $setsqlarr," id='{$id}' AND uid='{$_SESSION['uid']}' ")) exit("err");
	if (!$db->updatetable(table('jobs_tmp'), $setsqlarr," id='{$id}' AND uid='{$_SESSION['uid']}' ")) exit("err");
	if (!$db->updatetable(table('jobs_contact'), $setsqlarr_contact," pid='{$id}' ")){
		exit("err");
	}
	if ($add_mode=='1')
	{
		if ($points_rule['jobs_edit']['value']>0)
		{
		report_deal($_SESSION['uid'],$points_rule['jobs_edit']['type'],$points_rule['jobs_edit']['value']);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['jobs_edit']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"修改职位：<strong>{$setsqlarr['jobs_name']}</strong>，({$operator}{$points_rule['jobs_edit']['value']})，(剩余:{$user_points})",1,1002,"修改招聘信息","{$operator}{$points_rule['jobs_edit']['value']}","{$user_points}");
		}
	}
	//标签
	add_jobs_tag(intval($_POST['id']),$_SESSION['uid'],$_POST['tag'])?"":exit("err");
	$searchtab['topclass']=$setsqlarr['topclass'];
	$searchtab['category']=$setsqlarr['category'];
	$searchtab['subclass']=$setsqlarr['subclass'];
	$searchtab['district']=$setsqlarr['district'];
	$searchtab['sdistrict']=$setsqlarr['sdistrict'];
	$searchtab['wage']=$setsqlarr['wage'];
	//
	$db->updatetable(table('jobs_search_wage'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_rtime'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_stickrtime'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_hot'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_scale'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}'");
	$searchtab['key']=$setsqlarr['key'];
	$searchtab['likekey']=$setsqlarr['jobs_name'].','.$company_profile['companyname'];
	$db->updatetable(table('jobs_search_key'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	unset($searchtab);
	// distribution_jobs($id,$_SESSION['uid']);
	write_memberslog($_SESSION['uid'],$_SESSION['utype'],2002,$_SESSION['username'],"修改了职位：{$setsqlarr['jobs_name']}，职位ID：{$id}");
	exit("ok");
}
// 职位刷新
elseif($act=="jobs_refresh")
{
	$smarty->cache = false;
	$id=intval($_POST['id']);
	$jobs_num = 1;
	//积分模式
	if($_CFG['operation_mode']=='1'){
		//限制刷新时间
		//最经一次的刷新时间
		$refrestime=get_last_refresh_date($_SESSION['uid'],"1001");
		$duringtime=time()-$refrestime['max(addtime)'];
		$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001");
		if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
		{
		exit("每天最多只能刷新".$_CFG['com_pointsmode_refresh_time']."次,您今天已超过最大刷新次数限制！");	
		}
		elseif($duringtime<=$space){
		exit($_CFG['com_pointsmode_refresh_space']."分钟内不能重复刷新职位！");
		}
		else 
		{	
			$points_rule=get_cache('points_rule');
			if($points_rule['jobs_refresh']['value']>0)
			{
				$user_points=get_user_points($_SESSION['uid']);
				$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
				if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
				{
						$link[0]['text'] = "返回上一页";
						$link[0]['href'] = 'javascript:history.go(-1)';
						$link[1]['text'] = "立即充值";
						$link[1]['href'] = 'company_service.php?act=order_add';
				showmsg("您的".$_CFG['points_byname']."不足，请先充值！",0,$link);
				}
				report_deal($_SESSION['uid'],$points_rule['jobs_refresh']['type'],$total_point);
				$user_points=get_user_points($_SESSION['uid']);
				$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
				write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"刷新了{$jobs_num}条职位，({$operator}{$total_point})，(剩余:{$user_points})",1,1003,"刷新职位","{$operator}{$total_point}","{$user_points}");
				write_refresh_log($_SESSION['uid'],1001);	
				refresh_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
			}
			write_refresh_log($_SESSION['uid'],1001);
			refresh_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
		}
	}	
	//套餐模式
	elseif($_CFG['operation_mode']=='2') 
	{
		//限制刷新时间
		//最经一次的刷新时间
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if (empty($setmeal))
		{					
			exit("您还没有开通服务，请开通");
		}
		elseif ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{					
			exit("您的服务已经到期，请重新开通");
		}
		else
		{
			$refrestime=get_last_refresh_date($_SESSION['uid'],"1001");
			$duringtime=time()-$refrestime['max(addtime)'];
			$space = $setmeal['refresh_jobs_space']*60;
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001");
			if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
			{
			exit("每天最多只能刷新".$setmeal['refresh_jobs_time']."次,您今天已超过最大刷新次数限制！");
			}
			elseif($duringtime<=$space){
			exit($setmeal['refresh_jobs_space']."分钟内不能重复刷新职位！");	
			}
			write_refresh_log($_SESSION['uid'],1001);
			refresh_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
		}
	}
	//混合模式
	elseif($_CFG['operation_mode']=='3') 
	{
		//限制刷新时间
		//最经一次的刷新时间
		$setmeal=get_user_setmeal($_SESSION['uid']);
		$refrestime=get_last_refresh_date($_SESSION['uid'],"1001");
		$duringtime=time()-$refrestime['max(addtime)'];
		$space = $setmeal['refresh_jobs_space']*60;
		$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001");
		if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
		{
		exit("每天最多只能刷新".$setmeal['refresh_jobs_time']."次,您今天已超过最大刷新次数限制！");
		}
		elseif($duringtime<=$space){
		exit($setmeal['refresh_jobs_space']."分钟内不能重复刷新职位！");	
		}
		else
		{
			$points_rule=get_cache('points_rule');
			if($points_rule['jobs_refresh']['value']>0)
			{
				$user_points=get_user_points($_SESSION['uid']);
				$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
				if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
				{
				exit("您的".$_CFG['points_byname']."不足，请先充值！");
				}
				report_deal($_SESSION['uid'],$points_rule['jobs_refresh']['type'],$total_point);
				$user_points=get_user_points($_SESSION['uid']);
				$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
				write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"刷新了{$jobs_num}条职位，({$operator}{$total_point})，(剩余:{$user_points})",1,1003,"刷新职位","{$operator}{$total_point}","{$user_points}");
				write_refresh_log($_SESSION['uid'],1001);
				refresh_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
			}
			else
			{
				write_refresh_log($_SESSION['uid'],1001);
				refresh_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
			}
		}
	}
}
// 暂停 职位 
elseif($act=="jobs_pause")
{
	$smarty->cache = false;
	$id=intval($_POST['id']);
	$uid=intval($_SESSION["uid"]);
	activate_jobs($id,2,$_SESSION['uid']);
	exit("ok");
}
// 暂停职位 恢复
elseif($act=="jobs_regain")
{
	$smarty->cache = false;
	$id=intval($_POST['id']);
	$uid=intval($_SESSION["uid"]);
	$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
	$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit<>3 and display=1 ");
	$com_jobs_num=$jobs_num+$jobs_tmp_num;
	$setmeal= get_user_setmeal($_SESSION['uid']);
	if ($com_jobs_num>=$setmeal['jobs_ordinary'])
	{
		exit("当前显示的职位已经超过了最大限制，请升级服务套餐，或将不招聘的职位设为关闭！");
	}else
	{
		activate_jobs($id,1,$_SESSION['uid']);
		exit("ok");
	}
}
// 删除职位 
elseif($act=="jobs_del")
{
	$smarty->cache = false;
	$id=intval($_POST['id']);
	del_jobs($id,intval($_SESSION['uid']))?exit("ok"):exit("err");
}
// 职位推广
elseif($act=="set_promotion")
{
	$catid = intval($_GET['catid'])?intval($_GET['catid']):1;
	$jobid = intval($_GET['jobid'])?intval($_GET['jobid']):exit("参数错误！");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	$jobinfo = get_jobs_one($jobid);
	$promotion = get_promotion_category_one($catid);
	if ($_CFG['operation_mode']=='2')
	{
		$setmeal=get_user_setmeal($uid);//获取会员套餐
		if($setmeal['endtime']<time() && $setmeal['endtime']<>'0')
		{
			$end=1;//判断套餐是否到期
			exit("套餐已到期！");
		}
		else
		{
			$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
			exit("2"."|".$data['days']);
		}
	}
	elseif($_CFG['operation_mode']=='1')
	{
		$points = get_user_points($uid);
		exit("1"."|".$promotion['cat_points']."|".$promotion['cat_minday']."|".$promotion['cat_maxday']);
	}
	elseif($_CFG['operation_mode']=='3')
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);//获取会员套餐
		if($setmeal['endtime']<time() && $setmeal['endtime']<>'0')
		{
			if($_CFG['setmeal_to_points']!=1)
			{
				exit("套餐已到期！");
			}
			else
			{
				$points = get_user_points($uid);
				exit("1"."|".$promotion['cat_points']."|".$promotion['cat_minday']."|".$promotion['cat_maxday']);
			}
		}
		else
		{
			$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
			exit("2"."|".$data['days']);
		}
	}
}
// 职位推广混合模式下判断该推广类型是否使用积分
elseif($act=="set_promotion_operation")
{
	$catid = intval($_GET['catid'])?intval($_GET['catid']):exit("参数错误！");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	if($_CFG['operation_mode'] != 3)
	{
		exit('模式错误！');
	}
	$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
	if($data['num'] >= 1)
	{
		exit("2"."|".$data['days']);
	}
	else
	{
		$promotion = get_promotion_category_one($catid);
		exit("1"."|".$promotion['cat_points']."|".$promotion['cat_minday']."|".$promotion['cat_maxday']);
	}
}
// 积分模式下判断返回该推广类型的 每天消耗积分、推广最小天数、最大天数
elseif($act=="set_promotion_points")
{
	$catid = intval($_GET['catid'])?intval($_GET['catid']):exit("参数错误！");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	$promotion = get_promotion_category_one($catid);
	if($promotion)
	{
		exit("1"."|".$promotion['cat_points']."|".$promotion['cat_minday']."|".$promotion['cat_maxday']);
	}
	else
	{
		exit("参数错误！");
	}
}
// 套餐模式下判断返回该推广类型的 推广天数
elseif($act=="set_promotion_setmeal")
{
	$catid = intval($_GET['catid'])?intval($_GET['catid']):exit("参数错误！");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
	if($data)
	{
		exit("2"."|".$data['days']);
	}
	else
	{
		exit("参数错误！");
	}
}
// 保存推广
elseif($act=="set_promotion_save")
{
	// typeid : 运营模式
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	$catid = intval($_GET['catid'])?intval($_GET['catid']):exit("参数错误！");
	$typeid = intval($_GET['typeid'])?intval($_GET['typeid']):exit("参数错误！");
	$jobid = intval($_GET['jobid'])?intval($_GET['jobid']):exit("参数错误！");
	$day = intval($_GET['day'])?intval($_GET['day']):exit("参数错误！");
	$jobs=get_jobs_one($jobid,$uid);
	$jobs = array_map("addslashes", $jobs);
	if ($jobid>0 && $day>0)
	{
		$pro_cat=get_promotion_category_one($catid);
		if($typeid == 1)
		{
			if ($pro_cat['cat_points']>0)
			{
				$points=$pro_cat['cat_points']*$day;
				$user_points=get_user_points($uid);
				if ($points>$user_points)
				{
					exit("你的".$_CFG['points_byname']."不够进行此次操作，请先充值！");
				}
			}
		}
		elseif($typeid == 2)
		{
			$setmeal=get_setmeal_promotion($uid,$catid);//获取会员套餐
			$num=$setmeal['num'];
			if(($setmeal['endtime']<time() && $setmeal['endtime']<>'0') || $num<=0)
			{
				exit("你的套餐已到期或套餐内剩余{$pro_cat['cat_name']}不够，请尽快开通新套餐");
			}
		}
		$info=get_promotion_one($jobid,$uid,$catid);
		if (!empty($info))
		{
			exit("此职位正在推广中，请选择其他职位或其他方案");
		}
		$setsqlarr['cp_available']=1;
		$setsqlarr['cp_promotionid']=$catid;
		$setsqlarr['cp_uid']=$uid;
		$setsqlarr['cp_jobid']=$jobid;
		$setsqlarr['cp_days']=$day;
		$setsqlarr['cp_starttime']=time();
		$setsqlarr['cp_endtime']=strtotime("{$day} day");
		$color = get_color();
		$val_code = $color[0]['value'];
		$setsqlarr['cp_val']=$val_code;
		if ($db->inserttable(table('promotion'),$setsqlarr))
		{
			set_job_promotion($jobid,$setsqlarr['cp_promotionid'],$val_code);
			if($typeid == 1 && $pro_cat['cat_points']>0)
			{
				report_deal($uid,2,$points);
				$user_points=get_user_points($uid);
				write_memberslog($uid,1,9001,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$day} 天，(-{$points})，(剩余:{$user_points})",1,1018,"{$pro_cat['cat_name']}","-{$points}","{$user_points}");
			}
			elseif($typeid == 2)
			{
				$user_pname=trim($_POST['pro_name']);
				action_user_setmeal($uid,$setmeal['name']); //更新套餐中相应推广方式的条数
				$setmeal=get_user_setmeal($uid);//获取会员套餐
				write_memberslog($uid,1,9002,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$day} 天，套餐内剩余{$pro_cat['cat_name']}条数：{$setmeal[$user_pname]}条。",2,1018,"{$pro_cat['cat_name']}","-{$day}","{$setmeal[$user_pname]}");//9002是套餐操作
			}
			write_memberslog($uid,1,3004,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$day} 天。");
			exit('ok');
		}
		else
		{
			exit("推广失败！");
		}
	}
	else
	{
		exit("职位或者推广天数参数错误！");
	}
}

?>