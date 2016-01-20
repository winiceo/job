<?php
/*
 * 74cms 猎头会员中心
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/hunter_common.php');
$smarty->assign('leftmenu',"recruitment");
if ($act=='down_resume_list')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$perpage=10;
	$joinsql=" LEFT JOIN  ".table('resume')." as r ON d.resume_id=r.id ";
	$wheresql=" WHERE  d.hunter_uid='{$_SESSION['uid']}' ";
	$settr=intval($_GET['settr']);
	if($settr>0)
	{
	$settr_val=strtotime("-{$settr} day");
	$wheresql.=" AND d.down_addtime>{$settr_val} ";
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('hunter_down_resume')." as d".$wheresql;
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('title','已下载的简历 - 猎头顾问会员中心 - '.$_CFG['site_name']);
	$smarty->assign('act',$act);
	$smarty->assign('list',get_down_manager_resume($offset,$perpage,$joinsql.$wheresql));
	$smarty->assign('page',$page->show(3));
	$smarty->display('member_hunter/hunter_down_resume.htm');
}
elseif ($act=='del_down')
{
	$yid =!empty($_REQUEST['y_id'])?$_REQUEST['y_id']:showmsg("你没有选择下载记录！",1);
	if ($n=del_down_manager($yid,$_SESSION['uid']))
	{
	showmsg("删除成功！共删除 {$n} 行",2);
	}
	else
	{
	showmsg("失败！",0);
	}
}
//收到的简历
elseif ($act=='apply_jobs')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$joinsql=" LEFT JOIN  ".table('resume')." AS r  ON  a.resume_id=r.id ";
	$wheresql=" WHERE a.huntet_uid='{$_SESSION['uid']}' ";
	$look=intval($_GET['look']);
	if($look>0)$wheresql.=" AND a.personal_look='{$look}'";
	$jobsid=intval($_GET['jobsid']);
	if($jobsid>0){
		$wheresql.=" AND a.jobs_id='{$jobsid}' ";
		$sql="select jobs_name from ".table("hunter_jobs")." where id=".intval($_GET['jobsid'])." ";
		$row=$db->getone($sql);
		$smarty->assign('jobs_name',$row["jobs_name"]);
	}
	$perpage=10;
	$total_sql="SELECT COUNT(*) AS num FROM ".table('personal_hunter_jobs_apply')." AS a  {$wheresql} ";
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$offset=($page->nowindex-1)*$perpage;
	$smarty->assign('act',$act);
	$smarty->assign('title','收到的职位申请 - 猎头会员中心 - '.$_CFG['site_name']);
	$smarty->assign('jobs_apply',get_apply_hunter_jobs($offset,$perpage,$joinsql.$wheresql));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('count',count_jobs_apply($_SESSION['uid'],0,$jobsid));
	$smarty->assign('count1',count_jobs_apply($_SESSION['uid'],1,$jobsid));
	$smarty->assign('count2',count_jobs_apply($_SESSION['uid'],2,$jobsid));
	$smarty->assign('jobs',get_auditjobs($_SESSION['uid']));	
	$smarty->display('member_hunter/hunter_apply_jobs.htm');
}
elseif ($act=='set_apply_jobs')
{
	$yid =!empty($_REQUEST['y_id'])?$_REQUEST['y_id']:showmsg("你没有选择任何项目！",1);
	set_apply($yid,$_SESSION['uid'],2)?showmsg("设置成功！",2):showmsg("设置失败！",0);
}
elseif ($act=='apply_jobs_del')
{
	$yid =!empty($_REQUEST['y_id'])?$_REQUEST['y_id']:showmsg("你没有选择项目！",1);
	if ($n=del_apply_jobs($yid,$_SESSION['uid']))
	{
	showmsg("删除成功！共删除 {$n} 行",2);
	}
	else
	{
	showmsg("失败！",0);
	}
}
//面试邀请
elseif ($act=='interview')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$perpage=10;
	$joinsql=" LEFT JOIN ".table('resume')." as r ON i.resume_id=r.id ";
	$wheresql=" WHERE i.hunter_uid='{$_SESSION['uid']}' ";
	$jobsid=intval($_GET['jobsid']);
	if($jobsid>0){
		$wheresql.=" AND i.jobs_id='{$jobsid}' ";
		$sql="select jobs_name from ".table("hunter_jobs")." where id=".intval($_GET['jobsid'])." ";
		$row=$db->getone($sql);
		$smarty->assign('jobs_name',$row["jobs_name"]);
	}
	$look=intval($_GET['look']);
	if($look>0)$wheresql.=" AND  i.personal_look='{$look}' ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('hunter_interview')." as i ".$wheresql;
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('act',$act);
	$smarty->assign('title','我发起的面试邀请 - 猎头会员中心 - '.$_CFG['site_name']);
	$smarty->assign('resume',get_interview($offset, $perpage,$joinsql.$wheresql));
	$count1=count_interview($_SESSION['uid'],1,$jobsid);//未查看
	$count2=count_interview($_SESSION['uid'],2,$jobsid);//以查看
	$count=$count1+$count2;
	$smarty->assign('count',$count);
	$smarty->assign('count1',$count1);
	$smarty->assign('count2',$count2);
	$smarty->assign('jobs',get_auditjobs($_SESSION['uid']));
	$smarty->assign('page',$page->show(3));
	$smarty->display('member_hunter/hunter_interview.htm');
}
//删除面试邀请信息
elseif ($act=='interview_del')
{
	$yid =!empty($_REQUEST['y_id'])?$_REQUEST['y_id']:showmsg("你没有选择简历！",1);
	if (del_interview($yid,$_SESSION['uid']))
	{
		showmsg("删除成功！",2);
	}
	else
	{
		showmsg("删除失败！",0);
	}
}
unset($smarty);
?>