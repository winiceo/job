<?php
 /*
 * 74cms �ƻ�����
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
function get_list($offset, $perpage, $get_sql= '')
{
	global $db;
	$row_arr = array();
	$limit=" LIMIT ".$offset.','.$perpage;
	$result = $db->query("SELECT * FROM ".table('relation').$get_sql.$limit);
	while($row = $db->fetch_array($result))
	{

	
	    $row_arr[] = $row;
	}
	return $row_arr;	
}
function del_replace($id)
{
	global $db;
	if(!is_array($id)) $id=array($id);
	$return=0;
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
		if (!$db->query("Delete from ".table('relation')." WHERE  id  IN (".$sqlin.")   ")) return false;
		$return=$return+$db->affected_rows();
	}
	return $return;
}
function get_replace_one($id)
{
	global $db;
	$sql = "select * from ".table('relation')." where id=".intval($id)."";
	return $db->getone($sql);
}
function get_title_list()
{
    global $db;
    $row_arr = array();
    $result = $db->query("SELECT id,name FROM ".table('relation')." where type=1 group by name");
    while($row = $db->fetch_array($result))
    {


        $row_arr[] = $row;
    }
    return $row_arr;
}
function get_source_list()
{
    global $db;
    $row_arr = array();
    $result = $db->query("SELECT id,source FROM ".table('relation')." where type=1 group by source ");
    while($row = $db->fetch_array($result))
    {


        $row_arr[] = $row;
    }
    return $row_arr;
}
?>