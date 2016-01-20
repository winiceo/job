<?php 
 /*
 * 74cms 支付异步操作
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
require_once(QISHI_ROOT_PATH."include/payment/alipay.php");

$dingdan           = $_POST['out_trade_no'];		//获取订单号
$total_fee         = $_POST['total_fee'];		//获取总价格
$order=$db->getone("select * from ".table('order')." WHERE oid ='{$dingdan}'  LIMIT 1 ");
$funtype=array('1'=>'include/fun_company.php',4=>'include/fun_train.php',3=>'include/fun_hunter.php');
require_once(QISHI_ROOT_PATH.$funtype[$order['utype']]);

$time=date('Y-m-d H:i:s',time());
$log_file = QISHI_ROOT_PATH.'/data/'.'allpay_safe.txt'; 
fputs(fopen($log_file,'a+'),$time.'/'.$dingdan.'/'.$order['is_paid']."\r\n");

	
$payment= get_payment_info('alipay');	
$partner		= trim($payment['partnerid']);
$key			= trim($payment['ytauthkey']);
$sign_type		= "MD5";
$_input_charset	= "GBK";
$transport		= "http";
$alipay = new alipay_notify($partner,$key,$sign_type,$_input_charset,$transport);
$verify_result = $alipay->notify_verify();
if($verify_result) {//验证成功
	order_paid($dingdan);
	return 'success';
}
else {
	return 'err';
}
?>
