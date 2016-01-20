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
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'check_file';
$smarty->assign('act',$act);
if ($act=="check_file")
{
	$smarty->display('sys/admin_check_file.htm');
}
elseif($act=="do_check") 
{
	$sql_data = array();
	$local_detail = array();
	//验证 授权码
	$rst=https_request("http://www.74cms.com/plus/check_webkey.php?web_key=$_POST[web_key]&web=$_CFG[site_domain]&check_type=file");
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

	//获取当前网站 文件目录
	function get_filetree($path)
	{
		$tree = array();
		$single="";
		foreach(glob($path.'/*') as $single){
		if(is_dir($single)){
		$tree = array_merge($tree,get_filetree($single));
		}
		else{
		$tree[] = $single.",".filesize($single).",".date("Y-m-d H:i:s",filemtime($single));
		}
		}
		return $tree;
	}
	$dir=dirname(__FILE__);
	$num=strripos($dir,"\\");
	$path=substr($dir,0,$num);
	$tree=get_filetree($path);
	foreach($tree as $key => $value)
	{
		$num=stripos($value,"/");
		$value=substr($value,$num+1);
		if(preg_match('/data\/|temp|templates\/|statement\/|install\//', $value))
		{
			continue;
		}
		else
		{
			$value_arr=explode(",", $value);
			$num=strripos($value_arr[0],"/");
			$name=substr($value_arr[0],$num+1);
			$tree_arr[$name]=$value_arr;
		}
	}
	$local_detail=$tree_arr;
	$server_detail = $sql_data;
	//校验(双向校验)
	$diff_field = $diff_table = array();
	foreach($server_detail as $k=>$v)
	{
		//判断本地 否有此文件
		if(!is_array($local_detail[$k]))
		{
			//本地缺少文件
			$diff_table['less'][] = $v[0];
		}
		else
		{
			//文件是否修改
			if(!($local_detail[$k] === $v))
			{
				//把不一样的字段 放到diff_field数组中
				if($local_detail[$k][1]!==$v[1] || $local_detail[$k][2]!==$v[2])
				{
					$file['local']=$local_detail[$k];
					$file['web']=$v;
					$diff_field[$k]=$file;
				}
			}
		}
	}
	foreach($local_detail as $k=>$v)
	{
		//判断 服务器上是否有此文件
		if(!is_array($server_detail[$k]))
		{
			//本地多出文件
			$diff_table['many'][] = $v[0];
		}
	}
	$smarty->assign('less',$diff_table['less']); 	//本地数据库缺少的表
	$smarty->assign('less_num',count($diff_table['less']));
	$smarty->assign('many',$diff_table['many']);	//本地数据库多出的表
	$smarty->assign('many_num',count($diff_table['many']));
	$smarty->assign('different',$diff_field);	//本地数据库不同于服务器的表
	$smarty->assign('diff_num',count($diff_field));
	$smarty->display('sys/admin_check_file.htm');
}
?>