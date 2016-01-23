<?php
 /*
 * 74cms 测试结果详情页面
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
$alias="QS_paper_result";
error_reporting(E_ERROR);
require_once(dirname(__FILE__).'/../include/common.inc.php');
if($mypage['caching']>0){
	$smarty->cache =true;
	$smarty->cache_lifetime=$mypage['caching'];
}else{
	$smarty->cache = false;
}
$cached_id=$alias.(isset($_GET['id'])?"|".(intval($_GET['id'])%100).'|'.intval($_GET['id']):'').(isset($_GET['page'])?"|p".intval($_GET['page'])%100:'');
//测卷结果详情
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$id=$_GET['id']?intval($_GET['id']):0;
$paper_sql = "SELECT r.score,r.result_description,p.* FROM ".table('evaluation_record')." AS r LEFT JOIN  ".table('evaluation_paper')." AS p  ON r.paper_id=p.id  WHERE uid=".$_SESSION['uid']."  and r.id = {$id}  ";
$paper_info = $db->getone($paper_sql);
//var_dump($paper_info);
$smarty->assign('paper_info',$paper_info);
//测评推荐
$paper_list_sql = "SELECT * FROM ".table('evaluation_paper')." ORDER BY join_num desc LIMIT 3 ";
$paper_list = $db->getall($paper_list_sql);
$smarty->assign('paper_list',$paper_list);
if(!$smarty->is_cached($mypage['tpl'],$cached_id))
{
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