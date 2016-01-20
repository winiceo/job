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
require_once(ADMIN_ROOT_PATH.'include/admin_campus_fun.php');
require_once(ADMIN_ROOT_PATH.'include/upload.php');
$campus_updir="../data/campus/logo/";
$campus_dir=$_CFG['site_dir']."data/campus/logo/";
$campus_img_updir="../data/campus/img/";
$campus_img_dir=$_CFG['site_dir']."data/campus/img/";
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'campus_list';
$config = get_cache('config');
check_permissions($_SESSION['admin_purview'],"set_campus");
//合作院校
if($act == 'campus_list')
{
	get_token();
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	//右下角搜索
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		
		if($key_type===1)$wheresql=" WHERE campusname like '%{$key}%'";
	}
	else
	{
		//添加时间
		$settr=$_GET['settr'];
		if ($settr<>"")
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql.=" WHERE addtime> ".$settr;
		}
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('cooperate_campus').$wheresql;
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('list',get_cooperate_campus_list($offset,$perpage,$wheresql));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('total',$total_val);
	$smarty->assign('navlabel',"campus_list");
	$smarty->assign('campus_dir',$campus_dir);
	$smarty->assign('pageheader',"合作院校");
	$smarty->display('campus/admin_cooperate_campus_list.htm');
}
//添加院校
elseif($act == 'campus_add')
{
	get_token();
	$smarty->assign('navlabel',"campus_add");
	$smarty->assign('pageheader',"合作院校");
	$smarty->display('campus/admin_cooperate_campus_add.htm');
}
//保存添加院校
elseif($act == 'campus_add_save')
{
	check_token();
	$setsqlarr['campusname']=trim($_POST['campusname'])?trim($_POST['campusname']):adminmsg('您没有填写院校名称！',1);
	$setsqlarr['website']=trim($_POST['website'])?trim($_POST['website']):adminmsg('您没有填写院校主页！',1);
	$setsqlarr['address']=trim($_POST['address'])?trim($_POST['address']):adminmsg('您没有填写院校地址！',1);
	$setsqlarr['contents']=trim($_POST['contents'])?trim($_POST['contents']):adminmsg('您没有填写院校简介！',1);
	//LOGO
	if (empty($_FILES['logo']['name']))
	{
		adminmsg('请上传图片！',1);
	}
	else
	{
		$campus_updir=$campus_updir.date("Y/m/d/");
		make_dir($campus_updir);
		$setsqlarr['logo']=_asUpFiles($campus_updir,"logo",1000,'gif/jpg/bmp/png',true);
		if (empty($setsqlarr['logo']))
		{
			adminmsg('上传文件失败！',1);
		}
		$setsqlarr['logo']=date("Y/m/d/").$setsqlarr['logo'];
	}
	$setsqlarr['addtime']=$timestamp;
	$link[0]['text'] = "继续添加";
	$link[0]['href'] ="?act=campus_add";
	$link[1]['text'] = "返回院校列表";
	$link[1]['href'] ="?act=campus_list";
	$ad_id = $db->inserttable(table('cooperate_campus'),$setsqlarr,true);
	if($ad_id < 0)
	{
		//填写管理员日志
		write_log("后台添加合作院校失败", $_SESSION['admin_name'],3);
		adminmsg("添加失败！",0);
	}
	else
	{
		//保存院校图片
		if (!empty($_FILES['image']))
		{
			foreach ($_FILES['image']['name'] as $key => $value) 
			{
				$img_path='';
				$name=explode('.',$value);//将文件名以'.'分割得到后缀名,得到一个数组
				$img_path=$campus_img_updir.date("Y/m/d/");
				make_dir($img_path);
				$qid=uniqid();
				$img_path = $img_path.$qid.".".$name[1];
				if(move_uploaded_file($_FILES['image']['tmp_name'][$key], $img_path))
				{
					$imgarr=array();
					$imgarr['img']=date("Y/m/d/").$qid.".".$name[1];
					$imgarr['addtime'] = $timestamp;
					$imgarr['campus_id'] = $ad_id;
					$db->inserttable(table('cooperate_campus_img'),$imgarr);
				}
			}
		}
		else
		{
			adminmsg('请上传风采图片！',1);
		}
		//填写管理员日志
		write_log("后台成功添加合作院校", $_SESSION['admin_name'],3);
		baidu_submiturl(url_rewrite('QS_campusshow',array('id'=>$ad_id)),'addcampus');
		adminmsg("添加成功！",2,$link);
	}
}
//删除风采图片
elseif($act == 'del_img')
{
	$id = !empty($_GET['id'])?intval($_GET['id']):adminmsg("风采图片id丢失！",0);
	$campus_id = !empty($_GET['c_id'])?intval($_GET['c_id']):adminmsg("院校id丢失！",0);
	$link[0]['text'] = "继续删除";
	$link[0]['href'] ="?act=edit_campus&id={$campus_id}";
	$link[1]['text'] = "返回院校列表";
	$link[1]['href'] ="?act=campus_list";
	if(del_campus_img($id,$campus_id))
	{
		adminmsg("删除成功！",2,$link);
	}
	else
	{
		adminmsg("删除失败！",0,$link);
	}
}
//修改院校
elseif($act == 'edit_campus')
{
	get_token();
	$id=!empty($_GET['id'])?intval($_GET['id']):adminmsg('院校id丢失！',1);
	$campus = get_campus_one($id);
	//风采图片
	$campus_img = get_campus_img($id);
	$smarty->assign('rand',rand(1,100));
	$smarty->assign('campus_img',$campus_img);
	$smarty->assign('campus',$campus);
	$smarty->assign('campus_img_dir',$campus_img_dir);
	$smarty->assign('campus_dir',$campus_dir);
	$smarty->assign('pageheader',"合作院校");
	$smarty->display('campus/admin_cooperate_campus_edit.htm');
}
//保存:修改院校
elseif($act == 'edit_campus_save')
{
	check_token();
	$id=intval($_POST['id']);
	$setsqlarr['campusname']=trim($_POST['campusname'])?trim($_POST['campusname']):adminmsg('您没有填写院校名称！',1);
	$setsqlarr['website']=trim($_POST['website'])?trim($_POST['website']):adminmsg('您没有填写院校主页！',1);
	$setsqlarr['address']=trim($_POST['address'])?trim($_POST['address']):adminmsg('您没有填写院校地址！',1);
	$setsqlarr['contents']=trim($_POST['contents'])?trim($_POST['contents']):adminmsg('您没有填写院校简介！',1);
	//LOGO
	if (!empty($_FILES['logo']['name']))
	{
		$campus_updir=$campus_updir.date("Y/m/d/");
		make_dir($campus_updir);
		$setsqlarr['logo']=_asUpFiles($campus_updir,"logo",1000,'gif/jpg/bmp/png',true);
		if (empty($setsqlarr['logo']))
		{
			adminmsg('上传文件失败！',1);
		}
		$setsqlarr['logo']=date("Y/m/d/").$setsqlarr['logo'];
	}
	//保存院校图片
	foreach ($_FILES['image']['name'] as $key => $value) 
	{
		if(empty($value))
		{
			continue;
		}
		$img_path='';
		$name=explode('.',$value);//将文件名以'.'分割得到后缀名,得到一个数组
		$img_path=$campus_img_updir.date("Y/m/d/");
		make_dir($img_path);
		$qid=uniqid();
		$img_path = $img_path.$qid.".".$name[1];
		if(move_uploaded_file($_FILES['image']['tmp_name'][$key], $img_path))
		{
			$imgarr=array();
			$imgarr['img']=date("Y/m/d/").$qid.".".$name[1];
			$imgarr['addtime'] = $timestamp;
			$imgarr['campus_id'] = $id;
			$db->inserttable(table('cooperate_campus_img'),$imgarr);
		}
	}

	$wheresql=" id='".$id."' ";
	if(!$db->updatetable(table('cooperate_campus'),$setsqlarr,$wheresql))
	{
		//填写管理员日志
		write_log("后台修改合作院校失败", $_SESSION['admin_name'],3);
		adminmsg("修改失败！",0);
	}
	else
	{
		$link[0]['text'] = "返回院校列表";
		$link[0]['href'] = "?act=campus_list";
		//填写管理员日志
		write_log("后台成功修改合作院校", $_SESSION['admin_name'],3);
		adminmsg("修改成功！",2,$link);
	}
}
//删除合作院校
elseif($act=='del_campus')
{
	$id=$_REQUEST['id'];
	check_token();
	if (empty($id)) adminmsg("请选择项目！",0);
	if ($num=del_campus($id))
	{
		adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
		adminmsg("删除失败！".$num,1);
	}
}
?>