<?php
 /*
 * 74cms ajax 加载评论
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(dirname(__FILE__)).'/include/plus.common.inc.php');
if ($_CFG['open_comment']=='0')
{
exit();
}
$act = !empty($_POST['act']) ? trim($_POST['act']) :trim($_GET['act']);
if ($act=='')
{
	$id=intval($_GET['companyid']);
	$company_profile=$db->getone("select * from ".table('company_profile')." where id='{$id}' LIMIT 1");
	$captcha=get_cache('captcha');
	$html='';
	$html.='<div class="comment-top clearfix">
							<span class="f-left">文明上网理性发言</span>
							<span class="f-right">'.$company_profile['comment'].'条评论</span>
						</div>';
	if ($_SESSION['uid']=='' || $_SESSION['username']=='' || intval($_SESSION['uid'])===0 || intval($_SESSION['utype'])!=2)
	{
	$html.='<div class="comment-block">
							<div class="comment-textarea" style="text-align: center;line-height: 87px;">您还没有登录！个人会员请 <a href="javascript:void(0);" class="ajax_user_login">登录</a> 或 <a href="'.$_CFG['site_dir'].'user/user_reg.php">注册</a> 后进行评论！</div>
							<div class="textarea-control clearfix">
								<div class="text-nologin f-left"></div>
								<div class="comment-submit f-right"><input type="button" value="发表评论" /></div>
							</div>
						</div><div class="clear"></div>';
	}
	else
	{
		if ($captcha['verify_comment']=='1')
		{
			$verify_comment='<input type="text" name="postcaptcha" id="postcaptcha" value="点击获取验证码" style="width: 100px;	height: 28px;border-width:1px;padding-left:5px;"/><div id="imgdiv"></div>
			<script>
				$(document).ready(function(){
	function imgcaptcha(inputID,imgdiv)
	{
		$(inputID).focus(function(){
			if ($(inputID).val()=="点击获取验证码")
			{
				$(inputID).val("");
				$(inputID).css("color","#333333");
			}
			$(inputID).parent("div").css("position","relative");
			$(imgdiv).css({ position: "absolute", left:  $(inputID).width(), "bottom": "0px" , "z-index": "10", "background-color": "#FFFFFF", "border": "1px #A3C8DC solid","display": "none","margin-left": "15px"});
			$(imgdiv).show();
			if ($(imgdiv).html()=="")
			{
				var tsTimeStamp= new Date().getTime();
				$(imgdiv).append("<img width=\'100\' src=\''.$_CFG["site_dir"].'include/imagecaptcha.php?t=\'+tsTimeStamp+\' id=\'getcode\' align=\'absmiddle\'  style=\'cursor:pointer; margin:1px;\' title=\'看不请验证码？点击更换一张\' border=\'0\'/>");
			}
			$(imgdiv+" img").click(function(){
				$(imgdiv+" img").attr("src",$(imgdiv+" img").attr("src")+"1");
			});
			$(document).unbind().click(function(event){
				var clickid=$(event.target).attr("id");
				if (clickid!="getcode" &&  clickid!="postcaptcha")
				{
					$(imgdiv).hide();
					$(inputID).parent("div").css("position","");
					$(document).unbind();
				}
			});
		});
	}
	imgcaptcha("#postcaptcha","#imgdiv");
});
			</script>';
		}
		$html.='<div class="comment-block">
							<div class="comment-textarea"><textarea id="content" cols="30" rows="3" style="font-size: 12px;">文明上网，理性发言</textarea></div>
							<div class="textarea-control clearfix">
								<div class="text-nologin f-left">'.$verify_comment.'</div>
								<div class="comment-submit f-right"><input type="button" value="发表评论" id="submitcomment"/></div>
								<div class="f-right" id="posterr" style="color: red;margin-right: 10px;height: 28px;line-height: 28px;"></div>
							</div>
						</div>';
	}

	$commentarray=$db->getall("select  c.*,m.username,m.avatars from ".table('comment')." as c LEFT JOIN  ".table('members')." AS m ON c.uid=m.uid where company_id = '{$id}' and c.audit=1 ORDER BY c.id DESC LIMIT 3");
	//若有评论  循环输出
	$html.='<div class="comment-list" id="comment_list_box">';
	if (!empty($commentarray))
	{
		foreach($commentarray as $li)
		{
			++$num;
			$li['username'] = cut_str($li['username'],1,0,'****');
			if($li['avatars'])
			{
				$avatars='<div class="logoimg"><img src="'.$_CFG['site_dir'].'data/avatar/48/'.$li['avatars'].'"></div>';
			}
			else
			{
				$avatars='<div class="logoimg"><img src="'.$_CFG['site_dir'].'data/avatar/no_photo.gif"></div>';
			}
			if($li['reply'])
			{
				$hf_a='<a href="javascript:;" class="control-item f-left more-talk">企业回复</a>';
			}
			else
			{
				if($_SESSION['uid']==$company_profile['uid'])
				{
					$hf_a='<a href="javascript:;" class="control-item f-left talk">回复</a>';
				}else
				{
					$hf_a='';
				}
			}
			$html.='<div class="comment-item" id="li-0">
								'.$avatars.'
								<div class="comment-title"><a>'.$li['username'].'</a><span>'.$num.'楼</span><span>'.date('Y-m-d H:i',$li['addtime']).'</span></div>
								<div class="comment-content">'.$li['content'].'</div>
								<div class="comment-control clearfix">
									'.$hf_a.'
								</div>';
			//回复 内容					
			if($li['reply'])
			{
				$html.='<div class="talk-again" style="display:block">
									<i class="ta-arrow" style="left:37px"></i>
									<div class="talk-again-item f-left">
										<div class="talk-man clearfix">
											<div class="talk-id f-left"><a>'.$company_profile['companyname'].'</a> 回复了 <a>'.$li['username'].'</a></div>
											<div class="talk-time f-right">'.date('Y-m-d H:i',$li['reply_time']).'</div>
										</div>
										<div class="talk-content">'.$li['reply'].'</div>
									</div><div class="clear"></div>
								</div>';
			}
			else
			{
				// 企业自己
				if($_SESSION['uid']==$company_profile['uid'])
				{
					$html.='<div class="comment-block talk-block">
									<div class="comment-textarea"><textarea  class="reply" cols="30" rows="3"></textarea></div>
									<div class="textarea-control clearfix">
										<div class="comment-submit f-right"><input type="button" value="回复评论" class="reply_submit" id='.$li['id'].' company_id="'.$li['company_id'].'"/></div>
									</div>
								</div>';
				}
			}
			$html.='</div>';
		}
		$html.='</div>';
		if($company_profile['comment']>3)
		{
			$html.="<div class=\"comment_more\"><span>加载更多...</span></div>";
		}
	}
	else
	{
		$html.='<div style="text-align: center;padding: 10px 0;">目前还没有人发表评论！</div>';
		$html.="<div class=\"comment_more\" style=\"display:none\"><span>加载更多...</span></div>";
	}
	$html.="<script type=\"text/javascript\">";
	$html.="$(document).ready(function()";
	$html.="{";
	$html.="$(\"#content\").focus(function(){";
	$html.="if ($(\"#content\").val()==\"文明上网，理性发言\")";
	$html.="{";
	$html.="$(\"#content\").val('');";
	$html.="} "; 
	$html.="});";
	$html.="$(\"#submitcomment\").click(function(){";
	$html.="$(\"#posterr\").html('');";
	$html.="var content=$(\"#content\").val();";
	$html.="var postcaptcha=$(\"#postcaptcha\").val();";
	$html.="if (content=='' || content=='文明上网，理性发言')";
	$html.="{";
	$html.="$(\"#posterr\").html('请填写内容');";
	$html.="$(\"#content\").focus();";
	$html.="}";
	$html.="else if (content.length<5 || content.length>300)";
	$html.="{";
	$html.="$(\"#posterr\").html('内容长度为必须为5-300个字符！');";
	$html.="}";
	if ($captcha['verify_comment']=='1')
	{
		$html.="else if (postcaptcha=='' || postcaptcha=='点击获取验证码')";
		$html.="{";
		$html.="$(\"#posterr\").html('请填写验证码');";
		$html.="}";
	}
	$html.="else";
	$html.="{";
	$html.="$(\"#submitcomment\").val('提交中').attr(\"disabled\",\"disabled\");";
	$html.="$.post(\"{$_CFG['site_dir']}plus/ajax_comment.php\", {\"content\": $(\"#content\").val(),\"postcaptcha\": $(\"#postcaptcha\").val(),\"company_id\": \"{$company_profile['id']}\",\"time\": new Date().getTime(),\"act\":\"comment_save\"},";
	$html.="function (data,textStatus)";
	$html.="{";
	$html.="if (data==\"err\" || data==\"errcaptcha\" || data==\"err1\")";
	$html.="{";		
	$html.="$(\"#submitcomment\").val('提交').attr(\"disabled\",\"\");";
	$html.="$(\"#posterr\").html('');";
	$html.="if (data==\"err\")";
	$html.="{";
	$html.="errinfo=\"发表失败！\";";
	$html.="}";
	$html.="else if(data==\"errcaptcha\")";
	$html.="{";
	$html.="$(\"#imgdiv img\").attr(\"src\",$(\"#imgdiv img\").attr(\"src\")+\"1\");";
	$html.="errinfo=\"验证码错误\";";
	$html.="}";
	$html.="if (data==\"err1\")";
	$html.="{";
	$html.="errinfo=\"您今天发表的评论过多，明天再来试试吧！\";";
	$html.="}";
	$html.="$(\"#posterr\").html(errinfo);";
	$html.="}";
	$html.="else";
	$html.="{";
	$html.="$(\"#submitcomment\").val('提交').attr(\"disabled\",\"\");";
	$html.="$(\"#countcomment\").html(data);";
	$html.="$(\"#content\").val('');";
	$html.="$(\"#postcaptcha\").val('点击获取验证码');";
	$html.="$(\"#imgdiv img\").attr(\"src\",$(\"#imgdiv img\").attr(\"src\")+\"1\");";
	$html.="var tsTimeStamp= new Date().getTime();";
	$html.="$.get(\"{$_CFG['site_dir']}plus/ajax_comment.php\", { \"id\": \"{$company_profile['id']}\",\"time\":tsTimeStamp,\"act\":\"show_list\",\"offset\":\"0\",\"rows\":\"3\"},";
	$html.="function (data,textStatus)";
	$html.="{";
	$html.="$(\"#comment_list_box\").html(data);";
	$html.="$(\".comment_more\").show();";
	$html.="$(\".noinfo\").remove();";
	$html.="$(\".comment_more span\").html('加载更多...');";
	$html.="}";
	$html.=");";

	if($_CFG['open_commentaudit']=='1'){
		$html.="alert('发表成功,正在审核中');";
	}else{
		$html.="alert('发表成功');";
	}

	$html.="}";
	$html.="})	";
	$html.="}";
	$html.="});";
	//加载更多
	$html.="$(\".comment_more\").bind('click',function(){";
	$html.="$(\".comment_more\").show();";
	$html.="$(\".comment_more span\").html('正在加载，请稍后...');";
	$html.="var offset=$(\"#comment_list_box div[class='comment-item']:last-child\").attr('id');";
	$html.="offset=parseInt(offset.substr(3));";
	$html.="var tsTimeStamp= new Date().getTime();";
	$html.="$.get(\"{$_CFG['site_dir']}plus/ajax_comment.php\", { \"id\": \"{$company_profile['id']}\",\"time\":tsTimeStamp,\"act\":\"show_list\",\"offset\":offset+3,\"rows\":\"3\"},";
	$html.="function (data,textStatus)";
	$html.="{";
	$html.="if (data=='empty!')";
	$html.="{";
	$html.="$(\".comment_more span\").html('已加载所有数据！');";
	$html.="}";
	$html.="else";
	$html.="{";
	$html.="$(\".comment_more span\").html('加载更多...');";
	$html.="$(\"#comment_list_box\").append(data);";
	// if($_SESSION['uid']==$company_profile['uid'])
	// {
	// $html.="$(\".comment-control\").append('<a href=\"javascript:;\" class=\"control-item f-left talk\">回复</a>');";
	// }
	$html.="}";
	$html.="}";
	$html.=");";
	$html.="});";
	$html.="});";
	$html.="</script>";
	exit($html);
}
elseif ($act=='comment_save')
{
	if ($_SESSION['uid']=='' || $_SESSION['username']=='' || intval($_SESSION['uid'])===0)
	{
	exit('err');
	}
	$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
	$n=$db->get_total("SELECT COUNT(*) AS num FROM ".table('comment')." WHERE uid='".intval($_SESSION['uid'])."' AND addtime>{$today}");
	if($n>=30)
	{
	 exit('err1');
	}
	$captcha=get_cache('captcha');
	if ($captcha['verify_comment']=="1")
	{
			$postcaptcha=$_POST['postcaptcha'];
			if ($captcha['captcha_lang']=="cn" && strcasecmp(QISHI_DBCHARSET,"utf8")!=0)
			{
			$postcaptcha=iconv("utf-8",QISHI_DBCHARSET,$postcaptcha);
			}
			if (empty($postcaptcha) || empty($_SESSION['imageCaptcha_content']) || strcasecmp($_SESSION['imageCaptcha_content'],$postcaptcha)!=0)
			{
			unset($_SESSION['imageCaptcha_content']);
			exit("errcaptcha");
			}
			unset($_SESSION['imageCaptcha_content']);
	}
	$setsqlarr['content']=trim($_POST['content']);
	if (strcasecmp(QISHI_DBCHARSET,"utf8")!=0)
	{
	$setsqlarr['content']=utf8_to_gbk($setsqlarr['content']);
	}
	$setsqlarr['uid']=intval($_SESSION['uid']);
	$setsqlarr['company_id']=intval($_POST['company_id']);
	$setsqlarr['addtime']=time();

	if($_CFG['open_commentaudit']=='1'){
		$setsqlarr['audit']=3;//审核中
	}else{
		$setsqlarr['audit']=1;//没有开启职位评论审核，直接审核通过
	}

	$db->inserttable(table('comment'),$setsqlarr);
	$sql="update ".table('company_profile')." set comment=comment+1 WHERE id='{$setsqlarr['company_id']}'  LIMIT 1";
	$db->query($sql);
	$company_profile=$db->getone("select comment from ".table('company_profile')." where id='{$setsqlarr['company_id']}' LIMIT 1");
	exit($company_profile['comment']);
}
elseif ($act=='show_list')
{
	$commenthtml="";
	$rows=intval($_GET['rows']);
	$offset=intval($_GET['offset']); 
	$id=intval($_GET['id']); 
	$company_profile=$db->getone("select * from ".table('company_profile')." where id='{$id}' LIMIT 1");
	$commentarray=$db->getall("select  c.*,m.username,m.avatars from ".table('comment')." as c LEFT JOIN  ".table('members')." AS m ON c.uid=m.uid where company_id = '{$id}' and c.audit=1 ORDER BY c.id DESC LIMIT {$offset},{$rows}");
	if (!empty($commentarray) && $offset<=100)
	{
		foreach($commentarray as $li)
		{
			++$num;
			$li['username'] = cut_str($li['username'],1,0,'****');
			if($li['avatars'])
			{
				$avatars='<div class="logoimg"><img src="'.$_CFG['site_dir'].'data/avatar/48/'.$li['avatars'].'"></div>';
			}
			else
			{
				$avatars='<div class="logoimg"><img src="'.$_CFG['site_dir'].'data/avatar/no_photo.gif"></div>';
			}
			if($li['reply'])
			{
				$hf_a='<a href="javascript:;" class="control-item f-left more-talk">企业回复</a>';
			}
			else
			{
				if($_SESSION['uid']==$company_profile['uid'])
				{
					$hf_a='<a href="javascript:;" class="control-item f-left talk">回复</a>';
				}else
				{
					$hf_a='';
				}
			}
			$commenthtml.='<div class="comment-item" id="li-'.$offset.'">
								'.$avatars.'
								<div class="comment-title"><a>'.$li['username'].'</a><span>'.$num.'楼</span><span>'.date('Y-m-d H:i',$li['addtime']).'</span></div>
								<div class="comment-content">'.$li['content'].'</div>
								<div class="comment-control clearfix">
									'.$hf_a.'
								</div>';
			//回复 内容					
			if($li['reply'])
			{
				$commenthtml.='<div class="talk-again" style="display:block">
									<i class="ta-arrow" style="left:37px"></i>
									<div class="talk-again-item f-left">
										<div class="talk-man clearfix">
											<div class="talk-id f-left"><a>'.$company_profile['companyname'].'</a> 回复了 <a>'.$li['username'].'</a></div>
											<div class="talk-time f-right">'.date('Y-m-d H:i',$li['reply_time']).'</div>
										</div>
										<div class="talk-content">'.$li['reply'].'</div>
									</div><div class="clear"></div>
								</div>';
			}
			else
			{
				// 企业自己
				if($_SESSION['uid']==$company_profile['uid'])
				{
					$commenthtml.='<div class="comment-block talk-block">
									<div class="comment-textarea"><textarea  class="reply" cols="30" rows="3"></textarea></div>
									<div class="textarea-control clearfix">
										<div class="comment-submit f-right"><input type="button" value="回复评论" class="reply_submit" id='.$li['id'].' company_id="'.$li['company_id'].'" /></div>
									</div>
								</div>';
				}
			}
			$commenthtml.='</div>';
		}
		exit($commenthtml);
	}
	else
	{
		exit('empty!');
	}
}
// 企业回复
elseif($act == "company_reply")
{
	$comment_id=$_POST['comment_id']?intval($_POST['comment_id']):exit("评论ID丢失！");
	$company_id=$_POST['company_id']?intval($_POST['company_id']):exit("企业ID丢失！");
	$reply=$_POST['reply']?trim($_POST['reply']):exit("回复内容不能为空！");
	$setarr['reply']=iconv("utf-8", "GBK", $reply);
	$setarr['reply_time']=time();
	$db->updatetable(table("comment"),$setarr,array("company_id"=>$company_id,"id"=>$comment_id))?exit("ok"):exit('回复出错！');
}
?>