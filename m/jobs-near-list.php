<?php
 /*
 * 74cms WAP
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(QISHI_ROOT_PATH.'include/fun_wap.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'near';
if($act == 'near')
{
	$lng = trim($_GET['lng']);
	$lat = trim($_GET['lat']);
	//筛选条件
	$rows = intval($_GET['rows'])>0 ? intval($_GET['rows']) : 5;
	$offset = intval($_GET['offset'])>0 ? intval($_GET['offset']) : 0;
	$district = intval($_GET['district'])==0?"":intval($_GET['district']);
	$sdistrict = intval($_GET['sdistrict'])==0?"":intval($_GET['sdistrict']);
	$trade = intval($_GET['trade'])==0?"":intval($_GET['trade']);
	$topclass = intval($_GET['topclass'])==0?"":intval($_GET['topclass']);
	$category = intval($_GET['category'])==0?"":intval($_GET['category']);
	$subclass = intval($_GET['subclass'])==0?"":intval($_GET['subclass']);
	$recommend = intval($_GET['recommend'])==0?"":intval($_GET['recommend']);
	$emergency = intval($_GET['emergency'])==0?"":intval($_GET['emergency']);
	$wage = intval($_GET['wage'])==0?"":intval($_GET['wage']);
	$key = empty($_GET['key'])?"":$_GET['key'];
	$settr = intval($_GET['settr'])==0?"":intval($_GET['settr']);
	$education = intval($_GET['education'])==0?"":intval($_GET['education']);
	$experience = intval($_GET['experience'])==0?"":intval($_GET['experience']);
	$nature = intval($_GET['nature'])==0?"":intval($_GET['nature']);
	$scale = intval($_GET['scale'])==0?"":intval($_GET['scale']);
	$jobstable=table('jobs_search_key');
	if ($district<>'')
	{
		$wheresql.=" AND `district` = ".$district;
		if ($sdistrict<>'')
		{
			$wheresql.=" AND `sdistrict` = ".$sdistrict;
		}
	}
	if ($trade<>'')
	{
		$wheresql.=" AND `trade` = ".$trade;
	}
	if ($topclass<>'')
	{
		$wheresql.=" AND `topclass` = ".$topclass;
		if ($category<>'')
		{
			$wheresql.=" AND `category` = ".$category;
			if ($subclass<>'')
			{
				$wheresql.=" AND `subclass` = ".$subclass;
			}
		}
	}
	if ($wage<>'')
	{
		$wheresql.=" AND `wage` = ".$wage;
	}
	if ($recommend<>'')
	{
		$wheresql.=" AND `recommend` = ".$recommend;
	}
	if ($emergency<>'')
	{
		$wheresql.=" AND `emergency` = ".$emergency;
	}
	if (!empty($key))
	{
		$key=help::addslashes_deep(trim($key));
		if ($_CFG['jobsearch_type']=='1')
		{
			$akey=explode(' ',$key);
			if (count($akey)>1)
			{
			$akey=array_filter($akey);
			$akey=array_slice($akey,0,2);
			$akey=array_map("fulltextpad",$akey);
			$key='+'.implode(' +',$akey);
			$mode=' IN BOOLEAN MODE';
			}
			else
			{
			$key=fulltextpad($key);
			$mode=' ';
			}
			$wheresql.=" AND  MATCH (`key`) AGAINST ('{$key}'{$mode}) ";
		}
		else
		{
			$wheresql.=" AND likekey LIKE '%{$key}%' ";
		}
	}
	if($settr<>'')
	{
		$settr_val=strtotime("-{$settr} day");
		$wheresql.=" AND refreshtime>{$settr_val} ";
	}
	if($education<>'')
	{
		$wheresql.=" AND education={$education} ";
	}
	if($experience<>'')
	{
		$wheresql.=" AND experience={$experience} ";
	}
	if($nature<>'')
	{
		$wheresql.=" AND nature={$nature} ";
	}
	if($scale<>'')
	{
		$wheresql.=" AND scale={$scale} ";
	}
	if (!empty($wheresql))
	{
		$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
		$wheresql .= " AND map_x!='' AND map_y!='' ";
	}
	$id = array();
	if(!empty($lng) && !empty($lat))
	{
		$idresult = $db->query("SELECT id , ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM {$jobstable} ".$wheresql." ORDER BY juli ASC   LIMIT {$offset},{$rows}");
		while($row = $db->fetch_array($idresult))
		{
			$id[]=$row['id'];
		}
	}
	if (!empty($id))
	{
		$wheresql=" WHERE id IN (".implode(',',$id).") ";
		$sql = "SELECT *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM ".table('jobs').$wheresql."  ORDER BY juli ASC , stick DESC , refreshtime DESC ";
		$jobs_list = $db->getall($sql);
		foreach ($jobs_list as $key => $value) 
		{
			$jobs_list[$key]["juli"] = distancerange($value["juli"]);
		}
	}
	else
	{
		$jobs_list=array();
	}
	$smarty->assign('jobs',$jobs_list);
	$smarty->assign('goback',$_SERVER["HTTP_REFERER"]);
	//热门关键字
	$sqlnearhot="select w_word,w_hot from ".table("hotword")." order by w_hot desc limit 15 ";
	$hotword_list = $db->getall($sqlnearhot);
	$smarty->assign('hotword',$hotword_list);
	$smarty->display("m/m-jobs-near-list.html");
}
elseif($act == 'ajaxjobsnearlist')
{
	$district = intval($_GET['district'])==0?"":intval($_GET['district']);
	$sdistrict = intval($_GET['sdistrict'])==0?"":intval($_GET['sdistrict']);
	$trade = intval($_GET['trade'])==0?"":intval($_GET['trade']);
	$topclass = intval($_GET['topclass'])==0?"":intval($_GET['topclass']);
	$category = intval($_GET['category'])==0?"":intval($_GET['category']);
	$subclass = intval($_GET['subclass'])==0?"":intval($_GET['subclass']);
	$recommend = intval($_GET['recommend'])==0?"":intval($_GET['recommend']);
	$emergency = intval($_GET['emergency'])==0?"":intval($_GET['emergency']);
	$wage = intval($_GET['wage'])==0?"":intval($_GET['wage']);
	$key = empty($_GET['key'])?"":iconv("UTF-8","GBK",$_GET['key']);
	$settr = intval($_GET['settr'])==0?"":intval($_GET['settr']);
	$education = intval($_GET['education'])==0?"":intval($_GET['education']);
	$experience = intval($_GET['experience'])==0?"":intval($_GET['experience']);
	$nature = intval($_GET['nature'])==0?"":intval($_GET['nature']);
	$scale = intval($_GET['scale'])==0?"":intval($_GET['scale']);
	$lng = trim($_GET['lng']);
	$lat = trim($_GET['lat']);
	$jobstable=table('jobs_search_key');
	$rows = intval($_GET['rows'])>0 ? intval($_GET['rows']) : 5;
	$offset = intval($_GET['offset'])>0 ? intval($_GET['offset']) : 0;
	if ($district<>'')
	{
		$wheresql.=" AND `district` = ".$district;
		if ($sdistrict<>'')
		{
			$wheresql.=" AND `sdistrict` = ".$sdistrict;
		}
	}
	if ($trade<>'')
	{
		$wheresql.=" AND `trade` = ".$trade;
	}
	if ($topclass<>'')
	{
		$wheresql.=" AND `topclass` = ".$topclass;
		if ($category<>'')
		{
			$wheresql.=" AND `category` = ".$category;
			if ($subclass<>'')
			{
				$wheresql.=" AND `subclass` = ".$subclass;
			}
		}
	}
	if ($wage<>'')
	{
		$wheresql.=" AND `wage` = ".$wage;
	}
	if ($recommend<>'')
	{
		$wheresql.=" AND `recommend` = ".$recommend;
	}
	if ($emergency<>'')
	{
		$wheresql.=" AND `emergency` = ".$emergency;
	}
	if (!empty($key))
	{
		$key=help::addslashes_deep(trim($key));
		if ($_CFG['jobsearch_type']=='1')
		{
			$akey=explode(' ',$key);
			if (count($akey)>1)
			{
			$akey=array_filter($akey);
			$akey=array_slice($akey,0,2);
			$akey=array_map("fulltextpad",$akey);
			$key='+'.implode(' +',$akey);
			$mode=' IN BOOLEAN MODE';
			}
			else
			{
			$key=fulltextpad($key);
			$mode=' ';
			}
			$wheresql.=" AND  MATCH (`key`) AGAINST ('{$key}'{$mode}) ";
		}
		else
		{
			$wheresql.=" AND likekey LIKE '%{$key}%' ";
		}
	}
	if($settr<>'')
	{
		$settr_val=strtotime("-{$settr} day");
		$wheresql.=" AND refreshtime>{$settr_val} ";
	}
	if($education<>'')
	{
		$wheresql.=" AND education={$education} ";
	}
	if($experience<>'')
	{
		$wheresql.=" AND experience={$experience} ";
	}
	if($nature<>'')
	{
		$wheresql.=" AND nature={$nature} ";
	}
	if($scale<>'')
	{
		$wheresql.=" AND scale={$scale} ";
	}
	if (!empty($wheresql))
	{
		$wheresql=" WHERE ".ltrim(ltrim($wheresql),'AND');
		$wheresql .= " AND map_x!='' AND map_y!='' ";
	}
	else
	{
		$wheresql = " WHERE map_x!='' AND map_y!='' ";
	}

	$idresult = $db->query("SELECT id , ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM {$jobstable} {$wheresql} ORDER BY juli ASC   LIMIT {$offset},{$rows}");
	while($row = $db->fetch_array($idresult))
	{
		$id[]=$row['id'];
	}
	if (!empty($id))
	{
		$wheresql=" WHERE id IN (".implode(',',$id).") ";
		$sql = "SELECT *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM ".table('jobs').$wheresql."  ORDER BY juli ASC , stick DESC , refreshtime DESC ";
		$jobs_list = $db->getall($sql);
	}
	else
	{
		$jobs_list=array();
	}
	if(!empty($jobs_list) && $offset<=100)
	{
		foreach ($jobs_list as $key => $li) 
		{
			$url = wap_url_rewrite("jobs-show",array("id"=>$li['id']));
			$jobslisthtml.='<section class="jobs-item thisurl" url="'.$url.'">';
			$ding = '';
			if($li['stick']=="1")
			{
				$ding = '<span class="job-ding"></span><h3><a href="'.$url.'">'.$li["jobs_name"].'</a>';
			}
			else
			{
				$ding = '<h3><a href="'.$url.'">'.$li["jobs_name"].'</a>';
			}
			$emergency = '';
			if($li['emergency']=="1")
			{
				$emergency = '<i class="ji-icon">急</i>';
			}
			$recommend = '';
			if($li['recommend']=="1")
			{
				$recommend = '<i class="jian-icon">荐</i>';
			}
			$jobslisthtml.=$ding.$emergency.$recommend;
			$jobslisthtml.='</h3>
						<div class="jobs-add">'.$li["district_cn"].' | '.$li["companyname"].'</div>
						<div class="pay-date clearfix">
							<div class="money f-left">'.$li["wage_cn"].'</div>
							<span class="f-right date">';
			$juli = distancerange($li["juli"]);
			$jobslisthtml.=$juli.'</span></div></section>';
		}
		exit($jobslisthtml);
	}
	else
	{
		exit('-1');
	}
}
?>