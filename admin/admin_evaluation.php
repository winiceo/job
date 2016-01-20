<?php
 /*
 * 74cms 人才测评模块
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
require_once(ADMIN_ROOT_PATH.'include/admin_evaluation_fun.php');
require_once(ADMIN_ROOT_PATH.'include/upload.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'list';
$smarty->assign('act',$act);
check_permissions($_SESSION['admin_purview'],"set_evaluation");
//试卷列表
if($act == 'list')
{
	get_token();
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY id  DESC";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		if($key_type===1)$wheresql=" WHERE title like '%{$key}%'";
		$oederbysql="";
	}
	else
	{
		//测评类型
		$type_id=intval($_GET['type_id']);
		if ($type_id>0)
		{
			$wheresql.=empty($wheresql)?" WHERE ":" AND  ";
			$wheresql.=" type_id = {$type_id} ";	
		}
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('evaluation_paper').$wheresql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_evaluation_paper($offset, $perpage,$wheresql.$oederbysql);
	$smarty->assign('list',$list);
	$smarty->assign('total',$total);
	$smarty->assign('page',$page->show(3));
	//测评类型
	$evaluat_type = get_evaluation_category();
	$smarty->assign('evaluat_type',$evaluat_type);
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_evaluation_list.htm');
}
//测评类型
elseif($act == 'type_list')
{
	get_token();
	//测评类型
	$evaluat_type = get_evaluation_category();
	$smarty->assign('evaluat_type',$evaluat_type);
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_evaluation_type_list.htm');
}
// 修改测评类型
elseif($act == 'type_edit')
{
	get_token();
	$id=intval($_GET['id']);
	$smarty->assign('pageheader',"人才测评 ");
	$smarty->assign('show',get_evaluation_category_one($id));
	$eval_dir=$_CFG['site_dir']."data/eval/logo/";
	$smarty->assign('eval_dir',$eval_dir);
	$smarty->display('evaluation/admin_evaluation_type_edit.htm');
}
// 保存测评类型
elseif($act == 'save_type')
{
	check_token();
	$id=intval($_POST['id']);
	//LOGO
	if (!empty($_FILES['logo']['name']))
	{
		$eval_updir="../data/eval/logo/".date("Y/m/d/");
		make_dir($eval_updir);
		$setarr['logo']=_asUpFiles($eval_updir,"logo",1000,'gif/jpg/bmp/png',true);
		if (empty($setarr['logo']))
		{
			adminmsg('上传文件失败！',1);
		}
		$setarr['logo']=date("Y/m/d/").$setarr['logo'];
	}
	$setarr["suitable_crowd"]=$_POST["suitable_crowd"]?trim($_POST["suitable_crowd"]):adminmsg("请输入适用人群！");
	$setarr["description"]=$_POST["description"]?trim($_POST["description"]):adminmsg("请输入类型描述！");
	//修改
	$link[0]['text'] = "返回测试类型列表";
	$link[0]['href'] = '?act=type_list';
	!$db->updatetable(table("evaluation_type"),$setarr,array("id"=>$id))?adminmsg("修改失败！"):adminmsg("修改成功！",2,$link);	
}
// 添加试卷
elseif($act == 'paper_add')
{
	get_token();
	//测评类型
	$evaluat_type = get_evaluation_category();
	$smarty->assign('evaluat_type',$evaluat_type);
	$smarty->assign('pageheader',"人才测评 ");
	$smarty->display('evaluation/admin_evaluation_add.htm');
}
// 修改试卷
elseif($act == 'paper_edit')
{
	get_token();
	$id=intval($_GET['id']);
	$smarty->assign('pageheader',"人才测评 ");
	$smarty->assign('show',get_evaluation_one($id));
	//测评类型
	$evaluat_type = get_evaluation_category();
	$smarty->assign('evaluat_type',$evaluat_type);
	$eval_dir=$_CFG['site_dir']."data/eval/img/";
	$smarty->assign('eval_dir',$eval_dir);
	$smarty->display('evaluation/admin_evaluation_add.htm');
}
// 保存试卷
elseif($act == 'paper_save')
{
	check_token();
	$id=intval($_POST['id']);
	$setarr["title"]=$_POST["title"]?trim($_POST["title"]):adminmsg("请输入试卷标题");
	$setarr["type_id"]=$_POST["type_id"]?intval($_POST["type_id"]):adminmsg("请选择所属测评类型");
	$setarr["timelimit"]=$_POST["timelimit"]?intval($_POST["timelimit"]):adminmsg("请输入答题时长");
	$setarr["price"]=$_POST["price"]?intval($_POST["price"]):0;
	$setarr["keywords"]=$_POST["keywords"]?trim($_POST["keywords"]):adminmsg("请输入关键词");
	$setarr["suitable_crowd"]=$_POST["suitable_crowd"]?trim($_POST["suitable_crowd"]):adminmsg("请输使用人群");
	$setarr["description"]=$_POST["description"]?trim($_POST["description"]):adminmsg("请输入试卷描述");
	$setarr["explain"]=$_POST["explain"]?trim($_POST["explain"]):adminmsg("请输入得分说明");
	//修改
	if($id>0)
	{
		//LOGO
		if (!empty($_FILES['img']['name']))
		{
			$eval_updir="../data/eval/img/".date("Y/m/d/");
			make_dir($eval_updir);
			$setarr['img']=_asUpFiles($eval_updir,"img",1000,'gif/jpg/bmp/png',true);
			if (empty($setarr['img']))
			{
				adminmsg('上传文件失败！',1);
			}
			$setarr['img']=date("Y/m/d/").$setarr['img'];
		}
		write_log("后台修改试卷信息", $_SESSION['admin_name'],3);
		!$db->updatetable(table("evaluation_paper"),$setarr,array("id"=>$id))?adminmsg("修改失败！"):adminmsg("修改成功！",2);
	}
	//添加
	else
	{
		//LOGO
		if (empty($_FILES['img']['name']))
		{
			adminmsg('请上传图片！',1);
		}
		else
		{
			$eval_updir="../data/eval/img/".date("Y/m/d/");
			make_dir($eval_updir);
			$setarr['img']=_asUpFiles($eval_updir,"img",1000,'gif/jpg/bmp/png',true);
			if (empty($setarr['img']))
			{
				adminmsg('上传文件失败！',1);
			}
			$setarr['img']=date("Y/m/d/").$setarr['img'];
		}
		$link[0]['text'] = "返回列表";
		$link[0]['href'] = '?act=list';
		write_log("后台添加试卷信息", $_SESSION['admin_name'],3);
		//更新试卷数  1=>加    2=>减
		set_paper_num('1',$setarr["type_id"],1);
		!$db->inserttable(table("evaluation_paper"),$setarr)?adminmsg("添加失败！"):adminmsg("添加成功！",2,$link);	
	}
}
// 删除试卷
elseif($act=="paper_del")
{
	$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择试卷！",1);
	$num=del_paper($id);
	if ($num>0)
	{
	write_log("后台删除试卷,共删除".$return."行", $_SESSION['admin_name'],3);
	adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
	adminmsg("删除失败！",0);
	}
}
// 试卷问题列表
elseif($act == 'question_list')
{
	get_token();
	// 试卷id 
	$paper_id=intval($_GET['id']);
	if($paper_id <= 0)
	{
		adminmsg("试卷编号丢失！",0);
	}
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$wheresql=" WHERE paper_id=".$paper_id;
	$oederbysql=" order BY id  DESC";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		if($key_type===1)$wheresql.=" AND title like '%{$key}%'";
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('evaluation_question').$wheresql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_paper_question($offset, $perpage,$wheresql.$oederbysql);
	$smarty->assign('list',$list);
	$paper_info = get_evaluation_one($paper_id);
	$smarty->assign('paper_info',$paper_info);
	$smarty->assign('total',$total);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_question_list.htm');
}
// 新增测题
elseif($act == 'question_add')
{
	get_token();
	// 试卷id 
	$paper_id=intval($_GET['paper_id']);
	$smarty->assign('paper_id',$paper_id);
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_question_add.htm');
}
// 保存 新增测题
elseif($act == 'add_question_save')
{
	check_token();
	$paper_id=intval($_POST['paper_id']);
	if(empty($paper_id))
	{
		adminmsg("试卷信息丢失！");
	}
	//新增的入库
	if (is_array($_POST['title']) && count($_POST['title'])>0)
	{
		for ($i =0; $i <count($_POST['title']);$i++){
			if (!empty($_POST['title'][$i]))
			{	
				$setsqlarr['title']=trim($_POST['title'][$i]);
				$setsqlarr['paper_id']=$paper_id;
				!$db->inserttable(table('evaluation_question'),$setsqlarr)?adminmsg("保存失败！",0):"";
				$num=$num+$db->affected_rows();
			}
		}
	}
	$link[0]['text'] = "返回试卷问题列表";
	$link[0]['href'] = '?act=question_list&id='.$paper_id;
	//更新试卷题目数  1=>加    2=>减
	set_paper_question_num('1',$paper_id,$num);
	//填写管理员日志
	write_log("后台成功添加试卷测题！本次添加了".$num."个测题", $_SESSION['admin_name'],3);
	adminmsg("添加成功！本次添加了".$num."个测题",2,$link);	
}
// 修改测题
elseif($act == 'question_edit')
{
	get_token();
	$smarty->assign('pageheader',"人才测评");
	$smarty->assign('question',get_question_one($_GET['id']));
	$smarty->display('evaluation/admin_question_edit.htm');
}
// 修改保存测题
elseif($act == 'edit_question_save')
{
	check_token();
	$setsqlarr['title']=!empty($_POST['title']) ?trim($_POST['title']) : adminmsg("请填写题目",1);
	!$db->updatetable(table('evaluation_question'),$setsqlarr," id=".intval($_POST['id']))?adminmsg("修改失败！",0):"";
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=question_list&id='.$_POST['paper_id'];
	//填写管理员日志
	write_log("后台成功修改试卷测题！", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2,$link);
}
// 删除测题
elseif($act=="question_del")
{
	$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择测题！",1);
	$paper_id = $_REQUEST['paper_id'];
	$num=del_question($id);
	if ($num>0)
	{
		//更新试卷题目数  1=>加    2=>减
		set_paper_question_num('2',$paper_id,$num);
		write_log("后台删除测题,共删除".$return."行", $_SESSION['admin_name'],3);
		adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
		adminmsg("删除失败！",0);
	}
}
// 问题选项列表
elseif($act == 'option_list')
{
	get_token();
	// 测题id 
	$question_id=intval($_GET['id']);
	if($question_id <= 0)
	{
		adminmsg("测题编号丢失！",0);
	}
	$question_info = get_question_one($question_id);
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$wheresql=" WHERE question_id=".$question_id." AND paper_id=".$question_info['paper_id'];
	$oederbysql=" order BY id  DESC";
	$total_sql="SELECT COUNT(*) AS num FROM ".table('evaluation_option').$wheresql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_question_option($offset, $perpage,$wheresql.$oederbysql);
	$smarty->assign('list',$list);
	$question_info = get_question_one($question_id);
	$smarty->assign('question_info',$question_info);
	$smarty->assign('total',$total);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_option_list.htm');
}
// 新增选项
elseif($act == 'option_add')
{
	get_token();
	// 测题id 
	$question_id=intval($_GET['question_id']);
	$smarty->assign('question_id',$question_id);
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_option_add.htm');
}
// 保存选项
elseif($act == 'add_option_save')
{
	check_token();
	$question_id=intval($_POST['question_id']);
	if(empty($question_id))
	{
		adminmsg("测题信息丢失！");
	}
	$question_info = get_question_one($question_id);
	if (is_array($_POST['name']) && count($_POST['name'])>0)
	{
		for ($i =0; $i <count($_POST['name']);$i++){
			if (!empty($_POST['name'][$i]))
			{	
				$setsqlarr['question_id']=$question_id;
				$setsqlarr['name']=trim($_POST['name'][$i]);
				$setsqlarr['score']=intval($_POST['score'][$i]);
				$setsqlarr['paper_id']=$question_info['paper_id'];
				!$db->inserttable(table('evaluation_option'),$setsqlarr)?adminmsg("保存失败！",0):"";
				$num=$num+$db->affected_rows();
			}
		}
	}
	$link[0]['text'] = "返回选项列表";
	$link[0]['href'] = '?act=option_list&id='.$question_id;
	//填写管理员日志
	write_log("后台成功添加测题选项！本次添加了".$num."个选项", $_SESSION['admin_name'],3);
	adminmsg("添加成功！本次添加了".$num."个选项",2,$link);	
}
// 修改选项
elseif($act == 'option_edit')
{
	get_token();
	$smarty->assign('pageheader',"人才测评");
	$smarty->assign('option',get_option_one($_GET['id']));
	$smarty->display('evaluation/admin_option_edit.htm');
}
// 修改保存选项
elseif($act == 'edit_option_save')
{
	check_token();
	$setsqlarr['name']=!empty($_POST['name']) ?trim($_POST['name']) : adminmsg("请填写选项内容",1);
	$setsqlarr['score']=!empty($_POST['score']) ?trim($_POST['score']) : adminmsg("请填写选项分值",1);
	!$db->updatetable(table('evaluation_option'),$setsqlarr," id=".intval($_POST['id']))?adminmsg("修改失败！",0):"";
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = '?act=option_list&id='.$_POST['question_id'];
	//填写管理员日志
	write_log("后台成功修改测题选项！", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2,$link);
}
// 删除选项
elseif($act=="option_del")
{
	$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择选项！",1);
	$question_id = $_REQUEST['question_id'];
	$num=del_option($id);
	if ($num>0)
	{
		write_log("后台删除测题选项,共删除".$return."行", $_SESSION['admin_name'],3);
		adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
		adminmsg("删除失败！",0);
	}
}
// 测评记录
elseif($act=="record_list")
{
	//测评类型
	$evaluat_type = get_evaluation_category();
	$smarty->assign('evaluat_type',$evaluat_type);
	
	$typeid=intval($_GET['typeid']) ? intval($_GET['typeid']) : 0;
	if($typeid > 0)
	{
		$recordsql .= " AND type_id=".$typeid;
		$wheresql = " where type_id=".$typeid;
	}
	$papertype = $db->getall("select id,title from ".table('evaluation_paper').$wheresql);
	$smarty->assign('papertype',$papertype);
	// 筛选条件
	$paperid=intval($_GET['paperid']) ? intval($_GET['paperid']) : 0;
	if($paperid > 0)
	{
		$recordsql .= " AND paper_id=".$paperid;
	}
	//测评时间
	$addsettr=intval($_GET['addsettr']) ? intval($_GET['addsettr']) : 0;
	if($addsettr > 0)
	{
		$settr=strtotime("-".intval($_GET['addsettr'])." day");
		$recordsql .= " AND addtime>".$settr;
	}
	if (!empty($recordsql))
	{
		$recordsql=" WHERE ".ltrim(ltrim($recordsql),'AND');
	}
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if (!empty($key) && $key_type>0)
	{
		if     ($key_type===1)$recordsql=" WHERE paper_title like '%{$key}%'";
	}
	$oederbysql=" order BY addtime  DESC";
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$total_sql="SELECT COUNT(*) AS num FROM ".table('evaluation_record').$recordsql;
	$total=$db->get_total($total_sql);
	$page = new page(array('total'=>$total, 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$record_sql = "select r.*,m.username as username,t.name as type_name from ".table('evaluation_record')." as r  LEFT JOIN  ".table('members')." AS m  ON r.uid=m.uid  LEFT JOIN  ".table('evaluation_type')." AS t  ON r.type_id=t.id  ".$recordsql.$oederbysql;
	$record = $db->getall($record_sql);
	$smarty->assign('record',$record);
	$smarty->assign('total',intval($total));
	$smarty->assign('page',$page->show(3));
	$smarty->assign('pageheader',"人才测评");
	$smarty->display('evaluation/admin_record_list.htm');
}
// 删除测评记录
elseif($act=="record_del")
{
	$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择选项！",1);
	$num=del_record($id);
	if ($num>0)
	{
		write_log("后台删除测评记录,共删除".$return."行", $_SESSION['admin_name'],3);
		adminmsg("删除成功！共删除".$num."行",2);
	}
	else
	{
		adminmsg("删除失败！",0);
	}
}
?>