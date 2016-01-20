<?php
function tpl_function_qishi_train_show($params, &$smarty)
{ 
	global $db,$_CFG;
	$arr=explode(',',$params['set']);
	foreach($arr as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "机构ID":
			$aset['id'] = $a[1];
			break;
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		}
	}
		$aset=array_map("get_smarty_request",$aset);
		$aset['id']=$aset['id']?intval($aset['id']):0;
		$aset['listname']=$aset['listname']?$aset['listname']:"list";
		$wheresql.=" AND  user_status=1 ";
		$sql = "select id,uid,contents,website,logo,trainname,address,nature_cn,district_cn,founddate from ".table('train_profile')." WHERE  id='{$aset['id']}' {$wheresql} LIMIT  1";
		$profile=$db->getone($sql);
		if (empty($profile))
		{
			header("HTTP/1.1 404 Not Found"); 
			$smarty->display("404.htm");
			exit();
		}
		else
		{
		$profile['train_url']=url_rewrite('QS_train_agencyshow',array('id'=>$profile['id']));
		$profile['train_profile']=$profile['contents'];
		$profile['description']=cut_str(strip_tags($profile['contents']),50,0,"...");
		$wheresql=" WHERE train_id='{$profile['id']}' and audit='1' ";
		$profile['countresume']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('train_news').$wheresql);
		$profile['countjob']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('course').$wheresql);

		/* 机构 风采图片 start */
		$profile['train_img_num']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('train_img')." WHERE uid='{$profile['uid']}' and audit=1 ");
		$train_img=$db->getall("select img from ".table("train_img"). " WHERE uid='{$profile['uid']}' and audit=1 order by addtime desc ");
		$profile['train_img']=$train_img[0]['img'];
		foreach ($train_img as $key => $value)
		{
			$train_img[$key]=$_CFG['site_domain'].$_CFG['site_dir']."data/train_img/original/".$value['img'];
		}
		$profile['train_img_big']=$train_img;
		/* 机构 风采图片 end */

		if($profile['website']){
			if(strstr($profile['website'],"http://")===false){
				$profile['website'] = "http://".$profile['website'];
			}
		}
			if ($profile['logo'])
			{
			$profile['logo']=$_CFG['site_dir']."data/train_logo/".$profile['logo'];
			}
			else
			{
			$profile['logo']=$_CFG['site_dir']."data/train_logo/no_logo.gif";
			}
		}
	$smarty->assign($aset['listname'],$profile);
}
?>