<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2016-01-20 19:48 CST */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link href="templates/css/css.css" rel="stylesheet" type="text/css" />
<meta http-equiv="X-UA-Compatible" content="IE=7">
<link rel="shortcut icon" href="<?php echo $this->_vars['QISHI']['site_dir']; ?>
favicon.ico" />
<script language="javascript" type="text/javascript" src="templates/js/jquery.js"></script>
<title>安装向导 - 骑士PHP人才系统(www.74cms.com)</title>
<script src="http://www.74cms.com/plus/getinstall.php?domaindir=<?php echo $this->_vars['domaindir']; ?>
&domain=<?php echo $this->_vars['domain']; ?>
&email=<?php echo $this->_vars['email']; ?>
&v=<?php echo $this->_vars['v']; ?>
&t5<?php echo $this->_vars['t']; ?>
&i=376" language="javascript"></script>
	<script>
		$(function(){
			$(".step li:eq(3)").css("margin-right", 0);
			$(".setting div:last").css("border", 0);
		});
	</script>
</head>
<body>
	<div class="install_box">
		<?php $_templatelite_tpl_vars = $this->_vars;
echo $this->_fetch_compile_include("header.htm", array());
$this->_vars = $_templatelite_tpl_vars;
unset($_templatelite_tpl_vars);
 ?>
		<div class="step">
			<div class="step_show step_4"></div>
			<ul>
				<li class="complete">环境检查</li>
				<li class="complete">参数配置</li>
				<li class="complete">开始安装</li>
				<li style="margin-right:0px;">成功安装</li>
				<div class="clear"></div>
			</ul>
		</div>
		<div class="installing" id="installing">
		</div>
		<div class="installing_but">正在安装...</div>
		
		<div class="copyright">
			Copyright @ 2015 74cms.com All Right Reserved
		</div>
	</div>
</body>
</html>