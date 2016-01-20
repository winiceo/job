<?php
function tpl_function_qishi_hunter_jobs_show($params, &$smarty){
	global $db,$timestamp,$_CFG;
	$arr=explode(',',$params['set']);
	foreach($arr as $str)
	{
	$a=explode(':',$str);
		switch ($a[0])
		{
		case "职位ID":
			$aset['id'] = $a[1];
			break;
		case "列表名":
			$aset['listname'] = $a[1];
			break;
		case "描述长度":
			$aset['brieflylen'] = $a[1];
			break;
		case "填补字符":
			$aset['dot'] = $a[1];
			break;
		}
	}
	$aset=array_map("get_smarty_request",$aset);
	$aset['id']=$aset['id']?intval($aset['id']):0;
	$aset['brieflylen']=isset($aset['brieflylen'])?intval($aset['brieflylen']):0;
	$aset['listname']=$aset['listname']?$aset['listname']:"list";
	$wheresql=" WHERE id={$aset['id']} ";
	$sql = "select id,uid,utype,setmeal_deadline,add_mode,contents,jobs_qualified,refreshtime,language,wage_structure,jobs_name,companyname,addtime,click,wage_cn,trade_cn,scale_cn,department,reporter,nature_cn,district_cn,category_cn,age_cn,education_cn,sex_cn,experience_cn from ".table('hunter_jobs').$wheresql." LIMIT 1";
	$val=$db->getone($sql);
	if (empty($val))
	{
		header("HTTP/1.1 404 Not Found"); 
		$smarty->display("404.htm");
		exit();
	}
	else
	{
		if(intval($_SESSION['uid'])>0 && intval($_SESSION['utype'])==2){
			//检查该职位是否对此会员发起面试邀请,并且此会员没看
			$check_int = check_interview(intval($_SESSION['uid']),$val['id']);
			if($check_int){
				update_interview(intval($_SESSION['uid']),$val['id']);
			}
		}
		if ($val['setmeal_deadline']<time() && $val['setmeal_deadline']<>"0" && $val['add_mode']=="2")
		{
		$val['deadline']=$val['setmeal_deadline'];
		}
		$val['jobs_url']=url_rewrite('QS_hunter_jobsshow',array('id'=>$val['id']));
		$val['expire']=sub_day($val['deadline'],time());	
		if ($aset['brieflylen']>0)
		{
			$val['briefly']=cut_str(strip_tags($val['contents']),$aset['brieflylen'],0,$aset['dot']);
			$val['jobs_qualified']=cut_str(strip_tags($val['jobs_qualified']),$aset['brieflylen'],0,$aset['dot']);
		}
		else
		{
			$val['briefly']=strip_tags($val['contents']);
			$val['jobs_qualified']=strip_tags($val['jobs_qualified']);
		}
		$val['refreshtime_cn']=daterange(time(),$val['refreshtime'],'Y-m-d',"#FF3300");
		$val['company_url']=url_rewrite('QS_companyshow',array('id'=>$val['company_id']));
		$val['languagecn']=preg_replace("/\d+/", '',$val['language']);
		$val['languagecn']=preg_replace('/\,/','',$val['languagecn']);
		$val['languagecn']=preg_replace('/\|/','&nbsp;&nbsp;&nbsp;',$val['languagecn']);

		$wage_structure = explode("|", $val['wage_structure']);
		foreach ($wage_structure as $key => $value) {
			$wage = explode(",", $value);
			$val['structure'][$key]['value'] = $wage[1];
		}
		if($val['utype']=='1'){
		$company=GetJobsCompanyProfile($val['uid']);
		$val['company_id']=$company['id'];
		}
		$wheresql=" WHERE huntet_uid='{$val['uid']}' AND jobs_id= '{$val['id']}' AND personal_look=1 ";
		$val['countresume']=$db->get_total("SELECT COUNT(*) AS num FROM ".table('personal_hunter_jobs_apply').$wheresql);
	}
$smarty->assign($aset['listname'],$val);
}
function GetJobsCompanyProfile($uid)
{
	global $db;
	$sql = "select id from ".table('company_profile')." where uid=".intval($uid)." LIMIT 1 ";
	return $db->getone($sql);
}
function check_interview($uid,$jobsid){
	global $db;
	$result = $db->getone("select did from ".table("hunter_interview")." where `personal_look`=1 and  `resume_uid`=".$uid." and `jobs_id`=".$jobsid);
	return $result;
}
function update_interview($uid,$jobsid){
	global $db;
	$setsqlarr['personal_look'] = 2;
	$db->updatetable(table("hunter_interview"),$setsqlarr," `resume_uid`=".$uid." and `jobs_id`=".$jobsid );
}
?>
