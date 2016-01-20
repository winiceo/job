<?php
 /*
 * 74cms 系统设置
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
require_once(ADMIN_ROOT_PATH.'include/admin_app_fun.php');
require_once(ADMIN_ROOT_PATH.'include/upload.php');
$ads_updir="../data/appads/";
$ads_dir=$_CFG['site_dir']."data/appads/";
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'set';
$smarty->assign('pageheader',"APP配置");
$config = get_cache('config');
//基本设置
if($act == 'set')
{	
	get_token();
	check_permissions($_SESSION['admin_purview'],"set_app");
	$smarty->assign('config',$config);
	$smarty->assign('navlabel',"set");	
	$smarty->display('app/admin_set_app.htm');
}
elseif($act == 'set_save')
{
	check_token();
	foreach($_POST as $k => $v)
	{
		!$db->query("UPDATE ".table('config')." SET value='{$v}' WHERE name='{$k}'")?adminmsg('更新APP设置失败', 1):"";
	}
	refresh_cache('config');
	//填写管理员日志
	write_log("后台成功设置APP配置", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2);
}
//广告管理
elseif($act == 'ad_list')
{
	get_token();
	check_permissions($_SESSION['admin_purview'],"set_app_ad");
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	//右下角搜索
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		
		if($key_type===1)$wheresql=" WHERE a.title like '%{$key}%'";
	}
	else
	{
		$category_id=isset($_GET['category_id'])?intval($_GET['category_id']):"";
		if ($category_id>0)
		{
		$wheresql=empty($wheresql)?" WHERE a.category_id= ".$category_id:$wheresql." AND a.category_id= ".$category_id;
		}
		$settr=$_GET['settr'];
		if ($settr<>"")
		{
			$wheresql.=empty($wheresql)?" WHERE ":" AND  ";
			$days=intval($settr);
			$settr=strtotime($days." day");
			if ($days===0)
			{
			$wheresql.=" a.deadline< ".time()." AND a.deadline>0 ";
			}
			else
			{
			$wheresql.=" a.deadline< ".$settr." AND  a.deadline>".time()." ";
			}		
		}
		$is_display=isset($_GET['is_display'])?$_GET['is_display']:"";
		if ($is_display<>'')
		{
		$is_display=intval($is_display);
		$wheresql=empty($wheresql)?" WHERE a.is_display= ".$is_display:$wheresql." AND a.is_display= ".$is_display;
		}
	}
	$joinsql=" LEFT JOIN  ".table('ad_app_category')." AS c ON  a.category_id=c.id ";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('ad_app')." AS a " .$joinsql.$wheresql;
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('list',get_ad_list($offset,$perpage,$joinsql.$wheresql));
	$smarty->assign('ad_app_category',get_ad_app_category());
	$smarty->assign('page',$page->show(3));
	$smarty->assign('total',$total_val);
	$smarty->assign('navlabel',"ad_list");
	$smarty->display('app/admin_ad_app_list.htm');
}
//添加广告
elseif($act == 'ad_add')
{
	$smarty->assign('datefm',convert_datefm(time(),1));
	$smarty->assign('ad_app_category',get_ad_app_category());
	$smarty->assign('navlabel',"ad_list");
	get_token();
	$smarty->display('app/admin_ad_app_add.htm');
}
//保存添加广告
elseif($act == 'ad_add_save')
{
	check_token();
	$setsqlarr['title']=trim($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['is_display']=trim($_POST['is_display'])?trim($_POST['is_display']):0;
	$setsqlarr['category_id']=trim($_POST['category_id'])?trim($_POST['category_id']):adminmsg('您没有选择广告分类！',1);
	$setsqlarr['type_id']=trim($_POST['type_id'])?trim($_POST['type_id']):adminmsg('您没有选择广告类型！',1);
	$setsqlarr['alias']=trim($_POST['alias'])?trim($_POST['alias']):adminmsg('参数错误，调用ID不存在！',1);
	$setsqlarr['show_order']=intval($_POST['show_order']);
	$setsqlarr['note']=trim($_POST['note']);	
	if ($_POST['starttime']=="")
	{
		$setsqlarr['starttime']=0;
	}
	else
	{
		$setsqlarr['starttime']=intval(convert_datefm($_POST['starttime'],2));
	}
	if($_POST['deadline']=="")
	{
		$setsqlarr['deadline']=0;
	}
	else
	{
		$setsqlarr['deadline']=intval(convert_datefm($_POST['deadline'],2));
	}
	//图片
	if($setsqlarr['type_id']=="2")
	{
		if (empty($_FILES['img_file']['name']) && empty($_POST['img_path']))
		{
			adminmsg('请上传图片或者填写图片路径！',1);
		}
		if ($_FILES['img_file']['name'])
		{
			$ads_updir=$ads_updir.date("Y/m/d/");
			make_dir($ads_updir);
			$setsqlarr['img_path']=_asUpFiles($ads_updir,"img_file",1000,'gif/jpg/bmp/png',true);
			if (empty($setsqlarr['img_path']))
			{
				adminmsg('上传文件失败！',1);
			}
			$setsqlarr['img_path']=$ads_dir.date("Y/m/d/").$setsqlarr['img_path'];
		}
		else
		{
			$setsqlarr['img_path']=trim($_POST['img_path']);
		}
		$setsqlarr['img_url']=trim($_POST['img_url']);
		$setsqlarr['img_explain']=trim($_POST['img_explain']);
		$setsqlarr['img_uid']=intval($_POST['img_uid']);
	}
	else
	{
		adminmsg('广告类型错误！',1);
	}
	$setsqlarr['addtime']=$timestamp;
	$link[0]['text'] = "继续添加";
	$link[0]['href'] ="?act=ad_add&category_id=".$_POST['category_id']."&type_id=".$_POST['type_id']."&alias=".$_POST['alias'];
	$link[1]['text'] = "返回广告列表";
	$link[1]['href'] ="?act=ad_list";
	$ad_id = $db->inserttable(table('ad_app'),$setsqlarr,true);
	if($ad_id < 0)
	{
		//填写管理员日志
		write_log("后台添加APP广告失败", $_SESSION['admin_name'],3);
		adminmsg("添加失败！",0);
	}
	else
	{
		//若添加的欢迎页广告,则需将之前的设置为禁用
		if($setsqlarr['category_id']=='2')
		{
			$db->updatetable(table('ad_app'),array('is_display'=>0)," id!={$ad_id} and category_id=2 ");
		}
		//若添加的是首页轮番广告,并且正常显示的大于了5个,则需将最早上传的设置为禁用
		if($setsqlarr['category_id']=='1')
		{
			//统计正常显示的首页轮番广告
			$num_add_index = $db->get_total("SELECT COUNT(*) AS num FROM ".table('ad_app')." WHERE is_display=1 AND category_id=1 ");
			if($num_add_index > 5)
			{
				$db->updatetable(table('ad_app'),array('is_display'=>0)," id!={$ad_id} and is_display=1 and category_id=1 order by show_order asc,id asc limit 1 ");
			}
		}
		//填写管理员日志
		write_log("后台成功添加APP广告", $_SESSION['admin_name'],3);
		adminmsg("添加成功！",2,$link);
	}
}
//修改广告
elseif($act == 'edit_ad')
{
	get_token();
	$id=!empty($_GET['id'])?intval($_GET['id']):adminmsg('没有广告id！',1);
	$ad=get_ad_one($id);
	$smarty->assign('ad',$ad);
	$smarty->assign('ad_app_category',get_ad_app_category());//广告位分类列表
	$smarty->assign('url',$_SERVER['HTTP_REFERER']);
	$smarty->display('app/admin_ad_app_edit.htm');
}

//保存:修改广告
elseif($act == 'ad_edit_save')
{
	check_token();
	$setsqlarr['title']=trim($_POST['title'])?trim($_POST['title']):adminmsg('您没有填写标题！',1);
	$setsqlarr['is_display']=trim($_POST['is_display'])?trim($_POST['is_display']):0;
	$setsqlarr['category_id']=trim($_POST['category_id'])?trim($_POST['category_id']):adminmsg('您没有填写广告分类！',1);
	$setsqlarr['type_id']=trim($_POST['type_id'])?trim($_POST['type_id']):adminmsg('您没有填写广告类型！',1);
	$setsqlarr['alias']=trim($_POST['alias'])?trim($_POST['alias']):adminmsg('参数错误，调用ID不存在！',1);
	$setsqlarr['show_order']=intval($_POST['show_order']);
	$setsqlarr['note']=trim($_POST['note']);	
	if ($_POST['starttime']=="")
	{
		$setsqlarr['starttime']=0;
	}
	else
	{
		$setsqlarr['starttime']=intval(convert_datefm($_POST['starttime'],2));
	}
	if ($_POST['deadline']=="")
	{
		$setsqlarr['deadline']=0;
	}
	else
	{
		$setsqlarr['deadline']=intval(convert_datefm($_POST['deadline'],2));
	}
	//图片
	if ($setsqlarr['type_id']=="2")
	{
		if (empty($_FILES['img_file']['name']) && empty($_POST['img_path']))
		{
		adminmsg('请上传图片或者填写图片路径！',1);
		}
		if ($_FILES['img_file']['name'])
		{
			$ads_updir=$ads_updir.date("Y/m/d/");
			make_dir($ads_updir);
			$setsqlarr['img_path']=_asUpFiles($ads_updir,"img_file",1000,'gif/jpg/bmp/png',true);
			if (empty($setsqlarr['img_path']))
			{
				adminmsg('上传文件失败！',1);
			}
			$setsqlarr['img_path']=$ads_dir.date("Y/m/d/").$setsqlarr['img_path'];
		}
		else
		{
			$setsqlarr['img_path']=trim($_POST['img_path']);
		}
		$setsqlarr['img_url']=trim($_POST['img_url']);
		$setsqlarr['img_explain']=trim($_POST['img_explain']);
		$setsqlarr['img_uid']=intval($_POST['img_uid']);
	}
	else
	{
		adminmsg('广告类型错误！',1);
	}
	$setsqlarr['addtime']=$timestamp;
	$link[0]['text'] = "返回列表";
	$link[0]['href'] =trim($_POST['url']);
	$wheresql=" id='".intval($_POST['id'])."' "; 
	if(!$db->updatetable(table('ad_app'),$setsqlarr,$wheresql))
	{
		//填写管理员日志
		write_log("后台修改广告失败", $_SESSION['admin_name'],3);
		adminmsg("修改失败！",0);
	}
	else
	{
		//若修改的是欢迎页广告,并且是修改为正常,则需将之前的设置为禁用
		if($setsqlarr['category_id']=='2' && $setsqlarr['is_display']=='1')
		{
			$db->updatetable(table('ad_app'),array('is_display'=>0)," id!={$_POST['id']} and category_id=2 ");
		}
		//若修改的是首页轮番广告,并且正常显示的大于了5个,则需将最早上传的设置为禁用
		if($setsqlarr['category_id']=='1' && $setsqlarr['is_display']=='1')
		{
			//统计正常显示的首页轮番广告
			$num_add_index = $db->get_total("SELECT COUNT(*) AS num FROM ".table('ad_app')." WHERE is_display=1 AND category_id=1 ");
			if($num_add_index > 5)
			{
				$db->updatetable(table('ad_app'),array('is_display'=>0)," id!={$_POST['id']} and is_display=1 and category_id=1 order by show_order asc,id asc limit 1 ");
			}
		}
		//填写管理员日志
		write_log("后台修改广告成功", $_SESSION['admin_name'],3);
		adminmsg("修改成功！",2,$link);
	}
}
//删除广告
elseif($act=='del_ad')
{
	$id=$_REQUEST['id'];
	check_token();
	if (empty($id)) adminmsg("请选择项目！",0);
	if ($num=del_ad($id))
	{
		adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
		adminmsg("删除失败！".$num,1);
	}
}

?>