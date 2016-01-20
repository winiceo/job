<?php
 /*
 * 74cms 触屏版招聘会模块
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
$page = empty($_GET['page'])?1:intval($_GET['page']);
$jobstable=table('jobfair');
$orderbysql=" ORDER BY `addtime` desc";
$perpage = 5;
$count  = 0;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
$total_sql="SELECT COUNT(*) AS num FROM {$jobstable} ".$orderbysql;
$count=$db->get_total($total_sql);
$limit=" LIMIT {$start},{$perpage}";
$idresult = $db->query("SELECT * FROM {$jobstable} ".$orderbysql.$limit);
$jobfairs=array();
$time=time();
while($row = $db->fetch_array($idresult))
{
	$row['url'] = wap_url_rewrite("jobfair-show",array("id"=>$row['id']));
	//状态
	if($row['predetermined_status']=="1" && $row['predetermined_start']>$time)
	{
		$row['predetermined_ok'] = 1; // 未开始
	}
	else if ($row['predetermined_status']=="1" && $row['holddate_start']>$time && ($row['predetermined_end']=="0" || $row['predetermined_end']>$time) && ($row['predetermined_web']=="1" || $row['predetermined_tel']=="1"))
	{
		$row['predetermined_ok'] = 2; // 预定中
	}
	else
	{
		$row['predetermined_ok'] = 0; // 已结束
	}
	$jobfairs[] = $row;
}
$smarty->assign('jobfairs',$jobfairs);
$smarty->display("m/jobfair-list.html");
?>