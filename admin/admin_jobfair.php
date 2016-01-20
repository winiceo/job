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
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'jobfair';
$smarty->assign('act',$act);
if($act == 'jobfair')
{	
	check_permissions($_SESSION['admin_purview'],"jobfair");
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	$oederbysql=" order BY `order` DESC,id DESC";
	if ($key && $key_type>0)
	{
		
		if     ($key_type==1)$wheresql=" WHERE title like '%{$key}%'";
	}
	else
	{
		if (!empty($_GET['predetermined_status']))
		{
		$wheresql=" WHERE predetermined_status=".intval($_GET['predetermined_status']);
		}
		if (!empty($_GET['settr']))
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql=empty($wheresql)?" WHERE addtime> ".$settr:$wheresql." AND addtime> ".$settr;
		}
	}
	
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobfair')." ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('list',get_jobfair($offset, $perpage,$wheresql.$oederbysql));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('time',time());
	$smarty->assign('pageheader',"招聘会");
	get_token();
	$smarty->display('jobfair/admin_jobfair.htm');
}
elseif($act =='jobfair_del')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	$id=$_REQUEST['id'];
	if (empty($id)) adminmsg("请选择项目！",1);
	check_token();
	if ($n=del_jobfair($id))
	{
	adminmsg("删除成功！影响行数 {$n}",2);
	}
}
elseif($act =='jobfair_add')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	$smarty->assign('pageheader',"添加招聘会");
	get_token();
	$smarty->display('jobfair/admin_jobfair_add.htm');
 
}
elseif($act == 'addsave')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	check_token();
	$setsqlarr['title']=!empty($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['number']=trim($_POST['number']);
	$setsqlarr['holddate_start']=intval(convert_datefm($_POST['holddate_start'],2));
	$setsqlarr['holddate_end']=intval(convert_datefm($_POST['holddate_end'],2));
	if (empty($setsqlarr['holddate_start']) || empty($setsqlarr['holddate_end']))
	{
		adminmsg('请填写举办日期！',1);
	}	
	$setsqlarr['address']=!empty($_POST['address'])?trim($_POST['address']):adminmsg('您没有填写举办地址！',1);
	$setsqlarr['bus']=trim($_POST['bus']);
	$setsqlarr['contact']=!empty($_POST['contact'])?trim($_POST['contact']):adminmsg('您没有填写联系人！',1);
	$setsqlarr['service_setmeal']=!empty($_POST['service_setmeal'])?trim($_POST['service_setmeal']):adminmsg('请填写服务套餐！',1);
	$setsqlarr['price']=!empty($_POST['price'])?trim($_POST['price']):adminmsg('请填写展位价格！',1);
	$setsqlarr['trade']=trim($_POST['trade']);
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	$setsqlarr['map_x']=!empty($_POST['x'])?trim($_POST['x']):adminmsg('请标注地图！',1);
	$setsqlarr['map_y']=!empty($_POST['y'])?trim($_POST['y']):adminmsg('请标注地图！',1);
	$setsqlarr['map_zoom']=trim($_POST['zoom']);
	$setsqlarr['phone']=!empty($_POST['phone'])?trim($_POST['phone']):adminmsg('您没有填写联系电话！',1);
	$setsqlarr['display']=intval($_POST['display']);
	$setsqlarr['order']=intval($_POST['order']);
	$setsqlarr['introduction']=!empty($_POST['introduction'])?$_POST['introduction']:adminmsg('请填写招聘会简介',1);
	$setsqlarr['predetermined_status']=intval($_POST['predetermined_status']);
	$setsqlarr['predetermined_web']=intval($_POST['predetermined_web']);
	$setsqlarr['predetermined_tel']=intval($_POST['predetermined_tel']);
	$setsqlarr['predetermined_point']=intval($_POST['predetermined_point']);
	if ($_POST['predetermined_start']=="")
	{
		$setsqlarr['predetermined_start']=0;
	}
	else
	{
		$setsqlarr['predetermined_start']=intval(convert_datefm($_POST['predetermined_start'],2));
	}
	if ($_POST['predetermined_end']=="")
	{
		$setsqlarr['predetermined_end']=0;
	}
	else
	{
		$setsqlarr['predetermined_end']=intval(convert_datefm($_POST['predetermined_end'],2));
	}
	$setsqlarr['addtime']=time();
	$insertid = $db->inserttable(table('jobfair'),$setsqlarr,1);
	if ($insertid)
	{
	write_log("添加招聘会：".$setsqlarr['title'], $_SESSION['admin_name'],3);
	baidu_submiturl(url_rewrite('QS_jobfairshow',array('id'=>$insertid)),'addjobfair');
	$link[0]['text'] = "继续添加";
	$link[0]['href'] = '?act=jobfair_add';
	$link[1]['text'] = "返回列表";
	$link[1]['href'] = '?act=';
	adminmsg("添加成功！",2,$link);	
	}
	else
	{
	adminmsg("添加失败！",0);
	}	
}
elseif($act == 'jobfair_edit')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	$id=intval($_GET['id']);
	$sql = "select * from ".table('jobfair')." where id=".intval($id)." LIMIT 1";
	$info=$db->getone($sql);
	$info['holddate_start']=convert_datefm($info['holddate_start'],1);
	$info['holddate_end']=convert_datefm($info['holddate_end'],1);
	if ($info['predetermined_start']=="0")
	{
		$info['predetermined_start']="";
	}
	else
	{
		$info['predetermined_start']=convert_datefm($info['predetermined_start'],1);
	}
	if ($info['predetermined_end']=="0")
	{
		$info['predetermined_end']="";
	}
	else
	{
		$info['predetermined_end']=convert_datefm($info['predetermined_end'],1);
	}
	$smarty->assign('info',$info);
	$smarty->assign('pageheader',"招聘会");
	get_token();
	$smarty->display('jobfair/admin_jobfair_edit.htm');
}
elseif($act == 'editsave')
{
	check_permissions($_SESSION['admin_purview'],"jobfair");
	check_token();
	$id=intval($_POST['id']);
	$setsqlarr['title']=!empty($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['number']=trim($_POST['number']);
	$setsqlarr['holddate_start']=intval(convert_datefm($_POST['holddate_start'],2));
	$setsqlarr['holddate_end']=intval(convert_datefm($_POST['holddate_end'],2));
	if (empty($setsqlarr['holddate_start']) || empty($setsqlarr['holddate_end']))
	{
	adminmsg('请填写举办日期！',1);
	}	
	$setsqlarr['address']=!empty($_POST['address'])?trim($_POST['address']):adminmsg('您没有填写举办地址！',1);
	$setsqlarr['bus']=trim($_POST['bus']);
	$setsqlarr['contact']=!empty($_POST['contact'])?trim($_POST['contact']):adminmsg('您没有填写联系人！',1);
	$setsqlarr['service_setmeal']=!empty($_POST['service_setmeal'])?trim($_POST['service_setmeal']):adminmsg('请填写服务套餐！',1);
	$setsqlarr['price']=!empty($_POST['price'])?trim($_POST['price']):adminmsg('请填写展位价格！',1);
	$setsqlarr['trade']=trim($_POST['trade']);
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	$setsqlarr['map_x']=!empty($_POST['x'])?trim($_POST['x']):adminmsg('请标注地图！',1);
	$setsqlarr['map_y']=!empty($_POST['y'])?trim($_POST['y']):adminmsg('请标注地图！',1);
	$setsqlarr['map_zoom']=trim($_POST['zoom']);
	$setsqlarr['phone']=!empty($_POST['phone'])?trim($_POST['phone']):adminmsg('您没有填写联系电话！',1);
	$setsqlarr['display']=intval($_POST['display']);
	$setsqlarr['order']=intval($_POST['order']);
	$setsqlarr['introduction']=!empty($_POST['introduction'])?$_POST['introduction']:adminmsg('请填写招聘会简介',1);
	$setsqlarr['predetermined_status']=intval($_POST['predetermined_status']);
	$setsqlarr['predetermined_web']=intval($_POST['predetermined_web']);
	$setsqlarr['predetermined_tel']=intval($_POST['predetermined_tel']);
	$setsqlarr['predetermined_point']=intval($_POST['predetermined_point']);
	if ($_POST['predetermined_start']=="")
	{
		$setsqlarr['predetermined_start']=0;
	}
	else
	{
		$setsqlarr['predetermined_start']=intval(convert_datefm($_POST['predetermined_start'],2));
	}
	if ($_POST['predetermined_end']=="")
	{
		$setsqlarr['predetermined_end']=0;
	}
	else
	{
		$setsqlarr['predetermined_end']=intval(convert_datefm($_POST['predetermined_end'],2));
	}
	$setsqlarr['addtime']=time();
	
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=';
	$link[1]['text'] = "查看修改结果";
	$link[1]['href'] = "?act=jobfair_edit&id=".$id;
	$db->updatetable(table('jobfair_exhibitors'),array('jobfair_title'=>$setsqlarr['title'])," jobfairid=".$id."");
	write_log("修改招聘会id为".$id."的招聘会信息", $_SESSION['admin_name'],3);
	!$db->updatetable(table('jobfair'),$setsqlarr," id=".$id."")?adminmsg("修改失败！",0):adminmsg("修改成功！",2,$link);
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