<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2016-01-20 19:47 CST */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link href="templates/css/css.css" rel="stylesheet" type="text/css" />
<meta http-equiv="X-UA-Compatible" content="IE=7">
<link rel="shortcut icon" href="<?php echo $this->_vars['QISHI']['site_dir']; ?>
favicon.ico" />
<script language="javascript" type="text/javascript" src="templates/js/jquery.js"></script>
<title>��װ�� - ��ʿPHP�˲�ϵͳ(www.74cms.com)</title>
<script>
	$(function(){
		$(".step li:eq(3)").css("margin-right", 0);
		$(".setting div:last").css("border", 0);
	})
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
			<div class="step_show step_2"></div>
			<ul>
				<li class="complete">�������</li>
				<li class="complete">��������</li>
				<li>��ʼ��װ</li>
				<li>�ɹ���װ</li>
				<div class="clear"></div>
			</ul>
		</div>
		<form action="index.php?act=4" method="post">
		<div class="setting">
			<div class="setting_check">
				<h3>��д���ݿ���Ϣ</h3>
				<table>
					<tbody>
						<tr height="40">
							<td width="95" align="right">���ݿ�������</td>
							<td width="269"><input name="dbhost" id="dbhost" value="localhost" type="text" class="install_input_text" /></td>
							<td style="color:#999999;">���ݿ��������ַ��һ��Ϊlocalhost</td>
						</tr>
						<tr height="40">
							<td width="95" align="right">���ݿ��û�����</td>
							<td width="269"><input name="dbuser" id="dbuser" type="text" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">���ݿ����룺</td>
							<td width="269"><input name="dbpass" id="dbpass" type="text" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">���ݿ����ƣ�</td>
							<td width="269"><input name="dbname" id="dbname" type="text" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">���ݱ�ǰ׺��</td>
							<td width="269"><input name="pre" id="pre" value="qs_" type="text" class="install_input_text" /></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="menu_check">
				<h3>����Ա�˺�</h3>
				<table>
					<tbody>
						<tr height="40">
							<td width="95" align="right">����Ա������</td>
							<td width="269"><input name="admin_name" id="admin_name" type="text" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">��¼���룺</td>
							<td width="269"><input name="admin_pwd" id="admin_pwd" type="password" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">����ȷ�ϣ�</td>
							<td width="269"><input name="admin_pwd1" id="admin_pwd1" type="password" class="install_input_text" /></td>
						</tr>
						<tr height="40">
							<td width="95" align="right">�������䣺</td>
							<td width="269"><input name="admin_email" id="admin_email" type="text" class="install_input_text" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="next">
			<input type="button" value="��һ��" class="up" onclick="window.location.href='index.php?act=2';"/>
			<input type="submit" value="��һ��" class="down" onclick="openLayer('op1','tis');"/>
		</div>
		</form>
		<div class="copyright">
			Copyright @ 2015 74cms.com All Right Reserved
		</div>
		<span id="op1"></span>
	</div>
<!--�����������-->

<!--����������ݽ���-->
</body>
</html>