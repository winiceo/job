<?php
 /*
 * 74cms ������΢�����б�ģ��
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
$key = empty($_GET['key'])?"":$_GET['key'];
$jobstable=table('simple_resume');
if (isset($key) && !empty($key))
{
	$key=help::addslashes_deep(trim($key));
	$wheresql.=" AND  likekey like '%{$key}%'";
}
$orderbysql=" ORDER BY `refreshtime` desc,`id` desc ";
if (!empty($wheresql))
{
	$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
}

$perpage = 5;
$count  = 0;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
$total_sql="SELECT COUNT(*) AS num FROM {$jobstable} {$wheresql}";
$count=$db->get_total($total_sql);
$limit=" LIMIT {$start},{$perpage}";
$result = $db->query("SELECT * FROM ".table('simple_resume')." ".$wheresql.$orderbysql.$limit);
$list   = array();
while($row = $db->fetch_array($result))
{
	$row['detailed']=strip_tags($row['detailed']);
	$row['refreshtime_cn']=daterange(time(),$row['refreshtime'],'Y-m-d',"#FF3300");
	$row['simple_resume_url']=wap_url_rewrite("simple-resume-show",array('id'=>$row['id']));
	$list[] = $row;
}
$smarty->assign('simple_resume',$list);
$smarty->display("m/simple-resume-list.html");
?>