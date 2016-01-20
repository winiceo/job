<?php
 /*
 * 74cms 招聘会
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
require_once(ADMIN_ROOT_PATH.'include/admin_jobfair_fun.php');
require_once(ADMIN_ROOT_PATH.'include/upload.php');
$ads_updir="../data/comads/";
$ads_dir=$_CFG['site_dir']."data/comads/";
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';
$smarty->assign('act',$act);
if($act == 'list')
{	
	check_permissions($_SESSION['admin_purview'],"jobfair");
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	$oederbysql=" order BY id DESC";
	if ($key && $key_type>0)
	{
		
		if     ($key_type==1)$wheresql=" WHERE title like '%{$key}%'";
	}
	else
	{
		if (!empty($_GET['settr']))
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql=empty($wheresql)?" WHERE addtime> ".$settr:$wheresql." AND addtime> ".$settr;
		}
	}
	
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobfair_section')." ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('list',get_jobfair_section($offset, $perpage,$wheresql.$oederbysql));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('time',time());
	$smarty->assign('pageheader',"专场招聘会");
	get_token();
	$smarty->display('jobfair/admin_jobfair_section.htm');
}
elseif($act =='jobfair_section_del')
{
	$id=$_REQUEST['id'];
	if (empty($id)) adminmsg("请选择项目！",1);
	if ($n=del_jobfair_section($id))
	{
	adminmsg("删除成功！影响行数 {$n}",2);
	}
}
elseif($act =='jobfair_add')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	$smarty->assign('pageheader',"添加专场招聘会");
	get_token();
	$smarty->display('jobfair/admin_jobfair_section_add.htm');
 
}
elseif($act == 'addsave')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	check_token();
	$setsqlarr['title']=!empty($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['trade']=trim($_POST['trade'])?trim($_POST['trade']):adminmsg('清选择招聘会行业！',1);
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	
	$tradearr = explode(',', $setsqlarr['trade']);
	if(count($tradearr)>1)
	{
		$setsqlarr['is_singleness']=2;
	}else
	{
		$setsqlarr['is_singleness']=1;
	}
	$setsqlarr['addtime']=time();
	$insertid = $db->inserttable(table('jobfair_section'),$setsqlarr,1);
	if ($insertid)
	{
	write_log("添加专场招聘会：".$setsqlarr['title'], $_SESSION['admin_name'],3);
	$link[0]['text'] = "添加专场招聘会参企业";
	$link[0]['href'] = '?act=jobfair_section_show&id='.$insertid;
	adminmsg("添加成功！",2,$link);	
	}
	else
	{
	adminmsg("添加失败！",0);
	}	
}
elseif($act == "editsave")
{
	$jobfair_id=intval($_POST['jobfair_id'])>0?intval($_POST['jobfair_id']):adminmsg("招聘会ID丢失！",1);

	$setsqlarr['title']=!empty($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['trade']=trim($_POST['trade'])?trim($_POST['trade']):adminmsg('清选择招聘会行业！',1);
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	$tradearr = explode(',', $setsqlarr['trade']);
	if(count($tradearr)>1)
	{
		$setsqlarr['is_singleness']=2;
	}else
	{
		$setsqlarr['is_singleness']=1;
	}
	$setsqlarr['addtime']=time();
	if ($db->updatetable(table('jobfair_section'),$setsqlarr,array('id'=>$jobfair_id)))
	{
	$db->query("delete from ".table('jobfair_section_company')." where trade not in({$setsqlarr['trade']}) and jobfair_id=$jobfair_id ");
	adminmsg("修改成功！",2);	
	}
	else
	{
	adminmsg("修改失败！",0);
	}	
}
// 专场招聘会详细
elseif($act == 'jobfair_section_show')
{
	$id=intval($_GET['id'])>0?intval($_GET['id']):adminmsg('id丢失！',0);
	$show = get_jobfair_section_one($id);
	// 参会企业列表
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$joinsql =" left join ".table('company_profile')." as c on j.company_id=c.id ";
	$wheresql=" where j.jobfair_id=$id ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobfair_section_company')." as j ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$company_list = get_jobfair_section_company($offset, $perpage,$joinsql.$wheresql);
	$smarty->assign('company_list',$company_list);

	foreach ($company_list as $key => $value) {
		$company_id_str .=','.$value['company_id'];
	}
	$company_id_str =ltrim($company_id_str,',');
	$smarty->assign('company_id_str',$company_id_str);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('show',$show);
	
	// 参会行业的 企业
	$company_select = $db->getall("select companyname,id,trade,trade_cn from ".table('company_profile')." where trade in ($show[trade])");
	$trade_arr = explode(',', $show['trade_cn']);
	foreach ($trade_arr as $key => $value) {
		$trade_arr_[$value]='';
	}
	foreach ($company_select as $key => $value) {
		$value['companyname'] = cut_str($value['companyname'],10,0,"...");
		$trade_arr_[$value['trade_cn']][]=$value;

	}
	$trade_arr_title= array_keys($trade_arr_);
	$trade_arr_com= array_values($trade_arr_);
	$smarty->assign('trade_arr_title',$trade_arr_title);
	$smarty->assign('trade_arr_com',$trade_arr_com);


	$smarty->assign('pageheader',"专场招聘会详细");
	$smarty->display('jobfair/admin_jobfair_section_show.htm');
}
elseif($act == 'jobfair_section_company_add')
{
	global $db;
	$jobfair_id= intval($_POST['jobfair_id'])>0?intval($_POST['jobfair_id']):exit("err1");
	$company_id=trim($_POST['company_id'])?trim($_POST['company_id']):exit('err2');
	$trade_id=trim($_POST['trade_id'])?trim($_POST['trade_id']):exit('err2');
	$company_arr =explode(',', $company_id);
	$trade_arr =explode(',', $trade_id);
	// 先删除 
	$db->query("delete from ".table('jobfair_section_company')." where jobfair_id=$jobfair_id and company_id not in($company_id) ");
	foreach ($company_arr as $key => $value) {
		$company = $db->getone("select id from ".table('jobfair_section_company')." where company_id=$value and jobfair_id=$jobfair_id limit 1 ");
		if(!empty($company))
		{
			continue;
		}
		$db->inserttable(table('jobfair_section_company'),array('jobfair_id'=>$jobfair_id,'company_id'=>$value,'trade'=>$trade_arr[$key],'addtime'=>time()));
	}
	exit("ok");
}
// 修改参会企业 签名
elseif($act == 'jobfair_section_company_edit')
{
	global $db;
	$jobfair_id=intval($_POST['jobfair_id'])>0?intval($_POST['jobfair_id']):adminmsg("招聘会ID丢失！",1);
	foreach ($_POST['company_signature'] as $key => $value) 
	{
		$settr['company_signature']=$value;
		$db->updatetable(table('jobfair_section_company'),$settr,array('jobfair_id'=>$jobfair_id,'id'=>$key));
	}
	adminmsg("保存成功！",2);
}
// 删除参会企业
elseif($act == 'jobfair_section_company_del')
{
	$id=$_REQUEST['id'];
	if (empty($id)) adminmsg("请选择项目！",1);
	if ($n=del_jobfair_section_company($id))
	{
	adminmsg("删除成功！影响行数 {$n}",2);
	}
}

elseif($act == 'exhibitors')
{	
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	$oederbysql=" order BY id DESC";
	if ($key && $key_type>0)
	{
		
		if     ($key_type==1)$wheresql=" WHERE companyname like '%{$key}%'";
		if     ($key_type==2)$wheresql=" WHERE jobfair_title like '%{$key}%'";
	}
	else
	{
		if ($_GET['predetermined_status']<>'')
		{
		$wheresql=" WHERE audit=".intval($_GET['audit']);
		}
		if (!empty($_GET['etypr']))
		{
			$wheresql=empty($wheresql)?" WHERE etypr=".intval($_GET['etypr']):$wheresql." AND etypr> ".intval($_GET['etypr']);
		}
		if (!empty($_GET['settr']))
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql=empty($wheresql)?" WHERE eaddtime> ".$settr:$wheresql." AND eaddtime> ".$settr;
		}
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobfair_exhibitors')." ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('list',get_jobfair_exhibitors($offset, $perpage,$wheresql.$oederbysql));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('time',time());
	$smarty->assign('pageheader',"参会企业");
	get_token();
	$smarty->display('jobfair/admin_jobfair_exhibitors.htm');
}
elseif($act =='exhibitors_del')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	$id=$_REQUEST['id'];
	if (empty($id)) adminmsg("请选择项目！",1);
	check_token();
	if ($n=del_exhibitors($id))
	{
	adminmsg("删除成功！影响行数 {$n}",2);
	}
}
elseif($act =='exhibitors_audit')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	$id=$_REQUEST['id'];
	if (empty($id)) adminmsg("请选择项目！",1);
	if ($n=edit_exhibitors_audit($id,$_POST['audit']))
	{
	adminmsg("设置成功！影响行数 {$n}",2);
	}
}
elseif($act =='exhibitors_add')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	get_token();
	$smarty->assign('pageheader',"参会企业");	
	$smarty->assign('jobfair',get_jobfair_audit());
	$smarty->display('jobfair/admin_jobfair_exhibitors_add.htm'); 
}
elseif($act == 'exhibitors_add_save')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	check_token();	
	//判断该企业是非人才网用户还是人才网用户
	$t=$_POST['t'];
	if ($t=="2")
	{
		$setsqlarr['companyname']=!empty($_POST['companyname'])?trim($_POST['companyname']):adminmsg('您没有企业名称！',1);
	}
	elseif ($t=="1")
	{
		$comid=intval($_POST['comid']);
		if ($comid==0)
		{
			adminmsg('请选择企业！',1);
		}
		else
		{
			$sql = "select * from ".table('company_profile')." where id={$comid} LIMIT 1";
			$info=$db->getone($sql);
			if (empty($info))
			{
			adminmsg('企业不存在！',1);
			}
			$setsqlarr['uid']=$info['uid'];
			$setsqlarr['companyname']=$info['companyname'];
			$setsqlarr['company_id']=$info['id'];
			$setsqlarr['company_addtime']=$info['addtime'];
		}
	}
	$setsqlarr['audit']=intval($_POST['audit']);
	$setsqlarr['etypr']=intval($_POST['etypr']);	
	$setsqlarr['eaddtime']=time();
	$setsqlarr['note']=trim($_POST['note']);
	//查看选择的招聘会是否存在
	$setsqlarr['jobfairid']=intval($_POST['jobfairid']);
	$sql = "select * from ".table('jobfair')." where id={$setsqlarr['jobfairid']} LIMIT 1";
	$jobfair=$db->getone($sql);
	if (empty($jobfair))
	{
	adminmsg('招聘会不存在',1);
	}
	$setsqlarr['jobfair_title']=$jobfair['title'];
	$setsqlarr['jobfair_addtime']=$jobfair['addtime'];
	//判断该企业是否已经预订过该招聘会
	$is_join = $db->getone("select * from ".table('jobfair_exhibitors')." where companyname='".trim($setsqlarr['companyname'])."' and jobfairid=".$setsqlarr['jobfairid']." limit 1 ");
	if(!empty($is_join))
	{
		adminmsg('该企业已经预订过此招聘会',1);
	}
	if ($db->inserttable(table('jobfair_exhibitors'),$setsqlarr))
	{
	write_log("添加参会企业：".$setsqlarr['companyname'], $_SESSION['admin_name'],3);
	$link[0]['text'] = "继续添加";
	$link[0]['href'] = '?act=exhibitors_add';
	$link[1]['text'] = "返回列表";
	$link[1]['href'] = '?act=exhibitors';
	adminmsg("添加成功！",2,$link);	
	}
	else
	{
	adminmsg("添加失败！",0);
	}	
}
elseif($act == 'exhibitors_edit')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	get_token();
	$id=intval($_GET['id']);
	$sql = "select * from ".table('jobfair_exhibitors')." where id='{$id}' LIMIT 1";
	$info=$db->getone($sql);
	if ($info['uid']>0)
	{
	$info['company_url']=url_rewrite('QS_companyshow',array('id'=>$info['company_id']));
	}
	$smarty->assign('info',$info);
	$smarty->assign('jobfair',get_jobfair_audit());
	$smarty->assign('pageheader',"招聘会");	
	$smarty->display('jobfair/admin_jobfair_exhibitors_edit.htm');
}
elseif($act == 'exhibitors_edit_save')
{
	check_permissions($_SESSION['admin_purview'],"jobfair_exhibitors");
	check_token();
	$id=intval($_POST['id']);
	$setsqlarr['companyname']=!empty($_POST['companyname'])?trim($_POST['companyname']):adminmsg('您没有企业名称！',1);	 
	$setsqlarr['audit']=intval($_POST['audit']);
	$setsqlarr['etypr']=intval($_POST['etypr']);	
	$setsqlarr['note']=trim($_POST['note']);
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=exhibitors';
	write_log("修改id为：".$id."的参会企业信息", $_SESSION['admin_name'],3);
	!$db->updatetable(table('jobfair_exhibitors'),$setsqlarr," id=".$id."")?adminmsg("修改失败！",0):adminmsg("修改成功！",2,$link);
}
?>