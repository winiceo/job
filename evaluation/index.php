<?php
 /*
 * 74cms 人才测评首页
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
$alias="QS_evaluation_index";
require_once(dirname(__FILE__).'/../include/common.inc.php');
$smarty->assign("evaluation_nav","index");
if($mypage['caching']>0)
{
	$smarty->cache =true;
	$smarty->cache_lifetime=$mypage['caching'];
}
else
{
	$smarty->cache = false;
}
$cached_id=$alias.(isset($_GET['id'])?"|".(intval($_GET['id'])%100).'|'.intval($_GET['id']):'').(isset($_GET['page'])?"|p".intval($_GET['page'])%100:'');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
//统计测评类型参与人数
$sql = "SELECT *  FROM ".table('evaluation_type')." ";
$type_info = $db->getall($sql);
foreach ($type_info as $key => $value) 
{
	$num = 0;
	$sql_num = "SELECT * FROM ".table('evaluation_paper')." WHERE type_id=".$value['id'];
	$type_num = $db->query($sql_num);
	while($row = $db->fetch_array($type_num))
	{
		$num = $num + intval($row['join_num']);
	}
	$type_info[$key]['join_num'] = $num;
}
//热门测评
$paper_sql = "SELECT * FROM ".table('evaluation_paper')." ORDER BY join_num LIMIT 10 ";
$paper_info = $db->getall($paper_sql);
foreach ($paper_info as $key => $value) 
{
	$paper_info[$key]['key'] = $key+1;
}
$smarty->assign('type_info',$type_info);
$smarty->assign('paper_info',$paper_info);
if(!$smarty->is_cached($mypage['tpl'],$cached_id))
{
	require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
	$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
	$mypage['tpl']='../tpl_evaluation/default/'.$mypage['tpl'];
	$smarty->display($mypage['tpl'],$cached_id);
	$db->close();
}
else
{
	$smarty->display($mypage['tpl'],$cached_id);
}
unset($smarty);
?>