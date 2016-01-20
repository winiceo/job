<?php
 /*
 * 74cms 数据库
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'check_database';
$smarty->assign('act',$act);
if ($act=="check_database")
{
	$smarty->display('sys/admin_check_database.htm');
}
elseif($act=="do_check") 
{
	$sql_data = array();
	$local_detail = array();
	//验证 授权码
	$rst=https_request("http://www.74cms.com/plus/check_webkey.php?web_key=$_POST[web_key]&web=$_CFG[site_domain]");
	if($rst===false)
	{
		adminmsg("请先开启您php，curl模块!");
	}
	elseif($rst=="err")
	{
		adminmsg("您输入的授权码有误!");
	}
	else
	{
		$sql_data=json_decode($rst,1);
	}
	//得到数据库中表以及表中的字段值
	$local_table = mysql_query("show tables");
	while($local = mysql_fetch_array($local_table))
	{
		//$name-> 表名
		$name = $local[0];
		$name = substr($local[0],strpos($local[0],'_')+1);
		//desc table_name -> 查看表描述(字段  类型  是否为空  主外键  默认值  额外/自增)
		//MYSQL_ASSOC -> 只得到关联索引
		$table_info = mysql_query("desc ".$pre."$name");
		while ($info = mysql_fetch_assoc($table_info)) 
		{
			$local_detail[$name][] =$info;
		}
	}
	$server_detail = $sql_data;
	//校验(双向校验)
	$diff_field = $diff_table = array();
	foreach($server_detail as $k=>$v)
	{
		//判断服务器数据库中是否有此表的信息()
		if(!is_array($local_detail[$k]))
		{
			//把不一样的表 放到diff_table数组中
			$diff_table['less'][] = $k;
		}
		else
		{
			//判断字段数(值  顺序  类型)是否一样
			if(!($local_detail[$k] === $v))
			{
				//把不一样的字段 放到diff_field数组中
				foreach($v as $k1=>$v1)
				{
					if(!in_array($v1,$local_detail[$k]))
					{
						$diff_field[$k][] = $v1;
					}
				}
			}
		}
	}
	foreach($local_detail as $k=>$v)
	{
		//判断服务器数据库中是否有此表的信息()
		if(!is_array($server_detail[$k]))
		{
			//把不一样的表 放到diff_table数组中
			$diff_table['many'][] = $k;
		}
	}
	$smarty->assign('less',$diff_table['less']); 	//本地数据库缺少的表
	$smarty->assign('less_num',count($diff_table['less']));
	$smarty->assign('many',$diff_table['many']);	//本地数据库多出的表
	$smarty->assign('many_num',count($diff_table['many']));
	$smarty->assign('different',$diff_field);		//本地数据库不同于服务器的表
	$smarty->assign('diff_num',count($diff_field));
	$smarty->display('sys/admin_check_database.htm');
}
?>