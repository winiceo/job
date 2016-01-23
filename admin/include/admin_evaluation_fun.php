<?php
 /*
 * 74cms 管理中心 设置分类 数据调用函数
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
// 获取测评类型
function get_evaluation_category()
{
	global $db;
	$sql = "select * from ".table('evaluation_type')."  order BY id  asc";
	return   $db->getall($sql);
}
// 获取单一测评类型
function get_evaluation_category_one($id)
{
	global $db;
	$id=intval($id);
	$sql = "select * from ".table('evaluation_type')."  where id=$id limit 1 ";
	return   $db->getone($sql);
}
// 获取测评试卷
function get_evaluation_paper($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('evaluation_paper').$sql.$limit);
	foreach ($rows as $key => $value) 
	{
		$value['url']=url_rewrite('QS_paper_answer',array('id'=>$value['id']));
		$list[$key] = $value;
	}
	return $list;
}
// 获取单一试卷信息
function get_evaluation_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('evaluation_paper')." where id=$id limit 1 ");
}
// 删除试卷
function del_paper($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		//循环试卷 更新试卷相应类型的试卷数
		$sql = "SELECT *  FROM ".table('evaluation_paper')." WHERE id IN ({$sqlin})";
		$paper_info = $db->getall($sql);
		foreach ($paper_info as $key => $value) 
		{
			//更新试卷数  1=>加    2=>减
			set_paper_num('2',$value['type_id'],1);
		}
		if (!$db->query("Delete from ".table('evaluation_paper')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}
// 获取试卷问题列表
function get_paper_question($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('evaluation_question').$sql.$limit);
	return $rows;
}
// 获取试卷单一问题
function get_question_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('evaluation_question')." where id=$id limit 1 ");
}
// 更新试卷问题数
function set_paper_question_num($type, $paper_id, $num)
{
	global $db,$_CFG;
	$paper_id=intval($paper_id);
	if(empty($paper_id))
	{
		return false;
	}
	$num=intval($num);
	$paper_info = get_evaluation_one($paper_id);
	if(intval($type) == 1)
	{
		$num = $num + intval($paper_info['question_num']);
	}
	else
	{
		$num = intval($paper_info['question_num']) - $num;
	}
	$paperarr['question_num'] = $num;
	if(!$db->updatetable(table("evaluation_paper"),$paperarr,array("id"=>$paper_id)))
	{
		return false;
	}
	return true;
}
// 删除测题
function del_question($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('evaluation_question')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}
// 获取测题选项列表
function get_question_option($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('evaluation_option').$sql.$limit);
	return $rows;
}
// 获取试卷单一问题
function get_option_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('evaluation_option')." where id=$id limit 1 ");
}
// 删除选项
function del_option($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('evaluation_option')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}
// 更新试卷数
function set_paper_num($type, $type_id, $num)
{
	global $db,$_CFG;
	$type_id=intval($type_id);
	if(empty($type_id))
	{
		return false;
	}
	$num=intval($num);
	$sql = "select * from ".table('evaluation_type')." WHERE id=".$type_id." LIMIT 1 ";
	$eval_type = $db->getone($sql);
	if(intval($type) == 1)
	{
		$num = $num + intval($eval_type['num']);
	}
	else
	{
		$num = intval($eval_type['num']) - $num;
	}
	$paperarr['num'] = $num;
	if(!$db->updatetable(table("evaluation_type"),$paperarr,array("id"=>$type_id)))
	{
		return false;
	}
	return true;
}
// 删除测评记录
function del_record($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('evaluation_record')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}

// 获取试卷问题列表
function get_paper_result($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('evaluation_result').$sql.$limit);
	return $rows;
}
// 获取答案
function get_result_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('evaluation_result')." where id=$id limit 1 ");
}
// 删除答案
function del_result($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('evaluation_result')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
		return false;
	}
}
?>