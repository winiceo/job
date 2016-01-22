<?php
/*
* 74cms 计划任务
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
define('IN_QISHI', true);
require_once(dirname(__FILE__) . '/../data/config.php');
require_once(dirname(__FILE__) . '/include/admin_common.inc.php');
require_once(ADMIN_ROOT_PATH . 'include/admin_replace_fun.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'list';
check_permissions($_SESSION['admin_purview'], "crons");
$smarty->assign('pageheader', "简历字段替换");
if ($act == 'list') {
    require_once(QISHI_ROOT_PATH . 'include/page.class.php');
    $wheresql=" WHERE  1=1 ";
    $oederbysql=" order BY id DESC ";
    $name=isset($_GET['name'])?trim($_GET['name']):"";
    if ($name )
    {
        $wheresql.=" AND `name` like '{$name}%'";

        $oederbysql="";
    }
    else
    {

        if (!empty($_GET['type'])) {
            if ($_GET['type'] == "1") {
                $wheresql .= " AND `type` = 1";
            } elseif ($_GET['type'] == "2") {
                $wheresql .= " AND type = 2";
            }

        }
        if (!empty($_GET['source'])) {
            $source=$_GET["source"];

             $wheresql .= " AND `source` = '".$source."'";


        }
        if (!empty($_GET['title'])) {

            $wheresql .= " AND `parent_name` = '".$_GET["title"]."' ";


        }

    }



    $total_sql = "SELECT COUNT(*) AS num FROM " . table('relation').$wheresql;
    $total_val = $db->get_total($total_sql);
    $page = new page(array('total' => $total_val, 'perpage' => $perpage, 'getarray' => $_GET));
    $currenpage = $page->nowindex;
    $offset = ($currenpage - 1) * $perpage;
    $list = get_list($offset, $perpage, $wheresql . $oederbysql);
    $smarty->assign('list', $list);
    $smarty->assign('page', $page->show(3));
    $smarty->assign('navlabel', "list");

    $smarty->assign('source_list', get_source_list());
    $smarty->assign('title_list', get_title_list());

    $smarty->assign('source', $_GET['source']);
    $smarty->assign('title', $_GET['title']);
    $smarty->assign('type', $_GET['type']);

    get_token();
    $smarty->display('replace/admin_replace.htm');
} elseif ($act == 'add') {
    get_token();
    $smarty->assign('navlabel', "add");
    $smarty->display('replace/admin_replace_add.htm');
} elseif ($act == 'add_save') {
    check_token();
    $setsqlarr['name'] = !empty($_POST['name']) ? trim($_POST['name']) : adminmsg('原内容不能为空', 1);
    $setsqlarr['value'] = !empty($_POST['value']) ? trim($_POST['value']) : adminmsg('替换后新内容不能为空', 1);
    $setsqlarr['type'] = !empty($_POST['type']) ? trim($_POST['type']) : adminmsg('分类', 1);
    $setsqlarr['source'] = !empty($_POST['source']) ? trim($_POST['source']) : "";
    $setsqlarr['parent_name'] = !empty($_POST['parent_name']) ? trim($_POST['parent_name']) : "";
    $setsqlarr['parent_id'] = !empty($_POST['parent_id']) ? trim($_POST['parent_id']) : "";


    if ($db->inserttable(table('relation'), $setsqlarr)) {
        $link[0]['text'] = "返回列表";
        $link[0]['href'] = "?act=";
        write_log("添加：" . $setsqlarr['name'], $_SESSION['admin_name'], 3);
        adminmsg("添加成功！", 2, $link);
    } else {
        adminmsg("添加失败！", 0);
    }
} elseif ($act == 'edit') {
    get_token();
    $smarty->assign('show', get_replace_one(intval($_GET['id'])));
    $smarty->display('replace/admin_replace_edit.htm');
} elseif ($act == 'edit_save') {
    check_token();
    $link[0]['text'] = "返回列表";
    $link[0]['href'] = "?act=";
    $setsqlarr['name'] = !empty($_POST['name']) ? trim($_POST['name']) : adminmsg('原内容不能为空', 1);
    $setsqlarr['value'] = !empty($_POST['value']) ? trim($_POST['value']) : adminmsg('替换后新内容不能为空', 1);
    $setsqlarr['type'] = !empty($_POST['type']) ? trim($_POST['type']) : adminmsg('分类', 1);
    $setsqlarr['source'] = !empty($_POST['source']) ? trim($_POST['source']) : "";

    $setsqlarr['parent_name'] = !empty($_POST['parent_name']) ? trim($_POST['parent_name']) : "";
    $setsqlarr['parent_id'] = !empty($_POST['parent_id']) ? trim($_POST['parent_id']) : "";

    $wheresql = " id=" . intval($_POST['id']);
    !$db->updatetable(table('relation'), $setsqlarr, $wheresql) ? adminmsg("修改失败！", 0) : adminmsg("修改成功！", 2, $link);
} elseif ($act == 'del') {
    get_token();
    $id = $_REQUEST['id'];
    if (empty($id)) adminmsg("请选择项目！", 0);
    if ($num = del_replace($id)) {
        write_log("删除,共删除" . $num . "行", $_SESSION['admin_name'], 3);
        adminmsg("删除成功！共删除" . $num . "行", 2);
    } else {
        adminmsg("删除失败！" . $num, 1);
    }
} elseif ($act == 'execution') {
    check_token();
    $id = intval($_GET['id']);
    $crons = $db->getone("select * from " . table('crons') . " WHERE  cronid='{$id}' LIMIT 1 ");
    if (!empty($crons)) {
        if (!file_exists(QISHI_ROOT_PATH . "include/crons/" . $crons['filename'])) {
            adminmsg("任务文件 {$crons['filename']} 不存在！", 0);
        }
        require_once(QISHI_ROOT_PATH . "include/crons/" . $crons['filename']);
        adminmsg("执行成功！", 2);
    }
}
?>