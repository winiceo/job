<?php
/*
* 74cms 申请职位
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__) . '/../include/common.inc.php');
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'app';
require_once(QISHI_ROOT_PATH . 'include/mysql.class.php');
require_once(QISHI_ROOT_PATH . 'genv/func_company.php');
$db = new mysql($dbhost, $dbuser, $dbpass, $dbname);
if ((empty($_SESSION['uid']) || empty($_SESSION['username']) || empty($_SESSION['utype'])) && $_COOKIE['QS']['username'] && $_COOKIE['QS']['password'] && $_COOKIE['QS']['uid']) {
    require_once(QISHI_ROOT_PATH . 'include/fun_user.php');
    if (check_cookie($_COOKIE['QS']['uid'], $_COOKIE['QS']['username'], $_COOKIE['QS']['password'])) {
        update_user_info($_COOKIE['QS']['uid'], false, false);
        header("Location:" . get_member_url($_SESSION['utype']));
    } else {
        unset($_SESSION['uid'], $_SESSION['username'], $_SESSION['utype'], $_SESSION['uqqid'], $_SESSION['activate_username'], $_SESSION['activate_email'], $_SESSION["openid"]);
        setcookie("QS[uid]", "", time() - 3600, $QS_cookiepath, $QS_cookiedomain);
        setcookie('QS[username]', "", time() - 3600, $QS_cookiepath, $QS_cookiedomain);
        setcookie('QS[password]', "", time() - 3600, $QS_cookiepath, $QS_cookiedomain);
        setcookie("QS[utype]", "", time() - 3600, $QS_cookiepath, $QS_cookiedomain);
    }
}
if ($_SESSION['uid'] == '' || $_SESSION['username'] == '') {

    $captcha = get_cache('captcha');
    $smarty->assign('verify_userlogin', $captcha['verify_userlogin']);
    $smarty->display('plus/ajax_login.htm');
    exit();
}
//if ($_SESSION['utype']!='2')
//{
//	exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
//		    <tr>
//				<td width="20" align="right"></td>
//				<td class="ajax_app">
//					必须是个人会员才可以投简历！
//				</td>
//		    </tr>
//		</table>');
//}
require_once(QISHI_ROOT_PATH . 'include/fun_personal.php');
$user = get_user_info($_SESSION['uid']);
if ($user['status'] == "2") {
    exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
		    <tr>
				<td width="20" align="right"></td>
				<td class="ajax_app">
					您的账号处于暂停状态，请联系管理员设为正常后进行操作！
				</td>
		    </tr>
		</table>');
}
if ($act == "app") {
    $id = isset($_GET['id']) ? $_GET['id'] : exit("id 丢失");
    $jobs = app_get_jobs($id);
    $promotion=get_promotion_info($id,5);

    $json=json_array($promotion["cp_json"]);
    if (empty($jobs)||empty($promotion)) {
        exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
			    <tr>
					<td width="20" align="right"></td>
					<td class="ajax_app">
						简历已失效！
					</td>
			    </tr>
			</table>');
    }
    $detail[]="面试人数：".$json["num"]."<br>";
    $detail[]="面试成功金额：".$json["amount"]."<br>";
    $detail[]="招聘人数：".$json["success_num"]."<br>";
    $detail[]="招聘成功金额：".$json["success_amount"]."<br>";
    $detail=join(" ",$detail);

    ?>
    <script type="text/javascript">
        $(".but80").hover(function () {
            $(this).addClass("but80_hover")
        }, function () {
            $(this).removeClass("but80_hover")
        });

        var resumeid = $("#resumeid").val();
        var url = '../resume/resume-show.php?id=' + resumeid + '';
        $("#resume_yl").attr("href", url);
        $("#resumeid").change(function () {
            var resumeid = $("#resumeid").val();
            var url = '../resume/resume-show.php?id=' + resumeid + '';
            $("#resume_yl").attr("href", url);
        })
        //计算今天申请数量
        var app_max = "<?php echo $_CFG['apply_jobs_max'] ?>";
        var app_today = "<?php echo get_now_applyjobs_num($_SESSION['uid']) ?>";

        //验证
        $("#ajax_app").click(function () {

            $("#app").hide();
            $("#notice").hide();
            $("#waiting").show();
            var tsTimeStamp = new Date().getTime();
            if ($("#link_name").val() == "") {
                alert("请填写联系人");
                return
            }
            if ($("#link_telepone").val() == "") {
                alert("请填写联系人联系方式");
                return
            }

            $.get("<?php echo $_CFG['site_dir'] ?>user/user_clue_jobs.php", {
                    "link_name": $("#link_name").val(),
                    "jobsid": "<?php echo $id;?>",
                    "remark": $("#remark").val(),
                    "link_telephone": $("#link_telephone").val(),
                    "time": tsTimeStamp,
                    "act": "app_save"
                },

                function (data, textStatus) {
                    if (data == "ok") {
                        $("#app").hide();
                        $("#notice").hide();
                        $("#waiting").hide();
                        $("#app_ok").show();
                    }

                    else {
                        $("#app").hide();
                        $("#notice").hide();
                        $("#waiting").hide();
                        $("#app_ok").hide();
                        $("#error_msg").html("投递失败！" + data);
                        $("#error").show();
                    }
                })

        });
    </script>
    <style>
        .input_text_200 {
            width: 200px;
            border: 1px solid #CCCCCC;
            font-size: 14px;
            line-height: 16px;
            padding: 9px;
            color: #666666;
        }

    </style>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall" id="app">
        <tr>
            <td width="120" align="right" valign="top">职位：</td>
            <td class="ajax_app">
                <ul>
                    <?php

                    foreach ($jobs as $j)
                    {
                    ?>
                    <li style="float:left;width:110px;margin-right:10px;"><label> <?php echo $j['jobs_name']?></label>
                        <?php }?>
                    </li>
                    <div style="clear:both"></div>
                </ul>
            </td>
        </tr>

        <tr>
            <td align="right">联系人：</td>
            <td>
                <input name="link_name" id="link_name" class="input_text_200" type="text" maxlength="10"/>
            </td>
        </tr>
        <tr>
            <td align="right">联系方式：</td>
            <td>
                <input name="link_telephone" id="link_telephone" class="input_text_200" type="text" maxlength="20"/>

            </td>
        </tr>
        <tr>
            <td align="right">备注：</td>
            <td>
                <textarea name="remark" id="remark"
                          style="width:300px; height:60px; line-height:180%; font-size:12px;border:1px solid #e2e2e2;resize:both"></textarea>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <input type="button" name="Submit" id="ajax_app" class="but130lan" value="提交线索"/>
            </td>
        </tr>
        <tr><td></td>
            <td ><?php echo $detail;?></td>

        </tr>
    </table>



    <table width="100%" border="0" cellspacing="5" cellpadding="0" id="waiting" style="display:none">
        <tr>
            <td align="center" height="60"><img src="<?php echo $_CFG['site_template']?>images/30.gif" border="0"/></td>
        </tr>
        <tr>
            <td align="center">请稍后...</td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall" id="app_ok" style="display:none">
        <tr>
            <td width="140" align="right"><img height="100"
                                               src="<?php echo $_CFG['site_template']?>images/big-yes.png"/></td>
            <td>
                <strong style="font-size:14px ; color:#0066CC;margin-left:20px">线索提交成功!</strong>

                <div
                    style="border-top:1px #CCCCCC solid; line-height:180%; margin-top:10px; padding-top:10px; height:50px;margin-left:20px"
                    class="dialog_closed">
                    <a href="javascript:void(0)" class="DialogClose underline"
                       style="color:#0180cf;text-decoration:none;">完成</a>
                </div>
            </td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="5" cellpadding="0" id="error" style="display:none">
        <tr>
            <td align="center" id="error_msg"></td>
        </tr>
    </table>
<?php
} elseif ($act == "app_save") {
    $_POST = $_GET;
    $jobsid = isset($_POST['jobsid']) ? $_POST['jobsid'] : exit("出错了");

    $remark = isset($_POST['remark']) ? trim($_POST['remark']) : "";
    $link_name = isset($_POST['link_name']) ? trim($_POST['link_name']) : "";
    $link_telephone = isset($_POST['link_telephone']) ? trim($_POST['link_telephone']) : "";
    if (empty($link_name)) {
        exit("联系人姓名不能为空");
    }
    if (empty($link_telephone)) {
        exit("联系人联系方式不能为空");
    }
    $jobsarr = app_get_jobs($jobsid);
    if (empty($jobsarr)) {
        exit("职位丢失");
    }

    $i = 0;
    foreach ($jobsarr as $jobs) {
        $jobs = array_map("addslashes", $jobs);
        if (check_jobs_apply($jobs['id'], $resumeid, $_SESSION['uid'])) {
            continue;
        }

        $addarr['job_id'] = $jobs['id'];
        $addarr['job_name'] = $jobs['jobs_name'];
        $addarr['company_id'] = $jobs['company_id'];
        $addarr['company_name'] = $jobs['companyname'];
        $addarr['company_uid'] = $jobs['uid'];
        $addarr['link_name'] = $link_name;
        $addarr['link_telephone'] = $link_telephone;
        $addarr['remark'] = $remark;
        if (strcasecmp(QISHI_DBCHARSET, "utf8") != 0) {
            $addarr['remark'] = utf8_to_gbk($addarr['remark']);
            $addarr['link_name'] = utf8_to_gbk($link_name);
            $addarr['link_telephone'] = utf8_to_gbk($link_telephone);
        }
        $addarr['addtime'] = time();
        $addarr['uid'] = $_SESSION["uid"];;
        if ($db->inserttable(table('jobs_reward_clue'), $addarr)) {

            write_memberslog($_SESSION['uid'], 2, 1301, $_SESSION['username'], "提交了人才线索，职位:{$jobs['jobs_name']}");

        }
        $i = $i + 1;
    }
    if ($i == 0) {
        exit("repeat");
    } else {
        exit("ok");
    }

}
function reduce_user_sms($uid)
{
    global $db;
    $db->query("UPDATE " . table('members') . " SET `sms_num` = sms_num - 1 WHERE `uid` = " . $uid . " LIMIT 1 ;");
}

?>
