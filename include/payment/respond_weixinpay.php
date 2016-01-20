<?php 
 /*
 * 74cms 支付响应页面
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$funtype=array('1'=>'include/fun_company.php',2=>'include/fun_personal.php',4=>'include/fun_train.php',3=>'include/fun_hunter.php');
$timestamp = time();
$result = $_POST;
global $_CFG;
if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS")
{
	$order=$db->getone("select * from ".table('order')." WHERE oid ='".$result['out_trade_no']."'  LIMIT 1 ");
	require_once(QISHI_ROOT_PATH.$funtype[$order['utype']]);
	//判断是否支付完成
	if(intval($order['is_paid']) == 1)
	{
		$sql = "UPDATE ".table('order')." SET is_paid= '2',payment_time='{$timestamp}' WHERE oid='".$result['out_trade_no']."' LIMIT 1 ";
		if (!$db->query($sql)) return false;
	
		//套餐、积分支付
		if($order['pay_type'] == '1' || $order['pay_type'] == '4')			
		{		 
			$order_name = "套餐积分订单";
			$user=get_user_info($order['uid']);
			if($order['amount']=='0.00'){
				$ismoney=1;
			}else{
				$ismoney=2;
			}
			if ($order['points']>0)
			{
				report_deal($order['uid'],1,$order['points']);				
				$user_points=get_user_points($order['uid']);
				$notes=date('Y-m-d H:i',time())."通过：".get_payment_info($order['payment_name'],true)." 成功充值 ".$order['amount']."元，(+{$order['points']})，(剩余:{$user_points}),订单:{$v_oid}";					
				write_memberslog($order['uid'],1,9001,$user['username'],$notes); 
				//会员套餐变更记录。会员购买成功。2表示：会员自己购买
				write_setmeallog($order['uid'],$user['username'],$notes,2,$order['amount'],$ismoney,1);
			}
			elseif ($order['setmeal']>0)
			{
				set_members_setmeal($order['uid'],$order['setmeal']);
				$setmeal=get_setmeal_one($order['setmeal']);
				$notes=date('Y-m-d H:i',time())."通过：".get_payment_info($order['payment_name'],true)." 成功充值 ".$order['amount']."元并开通{$setmeal['setmeal_name']}";
				write_memberslog($order['uid'],1,9002,$user['username'],$notes);
				//会员套餐变更记录。会员购买成功。2表示：会员自己购买
				write_setmeallog($order['uid'],$user['username'],$notes,2,$order['amount'],$ismoney,2,1);
			} 
		}
		//广告位支付
		elseif($order['pay_type'] == '2')		
		{	 
			$order_name = "广告位订单";
			write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"申请广告位：<strong>{$order['description']}</strong>，(花费： {$order['amount']})。",1,1020,"申请广告位","-{$order['amount']}","{$user_points}"); 
		}
		//短信套餐支付
		elseif($order['pay_type'] == '3')		
		{	
			$order_name = "短信套餐订单";
			$user=get_user_info($order['uid']);
			if($order['setmeal'] > 0){	//查看短信套餐
				set_members_sms($order['uid'],intval($order['setmeal']));	//支付成功，向用户增加短信条数
				$user_points = get_user_setmeal($order['uid']);
				write_memberslog($_SESSION['uid'],1,9003,$_SESSION['username'],"短信充值套餐：<strong>{$order['description']}</strong>，(- {$order['amount']})，(剩余:{$user_points['set_sms']})",1,1020,"申请广告位","- {$order['amount']}","{$user_points['set_sms']}");
			}
		} 
		//sendemail
		$mailconfig=get_cache('mailconfig');
		if ($mailconfig['set_payment']=="1" && $user['email_audit']=="1" && $order['amount']>0)
		{
		dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_mail.php?uid={$order['uid']}&key=".asyn_userkey($order['uid'])."&act=set_payment");
		} 
		//sms
		$sms=get_cache('sms_config');
		if ($sms['open']=="1" && $sms['set_payment']=="1"  && $user['mobile_audit']=="1" && $order['amount']>0)
		{  
			dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_sms.php?uid={$order['uid']}&key=".asyn_userkey($order['uid'])."&act=set_payment"); 
		}
		//return true;
 	}
 	// else
 	// {
 	// 	//return true;
 	// }
 	
 	echo $order['oid'];
}

?>
