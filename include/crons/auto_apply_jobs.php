<?php
 /*
 * 74cms �ƻ����� �Զ�Ͷ�ݼ���
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
				//����ü����������й���������ҵ
				$worksql = "select * from ".table('resume_work')." where pid=".$row['id']." and uid=".$row['uid'];
				$work = $db->getall($worksql);
				// ������������
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
				// ��������ְλ
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
					// ����������ҵ
					$personal_shield_company=$db->getone("select id from ".table('personal_shield_company')." where pid={$row['id']} and comkeyword='".$value['companyname']."'");
					if(!empty($personal_shield_company))
					{
						continue;
					}
					// ���ι�������
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
					$addarr['notes']= "ί��Ͷ��";
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
	//��������ʱ���
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