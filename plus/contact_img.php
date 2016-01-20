<?php
 /*
 * 74cms 联系方式图形化
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/plus.common.inc.php');
require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = trim($_GET['act']);
$type =intval($_GET['type']);
$token=trim($_GET['token']);
$id=intval($_GET['id']);
function hidtel($phone){
    $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话
    if($IsWhat == 1){
        return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);
    }else{
        return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
    }
} 
if($act == 'jobs_contact')
{
			$sql = "select * from ".table('jobs_contact')." where pid='{$id}' LIMIT 1";
			$val=$db->getone($sql);
			//对座机进行分隔
			$telarray = explode('-',$val['landline_tel']);
			if(intval($telarray[0]) > 0)
			{
				$landline_tel = $telarray[0];
			}
			if(intval($telarray[1]) > 0)
			{
				$landline_tel = empty($landline_tel)?$telarray[1]:$landline_tel."-".$telarray[1];
			}
			if(intval($telarray[2]) > 0)
			{
				$landline_tel = empty($landline_tel)?$telarray[2]:$landline_tel."-".$telarray[2];
			}
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			$user = $db->getone("select m.username uname from ".table("members")." as m left join ".table("jobs")." as j on m.uid=j.uid where j.id=$id ");
			$hashstr=substr(md5($user['uname']),8,16);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 1:
			  $t=$val['contact'];
			  break;  
			case 2:
				if($hashstr==$_GET['hashstr']){
					$t=$val['telephone'];
				}else{
					$t=hidtel($val['telephone']); 
				} 
			  break;
			  case 3:
			   $t=$val['email'];
			  break;
			  case 4:
			   $t=$val['address'];
			  break;
			  case 5:
			   $t=$val['qq'];
			  break;
			  case 6:
			   $t=$landline_tel;
			  break;
			}
}
elseif($act == 'company_contact')
{
			$sql = "select contact,telephone,email,address,landline_tel FROM ".table('company_profile')." where id=".intval($id)." LIMIT 1";
			$val=$db->getone($sql);
			//对座机进行分隔
			$telarray = explode('-',$val['landline_tel']);
			if(intval($telarray[0]) > 0)
			{
				$landline_tel = $telarray[0];
			}
			if(intval($telarray[1]) > 0)
			{
				$landline_tel = empty($landline_tel)?$telarray[1]:$landline_tel."-".$telarray[1];
			}
			if(intval($telarray[2]) > 0)
			{
				$landline_tel = empty($landline_tel)?$telarray[2]:$landline_tel."-".$telarray[2];
			}
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 1:
			  $t=$val['contact'];
			  break;  
			case 2: 
			 $t=$val['telephone']; 
			  break;
			case 3:
			   $t=$val['email'];
			  break;
			  case 4:
			   $t=$val['address'];
			  break;
			  case 5:
			   $t=$landline_tel;
			  break;
			}
}
//简历联系方式
elseif($act == 'resume_contact')
{
		$val=$db->getone("select fullname,telephone,email,residence from ".table('resume')." WHERE  id='{$id}'  LIMIT 1");
		$tmd5=md5($val['fullname'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}	
		switch ($type)
			{
			case 1:
			  $t=$val['fullname'];
			  break; 
			case 2: 
			 $t=$val['telephone']; 
			  break;  
			case 3:
			   $t=$val['email'];
			  break;
			 case 4:
			   $t=$val['residence'];
			  break;
			}
}
elseif($act == 'course_contact')
{
			$sql = "select * from ".table('course_contact')." where pid='{$id}' LIMIT 1";
			$val=$db->getone($sql);
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 1:
			  $t=$val['contact'];
			  break;  
			case 2: 
			  $t=$val['telephone']; 
			  break;
			  case 3:
			   $t=$val['email'];
			  break;
			}
}
elseif($act == 'train_contact')
{
			$sql = "select contact,telephone,email,address,website FROM ".table('train_profile')." where id=".intval($id)." LIMIT 1";
			$val=$db->getone($sql);
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 1:
			  $t=$val['contact'];
			  break;  
			case 2: 
				 $t=$val['telephone']; 
			  break;
			  case 3:
			   $t=$val['email'];
			  break;
			  case 4:
			   $t=$val['address'];
			  break;
			  case 5:
			   $t=$val['website'];
			  break;
			}
}
elseif($act == 'teacher_contact')
{
			$sql = "select telephone,email,address FROM ".table('train_teachers')." where id=".intval($id)." LIMIT 1";
			$val=$db->getone($sql);
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 2: 
			 $t=$val['telephone']; 
			  break;
			  case 3:
			   $t=$val['email'];
			  break;
			  case 4:
			   $t=$val['address'];
			  break;
			}
}
//高级职位
elseif($act == 'hunterjobs_contact')
{
			$sql = "select telephone,telephone_show,email,email_show,address,address_show,contact,contact_show,qq,qq_show from ".table('hunter_jobs')." where id='{$id}' LIMIT 1";
			$val=$db->getone($sql);
			$tmd5=md5($val['contact'].$id.$val['telephone']);
			if ($tmd5<>$token)
			{
			exit();
			}
			switch ($type)
			{
			case 1:
			  $t=$val['contact'];
			  break;  
			case 2: 
			     $t=$val['telephone']; 
			  break;
			  case 3:
			   $t=$val['email'];
			  break;
			  case 4:
			   $t=$val['address'];
			  break;
			  case 5:
			   $t=$val['qq'];
			  break;
			}
} 
header("Content-type: image/gif");
$w=10+(strlen($t)*9);
$h=22;
$im = imagecreate($w,$h);
$white = imagecolorallocate($im, 255,255,255);
$black = imagecolorallocate($im, 255,0,0);
if (strcasecmp(QISHI_DBCHARSET,"utf8")!=0)
	{
		$t=iconv(QISHI_DBCHARSET,"utf-8",$t);
	}
$ttf=QISHI_ROOT_PATH."data/contactimgfont/cn.ttc";
imagettftext($im, 12, 0, 10, 15, $black, $ttf,$t); 
imagegif($im);
ImageDestroy($im);

  
?> 
