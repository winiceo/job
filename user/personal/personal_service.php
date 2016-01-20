<?php
/*
 * 74cms 个人会员中心
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/personal_common.php');
$smarty->assign('leftmenu',"service");
//我的账户 -> 积分操作 
if ($act=='j_account')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$smarty->assign('operation_mode',intval($_CFG['operation_mode']));
	//收支状态(消耗->1 赠送->2)/操作时间
	$cid=trim($_GET['cid']);
	$settr=intval($_GET['settr']);
	//积分
	$my_points = get_user_points(intval($_SESSION['uid']));
	$smarty->assign('points',$my_points);
	$smarty->assign('act','j_account');
	$smarty->assign('title','我的账户 - 个人会员中心 - '.$_CFG['site_name']);
	//积分消费明细
	if(trim($_GET['detail']) == '1')
	{
		$wheresql=" WHERE log_uid='{$_SESSION['uid']}' AND log_type=9001 AND log_mode=2";
		if($settr>0)
		{
			$settr_val=strtotime("-".$settr." day");
			$wheresql.=" AND log_addtime>".$settr_val;
			$smarty->assign('settr',$_GET['settr']);
		}
		if($cid == '1')
		{
			$smarty->assign('c_type',"消耗");
			$smarty->assign('cid',$_GET['cid']);
			$wheresql.=" AND log_op_used < 0 ";
		}
		elseif($cid == '2')
		{
			$smarty->assign('c_type',"赠送");
			$smarty->assign('cid',$_GET['cid']);
			$wheresql.=" AND log_op_used > 0 ";
		}
		$perpage=10;
		$total_sql="SELECT COUNT(*) AS num FROM ".table('members_log').$wheresql;
		$total_val=$db->get_total($total_sql);
		$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
		$offset=($page->nowindex-1)*$perpage;
		$smarty->assign('report',get_user_report($offset, $perpage,$wheresql));
		$smarty->assign('page',$page->show(3));
		$smarty->display('member_personal/personal_my_account_detail.htm');
	}
	//积分规则
	else
	{
		$smarty->assign('points_rule',get_points_rule());
		$smarty->display('member_personal/personal_my_account.htm');
	}
}
elseif ($act=='order_list')
{
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$is_paid=trim($_GET['is_paid']);
	$pay_type=intval($_GET['pay_type']);
	$wheresql=" WHERE uid='".$_SESSION['uid']."' ";
	//订单状态
	if($is_paid<>'' && is_numeric($is_paid))
	{
		$smarty->assign('is_paid',$is_paid);
		$wheresql.=" AND is_paid='".intval($is_paid)."' ";
	}
	//订单类别
	if($pay_type > 0)
	{
		$smarty->assign('pay_type',$pay_type);
		$wheresql.=" AND pay_type='".intval($pay_type)."' ";
	}
	$perpage=10;
	$total_sql="SELECT COUNT(*) AS num FROM ".table('order').$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$smarty->assign('title','充值记录 - 个人会员中心 - '.$_CFG['site_name']);
	$smarty->assign('act',$act);
	$smarty->assign('is_paid',$is_paid);
	$smarty->assign('payment',get_order_all($offset, $perpage,$wheresql));
	if ($total_val>$perpage)
	{
	$smarty->assign('page',$page->show(3));
	}
	$smarty->display('member_personal/personal_order_list.htm');
}
elseif ($act=='order_add')
{
	$smarty->assign('title','在线充值 - 个人会员中心 - '.$_CFG['site_name']);
	$smarty->assign('payment',get_payment());
	$smarty->assign('points',get_user_points($_SESSION['uid']));
	$smarty->display('member_personal/personal_order_add.htm');
}
elseif ($act=='order_add_save')
{
	$myorder=get_user_order($_SESSION['uid'],1);
	$order_num=count($myorder);
	if ($order_num>=5)
	{
	$link[0]['text'] = "立即查看";
	$link[0]['href'] = '?act=order_list&is_paid=1';
	showmsg("未处理的订单不能超过 5 条，请先处理后再次申请！",1,$link,true,8);
	}
	$amount=(trim($_POST['amount'])).(intval($_POST['amount']))?trim($_POST['amount']):showmsg('请填写充值金额！',1);
	($amount<$_CFG['payment_min'])?showmsg("单笔充值金额不能少于 ".$_CFG['payment_min']." 元！",1):'';
	$payment_name=empty($_POST['payment_name'])?showmsg("请选择付款方式！",1):$_POST['payment_name'];
	$paymenttpye=get_payment_info($payment_name);
	if (empty($paymenttpye)) showmsg("支付方式错误！",0);
	$fee=number_format(($amount/100)*$paymenttpye['fee'],1,'.','');//手续费
	$order['oid']= strtoupper(substr($paymenttpye['typename'],0,1))."-".date('ymd',time())."-".date('His',time());//订单号
	$order['v_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond_".$paymenttpye['typename'].".php";
	$order['v_amount']=$amount+$fee; 
	$points=$amount*$_CFG['payment_rate'];
	$order_id=add_order($_SESSION['uid'],4,$order['oid'],$amount,$payment_name,"充值积分:".$points,$timestamp,$points,'',2);
	if ($order_id)
	{
		header("location:?act=payment&order_id=".$order_id);
	}
	else
	{
		showmsg("添加订单失败！",0);
	}
}
elseif ($act=='payment')
{
	$smarty->assign('payment',get_payment());
	$order_id=intval($_GET['order_id']);
	$myorder=get_order_one($_SESSION['uid'],$order_id);
	$payment=get_payment_info($myorder['payment_name']);
	if (empty($payment)) showmsg("支付方式错误！",0);
	$fee=number_format(($myorder['amount']/100)*$payment['fee'],1,'.','');//手续费
	$order['oid']=$myorder['oid'];//订单号
	//判断是否是支付宝  若是支付宝提供异步通知链接
	if($payment['typename']=='alipay')
	{
		$order['n_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond.php";
	}
	$order['v_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond_".$payment['typename'].".php";
	//$order['v_amount']=$myorder['amount']+$fee;
	$order['v_amount']=1;
	if ($myorder['payment_name']!='remittance' && $myorder['payment_name']!='weixinpay')//假如是非线下支付，
	{
		require_once(QISHI_ROOT_PATH."include/payment/".$payment['typename'].".php");
		$payment_form=get_code($order,$payment);
		if (empty($payment_form)) showmsg("在线支付参数错误！",0);
	}
	//微信支付  生成二维码图片
	elseif($myorder['payment_name']=='weixinpay')
	{
		require_once(QISHI_ROOT_PATH.'include/payment/native.php');
		$smarty->assign('w_url',urlencode($url2));
		fopen(QISHI_ROOT_PATH.'data/wxpay/'.$myorder['oid'].'.tmp', "w") or die("无法打开缓存文件!");
		$_SESSION['wxpay_no'] = $myorder['oid'];
	}
	$smarty->assign('points',get_user_points($_SESSION['uid']));
	$smarty->assign('title','付款 - 个人会员中心 - '.$_CFG['site_name']);
	$smarty->assign('fee',$fee);
	$smarty->assign('amount',$myorder['amount']);
	$smarty->assign('oid',$order['oid']);
	$smarty->assign('byname',$payment);
	$smarty->assign('payment_form',$payment_form);
	$smarty->display('member_personal/personal_order_pay.htm');
}
elseif ($act=='order_del')
{
	$link[0]['text'] = "返回上一页";
	$link[0]['href'] = '?act=order_list';
	$id=intval($_GET['id']);
	del_order($_SESSION['uid'],$id)?showmsg('取消成功！',2,$link):showmsg('取消失败！',1);
}

unset($smarty);
?>