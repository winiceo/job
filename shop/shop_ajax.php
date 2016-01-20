<?php
 /*
 * 74cms 积分商城首页
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once('shop_common.php');
$act =$_GET['act']?trim($_GET['act']):"check";
if($act == 'check')
{
	$id=intval($_GET['id']); //商品id
	$num=$_GET['num']?intval($_GET['num']):1;//兑换数目
	$uid=intval($_SESSION['uid']);
	// 获取企业积分
	$uesr_points=get_user_points($uid);
	// 获取商品信息
	$shop_one=get_shop_one($id);
	if(empty($shop_one))
	{
		exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
		    <tr>
				<td width="20" align="right"></td>
				<td style="padding-bottom:10px">
					商品id丢失！
				</td>
		    </tr>
		</table>');
	}
	if($_SESSION['username']=='')
	{
		$smarty->assign('verify_userlogin',$captcha['verify_userlogin']);
		$smarty->display('plus/ajax_login.htm');
		exit();
	}
	if($_SESSION['utype']!='1' && $_SESSION['utype']!='2')
	{
		exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
		    <tr>
				<td width="20" align="right"></td>
				<td style="padding-bottom:10px">
					商品兑换仅对企业和个人会员开放！
				</td>
		    </tr>
		</table>');
	}
	if($uesr_points>$shop_one['shop_points']*$num)
	{
		if($shop_one['shop_stock']>$num)
		{
			if($shop_one['shop_customer']>0)
			{
				$com_num=count_exchange($uid,$id);
				$shop_customer=$shop_one['shop_customer']-$com_num;
				if($shop_customer>=$num)
				{
					$key=substr(md5($id.$_SESSION['username'].$num),8,16);
					exit("".$key.",1");
				}
				else
				{
					exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
					    <tr>
							<td width="20" align="right"></td>
							<td style="padding-bottom:10px">
								此商品限制每人兑换'.$shop_one['shop_customer'].'件,您已经兑换'.$com_num.'件
							</td>
					    </tr>
					</table>');
				}
			}
			else
			{
				$key=substr(md5($id.$_SESSION['username'].$num),8,16);
				exit("".$key.",1");
			}
		}
		else
		{
			exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
					    <tr>
							<td width="20" align="right"></td>
							<td style="padding-bottom:10px">
								库存不足
							</td>
					    </tr>
					</table>');
		}
	}
	else
	{
		exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
					    <tr>
							<td width="20" align="right"></td>
							<td style="text-align:center;">
								您的积分不足，请重新选择礼品兑换！<br>
								<a class="prevListIndex" href="shop_list.php">返回首页</a>
							</td>
					    </tr>
					</table>');
	}
}
elseif($act== "order_show")
{
	$id=intval($_GET['id']);
	$order_show=get_order_one($id);

	if(!empty($order_show))
	{
		if($order_show['state']==0)
		{
			$order_show['state_cn']="<span class=\"coff9125\">待审核</span>";
		}
		elseif($order_show['state']==1)
		{
			$order_show['state_cn']="<span class=\"co5dbc47\">审核通过</span>";
		}
		else
		{
			$order_show['state_cn']="<span class=\"cofe4848\">审核未通过</span>";
		}
		exit('<div class="order-d-wrap">
		<div class="order-d-item clearfix">
			<div class="od-type f-left">订单内容：</div>
			<div class="od-content f-left">'.$order_show['shop_title'].'  * '.$order_show['shop_num'].'</div>
		</div>
		<div class="order-d-item clearfix">
			<div class="od-type f-left">订单状态：</div>
			<div class="od-content f-left">'.$order_show['state_cn'].'</div>
		</div>
		<div class="order-d-item clearfix">
			<div class="od-type f-left">订单时间：</div>
			<div class="od-content f-left">'.date('Y-m-d H:i:s',$order_show['addtime']).'</div>
		</div>
		<div class="order-d-item clearfix">
			<div class="od-type f-left">联 系 人：</div>
			<div class="od-content f-left">'.$order_show['contact'].'  '.$order_show['mobile'].'</div>
		</div>
		<div class="order-d-item clearfix">
			<div class="od-type f-left">联系地址：</div>
			<div class="od-content f-left">'.$order_show['address'].'</div>
		</div>
	</div>');
	}
}
?>