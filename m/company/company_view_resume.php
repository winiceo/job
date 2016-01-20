<?php
 /*
 * 74cms 触屏版浏览过的简历列表
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
require_once(QISHI_ROOT_PATH.'include/fun_company.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'index';
if ($_SESSION['uid']=='' || $_SESSION['username']=='' || intval($_SESSION['utype'])==2)
{
	header("Location: ../login.php");
}
elseif ($act == 'index')
{
	$smarty->cache = false;
	$joinsql=" LEFT JOIN  ".table('resume')." AS r  ON  a.resumeid=r.id ";
	$wheresql=" WHERE a.uid='{$_SESSION['uid']}' ";
	//查看时间筛选
	$settr=intval($_GET['settr']);
	if($settr>0)
	{
	$settr_val=strtotime("-".$settr." day");
	$wheresql.=" AND a.addtime>".$settr_val;
	}
	//学历
	$education=intval($_GET['education']);
	if($education>0)
	{
	$wheresql.=" AND r.education=".$education;
	}
	//经验
	$experience=intval($_GET['experience']);
	if($experience>0)
	{
	$wheresql.=" AND r.experience=".$experience;
	}
	//职能
	$category=intval($_GET['category']);
	$subclass=intval($_GET['subclass']);
	if($category>0)
	{
		$joinwheresql=" WHERE  category=".intval($category);
		if($subclass>0)
		{
			$joinwheresql.=" AND  subclass=".intval($subclass);
		}
		$joinsql.="  INNER  JOIN  ( SELECT DISTINCT pid FROM ".table('resume_jobs')." {$joinwheresql} )AS j ON  a.resumeid=j.pid ";
	}
	$perpage = 5;
	$count  = 0;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	$total_sql="SELECT COUNT(*) AS num FROM ".table('view_resume')." AS a ".$joinsql." {$wheresql} ";
	$total_val=$db->get_total($total_sql);
	$view_resumes = wap_get_my_attention($start,$perpage,$joinsql.$wheresql);
	$smarty->assign('view_resumes',$view_resumes);
	$smarty->display("m/company/m-my-attention.html");	
}
?>