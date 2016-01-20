<?php
 /*
 * 74cms 积分商城首页
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
if(!defined('IN_QISHI')) die('Access Denied!');
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'include/fun_shop.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
if($_CFG['operation_mode']==2)
{
	$link[0]['text'] = "网站首页";
	$link[0]['href'] = url_rewrite('QS_index');
	showmsg('套餐模式不能使用积分商城',1,$link);
}
if($_SESSION['utype']=='1')
{
	$smarty->assign("com_point",get_user_points($_SESSION['uid']));
	$smarty->assign("com_info",get_company($_SESSION['uid']));

}
elseif($_SESSION['utype']=='2')
{
	$smarty->assign("com_point",get_user_points($_SESSION['uid']));
}
elseif ($_SESSION['utype']!='' && $_SESSION['utype']!='1') 
{
	$link[0]['text'] = "网站首页";
	$link[0]['href'] = url_rewrite('QS_index');
	showmsg('积分商城仅对企业开放！',1,$link);
}
// 积分规则
$smarty->assign("points_rule",get_cache("points_rule"));
// 热门关键字 
$smarty->assign("hotword",get_shop_hotword(6));
// 最新兑换记录 
$smarty->assign("exchange_list",get_exchange_index(4));
?>