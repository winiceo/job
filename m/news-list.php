<?php
 /*
 * 74cms 触屏版新闻列表模块
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
$page = empty($_GET['page'])?1:intval($_GET['page']);
$jobstable=table('article');
$orderbysql=" ORDER BY `addtime` desc";
$wheresql = "";
//类型筛选
$type = intval($_GET['type_id']);
if($type > 0)
{
	$wheresql=" WHERE type_id=".$type;
}
//关键字
$key = empty($_GET['key'])?"":$_GET['key'];
if (!empty($key))
{
	$key=trim($key);
	$wheresql =empty($wheresql)?" WHERE  title LIKE '%{$key}%' ":$wheresql." AND  title LIKE '%{$key}%'";
}
$perpage = 5;
$count  = 0;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$theurl = "news-list.php";
$start = ($page-1)*$perpage;
$total_sql="SELECT COUNT(*) AS num FROM {$jobstable} ".$wheresql.$orderbysql;
$count=$db->get_total($total_sql);
$limit=" LIMIT {$start},{$perpage}";
$idresult = $db->query("SELECT * FROM {$jobstable} ".$wheresql.$orderbysql.$limit);
$article=array();
while($row = $db->fetch_array($idresult))
{
	$row['url'] = wap_url_rewrite("news-show",array("id"=>$row['id']));
	$row['addtime'] = daterange(time(),$row['addtime'],'Y-m-d',"#FF3300");
	$row['bimg']=$_CFG['upfiles_dir'].$row['Small_img'];
	$row['content'] = strip_tags($row['content']);
	$row['content']=cut_str($row['content'],100,0,"...");
	$article[] = $row;
}
$smarty->assign('article',$article);
//新闻资讯分类
if($type > 0)
{
	$category_first = $db->getone("SELECT id,parentid,categoryname FROM ".table('article_category')." where id=".$type);
	$category_more = $db->getall("SELECT id,parentid,categoryname FROM ".table('article_category')." where parentid!=0 and id!=".$type." ORDER BY  id asc");
}
else
{
	$category = $db->getall("SELECT id,parentid,categoryname FROM ".table('article_category')." where parentid!=0 ORDER BY  id asc");
	$category_first = $category[0];
	for ($i=1; $i < count($category) ; $i++) 
	{ 
		$category_more[] = $category[$i];
	}
}
$smarty->assign('category_first',$category_first);
$smarty->assign('category_more',$category_more);
$smarty->display("m/news-list.html");
?>