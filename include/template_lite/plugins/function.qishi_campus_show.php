<?php
function tpl_function_qishi_campus_show($params, &$smarty)
{ 
	global $db,$_CFG;
	$arr=explode(',',$params['set']);
	foreach($arr as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "院校ID":
			$aset['id'] = $a[1];
			break;
		case "院校介绍长度":
			$aset['campusnamelen'] = $a[1];
			break;
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		}
	}
	$aset=array_map("get_smarty_request",$aset);
	$aset['id']=$aset['id']?intval($aset['id']):0;
	$aset['listname']=$aset['listname']?$aset['listname']:"list";
	$aset['campusnamelen']=isset($aset['campusnamelen'])?intval($aset['campusnamelen']):50;
	$sql = "select id,campusname,website,logo,address,contents from ".table('cooperate_campus')." WHERE  id='{$aset['id']}'  LIMIT  1";
	$profile=$db->getone($sql);
	if (empty($profile))
	{
		header("HTTP/1.1 404 Not Found"); 
		$smarty->display("404.htm");
		exit();
	}
	else
	{
		//主页
		$profile['website_']=$profile['website'];
		$frist = stristr($profile['website'], 'http' );
		if(!$frist)
		{
			$profile['website'] = "http://".$profile['website'];
		}
			
		//统计注册毕业生数
		$sql = "SELECT COUNT(distinct(pid)) AS num FROM ".table('resume_education')." as e INNER JOIN ".table('resume')." as r on r.id=e.pid  WHERE e.school='".trim($profile['campusname']). "' AND r.display=1  ";
		$graduate_num = $db->get_total($sql);
		$profile['graduate_num']=$graduate_num;
		/* 院校 风采图片 start */
		$profile['campus_img_num']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('cooperate_campus_img')." WHERE campus_id='{$profile['id']}'  ");
		$campus_img=$db->getall("select img from ".table("cooperate_campus_img"). " WHERE campus_id='{$profile['id']}' order by addtime desc ");
		$profile['campus_img']=$_CFG['site_domain'].$_CFG['site_dir']."data/campus/img/".$campus_img[0]['img'];
		foreach ($campus_img as $key => $value)
		{
			$campus_img[$key]=$_CFG['site_domain'].$_CFG['site_dir']."data/campus/img/".$value['img'];
		}
		$profile['campus_img_big']=$campus_img;
		/* 院校 风采图片 end */
		if ($profile['logo'])
		{
			$profile['logo']=$_CFG['site_dir']."data/campus/logo/".$profile['logo'];
		}
		else
		{
			$profile['logo']=$_CFG['site_dir']."data/campus/logo/no_logo.gif";
		}
		$profile['contents']=htmlspecialchars_decode($profile['contents']);
	} 
	$smarty->assign($aset['listname'],$profile);
}
 
?>