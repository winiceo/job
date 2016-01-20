<?php
/*********************************************
*导航栏目
* *******************************************/
function tpl_function_qishi_get_salary_data_salary_barchart($params, &$smarty)
{
	global $db,$_CFG;
	$arr=explode(',',$params['set']);
	foreach($arr as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "调用名称":
			$aset['alias'] = $a[1];
			break;
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		case "地区":
			$aset['district'] = $a[1];
			break;
		case "职位":
			$aset['category'] = $a[1];
			break;
		}
	}
	$filename = urlencode($aset['district'].'_'.$aset['category']).'_salary_barchart.cache';
	$result = check_cache($filename,'salary',7);
	if(!$result){
		$result = dfopen("http://www.74cms.com/salary/get_salary_data_salary_barchart.php?district=".$aset['district']."&category=".$aset['category']."&certification=".$_SERVER['SERVER_NAME']);
		write_cache($filename,$result,'salary');
	}
	$smarty->assign($aset['listname'],$result);
}

?>