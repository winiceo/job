<?php
function tpl_function_qishi_jobfair_list($params, &$smarty)
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
	case "日期范围":
		$aset['settr'] = $a[1];
		break;
	case "分页显示":
		$aset['paged'] = $a[1];
		break;
	case "页面":
		$aset['showname'] = $a[1];
		break;
	case "列表页":
		$aset['listpage'] = $a[1];
		break;
	case "参会企业页":
		$aset['exhibitorspage'] = $a[1];
		break;
	case "校招招聘会列表":
		$aset['campus_fair'] = $a[1];
		break;
	}
}
if (is_array($aset)) $aset=array_map("get_smarty_request",$aset);
$aset['listname']=isset($aset['listname'])?$aset['listname']:"list";
$aset['row']=isset($aset['row'])?intval($aset['row']):10;
$aset['start']=isset($aset['start'])?intval($aset['start']):0;
$aset['titlelen']=isset($aset['titlelen'])?intval($aset['titlelen']):15;
$aset['showname']=isset($aset['showname'])?$aset['showname']:'QS_jobfairshow';
$aset['listpage']=isset($aset['listpage'])?$aset['listpage']:'QS_jobfairlist';
$aset['exhibitorspage']=isset($aset['exhibitorspage'])?$aset['exhibitorspage']:'QS_jobfairexhibitors';
$aset['campus_fair']=isset($aset['campus_fair'])?intval($aset['campus_fair']):0;
$orderbysql=" order BY `order` DESC,id DESC ";
$wheresql=" WHERE display=1 ";
if (isset($aset['settr']))
{
$settr_val=strtotime("-".intval($aset['settr'])." day");
$wheresql.=" AND addtime > ".$settr_val;
}
if (isset($aset['paged']))
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobfair').$wheresql;
	$total_count=$db->get_total($total_sql);
	$pagelist = new page(array('total'=>$total_count, 'perpage'=>$aset['row'],'alias'=>$aset['listpage']));
	$currenpage=$pagelist->nowindex;
	$aset['start']=($currenpage-1)*$aset['row'];
		if ($total_count>$aset['row'])
		{
		$smarty->assign('page',$pagelist->show(3));
		}
		else
		{
		$smarty->assign('page','');
		}
		$smarty->assign('total',$total_count);
}
$limit=" LIMIT ".abs($aset['start']).','.$aset['row'];
$result = $db->query("SELECT id,title,holddate_start,holddate_end,trade_cn,address,map_x,map_y,map_zoom,bus,predetermined_status,predetermined_start,predetermined_end,predetermined_web,predetermined_tel FROM ".table('jobfair')." ".$wheresql.$orderbysql.$limit);
$list= array();
$week=array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
$time=time();
$jobfairArryet = $jobfairArring = $jobfairArringTwo = $jobfairArrover = array();
while($row = $db->fetch_array($result))
{
	//校招招聘会列表
	if(isset($aset['campus_fair']) && !empty($aset['campus_fair']))
	{
		$row['url'] = url_rewrite($aset['showname'],array('id'=>$row['id']));
		$list[] = $row;
	}
	else
	{
		$row['title_']=$row['title'];
		$row['title']=cut_str($row['title'],$aset['titlelen'],0,$aset['dot']);
		$row['holddates_week']=$week[date("w",$row['holddate_start'])];
		$row['url'] = url_rewrite($aset['showname'],array('id'=>$row['id']));
		$row['bus']=cut_str($row['bus'],20,0,"...");
		$row['exhibitorsurl'] = url_rewrite($aset['exhibitorspage'],array('id'=>$row['id']));	
		if($row['predetermined_status']=="1" && $row['predetermined_start']>$time)
		{
			$row['predetermined_ok'] = 1; // 未开始
			$jobfairArryet[] = $row;
		}
		else if ($row['predetermined_status']=="1" && $row['holddate_start']>$time && ($row['predetermined_end']=="0" || $row['predetermined_end']>$time) && ($row['predetermined_web']=="1" || $row['predetermined_tel']=="1"))
		{
			$row['predetermined_ok'] = 2; // 预定中
			if (count($jobfairArring) < 3) {
				$jobfairArring[] = $row;
			} else {
				$jobfairArringTwo[] = $row;
			}
		}
		else
		{
			$row['predetermined_ok'] = 0; // 已结束
			$jobfairArrover[] = $row;
		}
	}
}
//校招招聘会列表
if(isset($aset['campus_fair']) && !empty($aset['campus_fair']))
{
	$smarty->assign($aset['listname'],$list);
}
else
{
	//yet 未开始
	$list['yet'] = $jobfairArryet;
	//ing 预订中
	$list['ing'] = $jobfairArring;
	$list['ingTwo'] = $jobfairArringTwo;
	//over 已经结束
	$list['over'] = $jobfairArrover;
	$smarty->assign($aset['listname'],$list);
}

}
?>