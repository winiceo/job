<?php
 /*
 * 74cms ��������ҳ
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
//����
$notice_list = $db->getall("SELECT * FROM ".table('notice')."  ORDER BY `sort` DESC,`id` DESC LIMIT 5");	
$smarty->assign('notice_list',$notice_list);
//����ְλ
$emergency_jobs = $db->getall("SELECT id,jobs_name,district_cn,companyname,wage_cn,refreshtime FROM ".table('jobs')." WHERE emergency=1 ORDER BY `refreshtime` DESC,`id` DESC LIMIT 5");
foreach ($emergency_jobs as $key => $value) 
{
	$emergency_jobs[$key]['url'] = wap_url_rewrite("jobs-show",array("id"=>$value['id']));
	$emergency_jobs[$key]['r_time'] = daterange(time(),$value['refreshtime'],'Y-m-d',"#FF3300");
}
$smarty->assign('emergency_jobs',$emergency_jobs);
//�Ƽ�ְλ
$recommend_jobs = $db->getall("SELECT id,jobs_name,district_cn,companyname,wage_cn,refreshtime FROM ".table('jobs')." WHERE recommend=1 ORDER BY `refreshtime` DESC,`id` DESC LIMIT 5");
foreach ($recommend_jobs as $key => $value) 
{
	$recommend_jobs[$key]['url'] = wap_url_rewrite("jobs-show",array("id"=>$value['id']));
	$recommend_jobs[$key]['r_time'] = daterange(time(),$value['refreshtime'],'Y-m-d',"#FF3300");
}
$smarty->assign('recommend_jobs',$recommend_jobs);
//����ְλ
$new_jobs = $db->getall("SELECT id,jobs_name,district_cn,companyname,wage_cn,refreshtime FROM ".table('jobs')."  ORDER BY `refreshtime` DESC,`id` DESC LIMIT 5");
foreach ($new_jobs as $key => $value) 
{
	$new_jobs[$key]['url'] = wap_url_rewrite("jobs-show",array("id"=>$value['id']));
	$new_jobs[$key]['r_time'] = daterange(time(),$value['refreshtime'],'Y-m-d',"#FF3300");
}
$smarty->assign('new_jobs',$new_jobs);
//�����Ƽ����λ
$ad_list = $db->getall("SELECT id,img_path,img_url FROM ".table('ad')." WHERE alias='QS_yellowpage'  ORDER BY `show_order` DESC,`id` DESC LIMIT 6");
$smarty->assign('ad_list',$ad_list);
//���Źؼ���
$sql="select w_word,w_hot from ".table("hotword")." order by w_hot desc limit 15 ";
$hotword_list = $db->getall($sql);
$smarty->assign('hotword',$hotword_list);
$smarty->display("m/m-index.html");
?>