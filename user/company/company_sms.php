<?php
/*
 * 74cms 企业短信管理中心
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/company_common.php');
$smarty->assign('leftmenu',"sms");
	 
if(check_sms_open() == true){ 
	$link[0]['text'] = "会员中心首页";
	$link[0]['href'] = 'company_index.php?act=';
	showmsg("您没有权限操作！",2,$link);	 
}
if ($act=='sms_order')
{ 
	$sms_setmeal = get_sms_setmeal($_SESSION['uid']);
	$smarty->assign('sms_setmeal',$sms_setmeal); 
	$smarty->assign('title','短信充值 - 企业会员中心 - '.$_CFG['site_name']);
	$smarty->display('member_company/company_sms_order.htm');
} 
elseif ($act=='sms_order_add')
{ 
	$setmealid = intval($_GET['setmealid'])?intval($_GET['setmealid']):showmsg("请选择短信套餐！",1); 
	$smarty->assign('title','申请短信套餐 - 企业会员中心 - '.$_CFG['site_name']);
	$smarty->assign('meal_sms',get_sms_setmeal_one($setmealid));
	$smarty->assign('payment',get_payment());
	$smarty->assign('points',get_user_points($_SESSION['uid']));
	$smarty->display('member_company/company_sms_order_add.htm');
}
elseif ($act=='order_sms_add_save')
{
	if (!$cominfo_flge)
	{
		$link[0]['text'] = "填写企业资料";
		$link[0]['href'] = 'company_info.php?act=company_profile';
		showmsg("请先填写您的企业资料！",1,$link);
	}
	 
	$sms_meal=get_sms_setmeal_one($_POST['meal_id']); 
	$payment_name=empty($_POST['payment_name'])?showmsg("请选择付款方式！",1):$_POST['payment_name']; 
	if($payment_name=="points"){
		//选择积分模式
		$p = get_user_points($_SESSION['uid']);
		$expense = intval($_POST['points_expense_input']);
		if($p<$expense){
			showmsg("您的积分不足以支付！",1);
		}
		$order['oid']= "P-".date('ymd',time())."-".date('His',time());//订单号
		$order['v_amount']=$expense;	//支付的积分数量
		$order_id=add_sms_order($_SESSION['uid'],$order['oid'],$expense, $payment_name,$sms_meal['setmeal_name'],$timestamp,$expense,intval($_POST['meal_id']));
	}
	else
	{	//金钱模式 
		$paymenttpye=get_payment_info($payment_name);
		if (empty($paymenttpye)) showmsg("支付方式错误！",0);
		$expense = intval($_POST['expense_input']);
		$fee=number_format(($expense/100)*$paymenttpye['fee'],1,'.','');//手续费
		$order['oid']= strtoupper(substr($paymenttpye['typename'],0,1))."-".date('ymd',time())."-".date('His',time());//订单号
		$order['v_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond_".$paymenttpye['typename'].".php";
		$order['v_amount']=$expense+$fee;//金额
		$order_id=add_sms_order($_SESSION['uid'],$order['oid'],$expense, $payment_name,$sms_meal['setmeal_name'],$timestamp,0,intval($_POST['meal_id']));
	}
	if($order_id)
	{
		if ($order['v_amount']==0)	//0元短信套餐
		{
			if (sms_order_paid($order['oid']))
			{
				$link[0]['text'] = "查看订单";
				$link[0]['href'] = 'company_service.php?act=order_list';
				$link[1]['text'] = "会员中心首页";
				$link[1]['href'] = 'company_index.php?act=';
				showmsg("操作成功，请等待管理员审核！",2,$link);	
			}
		}
		header("Location:?act=sms_payment&order_id=".$order_id."");//付款页面
	}
	else
	{
	showmsg("添加订单失败！",0);
	}
}
elseif ($act=='sms_payment')
{
	$_SESSION['sms_pay'] = 1;
	$smarty->assign('payment',get_payment());
	$order_id=intval($_GET['order_id']);
	$myorder=get_sms_order_one($_SESSION['uid'],$order_id);
	if($myorder['payment_name']!="points")
	{ 
		$payment=get_payment_info($myorder['payment_name']);
		if (empty($payment)) showmsg("支付方式错误！",0); 
		$fee=number_format(($myorder['amount']/100)*$payment['fee'],1,'.','');//手续费
		$order['oid']=$myorder['oid'];//订单号
		//订单号//判断是否是支付宝  若是支付宝提供异步通知链接
		if($payment['typename']=='alipay')
		{
			$order['n_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond.php";
		}
		$order['v_url']=$_CFG['site_domain'].$_CFG['site_dir']."include/payment/respond_".$payment['typename'].".php";
		$order['v_amount']=$myorder['amount']+$fee;
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
	}
	if($myorder['payment_name']=="points")
	{
		$myorder['amount'] = intval($myorder['amount']);
	} 
	$myorder['payment_name_'] = get_payment_info($myorder['payment_name'],true);
	$smarty->assign('myorder',$myorder);
	$smarty->assign('fee',$fee);
	$smarty->assign('title','付款 - 企业会员中心 - '.$_CFG['site_name']);
	$smarty->assign('byname',$payment);
	$smarty->assign('payment_form',$payment_form);
	$smarty->display('member_company/company_sms_order_pay.htm');
}
elseif($act == "sms_order_pay")
{
	$orderid = intval($_GET['order_id'])?intval($_GET['order_id']):showmsg("您没有选择订单！",1);
	$myorder=get_adv_order_one($_SESSION['uid'],$orderid);
	if (sms_order_paid($myorder['oid']))
	{
		$link[0]['text'] = "查看订单";
		$link[0]['href'] = 'company_service.php?act=order_list';
		$link[1]['text'] = "会员中心首页";
		$link[1]['href'] = 'company_index.php?act=';
		showmsg("操作成功，请等待管理员审核！",2,$link);	
	}
}
unset($smarty);
?>