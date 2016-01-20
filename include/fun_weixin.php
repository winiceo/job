<?php
 /*
 * 74cms 微信提醒方法
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
  /*
 * 微信提醒：申请职位 
 * $uid    					企业会员uid 
 * $resumeid 				简历id
 * $jobs_name 				职位名称
 * $personal_fullname 		个人姓名
 * $resume_experience_cn 	简历工作经验
 * $notes 					申请说明
*/
function set_applyjobs($uid,$resumeid,$jobs_name,$personal_fullname,$resume_experience_cn,$notes)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_applyjobs']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid = {$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$resume_url=$_CFG['wap_domain']."/resume-show.php?id=".$resumeid;
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "u_yoFifHb-ryYXMtNSlATj_Wfm1CWTKEjf8EkiM6dvY",
				'url' => $resume_url,
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("你收到了一份新简历，请及时登录".$_CFG['site_name']."查阅")),
									'color' => "#743A3A",
						),
					'job' => array('value' => urlencode(gbk_to_utf8($jobs_name)),
									'color' => "#743A3A",
						),
					'resuname' => array('value' => urlencode(gbk_to_utf8("--")),
									'color' => "#743A3A",
						),
					'realname' => array('value' => urlencode(gbk_to_utf8($personal_fullname)),
									'color' => "#743A3A",
						),
					'exp' => array('value' => urlencode(gbk_to_utf8($resume_experience_cn)),
									'color' => "#743A3A",
						),
					'lastjob' => array('value' => urlencode(gbk_to_utf8("--")),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n".$notes),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
  /*
 * 微信提醒：邀请面试
 * $uid    					个人会员uid 
 * $jobsid 					职位id
 * $companyname 			公司名称
 * $jobs_name 				职位名称
 * $jobs_address 			职位地址
 * $jobs_contact 			职位联系人
 * $jobs_telephone 			职位联系电话
 * $notes 					邀请说明
*/
function set_invite($uid,$jobsid,$companyname,$jobs_name,$jobs_address,$jobs_contact,$jobs_telephone,$notes)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_invite']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$jobs_url=$_CFG['wap_domain']."/jobs-show.php?id=".$jobsid;
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "sdjPV1l3vyv_9mclCe6_Fm8UzyAadMI_w5iIC1DPFPE",
				'url' => $jobs_url,
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8($companyname."邀请您参加公司面试")),
									'color' => "#743A3A",
						),
					'job' => array('value' => urlencode(gbk_to_utf8($jobs_name)),
									'color' => "#743A3A",
						),
					'company' => array('value' => urlencode(gbk_to_utf8($companyname)),
									'color' => "#743A3A",
						),
					'time' => array('value' => urlencode(gbk_to_utf8("请点击查看")),
									'color' => "#743A3A",
						),
					'address' => array('value' => urlencode(gbk_to_utf8($jobs_address)),
									'color' => "#743A3A",
						),
					'contact' => array('value' => urlencode(gbk_to_utf8($jobs_contact)),
									'color' => "#743A3A",
						),
					'tel' => array('value' => urlencode($jobs_telephone),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n".$notes),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
 /*
 * 微信提醒：开通电子地图
 * $uid    					企业会员uid 
 * $address 					地址
*/
function set_addmap($uid,$address)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_addmap']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "RMJ-itxpYQHpZp6Zlr7c9qCpg8LUFU3svJoyq4pDiXk",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("用户开通电子地图提醒")),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8("尊敬的用户，您于【".date('Y-m-d',time())."】申请开通电子地图。")),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8("您电子地图标注的具体位置为 : ".$address)),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n感谢您的使用"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
/*
 * 微信提醒：职位审核通知
 * $uid    					会员uid 
 * $weixinconfig 			操作 : 比如职位审核
 * $first 					提醒标题
 * $keyword1 				职位名称
 * $keyword2 				审核结果
 * $keyword3 				原因
*/
function set_jobsallow($uid,$weixinconfig,$first,$keyword1,$keyword2,$keyword3)
{
	global $db,$_CFG;
	//操作
	$weixinconfig = trim($weixinconfig);
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "y-YiAT3ugGu0DC9KTbiugJmFGrdsdOirAFFfBYMmIjw",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("尊敬的用户，您好，您所发布的职位信息".$first)),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8($keyword2)),
									'color' => "#743A3A",
						),
					'keyword3' => array('value' => urlencode(gbk_to_utf8($keyword3)),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n请您修改相关信息后重新提交，谢谢。"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
/*
 * 微信提醒：简历审核通知
 * $uid    					会员uid 
 * $weixinconfig 			操作 : 比如简历审核
 * $keyword1 				简历名称
 * $keyword3 				审核结果
 * $keyword4 				原因
*/
function set_resumeallow($uid,$weixinconfig,$keyword1,$keyword2,$keyword3)
{
	global $db,$_CFG;
	//操作
	$weixinconfig = trim($weixinconfig);
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "PL9gZ6pm6qORiDq15yUAySA0n1lcrcoVncGdwENAmHU",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("你好！你的简历审核结果如下，敬请留意！")),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8(date('Y-m-d',time()))),
									'color' => "#743A3A",
						),
					'keyword3' => array('value' => urlencode(gbk_to_utf8($keyword3)),
									'color' => "#743A3A",
						),
					'keyword4' => array('value' => urlencode(gbk_to_utf8($keyword4)),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n一份完整、详实的简历是您美好职业未来的敲门砖。"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
 /*
 * 微信提醒：审核通知
 * $uid    					会员uid 
 * $weixinconfig 			操作 : 营业执照审核不通过
 * $first 					提醒标题
 * $keyword1 				审核结果
 * $keyword2 				原因
*/
function set_licenseallow($uid,$weixinconfig,$first,$keyword1,$keyword2)
{
	global $db,$_CFG;
	//操作
	$weixinconfig = trim($weixinconfig);
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "zXQIq9OAEOLMa7HUM6q39wmti95tjhbmlWq_Vb9PiRA",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8($first)),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8($keyword2)),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n感谢您对".$_CFG['site_name']."的支持！"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
/*
 * 微信提醒：修改密码
 * $uid    					会员uid 
 * $keyword1 				登录账号
 * $keyword2 				密码
*/
function set_editpwd($uid,$keyword1,$keyword2)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_editpwd']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "Q6LCTh109qdER7HR-kAu6egWbGiwuuyIw4xBX8Rwtq4",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("你在".$_CFG['site_name']."的登录信息如下：")),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8($keyword2)),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n请妥善保管。"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
/*
 * 微信提醒：充值成功
 * $uid    					会员uid 
 * $keyword1 				商品名称
 * $keyword2 				编号
 * $keyword3 				金额
*/
function set_payment($uid,$keyword1,$keyword2,$keyword3)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_payment']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "ibrGQdiK_o4r8sNSQT7Fx1f5UZwiE6Zshqff5VZ8ayY",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("尊敬的客户，您已充值成功")),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8($keyword2)),
									'color' => "#743A3A",
						),
					'keyword3' => array('value' => urlencode(gbk_to_utf8($keyword3)),
									'color' => "#743A3A",
						),
					'keyword4' => array('value' => urlencode(gbk_to_utf8(data('Y-m-d',time()))),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n感谢您的光临。"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}
/*
 * 微信提醒：新增订单
 * $uid    					会员uid 
 * $keyword1 				订单编号
 * $keyword2 				订单类型
 * $keyword3 				金额
*/
function set_order_msg($uid,$keyword1,$keyword2,$keyword3)
{
	global $db,$_CFG;
	$weixinconfig=get_cache('weixin_config');
	if(intval($_CFG['weixin_apiopen'])==1 && $weixinconfig['set_payment']=='1')
	{
		$user=$db->getone("select weixin_openid from ".table('members')." where uid ={$uid} limit 1");
		if($user['weixin_openid']!="")
		{
			$template = array(
				'touser' => $user['weixin_openid'],
				'template_id' => "UxurRCK19QNDb3_g2lO5GSQ4lHO48-d1idxBYvutbBc",
				'url' => '',
				'topcolor' => "#7B68EE",
				'data' => array(
					'first' => array('value' => urlencode(gbk_to_utf8("尊敬的用户，您有以下订单需要完成支付：")),
									'color' => "#743A3A",
						),
					'keyword1' => array('value' => urlencode(gbk_to_utf8($keyword1)),
									'color' => "#743A3A",
						),
					'keyword2' => array('value' => urlencode(gbk_to_utf8($keyword2)),
									'color' => "#743A3A",
						),
					'keyword3' => array('value' => urlencode(gbk_to_utf8($keyword3)),
									'color' => "#743A3A",
						),
					'keyword4' => array('value' => urlencode(gbk_to_utf8(data('Y-m-d',time()))),
									'color' => "#743A3A",
						),
					'remark' => array('value' => urlencode("\\n您可以查看该订单后支付。"),
									'color' => "#743A3A",
						)
					)
				);
			send_template_message(urldecode(json_encode($template)));
		}
	}
}


?>