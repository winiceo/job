<?php
function tpl_function_qishi_shop_show($params, &$smarty)
{
	global $db,$_CFG;
	$arrset=explode(',',$params['set']);
	foreach($arrset as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "商品ID":
			$aset['id'] = $a[1];
			break;
		case "标题长度":
			$aset['titlelen'] = $a[1];
			break;
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		}
	}
	if (is_array($aset)) $aset=array_map("get_smarty_request",$aset);
	$aset['id']=isset($aset['id'])?intval($aset['id']):"";
	$aset['titlelen']=isset($aset['titlelen'])?intval($aset['titlelen']):15;
	$aset['listname']=isset($aset['listname'])?$aset['listname']:"show";
	$result = $db->query("SELECT * FROM ".table('shop_goods')." where id=$aset[id] limit 1");
	$config = get_cache('config');
	$row = $db->fetch_array($result);
	if(!empty($row))
	{
		$row['shop_title_']=cut_str(strip_tags($row['shop_title']),$aset['titlelen'],0,$aset['dot']);
		$row['shop_url']=$config['site_domain'].$config['site_dir']."shop/shop_show.php?id=".$row['id'];
		// 兑换记录
		$row['exchange']=get_exchange(10,$row['id']);
		$smarty->assign($aset['listname'],$row);
	}
	else
	{
		header("HTTP/1.1 404 Not Found"); 
		$smarty->display("404.htm");
		exit();
	}
	
}
?>