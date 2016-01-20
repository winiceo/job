<?php
 /*
 * 74cms 谁在关注我模块
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'include/fun_personal.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'attention_me';
if ($_SESSION['uid']=='' || $_SESSION['username']==''||intval($_SESSION['utype'])==1)
{
	header("Location: ../login.php");
}
$user = get_user_info($_SESSION['uid']);
if($_CFG['login_per_audit_mobile'] && $user['mobile_audit']=="0")
{
	$str= "<script>";
	$str.="alert('请先验证手机！');";
	$str.="window.location.href='account_security.php';";
	$str.= "</script>";
	echo $str;
}
elseif ($act == 'attention_me')
{
	$joinsql=" LEFT JOIN  ".table('resume')." AS r  ON  a.resumeid=r.id ";
	$resume = get_auditresume_list($_SESSION['uid']);
	foreach($resume as $k=>$v){
		$rid[] = $v['id'];
	}
	if(!empty($rid)){
		$rid_str = implode(",",$rid);
		$total_sql="SELECT COUNT(*) AS num FROM ".table('view_resume')." AS a where resumeid in (".$rid_str.")";
		$total_val=$db->get_total($total_sql);
		$wheresql=" where a.resumeid in (".$rid_str.") ";
	}else{
		$total_val = 0;
	}
	$perpage=10;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$offset=($page-1)*$perpage;
	$smarty->assign('view_list',get_view_resume($offset,$perpage,$joinsql.$wheresql));
	$smarty->display('m/personal/m-attentionme.html');

}
elseif ($act == 'del_view_resume')
{
	$yid =!empty($_POST['id'])?$_POST['id']:exit("请选择删除项目！");
	if($n=del_view_resume($yid))
	{
		exit("ok");
	}
	else
	{
		exit("err");
	}
}
?>