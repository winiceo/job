<?php
/*
 * 74cms 企业会员中心ajax弹出框
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__) . '/company_common.php');
require_once(QISHI_ROOT_PATH . 'genv/func_company.php');

if ($act == "company_profile_save_succeed") {
    $tpl = '../../templates/' . $_CFG['template_dir'] . "member_company/ajax_companyprofile_save_succeed_box.htm";
    $contents = file_get_contents($tpl);
    if ($company_profile['map_open'] == '1') {
        $save_msg = '您接下来就可以发布职位啦！ <br />';
        $opt_button = '<div class="but130cheng " onclick="javascript:location.href=\'company_jobs.php?act=addjobs\'">发布职位</div>';
    } else {
        $save_msg = '为了让求职者更直观的了解公司所在位置，合理计划 <br />面试出行路线，98%的企业已开通了电子地图。';
        $opt_button = '<div class="but130cheng" onclick="javascript:location.href=\'company_info.php?act=company_map_open\'">立即开通</div>
		<div class="but130hui but_right" onclick="javascript:location.href=\'company_jobs.php?act=addjobs\'">发布职位</div>';
	}
	$contents=str_replace('{#$save_msg#}',$save_msg,$contents);
	$contents=str_replace('{#$opt_button#}',$opt_button,$contents);
	exit($contents);
}
elseif($act=="user_email"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_email_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_email_key']=mt_rand(100000, 999999);
	$contents=str_replace('{#$email#}',$user["email"],$contents);
	$contents=str_replace('{#$site_name#}',$_CFG['site_name'],$contents);
	$contents=str_replace('{#$send_email_key#}',$_SESSION['send_email_key'],$contents);
	$contents=str_replace('{#$notice#}','接收职位申请邮件',$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}	
elseif($act=="user_mobile"){
	$tpl='../../templates/'.$_CFG['template_dir']."plus/ajax_authenticate_mobile_box.htm";
	$contents=file_get_contents($tpl);
	$_SESSION['send_mobile_key']=mt_rand(100000, 999999);
	$contents=str_replace('{#$mobile#}',$user["mobile"],$contents);
	$contents=str_replace('{#$site_name#}',$_CFG['site_name'],$contents);
	$contents=str_replace('{#$send_mobile_key#}',$_SESSION['send_mobile_key'],$contents);
	$contents=str_replace('{#$notice#}','接收职位申请通知',$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
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
	$contents=str_replace('{#$notice#}','接收职位申请通知',$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
elseif($act=="set_promotion"){
	$catid = intval($_GET['catid'])?intval($_GET['catid']):exit("参数错误！");
	$jobid = intval($_GET['jobid'])?intval($_GET['jobid']):exit("参数错误！");
	$uid = intval($_SESSION['uid'])?intval($_SESSION['uid']):exit("参数错误！");
	$jobinfo = get_jobs_one($jobid);
	$promotion = get_promotion_category_one($catid);
	if ($_CFG['operation_mode']=='2')
	{
		$setmeal=get_user_setmeal($uid);//获取会员套餐
		if($setmeal['endtime']<time() && $setmeal['endtime']<>'0'){
			$end=1;//判断套餐是否到期
			$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_promotion_end.htm";
		}else{
			$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
			$operation_mode=2;
			$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_setmeal_promotion.htm";
		}
	}
	elseif($_CFG['operation_mode']=='1')
	{
		$points = get_user_points($uid);
		$operation_mode=1;
		$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_points_promotion.htm";
	}
	elseif($_CFG['operation_mode']=='3')
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);//获取会员套餐
		if($setmeal['endtime']<time() && $setmeal['endtime']<>'0'){
			if($_CFG['setmeal_to_points']!=1){
				$end=1;//判断套餐是否到期
				$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_promotion_end.htm";
			}else{
				$operation_mode=1;
				$points = get_user_points($uid);
				$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_points_promotion.htm";
			}
		}else{
			$data=get_setmeal_promotion($uid,$catid);//获取会员某种推广的剩余条数和天数，名称，总条数
			if($data['num']<1){
				if($_CFG['setmeal_to_points']==1){
					$operation_mode=1;
					$points = get_user_points($uid);
					$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_points_promotion.htm";
				}else{
					$operation_mode=2;
					$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_setmeal_promotion.htm";
				}
			}else{
				$operation_mode=2;
				$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_setmeal_promotion.htm";
			}
		}
	}
    if ($catid == '5') {
        $balance = get_user_can_balance($uid);

        $operation_mode = 1;

        $tpl = '../../templates/' . $_CFG['template_dir'] . "member_company/ajax_set_balance_promotion.htm";
    }
	$contents=file_get_contents($tpl);
	if($end!=1){
		if($catid=="4"){
			$color = get_color();
			$color_list = '<tr>
				<td height="50">选择颜色：</td>
				<td>
					<div style="position:relateve;">
	             	 	<div id="val_menu" class="input_text_70_bg">请选择</div>	
	             	 	<div class="menu" id="menu1">
		              		<ul style="width:88px;">';
			foreach ($color as $key => $value) {
				$color_list.='<li id="'.$value["id"].'" title="'.$value["value"].'" style="background-color:'.$value["value"].'">&nbsp;</li>';
			}
			$color_list.='</ul>
		              	</div>
		            </div>
		            <input type="hidden" name="val" value="" id="val">
				</td>
				<td></td>
			</tr>';
			$contents=str_replace('{#$color_list#}',$color_list,$contents);
		}else{
			$contents=str_replace('{#$color_list#}',"",$contents);
		}
		$contents=str_replace('{#$jobid#}',$jobid,$contents);
		$contents=str_replace('{#$catid#}',$catid,$contents);
		$contents=str_replace('{#$jobs_name#}',$jobinfo['jobs_name'],$contents);
		$contents=str_replace('{#$promotion_name#}',$promotion['cat_name'],$contents);
		$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
		if($operation_mode==1){
			if($promotion['cat_minday']=="0"){
				$promotion['cat_minday'] = "不限制";
			}
			if($promotion['cat_maxday']=="0"){
				$promotion['cat_maxday'] = "不限制";
			}
			if($promotion['cat_points']=="0"){
				$promotion['cat_points'] = "免费";
			}
			$contents=str_replace('{#$user_points#}',$points,$contents);
			$contents=str_replace('{#$points_perday#}',$promotion['cat_points'],$contents);
			$contents=str_replace('{#$points_quantifier#}',$_CFG['points_quantifier'],$contents);
			$contents=str_replace('{#$points_byname#}',$_CFG['points_byname'],$contents);
			$contents=str_replace('{#$cat_minday#}',$promotion['cat_minday'],$contents);
			$contents=str_replace('{#$cat_maxday#}',$promotion['cat_maxday'],$contents);
            if ($catid == 5) {
                $contents = str_replace('{#$cat_notes#}', htmlspecialchars_decode($promotion['cat_notes']), $contents);
                $contents = str_replace('{#$balance#}', $balance, $contents);

            }
		}elseif($operation_mode==2){
			$contents=str_replace('{#$days#}',$data['days'],$contents);
			$contents=str_replace('{#$setmeal_name#}',$setmeal['setmeal_name'],$contents);
			$contents=str_replace('{#$num#}',$data['num'],$contents);
			$contents=str_replace('{#$pro_name#}',$data['name'],$contents);
			$contents=str_replace('{#$cat_minday#}',$promotion['cat_minday'],$contents);
			$contents=str_replace('{#$cat_maxday#}',$promotion['cat_maxday'],$contents);
		}
	}
	exit($contents);
}elseif ($act == "set_date") {
    $catid = intval($_GET['catid']) ? intval($_GET['catid']) : exit("参数错误！");
    $jobid = intval($_GET['jobid']) ? intval($_GET['jobid']) : exit("参数错误！");
    $uid = intval($_SESSION['uid']) ? intval($_SESSION['uid']) : exit("参数错误！");
    $jobinfo = get_jobs_one($jobid);
    $promotion_category = get_promotion_category_one($catid);
    $promotion = get_promotion_one($jobid,$uid,$catid);

    $tpl = '../../templates/' . $_CFG['template_dir'] . "member_company/ajax_set_date_promotion.htm";
    $contents = file_get_contents($tpl);
    if($catid==5){
         $json=str_replace('&quot;', '"', trim($promotion["cp_json"]));
        $block_balance=json_decode($json)->block_balance;

        $other='<tr>
            <td height="50">冻结金额：</td>
            <td>'.$block_balance.'</td>
            <td> </td>
        </tr>';
    }else{
        $other='<tr>
            <td height="50">推广期限：</td>
            <td>从 '.date("Y-m-d", $promotion["cp_starttime"]).' 到 '.date("Y-m-d", $promotion["cp_endtime"]).'</td>

        </tr>';
    }
    $contents = str_replace('{#$cp_val#}', $promotion["cp_val"], $contents);
    $contents = str_replace('{#$other#}', $other, $contents);
    $contents = str_replace('{#$jobs_name#}', $jobinfo['jobs_name'], $contents);
    $contents = str_replace('{#$promotion_name#}', $promotion_category['cat_name'], $contents);



    exit($contents);
} 
elseif ($act == "promotion_add_save") {
    $catid = intval($_POST['catid']) ? intval($_POST['catid']) : exit("请选择推广类型！");
    $jobid = intval($_POST['jobid']) ? intval($_POST['jobid']) : exit("职位id丢失！");
    $days = intval($_POST['days']) ? intval($_POST['days']) : exit("请填写推广天数！");
    $uid = intval($_SESSION['uid']) ? intval($_SESSION['uid']) : exit("UID丢失！");

	if($catid==4){
		$val = intval($_POST['val'])?intval($_POST['val']):exit("请选择颜色！");
		$color = get_color_one($val);
		$val_code = $color['value'];
	}else{
		$val_code = "";
	}
	$jobs=get_jobs_one($jobid,$uid);
	$jobs = array_map("addslashes", $jobs);
	if($jobs['deadline']<time()){
		exit("该职位已到期，请先延期！");
	}
	if ($jobid>0 && $days>0)
	{
		$pro_cat=get_promotion_category_one($catid);
		if($_CFG['operation_mode']=='3'){
			$setmeal=get_setmeal_promotion($uid,$catid);//获取会员套餐
			$num=$setmeal['num'];
			if(($setmeal['endtime']<time() && $setmeal['endtime']<>'0') || $num<=0){
				if($_CFG['setmeal_to_points']==1){
					if ($pro_cat['cat_points']>0)
					{
						$points=$pro_cat['cat_points']*$days;
						$user_points=get_user_points($uid);
						if ($points>$user_points)
						{
							exit("你的".$_CFG['points_byname']."不够进行此次操作，请先充值！");
						}else{
							$_CFG['operation_mode']=1;
						}
					}else{
						$_CFG['operation_mode']=1;
					}
				}else{
					exit("你的套餐已到期或套餐内剩余{$pro_cat['cat_name']}不够，请尽快开通新套餐");
				}
			}else{
				$_CFG['operation_mode']=2;
			}
		}elseif($_CFG['operation_mode']=='1'){
			if ($pro_cat['cat_points']>0)
			{
				$points=$pro_cat['cat_points']*$days;
				$user_points=get_user_points($uid);
				if ($points>$user_points)
				{
				exit("你的".$_CFG['points_byname']."不够进行此次操作，请先充值！");
				}
			}
		}elseif($_CFG['operation_mode']=='2'){
			$setmeal=get_setmeal_promotion($uid,$catid);//获取会员套餐
			$num=$setmeal['num'];
			if(($setmeal['endtime']<time() && $setmeal['endtime']<>'0') || $num<=0){
				exit("你的套餐已到期或套餐内剩余{$pro_cat['cat_name']}不够，请尽快开通新套餐");
			}
		}
		$info=get_promotion_one($jobid,$uid,$catid);
		if (!empty($info))
		{
		exit("此职位正在推广中，请选择其他职位或其他方案");
		}
		$setsqlarr['cp_available']=1;
		$setsqlarr['cp_promotionid']=$catid;
		$setsqlarr['cp_uid']=$uid;
		$setsqlarr['cp_jobid']=$jobid;
		$setsqlarr['cp_days']=$days;
		$setsqlarr['cp_starttime']=time();
		$setsqlarr['cp_endtime']=strtotime("{$days} day");
		$setsqlarr['cp_val']=$val_code;
		if ($setsqlarr['cp_promotionid']=="4" && empty($setsqlarr['cp_val']))
		{
		exit("请选择颜色！");
		}
			if ($db->inserttable(table('promotion'),$setsqlarr))
			{
				set_job_promotion($jobid,$setsqlarr['cp_promotionid'],$val_code);
				if ($_CFG['operation_mode']=='1' && $pro_cat['cat_points']>0)
				{
					report_deal($uid,2,$points);
					$user_points=get_user_points($uid);
					write_memberslog($uid,1,9001,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$days} 天，(-{$points})，(剩余:{$user_points})",1,1018,"{$pro_cat['cat_name']}","-{$points}","{$user_points}");
				}elseif($_CFG['operation_mode']=='2'){
					$user_pname=trim($_POST['pro_name']);
					action_user_setmeal($uid,$user_pname); //更新套餐中相应推广方式的条数
					$setmeal=get_user_setmeal($uid);//获取会员套餐
					write_memberslog($uid,1,9002,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$days} 天，套餐内剩余{$pro_cat['cat_name']}条数：{$setmeal[$user_pname]}条。",2,1018,"{$pro_cat['cat_name']}","-{$days}","{$setmeal[$user_pname]}");//9002是套餐操作
				}
				write_memberslog($uid,1,3004,$_SESSION['username'],"{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，推广 {$days} 天。");
				exit('推广成功！');
			}
	}
	else
	{
	exit("推广失败！");
	}
}

elseif ($act == "reward_add_save") {
    //悬赏招聘添加
    $_POST = $_GET;
    $catid = intval($_POST['catid']) ? intval($_POST['catid']) : exit("请选择推广类型！");
    $jobid = intval($_POST['jobid']) ? intval($_POST['jobid']) : exit("职位id丢失！");
    $interview_num = intval($_POST['interview_num']) ? intval($_POST['interview_num']) : exit("请填写面试人数！");
    $interview_money = intval($_POST['interview_money']) ? intval($_POST['interview_money']) : exit("请填写面试金额！");
    $interview_success_money = intval($_POST['interview_success_money']) ? intval($_POST['interview_success_money']) : exit("请填写面试成功金额！");


    $uid = intval($_SESSION['uid']) ? intval($_SESSION['uid']) : exit("UID丢失！");
    $val_code = "";
    //可用金额
    $can_balance = get_user_can_balance($uid);

    $jobs = get_jobs_one($jobid, $uid);
    $jobs = array_map("addslashes", $jobs);

    if ($jobs['deadline'] < time()) {
        exit("该职位已到期，请先延期！");
    }
    if ($jobid > 0) {
        $pro_cat = get_promotion_category_one($catid);

        if ($pro_cat["cat_minday"] > $interview_num) {
            exit("面试人数有误！");
        }
        if ($pro_cat["cat_maxday"] > $interview_money) {
            exit("面试金额有误！");
        }
        if ($pro_cat["cat_points"] > $interview_success_money) {
            exit("面试成功金额有误！");
        }
        $block_balance = $interview_num * $interview_money + $interview_success_money;


        if ($block_balance > $can_balance) {
            exit("余额不足！");
        }

        $info = get_promotion_one($jobid, $uid, $catid);
        if (!empty($info)) {
            exit("此职位正在推广中，请选择其他职位或其他方案");
        }
        $setsqlarr['cp_available'] = 1;
        $setsqlarr['cp_promotionid'] = $catid;
        $setsqlarr['cp_uid'] = $uid;
        $setsqlarr['cp_jobid'] = $jobid;


        $json=array();
        $json["interview_num"]=$interview_num;
        $json["interview_money"]=$interview_money;
        $json["interview_success_money"]=$interview_success_money;
        $json["block_balance"]=$block_balance;

        $setsqlarr['cp_json'] = json_encode($json);
        $db->inserttable(table('promotion'), $setsqlarr);
        $setsqlarr['addtime'] = time();
        $setsqlarr['interview_num'] = $interview_num;
        $setsqlarr['interview_money'] = $interview_money;
        $setsqlarr['interview_success_money'] = $interview_success_money;

        if ($db->inserttable(table('jobs_reward'), $setsqlarr)) {
            //标注简历推广
            set_job_reward($jobid, $setsqlarr['cp_promotionid'], $val_code);
            //锁定金额
            block_balance_reward($uid, $block_balance);
            $can_balance = get_user_can_balance($uid);
            write_memberslog($uid, 1, 9200, $_SESSION['username'], "{$pro_cat['cat_name']}：<strong>{$jobs['jobs_name']}</strong>，悬赏简历 冻结 {$block_balance} ，(可用:{$can_balance})", 1, 1018, "{$pro_cat['cat_name']}", "-{$block_balance}", "{$can_balance}");
            exit('推广成功！');
        }
    } else {
        exit("推广失败！");
    }
}


//备注 风采图片
elseif($act=='img_title')
{
	global $_CFG;
	$id = intval($_GET['id']);
	$uid = intval($_SESSION['uid']);
	$img =  $db->getone("SELECT * FROM ".table('company_img')." WHERE uid ='{$uid}' AND id='{$id}' LIMIT 1");
	$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_set_img_title.htm";
	$contents=file_get_contents($tpl);
	$contents=str_replace('{#$id#}',$id,$contents);
	$contents=str_replace('{#$title#}',$img['title'],$contents);
	$contents=str_replace('{#$addtime#}',date('Y-m-d',$img['addtime']),$contents);
	$contents=str_replace('{#$site_template#}',$_CFG['site_template'],$contents);
	exit($contents);
}
//保存 备注 风采图片
elseif($act=='img_title_save')
{
	$id = intval($_POST['id'])?intval($_POST['id']):exit("-1");
	$title = trim($_POST['title'])?utf8_to_gbk($_POST['title']):exit("-1");
	if($db->query("update  ".table('company_img')." SET title='{$title}'  WHERE id='{$id}'  LIMIT 1"))
	{
		exit('1');
	} 
	else
	{
		exit('2');
	}
}
//订单详情
elseif($act=='order_detail')
{
	$uid = intval($_SESSION['uid']);
	$order_id = intval($_GET['order_id'])?intval($_GET['order_id']):exit("订单编号丢失！");
	$order =  $db->getone("SELECT * FROM ".table('order')." WHERE uid ='{$uid}' AND id='{$order_id}' LIMIT 1");
	$tpl='../../templates/'.$_CFG['template_dir']."member_company/ajax_order_detail.htm";
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
//  简历发送到邮箱
elseif($act == "sendtoemail")
{
	global $_CFG;
	$uid=intval($_GET['uid']);
	$resume_id=intval($_GET['resume_id']);
	$email=trim($_GET['email']);
	$resume_basic=get_resume_basic($resume_id);
	if($resume_basic['tag_cn'])
	{
		$resume_tag=explode(',',$resume_basic['tag_cn']);
		$tag_str='<p>';
		foreach ($resume_tag as $value)
		{
			$tag_str.='<span style="color: #656565;display:inline-block;background-color: #f2f4f7; border: 1px solid #d6d6d7;text-align: center;height:30px;line-height: 30px;margin-right:10px;padding:0 10px">'.$value.'</span>';
		}
		$tag_str.='</p>';
	}
	$resume_work=get_resume_work($uid,$resume_id);
	$show_contact = false;
	if($_CFG['showapplycontact']=='1' || $_CFG['showresumecontact']=='0')
	{
		$show_contact = '<p>手机号码：'.$resume_basic["telephone"].' 电子邮箱：'.$resume_basic["email"].'</p>';
	}
	else
	{
		$show_contact = '<p>联系方式：<a href='.url_rewrite('QS_resumeshow',array('id'=>$resume_id)).'>点击查看</a></p>';
	}	
	$htm='<div style="width: 900px;margin: 0 auto;font-size: 14px;">
		<div style="margin-bottom:10px">
			<div style="float: left;"><a href="'.$_CFG['site_dir'].'"><img src="'.$_CFG['site_domain'].$_CFG['upfiles_dir'].$_CFG['web_logo'].'" alt="'.$_CFG['site_name'].'" border="0" align="absmiddle" width=180 height=50 /></div>
			<div style="float: right;padding-top:10px;">'.$templates.'更新时间：'.date("Y-m-d",$resume_basic["refreshtime"]).'</div>
			<div style="clear:both"></div>
		</div>
		<div style="padding-bottom: 10px;">
			<span style="font-size: 18px;font-weight: 700;">'.$resume_basic["fullname"].'</span><span>（'.$resume_basic["sex_cn"].'，'.$resume_basic["age"].'）</span>
			<p>学历：'.$resume_basic["education_cn"].' | 专业：'.$resume_basic["major_cn"].' | 工作经验：'.$resume_basic["experience_cn"].'年 | 现居住地：'.$resume_basic["residence"].'</p>

			'.$show_contact.$tag_str.'

		</div>
		<div style="padding-bottom: 10px;">
			<p style="font-size: 16px;font-weight: 700;">求职意向</p>
			<p>期望职位：'.$resume_basic["intention_jobs"].'</p>
			<p>期望薪资：'.$resume_basic["wage_cn"].'</p>
			<p>期望地区：'.$resume_basic["district_cn"].'</p>
		</div>
		<div style="padding-bottom: 10px;">
			<p style="font-size: 16px;font-weight: 700;">工作经验</p>';
				if(!empty($resume_work))
				{
					foreach ($resume_work as $value)
					{
						$htm.='<div>
								<p style="font-size: 14px;font-weight: 700;">'.$value["companyname"].'</p>
								<p>'.$value["startyear"].'年'.$value["startmonth"].'月-'.$value["endyear"].'年'.$value["endmonth"].'月 '.$value["jobs"].'</p>
								<div style="float: left;width: 100px;">工作内容：</div>
								<div style="float: right;width: 800px;">'.$value["achievements"].'</div>
								<div style="clear:both"></div>
							</div>'	;
					}
				}
				else
				{
					$htm.='<div>
								没有填写工作经历
							</div>'	;
				}
				
		$htm.='</div>';
		if($resume_basic["specialty"])
		{
			$htm.='<div style="padding-bottom: 10px;">
				<p style="font-size: 16px;font-weight: 700;">自我描述</p>
				<p>'.$resume_basic["specialty"].'</p>
			</div>';
		}
		$htm.='<div style="text-align: center;margin-top:20px">
				该简历来自<a href="'.$_CFG["site_domain"].$_CFG["site_dir"].'">'.$_CFG["site_name"].'</a>
			</div>
		</div>';
		$rst=smtp_mail($_GET['email'],"{$resume_basic['fullname']}的简历",$htm);
		exit($rst);
}
// 预约详情
elseif($act == "auto_refresh")
{
	global $db;
	$id=$_GET['id']?intval($_GET['id']):exit('ID丢失！');
	$user_points=get_user_points($_SESSION['uid']);
	$row=$db->getone("select * from ".table("jobs")." where id=$id and uid=$_SESSION[uid] limit 1 ");
	/*  预约刷新  */
	if($row['auto_refresh']==1)
	{
		$auto_refresh=$db->getone('select appointment_time,appointment_time_available,points,execute_time from '.table("jobs_appointment_refresh")." where jobs_id=$row[id] limit 1");
		$row['auto_refresh_num_all']=$auto_refresh['appointment_time'];
		$row['auto_refresh_num']=$auto_refresh['appointment_time_available'];
		$row['points']=$auto_refresh['points'];
		$row['points_day']=$auto_refresh['points']/=$auto_refresh['appointment_time'];
		$taday=date('Y-m-d');
		$is_taday=date("Y-m-d",$auto_refresh['execute_time']);
		if($taday==$is_taday)
		{
			$row['auto_refresh_day']="今天已经为您自动刷新职位";
		}
		else
		{
			$row['auto_refresh_day']="今天还没有为您自动刷新职位";
		}
	}

	$htm='<div class="yuyue-one-dialog">
				<div class="yo-block">
					<div class="short-text-tip" style="margin-left:0">您的账户剩余 <span>'.$user_points.' 积分,</span> '.$row['auto_refresh_day'].',还可以为您自动刷新'.$row['auto_refresh_num'].'次。</div>
					<div class="yue-one-item">
						<span class="yo-type">预约职位：</span><font>'.$row['jobs_name'].'</font>
					</div>
					<div class="yue-one-item">
						<span class="yo-type">刷新次数：</span>1次/天
					</div>
					<div class="yue-one-item">
						<span class="yo-type">预约天数：</span>'.$row['auto_refresh_num_all'].' 天
					</div>
					<div class="yue-one-item">
						<span class="yo-type">消耗积分：</span> '.$row['points'].'分<span class="use-fen">（每天消耗'.$row['points_day'].'积分）</span>
					</div>
				</div>
			</div>';
	exit($htm);
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
elseif($act == 'check_weixinpay_notify'){
	if(file_exists(QISHI_ROOT_PATH.'data/wxpay/'.$_SESSION['wxpay_no'].'.tmp')){
		exit('1');
	}else{
		@unlink(QISHI_ROOT_PATH.'data/wxpay/'.$_SESSION['wxpay_no'].'.tmp');
		unset($_SESSION['wxpay_no']);
		exit($_CFG['site_dir'].'user/company/company_service.php?act=order_list');
	}
}
// 职位刷新ajax
elseif($act == "jobs_refresh_ajax")
{
	global $db,$_CFG;
	$jobsid=$_GET['jobsid']?intval($_GET['jobsid']):exit('职位ID丢失！');
	$jobs_info = get_jobs_one($jobsid,$_SESSION['uid']);
	if($jobs_info['deadline']<time())
	{
		exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">该职位已到期！</span></div></div>');
	}
	if($_CFG['operation_mode']=='1')
	{
		$mode = 1;
	}
	elseif($_CFG['operation_mode']=='2')
	{
		$mode = 2;
	}
	elseif($_CFG['operation_mode']=='3') 
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);
		//该会员套餐过期 (套餐过期后就用积分来刷)
		if($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{
			//后台开通  服务超限时启用积分消费
			if($_CFG['setmeal_to_points']=='1')
			{
				$mode = 1;
			}
			//后台没有开通  服务超限时启用积分消费
			else
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您的服务已经到期，请重新开通</span></div></div>');
			}
		}
		//该会员套餐未过期 
		else
		{
			$points_rule=get_cache('points_rule');
			$user_points=get_user_points($_SESSION['uid']);
			//获取当天刷新的职位数(在套餐模式下刷新的)
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
			//当天剩余刷新职位数(在套餐模式下刷新的)
			$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
			//刷新职位数 大于 剩余刷新职位数 (超了)
			if($surplus_time <= 0)
			{
				//后台开通  服务超限时启用积分消费
				if($_CFG['setmeal_to_points']=='1')
				{
					$mode = 1;
				}
				else
				{
					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您的服务已经到期，请重新开通</span></div></div>');
				}
			}
			else
			{
				$mode = 2;
			}
		}
	}
	//积分模式
	if($mode=='1')
	{
		//限制刷新时间
		//最近一次的刷新时间
		$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",1);
		$duringtime=time()-$refrestime['max(addtime)'];
		$space = $_CFG['com_pointsmode_refresh_space']*60;
		$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",1);
		if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
		{
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！今日刷新次数已达上限，想要招人快？ <a href="company_jobs.php?act=jobs" class="underline">职位推广</a> 效果提升5倍！</span></div></div>');	
		}
		elseif($duringtime<=$space)
		{
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">'.$_CFG['com_pointsmode_refresh_space'].'分钟内不能重复刷新职位！</span></div></div>');
		}
		else 
		{
			$points_rule=get_cache('points_rule');
			if($points_rule['jobs_refresh']['value']>0)
			{
				$user_points=get_user_points($_SESSION['uid']);
				$total_point=1*$points_rule['jobs_refresh']['value'];
				if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
				{
					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！您的剩余'.$_CFG['points_byname'].'不足，无法进行此操作，请立即 <a href="company_service.php?act=order_add" class="underline">充值</a>！</span></div></div>');
				}
				else
				{
					// 不限次数
					if($_CFG['com_pointsmode_refresh_time']==0)
					{
						exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">本次刷新需要消耗<span style="color:#ff9900;">'.$points_rule['jobs_refresh']['value'].'</span>'.$_CFG['points_byname'].'，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
					}
					else
					{
						//剩余次数
						$surplus = intval($_CFG['com_pointsmode_refresh_time']) - intval($refresh_time['count(*)']);
						exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天还可用积分刷新<span style="color:#ff9900;">'.$surplus.'</span>次，本次刷新需要消耗<span style="color:#ff9900;">'.$points_rule['jobs_refresh']['value'].'</span>'.$_CFG['points_byname'].'，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
					}
				}
			}
			else
			{
				// 不限次数
				if($_CFG['com_pointsmode_refresh_time']==0)
				{

					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
				}
				else
				{
					//剩余次数
					$surplus = intval($_CFG['com_pointsmode_refresh_time']) - intval($refresh_time['count(*)']);
					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天还可用积分刷新<span style="color:#ff9900;">'.$surplus.'</span>次，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
				}
			}
		}
	}
	//套餐模式
	elseif($mode=='2') 
	{
		
		//限制刷新时间
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if (empty($setmeal))
		{			
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您还没有开通服务，请开通服务</span></div></div>');
		}
		elseif ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{			
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您的服务已经到期，请重新开通</span></div></div>');
		}
		else
		{
			//最近一次的刷新时间
			$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",2);
			$duringtime=time()-$refrestime['max(addtime)'];
			$space = $setmeal['refresh_jobs_space']*60;
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
			if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！今日刷新次数已达上限，想要招人快？ <a href="company_jobs.php?act=jobs" class="underline">职位推广</a> 效果提升5倍！</span></div></div>');
			}
			elseif($duringtime<=$space)
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">'.$setmeal['refresh_jobs_space'].'分钟内不能重复刷新职位！</span></div></div>');
			}
			else
			{
				//剩余次数
				$surplus = intval($setmeal['refresh_jobs_time']) - intval($refresh_time['count(*)']);
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天免费刷新次数剩余<span style="color:#ff9900;">'.$surplus.'</span>次，本次刷新<span style="color:#ff9900;">免费</span>，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
			}
		}
	}
}
// 批量职位刷新ajax
elseif($act == "jobs_all_refresh_ajax")
{
	global $db,$_CFG;
	$length=$_GET['length']?intval($_GET['length']):exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">请选择职位！</span></div></div>');
	if($_CFG['operation_mode']=='1')
	{
		$mode = 1;
	}
	elseif($_CFG['operation_mode']=='2')
	{
		$mode = 2;
	}
	elseif($_CFG['operation_mode']=='3') 
	{
		$setmeal=get_user_setmeal($_SESSION['uid']);
		//该会员套餐过期 (套餐过期后就用积分来刷)
		if($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您的服务已经到期，请重新开通</span></div></div>');
		}
		//该会员套餐未过期 
		else
		{
			$points_rule=get_cache('points_rule');
			$user_points=get_user_points($_SESSION['uid']);
			//获取当天刷新的职位数(在套餐模式下刷新的)
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
			//当天剩余刷新职位数(在套餐模式下刷新的)
			$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
			//刷新职位数 大于 剩余刷新职位数 (超了)
			if($setmeal['refresh_jobs_time']!=0&&($length>$surplus_time))
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">您批量刷新职位数超过了套餐剩余数，请单个职位刷新操作！</span></div></div>');
			}
			else
			{
				$mode = 2;
			}
		}
	}
	//积分模式
	if($mode=='1')
	{
		//限制刷新时间
		//最近一次的刷新时间
		$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",1);
		$duringtime=time()-$refrestime['max(addtime)'];
		$space = $_CFG['com_pointsmode_refresh_space']*60;
		$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",1);
		$surplus_time =  $_CFG['com_pointsmode_refresh_time'] - $refresh_time['count(*)'];
		if($_CFG['com_pointsmode_refresh_time']!=0&&($length>$surplus_time))
		{
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！您批量刷新职位数超过了积分刷新剩余数，请单个职位刷新操作！</span></div></div>');	
		}
		elseif($duringtime<=$space)
		{
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">'.$_CFG['com_pointsmode_refresh_space'].'分钟内不能重复刷新职位！</span></div></div>');
		}
		else 
		{
			$points_rule=get_cache('points_rule');
			if($points_rule['jobs_refresh']['value']>0)
			{
				$user_points=get_user_points($_SESSION['uid']);
				$total_point=$length*$points_rule['jobs_refresh']['value'];
				if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
				{
					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！您的剩余'.$_CFG['points_byname'].'不足，无法进行此操作，请立即 <a href="company_service.php?act=order_add" class="underline">充值</a>！</span></div></div>');
				}
				else
				{
					// 不限次数
					if($_CFG['com_pointsmode_refresh_time']==0)
					{
						exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">本次刷新需要消耗<span style="color:#ff9900;">'.$length*$points_rule['jobs_refresh']['value'].'</span>'.$_CFG['points_byname'].'，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
					}
					else
					{
						//剩余次数
						$surplus = intval($_CFG['com_pointsmode_refresh_time']) - intval($refresh_time['count(*)']);
						exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天还可用积分刷新<span style="color:#ff9900;">'.$surplus.'</span>次，本次刷新需要消耗<span style="color:#ff9900;">'.$length*$points_rule['jobs_refresh']['value'].'</span>'.$_CFG['points_byname'].'，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
					}
				}
			}
			else
			{
				// 不限次数
				if($_CFG['com_pointsmode_refresh_time']==0)
				{

					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
				}
				else
				{
					//剩余次数
					$surplus = intval($_CFG['com_pointsmode_refresh_time']) - intval($refresh_time['count(*)']);
					exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天还可用积分刷新<span style="color:#ff9900;">'.$surplus.'</span>次，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
				}
			}
		}
	}
	//套餐模式
	elseif($mode=='2') 
	{
		//限制刷新时间
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if (empty($setmeal))
		{			
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您还没有开通服务，请开通服务</span></div></div>');
		}
		elseif ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{			
			exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">您的服务已经到期，请重新开通</span></div></div>');
		}
		else
		{
			//最近一次的刷新时间
			$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",2);
			$duringtime=time()-$refrestime['max(addtime)'];
			$space = $setmeal['refresh_jobs_space']*60;
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
			$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
			if($setmeal['refresh_jobs_time']!=0&&($length>$surplus_time))
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">抱歉！您批量刷新职位数超过了套餐刷新剩余数，请单个职位刷新操作！</span></div></div>');	
			}
			elseif($duringtime<=$space)
			{
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">'.$setmeal['refresh_jobs_space'].'分钟内不能重复刷新职位！</span></div></div>');
			}
			else
			{
				//剩余次数
				$surplus = intval($setmeal['refresh_jobs_time']) - intval($refresh_time['count(*)']);
				exit('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text" style="line-height:30px;">今天免费刷新次数剩余<span style="color:#ff9900;">'.$surplus.'</span>次，本次刷新<span style="color:#ff9900;">免费</span>，确定要刷新吗？</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
			}
		}
	}
}
unset($smarty);
?>