<?php
function tpl_function_qishi_shop_list($params, &$smarty)
{
	global $db,$_CFG;
	$arrset=explode(',',$params['set']);
	foreach($arrset as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		case "显示数目":
			$aset['row'] = $a[1];
			break;
		case "标题长度":
			$aset['titlelen'] = $a[1];
			break;
		case "开始位置":
			$aset['start'] = $a[1];
			break;
		case "填补字符":
			$aset['dot'] = $a[1];
			break;
		case "分类ID":
			$aset['scategory'] = $a[1];
			break;
		case "关键字":
			$aset['key'] = $a[1];
			break;
		case "推荐":
			$aset['recommend'] = $a[1];
			break;	
		case "排序":
			$aset['displayorder'] = $a[1];
			break;
		case "分页显示":
			$aset['paged'] = $a[1];
			break;
		case "积分区间":
			$aset['points_interval'] = $a[1];//0|1000
			break;
		case "会员积分":
			$aset['user_points'] = $a[1]; //我能兑换的
			break;
		}
	}
	if (is_array($aset)) $aset=array_map("get_smarty_request",$aset);
	$aset['listname']=isset($aset['listname'])?$aset['listname']:"list";
	$aset['row']=isset($aset['row'])?intval($aset['row']):10;
	$aset['start']=isset($aset['start'])?intval($aset['start']):0;
	$aset['titlelen']=isset($aset['titlelen'])?intval($aset['titlelen']):15;
	$aset['dot']=isset($aset['dot'])?$aset['dot']:'';
	$aset['recommend']=isset($aset['recommend'])?intval($aset['recommend']):1;
	if (isset($aset['displayorder']))
	{
		$arr=explode('>',$aset['displayorder']);
		// 排序方式
		if($arr[1]=='desc'){
			$arr[1]="desc";
		}
		elseif($arr[1]=="asc")
		{
			$arr[1]="asc";
		}
		else
		{
			$arr[1]="desc";
		}
		if($arr[0]=="shop_points")
		{
			$orderbysql=" ORDER BY shop_points {$arr[1]},id desc ";
		}
		elseif($arr[0]=="click")
		{
			$orderbysql=" ORDER BY click {$arr[1]},id desc ";
		}
		else
		{
			if($aset['recommend']>0)
			{
				$orderbysql=" ORDER BY recommend desc,addtime {$arr[1]},id desc ";
			}
			else
			{
				$orderbysql=" ORDER BY addtime {$arr[1]},id desc ";
			}
		}

	}
	// 积分区间
	if(isset($aset['points_interval']) && !empty($aset['points_interval']))
	{
		$points_arr=explode('-', $aset['points_interval']);
		$points_min=$points_arr[0];
		$points_max=$points_arr[1];
		$wheresql.=" AND shop_points>$points_min AND shop_points<$points_max ";
	}
	// 我能兑换的 user_points
	if(isset($aset['user_points']) && !empty($aset['user_points']))
	{
		$user_points=intval($aset['user_points']);
		$wheresql.=" AND shop_points<$user_points ";
	}
	if ($aset['scategory'])
	{
	$wheresql.=" AND scategory=".intval($aset['scategory'])." ";
	}
	if (isset($aset['key']) && !empty($aset['key']))
	{
		$key=help::addslashes_deep(trim($aset['key']));
		$wheresql.=" AND shop_title LIKE '%{$key}%' ";
	}

	if (!empty($wheresql))
	{
	$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
	}

	if (isset($aset['paged']))
	{
		require_once(QISHI_ROOT_PATH.'include/page.class.php');
		$total_sql="SELECT  COUNT(*) AS num  FROM ".table("shop_goods").$wheresql;
		$total_count=$db->get_total($total_sql);
		$page = new page(array('total'=>$total_count, 'perpage'=>$aset['row'],'getarray'=>$_GET));
		$currenpage=$page->nowindex;
		$aset['start']=abs($currenpage-1)*$aset['row'];
		if($total_count>$aset['row'])
		{
			$smarty->assign('page',$page->show(3));
			$smarty->assign('pagemin',$page->show(7));
		}
		$smarty->assign('total',$total_count);
		$smarty->assign('pagenow',$page->show(6));
	}
	$limit=" LIMIT {$aset['start']} , {$aset['row']}";
	$result = $db->query("SELECT * FROM ".table('shop_goods')." ".$wheresql.$orderbysql.$limit);
	$list = array();
	$config = get_cache('config');
	while($row = $db->fetch_array($result))
	{
		$row['shop_title_']=cut_str($row['shop_title'],$aset['titlelen'],0,$aset['dot']);
		$row['shop_url']=url_rewrite('QS_shop_show',array('id'=>$row['id']));
		$list[] = $row;
	}
	$smarty->assign($aset['listname'],$list);
}
?>