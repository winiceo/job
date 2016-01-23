<?php
 /*
 * 74cms 个人测评删除记录ajax
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
$act =$_GET['act']?trim($_GET['act']):"del_record";
require_once(QISHI_ROOT_PATH.'genv/lib.php');

// 删除记录
if($act == 'del_record')
{
	require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
	$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
	//记录id
	$id=intval($_GET['id']);
	if ($db->query("Delete from ".table('evaluation_record')." WHERE id='{$id}'  "))
	{
		exit('1');
	}
	else
	{
		exit('删除失败！');
	}
}
// 下一页或者是提交
elseif($act == 'next_page')
{
	require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
	$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
	//试卷id
	$id=$_REQUEST['id']?intval($_REQUEST['id']):0;
	//试卷信息
	$peper_sql = "SELECT * FROM ".table('evaluation_paper')." WHERE id=".$id;
	$paper_info = $db->getone($peper_sql);
	$smarty->assign('paper_info',$paper_info);
	//测题信息
	$perpage = 10;
	$total_sql="SELECT COUNT(*) AS num from ".table('evaluation_question')." WHERE paper_id=".$id;
	$total_val=$db->get_total($total_sql);
	$page_num = ceil($total_val/$perpage);

	//点击上一页
	if($_REQUEST['pre_page'])
	{
		$page=$_REQUEST['page']?intval($_REQUEST['page'])-1:2;
		$offset=($page-1)*$perpage;
		$smarty->assign('page',$page);
		$smarty->assign('next_page',1);
		if($page != 1)
		{
			$smarty->assign('pre_page',1);
		}
	}
	else
	{
		$page=$_REQUEST['page']?intval($_REQUEST['page'])+1:2;
		if($page != $page_num)
		{
			$smarty->assign('next_page',1);
		}
		$smarty->assign('pre_page',1);
		$offset=($page-1)*$perpage;
		$smarty->assign('page',$page);
	}
	$limit = " LIMIT ".intval($offset).','.intval($perpage);
	$question_sql = "SELECT * FROM ".table('evaluation_question')." WHERE paper_id=".$id." ORDER BY id asc ".$limit;
	$question_list = $db->getall($question_sql);
	foreach ($question_list as $key => $value) 
	{
		$option_sql = "SELECT * FROM ".table('evaluation_option')." WHERE question_id=".$value['id']." ORDER BY id asc ";
		$option_list = $db->getall($option_sql);
		$question_list2[intval($offset)+intval($key)+1] = $value;
		$question_list2[intval($offset)+intval($key)+1]['key'] = intval($offset)+intval($key)+1;
		$question_list2[intval($offset)+intval($key)+1]['option'] = $option_list;
	}
	$smarty->assign('question_list',$question_list2);
	$paperid = intval($_REQUEST['paperid']);
	$page_num = intval($_REQUEST['page_num']);
	$perpage = intval($_REQUEST['perpage']);
	$total_val = intval($_REQUEST['total_val']);


	//读取 答完页的session 整合当前页面的 要不然当前页面的数据会把之前存的session覆盖掉
	if(!empty($_SESSION['paper_info']))
	{
		$pagerarr = json_decode($_SESSION['paper_info'],true);
		$_SESSION['paper_info'] = $pagerarr;
	}
	foreach ($_REQUEST['question'] as $key => $value) 
	{
		$question[$key]['answer_id'] = $value;
		$question[$key]['score'] = $_REQUEST['question_score'][$value];
	}
	$pagerarr[$_REQUEST['page']] = $question;
	$_SESSION['paper_info'] = json_encode($pagerarr);
	
	//提交
	if($_REQUEST['submit_button'])
	{

		$paperall = json_decode($_SESSION['paper_info'],true);

		if($paper_info["result_type"]==1){
			$score = array();


			//循环 session
			for ($i=1; $i <= $page_num; $i++)
			{
				foreach ($paperall[$i] as $key => $value)
				{
					$score[trim($value['score'])][]=$value['score'];
				}
			}
			$temp=array();
			foreach($score as $key=>$v){
				$temp[]=$key."共".count($v)."个";
			}
			$result=join(";",$temp);
			$setsqlarr['score'] = $result;



			$setsqlarr['result_description']="您的得分为{$result}<br>分析结果如下:".get_result_abcd($score,$paper_info["id"]);

		}elseif($paper_info["result_type"]==2){
			$score = 0;


			//循环 session
			for ($i=1; $i <= $page_num; $i++)
			{
				foreach ($paperall[$i] as $key => $value)
				{
					$score += intval($value['score']);
				}
			}
			$setsqlarr['score'] = $score;

			$setsqlarr['result_description']="您的得分为{$score}<br>分析结果如下:".get_result_score($score,$paper_info["id"]);


		}
		$setsqlarr['uid'] = $_SESSION['uid'];
		$setsqlarr['paper_id'] = $id;
		$setsqlarr['paper_title'] = $paper_info['title'];
		$setsqlarr['type_id'] = $paper_info['type_id'];
		$setsqlarr['addtime'] = time();
		$pid=$db->inserttable(table('evaluation_record'),$setsqlarr,true);
		$db->query("update  ".table('evaluation_paper')." SET join_num=join_num+1  WHERE id='".$id."' ");
		$db->query("update  ".table('evaluation_type')." SET num=num+1  WHERE id='".$paper_info['type_id']."' ");
		unset($_SESSION['paper_info']);
		if(intval($pid) > 0)
		{
			header("Location: ".url_rewrite('QS_paper_result',array('id'=>$pid)));
		}
		else
		{
			$link[0]['text'] = "返回我的测评";
			$link[0]['href'] = 'my_evaluation.php';
			showmsg('答题失败！',0,$link);
		}

	}
	else
	{
		$paperall = json_decode($_SESSION['paper_info'],true);
		//循环 session
		for ($i=1; $i <= $page_num; $i++) 
		{
			foreach ($paperall[$i] as $key => $value) 
			{
				$answer_id[] = intval($value['answer_id']);
			}
		}
		$smarty->assign('answer_id',$answer_id);
		$smarty->assign('page_num',$page_num);
		$smarty->assign('perpage',$perpage);
		$smarty->assign('total_val',$total_val);
		$smarty->display('../tpl_evaluation/default/paper_answer.htm');
	}
}
// 判断个人积分
elseif($act == 'is_answer')
{
	$uid = intval($_SESSION['uid']);
	if($uid < 0 || (intval($_SESSION['utype']) != 2))
	{
		exit("2|只有个人会员才能进行测评！");
	}
	//判断积分是否足够
	$paperid = intval($_GET['paperid']);
	if($paperid <= 0)
	{
		exit("2|试卷参数错误！");
	}
	require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
	$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
	//试卷信息
	$peper_sql = "SELECT * FROM ".table('evaluation_paper')." WHERE id=".$paperid;
	$paper_info = $db->getone($peper_sql);
	if(!$paper_info)
	{
		exit("2|试卷信息发生错误！");
	}
	$user_points = $db->getone("SELECT * FROM ".table('members_points')." WHERE uid=".$uid);
	if(!$user_points)
	{
		exit("2|会员积分发生错误！");
	}
	elseif(intval($user_points['points']) < intval($paper_info['price']) && intval($paper_info['price']) != 0 )
	{
		exit("2|积分不足，请立即充值！");
	}
	else
	{
		$record_info = $db->getone("SELECT * FROM ".table('evaluation_record')." WHERE uid=".$uid." AND paper_id=".$paperid);
		if($record_info)
		{
			exit('3|您已经答过此卷！');
		}
		else
		{
			exit('1|您是否消耗'.$paper_info['price'].'积分来答此问卷？');
		}
	}
}
// 扣除个人积分
elseif($act == 'is_answer_next')
{
	$uid = intval($_SESSION['uid']);
	if($uid < 0 || (intval($_SESSION['utype']) != 2))
	{
		exit("只有个人会员才能进行测评！");
	}
	//判断积分是否足够
	$paperid = intval($_POST['paperid']);
	if($paperid <= 0)
	{
		exit("试卷参数错误！");
	}
	require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
	$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
	//试卷信息
	$peper_sql = "SELECT * FROM ".table('evaluation_paper')." WHERE id=".$paperid;
	$paper_info = $db->getone($peper_sql);
	if(!$paper_info)
	{
		exit("试卷信息发生错误！");
	}
	$user_points = $db->getone("SELECT * FROM ".table('members_points')." WHERE uid=".$uid);
	if(!$user_points)
	{
		exit("会员积分发生错误！");
	}
	elseif(intval($user_points['points']) < intval($paper_info['price']) && intval($paper_info['price']) != 0 )
	{
		exit("积分不足，请立即充值！");
	}
	else
	{
		$points_val=intval($user_points['points']) - intval($paper_info['price']);
		$sql = "UPDATE ".table('members_points')." SET points= '{$points_val}' WHERE uid='{$uid}' LIMIT 1";
		if (!$db->query($sql)) exit('更新积分失败！');
		exit('ok');
	}
}
//按分获取结果
function get_result_score($score,$paper_id){
	global $db;
	$sql = "SELECT * FROM ".table('evaluation_result')." WHERE paper_id=".$paper_id." ORDER BY result_score desc ";
	$list = $db->getall($sql);
	foreach($list as $key=>$value){
		if($value["result_score"]>=$score){
			return $value["result_description"];
		}
	}
}
//按ABCD获取结果
function get_result_abcd($score,$paper_id){


	$result=array();
	foreach($score as $key=>$value){
		 $result[]=get_abcd($key,count($value),$paper_id);
	}
	return join("<br>",$result);

}
function get_abcd($key,$num,$paper_id){
	global $db;
	$sql = "SELECT * FROM ".table('evaluation_result')." WHERE paper_id=".$paper_id." and result_options='".$key."' ORDER BY result_num desc ";

	$list = $db->getall($sql);

	foreach($list as $p=>$value){
		if($value["result_num"]>=$num){
			return $value["result_description"];
		}
	}
}
?>