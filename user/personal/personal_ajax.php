<?php
/*
 * 74cms 个人会员中心ajax弹出框
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__) . '/personal_common.php');
if($act=="privacy"){
	$pid = intval($_GET['pid']);
	$uid = intval($_SESSION['uid']);
	$resume_basic=get_resume_basic($uid,$pid);
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_privacy_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$title#}',$resume_basic['title'],$contents);
	$contents=str_replace('{#$lastname#}',$resume_basic['lastname'],$contents);
	$contents=str_replace('{#$privacy_display#}',$resume_basic['display'],$contents);
	$contents=str_replace('{#$privacy_display_name#}',$resume_basic['display_name'],$contents);
	$contents=str_replace('{#$privacy_photo_display#}',$resume_basic['photo_display'],$contents);
	$contents=str_replace('{#$site_dir#}',$_CFG['site_dir'],$contents);
	exit($contents);
}
//委托简历
elseif($act=="entrust"){
	$pid = intval($_GET['pid']);
	$uid = intval($_SESSION['uid']);
	$resume_basic=get_resume_basic($uid,$pid);
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_entrust_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$title#}',$resume_basic['title'],$contents);
	exit($contents);
}
elseif($act=="user_email"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_email_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_email_key']=mt_rand(100000, 999999);
	$contents=str_replace('{#$email#}',$user["email"],$contents);
	$contents=str_replace('{#$site_name#}',$_CFG['site_name'],$contents);
	$contents=str_replace('{#$send_email_key#}',$_SESSION['send_email_key'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	$contents=str_replace('{#$notice#}','接收HR面试邀请',$contents);
	exit($contents);
}	
elseif($act=="user_mobile"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_mobile_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_mobile_key']=mt_rand(100000, 999999);
	$contents=str_replace('{#$mobile#}',$user["mobile"],$contents);
	$contents=str_replace('{#$site_name#}',$_CFG['site_name'],$contents);
	$contents=str_replace('{#$send_mobile_key#}',$_SESSION['send_mobile_key'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	$contents=str_replace('{#$notice#}','接收HR来电',$contents);
	exit($contents);
}
elseif($act=="old_mobile"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_old_mobile_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_mobile_key']=mt_rand(100000, 999999);
	$user["hid_mobile"] = substr($user["mobile"],0,3)."*****".substr($user["mobile"],7,4);
	$contents=str_replace('{#$mobile#}',$user["mobile"],$contents);
	$contents=str_replace('{#$hid_mobile#}',$user["hid_mobile"],$contents);
	$contents=str_replace('{#$send_mobile_key#}',$_SESSION['send_mobile_key'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act=="edit_mobile"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_edit_mobile_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_mobile_key']=mt_rand(100000, 999999);
	$contents=str_replace('{#$send_mobile_key#}',$_SESSION['send_mobile_key'],$contents);
	$contents=str_replace('{#$site_name#}',$_CFG['site_name'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	$contents=str_replace('{#$notice#}','接收HR来电',$contents);
	exit($contents);
}
elseif($act=="ajax_refresh_resume"){
	$pid = intval($_GET['pid']);
	$uid = intval($_SESSION['uid']);
	$resume_basic=get_resume_basic($uid,$pid);
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_refresh_resume.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$name#}',$resume_basic['fullname'],$contents);
	$contents=str_replace('{#$refreshtime#}',date('Y-m-d',$resume_basic['refreshtime']),$contents);
	$contents=str_replace('{#$current#}',$resume_basic['current'],$contents);
	$contents=str_replace('{#$current_cn#}',$resume_basic['current_cn'],$contents);
	$currentarray = $db->getall("select c_id,c_name from ".table('category')." where c_alias='QS_current' order by c_order desc ");
	$current_str = '';
	foreach ($currentarray as  $value) 
	{
		$current_str .= '<li><a href="javascript:;" id="'.$value['c_id'].'">'.$value['c_name'].'</a></li>';
	}
	$contents=str_replace('{#$current_str#}',$current_str,$contents);
	if(empty($resume_basic['telephone']))
	{
		$contents=str_replace('{#$phone#}',"未填写",$contents);
	}
	else
	{
		$contents=str_replace('{#$phone#}',$resume_basic['telephone'],$contents);
	}
	if(empty($resume_basic['email']))
	{
		$contents=str_replace('{#$email#}',"未填写",$contents);
	}
	else
	{
		$contents=str_replace('{#$email#}',$resume_basic['email'],$contents);
	}
	exit($contents);
}
elseif($act == "ajax_refresh_resume_save"){
	$resumeid = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择简历！");
	$current = intval($_GET['current'])?intval($_GET['current']):exit("参数错误！");
	$current_cn = trim($_GET['current_cn'])?trim($_GET['current_cn']):exit("参数错误！");
	$refrestime=get_last_refresh_date($_SESSION['uid'],"2001");
	$duringtime=time()-$refrestime['max(addtime)'];
	$space = $_CFG['per_refresh_resume_space']*60;
	$refresh_time = get_today_refresh_times($_SESSION['uid'],"2001");
	if($_CFG['per_refresh_resume_time']!=0&&($refresh_time['count(*)']>=$_CFG['per_refresh_resume_time']))
	{
	exit("每天最多只能刷新".$_CFG['per_refresh_resume_time']."次,您今天已超过最大刷新次数限制！");	
	}
	elseif($duringtime<=$space && $space!=0){
	exit($_CFG['per_refresh_resume_space']."分钟内不能重复刷新简历！");
	}
	else 
	{
		//修改目前状态
		$db->updatetable(table('resume'),array('current'=>$current,'current_cn'=>$current_cn)," id=".$resumeid);
		$db->updatetable(table('resume_search_key'),array('current'=>$current)," id=".$resumeid);
		$db->updatetable(table('resume_search_rtime'),array('current'=>$current)," id=".$resumeid);
		refresh_resume($resumeid,$_SESSION['uid'])?exit("ok"):exit('操作失败！');
	}
}
elseif($act=="shield_company"){
	$pid = intval($_GET['pid']);
	$uid = intval($_SESSION['uid']);
	$resume_basic=get_resume_basic($uid,$pid);
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_shield_company.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$title#}',$resume_basic['title'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act=="save_shield_company"){
	$setsqlarr['pid'] = intval($_POST['pid'])?intval($_POST['pid']):exit("-1");
	$setsqlarr['uid'] = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("-1");
	$count = count_com_keyword($setsqlarr['uid'],$setsqlarr['pid']);
	if($count>=10){
		exit("full");
	}
	$setsqlarr['comkeyword'] = trim($_POST['comkeyword'])?utf8_to_gbk(trim($_POST['comkeyword'])):exit("-1");
	$insertid = $db->inserttable(table("personal_shield_company"),$setsqlarr,1);
	if($insertid){
		exit("1");
	}else{
		exit("-1");
	}
}
elseif($act=="get_shield_com_keyword"){
	$pid = intval($_POST['pid'])?intval($_POST['pid']):exit("-1");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("-1");
	$comkeyword=get_com_keyword($uid,$pid);
	$html = "";
	if(!empty($comkeyword)){
		foreach ($comkeyword as $key => $value) {
			$html.='<div class="input_checkbox"><span keyword_id="'.$value["id"].'" class="del">'.$value["comkeyword"].'</span></div>';
		}
	}
	$html.='<div class="input_checkbox_add"><span>添加</span></div>';
	exit($html);
}
elseif($act=="del_shield_company"){
	$pid = intval($_POST['pid'])?intval($_POST['pid']):exit("-1");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("-1");
	$keyword_id = intval($_POST['keyword_id'])?intval($_POST['keyword_id']):exit("-1");
	if(del_shield_company($uid,$pid,$keyword_id)){
		exit("1");
	}else{
		exit("-1");
	}
}
elseif($act=="tpl"){
	$pid = intval($_GET['pid']);
	$uid = intval($_SESSION['uid']);
	$resume_basic=get_resume_basic($uid,$pid);
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_tpl.htm";
	$resumetpl = get_resumetpl();
	$resume_url = url_rewrite("QS_resumeshow",array("id"=>$pid),false);
	if ($resume_basic['tpl']=="")
	{
	$resume_basic['tpl']=$_CFG['tpl_personal'];
	}
	$html = "";
	if(!empty($resumetpl)){
		foreach ($resumetpl as $key => $value) {
			$html_l.='<label><input type="radio" id="tpl_pid" name="resume_tpl" value="'.$value["tpl_dir"].'" class="radio set_tpl" '.($resume_basic['tpl']==$value['tpl_dir']?'checked':'').'>'.$value["tpl_name"].($resume_basic['tpl']==$value['tpl_dir']?'<span>[当前]</span>':'').'</label>';
			$html.='<div class="resume_box tpl_img'.$value["tpl_dir"].'" '.($resume_basic['tpl']==$value['tpl_dir']?'style="display:block"':'style="display:none"').'>
					<div class="img"><img src="'.$_CFG["site_dir"].'templates/tpl_resume/'.$value["tpl_dir"].'/thumbnail.jpg" alt="" /></div>
					<p style="margin-top:10px;"><a target="_blank" href="'.$resume_url.'&style='.$value["tpl_dir"].'">[预览]</a></p>
				</div>';
			
		}
	}
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$resume_tpl#}',$resume_basic['tpl'],$contents);
	$contents=str_replace('{#$title#}',$resume_basic['title'],$contents);
	$contents=str_replace('{#$tpl_left#}',$html_l,$contents);
	$contents=str_replace('{#$tpl_img#}',$html,$contents);
	$contents=str_replace('{#$pid#}',$resume_basic['id'],$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	$contents=str_replace('{#$site_dir#}',$_CFG['site_dir'],$contents);
	exit($contents);
}
elseif($act == "save_tpl"){
	$wheresqlarr['id'] = intval($_POST['tpl_pid'])?intval($_POST['tpl_pid']):exit("-1");
	$wheresqlarr['uid'] = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("-1");
	$setsqlarr['tpl'] = trim($_POST['tpl_dir'])?trim($_POST['tpl_dir']):exit("-1");
	$db->updatetable(table('resume'),$setsqlarr,$wheresqlarr);
	exit("1");
}
elseif($act == "del_resume"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	exit($contents);
}
elseif($act == "refresh_resume"){
	$resumeid = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择简历！");
	$refrestime=get_last_refresh_date($_SESSION['uid'],"2001");
	$duringtime=time()-$refrestime['max(addtime)'];
	$space = $_CFG['per_refresh_resume_space']*60;
	$refresh_time = get_today_refresh_times($_SESSION['uid'],"2001");
	if($_CFG['per_refresh_resume_time']!=0&&($refresh_time['count(*)']>=$_CFG['per_refresh_resume_time']))
	{
	exit("每天最多只能刷新".$_CFG['per_refresh_resume_time']."次,您今天已超过最大刷新次数限制！");	
	}
	elseif($duringtime<=$space){
	exit($_CFG['per_refresh_resume_space']."分钟内不能重复刷新简历！");
	}
	else 
	{
	refresh_resume($resumeid,$_SESSION['uid'])?exit("1"):exit('操作失败！');
	}
}
elseif($act == "del_resume_edu"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$id = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择教育经历！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_edu_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act == "del_resume_work"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$id = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择工作经历！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_work_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act == "del_resume_training"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$id = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择培训经历！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_training_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act == "del_resume_language"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$id = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择语言能力！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_language_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act == "del_resume_credent"){
	$pid = intval($_GET['pid'])?intval($_GET['pid']):exit("您没有选择简历！");
	$id = intval($_GET['id'])?intval($_GET['id']):exit("您没有选择证书！");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_delete_resume_credent_box.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$resumeid#}',$pid,$contents);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
// 面试邀请详情
elseif($act == "interview_detail")
{
	global $db;
	$did=$_GET['did']?intval($_GET['did']):exit('ID丢失！');
	$interview_info = $db->getone("SELECT * FROM ".table('company_interview')." WHERE did=".$did." LIMIT 1 ");
	if($interview_info)
	{
		if(empty($interview_info['notes']))
		{
			$interview_info['notes'] = '无通知内容！';
		}
		$htm = '<div class="interview-dialog dialog-block">
					<div class="dialog-item clearfix">
						<div class="d-type f-left">邀请简历：</div>
						<div class="d-content f-left">'.$interview_info['resume_name'].'</div>
					</div>
					<div class="dialog-item clearfix">
						<div class="d-type f-left">面试职位：</div>
						<div class="data-filter d-content f-left">
							<div class="dropdown">'.$interview_info['jobs_name'].'</div>
				            <input type="hidden" name="jobsid" value="" id="jobsid">
						</div>
					</div>
					<div class="dialog-item clearfix">
						<div class="d-type f-left">面试日期：</div>
						<div class="d-content f-left">'.date('Y-m-d',$interview_info['interview_addtime']).'</div>
					</div>
					<div class="dialog-item clearfix">
						<div class="d-type f-left">通知内容：</div>
						<div class="d-content f-left">'.$interview_info['notes'].'</div>
					</div>
				</div>';
		exit($htm);
	}
	else
	{
		exit('无此数据！');
	}
	
}
//订单详情
elseif($act=='order_detail')
{
	$uid = intval($_SESSION['uid']);
	$order_id = intval($_GET['order_id'])?intval($_GET['order_id']):exit("订单编号丢失！");
	$order =  $db->getone("SELECT * FROM ".table('order')." WHERE uid ='{$uid}' AND id='{$order_id}' LIMIT 1");
	$tpl='../../templates/'.$_CFG['template_dir']."member_personal/ajax_order_detail.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$order_oid#}',$order['oid'],$contents);
	$contents=str_replace('{#$order_addtime#}',date('Y-m-d',$order['addtime']),$contents);
	if($order['is_paid']=='1')
	{
		$contents=str_replace('{#$order_is_paid#}','未完成',$contents);
		$button = '<a href="?act=payment&order_id={#$order_id#}"><input type="button" value="支付" class="btn-65-30blue btn-big-font" /></a>';
		$contents=str_replace('{#$button#}',$button,$contents);
	}
	else
	{
		$contents=str_replace('{#$order_is_paid#}','已支付',$contents);
		$button = '<input type="button" value="已支付" class="btn-65-30blue btn-big-font" />';
		$contents=str_replace('{#$button#}',$button,$contents);
	}
	$contents=str_replace('{#$order_des#}',$order['description'],$contents);
	if($order['payment_name']!='points')
	{
		$contents=str_replace('{#$order_amount#}','￥'.$order['amount'],$contents);
	}
	else
	{
		$contents=str_replace('{#$order_amount#}','兑换'.$order['amount'].'积分',$contents);
	}
	$contents=str_replace('{#$order_payname#}',get_payment_info($order['payment_name'],ture),$contents);
	if($order['notes'])
	{
		$contents=str_replace('{#$order_note#}',$order['notes'],$contents);
	}
	else
	{
		$contents=str_replace('{#$order_note#}',"无",$contents);
	}
	$contents=str_replace('{#$order_id#}',$order['id'],$contents);
	exit($contents);
}
elseif($act == 'check_weixinpay_notify'){
	if(file_exists(QISHI_ROOT_PATH.'data/wxpay/'.$_SESSION['wxpay_no'].'.tmp')){
		exit('1');
	}else{
		@unlink(QISHI_ROOT_PATH.'data/wxpay/'.$_SESSION['wxpay_no'].'.tmp');
		unset($_SESSION['wxpay_no']);
		exit($_CFG['site_dir'].'user/personal/personal_service.php?act=order_list');
	}
}
unset($smarty);
?>