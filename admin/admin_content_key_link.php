<?php
 /*
 * 74cms 热门关键字
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
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'list';
$smarty->assign('act',$act);
check_permissions($_SESSION['admin_purview'],"hotword");
$smarty->assign('pageheader',"内容聚合关键字");
if($act == 'list')
{	
	get_token();
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY id DESC ";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	if ($key)
	{
		$wheresql=" WHERE name like '%{$key}%'";
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('content_key_link')." ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql),'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$hotword = get_content_key_link($offset, $perpage,$wheresql.$oederbysql);	
	$smarty->assign('hotword',$hotword);
	$smarty->assign('navlabel',"list");	
	$smarty->assign('page',$page->show(3));	
	$smarty->display('content_key_link/admin_content_key_link_list.htm');
}
elseif($act == 'add')
{
	get_token();
	$smarty->assign('navlabel',"add");	
	$smarty->display('content_key_link/admin_content_key_link_add.htm');
}
elseif($act == 'addsave')
{
	check_token();
	$setsqlarr['name']=trim($_POST['name'])?trim($_POST['name']):adminmsg('关键词必须填写！',1);
	$setsqlarr['url']=trim($_POST['url'])?trim($_POST['url']):adminmsg('关键词链接必须填写',1);
	$check=check_content_key_link($setsqlarr['name']);
	if (!empty($check))
	{
	adminmsg("关键词已经存在！",0);
	}

	$link[0]['text'] = "继续添加";
	$link[0]['href'] = '?act=add';
	$link[1]['text'] = "返回列表";
	$link[1]['href'] = '?';
	write_log("添加内容聚合关键字", $_SESSION['admin_name'],3);
	!$db->inserttable(table('content_key_link'),$setsqlarr)?adminmsg("添加失败！",0):adminmsg("添加成功！",2,$link);
}
elseif($act == 'edit')
{
	get_token();
	$smarty->assign('hotword',get_content_key_link_one($_GET['id']));
	$smarty->display('content_key_link/admin_content_key_link_edit.htm');
}
elseif($act == 'editsave')
{
	check_token();
	$id = !empty($_POST['id']) ? intval($_POST['id']) : adminmsg('参数错误',1);
	$setsqlarr['name']=trim($_POST['name'])?trim($_POST['name']):adminmsg('关键词必须填写！',1);
	$setsqlarr['url']=trim($_POST['url'])?trim($_POST['url']):adminmsg('关键词链接必须填写',1);
	$check=check_content_key_link($setsqlarr['name']);
	if (!empty($check))
	{
	adminmsg("关键词已经存在！",0);
	}
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?';
	write_log("修改内容聚合关键字", $_SESSION['admin_name'],3);
 	!$db->updatetable(table('content_key_link'),$setsqlarr," id=".$id."")?adminmsg("修改失败！",0):adminmsg("修改成功！",2,$link);
}
elseif($act == 'content_key_link_del')
{
	check_token();
	$id=$_REQUEST['id'];
	if ($num=del_content_key_link($id))
	{
	write_log("删除热门内容聚合关键字,共删除 {$num} 行", $_SESSION['admin_name'],3);
	adminmsg("删除成功！共删除 {$num} 行",2);
	}
	else
	{
	adminmsg("删除失败！",0);
	}
}
elseif($act == 'set')
{
	get_token();
	$smarty->assign('pageheader',"内容聚合关键字");
	$smarty->assign('config',get_cache('config'));
	$smarty->assign('navlabel',"set");	
	$smarty->display('content_key_link/admin_content_key_link_set.htm');
}
elseif($act=='setsave')
{
	check_token();
	foreach($_POST as $k => $v)
	{
	!$db->query("UPDATE ".table('config')." SET value='{$v}' WHERE name='{$k}'")?adminmsg('更新站点设置失败', 1):"";
	}
	refresh_cache('config');
	//填写管理员日志
	write_log("后台成功设置网站配置", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2);
}	
?>