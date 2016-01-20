<?php
 /*
 * 74cms WAP
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
require_once(dirname(__FILE__).'/weixin_share.php');
$show = news_one($_GET['id']);
if($show)
{
	$show['addtime'] = daterange(time(),$show['addtime'],'Y-m-d',"#FF3300");
	$show['bimg']=$_CFG['upfiles_dir'].$show['Small_img'];
	$type_cn = $db->getone(" SELECT * FROM ".table('article_category')." WHERE id=".$show['type_id']);
	$show['type_cn'] = $type_cn['categoryname'];
	$smarty->assign('show',$show);
}
$smarty->assign('goback',$_SERVER["HTTP_REFERER"]);
$smarty->display("m/news-show.html");
?>