<?php
 /*
 * 74cms WAP
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(QISHI_ROOT_PATH.'include/jssdk.php');
$jssdk = new JSSDK($_CFG['weixin_appid'], $_CFG['weixin_appsecret'],get_access_token());
$signPackage = $jssdk->GetSignPackage();
$smarty->assign("signPackage",$signPackage);
?>