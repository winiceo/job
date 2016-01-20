<?php
 /*
 * 74cms 触屏版用户反馈
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
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'index';
if($act == 'index')
{
	//网站简介（说明页） 
	$site_detail = $db->getone("SELECT * FROM ".table('explain')." WHERE id=2 ");
	$smarty->assign('site_detail',$site_detail["content"]);
	$smarty->assign('goback',$_SERVER["HTTP_REFERER"]);
	$smarty->display("m/feedback.html");
}
elseif ($act == 'save')
{
	$setsqlarr['infotype']=intval($_POST['type']);
	$setsqlarr['feedback']=trim($_POST['feedback']);
	$setsqlarr['tel']=trim($_POST['tel']);
	$setsqlarr['addtime']=time();
	$db->inserttable(table('feedback'),$setsqlarr);
	header("Location: index.php");
}

?>