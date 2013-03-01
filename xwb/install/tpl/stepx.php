<?php !defined('IN_XWB_INSTALL_ENV') && exit('ACCESS DENIED IN INSTALL TPL'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博插件安装</title>
<link href="tpl/style.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div class="container">
	<div class="header">
		<h1>欢迎使用新浪微博<?php echo XWB_S_NAME;?>插件</h1>
	</div>
	<div class="main">
		<div class="mainTxt">
			<img src="tpl/images/sucess.png" class="txticon" alin="absmiddle"/>
			<p class="title">新浪微博<?php echo XWB_S_NAME;?>插件初始化完毕!<br />
			{{$info1}}<br />
			{{$info2}}
			</p><br />
			{{$info}}
		</div>
		<div class="clear"></div>
		
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

</body>
</html>
