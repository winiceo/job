<?php
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../include/plus.common.inc.php');
define("TOKEN", $_CFG['weixin_apptoken']);
define("APPID", $_CFG['weixin_appid']);
define("APPSECRET", $_CFG['weixin_appsecret']);
define("ROOT",$_CFG['site_domain']);
define("FIRST_PIC",$_CFG['weixin_first_pic']);
define("DEFAULT_PIC",$_CFG['weixin_default_pic']);
define("SITE_NAME",$_CFG['site_name']);
define("WAP_DOMAIN",rtrim($_CFG['wap_domain'],"/")."/");
define("APIOPEN", $_CFG['weixin_apiopen']);
define("PHP_VERSION",PHP_VERSION);
define("EncodingAESKey",$_CFG['weixin_encoding_aes_key']);

require_once(QISHI_ROOT_PATH.'include/mysql.class.php');
require_once(QISHI_ROOT_PATH.'include/weixinencodingaes/wxBizMsgCrypt.php');

class wechatCallbackapiTest extends mysql
{
	public $timestamp;
	public $nonce;
	public $msg_signature;
	public $encrypt_type;
	public $content;

	//验证签名
	public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature())
		{
        	exit($echoStr);
        }
    }
    //响应消息
    public function responseMsg()
    {
		if(!$this->checkSignature())
		{
        	exit();
        };
        $this->timestamp  = $_GET['timestamp'];
		$this->nonce = $_GET["nonce"];
		$this->msg_signature  = $_GET['msg_signature'];
		$this->encrypt_type = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? "aes" : "raw";

		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
			//解密
			if ($this->encrypt_type == 'aes'){
				$pc = new WXBizMsgCrypt(TOKEN, EncodingAESKey, APPID);                
				$decryptMsg = "";  //解密后的明文
				$errCode = $pc->DecryptMsg($this->msg_signature, $this->timestamp, $this->nonce, $postStr, $decryptMsg);
				$postStr = $decryptMsg;
			}
			if($this->check_php_version("5.2.11")){
				libxml_disable_entity_loader(true);
			}
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $rxType = trim($postObj->MsgType);
             
            //消息类型分离
            switch ($rxType)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$rxType;
                    break;
            }
            //加密
			if ($this->encrypt_type == 'aes'){
				$encryptMsg = ''; //加密后的密文
				$errCode = $pc->encryptMsg($result, $this->timeStamp, $this->nonce, $encryptMsg);
				$result = $encryptMsg;
			}
            echo $result;
        }else {
            echo "";
            exit;
        }
	}	
	//接收事件消息
    private function receiveEvent($object)
    {
    	global $_CFG;
        switch ($object->Event)
        {
            case "subscribe":
                $this->content = "欢迎关注".$_CFG['site_name']."！
1.绑定您的".$_CFG['site_name']."帐号，求职招聘更加方便，并实时接收提醒通知。<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点此立即绑定</a>
2.您可以回复【j】或【n】获取紧急、最新招聘信息，或者回复职位、公司等关键词如【销售】获取相关职位信息。
3.每日签到，免费获取积分，商城好礼兑不停！";
                break;
            case "LOCATION":
                  $map=(array)$object;
            	$cache_file_path =QISHI_ROOT_PATH . 'data/weixin/location/'.$object->FromUserName.'.php';
            	$content = "<?php\r\n";
			$content .= " return \$data=".var_export($map, true).";\r\n";
			if (!file_put_contents($cache_file_path, $content, LOCK_EX))
			{
				$fp = @fopen($cache_file_path, 'wb+');
				if (!$fp)
				{
					exit('生成缓存文件失败');
				}
				if (!@fwrite($fp, trim($content)))
				{
					exit('生成缓存文件失败');
				}
				@fclose($fp);
			}
	            break;
            case "SCAN":
                $this->actionScan($object);
                break;
            case "CLICK":
            	$this->check_weixin_open($object);
                switch ($object->EventKey)
                {
                    case "binding"://绑定
                    	$this->clickBinding($object);
						break;
					case "resume_refresh"://刷新简历
						$this->clickResumeRefresh($object);
						break;
					case "nearby_jobs"://周边职位
						$this->clickNearbyJobs($object);
						break;
					case "jobs_refresh"://刷新职位
						$this->clickJobsRefresh($object);
						break;
					case "sign_day"://每日签到
						$this->clickSignDay($object);
						break;
                }
                break;
                case "unsubscribe":
	            	$fromUsername = addslashes($object->FromUserName);
	                $sql = "update ".table('members')." set weixin_openid='' where weixin_openid='".$fromUsername."'";
			  		$this->query($sql);
                break;
            default:
                $this->content = "欢迎关注".$_CFG['site_name']."！
1.绑定您的".$_CFG['site_name']."帐号，求职招聘更加方便，并实时接收提醒通知。<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点此立即绑定</a>
2.您可以回复【j】或【n】获取紧急、最新招聘信息，或者回复职位、公司等关键词如【销售】获取相关职位信息。
3.每日签到，免费获取积分，商城好礼兑不停！";
                break;
        }
        if(is_array($this->content)){
            if (isset($this->content[0])){
                $result = $this->transmitNews($object, $this->content);
            }
        }else{
            $result = $this->transmitText($object, $this->content);
        }

        return $result;
    }
    //接收文本消息
    private function receiveText($object)
    {
    	$this->check_weixin_open($object);
        $keyword = trim($object->Content);
        $keyword = utf8_to_gbk($keyword);
		$keyword = addslashes($keyword);
       
        //自动回复模式
        $this->enterSearch($object,$keyword);
        if(is_array($this->content)){
            if (isset($this->content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $this->content);
            }
        }else{
            $result = $this->transmitText($object, $this->content);
        }
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
		$content = gbk_to_utf8($content);
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
        	$item = array_map("gbk_to_utf8", $item);
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }
    //验证签名
	private function checkSignature()
	{
	    $signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );		
		if($tmpStr == $signature )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	private function get_user_info($fromUsername){
		$usinfo = array();
		$fromUsername = addslashes($fromUsername);
		$usinfo_obj = $this->query("select * from ".table('members')." where weixin_openid='".$fromUsername."' limit 1");
		while($row = $this->fetch_array($usinfo_obj)){
			$usinfo = $row;
		}
		return $usinfo;
	}
	// 获取会员积分
	private function get_user_points($uid){
		$uid=intval($uid);
		$points = $this->getone("select points from ".table('members_points')." where uid='".$uid."' limit 1");
		return $points['points'];
	}
	// 获取积分规则
	private function get_points_rule()
	{
		$cache_file_path =QISHI_ROOT_PATH. "data/cache_points_rule.php";
		@include($cache_file_path);
		return $data;
	}
	// 操作会员积分
	private function report_deal($uid,$i_type=1,$points=0)
	{
		$points=intval($points);
		$uid=intval($uid);
		$points_val=$this->get_user_points($uid);
		if ($i_type==1)
		{
		$points_val=$points_val+$points;
		}
		if ($i_type==2)
		{
		$points_val=$points_val-$points;
		$points_val=$points_val<0?0:$points_val;
		}
		$sql = "UPDATE ".table('members_points')." SET points= '{$points_val}' WHERE uid='{$uid}' LIMIT 1";
		if (!$this->query($sql))return false;
		return true;
	}
	// 获取套餐
	private function get_user_setmeal($uid)
	{
		$uid=intval($uid);
		$sql = "select * from ".table('members_setmeal')."  WHERE uid='{$uid}' AND  effective=1 LIMIT 1";
		return $this->getone($sql);
	}
	//查看会员的日志
	private function get_last_refresh_date($uid,$type,$mode=0)
	{
		$sql = "select max(addtime) from ".table('refresh_log')." where uid=".intval($uid).' and ' . "`type`='".$type."' and mode = ".$mode;
		return $this->getone($sql);
	}
	//统计今天刷新次数
	private function get_today_refresh_times($uid,$type,$mode=0)
	{
		$today = strtotime(date('Y-m-d'));
		$tomorrow = $today+3600*24;
		$sql = "select count(*) from ".table('refresh_log')." where uid=".intval($uid).' and ' . "`type`='".$type."' and addtime>".$today." and addtime<".$tomorrow." and mode = ".$mode;
		return $this->getone($sql);
	}
	private function write_refresh_log($uid,$type,$mode=0)
	{
		$setsqlarr['uid'] = $uid;
		$setsqlarr['mode'] = $mode;
		$setsqlarr['type'] = $type;
		$setsqlarr['addtime'] = time();
		$this->inserttable(table('refresh_log'),$setsqlarr);
	}
	private function write_memberslog($uid,$utype,$type,$username,$str,$mode,$op_type,$op_type_cn,$op_used,$op_leave)
	{
	 	global $online_ip,$ip_address;
	 	$setarr["log_uid"]=$uid;
	 	$setarr["log_username"]=$username;
	 	$setarr["log_utype"]=$utype;
	 	$setarr["log_type"]=$type;
	 	$setarr["log_addtime"]=time();
	 	$setarr["log_ip"]=$online_ip;
	 	$setarr["log_address"]=$ip_address;
	 	$setarr["log_value"]=$str;
	 	$setarr["log_mode"]=$mode;
	 	$setarr["log_op_type"]=$op_type;
	 	$setarr["log_op_type_cn"]=$op_type_cn;
	 	$setarr["log_op_used"]=$op_used;
	 	$setarr["log_op_leave"]=$op_leave;
	 	return $this->inserttable(table("members_log"),$setarr);
	}
	private function refresh_jobs($uid)
	{
		global $_CFG;
		$uid=intval($uid);
		$time=time();
		$deadline=strtotime("".intval($_CFG['company_add_days'])." day");
		if (!$this->query("update  ".table('company_profile')."  SET refreshtime='{$time}' WHERE uid='{$uid}' LIMIT 1 ")) return false;
		if (!$this->query("update  ".table('jobs')."  SET refreshtime='{$time}',deadline='{$deadline}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_tmp')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_hot')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_key')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_rtime')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_scale')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_stickrtime')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		if (!$this->query("update  ".table('jobs_search_wage')."  SET refreshtime='{$time}' WHERE  uid='{$uid}'")) return false;
		return true;
	}
	private function check_php_version($version) {
		 $php_version = explode('-',phpversion());
		 // strnatcasecmp( $php_version[0], $version ) 0表示等于，1表示大于，-1表示小于
		 $is_pass = strnatcasecmp($php_version[0],$version)>=0?true:false;
		 return $is_pass;
	}
    //检查网站微信接口是否开启
	private function check_weixin_open($object){
		if(APIOPEN=='0')
		{
			$this->content="网站微信接口已经关闭";
			$this->transmitText($object,$this->content);
		}
	}
    //绑定事件
	private function clickBinding($object){
		global $_CFG;
		$usinfo = $this->get_user_info($object->FromUserName);
		if(!empty($usinfo)){

			$this->content="您已绑定过".$_CFG['site_name']."帐号【".$usinfo['username']."】,如需解绑,请回复'解绑'";
		}else
		{
			$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
		}
	}
    //刷新简历
	private function clickResumeRefresh($object)
	{
		global $_CFG;
		$usinfo = $this->get_user_info($object->FromUserName);
		if(!empty($usinfo)){
			if($usinfo['utype']!=2)
			{
				$this->content = "本操作需要绑定个人帐号！";
			}
			else
			{	
				$uid = $usinfo['uid'];
				$refrestime=$this->get_last_refresh_date($uid,"2001");
				$duringtime=time()-$refrestime['max(addtime)'];
				$space = $_CFG['per_refresh_resume_space']*60;
				$refresh_time = $this->get_today_refresh_times($uid,"2001");
				if($_CFG['per_refresh_resume_time']!=0 && ($refresh_time['count(*)']>=$_CFG['per_refresh_resume_time']))
				{
					$this->content = "每天最多只能刷新".$_CFG['per_refresh_resume_time']."次,您今天已超过最大刷新次数限制！";
				}
				elseif($duringtime<=$space)
				{
					$this->content = $_CFG['per_refresh_resume_space']."分钟内不能重复刷新简历！";
				}
				else
				{
					$resume_num = $this->get_total("select count(*) as num from ".table('resume')." where uid=".$uid);
					$rule=$this->get_points_rule();
					$user_points=$this->get_user_points();
					// 判断积分是否足够
					if($rule['resume_refresh']['type']==2 && $user_points<$rule['resume_refresh']['value']*$resume_num)
					{
						$this->content = "您的积分不足不能刷新简历！";
					}
					else
					{
						$time = time();
						$this->query("update ".table('resume')." set refreshtime=".$time." where uid=".$uid);
						$this->query("update ".table('resume_search_key')." set refreshtime=".$time." where uid=".$uid);
						$this->query("update ".table('resume_search_rtime')." set refreshtime=".$time." where uid=".$uid);
						// 刷新日志
						for ($i=$resume_num; $i > 0 ; $i--) { 
							write_refresh_log($uid,2001);
						}
						/*积分操作 ----*/
						$this->report_deal($uid,$rule['resume_refresh']['type'],$rule['resume_refresh']['value']*$resume_num);
						$this->content = "刷新成功!";
					}
				}
			}
		}else{
			$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
		}
	}
	// 周边职位
	private function clickNearbyJobs($object){
		global $_CFG;
		$usinfo = $this->get_user_info($object->FromUserName);
		if(!empty($usinfo)){
			if($usinfo['utype']!=2)
			{
				$this->content = "本操作需要绑定个人帐号！";
			}
			else
			{
				require_once(QISHI_ROOT_PATH . 'data/weixin/location/'.$object->FromUserName.'.php');
				if(empty($data['Latitude']) || empty($data['Longitude']))
				{
					$this->content = "奇怪了，难道HR们都偷懒放假去了？我什么职位都没捞着，要不您换个地方再发位置给我？如何发送地理位置？";
				}
				$lat = $data['Latitude'];
				$lng = $data['Longitude'];
				$jobstable=table('jobs_search_key');
				$rows = 5;
				$offset = 0;
				// 获取周边职位
				if(!empty($lng) && !empty($lat))
				{
					$idresult = $this->query("SELECT id , ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM {$jobstable}  WHERE map_x!='' AND map_y!='' ORDER BY juli ASC   LIMIT {$offset},{$rows}");
					while($row = $this->fetch_array($idresult))
					{
						$id[]=$row['id'];
					}
				}
				if (!empty($id))
				{
					$wheresql=" WHERE id IN (".implode(',',$id).") ";
					$sql = "SELECT *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$lat."*PI()/180-map_y*PI()/180)/2),2)+COS(".$lat."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$lng."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS juli FROM ".table('jobs').$wheresql."  ORDER BY juli ASC , stick DESC , refreshtime DESC limit 3";
					$jobs_list = $this->getall($sql);
				}
				if(empty($jobs_list))
				{
					$this->content="奇怪了，难道HR们都偷懒放假去了？我什么职位都没捞着，要不您换个地方再发位置给我？如何发送地理位置？";
				}
				else
				{
					$i=1;
					foreach ($jobs_list as $key => $value)
					{
						$jobs_name=$value['jobs_name'];
						$jobs_name=$value['jobs_name'];				
					    $companyname=$value['companyname'];
					    $title=$jobs_name."--".$companyname;
					    $url=WAP_DOMAIN."jobs-show.php?id=".$value['id']."&from=".$object->FromUserName;
					    if($i==1){
		                    $picurl=$first_pic;	
						}else{
							$picurl=$default_pic;	
						}
						$i++;
						$this->content[] = array("Title"=>$title, "Description"=>$con, "PicUrl"=>$picurl, "Url" =>$url);	
					}
				}
			}
		}else{
			$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
		}
	}
	// 刷新职位
	private function clickJobsRefresh($object)
	{
		global $_CFG;
		$usinfo = $this->get_user_info($object->FromUserName);
		if(!empty($usinfo)){
			if($usinfo['utype']!=1)
			{
				$this->content = "本操作需要绑定企业帐号！";
			}
			else
			{
				$uid = $usinfo['uid'];
				$jobs_num= $this->get_total("select count(*) as num from ".table('jobs')." where uid=$uid ");
				//积分模式
				if($_CFG['operation_mode']=='1')
				{
					$mode = 1;
					//限制刷新时间
					//最近一次的刷新时间
					$refrestime=$this->get_last_refresh_date($uid,"1001",1);
					$duringtime=time()-$refrestime['max(addtime)'];
					$space = $_CFG['com_pointsmode_refresh_space']*60;
					$refresh_time = $this->get_today_refresh_times($uid,"1001",1);
					if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
					{
					$this->content = "每天最多只能刷新".$_CFG['com_pointsmode_refresh_time']."次,您今天已超过最大刷新次数限制！";
					}
					elseif($duringtime<=$space)
					{
					$this->content = $_CFG['com_pointsmode_refresh_space']."分钟内不能重复刷新职位！";
					}
					else 
					{
						$points_rule=$this->get_points_rule();
						if($points_rule['jobs_refresh']['value']>0)
						{
							$user_points=$this->get_user_points();
							$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
							if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
							{
							$this->content = "您的".$_CFG['points_byname']."不足，请先充值！";
							}
							//加/减 积分
							$this->report_deal($uid,$points_rule['jobs_refresh']['type'],$total_point);
							$user_points=$this->get_user_points($uid);
							$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
							$this->write_memberslog($uid,1,9001,$usinfo['username'],"刷新了{$jobs_num}条职位，({$operator}{$total_point})，(剩余:{$user_points})",1,1003,"刷新职位","{$operator}{$total_point}","{$user_points}");
							for ($i=0; $i < $jobs_num; $i++) { 
								$this->write_refresh_log($_SESSION['uid'],1001,$mode);
							}
							$this->refresh_jobs($uid);
							$this->content = "刷新成功！";
						}
						else
						{
							for ($i=0; $i < $jobs_num; $i++) { 
								$this->write_refresh_log($_SESSION['uid'],1001,$mode);
							}
							$this->refresh_jobs($uid);
							$this->content = "刷新成功！";
						}
					}
				}	
				//套餐模式
				elseif($_CFG['operation_mode']=='2') 
				{
					$mode = 2;
					//限制刷新时间
					$setmeal=$this->get_user_setmeal($uid);
					if (empty($setmeal))
					{			
						$this->content = "您还没有开通服务，请开通";
					}
					elseif ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
					{		
						$this->content = "您的服务已经到期，请重新开通";
					}
					else
					{
						//最近一次的刷新时间
						$refrestime=$this->get_last_refresh_date($uid,"1001",2);
						$duringtime=time()-$refrestime['max(addtime)'];
						$space = $setmeal['refresh_jobs_space']*60;
						$refresh_time = $this->get_today_refresh_times($uid,"1001",2);
						if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
						{
							$this->content = "每天最多只能刷新".$setmeal['refresh_jobs_time']."次,您今天已超过最大刷新次数限制！";
						}
						elseif($duringtime<=$space){
							$this->content = $setmeal['refresh_jobs_space']."分钟内不能重复刷新职位！";
						}
						else
						{
							for ($i=0; $i < $jobs_num; $i++) { 
								$this->write_refresh_log($_SESSION['uid'],1001,$mode);
							}
							$this->refresh_jobs($uid);
							$this->content = "刷新成功！";
						}
					}
				}
				//混合模式
				elseif($_CFG['operation_mode']=='3') 
				{
					$setmeal=$this->get_user_setmeal($_SESSION['uid']);
					//该会员套餐过期 (套餐过期后就用积分来刷)
					if($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
					{
						//后台开通  服务超限时启用积分消费
						if($_CFG['setmeal_to_points']=='1')
						{
							$mode = 1;
							//用积分来刷新职位的话->按照积分模式限制->先看它是否超过次数限制和时间间隔
							$refrestime=$this->get_last_refresh_date($uid,"1001",1);
							$duringtime=time()-$refrestime['max(addtime)'];
							$space = $setmeal['refresh_jobs_space']*60;
							$refresh_time = $this->get_today_refresh_times($uid,"1001",1);
							if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
							{
								$this->content = "您的套餐已经过期，刷新职位需消耗积分，但是用积分刷新职位每天最多只能刷新".$_CFG['com_pointsmode_refresh_time']."次,您今天已超过最大刷新次数限制，请续费延期套餐！";
							}
							elseif($duringtime<=$space)
							{
								$this->content = "您的套餐已经过期，刷新职位需消耗积分，但是用积分刷新职位".$_CFG['com_pointsmode_refresh_space']."分钟内不能重复刷新！";
							}
							else
							{
								$points_rule=$this->get_points_rule;
								if($points_rule['jobs_refresh']['value']>0)
								{
									$user_points=$this->get_user_points($_SESSION['uid']);
									$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
									if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
									{
										$this->content ="您的套餐已过期，需消耗积分来刷新职位。但目前您的".$_CFG['points_byname']."不足，请先充值积分或续费延期套餐！";
									}
									//加/减 积分
									$this->report_deal($uid,$points_rule['jobs_refresh']['type'],$total_point);
									$user_points=$this->get_user_points($uid);
									$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
									$this->write_memberslog($uid,1,9001,$usinfo['username'],"刷新了{$jobs_num}条职位，({$operator}{$total_point})，(剩余:{$user_points})",1,1003,"刷新职位","{$operator}{$total_point}","{$user_points}");
									for ($i=0; $i < $jobs_num; $i++) { 
										$this->write_refresh_log($_SESSION['uid'],1001,$mode);
									}
									$this->refresh_jobs($uid);
									$this->content = "刷新成功！";
								}
								else
								{
									for ($i=0; $i < $jobs_num; $i++) { 
										$this->write_refresh_log($_SESSION['uid'],1001,$mode);
									}
									$this->refresh_jobs($uid);
									$this->content = "刷新成功！";
								}
							}
						}
						//后台没有开通  服务超限时启用积分消费
						else
						{
							$this->content = "您的服务已经到期，请重新开通";
						}
					}
					//该会员套餐未过期 
					else
					{
						$mode = 2;
						$points_rule=$this->get_points_rule();
						$user_points=$this->get_user_points($uid);
						//获取当天刷新的职位数(在套餐模式下刷新的)
						$refresh_time = $this->get_today_refresh_times($uid,"1001",2);
						//当天剩余刷新职位数(在套餐模式下刷新的)
						$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
						//刷新职位数 大于 剩余刷新职位数 (超了)
						if($setmeal['refresh_jobs_time']!=0&&($jobs_num>$surplus_time))
						{
							//后台开通  服务超限时启用积分消费
							if($_CFG['setmeal_to_points']=='1')
							{
								//判断当天积分刷新职位数 是否 超过次数和间隔限制
								$refrestime=$this->get_last_refresh_date($uid,"1001",1);
								$duringtime=time()-$refrestime['max(addtime)'];
								$space = $_CFG['com_pointsmode_refresh_space']*60;
								$refresh_time = $this->get_today_refresh_times($uid,"1001",1);
								if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
								{
								$this->content = "刷新职位数超过了套餐次数限制，刷新职位需消耗积分，每天用积分刷新最多只能刷新".$_CFG['com_pointsmode_refresh_time']."次,您今天已超过最大刷新次数限制！";
								}
								elseif($duringtime<=$space)
								{
									$this->content = "刷新职位数超过了套餐次数限制，刷新职位需消耗积分，并且".$_CFG['com_pointsmode_refresh_space']."分钟内不能重复刷新职位！";			
								}
								else
								{
									if($points_rule['jobs_refresh']['value']>0)
									{
										//超出的职位若想刷新 所需的积分
										$beyond = $jobs_num - $surplus_time;
										$surplus_total_point=$beyond*$points_rule['jobs_refresh']['value'];
										//会员积分不足以满足 所需积分
										if ($surplus_total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
										{
											$this->content = "您刷新职位数超过了套餐次数限制，超过的次数需消耗您的积分，但是您的".$_CFG['points_byname']."不足，请先充值！";
										}
										//判断超出的职位数是否 大于 积分限制次数
										if($beyond > $_CFG['com_pointsmode_refresh_time'] && $_CFG['com_pointsmode_refresh_time']!=0)
										{
											$this->content = "您刷新职位数超过了套餐次数限制，超过的职位数需消耗您的积分，并且也超过了".$_CFG['points_byname']."限制次数!";
										}
										for ($i=0; $i < $surplus_time; $i++) 
										{ 
											$this->refresh_jobs($uid);
											$this->$this->write_memberslog($uid,1,2004,$usinfo['username'],"刷新职位");
											$this->write_refresh_log($_SESSION['uid'],1001,2);
										}
										for ($i=$surplus_time; $i < $jobs_num; $i++) 
										{ 
											$this->refresh_jobs($uid);
											$this->write_memberslog($_SESSION['uid'],1,2004,$_SESSION['username'],"刷新职位");
											$this->write_refresh_log($uid,1001,1);
										}
										//更新会员积分
										//加/减 积分
										$this->report_deal($uid,$points_rule['jobs_refresh']['type'],$surplus_total_point);
										$user_points=$this->get_user_points($uid);
										$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
										$this->write_memberslog($uid,1,9001,$usinfo['username'],"刷新了{$jobs_num}条职位，({$operator}{$total_point})，(剩余:{$user_points})",1,1003,"刷新职位","{$operator}{$total_point}","{$user_points}");
										$this->content = "刷新成功!";
									}
									else
									{
										for ($i=0; $i < $jobs_num; $i++) { 
											$this->write_refresh_log($_SESSION['uid'],1001,$mode);
										}
										$this->refresh_jobs($uid);
										$this->content = "刷新成功！";
									}
								}
							}
							//后台没有开通  服务超限时启用积分消费
							else
							{
								$this->content = "您刷新职位数超过了套餐次数限制!";
							}
						}
						//刷新职位数 小于 剩余刷新职位数 (没超)
						else
						{
							//最近一次的刷新时间
							$refrestime=$this->get_last_refresh_date($uid,"1001",2);
							$duringtime=time()-$refrestime['max(addtime)'];
							$space = $setmeal['refresh_jobs_space']*60;
							$refresh_time = $this->get_today_refresh_times($uid,"1001",2);
							if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
							{
								$this->content = "每天最多只能刷新".$setmeal['refresh_jobs_time']."次,您今天已超过最大刷新次数限制！";
							}
							elseif($duringtime<=$space)
							{
								$this->content = $setmeal['refresh_jobs_space']."分钟内不能重复刷新职位！";	
							}
							else
							{
								for ($i=0; $i < $jobs_num; $i++) { 
									$this->write_refresh_log($_SESSION['uid'],1001,$mode);
								}
								$this->refresh_jobs($uid);
								$this->content = "刷新成功！";
							}
						}
					}
				}
			}
		}else{
			$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
		}
	}
	// 每日签到
	private function clickSignDay($object)
	{
		global $_CFG;
		$usinfo = $this->get_user_info($object->FromUserName);
		if(!empty($usinfo))
		{
			// 积分操作限制 
			$uid = $usinfo['uid'];
			$time =time();
			$today=mktime(0, 0, 0,date('m'), date('d'), date('Y'));
			$info=$this->getone("SELECT uid FROM ".table('members_handsel')." WHERE uid =".$uid." AND htype='sign_day' AND addtime>{$today}  LIMIT 1");
			if(!empty($info))
			{
				$this->content = "今日已签到，明天再来吧!";
			}
			else
			{
				$members_handsel_arr['uid']=$uid;
				$members_handsel_arr['htype']="sign_day";
				$members_handsel_arr['points']=$_CFG['weixin_signday'];
				$members_handsel_arr['addtime']=$time;
				$this->inserttable(table("members_handsel"),$members_handsel_arr);
				/*
					增加积分
				*/
				$this->report_deal($uid,$i_type=1,$_CFG['weixin_signday']);
				$this->content = "签到成功，获得 ".$_CFG['weixin_signday']." 积分！回复“签到记录”查看历史签到。
每日签到，免费获取积分，商城好礼兑不停，快邀请好友加入吧！";
			}
		}
		else{
			$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
		}
	}
    //扫描事件
	private function actionScan($object)
	{
		global $_CFG;
		$event_key = $object->EventKey;
		if($event_key<=10000000)
		{
			$usinfo = $this->get_user_info($object->FromUserName);
			if(!empty($usinfo)){
				$this->content = "<a href='".WAP_DOMAIN."login.php?act=weixin_login&openid=".$object->FromUserName."&uid=".$usinfo['uid']."&event_key=".$event_key."'>点此立即登录".SITE_NAME."网页</a>";
			}else{
				$this->content="您还未绑定".$_CFG['site_name']."帐号，现在开始绑定：<a href='".WAP_DOMAIN."binding.php?from=".$object->FromUserName."'>点击开始注册/绑定帐号</a>";
			}
		}
		elseif($event_key>10000000 && $event_key<=20000000)
		{
			$this->content = "请选择会员注册类型.<a href='".WAP_DOMAIN."login.php?act=weixin_reg&openid=".$object->FromUserName."&event_key=".$event_key."&utype=1'>企业会员</a>；<a href='".WAP_DOMAIN."login.php?act=weixin_reg&openid=".$object->FromUserName."&event_key=".$event_key."&utype=2'>个人会员</a>";
		}
		elseif($event_key>20000000 && $event_key<=30000000)
		{
			$usinfo = $this->get_user_info($object->FromUserName);
			if($usinfo){
				
				$this->content="您已绑定过".$_CFG['site_name']."帐号【".$usinfo['username']."】。如需解绑，请回复“解绑”";
			}else{
				$fp = @fopen(QISHI_ROOT_PATH . 'data/weixin/'.($event_key%10).'/'.$event_key.'.txt', 'wb+');
				@fwrite($fp, $object->FromUserName);
				@fclose($fp);
				$this->content="恭喜，您已成功绑定".$_CFG['site_name'].",下次可直接扫码登录了哦！如需解绑，请回复“解绑”";
			}
		}
	}
	
    //输入关键字搜索职位
	private function enterSearch($object,$keyword){
		if($keyword=="解绑")
		{
			$usinfo = $this->get_user_info($object->FromUserName);
			if(!empty($usinfo))
			{
				$this->query("UPDATE ".table('members')." SET weixin_openid=null,weixin_nick='' WHERE weixin_openid='".$object->FromUserName."'");
				$this->content="解除绑定成功！";
			}
			else
			{
				$this->content="您还没有绑定帐号！";
			}
		}
		elseif($keyword=="签到记录")
		{
			$usinfo = $this->get_user_info($object->FromUserName);
			if(!empty($usinfo))
			{
				$uid = $usinfo['uid'];
				$time =time();
				$month_time = strtotime(date('Y-m-01',$time));
				$all_num=$this->get_total("SELECT count(*) as num FROM ".table('members_handsel')." WHERE uid =".$uid." AND htype='sign_day'");
				$month_num=$this->get_total("SELECT count(*) as num FROM ".table('members_handsel')." WHERE uid =".$uid." AND htype='sign_day' and addtime>=".$month_time." and addtime<=".$time);

				$points =$this->getone("SELECT sum(points) as all_points FROM ".table('members_handsel')." WHERE uid =".$uid." AND htype='sign_day'");
				$info =$this->getone("SELECT addtime FROM ".table('members_handsel')." WHERE uid =".$uid." AND htype='sign_day' order by addtime desc limit 1");
				
				$this->content="您累计已签到: ".$all_num." 天
您本月已累计签到:".$month_num." 天
您上次签到时间:".date('Y-m-d H:i:s',$info['addtime'])."
您目前获得的总奖励为:".$points['all_points']."积分";
			}
			else
			{
				$this->content="您还没有绑定帐号！";
			}
		}
		else
		{
			$default_pic=ROOT."/data/images/".DEFAULT_PIC;
			$first_pic=ROOT."/data/images/".FIRST_PIC;
			$limit=" LIMIT 5";
			$orderbysql=" ORDER BY refreshtime DESC";
			if($keyword=="n")
			{
				$jobstable=table('jobs_search_rtime');			 
			}
			else if($keyword=="j")
			{
				$jobstable=table('jobs_search_rtime');
				$wheresql=" where `emergency`=1 ";	
			}
			else
			{
			$jobstable=table('jobs_search_key');
			$wheresql.=" where likekey LIKE '%{$keyword}%' ";
			}
			$list = $id = array();
			$idresult = $this->query("SELECT id FROM {$jobstable} ".$wheresql.$orderbysql.$limit);
			while($row = $this->fetch_array($idresult))
			{
			$id[]=$row['id'];
			}
			if (!empty($id))
			{
				$wheresql=" WHERE id IN (".implode(',',$id).") ";
				$result = $this->query("SELECT * FROM ".table('jobs').$wheresql.$orderbysql);
				$i=1;
				while($row = $this->fetch_array($result))
				{
					$jobs_name=$row['jobs_name'];				
				    $companyname=$row['companyname'];
				    $title=$jobs_name."--".$companyname;
				    $url=WAP_DOMAIN."jobs-show.php?id=".$row['id']."&from=".$object->FromUserName;
				    if($i==1){
	                    $picurl=$first_pic;	
					}else{
						$picurl=$default_pic;	
					}
					$i++;
					$this->content[] = array("Title"=>$title, "Description"=>$con, "PicUrl"=>$picurl, "Url" =>$url);	
				}
			}
			if(empty($this->content))
			{
				$this->content="没有找到包含关键字 {$keyword} 的信息，试试其他关键字";
			}
		}
	}
}

$wechatObj = new wechatCallbackapiTest($dbhost,$dbuser,$dbpass,$dbname);
if (!isset($_REQUEST['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}		
?>