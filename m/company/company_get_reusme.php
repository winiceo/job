<?php
 /*
 * 74cms 触屏版收到的简历模块
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
if ($_SESSION['uid']=='' || $_SESSION['username']==''||intval($_SESSION['utype'])==2)
{
	header("Location: ../login.php");
}
elseif ($act == 'index')
{
	$smarty->cache = false;
	$joinsql=" LEFT JOIN  ".table('resume')." AS r  ON  a.resume_id=r.id ";
	$wheresql=" WHERE a.company_uid='{$_SESSION['uid']}' ";
	//标签
	$state=intval($_GET['state']);
	if($state>0)
	{
		$joinsql.=" left join ".table('company_label_resume')." as l on l.resume_id=a.resume_id ";
		$wheresql.=" AND l.resume_state=$state AND l.uid={$_SESSION['uid']} ";
	}
	//状态
	$look=intval($_GET['look']);
	if($look>0)$wheresql.=" AND a.personal_look='{$look}'";
	//职位
	$jobsid=intval($_GET['jobsid']);
	if($jobsid>0)
	{
		$wheresql.=" AND a.jobs_id='{$jobsid}' ";
		$sql="select jobs_name from ".table("jobs")." where id=".intval($_GET['jobsid'])." ";
		$row=$db->getone($sql);
		$smarty->assign('jobs_name',$row["jobs_name"]);
	}
	//来源
	$is_apply=intval($_GET['is_apply']);
	if($is_apply>0)
	{
		if($is_apply==1)
		{
			$wheresql.=" AND a.is_apply=1 ";
		}
		else
		{
			$wheresql.=" AND a.is_apply=0 ";
		}
	}
	$perpage = 5;
	$count  = 0;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	$total_sql="SELECT COUNT(*) AS num FROM  ".table('personal_jobs_apply')." as a ".$joinsql." {$wheresql}";
	$count=$db->get_total($total_sql);
	$limit=" LIMIT {$start},{$perpage}";
	$smarty->assign('jobs',get_auditjobs($_SESSION['uid']));
	$smarty->assign('jobs_apply',wap_get_apply_jobs($start,$perpage,$joinsql.$wheresql));
	$smarty->display("m/company/m-get-resumes.html");	
}
elseif($act=="ajax_get_resume")
{
	$favoriteshtml="";
	$rows=intval($_GET['rows']);
	$offset=intval($_GET['offset']);
	$wheresql=" WHERE a.company_uid='{$_SESSION['uid']}' ";
	$favoritesarry=$db->getall("select a.*,r.title,r.fullname,r.display_name,r.education_cn,r.birthdate,r.experience_cn,r.residence from ".table("personal_jobs_apply")." as a left join ".table("resume")." as r on a.resume_id=r.id  $wheresql order by a.apply_addtime desc limit $offset,$rows");
	if (!empty($favoritesarry) && $offset<=100)
	{
		foreach($favoritesarry as $list)
		{
			$list["birthdate_"]=date('Y',time())-$list["birthdate"];
			/*if ($list['display_name']=="2")
			{
			$list['fullname']="N".str_pad($list['resume_id'],7,"0",STR_PAD_LEFT);
			}
			elseif ($list['display_name']=="3")
			{
			$list['fullname']=cut_str($list['fullname'],1,0,"**");
			}*/
			$favoriteshtml.='<div class="get_resume_box" onclick="window.location.href="../resume-show.php?id='.$list["resume_id"].'
""><div class="get_resume_left"><div class="name_box"><div class="name_box_l">'.$list["fullname"].'</div><div class="name_box_r">'.date("Y-m-d",$list["apply_addtime"]).'</div><div class="clear"></div></div><div class="person_detail">'.$list["education_cn"].'|'.$list["birthdate_"].'|'.$list["experience_cn"].'|'.$list["residence"].'<br />应聘职位：'.$list["jobs_name"].'</div></div><div class="get_resume_right"><img src="../images/34.gif" alt="" /></div><div class="clear"></div></div>';
		}
		exit($favoriteshtml);
	}
	else
	{
		exit('-1');
	}
}
?>