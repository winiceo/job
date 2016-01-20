<?php
class CollectionZhilian{
	private $username;
	private $password;
	private $cookie_path;

	public function __construct($username,$password,$cookie_path){
		$this->username = $username;
		$this->password = $password;
		$this->cookie_path = $cookie_path;
	}

	//post模拟方法
	private function curlPost($url, $data, $timeout = 30)
	{
	    $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
	    $ch = curl_init();
	    $opt = array(
	            CURLOPT_URL     => $url,
	            CURLOPT_POST    => 1,
	            CURLOPT_HEADER  => 0,
	            CURLOPT_POSTFIELDS      => (array)$data,
	            CURLOPT_RETURNTRANSFER  => 1,
	            CURLOPT_TIMEOUT         => $timeout,
	            CURLOPT_COOKIEJAR       => $this->cookie_path,
	            );
	    if ($ssl)
	    {
	        $opt[CURLOPT_SSL_VERIFYHOST] = 2;
	        $opt[CURLOPT_SSL_VERIFYPEER] = FALSE;
	    }
	    curl_setopt_array($ch, $opt);
	    $data = curl_exec($ch);
	    curl_close($ch);
	    return $data;
	}
	//模拟登录
	public function login(){
		$arr['LoginName'] = $this->username;
		$arr['Password'] = $this->password;
		$data = $this->curlPost('https://passport.zhaopin.com/account/login', $arr,30);
		return $data;
	}
	//获取简历列表id
	public function get_resume_list(){
		$url = "http://my.zhaopin.com/myzhaopin/resume_list.asp";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// curl_setopt($ch2,CURLOPT_REFERER,$referer_url);
		// curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookieFile); // 这是 把cookie存入
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_path); //这是 使用COOKIE 
		// curl_setopt($ch2, CURLOPT_POST, 1);
		// curl_setopt($ch2, CURLOPT_POSTFIELDS, $data); 
		//curl_setopt($ch2,CURLOPT_FOLLOWLOCATION,1);
		ob_start();
		curl_exec($ch);
		curl_close($ch);
		$rs = ob_get_contents(); //$rs就是返回的内容
		ob_clean();
		preg_match_all('/<div class="resumes_img1"><a href="resume_preview.asp(.*)&Version_Number=1&language_id=1&LocationUrl=resume_list" class="resumePreview/', $rs, $arr);
		$resume_id_arr = array_unique($arr[1]);
		preg_match_all('/<div class="resumes14"><a title="(.*)" target="_blank"/', $rs, $title_arr);
		$i = 0;
		$return_arr['resume_id_arr'] = $resume_id_arr;
		$return_arr['resume_title_arr'] = array();
		foreach ($title_arr[1] as $key => $value) {
			if($i==0){
				$return_arr['resume_title_arr'][] = $value;
				$i++;
			}else{
				$i = 0;
			}
		}
		return $return_arr;
	}
	//获取简历详情html
	private function get_resume_one($str,$cookie_path){
		$url = "http://my.zhaopin.com/myzhaopin/resume_preview.asp".$str."&Version_Number=1&language_id=1&LocationUrl=resume_list";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    // curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookie_jar_index); // 这是 把cookie存入
	    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path); //这是 使用COOKIE
	    ob_start();
	    curl_exec($ch);
	    curl_close($ch);
	    $rs = ob_get_contents(); //$rs就是返回的内容
	    ob_clean();
	    return $rs;
	}
	//解析简历
	public static function split_resume($str,$cookie_path){
		$html = self::get_resume_one($str,$cookie_path);
	    /**
	     * 解析简历基本信息
	     */
	    $arr['basicinfo'] = self::get_resume_basicinfo($html);
	    /**
	     * 解析简历工作经历
	     */
	    $arr['workinfo'] = self::get_resume_workinfo($html);
	    /**
	     * 解析简历教育经历
	     */
	    $arr['eduinfo'] = self::get_resume_eduinfo($html);
	    /**
	     * 解析简历培训经历
	     */
	    $arr['traininginfo'] = self::get_resume_traininginfo($html);
	    /**
	     * 解析简历语言能力
	     */
	    $arr['languageinfo'] = self::get_resume_languageinfo($html);
	    /**
	     * 解析简历证书
	     */
	    $arr['credentinfo'] = self::get_resume_credentinfo($html);
	    return $arr;
	}
	/**
     * 解析简历基本信息
     */
	private function get_resume_basicinfo($html){
		/**
	     * 解析简历基本信息
	     */
	    preg_match('/<h1>(.*)<\/h1>/',$html,$fullname);
	    $basicinfo['fullname'] = $fullname[1];
	    $pattern_basic = '/<\/h1>(.*)<\/a> <br \/>/s';//提取简历基本信息正则
	    preg_match($pattern_basic,$html,$basic_arr);//提取简历基本信息
	    $basic = $basic_arr[1];//处理基本信息
	    $arr = explode('|',$basic);//切割为数组，目的是为了提取出性别等信息
	    $basicinfo['sex_cn'] = trim(strip_tags($arr[0]));//性别
	    $basicinfo['marriage_cn'] = trim(strip_tags($arr[1]));//婚姻状况
	    $basicinfo['birthdate'] = intval(trim(strip_tags($arr[2])));//出生年份
	    $basicinfo['householdaddress'] = str_replace('户口：','',trim(strip_tags($arr[3])));//户口所在地
	    $residence_exp_arr = explode('<br />',$arr[4]);//拆分出现居住地和工作经验
	    $basicinfo['residence'] = str_replace('现居住于','',trim(strip_tags($residence_exp_arr[0])));
	    $basicinfo['experience_cn'] = trim(strip_tags($residence_exp_arr[1]));
	    $tel_pattern = '/(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}/';//提取手机号正则
	    preg_match($tel_pattern,$basic,$mobile_arr);//提取手机号
	    $basicinfo['telephone'] = $mobile_arr[0];
	    $email_pattern = '/<a href="mailto:(.*)">/';
	    preg_match($email_pattern,$basic,$email_arr);
	    $basicinfo['email'] = $email_arr[1];
	    /**
	     * 解析简历求职意向
	     */
	    $pattern_intention = '/求职意向(.*)<\/ul>/s';//提取简历求职意向正则
	    preg_match($pattern_intention,$html,$intention_arr);
	    preg_match('/工作性质：(.*)<\/li>/',$intention_arr[1],$nature_arr);
	    $basicinfo['nature_cn'] = strip_tags($nature_arr[1]);
	    preg_match('/期望职业：(.*)<\/li>/',$intention_arr[1],$intention_jobs_arr);
	    $basicinfo['intention_jobs'] = strip_tags($intention_jobs_arr[1]);
	    preg_match('/期望行业：(.*)<\/li>/',$intention_arr[1],$trade_arr);
	    $basicinfo['trade_cn'] = strip_tags($trade_arr[1]);
	    preg_match('/工作地区：(.*)<\/li>/',$intention_arr[1],$district_arr);
	    $basicinfo['district_cn'] = strip_tags($district_arr[1]);
	    preg_match('/期望月薪：(.*)<\/li>/',$intention_arr[1],$wage_arr);
	    $basicinfo['wage_cn'] = strip_tags($wage_arr[1]);
	    preg_match('/目前状况：(.*)<\/li>/',$intention_arr[1],$current_arr);
	    $basicinfo['current_cn'] = trim(strip_tags($current_arr[1]));

	    /**
	     * 解析简历自我评价
	     */
	    $pattern_specialty = '/<p style="word-break: break-all; ">(.*)工作经历/s';//提取简历自我评价正则
	    preg_match($pattern_specialty,$html,$specialty_arr);
	    $basicinfo['specialty'] = trim(strip_tags($specialty_arr[1]));

	    return $basicinfo;
	}
	/**
     * 解析简历工作经历
     */
	private function get_resume_workinfo($html){
		$workinfo = array();
	    $pattern_work = '/工作经历(.*)项目经验/s';//提取简历工作经历正则
	    preg_match($pattern_work,$html,$work_tmp);
	    if($work_tmp){
	    	$work_arr = explode('<div class="work-experience">',$work_tmp[1]);
		    unset($work_arr[0]);
		    foreach ($work_arr as $key => $value) {
		        $workinfo[] = self::get_resume_workinfo_one($value);
		    }	
	    }
	    return $workinfo;
	}
	/**
     * 解析简历教育经历
     */
	private function get_resume_eduinfo($html){
		$eduinfo = array();
	    $pattern_edu = '/教育经历(.*)在校学习情况/s';//提取简历教育经历正则
	    preg_match($pattern_edu,$html,$edu_tmp);
	    if($edu_tmp){
	    	$edu_arr = explode('<div class="education-background">',$edu_tmp[1]);
		    unset($edu_arr[0]);
		    foreach ($edu_arr as $key => $value) {
		        $eduinfo[] = self::get_resume_eduinfo_one($value);
		    }	
	    }
	    return $eduinfo;
	}
	/**
     * 解析简历培训经历
     */
	private function get_resume_traininginfo($html){
		$traininginfo = array();
	    $pattern_training = '/培训经历(.*)证书/s';//提取简历培训经历正则
	    preg_match($pattern_training,$html,$training_tmp);
	    if($training_tmp){
	    	$training_arr = explode('<div class="training">',$training_tmp[1]);
		    unset($training_arr[0]);
		    foreach ($training_arr as $key => $value) {
		        $traininginfo[] = self::get_resume_traininginfo_one($value);
		    }	
	    }
	    return $traininginfo;
	}
	/**
     * 解析简历语言能力
     */
	private function get_resume_languageinfo($html){
		$languageinfo = array();
	    $pattern_language = '/语言能力(.*)专业技能/s';//提取简历语言能力正则
	    preg_match($pattern_language,$html,$language_tmp);
	    if($language_tmp){
	    	$language_arr = explode('<div class="language-skill">',$language_tmp[1]);
		    unset($language_arr[0]);
		    foreach ($language_arr as $key => $value) {
		        $languageinfo[] = self::get_resume_languageinfo_one($value);
		    }	
	    }
	    return $languageinfo;
	}
	/**
     * 解析简历证书
     */
	private function get_resume_credentinfo($html){
		$credentinfo = array();
	    $pattern_credent = '/证书(.*)语言能力/s';//提取简历证书正则
	    preg_match($pattern_credent,$html,$credent_tmp);
	    if($credent_tmp){
	    	$credent_arr = explode('<div class="certificates">',$credent_tmp[1]);
		    unset($credent_arr[0]);
		    foreach ($credent_arr as $key => $value) {
		        $credentinfo[] = self::get_resume_credentinfo_one($value);
		    }	
	    }
	    return $credentinfo;
	}
	/**
     * 解析简历单个工作经历
     */
	function get_resume_workinfo_one($html){
	    $arr1 = explode('<h6>',$html);
	    $time_str = str_replace(' ','',$arr1[0]);
	    $time_arr = explode('--',$time_str);
	    $starttime_arr = explode('/',strip_tags($time_arr[0]));
	    $work['startyear'] = trim($starttime_arr[0]);
	    $work['startmonth'] = trim($starttime_arr[1]);

	    if(trim(strip_tags($time_arr[1]))=='至今'){
	        $work['todate'] = 1;
	    }else{
	        $endtime_arr = explode('/',strip_tags($time_arr[1]));
	        $work['endyear'] = trim($endtime_arr[0]);
	        $work['endmonth'] = trim($endtime_arr[1]);    
	    }
	    $arr2 = explode('|',$arr1[1]);
	    
	    $work['companyname'] = strip_tags($arr2[0]);
	    $jobs_arr = explode('</h6>',$arr2[2]);
	    $work['jobs'] = trim(strip_tags($jobs_arr[0]));
	    preg_match('/工作描述：(.*)<\/p>/s',$arr1[1],$achievements);
	    $work['achievements'] = trim(strip_tags($achievements[1]));
	   	return $work;
	}
	/**
     * 解析简历单个教育经历
     */
	function get_resume_eduinfo_one($html){
	    $arr1 = explode('<h6>',$html);
	    $time_str = str_replace(' ','',$arr1[0]);
	    $time_arr = explode('--',$time_str);
	    $starttime_arr = explode('/',strip_tags($time_arr[0]));
	    $edu['startyear'] = trim($starttime_arr[0]);
	    $edu['startmonth'] = trim($starttime_arr[1]);

	    if(trim(strip_tags($time_arr[1]))=='至今'){
	        $edu['todate'] = 1;
	    }else{
	        $endtime_arr = explode('/',strip_tags($time_arr[1]));
	        $edu['endyear'] = trim($endtime_arr[0]);
	        $edu['endmonth'] = trim($endtime_arr[1]);    
	    }
	    $arr2 = explode('|',$arr1[1]);
	    
	    $edu['school'] = trim(strip_tags($arr2[0]));
	    $edu['speciality'] = trim(strip_tags($arr2[1]));
	    $edu['education_cn'] = trim(strip_tags($arr2[2]));
	   	return $edu;
	}
	/**
     * 解析简历单个培训经历
     */
	function get_resume_traininginfo_one($html){
	    $arr1 = explode('<h6>',$html);
	    $time_str = str_replace(' ','',$arr1[0]);
	    $time_arr = explode('--',$time_str);
	    $starttime_arr = explode('/',strip_tags($time_arr[0]));
	    $training['startyear'] = trim($starttime_arr[0]);
	    $training['startmonth'] = trim($starttime_arr[1]);

	    if(trim(strip_tags($time_arr[1]))=='至今'){
	        $training['todate'] = 1;
	    }else{
	        $endtime_arr = explode('/',strip_tags($time_arr[1]));
	        $training['endyear'] = trim($endtime_arr[0]);
	        $training['endmonth'] = trim($endtime_arr[1]);    
	    }
	    $arr2 = explode('</h6>',$arr1[1]);
	    
	    $training['agency'] = trim(strip_tags($arr2[0]));
	    preg_match('/培训课程：(.*)<br \/><\/div>/',$arr2[1],$course);
	    $training['course'] = trim(strip_tags($course[1]));
	    preg_match('/培训描述：(.*)<\/div>/',$arr2[1],$description);
	    $training['description'] = trim(strip_tags($description[1]));
	   	return $training;
	}
	/**
     * 解析简历单个语言能力
     */
	function get_resume_languageinfo_one($html){
	    $arr1 = explode('|',$html);
	    $arr2 = explode('：',$arr1[0]);
	    $language['language_cn'] = trim(strip_tags($arr2[0]));
	    preg_match('/读写能力(.*)<span class="ver-line">/',$arr2[1],$language_level_cn);
	    $language['level_cn'] = trim(strip_tags($language_level_cn[1]));
	   	return $language;
	}
	/**
     * 解析简历单个证书
     */
	function get_resume_credentinfo_one($html){
		$pattern_time = '/[0-9]{4}\/[0-9]{2}/';
	    preg_match($pattern_time,$html,$time);
	    $arr1 = explode('/',$time[0]);
	    $credent['year'] = $arr1[0];
	    $credent['month'] = $arr1[1];
	    preg_match('/h6(.*)<\/h6>/',$html,$cre);
	    $credent['name'] = trim(strip_tags(str_replace('>', '', $cre[1])));
	    return $credent;
	}
}
?>