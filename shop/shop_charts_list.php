<?php
 /*
 * 74cms 积分商城首页
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
$alias="QS_shop_charts";
require_once('shop_common.php');
$smarty->assign("shop_nav","charts");
$aset['scategory']=$_GET['scategory'];
$aset['display']=intval($_GET['display']);
$aset['display2']=intval($_GET['display2']);
$limit=" limit 30 ";
$orderby=" ORDER BY recommend desc,click desc,id desc ";
if (isset($aset['scategory']) && $aset['scategory']!="")
{
$wheresql.=" AND scategory=".intval($aset['scategory'])." ";
}
if($aset['display']>0 && $aset['display2']>0)
{
	$start=$aset['display']-1;
	$end=$aset['display2']-1;
	$limit=" limit $start,$end";
}
if (!empty($wheresql))
{
$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
}
$sql="select * from ".table('shop_goods').$wheresql.$orderby.$limit;

$shop_list=$db->getall($sql);
foreach ($shop_list as $key => $value)
{
	$value['shop_title_']=cut_str($value['shop_title'],10,0,'...');
	$value['content_']=cut_str(strip_tags(htmlspecialchars_decode($value['content'])),50,0,'...');
	$value['shop_url']=$_CFG['site_domain'].$_CFG['site_dir']."shop/shop_show.php?id=".$value['id'];
	$value['displaynum']=++$num;
	$shop_list[$key]=$value;
}
$smarty->assign("list",$shop_list);
if($mypage['caching']>0){
        $smarty->cache =true;
		$smarty->cache_lifetime=$mypage['caching'];
	}else{
		$smarty->cache = false;
	}
$cached_id=$alias.(isset($_GET['id'])?"|".(intval($_GET['id'])%100).'|'.intval($_GET['id']):'').(isset($_GET['page'])?"|p".intval($_GET['page'])%100:'');
if(!$smarty->is_cached($mypage['tpl'],$cached_id))
{
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$mypage['tpl']=get_tpl("shop",$_GET['id']).$mypage['tpl'];
$smarty->display($mypage['tpl'],$cached_id);
$db->close();
}
else
{
$smarty->display($mypage['tpl'],$cached_id);
}
unset($smarty);
unset($smarty);
?>