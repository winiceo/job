<?php
 /*
 * 74cms 个人
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
require_once(ADMIN_ROOT_PATH.'include/admin_personal_fun.php');
require_once(QISHI_ROOT_PATH.'include/fun_weixin.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'list';
if($act == 'list')
{
	get_token();
	check_permissions($_SESSION['admin_purview'],"resume_show");
	$tabletype=intval($_GET['tabletype']);
	$audit=intval($_GET['audit']);
	if (empty($tabletype))
	{
		$tabletype=1;
		$_GET['tabletype']=1;
	}
	if ($tabletype==1)
	{
	$audit="";
	}
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$oederbysql=" order BY refreshtime DESC ";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		
		if     ($key_type===1)$wheresql=" WHERE fullname like '%{$key}%'";
		elseif ($key_type===2)$wheresql=" WHERE id='".intval($key)."'";
		elseif ($key_type===3)$wheresql=" WHERE uid='".intval($key)."'";	
		elseif ($key_type===4)$wheresql=" WHERE telephone like '%{$key}%'";	
		elseif ($key_type===5)$wheresql=" WHERE qq like '%{$key}%'";
		elseif ($key_type===6)$wheresql=" WHERE address like '%{$key}%'";
		$oederbysql="";
		$tablename="all";
	}
	else
	{
		$photo_audit=intval($_GET['photo_audit']);
		!empty($audit)? $wheresqlarr['audit']=$audit:'';
		!empty($_GET['talent'])? $wheresqlarr['talent']=intval($_GET['talent']):'';
		if ($photo_audit>0)
		{
			$wheresqlarr['photo_audit']=$photo_audit;
			$oederbysql="";
		}
		if ($_GET['photo']<>'')
		{
		$wheresqlarr['photo']=intval($_GET['photo']);
		$oederbysql=" order BY addtime DESC ";
		}
		if ($_GET['photo_display']<>'')
		{
		$wheresqlarr['photo_display']=intval($_GET['photo_display']);
		$oederbysql=" order BY addtime DESC ";
		}
		if (is_array($wheresqlarr)) $wheresql=wheresql($wheresqlarr);	
		if (!empty($_GET['addtimesettr']))
		{
			$settr=strtotime("-".intval($_GET['addtimesettr'])." day");
			$wheresql=empty($wheresql)?" WHERE addtime> ".$settr:$wheresql." AND addtime> ".$settr;
			$oederbysql=" order BY addtime DESC ";
		}
		if (!empty($_GET['settr']))
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql=empty($wheresql)?" WHERE refreshtime> ".$settr:$wheresql." AND refreshtime> ".$settr;
		}
		//是否委托
		if(!empty($_GET['entrust'])){
			//委托了一个月的
			if($_GET['entrust']=='3'){
				$now = time();
				$wheresql=empty($wheresql)?" WHERE entrust = 3":$wheresql." AND  entrust = 3";
			}
			//委托了两周的
			elseif($_GET['entrust']=='2'){
				$now = time();
				$wheresql=empty($wheresql)?" WHERE entrust = 2":$wheresql." AND  entrust = 2";
			}
			//委托了一周的
			else{
				$now = time();
				$wheresql=empty($wheresql)?" WHERE entrust = 1":$wheresql." AND  entrust = 1";
			}
		}
	}
	if ($tablename=="all")
	{
	$total_sql="SELECT COUNT(*) AS num FROM ".table('resume').$wheresql;
	}
	else
	{
		if($tabletype==1){
			$wheresql=empty($wheresql)?" WHERE display=1 AND audit=1":$wheresql." AND display=1 AND audit=1";
		}elseif($tabletype==2){
			if(!empty($audit)){
				if($audit==1){
					$wheresql=$wheresql." AND display=2 ";
				}
			}else{
				$wheresql=empty($wheresql)?" WHERE display<>1 OR (display=1 AND audit<>1) ":$wheresql." AND display<>1 OR (display=1 AND audit<>1) ";
			}
			
		}
		$total_sql="SELECT COUNT(*) AS num FROM ".table('resume').$wheresql;
	}
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	if ($tablename=="all")
	{
	$getsql="SELECT * FROM ".table('resume').$wheresql;
	}
	else
	{
		if($tabletype==1){
			$wheresql=empty($wheresql)?" WHERE display=1 AND audit=1":$wheresql." AND display=1 AND audit=1";
		}elseif($tabletype==2){
			if(!empty($audit)){
				if($audit==1){
					$wheresql=$wheresql." AND display=2 ";
				}
			}else{
				$wheresql=empty($wheresql)?" WHERE display=2 OR (display=1 AND audit<>1) ":$wheresql." AND display=2 OR (display=1 AND audit<>1) ";
			}
			
		}
		$getsql="SELECT * FROM ".table('resume')." ".$wheresql.$oederbysql;
	}
	$resumelist = get_resume_list($offset,$perpage,$getsql);
	$total_all_resume = $db->get_total("SELECT COUNT(*) AS num FROM ".table('resume'));
	$total[0]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('resume')." where display=1 and audit=1");
	$total[1]=$total_all_resume-$total[0];
	if ($tabletype===2)
	{
	$total[2]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('resume')." WHERE audit=1 AND display=2");
	$total[3]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('resume')." WHERE audit=2 ");
	$total[4]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('resume')." WHERE audit=3 ");
	}
	$smarty->assign('total',$total);
	$smarty->assign('pageheader',"简历列表");
	$smarty->assign('resumelist',$resumelist);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('total_val',$total_val);
	$smarty->display('personal/admin_personal_resume.htm');
}if($act == 'list_temp')
{
    get_token();
    check_permissions($_SESSION['admin_purview'],"resume_show");
    require_once(QISHI_ROOT_PATH . '/genv/func_resume_upload.php');

    require_once(QISHI_ROOT_PATH.'include/page.class.php');
    $oederbysql=" order BY id DESC ";
    $wheresql=" ";

    $total_sql="SELECT COUNT(*) AS num FROM ".table('resume_temp').$wheresql;

    $total_val=$db->get_total($total_sql);
    $page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
    $currenpage=$page->nowindex;
    $offset=($currenpage-1)*$perpage;

    $getsql="SELECT * FROM ".table('resume_temp')." ".$wheresql.$oederbysql;

    $resumelist = get_resume_temp_list($offset,$perpage,$getsql);


    $smarty->assign('pageheader',"简历列表");
    $smarty->assign('resumelist',$resumelist);
    $smarty->assign('page',$page->show(3));
    $smarty->assign('total_val',$total_val);
    $smarty->display('personal/admin_personal_resume_temp.htm');
}elseif($act == 'perform_temp')
{



    require_once(QISHI_ROOT_PATH . '/genv/func_resume_upload.php');

    check_token();
    $id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择简历！",1);
    if (!empty($_REQUEST['delete']))
    {
        check_permissions($_SESSION['admin_purview'],"resume_del");
        if ($n=del_resume_temp($id))
        {
            adminmsg("删除成功！该删除 {$n} 行",2);
        }
        else
        {
            adminmsg("删除失败！",0);
        }
    }
    if (!empty($_POST['set_audit']))
    {
        check_permissions($_SESSION['admin_purview'],"resume_audit");

        if (!is_array($id))  $id=array($id);

        foreach($id as $item){

            import_resume_temp($item);
        }
        adminmsg("设置成功！",1);
    }
}
elseif($act == 'perform')
{
		check_token();
		$id =!empty($_REQUEST['id'])?$_REQUEST['id']:adminmsg("你没有选择简历！",1);
		if (!empty($_REQUEST['delete']))
		{
			check_permissions($_SESSION['admin_purview'],"resume_del");
			if ($n=del_resume($id))
			{
			adminmsg("删除成功！该删除 {$n} 行",2);
			}
			else
			{
			adminmsg("删除失败！",0);
			}
		}
		if (!empty($_POST['set_audit']))
		{
			check_permissions($_SESSION['admin_purview'],"resume_audit");
			$audit=$_POST['audit'];
			$pms_notice=intval($_POST['pms_notice']);
			$reason=trim($_POST['reason']);
			!edit_resume_audit($id,$audit,$reason,$pms_notice)?adminmsg("设置失败！",0):adminmsg("设置成功！",2,$link);
		}
		
		if (!empty($_POST['set_talent']))
		{
		check_permissions($_SESSION['admin_purview'],"resume_talent");
		$talent=$_POST['talent'];
		!edit_resume_talent($id,$talent)?adminmsg("设置失败！",0):adminmsg("设置成功！",2,$link);
		}
		if (!empty($_POST['set_photoaudit']))
		{
		check_permissions($_SESSION['admin_purview'],"resume_photo_audit");
		$photoaudit=$_POST['photoaudit'];
		$is_del_img=intval($_POST['is_del_img']);
		!edit_resume_photoaudit($id,$photoaudit,$is_del_img)?adminmsg("设置失败！",0):adminmsg("设置成功！",2,$link);
		}
		elseif (!empty($_GET['refresh']))
		{
			if($n=refresh_resume($id))
			{
			adminmsg("刷新成功！响应行数 {$n}",2);
			}
			else
			{
			adminmsg("刷新失败！",0);
			}
		}
		elseif (!empty($_REQUEST['export']))
		{
			check_permissions($_SESSION['admin_purview'],"resume_export");
			if(!export_resume($id)){
				adminmsg("导出失败！",0);
			}
		}	
}
elseif($act == 'members_list')
{
	get_token();
	check_permissions($_SESSION['admin_purview'],"per_user_show");
		require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$wheresql=" WHERE  m.utype=2 ";
	$oederbysql=" order BY m.uid DESC ";
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if ($key && $key_type>0)
	{
		if     ($key_type===1)$wheresql.=" AND m.username like '{$key}%'";
		elseif ($key_type===2)$wheresql.=" AND m.uid='".intval($key)."'";
		elseif ($key_type===3)$wheresql.=" AND m.email like '{$key}%'";
		elseif ($key_type===4)$wheresql.=" AND m.mobile like '{$key}%'";
		$oederbysql="";
	}
	else
	{	
		if (!empty($_GET['settr']))
		{
			$settr=strtotime("-".intval($_GET['settr'])." day");
			$wheresql.=" AND m.reg_time> ".$settr;
		}
		if (!empty($_GET['verification']))
		{
			if ($_GET['verification']=="1")
			{
			$wheresql.=" AND m.email_audit = 1";
			}
			elseif ($_GET['verification']=="2")
			{
			$wheresql.=" AND m.email_audit = 0";
			}
			elseif ($_GET['verification']=="3")
			{
			$wheresql.=" AND m.mobile_audit = 1";
			}
			elseif ($_GET['verification']=="4")
			{
			$wheresql.=" AND m.mobile_audit = 0";
			}
		}
	}
	$total_sql="SELECT COUNT(*) AS num FROM ".table('members')." as m ".$wheresql;
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$member = get_member_list($offset,$perpage,$wheresql.$oederbysql);
	$smarty->assign('pageheader',"个人会员");
	$smarty->assign('member',$member);
	$smarty->assign('page',$page->show(3));
	$smarty->display('personal/admin_personal_user_list.htm');
}
elseif($act == 'delete_user')
{
	check_token();
	check_permissions($_SESSION['admin_purview'],"per_user_del");
	$tuid =!empty($_POST['tuid'])?$_POST['tuid']:adminmsg("你没有选择会员！",1);
	if ($_POST['delete'])
	{
		if ($_POST['delete_user']=='yes' && !delete_member($tuid))
		{
			adminmsg("删除会员失败！",0);
		}
		if ($_POST['delete_resume']=='yes' && !del_resume_for_uid($tuid))
		{
			adminmsg("删除简历失败！",0);
		}
		adminmsg("删除成功！",2);
	}
}
elseif($act == 'user_edit')
{	
	get_token();
	check_permissions($_SESSION['admin_purview'],"per_user_edit");
	$smarty->assign('pageheader',"个人会员");
	$smarty->assign('user',get_member_one($_GET['tuid']));
	$smarty->assign('userpoints',get_user_points($_GET['tuid']));
	$smarty->assign('resume',get_resume_uid($_GET['tuid']));
	$smarty->assign('url',$_SERVER["HTTP_REFERER"]);
	$smarty->display('personal/admin_personal_user_edit.htm');
}
elseif($act == 'userpoints_edit')
{
	check_token();
	if (intval($_POST['points'])<1) adminmsg('请输入积分！',1);
	if (trim($_POST['points_notes'])=='') adminmsg('请填写积分操作说明！',1);
	$link[0]['text'] = "返回列表";
	$link[0]['href'] = $_POST['url'];
	$user=get_user($_POST['personal_uid']);
	$points_type=intval($_POST['points_type']);	
	$t=$points_type==1?"+":"-";
	report_deal($user['uid'],$points_type,intval($_POST['points']));
	$points=get_user_points($user['uid']);
	write_memberslog(intval($_POST['personal_uid']),2,9001,$user['username']," 管理员操作积分({$t}{$_POST['points']})，(剩余:{$points})，备注：".$_POST['points_notes'],2,1012,"管理员操作积分","{$t}{$_POST['points']}","{$points}");
		//会员积分变更记录。管理员后台修改会员的积分。3表示：管理员后台修改
		$user=get_user($_POST['personal_uid']);
		if(intval($_POST['is_money']) && $_POST['log_amount']){
			$amount=round($_POST['log_amount'],2);
			$ismoney=2;
		}else{
			$amount='0.00';
			$ismoney=1;
		}
		$notes="操作人：{$_SESSION['admin_name']},说明：修改会员 {$user['username']} 积分 ({$t}{$_POST['points']})。收取积分金额：{$amount} 元，备注：{$_POST['points_notes']}";
		write_setmeallog($_POST['personal_uid'],$user['username'],$notes,3,$amount,$ismoney,1,1);
	write_log("修改会员uid为".$user['uid']."积分", $_SESSION['admin_name'],3);		
	adminmsg('保存成功！',2);
}
elseif($act == 'set_account_save')
{
	check_token();
	check_permissions($_SESSION['admin_purview'],"per_user_edit");
	require_once(ADMIN_ROOT_PATH.'include/admin_user_fun.php');
	$setsqlarr['username']=trim($_POST['username']);
	$setsqlarr['email']=trim($_POST['email']);
	$setsqlarr['email_audit']=intval($_POST['email_audit']);
	$setsqlarr['mobile']=trim($_POST['mobile']);
	$setsqlarr['mobile_audit']=intval($_POST['mobile_audit']);
	if ($_POST['qq_openid']=="1")
	{
	$setsqlarr['qq_openid']='';
	}
	$thisuid=intval($_POST['thisuid']);	
	if (strlen($setsqlarr['username'])<3) adminmsg('用户名必须为3位以上！',1);
	$getusername=get_user_inusername($setsqlarr['username']);
	if (!empty($getusername)  && $getusername['uid']<>$thisuid)
	{
	adminmsg("用户名 {$setsqlarr['username']}  已经存在！",1);
	}
	//若勾选已验证，则需判断手机号是否填写
	if($setsqlarr['mobile_audit']==1)
	{
		if (empty($setsqlarr['mobile']))
		{
		adminmsg('手机号码为空！',1);
		}
	}
	if (!empty($setsqlarr['mobile']) && !preg_match("/^(13|15|14|17|18)\d{9}$/",$setsqlarr['mobile']))
	{
	adminmsg('手机号码错误！',1);
	}
	if(!empty($setsqlarr['email']))
	{
		$getemail=get_user_inemail($setsqlarr['email']);
		if (!empty($getemail)  && $getemail['uid']<>$thisuid)
		{
		adminmsg("Email  {$setsqlarr['email']}  已经存在！",1);
		}
	}
	//若勾选已验证，则需判断手机号是否填写
	if($setsqlarr['mobile_audit']==1)
	{
		if (empty($setsqlarr['mobile']))
		{
		adminmsg('手机号码为空！',1);
		}
	}
	if (!empty($setsqlarr['mobile']) && !preg_match("/^(13|15|14|17|18)\d{9}$/",$setsqlarr['mobile']))
	{
	adminmsg('手机号码错误！',1);
	}
	$getmobile=get_user_inmobile($setsqlarr['mobile']);
	if (!empty($setsqlarr['mobile']) && !empty($getmobile)  && $getmobile['uid']<>$thisuid)
	{
	adminmsg("手机号 {$setsqlarr['mobile']}  已经存在！",1);
	}
	if ($db->updatetable(table('members'),$setsqlarr," uid=".$thisuid.""))
	{
		$u['email']=$setsqlarr['email'];
		$db->updatetable(table('resume'),$u," uid={$thisuid}");
		//填写管理员日志
		write_log("修改会员uid为".$thisuid."的基本信息", $_SESSION['admin_name'],3);
		$link[0]['text'] = "返回列表";
		$link[0]['href'] = $_POST['url'];
		adminmsg('修改成功！',2,$link);
	}
	else
	{
		adminmsg('修改失败！',1);
	}
}
elseif($act == 'userpass_edit')
{
	check_token();
	check_permissions($_SESSION['admin_purview'],"per_user_edit");
	if (strlen(trim($_POST['password']))<6) adminmsg('新密码必须为6位以上！',1);
	$user_info=get_member_one($_POST['memberuid']);
	$pwd_hash=$user_info['pwd_hash'];
	$md5password=md5(md5(trim($_POST['password'])).$pwd_hash.$QS_pwdhash);	
		if ($db->query( "UPDATE ".table('members')." SET password = '{$md5password}'  WHERE uid='{$user_info['uid']}' LIMIT 1"))
		{
			if(defined('UC_API'))
			{
			include_once(QISHI_ROOT_PATH.'uc_client/client.php');
			uc_user_edit($user_info['username'],trim($_POST['password']),trim($_POST['password']),"",1);
			}
			$link[0]['text'] = "返回列表";
			$link[0]['href'] = $_POST['url'];
			$member=get_member_one($user_info['uid']);
			write_memberslog($member['uid'],1,1004,$member['username'],"管理员在后台修改登录密码");
			//填写管理员日志
			write_log("修改会员uid为".$member['uid']."的密码", $_SESSION['admin_name'],3);
			adminmsg('操作成功！',2,$link);
		}
		else
		{
			adminmsg('操作失败！',1);
		}
}
elseif($act == 'members_add')
{
	get_token();
	check_permissions($_SESSION['admin_purview'],"per_user_add");
	$smarty->assign('pageheader',"个人会员");
	$smarty->display('personal/admin_personal_user_add.htm');
}
elseif($act == 'members_add_save')
{
	check_token();
	check_permissions($_SESSION['admin_purview'],"per_user_add");	
	require_once(ADMIN_ROOT_PATH.'include/admin_user_fun.php');
	if (strlen(trim($_POST['username']))<3) adminmsg('用户名必须为3位以上！',1);
	if (strlen(trim($_POST['password']))<6) adminmsg('密码必须为6位以上！',1);
	$sql['username'] = !empty($_POST['username']) ? trim($_POST['username']):adminmsg('请填写用户名！',1);
	$sql['password'] = !empty($_POST['password']) ? trim($_POST['password']):adminmsg('请填写密码！',1);	
	if ($sql['password']<>trim($_POST['password1']))
	{
	adminmsg('两次输入的密码不相同！',1);
	}
	$sql['utype'] = !empty($_POST['member_type']) ? intval($_POST['member_type']):adminmsg('你没有选择注册类型！',1);
	if (empty($_POST['email']) || !preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$_POST['email']))
	{
	adminmsg('电子邮箱格式错误！',1);
	}
	$sql['email']= trim($_POST['email']);
	if (get_user_inusername($sql['username']))
	{
	adminmsg('该用户名已经被使用！',1);
	}
	if (get_user_inemail($sql['email']))
	{
	adminmsg('该 Email 已经被注册！',1);
	}
	if(defined('UC_API'))
	{
		include_once(QISHI_ROOT_PATH.'uc_client/client.php');
		if (uc_user_checkname($sql['username'])<>"1")
		{
		adminmsg('该用户名已经被使用或者用户名非法！',1);
		exit();
		}
		elseif (uc_user_checkemail($sql['email'])<>"1")
		{
			adminmsg('该 Email已经被使用或者非法！',1);
			exit();
		}
		else
		{
			uc_user_register($sql['username'],$sql['password'],$sql['email']);
		}
	}
	$sql['pwd_hash'] = randstr();
	$sql['password'] = md5(md5($sql['password']).$sql['pwd_hash'].$QS_pwdhash);
	$sql['reg_time']=time();
	$sql['reg_ip']=$online_ip;
	$insert_id=$db->inserttable(table('members'),$sql,true);
	if ($insert_id)
	{
		$db->query("INSERT INTO ".table('members_points')." (uid) VALUES ('{$insert_id}')");
		//填写管理员日志
		write_log("添加id为".$insert_id."的个人会员", $_SESSION['admin_name'],3);
		write_memberslog($insert_id,1,1000,$sql['username'],"管理员在后台新增会员");
		$link[0]['text'] = "返回列表";
		$link[0]['href'] = "?act=members_list";
		adminmsg('添加成功！',2,$link);
	}	
}
elseif($act == 'resume_show')
{
	check_permissions($_SESSION['admin_purview'],"resume_show");
	$id =!empty($_REQUEST['id'])?intval($_REQUEST['id']):adminmsg("你没有选择简历！",1);
	$uid =intval($_REQUEST['uid']);
	$smarty->assign('pageheader',"查看简历");
	$resume=get_resume_basic($uid,$id);
	if (empty($resume))
	{
	$link[0]['text'] = "返回简历列表";
	$link[0]['href'] = '?act=list';
	adminmsg('简历不存在或已经被删除！',1,$link);
	}
	$smarty->assign('random',mt_rand());
	$smarty->assign('time',time());
	$smarty->assign('url',$_SERVER["HTTP_REFERER"]);
	$smarty->assign('resume',$resume);
	$smarty->assign('resume_education',get_resume_education($uid,$id));
	$smarty->assign('resume_work',get_resume_work($uid,$id));
	$smarty->assign('resume_training',get_resume_training($uid,$id));
	$smarty->assign('resumeaudit',get_resumeaudit_one($id));
	$smarty->display('personal/admin_personal_resume_show.htm');
}
elseif($act == 'del_auditreason')
{	
	check_permissions($_SESSION['admin_purview'],"resume_audit");
	$id =!empty($_REQUEST['a_id'])?$_REQUEST['a_id']:adminmsg("你没有选择日志！",1);
$n=reasonaudit_del($id);
	if ($n>0)
	{
	adminmsg("删除成功！共删除 {$n} 行",2);
	}
	else
	{
	adminmsg("删除失败！",0);
	}
}
elseif($act == 'management')
{	
	$id=intval($_GET['id']);
	$u=get_user($id);
	if (!empty($u))
	{
		unset($_SESSION['uid']);
		unset($_SESSION['username']);
		unset($_SESSION['utype']);
		unset($_SESSION['uqqid']);
		setcookie("QS[uid]","",time() - 3600,$QS_cookiepath, $QS_cookiedomain);
		setcookie("QS[username]","",time() - 3600,$QS_cookiepath, $QS_cookiedomain);
		setcookie("QS[password]","",time() - 3600,$QS_cookiepath, $QS_cookiedomain);
		setcookie("QS[utype]","",time() - 3600,$QS_cookiepath, $QS_cookiedomain);
		unset($_SESSION['activate_username']);
		unset($_SESSION['activate_email']);
		
		$_SESSION['uid']=$u['uid'];
		$_SESSION['username']=$u['username'];
		$_SESSION['utype']=$u['utype'];
		$_SESSION['uqqid']="1";
		setcookie('QS[uid]',$u['uid'],0,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[username]',$u['username'],0,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[password]',$u['password'],0,$QS_cookiepath,$QS_cookiedomain);
		setcookie('QS[utype]',$u['utype'], 0,$QS_cookiepath,$QS_cookiedomain);
		header("Location:".get_member_url($u['utype']));
	}	
}
//匹配
elseif($act == 'matching'){
	//简历id 
	$id =!empty($_REQUEST['id'])?intval($_REQUEST['id']):adminmsg("你没有选择简历！",1);
	//会员id 
	$uid =intval($_REQUEST['uid']);
	$smarty->assign('pageheader',"委托投递");
	$resume=get_resume_basic($uid,$id);
	if (empty($resume))
	{
	$link[0]['text'] = "返回简历列表";
	$link[0]['href'] = '?act=list';
	adminmsg('简历不存在或已经被删除！',1,$link);
	}
	//得到该简历的意向职位
	$resume_jobs_sql="select * from ".table("resume_jobs")." where pid=$id order by id desc";
	$resume_jobs_row=$db->getall($resume_jobs_sql);

	$resume_jobs_topclass='';
	$resume_jobs_category='';
	$resume_jobs_subclass='';
	foreach ($resume_jobs_row as $key => $value) {
		if($resume_jobs_topclass==''){
			$resume_jobs_topclass = $value['topclass'];
		}else{
			$resume_jobs_topclass = $resume_jobs_topclass.','.$value['topclass'];
		}

		if($resume_jobs_category==''){
			$resume_jobs_category = $value['category'];
		}else{
			$resume_jobs_category = $resume_jobs_category.','.$value['category'];
		}

		if($resume_jobs_subclass==''){
			$resume_jobs_subclass = $value['subclass'];
		}else{
			$resume_jobs_subclass = $resume_jobs_subclass.','.$value['subclass'];
		}
	}
	
	$shield_com = $db->getall("select * from ".table('personal_shield_company')." where id={$id} and uid={$uid}");
	
	
	//查看该简历是否屏蔽企业信息
	$entrustsql = "select * from ".table('resume_entrust')." where id={$id} and uid={$uid}";
	$shield = $db->getone($entrustsql);
	//该简历屏蔽企业(包括两部分 : 1.以前工作过的企业  2.屏蔽的企业)
	if(intval($shield['isshield'])==1){
		//查出该简历工作表中工作过的企业
		$worksql = "select * from ".table('resume_work')." where pid={$id} and uid={$uid}";
		$work = $db->getall($worksql);
		$work_shield = '';
		//查出该简历屏蔽的企业关键字
		$shieldsql = "select * from ".table('personal_shield_company')." where pid={$id} and uid={$uid}";
		$shieldcom = $db->getall($shieldsql);
		$com_shield = '';
		//有工作经验
		if(!empty($work)){
			foreach ($work as  $value) {
				if($work_shield==''){
					$work_shield = addslashes($value['companyname']);
				}else{
					$work_shield = $work_shield."','".addslashes($value['companyname']);
				}
			}
			$wheresql = " where is_entrust=0 and  companyname not in('".$work_shield."') and  (topclass in (".$resume_jobs_topclass.") or  category in (".$resume_jobs_category.") or subclass in (".$resume_jobs_subclass."))";
		}
		//没工作经验
		else{
			//简历匹配的职位
			$wheresql = " where is_entrust=0 and  topclass in (".$resume_jobs_topclass.") or  category in (".$resume_jobs_category.") or subclass in (".$resume_jobs_subclass.")";
		}
		//有屏蔽企业
		if(!empty($shieldcom)){
			foreach ($shieldcom as  $value) {
				if($com_shield==''){
					$com_shield = " and  companyname not like '%".$value['comkeyword']."%'";
				}else{
					$com_shield = $com_shield." and  "." companyname not like '%".$value['comkeyword']."%'";
				}
			}
		}
		//简历匹配的职位
		$sql = "select * from ".table('jobs')." where is_entrust=0 and  companyname not in('".$work_shield."') and  (topclass in (".$resume_jobs_topclass.") or  category in (".$resume_jobs_category.") or subclass in (".$resume_jobs_subclass.")) ".$com_shield;
	}
	//不屏蔽
	else{
		//简历匹配的职位
		$sql = "select * from ".table('jobs')." where  is_entrust=0 and  topclass in (".$resume_jobs_topclass.") or  category in (".$resume_jobs_category.") or subclass in (".$resume_jobs_subclass.")";
	}
	
	//分页
	require_once(QISHI_ROOT_PATH.'include/page.class.php');
	$total_sql="select count(*) as num  from ".table('jobs')." where topclass in (".$resume_jobs_topclass.") or  category in (".$resume_jobs_category.") or subclass in (".$resume_jobs_subclass.")";
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$jobs = get_jobs($offset,$perpage,$sql);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('jobs',$jobs);
	$smarty->assign('resume',$resume);
	$smarty->display('personal/admin_personal_resume_entrust.htm');
}
//投递
elseif($act == 'mailing'){
	//批量和非批量得到不同的职位id（批量得到的id是个数组jobsyid，非批量得到的是一个id值jobsid）
	$jobsid =$_REQUEST['id'];
	$jobsyid =$_REQUEST['y_id'];
	$uid = $_GET['uid'];
	if(empty($jobsid) && empty($jobsyid)){
		adminmsg("你没有选择职位！",1);
	}
	$jobs_id = empty($jobsid)?$jobsyid:$jobsid;
	$resumeid=isset($_GET['rid'])?intval($_GET['rid']):adminmsg("出错了");
	
	$jobsarr_id='';
	if(is_array($jobs_id)){
		foreach ($jobs_id as  $value) {
			if(empty($jobsarr_id)){
				$jobsarr_id = $value;
			}else{
				$jobsarr_id = $jobsarr_id.','.$value;
			}
		}
	}else{
		$jobsarr_id = $jobs_id;
	}
	$sql = "select * from ".table('jobs')." WHERE id IN ({$jobsarr_id}) ";
	$jobsarr = $db->getall($sql);
	if (empty($jobsarr))
	{
	adminmsg("职位丢失");
	}
	$resume_basic=$db->getone("select * from ".table('resume')." where id='{$resumeid}'  LIMIT 1 ");
	if (empty($resume_basic))
	{
	adminmsg("简历丢失");
	}
	$i=0;
	$jobs_id_=' ';
	foreach($jobsarr as $jobs)
	 {
	 	$jobs_id_ = $jobs_id_.$jobs['id'].',';
	 	//让它一直能投递，把下面的这个if 隐藏掉
 		/*if (check_jobs_apply($jobs['id'],$resumeid,$uid))
		{
		 continue ;
		}
		if ($resume_basic['display_name']=="2")
		{
			$personal_fullname="N".str_pad($resume_basic['id'],7,"0",STR_PAD_LEFT);
		}
		elseif($resume_basic['display_name']=="3")
		{
			$personal_fullname=cut_str($resume_basic['fullname'],1,0,"**");
		}
		else
		{
			$personal_fullname=$resume_basic['fullname'];
		}*/
 		$addarr['resume_id']=$resumeid;
		$addarr['resume_name']=$resume_basic['fullname'];
		$addarr['personal_uid']=$uid;
		$addarr['jobs_id']=$jobs['id'];
		$addarr['jobs_name']=$jobs['jobs_name'];
		$addarr['company_id']=$jobs['company_id'];
		$addarr['company_name']=$jobs['companyname'];
		$addarr['company_uid']=$jobs['uid'];
		
		$addarr['apply_addtime']=time();
		$addarr['personal_look']=1;
		if ($db->inserttable(table('personal_jobs_apply'),$addarr))
		{
			$mailconfig=get_cache('mailconfig');
			$weixinconfig=get_cache('weixin_config');
			$jobs['contact']=$db->getone("select * from ".table('jobs_contact')." where pid='{$jobs['id']}' LIMIT 1 ");
			$sms=get_cache('sms_config');	
			$comuser=get_user_info($jobs['uid']);	
			if ($mailconfig['set_applyjobs']=="1"  && $comuser['email_audit']=="1" && $jobs['contact']['notify']=="1")
			{	
				dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid={$_SESSION['uid']}&key=".asyn_userkey($_SESSION['uid'])."&act=jobs_apply&jobs_id={$jobs['id']}&jobs_name={$jobs['jobs_name']}&personal_fullname={$personal_fullname}&email={$comuser['email']}");
			}
			//sms	
			if ($sms['open']=="1"  && $sms['set_applyjobs']=="1"  && $comuser['mobile_audit']=="1")
			{
			//修正bug,求职者申请职位不发送短信
				dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_sms.php?uid={$_SESSION['uid']}&key=".asyn_userkey($_SESSION['uid'])."&act=jobs_apply&jobs_id=".$jobs['id']."&jobs_name=".$jobs['jobs_name'].'&jobs_uid='.$jobs['uid']."&personal_fullname=".$personal_fullname."&mobile=".$comuser['mobile']);
			}
			//站内信
			$pms_notice=1;
			if($pms_notice=='1'){
				$user=$db->getone("select username from ".table('members')." where uid ={$jobs['uid']} limit 1");
				$jobs_url=url_rewrite('QS_jobsshow',array('id'=>$jobs['id']));
				$resume_url=url_rewrite('QS_resumeshow',array('id'=>$resumeid));
				$message=$personal_fullname."申请了您发布的职位：<a href=\"{$jobs_url}\" target=\"_blank\">{$jobs['jobs_name']}</a>,<a href=\"{$resume_url}\" target=\"_blank\">点击查看</a>";
				write_pmsnotice($jobs['uid'],$user['username'],$message);
			}
			write_memberslog($_SESSION['uid'],2,1301,$_SESSION['username'],"投递了简历，职位:{$jobs['jobs_name']}");
			//微信
			set_applyjobs($jobs['uid'],$resumeid,$jobs['jobs_name'],$personal_fullname,$resume_basic['experience_cn'],$notes);
		}
		$i=$i+1;
	 }
	 if ($i==0)
	 {
	 adminmsg("投递失败！");
	 }
	 else
	 {
		//填写管理员日志
		write_log("后台将简历id为".$resumeid."的简历投递给职位id为".$jobs_id_."的职位", $_SESSION['admin_name'],3);
	 	adminmsg("投递成功！",3);
	 }
}
elseif($act == 'userstatus_edit')
{
	check_token();
	check_permissions($_SESSION['admin_purview'],"com_user_edit");
	if(set_user_status(intval($_POST['status']),intval($_POST['userstatus_uid'])))
	{
		$link[0]['text'] = "返回列表";
		$link[0]['href'] = $_POST['url'];
		adminmsg('操作成功！',2,$link);
	}
	else
	{
	adminmsg('操作失败！',1);
	}
}
elseif($act == 'import')
{
	global $db;
	check_permissions($_SESSION['admin_purview'],"resume_import");
	if($_FILES["file"]["name"]==""){
		adminmsg("请上传有效的excel文件！",1);
	}
	ini_set('memory_limit','128M');
	$file = QISHI_ROOT_PATH."data/tmp/".$_FILES["file"]["name"];
	move_uploaded_file($_FILES["file"]["tmp_name"],$file);
	require_once ADMIN_ROOT_PATH.'include/Excel/reader.php';
	require_once ADMIN_ROOT_PATH.'include/admin_locoyspider_fun.php';
	require_once QISHI_ROOT_PATH.'include/splitword.class.php';
	$sp = new SPWord();
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('gbk');
	$data->read($file);
	error_reporting(E_ALL ^ E_NOTICE);
	$count = 0;
	$total = 0;
	$now = time();
	for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) 
	{
		//var_dump($data->sheets[0]['cells'][$i][1]);
		$username = uniqid().time();
		$email = trim($data->sheets[0]['cells'][$i][9]);
		$mobile = trim($data->sheets[0]['cells'][$i][8]);
		//注册会员
		$userid = import_user_register($username,'123456',2,$email,$mobile,false);
		if($userid>0)
		{
			//个人信息表
			$member_info['uid'] = $userid;
			$member_info['realname'] = trim($data->sheets[0]['cells'][$i][2]);
			$member_info['sex_cn'] = trim($data->sheets[0]['cells'][$i][3]);
			$member_info['birthday'] = trim($data->sheets[0]['cells'][$i][4]);
			switch($member_info['sex_cn'])
			{
				case "男":$member_info['sex'] = 1;break;
				case "女":$member_info['sex'] = 2;break;
				default:$member_info['sex'] = 1;$member_info['sex_cn'] = "男";break;
			}
			$member_info['residence'] = trim($data->sheets[0]['cells'][$i][5]);
			$edu = resume_education(addslashes($data->sheets[0]['cells'][$i][6]));
			$member_info['education_cn'] = $edu['cn'];
			$member_info['education'] = $edu['id'];
			$exp = resume_experience(addslashes($data->sheets[0]['cells'][$i][7]));
			$member_info['experience_cn'] = $exp['cn'];
			$member_info['experience'] = $exp['id'];
			$member_info['email'] = trim($data->sheets[0]['cells'][$i][9]);
			$member_info['phone'] = trim($data->sheets[0]['cells'][$i][8]);
			$member_info['height'] = trim($data->sheets[0]['cells'][$i][10]);
			$member_info['householdaddress'] = trim($data->sheets[0]['cells'][$i][11]);
			$member_info['marriage_cn'] = trim($data->sheets[0]['cells'][$i][12]);
			switch($member_info['marriage_cn'])
			{
				case "未婚":$member_info['marriage'] = 1;break;
				case "已婚":$member_info['marriage'] = 2;break;
				default:$member_info['marriage'] = 1;$member_info['marriage_cn'] = "未婚";break;
			}
			if(!$db->inserttable(table('members_info'),$member_info,true)) continue;
			//简历表
			$resume['uid'] = $userid;
			$resume['title'] = trim($data->sheets[0]['cells'][$i][1]);
			$resume['fullname'] = trim($data->sheets[0]['cells'][$i][2]);
			$resume['birthdate'] = $member_info['birthday'];
			$resume['residence'] =$member_info['residence'];
			$resume['height'] =$member_info['height'];
			$resume['sex'] = $member_info['sex'];
			$resume['sex_cn'] = $member_info['sex_cn'];
			$resume['marriage'] = $member_info['marriage'];
			$resume['marriage_cn'] = $member_info['marriage_cn'];
			$resume['experience'] = $member_info['experience'];
			$resume['experience_cn'] = $member_info['experience_cn'];
			$resume['education'] = $member_info['education'];
			$resume['education_cn'] = $member_info['education_cn'];
			$resume['householdaddress'] = $member_info['householdaddress'];
			$resume['email'] = $member_info['email'];
			$resume['telephone'] = $member_info['phone'];
			$resume['nature_cn'] = trim($data->sheets[0]['cells'][$i][13]);
			switch($resume['nature_cn'])
			{
				case "全职":$resume['nature'] = 62;break;
				case "兼职":$resume['nature'] = 63;break;
				case "实习":$resume['nature'] = 64;break;
				default:$resume['nature'] = 62;$resume['nature_cn'] = "全职";break;
			}
			$resume['trade'] = '';
			$resume['trade_cn'] = '';
			$trade =addslashes($data->sheets[0]['cells'][$i][14]);
			$trade_array = explode(',', $trade);
			foreach ($trade_array as $key => $value) {
				$everyone = resume_trade($value);
				$resume['trade'] .= empty($resume['trade'])?$everyone['id']:",".$everyone['id'];
				$resume['trade_cn'] .= empty($resume['trade_cn'])?$everyone['cn']:",".$everyone['cn'];
			}
			$wage = resume_wage(addslashes($data->sheets[0]['cells'][$i][15]));
			$resume['wage_cn'] = $wage['cn'];
			$resume['wage'] = $wage['id'];
			$resume['addtime'] = strtotime($data->sheets[0]['cells'][$i][16]);
			$resume['refreshtime'] = strtotime($data->sheets[0]['cells'][$i][17]);
			$resume['specialty'] = addslashes($data->sheets[0]['cells'][$i][21]);
			$resume['complete_percent'] = addslashes($data->sheets[0]['cells'][$i][22]);
			$pid = $db->inserttable(table("resume"),$resume,1);
			if($pid)
			{
				//索引表
				$searchtab['id'] = $pid;
				$searchtab['uid'] = $userid;
				$searchtab['sex']=$resume['sex'];
				$searchtab['nature']=$resume['nature'];
				$searchtab['marriage']=$resume['marriage'];
				$searchtab['experience']=$resume['experience'];
				$searchtab['wage']=$resume['wage'];
				$searchtab['education']=$resume['education'];
				$searchtab['refreshtime']=$resume['refreshtime'];
				$searchtab['audit']=1;
				$db->inserttable(table('resume_search_rtime'),$searchtab);
				$searchtab['likekey']=$resume['trade_cn'].','.$resume['fullname'];
				$db->inserttable(table('resume_search_key'),$searchtab);
				unset($searchtab);

				//教育经历
				$resume_education = trim($data->sheets[0]['cells'][$i][18]);
				$edu_array = explode(';', $resume_education);
				foreach ($edu_array as $key => $value) 
				{
					if(empty($value))
					{
						continue;
					}
					$data_info = explode(',', $value);
					$time_edu = explode('就读于', $data_info[0]);
					$edu_time = explode('~', $time_edu[0]);
					$edu_start_time = explode('-', $edu_time[0]);
					$eduarrsql['startyear'] = $edu_start_time[0];
					$eduarrsql['startmonth'] = $edu_start_time[1];
					if(trim($edu_time[1]) == '至今')
					{
						$eduarrsql['todate'] = 1;
						$eduarrsql['endyear'] = 0;
						$eduarrsql['endmonth'] = 7;
					}
					else
					{
						$eduarrsql['todate'] = 0;
						$time_info = explode('-', $edu_time[1]);
						$eduarrsql['endyear'] = $time_info[0];
						$eduarrsql['endmonth'] = $time_info[1];
					}

					$eduarrsql['school'] = $time_edu[1];
					$major_info = explode('：', $data_info[1]);
					$eduarrsql['speciality'] = $major_info[1];
					$education_info = explode('：', $data_info[2]);
					$eduarrsql['education_cn'] = $education_info[1];
					$work_edu = resume_work_education($eduarrsql['education_cn']);
					$eduarrsql['education'] = $work_edu['id'];
					$eduarrsql['pid'] =  $pid;
					$eduarrsql['uid'] =  $userid;
					$db->inserttable(table("resume_education"),$eduarrsql);
				}
				//工作经历
				$resume_work = $data->sheets[0]['cells'][$i][19];
				$work_array = explode(';', $resume_work);
				foreach ($work_array as $key => $value) 
				{
					if(empty($value))
					{
						continue;
					}
					$data_info = explode(',', $value);
					$time_work = explode('就职于', $data_info[0]);
					$work_time = explode('~', $time_work[0]);
					$edu_start_time = explode('-', $work_time[0]);
					$workarrsql['startyear'] = $edu_start_time[0];
					$workarrsql['startmonth'] = $edu_start_time[1];
					if(trim($work_time[1]) == '至今')
					{
						$workarrsql['todate'] = 1;
						$workarrsql['endyear'] = 0;
						$workarrsql['endmonth'] = 7;
					}
					else
					{
						$workarrsql['todate'] = 0;
						$time_info = explode('-', $work_time[1]);
						$workarrsql['endyear'] = $time_info[0];
						$workarrsql['endmonth'] = $time_info[1];
					}

					$workarrsql['companyname'] = $time_work[1];
					$job_info = explode('：', $data_info[1]);
					$workarrsql['jobs'] = $job_info[1];
					$workarrsql['pid'] =  $pid;
					$workarrsql['uid'] =  $userid;
					$db->inserttable(table("resume_work"),$workarrsql);
				}
				//培训经历
				$resume_training = trim($data->sheets[0]['cells'][$i][20]);
				$training_array = explode(';', $resume_training);
				foreach ($training_array as $key => $value) 
				{
					if(empty($value))
					{
						continue;
					}
					$data_info = explode('在', $value);
					$work_time = explode('~', $data_info[0]);
					$edu_start_time = explode('-', $work_time[0]);
					$trainingarrsql['startyear'] = $edu_start_time[0];
					$trainingarrsql['startmonth'] = $edu_start_time[1];
					if(trim($data_info[1]) == '至今')
					{
						$trainingarrsql['todate'] = 1;
						$trainingarrsql['endyear'] = 0;
						$trainingarrsql['endmonth'] = 7;
					}
					else
					{
						$trainingarrsql['todate'] = 0;
						$time_info = explode('-', $work_time[1]);
						$trainingarrsql['endyear'] = $time_info[0];
						$trainingarrsql['endmonth'] = $time_info[1];
					}
					$agency_info = explode('培训', $data_info[1]);
					$trainingarrsql['agency'] = $agency_info[0];
					$trainingarrsql['course'] = $agency_info[1];
					$trainingarrsql['pid'] =  $pid;
					$trainingarrsql['uid'] =  $userid;
					$db->inserttable(table("resume_training"),$trainingarrsql);
				}
				$count++;
			}
		}
		$total++; 
	}
	adminmsg("共有".$total."条数据，导入".$count."条数据成功",2);
}
?>