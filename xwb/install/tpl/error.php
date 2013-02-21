<?php !defined('IN_XWB_INSTALL_ENV') && exit('ACCESS DENIED IN INSTALL TPL'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>插件安装</title>
<link href="tpl/style.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div class="container">
	<div class="header">
		<h1>欢迎使用新浪微博Discuz插件</h1>
	</div>
	<div class="main">
		<div class="mainTxt">
			<img src="tpl/images/icon.gif" class="txticon" align="absmiddle"/>
			<p class="title"> <?php echo $msg;?> </p>
			
		</div>
		<div class="clear"></div>
		<div class="btnbox">
			<form>
			<?php if ( isset($_GET['step']) && (int)$_GET['step'] - 1 > 0 ){?>
				<a href="index.php?step=<?php echo (int)$_GET['step'] - 1  ?>" class="btn">&lt;&lt;返回上一步</a>
			<?php } ?>
			<a href="index.php?step=<?php echo isset($_GET['step']) ? (int)$_GET['step'] : 0;  ?>" class="btn">重 试</a>
			</form>
		</div>
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

</body>
</html>
