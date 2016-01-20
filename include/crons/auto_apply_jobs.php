<?php
 /*
 * 74cms 计划任务 自动投递简历
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
if(!defined('IN_QISHI'))
{
die('Access Denied!');
}
	global $_CFG,$db;
	$result = $db->query("select * from ".table('resume_entrust'));
	$time=time();
	while($row = $db->fetch_array($result))
	{
		if($row['entrust_end']>$time)
		{
			$district_sql='';
			$category_sql='';
			if($row['id'])
			{
				//查出该简历工作表中工作过的企业
				$worksql = "select * from ".table('resume_work')." where pid=".$row['id']." and uid=".$row['uid'];
				$work = $db->getall($worksql);
				// 简历期望地区
				$resume_basic = $db->getall("select district,sdistrict from ".table('resume_district')." where pid=".$row['id']);
				$sdistrict_sql='';
				foreach ($resume_basic as $value)
				{
					if($value['sdistrict'])
					{
						$sdistrict_sql.= $sdistrict_sql?" or sdistrict=".intval($value['sdistrict']):" sdistrict=".intval($value['sdistrict']);
					}elseif($value['district'])
					{	
						$sdistrict_sql.= $sdistrict_sql?" or district=".intval($value['district']):" district=".intval($value['district']);
					}
				}
				
				$district_sql.=$sdistrict_sql?" and (".$sdistrict_sql.")":"";
				// 简历期望职位
				$resume_jobs= $db->getall("select * from ".table('resume_jobs')." where pid=$row[id]");
				$sub_sql='';
				foreach ($resume_jobs as $value)
				{
					if($value['subclass'])
					{
						$sub_sql.= $sub_sql?" or subclass=".intval($value['subclass']):" subclass=".intval($value['subclass']);
					}elseif($value['category'])
					{	
						$sub_sql.= $sub_sql?" or category=".intval($value['category']):" category=".intval($value['category']);
					}
					
				}
				$category_sql.=$sub_sql?" and (".$sub_sql.")":"";

				$jobs = $db->getall("select id,jobs_name,company_id,companyname,uid from ".table('jobs')." where is_entrust!=1 {$district_sql} {$category_sql} order by refreshtime desc limit 10 ");
			}
			if(empty($jobs)){
				continue;
			}
			else{
				foreach ($jobs as $key => $value) {
					if (check_jobs_apply($value['id'],$row['id'],$row['uid']))
					{
						continue;
					}
					// 个人屏蔽企业
					$personal_shield_company=$db->getone("select id from ".table('personal_shield_company')." where pid={$row['id']} and comkeyword='".$value['companyname']."'");
					if(!empty($personal_shield_company))
					{
						continue;
					}
					// 屏蔽工作经验
					if(!empty($work))
					{
						$is_cont=0;
						foreach ($work as  $val) 
						{
							if($value['companyname'] == $val['companyname'])
							{
								$is_cont=1;
							}
						}
						if($is_cont == 1)
						{
							continue;
						}
					}
			 		$addarr['resume_id']=$row['id'];
					$addarr['resume_name']=$row['fullname'];
					$addarr['personal_uid']=intval($row['uid']);
					$addarr['jobs_id']=$value['id'];
					$addarr['jobs_name']=$value['jobs_name'];
					$addarr['company_id']=$value['company_id'];
					$addarr['company_name']=$value['companyname'];
					$addarr['company_uid']=$value['uid'];
					$addarr['notes']= "委托投递";
					$addarr['apply_addtime']=time();
					$addarr['personal_look']=1;
					$db->inserttable(table('personal_jobs_apply'),$addarr);	
				}
			}
		}
		else
		{
			$db->query("delete from ".table('resume_entrust')." where id=".$row['id']);
			$db->updatetable(table("resume"),array("entrust"=>"0")," id=".$row['id']." ");
		}
	}
	//更新任务时间表
	if ($crons['weekday']>=0)
	{
	$weekday=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$nextrun=strtotime("Next ".$weekday[$crons['weekday']]);
	}
	elseif ($crons['day']>0)
	{
	$nextrun=strtotime('+1 months'); 
	$nextrun=mktime(0,0,0,date("m",$nextrun),$crons['day'],date("Y",$nextrun));
	}
	else
	{
	$nextrun=time();
	}
	if ($crons['hour']>=0)
	{
	$nextrun=strtotime('+1 days',$nextrun); 
	$nextrun=mktime($crons['hour'],0,0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
	}
	if (intval($crons['minute'])>0)
	{
	$nextrun=strtotime('+1 hours',$nextrun); 
	$nextrun=mktime(date("H",$nextrun),$crons['minute'],0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
	}
	$setsqlarr['nextrun']=$nextrun;
	$setsqlarr['lastrun']=time();
	$db->updatetable(table('crons'), $setsqlarr," cronid ='".intval($crons['cronid'])."'");

function check_jobs_apply($jobs_id,$resume_id,$p_uid)
{
	global $db;
	$sql = "select did from ".table('personal_jobs_apply')." WHERE personal_uid = '".intval($p_uid)."' AND jobs_id='".intval($jobs_id)."'  AND resume_id='".intval($resume_id)."' LIMIT 1";
	return $db->getone($sql);
}
?>