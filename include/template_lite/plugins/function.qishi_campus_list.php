<?php
function tpl_function_qishi_campus_list($params, &$smarty)
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
	case "开始位置":
		$aset['start'] = $a[1];
		break;
	case "院校名称":
		$aset['campusname'] = $a[1];
		break;
	case "院校名长度":
		$aset['campusnamelen'] = $a[1];
		break;
	case "填补字符":
		$aset['dot'] = $a[1];
		break;
	case "排序":
		$aset['displayorder'] = $a[1];
		break;
	case "分页显示":
		$aset['paged'] = $a[1];
		break;
	case "院校页面":
		$aset['campusshow'] = $a[1];
		break;
	case "列表页":
		$aset['listpage'] = $a[1];
		break;
	}
}
if (is_array($aset)) $aset=array_map("get_smarty_request",$aset);
$aset['listname']=isset($aset['listname'])?$aset['listname']:"list";
$aset['row']=!empty($aset['row'])?intval($aset['row']):8;
$aset['start']=isset($aset['start'])?intval($aset['start']):0;
$aset['campusnamelen']=isset($aset['campusnamelen'])?intval($aset['campusnamelen']):12;
$aset['dot']=isset($aset['dot'])?$aset['dot']:'';
$aset['campusshow']=isset($aset['campusshow'])?$aset['campusshow']:'QS_campusshow';
$aset['listpage']=!empty($aset['listpage'])?$aset['listpage']:'QS_campuslist';
//排序
if ($aset['displayorder'])
{
	if (strpos($aset['displayorder'],'>'))
	{
		$arr=explode('>',$aset['displayorder']);
		// 排序字段
		if($arr[0]=='addtime')
		{
			$arr[0]="addtime";
		}
		else
		{
			$arr[0]="id";
		}
		// 排序方式
		if($arr[1]=='desc')
		{
			$arr[1]="desc";
		}
		else
		{
			$arr[1]="asc";
		}
		if ($arr[0] && $arr[1])
		{
			$orderbysql=" ORDER BY `".$arr[0]."` ".$arr[1];
		}
	}
}
//院校名称
if (!empty($aset['campusname']))
{
	$wheresql.=" AND (campusname  like '%".trim($aset['campusname'])."%') ";
}
if (!empty($wheresql))
{
$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
}
if (isset($aset['paged']))
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$total_sql="SELECT COUNT(*) AS num FROM ".table('cooperate_campus').$wheresql;
	$total_count=$db->get_total($total_sql);
	$pagelist = new page(array('total'=>$total_count, 'perpage'=>$aset['row'],'alias'=>$aset['listpage']));
	$currenpage=$pagelist->nowindex;
	$aset['start']=($currenpage-1)*$aset['row'];
	if ($total_count>$aset['row'])
	{
	$smarty->assign('page',$pagelist->show(3));
	}
	$smarty->assign('total',$total_count);
}
$limit=" LIMIT ".abs($aset['start']).','.$aset['row'];
$result = $db->query("SELECT id,campusname,website,logo,address FROM ".table('cooperate_campus')." ".$wheresql.$orderbysql.$limit);
$list= array();
while($row = $db->fetch_array($result))
{
	$row['campusname_']=$row['campusname'];
	$row['campusname']=cut_str($row['campusname'],$aset['campusnamelen'],0,$aset['dot']);
	//主页
	$row['website_']=$row['website'];
	$frist = stristr($row['website'], 'http' );
	if(!$frist)
	{
		$row['website'] = "http://".$row['website'];
	}

	$row['url'] = url_rewrite($aset['campusshow'],array('id'=>$row['id']));
	if ($row['logo'])
	{
		$row['logo']=$_CFG['site_dir']."data/campus/logo/".$row['logo'];
	}
	else
	{
		$row['logo']=$_CFG['site_dir']."data/campus/logo/no_logo.gif";
	}
	$list[] = $row;
}
$smarty->assign($aset['listname'],$list);
}
?>