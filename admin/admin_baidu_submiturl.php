<?php
 /*
 * 74cms ����HTML
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'set';
$smarty->assign('act',$act);
$smarty->assign('pageheader',"�ٶ������ύ");
if($act == 'set')
{
$smarty->assign('data',get_cache('baidu_submiturl'));
$smarty->display('baidu_submiturl/admin_baidu_submiturl_set.htm');
}
elseif($act == 'setsave')
{
	$_POST['token']=trim($_POST['token']);
	$_POST['addcompany']=intval($_POST['addcompany']);
	$_POST['addjob']=intval($_POST['addjob']);
	$_POST['addresume']=intval($_POST['addresume']);
	$_POST['addcompanynews']=intval($_POST['addcompanynews']);
	$_POST['addjobfair']=intval($_POST['addjobfair']);
	$_POST['addarticle']=intval($_POST['addarticle']);
	$_POST['addexplain']=intval($_POST['addexplain']);
	$_POST['addnotice']=intval($_POST['addnotice']);
	$_POST['addcampus']=intval($_POST['addcampus']);
	$_POST['addhunterjob']=intval($_POST['addhunterjob']);
	$_POST['addhunter']=intval($_POST['addhunter']);
	$_POST['addcourse']=intval($_POST['addcourse']);
	$_POST['addagency']=intval($_POST['addagency']);
	$_POST['addteacher']=intval($_POST['addteacher']);
	foreach($_POST as $k => $v)
	{
	!$db->query("UPDATE ".table('baidu_submiturl')." SET value='{$v}' WHERE name='{$k}'")?adminmsg('����ʧ��', 1):"";
	}
	refresh_cache('baidu_submiturl');
	write_log("�޸İٶ������ύ����", $_SESSION['admin_name'],3);
	adminmsg("����ɹ���",2);
}
?>