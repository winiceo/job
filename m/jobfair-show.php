<?php
 /*
 * 74cms WAP
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
require_once(dirname(__FILE__).'/weixin_share.php');
$show = jobfair_one($_GET['id']);
$time = time();
if($show)
{
	//״̬
	if($show['predetermined_status']=="1" && $show['predetermined_start']>$time)
	{
		$show['predetermined_ok'] = 1; // δ��ʼ
	}
	else if ($show['predetermined_status']=="1" && $show['holddate_start']>$time && ($show['predetermined_end']=="0" || $show['predetermined_end']>$time) && ($show['predetermined_web']=="1" || $show['predetermined_tel']=="1"))
	{
		$show['predetermined_ok'] = 2; // Ԥ����
	}
	else
	{
		$show['predetermined_ok'] = 0; // �ѽ���
	}
	//�λ���ҵ
	$jobfair_trade = explode(",", $show['trade_cn']);
	$show["trade_cn"] = $jobfair_trade;
	$smarty->assign('show',$show);
	//�λ���ҵ
	$exhibitors = $db->getall("SELECT * FROM ".table('jobfair_exhibitors')." WHERE jobfairid=".$show["id"]);      
	$smarty->assign('exhibitors',$exhibitors);
}
$smarty->assign('goback',$_SERVER["HTTP_REFERER"]);
$smarty->display("m/jobfair-show.html");
?>