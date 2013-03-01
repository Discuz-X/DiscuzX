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
			<img src="tpl/images/<?php echo $image_file;?>" class="txticon" align="absmiddle"/>
			<p class="title">检测安装环境</p>
			<?php foreach ($evnChk[1] as $chk) {
				$c = empty($chk[0]) ? 'con red' : 'con';
			?>
			<div class="<?php echo $c;?>"><p><?php echo $chk[1];?></p></div>
			<?php } ?>

			<?php if( !$evnChk[0] ){ ?>
				<div class="con red"><p>部分检测结果异常，请调整后重新检测，以便继续安装流程。</p></div>
			<?php } ?>
		</div>
		<div class="clear"></div>
		
		<div class="btnbox">
			<form>
			<a href="index.php?step=1" class="btn">重新检测</a>
			<?php if($evnChk[0]){ ?>
				<a href="index.php?step=2" <?php echo $btn_enable;?>>下一步&gt;&gt;</a>
			<?php } ?>
			<!--<a href="#" class="btn dis" onclick="window.close();">取消</a> -->
			</form>
		</div>
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

</body>
</html>
