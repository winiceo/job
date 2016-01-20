<?php
 /*
 * 74cms 礼品卡
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI',true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
require_once(ADMIN_ROOT_PATH.'include/admin_shop_fun.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'list';
$smarty->assign('act',$act);
check_permissions($_SESSION['admin_purview'],"shop");
if($act == 'list')
{
	get_token();
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY id  DESC";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		
		if($key_type===1)$wheresql=" WHERE shop_title like '%{$key}%'";
		$oederbysql="";
	}
	else
	{
		$settr=intval($_GET['settr']);
		if ($settr>0)
		{
			$wheresql.=empty($wheresql)?" WHERE ":" AND  ";
			$days=intval($settr);
			$settr=strtotime("-{$days} day");
			$wheresql.=" addtime> {$settr} ";	
		}
	}

	$total_sql="SELECT COUNT(*) AS num FROM ".table('shop_goods').$wheresql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_shop($offset, $perpage,$wheresql.$oederbysql);
	$smarty->assign('list',$list);
	$smarty->assign('total',$total);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_shop_list.htm');
}
elseif($act == 'shop_add')
{
	get_token();
	$smarty->assign('pageheader',"积分商城 ");
	$smarty->display('shop/admin_shop_add.htm');
}
elseif($act == 'shop_edit')
{
	get_token();
	$id=intval($_GET['id']);
	$smarty->assign('pageheader',"积分商城 ");
	$smarty->assign('show',get_shop_one($id));
	$smarty->display('shop/admin_shop_add.htm');
}
elseif($act == 'shop_save')
{
	check_token();
	$id=intval($_POST['id']);
	$setarr["shop_title"]=$_POST["shop_title"]?trim($_POST["shop_title"]):adminmsg("请输入商品名称");
	$setarr["shop_brand"]=$_POST["shop_brand"]?trim($_POST["shop_brand"]):adminmsg("请输入品牌");
	$setarr["scategory"]=$_POST["scategory"]?trim($_POST["scategory"]):adminmsg("请选择所属分类");
	$scategory_arr=explode(",", $setarr["scategory"]);
	$setarr["category"]=$scategory_arr[0];
	$setarr["scategory"]=$scategory_arr[1];
	$setarr["category_cn"]=$scategory_arr[2];

	$setarr["shop_stock"]=$_POST["shop_stock"]?intval($_POST["shop_stock"]):adminmsg("请输入商品库存");
	$setarr["shop_customer"]=intval($_POST["shop_customer"]);
	$setarr["shop_points"]=$_POST["shop_points"]?intval($_POST["shop_points"]):adminmsg("请输入商品兑换所需积分");
	$setarr["content"]=$_POST["content"]?trim($_POST["content"]):adminmsg("请输入商品描述");
	$setarr["recommend"]=intval($_POST["recommend"]);
	if($_FILES['shop_img']['name'])
	{
		require_once(QISHI_ROOT_PATH.'include/upload.php');
		$upfiles_dir="../data/shop/".date("Y/m/d/");
		make_dir($upfiles_dir);
		$shop_img=_asUpFiles($upfiles_dir, "shop_img", 1024*2, 'jpg/gif/png',true);
		$makefile=$upfiles_dir.$shop_img;
		$thumb_dir="../data/shop/thumb/".date("Y/m/d/");
		make_dir($thumb_dir);
		makethumb($makefile,$thumb_dir,288,288,1);
		$setarr['shop_img']=date("Y/m/d/").$shop_img;
	}
	if($id>0)
	{
		write_log("后台修改商品信息", $_SESSION['admin_name'],3);
		!$db->updatetable(table("shop_goods"),$setarr,array("id"=>$id))?adminmsg("修改失败！"):adminmsg("修改成功！",2);
	}
	else
	{
		$setarr['addtime']=time();
		$setarr['shop_number']=time().rand(1000,9999);
		write_log("后台添加商品信息", $_SESSION['admin_name'],3);
		!$db->inserttable(table("shop_goods"),$setarr)?adminmsg("添加失败！"):adminmsg("添加成功！",2);	
	}
}
elseif($act=="shop_del")
{
	$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择商品！",1);
	$num=del_shop($id);
	if ($num>0)
	{
	write_log("后台删除商品,共删除".$return."行", $_SESSION['admin_name'],3);
	adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
	adminmsg("删除失败！",0);
	}
}
/*
	商品分类
*/
elseif($act == 'category')
{
	get_token();
	$smarty->assign('pageheader',"积分商城-商品分类");
	$smarty->assign('category',get_shop_category());
	$smarty->display('shop/admin_shop_category.htm');
}
elseif($act == 'category_all_save')
{
	check_token();
	if (is_array($_POST['save_id']) && count($_POST['save_id'])>0)
	{
		foreach($_POST['save_id'] as $k=>$v)
		{
		 
				$setsqlarr['categoryname']=trim($_POST['categoryname'][$k]);
				$setsqlarr['category_order']=intval($_POST['category_order'][$k]);
				!$db->updatetable(table('shop_category'),$setsqlarr," id=".intval($_POST['save_id'][$k]))?adminmsg("保存失败！",0):"";
				$num=$num+$db->affected_rows();
 
		}
		//填写管理员日志
		write_log("后台成功更新商品分类 , 共更新".$num."个", $_SESSION['admin_name'],3);
	}
	//新增的入库
	if (is_array($_POST['add_pid']) && count($_POST['add_pid'])>0)
	{
		for ($i =0; $i <count($_POST['add_pid']);$i++){
			if (!empty($_POST['add_categoryname'][$i]))
			{	
				$setsqlarr['categoryname']=trim($_POST['add_categoryname'][$i]);
				$setsqlarr['category_order']=intval($_POST['add_category_order'][$i]);
				$setsqlarr['parentid']=intval($_POST['add_pid'][$i]);	
				!$db->inserttable(table('shop_category'),$setsqlarr)?adminmsg("保存失败！",0):"";
				$num=$num+$db->affected_rows();
			}

		}
		//填写管理员日志
		write_log("后台成功添加商品分类 , 共添加".$num."个", $_SESSION['admin_name'],3);
	}
	makejs_classify();
	makejs_train_classify();
	adminmsg("保存成功！",2);
}
elseif($act == 'del_category')
{
	check_token();
	$id=$_REQUEST['id'];
	if ($num=del_shop_category($id))
	{
	makejs_classify();
	//填写管理员日志
	write_log("后台成功删除商品分类！共删除".$num."行", $_SESSION['admin_name'],3);
	adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
	adminmsg("删除失败！",1);
	}
}
elseif($act == 'edit_category')
{
	get_token();
	$smarty->assign('pageheader',"积分商城-商品分类");
	$smarty->assign('category',get_shop_category_one($_GET['id']));
	$smarty->display('shop/admin_shop_category_edit.htm');
}
elseif($act == 'edit_category_save')
{
	check_token();
	$setsqlarr['categoryname']=!empty($_POST['categoryname']) ?trim($_POST['categoryname']) : adminmsg("请填写名称",1);
	$setsqlarr['category_order']=intval($_POST['category_order']);
	$setsqlarr['parentid']=intval($_POST['parentid']);				
	!$db->updatetable(table('shop_category'),$setsqlarr," id=".intval($_POST['id']))?adminmsg("修改失败！",0):"";
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=category';
	makejs_classify();
	makejs_train_classify();
	//填写管理员日志
	write_log("后台成功修改商品分类！", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2,$link);
}
elseif($act == 'add_category')
{
	get_token();
	$smarty->assign('pageheader',"积分商城-商品分类");
	$smarty->display('shop/admin_shop_category_add.htm');
}
elseif($act == 'add_category_save')
{
	check_token();
	//新增的入库
	if (is_array($_POST['categoryname']) && count($_POST['categoryname'])>0)
	{
		for ($i =0; $i <count($_POST['categoryname']);$i++){
			if (!empty($_POST['categoryname'][$i]))
			{	
				$setsqlarr['categoryname']=trim($_POST['categoryname'][$i]);
				$setsqlarr['category_order']=intval($_POST['category_order'][$i]);
				$setsqlarr['parentid']=intval($_POST['parentid'][$i]);	
				!$db->inserttable(table('shop_category'),$setsqlarr)?adminmsg("保存失败！",0):"";
				$num=$num+$db->affected_rows();
			}

		}
	}
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=category';
	makejs_classify();
	makejs_train_classify();
	//填写管理员日志
	write_log("后台成功添加商品分类！本次添加了{$num}个分类", $_SESSION['admin_name'],3);
	adminmsg("添加成功！本次添加了{$num}个分类",2,$link);	
}
elseif($act == 'order')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY id  DESC";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		
		if($key_type===1)$wheresql=" WHERE shop_title like '%{$key}%'";
		$oederbysql="";
	}
	else
	{
		$settr=intval($_GET['settr']);
		if ($settr>0)
		{
			$wheresql.=empty($wheresql)?" WHERE ":" AND  ";
			$days=intval($settr);
			$settr=strtotime("-{$days} day");
			$wheresql.=" addtime> {$settr} ";	
		}
		$state=intval($_GET['state']);
		if($state>0)
		{
			$state=$state-1;
			$wheresql.=empty($wheresql)?" WHERE ":" AND  ";
			$wheresql.=" state={$state} ";	
		}
		
	}

	$total_sql="SELECT COUNT(*) AS num FROM ".table('shop_order').$wheresql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_order($offset, $perpage,$wheresql.$oederbysql);
	$smarty->assign('list',$list);
	$smarty->assign('total',$total);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_shop_order.htm');
}
elseif($act =='order_show')
{
	$id=intval($_GET['id']);
	$show=get_order_one($id);
	$smarty->assign('show',$show);
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_shop_order_show.htm');
}
elseif($act == "order_del")
{
	$id=$_REQUEST['id']?$_REQUEST['id']:adminmsg("请选择订单",1);
	$num= order_del($id);
	if($num>0)
	{
		adminmsg("删除订单成功!",2);
	}else
	{
		adminmsg("删除订单失败",1);
	}
}	
// 设置订单状态
elseif($act=='set_order')
{
	$id=$_REQUEST['id']?$_REQUEST['id']:adminmsg("请选择订单",1);
	$state=$_GET['state']?intval($_GET['state']):intval($_POST['state']);
	$num = set_order($id,$state);
	if($num>0)
	{
		adminmsg("操作成功!",2);
	}else
	{
		adminmsg("操作失败！",1);
	}
}
// 热门关键字
elseif($act == "hotword")
{
	get_token();
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY w_hot DESC ";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	if ($key)
	{
		$wheresql=" WHERE w_word like '%{$key}%'";
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('shop_hotword')." ".$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql),'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$hotword = get_hotword($offset, $perpage,$wheresql.$oederbysql);	
	$smarty->assign('hotword',$hotword);
	$smarty->assign('navlabel',"list");	
	$smarty->assign('page',$page->show(3));	
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_hotword_list.htm');
}
elseif($act == 'add')
{
	get_token();
	$smarty->assign('navlabel',"add");	
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_hotword_add.htm');
}
elseif($act == 'addsave')
{
	check_token();
	$setsqlarr['w_word']=trim($_POST['w_word'])?trim($_POST['w_word']):adminmsg('关键词必须填写！',1);
	$setsqlarr['w_hot']=intval($_POST['w_hot']);
	if (get_hotword_obtainword($setsqlarr['w_word']))
	{
	adminmsg("关键词已经存在！",0);
	}
	$link[0]['text'] = "继续添加";
	$link[0]['href'] = '?act=add&w_type='.$setsqlarr['w_type'];
	$link[1]['text'] = "返回列表";
	$link[1]['href'] = '?act=hotword';
	write_log("添加热门关键字", $_SESSION['admin_name'],3);
	!$db->inserttable(table('shop_hotword'),$setsqlarr)?adminmsg("添加失败！",0):adminmsg("添加成功！",2,$link);
}
elseif($act == 'edit')
{
	get_token();
	$smarty->assign('hotword',get_hotword_one($_GET['id']));
	$smarty->assign('pageheader',"积分商城");
	$smarty->display('shop/admin_hotword_edit.htm');
}
elseif($act == 'editsave')
{
	check_token();
	$id = !empty($_POST['id']) ? intval($_POST['id']) : adminmsg('参数错误',1);
	$setsqlarr['w_word']=trim($_POST['w_word'])?trim($_POST['w_word']):adminmsg('关键词必须填写！',1);
	$setsqlarr['w_hot']=intval($_POST['w_hot']);
	$word=get_hotword_obtainword($setsqlarr['w_word']);
	if ($word['id'] && $word['id']<>$id)
	{
	adminmsg("关键词已经存在！",0);
	}
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=hotword';
	write_log("修改热门关键字", $_SESSION['admin_name'],3);
 	!$db->updatetable(table('shop_hotword'),$setsqlarr," id=".$id."")?adminmsg("修改失败！",0):adminmsg("修改成功！",2,$link);
}
elseif($act == 'hottype_del')
{
	check_token();
	$id=$_REQUEST['id'];
	if ($num=del_hottype($id))
	{
	write_log("删除热门关键字,共删除 {$num} 行", $_SESSION['admin_name'],3);
	adminmsg("删除成功！共删除 {$num} 行",2);
	}
	else
	{
	adminmsg("删除失败！",0);
	}
}
?>