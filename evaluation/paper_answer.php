<?php
 /*
 * 74cms 测试详情页面
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
$alias="QS_paper_answer";
error_reporting(E_ERROR);
require_once(dirname(__FILE__).'/../include/common.inc.php');
if($mypage['caching']>0){
	$smarty->cache =true;
	$smarty->cache_lifetime=$mypage['caching'];
}else{
	$smarty->cache = false;
}
$cached_id=$alias.(isset($_REQUEST['id'])?"|".(intval($_REQUEST['id'])%100).'|'.intval($_REQUEST['id']):'').(isset($_REQUEST['page'])?"|p".intval($_REQUEST['page'])%100:'');


require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'genv/lib.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$id=$_REQUEST['id']?intval($_REQUEST['id']):0;
if(empty($_SESSION['utype']) || $_SESSION['utype']!=2)
{
	$link[0]['text'] = "返回测评首页";
	$link[0]['href'] = url_rewrite('QS_evaluation_index');
	showmsg('只有个人会员才能进行测评！',1,$link);
}
if(empty($_SESSION['uid']))
{
	$uid =0;
}
else
{
	$uid = intval($_SESSION['uid']);
}
//判断一下该会员是否答过此卷
$check_sql = "SELECT * FROM ".table('evaluation_record')." WHERE paper_id=".$id.' AND uid='.$uid;
$check_info = $db->getone($check_sql);
if($check_info)
{
	$link[0]['text'] = "返回我的测评";
	$link[0]['href'] = url_rewrite('QS_my_evaluation');
	showmsg('您已经答过此卷！',1,$link);
}
//试卷信息
$peper_sql = "SELECT * FROM ".table('evaluation_paper')." WHERE id=".$id;
$paper_info = $db->getone($peper_sql);
$smarty->assign('paper_info',$paper_info);
//测题信息
$perpage = 10;
$page=$_REQUEST['page']?intval($_REQUEST['page']):1;
$total_sql="SELECT COUNT(*) AS num from ".table('evaluation_question')." WHERE paper_id=".$id;
$total_val=$db->get_total($total_sql);
$page_num = ceil($total_val/$perpage);
if($page<$page_num)
{
	$smarty->assign('next_page',$page+1);
}
if($page != 1)
{
	$smarty->assign('pre_page',$page-1);
}
$smarty->assign('page',$page);
$smarty->assign('page_num',$page_num);
$smarty->assign('perpage',$perpage);
$smarty->assign('total_val',$total_val);
$offset=($page-1)*$perpage;
$limit = " LIMIT ".intval($offset).','.intval($perpage);
$question_sql = "SELECT * FROM ".table('evaluation_question')." WHERE paper_id=".$id." ORDER BY id asc ".$limit;
$question_list = $db->getall($question_sql);
foreach ($question_list as $key => $value) 
{
	$option_sql = "SELECT * FROM ".table('evaluation_option')." WHERE question_id=".$value['id']." ORDER BY id asc ";
	$option_list = $db->getall($option_sql);
	$question_list2[intval($offset)+intval($key)+1] = $value;
	$question_list2[intval($offset)+intval($key)+1]['key'] = intval($offset)+intval($key)+1;
	$question_list2[intval($offset)+intval($key)+1]['option'] = $option_list;
}


$smarty->assign('question_list',$question_list2);
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