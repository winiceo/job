<?php
 /*
 * 74cms WAP
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(QISHI_ROOT_PATH.'include/jssdk.php');
$jssdk = new JSSDK($_CFG['weixin_appid'], $_CFG['weixin_appsecret'],get_access_token());
$signPackage = $jssdk->GetSignPackage();
$smarty->assign("signPackage",$signPackage);
?>