<?php
/**
 * Created by PhpStorm.
 * User: leven
 * Date: 16/1/15
 * Time: 上午9:54
 */
require_once(QISHI_ROOT_PATH . '/genv/lib.php');

//判断有无推广方案，有则不能修改，关闭，删除
function check_job_promotion($jid)
{
    global $db;
     $rs=$db->getone("select * from ".table('promotion')."   where  cp_jobid={$jid} LIMIT 1");

    return $rs;
}

function get_user_balance($uid)
{
    global $db;
    $uid=intval($uid);
    $points=$db->getone("select balance from ".table('members_points')." where uid ='{$uid}' LIMIT 1");
    return $points['balance'];
}
//获取用户可用余额
function get_user_can_balance($uid)
{
    global $db;
    $uid=intval($uid);
    $points=$db->getone("select balance-block_balance as can_balance from ".table('members_points')." where uid ='{$uid}' LIMIT 1");
    return $points['can_balance'];
}
//查看职位有没有已经在推广
function get_job_reward_one($jobid,$uid,$promotionid)
{
    global $db;
    $jobid=intval($jobid);
    $sql = "select * from ".table('jobs_reward')." where cp_jobid='{$jobid}' AND cp_uid='{$uid}' AND cp_promotionid='{$promotionid}'  LIMIT 1";
    return $db->getone($sql);
}
//查看职位有没有已经在推广
function get_promotion_info($jobid,$promotionid)
{
    global $db;
    $jobid=intval($jobid);
    $sql = "select * from ".table('promotion')." where cp_jobid='{$jobid}'   AND cp_promotionid='{$promotionid}'  LIMIT 1";
    return $db->getone($sql);
}
function set_job_reward($jobid,$type,$val='')
{
    global $db;
    $jobid=intval($jobid);
    $db->query("UPDATE ".table('jobs')." SET reward=1 WHERE id='{$jobid}' LIMIT 1");
    $db->query("UPDATE ".table('jobs_tmp')." SET reward=1 WHERE id='{$jobid}' LIMIT 1");
    $db->query("UPDATE ".table('jobs_search_rtime')." SET reward=1 WHERE id='{$jobid}' LIMIT 1");

    return true;

}
//冻结资金

function block_balance_reward($uid, $balance=0)
{
    global $db,$timestamp;
    $balance=intval($balance);
    $uid=intval($uid);
    $balance_can=get_user_can_balance($uid);
    if ($balance_can>=$balance)
    {
        $sql = "UPDATE ".table('members_points')." SET block_balance= block_balance+{$balance} WHERE uid='{$uid}' LIMIT 1";
        if (!$db->query($sql))return false;
        return true;
    }
    return false;

}




//企业资金异动，1为增加，2为减少；
function balance_deal($uid,$type=1,$money=0)
{
    global $db,$timestamp;
    $money=intval($money);
    $uid=intval($uid);
    $balance=get_user_balance($uid);
    if ($type==1)
    {
        $sql = "UPDATE ".table('members_points')." SET balance= balance+{$money} WHERE uid='{$uid}' LIMIT 1";
        if (!$db->query($sql))return false;
    }
    if ($type==2&&$balance>=$money)
    {
        $sql = "UPDATE ".table('members_points')." SET balance= balance-{$money} WHERE uid='{$uid}' LIMIT 1";
        if (!$db->query($sql))return false;
    }

    return true;
}

//个人资金异动，1为增加，2为减少；
function balance_deal_person($uid,$type=1,$money=0)
{
    global $db,$timestamp;
    $money=intval($money);
    $uid=intval($uid);
    $balance=get_user_balance($uid);
    if ($type==1)
    {
        $sql = "select * from ".table('members_person_points')." where uid='{$uid}'    LIMIT 1";
        if($db->getone($sql)){
            $sql = "UPDATE ".table('members_person_points')." SET balance= balance+{$money} WHERE uid='{$uid}' LIMIT 1";
            if (!$db->query($sql))return false;
        }else{
            $sqlarr['uid']=$uid;
            $sqlarr['balance']=$money;

           $db->inserttable(table('members_person_points'),$sqlarr) ;
        }
    }
    if ($type==2&&$balance>=$money)
    {
        $sql = "UPDATE ".table('members_person_points')." SET balance= balance-{$money} WHERE uid='{$uid}' LIMIT 1";
        if (!$db->query($sql))return false;
    }

    return true;
}



function report_deal_reward($uid,$i_type=1,$balance=0)
{
    global $db,$timestamp;
    $balance=intval($balance);
    $uid=intval($uid);
    $points_val=get_user_points($uid);
    if ($i_type==1)
    {
        $points_val=$points_val+$points;
    }
    if ($i_type==2)
    {
        $points_val=$points_val-$points;
        $points_val=$points_val<0?0:$points_val;
    }
    $sql = "UPDATE ".table('members_points')." SET balance= '{$points_val}' WHERE uid='{$uid}' LIMIT 1";
    if (!$db->query($sql))return false;
    return true;
}


function get_member_check_list($offset, $perpage, $get_sql = '')
{
    global $db;
    $row_arr = array();
    $limit = " LIMIT " . $offset . ',' . $perpage;
    $result = $db->query("SELECT m.*,c.status as check_status,c.id as cid,c.addtime FROM " . table('members') . " as m " . $get_sql . $limit);
    while ($row = $db->fetch_array($result)) {
        $row['company_url'] = url_rewrite('QS_companyshow', array('id' => $row['id']));
        $address = $db->getone("select log_address,log_id,log_uid from " . table("members_log") . " where log_type = '1000' and log_uid = " . $row['uid'] . " order by log_id asc limit 1");
        $row['ipAddress'] = $address['log_address'];
        $row_arr[] = $row;
    }
    return $row_arr;
}
function get_clue_check_list($offset, $perpage, $get_sql = '')
{
    global $db;
    $row_arr = array();
    $limit = " LIMIT " . $offset . ',' . $perpage;
    $result = $db->query("SELECT * FROM " . table('jobs_reward_clue') . " as m " . $get_sql . $limit);
    while ($row = $db->fetch_array($result)) {
        $row['company_url'] = url_rewrite('QS_companyshow', array('id' => $row['company_id']));
        $row['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $row['job_id']));

        $row_arr[] = $row;
    }
    return $row_arr;
}
//线索信息
function get_clue_one($id)
{
    global $db;
    $id=intval($id);
    $sql = "select * from ".table('jobs_reward_clue')." where    id='{$id}'  LIMIT 1";
    return $db->getone($sql);
}

function get_member_info($memberuid)
{
    global $db;
    $sql = "select * from ".table('members')." where uid=".intval($memberuid)." LIMIT 1";
    $val=$db->getone($sql);
    return $val;
}
//线索访问日志
function get_clue_log_list($id)
{
    global $db;
    $id=intval($id);
    $sql = "select * from ".table('jobs_reward_clue_log')." where    cid='{$id}' order by id desc  ";
    $result=$db->query($sql);
    while ($row = $db->fetch_array($result)) {

        $row_arr[] = $row;
    }
    return $row_arr;
}


//增加订单
function admin_add_order($uid,$pay_type,$oid,$amount,$payment_name,$description,$addtime,$points='',$setmeal='',$utype='1')
{
    global $db;
    $setsqlarr['uid']=intval($uid);
    $setsqlarr['pay_type']=$pay_type;
    $setsqlarr['oid']=$oid;
    $setsqlarr['is_paid']=2;

    $setsqlarr['amount']=$amount;
    $setsqlarr['payment_name']=$payment_name;
    $setsqlarr['description']=$description;
    $setsqlarr['addtime']=$addtime;
    $setsqlarr['points']=$points;
    $setsqlarr['setmeal']=$setmeal;
    $setsqlarr['utype']=$utype;
    write_memberslog($uid,1,3001,$_SESSION['username'],"添加订单，编号{$oid}，金额{$amount}元");
//    $userinfo=get_user_info($uid);
//    //sendemail
//    $mailconfig=get_cache('mailconfig');
//    if ($mailconfig['set_order']=="1" && $userinfo['email_audit']=="1" && $amount>0)
//    {
//        global $_CFG;
//        $paymenttpye=get_payment_info($payment_name);
//        dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_mail.php?uid={$uid}&key=".asyn_userkey($uid)."&act=set_order&oid={$oid}&amount={$amount}&paymenttpye={$paymenttpye['byname']}");
//    }
//    //sendemail
//    //sms
//    $sms=get_cache('sms_config');
//    if ($sms['open']=="1" && $sms['set_order']=="1"  && $userinfo['mobile_audit']=="1" && $amount>0)
//    {
//        global $_CFG;
//        $paymenttpye=get_payment_info($payment_name);
//        dfopen("{$_CFG['site_domain']}{$_CFG['site_dir']}plus/asyn_sms.php?uid={$uid}&key=".asyn_userkey($uid)."&act=set_order&oid={$oid}&amount={$amount}&paymenttpye={$paymenttpye['byname']}");
//    }
    //sms
    return $db->inserttable(table('order'),$setsqlarr,true);
}