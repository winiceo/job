<?php
 /*
 * 74cms 触屏版面试邀请列表
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
	$joinsql=" LEFT JOIN ".table('resume')." as r ON i.resume_id=r.id ";
	$wheresql=" WHERE i.company_uid='{$_SESSION['uid']}' ";
	//面试职位 筛选
	$jobsid=intval($_GET['jobsid']);
	if($jobsid>0)
	{
		$wheresql.=" AND i.jobs_id='{$jobsid}' ";
	}
	//对方查看状态 帅选
	$look=intval($_GET['look']);
	if($look>0)$wheresql.=" AND  i.personal_look='{$look}' ";
	//邀请时间
	$settr=intval($_GET['settr']);
	if ($settr>0)
	{
		$settr=strtotime("-".intval($_GET['settr'])." day");
		$wheresql.=" AND i.interview_time> ".$settr;
	}
	$perpage = 5;
	$count  = 0;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	$total_sql="SELECT COUNT(*) AS num FROM  ".table('company_interview')." as i ".$joinsql."  {$wheresql}";
	$count=$db->get_total($total_sql);
	$interview = wap_get_interview($start, $perpage,$joinsql.$wheresql);
	$smarty->assign('interview',$interview);
	$smarty->display("m/company/m-interview-list.html");	
}
elseif($act=="ajax_get_interview")
{
	$favoriteshtml="";
	$rows=intval($_GET['rows']);
	$offset=intval($_GET['offset']);
	$wheresql=" WHERE i.company_uid='{$_SESSION['uid']}' ";
	$favoritesarry=$db->getall("select i.*,r.title,r.fullname,r.display_name,r.education_cn,r.birthdate,r.experience_cn,r.residence from ".table("company_interview")." as i left join ".table("resume")." as r on i.resume_id=r.id  $wheresql order by i.interview_addtime desc limit $offset,$rows");
	if (!empty($favoritesarry) && $offset<=100)
	{
		foreach($favoritesarry as $list)
		{
			$list["birthdate_"]=date('Y',time())-$list["birthdate"];
			$favoriteshtml.='<div class="get_resume_box" onclick="window.location.href="../resume-show.php?id='.$list["resume_id"].'"">
							<div class="get_resume_left">
								<div class="name_box">
									<div class="name_box_l">'.$list["fullname"].'</div>
									<div class="name_box_r">'.date("Y-m-d",$list["apply_addtime"]).'</div>
									<div class="clear"></div>
								</div>
								<div class="person_detail">'.$list["education_cn"].'|'.$list["birthdate_"].'|'.$list["experience_cn"].'|'.$list["residence"].'<br />邀请职位：'.$list["jobs_name"].'
								</div>
							</div>
							<div class="get_resume_right"><img src="../images/34.gif" alt="" /></div>
							<div class="clear"></div>
						</div>';
		}
		exit($favoriteshtml);
	}
	else
	{
		exit('-1');
	}
}
?>