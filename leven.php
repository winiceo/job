<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

define('IN_QISHI', true);

define('ROOT', dirname(__FILE__) . "/");


require ROOT . 'vendor/autoload.php';
require ROOT . 'leven/lib.php';
error_reporting(E_ALL);


$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);
/**
 * 创建用户
 * @param $obj
 * @return array|bool|null
 */
function create_member($obj)
{
    require ROOT . "data/config.php";
    require ROOT . "include/fun_user.php";
    $pwd_hash = randstr();
    $name_rand = randusername();
    $password_hash = md5(md5($pwd_hash) . $pwd_hash . $QS_pwdhash);
    $setsqlarr['username'] = strtolower("em_" . $name_rand);
    $setsqlarr['password'] = $password_hash;
    $setsqlarr['pwd_hash'] = $pwd_hash;
    $new = \ORM::for_table(table('members'))->create();
    $new->utype = 2;
    $new->username = $setsqlarr['username'];
    $new->email = $obj["email"];
    $new->email_audit = 0;
    $new->mobile = str_replace("086-", "", $obj["telephone"]);
    $new->mobile_audit = 0;
    $new->password = $setsqlarr['password'];
    $new->pwd_hash = $setsqlarr['pwd_hash'];
    $new->reg_time = time();;
    $new->reg_ip = '';
    $new->last_login_time = 0;
    $new->last_login_ip = '';
    $new->qq_openid = '';
    $new->sina_access_token = '';
    $new->taobao_access_token = '';
    $new->qq_nick = '';
    $new->sina_nick = '';
    $new->taobao_nick = '';
    $new->weixin_nick = '';
    $new->qq_binding_time = 0;
    $new->sina_binding_time = 0;
    $new->taobao_binding_time = 0;
    $new->status = 1;
    $new->avatars = '';
    $new->robot = 0;
    $new->consultant = 0;
    $new->weixin_openid = '';
    $new->bindingtime = 0;
    $new->remind_email_time = 0;
    $new->imei = '';
    $new->sms_num = 0;
    $new->reg_type = 1;
    $new->status = 0;

    $rs = $new->save();

    if ($rs) {
        return $new->id;
    }
    return false;

}

//获取转换后文字对应的id;
function getId($value, $alias)
{
    $obj = \ORM::for_table(table('category'))->where("c_name", $value)->where("c_alias", $alias)->find_one();
    //->as_array();

    if ($obj) {
        return $obj->as_array();
    } else {
        return false;
    }
}

//创建简历
function createResume($obj)
{
    unset($obj["id"]);
    if (!$obj["title"]) {
        $obj["title"] = $obj["fullname"] . "的简历";
    }
    //dump($obj);
    $obj["sex"] = $obj["sex_cn"] == "男" ? 1 : 2;
    $nature = getId($obj["nature_cn"], "QS_jobs_nature");
    if ($nature) {
        $obj["nature"] = $nature["c_id"];
    } else {
        $obj["nature"] = 62;
        $obj["nature_cn"] = "全职";
    }
    $obj["trade_cn"] = "其他行业";
    $obj["trade"] = 45;
    $obj["height"] = 0;
    $obj["height"] = 0;
    $obj['marriage'] = 0;
    $obj['marriage_cn'] = "未婚";

    $experience_cn = get_reg($obj["experience_cn"]);
    if ($experience_cn) {
        $obj['experience_cn'] = $experience_cn;
        $obj['experience'] = getId($experience_cn, "QS_experience")["c_id"];
    } else {
        $obj['experience_cn'] = "无经验";
        $obj['experience'] = 74;
    }
    $wage_cn = get_reg($obj["wage_cn"]);
    if ($wage_cn) {
        $obj['wage_cn'] = $wage_cn;
        $obj['wage'] = getId($wage_cn, "QS_wage")["c_id"];
    } else {
        $obj['wage_cn'] = "面议";
        $obj['wage'] = 294;
    }


    $education_cn = get_reg($obj["education_cn"]);
    if ($education_cn) {
        $obj['education_cn'] = $education_cn;
        $obj['education'] = getId($education_cn, "QS_education")["c_id"];
    } else {
        $obj['education_cn'] = "初中";
        $obj['education'] = 65;
    }

    $major_cn = get_reg($obj["major_cn"]);
    if ($major_cn) {
        $obj['major_cn'] = $major_cn;
        $obj['major'] = getId($major_cn, "QS_major")["c_id"];
    } else {
        $obj['major_cn'] = "其他专业";
        $obj['major'] = 290;
    }
    $obj['tag_cn'] = "";

    $obj['tag'] = "";
    $obj['specialty'] = "";
    $obj['photo_img'] = "";
    $obj['addtime'] = time();
    $obj['refreshtime'] = time();
    $obj['level'] = 1;
    $obj["current_cn"] = "观望有好的机会再考虑";
    $obj["current"] = 243;
    $obj["word_resume"] = "";
    $obj["key"] = "";
    $obj["tpl"] = "";
    $obj["status"] = 0;


   //  dump($obj);
//exit;

    $new = \ORM::for_table(table('resume'))->create($obj);
    $a = $new->save();

    $searchtab['id'] = $new->id;
    $searchtab['uid'] = $obj['uid'];

    $db=Leven::db();
    require_once(ROOT . 'include/fun_personal.php');

    $db->inserttable(table('resume_search_key'),$searchtab);
    $db->inserttable(table('resume_search_rtime'),$searchtab);
    check_resume($obj["uid"],$new->id);
//      dump($a);
}

//获取替换文本
function get_reg($value)
{
    $obj = \ORM::for_table(table('relation'))->where("name", $value)->find_one()->as_array();
    if ($obj) {
        return $obj["value"];
    }
    return false;
}


$app->get('/genv/check_pass', function ($request, $response) {

    $id = $this->request->getParam("id");
    $obj = \ORM::for_table(table('resume_temp'))->find_one($id)->as_array();
    $uid = create_member($obj);

    if ($uid) {
        $obj["uid"] = $uid;
        createResume($obj);
    }


});
//初始化
$app->get('/genv/init', function ($request, $response) {

    //创建表
    //简历临时表；
    $sql=array();
    $sql[]="alter table ".table('jobs')." add reward tinyint(1) not null default 0";


    $sql[]="alter table ".table('jobs_tmp')." add reward tinyint(1) not null default 0";

    $sql[]="alter table ".table('resume')." modify major smallint(5) not null";

    $sql[]="CREATE table qs_resume_temp select * from qs_resume";


    $sql[]="alter table ".table('jobs_search_rtime')." add reward tinyint(1) not null default 0";//添加悬赏奖


//
//    //增加现金字段
//
     $sql[]="alter table ".table('members_points')." add balance int(10) not null default 0";

    //锁定现金；
     $sql[]="alter table ".table('members_points')." add block_balance int(10) not null default 0";


     $sql[]="CREATE table qs_members_person_points select * from qs_members_points";


    //推广表增加 悬赏相关
     $sql[]="alter table ".table('promotion')." add cp_json text";

     $sql[]="alter table ".table('promotion_category')." add cp_json text";



    $sql[]="alter table ".table('resume_temp')." add file varchar(250) not null ";
    $sql[]="alter table ".table('resume_temp')." add upload_uid int(10) not null ";
    $sql[]="alter table ".table('resume_temp')." add num tinyint(3) not null ";
    $sql[]="alter table ".table('resume_temp')." add upload_time int(11) not null ";


    $sql[]="alter table ".table('resume_temp')." add status tinyint(1)  not null default 0 ";

    $sql[]="insert qs_promotion_category set cat_id=5,cat_type=2,cat_name='悬赏',cat_notes=''";

    foreach($sql as $s){
        try{
            $rs=ORM::raw_execute($s);
            dump($rs);
        }catch(Exception $e){
            //dump($e);
        }
    }
    //更改临时表；
    $ss="SHOW FULL COLUMNS FROM qs_resume_temp";
    $rs=ORM::for_table("qs_resume_temp")->raw_query($ss)->find_array();
    //$sql=array();
    foreach($rs as $k=>$v){
        $sql=vsprintf("ALTER TABLE qs_resume_temp MODIFY %s   %s %s",array($v["Field"],$v["Type"],$v["Default"]?"default ".$v["Default"]:''));

        try{
            $rs=ORM::raw_execute($sql);

        }catch(Exception $e){

        }
    }

    try{
        $sql="ALTER TABLE `qs_resume_temp` CHANGE COLUMN `id` `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE COLUMN `key` `key` text CHARACTER SET gbk, ADD PRIMARY KEY (`id`)";
        $rs=ORM::raw_execute($sql);


    }catch(Exception $e){

    }

    $g=ORM::for_table("qs_category_group")->where("g_alias","Genv_check_false")->find_one();
    if(!$g){
        $g=ORM::for_table("qs_category_group")->create();
        $g->g_alias="Genv_check_false";
        $g->g_name="审核未通过";
        $g->g_sys=0;
        $g->save();

        $g=ORM::for_table("qs_category")->create();
        $g->c_alias="Genv_check_false";
        $g->c_name="电话打不通";
        $g->c_index="d";
        $g->c_parentid=0;
        $g->save();
        $g->c_alias="Genv_check_false";
        $g->c_name="已上班";
        $g->c_parentid=0;
        $g->c_index="y";
        $g->save();

    }

    $g=ORM::for_table("qs_category")->create();
    $g->c_alias="Genv_check_false";
    $g->c_name="电话打不通";
    $g->c_index="d";
    $g->c_parentid=0;
    $g->c_order=0;
    $g->c_note="";
    $g->stat_jobs="";
    $g->stat_resume="";

    $g->save();
    $g=ORM::for_table("qs_category")->create();
    $g->c_alias="Genv_check_false";
    $g->c_name="已上班";
    $g->c_parentid=0;
    $g->c_index="y";
    $g->c_order=0;
    $g->c_note="";
    $g->stat_jobs="";
    $g->stat_resume="";
    $g->save();



});
$app->get("/genv/test",function($request,$response){
    //&&in_array($dir,array("/data/job/admin/","/data/job/user/","/data/job/template/")


//    $rs=\ORM::for_table("qs_jobs")->find_one()->as_array();
//    unset($rs["id"]);
//    $rs=\ORM::for_table("qs_jobs")->create($rs);
//    $rs->save();
//    dump($rs);
//    exit;
//    ob_start(); //打开缓冲区


    $rs=listDir("/data/job/admin");
    $rs=listDir("/data/job/user");
    $rs=listDir("/data/job/templates");
    $rs=listDir("/data/job/genv");
    $rs=listDir("/data/job/include");
    $rs=listDir("/data/job/resume");
    $info=ob_get_contents(); //得到缓冲区的内容并且赋值给$info
    $file=fopen('modify.txt','w'); //打开文件info.txt
    fwrite($file,$info); //写入信息到info.txt
    fclose($file); //关闭文件info.txt
    exit;
    $ids=array(116,124,127,129);
    $id=119;
    foreach($ids as $uid){
        $rs=\ORM::for_table("qs_resume_check_log")->create();
        $rs->uid=124;
        $rs->rid=$id;
        $rs->result="adf";
        $rs->pass=0;
        $rs->save();
    }

    exit;

    $id=123;
    $count=\ORM::for_table(table("resume_check_log"))->where("rid",$id)->where_not_equal("result","")->count("*");
    \ORM::raw_execute(vsprintf("update %s set num=%d where id=%d",array(table("resume_temp"),$count,$id)));
    $count=\ORM::for_table(table("resume_check_log"))->where("rid",$id)->where_not_equal("result","")->count();
    dump($count);
    exit;
    $rs=\ORM::for_table(table("resume_check_log"))->select("result")->where("rid",$id)->find_array();
    $items=array();
    foreach($rs as $item){
        $items[]=$item["result"];
    }
    echo  implode(";",$items);
});

$app->post('/genv/payhulu', function ($request, $response) {

    $uid=$_SESSION['uid'];
    $amount = $this->request->getParam("amount");


    try {
        ORM::get_db()->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
        ORM::get_db()->beginTransaction();
        //减去余额
        $sql=vsprintf("UPDATE %s SET `balance` = balance-%d WHERE `uid` = %d and `balance`>=%d ", array(table("members_points"),$amount,$uid,$amount));

        ORM::raw_execute($sql);
        //增加积分；
        $sql=vsprintf("UPDATE %s SET `points` = points+%d WHERE `uid` = %d   ", array(table("members_points"),$amount,$uid));

        ORM::raw_execute($sql);


       echo  ORM::get_last_query();
//
//        Order::deduct_stock($order['coohua_id'], $order['product_id'], $order['order_code']);
//        ORM::raw_execute("UPDATE `order` SET `check` = 5 WHERE `order_code` = ? and `check`=4 ", array($order['order_code']));



        ORM::get_db()->commit();
        ORM::get_db()->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
    } catch (Exception $e) {
        //dump($e);
        ORM::get_db()->rollBack();

    }

});

function listDir($dir)
{

     if(is_dir($dir))
    {
        if ($dh = opendir($dir))
        {
            while (($file = readdir($dh)) !== false)
            {
                if((is_dir($dir."/".$file)) && $file!="." && $file!="..")
                {
                    if(date("Y-m-d",filemtime($dir."/".$file))>"2016-01-12") {
                        echo "<b><font color='red'>文件名：</font></b>", $dir,"/",$file, "更新时间", date("Y-m-d",filemtime($dir."/".$file)), "<br><hr><br>\n\r";
                    }
                    listDir($dir."/".$file."/");
                }
                else
                {
                    if($file!="." && $file!="..")
                    {
                        if(date("Y-m-d",filemtime($dir."/".$file))>"2016-01-12") {
                            echo $dir,"/",$file."更新时间",date("Y-m-d",filemtime($dir."/".$file)),"<br>\n\r";
                        }

                    }
                }
            }
            closedir($dh);
        }
    }
}
/** 快速查找目录、文件 */
  function find($dir)
{
    if(!is_dir($dir)) # 如果$dir变量不是一个目录，直接返回false
        return false;
    $dirs[] = '';     # 用于记录目录
    $files = array(); # 用于记录文件

    while(list($k,$path)=each($dirs))
    {
        if(!in_array($path,array("leven","vendor"))){
            $absDirPath = "$dir/$path";     # 当前要遍历的目录的绝对路径
            $handle = opendir($absDirPath); # 打开目录句柄
            readdir($handle);               # 先调用两次 readdir() 过滤 . 和 ..
            readdir($handle);               # 避免在 while 循环中 if 判断
            while(false !== $item=readdir($handle))
            {
                $relPath = "$path/$item";   # 子项目相对路径
                $absPath = "$dir/$relPath"; # 子项目绝对路径
                if(is_dir($absPath))        # 如果是一个目录，则存入到数组 $dirs
                    $dirs[] = $relPath;
                else                        # 否则是一个文件，则存入到数组 $files
                    $files[] = $relPath;
            }
            closedir($handle); # 关闭目录句柄
        }

    }
    dump($files);
    return array($dirs,$files);
}

$app->run();
