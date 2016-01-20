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
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
require_once(dirname(__FILE__).'/weixin_share.php');
$show = jobfair_one($_GET['id']);
$time = time();
if($show)
{
	//状态
	if($show['predetermined_status']=="1" && $show['predetermined_start']>$time)
	{
		$show['predetermined_ok'] = 1; // 未开始
	}
	else if ($show['predetermined_status']=="1" && $show['holddate_start']>$time && ($show['predetermined_end']=="0" || $show['predetermined_end']>$time) && ($show['predetermined_web']=="1" || $show['predetermined_tel']=="1"))
	{
		$show['predetermined_ok'] = 2; // 预定中
	}
	else
	{
		$show['predetermined_ok'] = 0; // 已结束
	}
	//参会行业
	$jobfair_trade = explode(",", $show['trade_cn']);
	$show["trade_cn"] = $jobfair_trade;
	$smarty->assign('show',$show);
	//参会企业
	$exhibitors = $db->getall("SELECT * FROM ".table('jobfair_exhibitors')." WHERE jobfairid=".$show["id"]);      
	$smarty->assign('exhibitors',$exhibitors);
}
$smarty->assign('goback',$_SERVER["HTTP_REFERER"]);
$smarty->display("m/jobfair-show.html");
?>