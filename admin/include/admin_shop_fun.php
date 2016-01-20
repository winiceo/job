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
function get_shop_category($pid='0')
{
	global $db;
	$pid=intval($pid);
	$sql = "select * from ".table('shop_category')." where parentid={$pid}  order BY category_order desc,id asc";
	return   $db->getall($sql);
}
function get_shop_category_one($id)
{
	global $db;
	$sql = "select * from ".table('shop_category')." WHERE id=".intval($id)." LIMIT 1";
	return $db->getone($sql);
}
function del_shop_category($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('shop_category')." WHERE id IN (".$sqlin.") ")) return false;
		$return=$return+$db->affected_rows();
		if (!$db->query("Delete from ".table('shop_category')." WHERE parentid IN (".$sqlin.") ")) return false;
		$return=$return+$db->affected_rows();
	}
	return $return;
}
// 获取商品
function get_shop($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('shop_goods').$sql.$limit);
	return $rows;
}
function get_shop_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('shop_goods')." where id=$id limit 1 ");
}
function del_shop($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('shop_goods')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}
function get_order($offset, $perpage, $sql= '')
{
	global $db,$_CFG;
	$limit=" LIMIT ".$offset.','.$perpage;
	$rows = $db->getall("SELECT * FROM ".table('shop_order').$sql.$limit);
	return $rows;
}
function get_order_one($id)
{
	global $db,$_CFG;
	$id=intval($id);
	return $db->getone("SELECT * FROM ".table('shop_order')." where id=$id limit 1");
}

// 操作积分
function report_deal($uid,$i_type=1,$points=0)
{
	global $db,$timestamp;
	$points=intval($points);
	$uid=intval($uid);
	$points_val=get_user_points($uid);
	if ($i_type==1)
	{
	$points_val=$points_val+$points;
	}
	if ($i_type==2)
	{
	$points_val=$points_val-$points;
	$points_val=$points_val<0?0:$points_val;
	}
	$sql = "UPDATE ".table('members_points')." SET points= '{$points_val}' WHERE uid='{$uid}' LIMIT 1";
	if (!$db->query($sql))return false;
	return true;
}
function get_user_points($uid)
{
	global $db;
	$uid=intval($uid);
	$points=$db->getone("select points from ".table('members_points')." where uid ='{$uid}' LIMIT 1");
	return $points['points'];
}
/* 热门关键字部分 */
function get_hotword($offset, $perpage, $wheresql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	return $db->getall("SELECT * FROM ".table('shop_hotword').$wheresql.$limit);
}
function get_hotword_one($id)
{
	global $db;
	$sql = "select * from ".table('shop_hotword')." where id=".intval($id)." LIMIT 1";
	return $db->getone($sql);
}
function get_hotword_obtainword($word)
{
	global $db;
	$sql = "select * from ".table('shop_hotword')." where w_word='".trim($word)."'  LIMIT 1";
	return $db->getone($sql);
}
function del_hottype($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('shop_hotword')." WHERE id IN (".$sqlin.") ")) return false;
		$return=$return+$db->affected_rows();
	}
	return $return;
}
// 订单设置 
function set_order($id,$state)
{
	global $db;
	$state=intval($state);
	if(!is_array($id)) $id=array($id);
	$return=0;
	foreach ($id as $value)
	{	
		$order_show=get_order_one($value);
		if($order_show['state']!=0)
		{
			continue;
		}
		$shop_one=get_shop_one($order_show['shop_id']);
		// 审核不通过 退回企业积分
		if($state==2)
		{
			$exchange_arr['state']=2;
			$db->updatetable(table("shop_exchange"),$exchange_arr,array("order_id"=>$order_show['id'],"shop_id"=>$order_show['shop_id'],"company_uid"=>$order_show['uid']));
			report_deal($order_show['uid'],1,$order_show['order_points']);
			//写入日志
			$user_points=get_user_points($order_show['uid']);
			write_memberslog($order_show['uid'],1,9001,$order_show['company_name'],$order_show['company_name']."积分兑换商品：<strong>{$setarr['shop_title']}</strong>未通过审核，并且返回积分：({$order_show['order_points']})。",1,2008,"返回积分","+{$order_show['order_points']}","{$user_points}");
		}
		// 审核通过 更新库存,兑换次数 写入兑换记录
		else
		{
			$shop_stock=$shop_one['shop_stock']-$order_show['shop_num'];
			$click=$shop_one['click']+$order_show['shop_num'];
			$db->query("update ".table("shop_goods")." set shop_stock=$shop_stock,click=$click where id={$order_show['shop_id']}");
			$exchange_arr['state']=1;
			$db->updatetable(table("shop_exchange"),$exchange_arr,array("order_id"=>$order_show['id'],"shop_id"=>$order_show['shop_id'],"company_uid"=>$order_show['uid']));
		}
		if($db->query("update ".table("shop_order")." set state=$state where id=$value"))
		{
			$return++;
		}
	}
	return $return;
}
// 删除订单
function order_del($id)
{
	global $db;
	if (!is_array($id))$id=array($id);
	$sqlin=implode(",",$id);
	$return=0;
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('shop_order')." WHERE id IN ({$sqlin})")) return false;
		$return=$return+$db->affected_rows();
		return $return;
	}
	else
	{
	return false;
	}
}
?>