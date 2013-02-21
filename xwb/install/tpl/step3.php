<?php !defined('IN_XWB_INSTALL_ENV') && exit('ACCESS DENIED IN INSTALL TPL'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博插件安装</title>
<link href="tpl/style.css" type="text/css" rel="stylesheet" />
<script type="text/javascript">
<!--
function setcopy(){
	var $IE = ('v' == '\v');
	var dom = document.getElementById('url');
	if($IE){
		try {
			clipboardData.setData('Text', dom.value );
			alert('地址已经复制到剪贴板');
		}catch(e){}
	}else{
		alert('抱歉！您的浏览器不支持直接复制到剪贴板。');
		setTimeout(function(){
				dom.select();
				},0)
	}
}
-->
</script>
</head>

<body>
<div class="container">
	<div class="header">
		<h1>欢迎使用新浪微博<?php echo XWB_S_NAME;?>插件</h1>
	</div>
	<div class="main">
		<div class="mainTxt">
			<img src="tpl/images/<?php echo $image_file;?>" class="txticon" align="absmiddle"/>
			<p class="title">初始化程序以及数据</p>
            <?php foreach ($dbTips as $tip) {
				$c = empty($tip[0]) ? 'con red' : 'con';
			?>
			<div class="<?php echo $c;?>"><p><?php echo $tip[1];?></p></div>
            <?php } ?>
            
			<?php foreach ($tips as $tip) {
				$c = empty($tip[0]) ? 'con red' : 'con';
			?>
			<div class="<?php echo $c;?>"><p><?php echo $tip[1];?></p></div>
            <?php } ?>
            
		</div>
		<div class="clear"></div>

		<?php if($dbSt){ ?>
		<!--安装成功的信息：给出完成按钮-->
		<div class="btnbox">
			<form>
			<!--<a href="#" class="btn dis" onclick="window.close();">上一步</a> -->
			<a href="<?php echo $finish_link; ?>" target="_parent" class="btn">完成&gt;&gt;</a>
			</form>
		</div>
		
		<?php }else{ ?>
		<!--安装失败的信息：给出提示信息-->
		<div class="mainTxt">
			<hr />
            		<div class="con red"><p><strong>安装失败，安装数据已全部回退。</strong></p></div>
			<div class="con red"><p>如果您能将“初始化程序以及数据”提示信息，以及论坛地址、论坛版本和论坛字符编码等信息反馈给我们（比如<a href="http://bbs.x.weibo.com/forum/forumdisplay.php?fid=9" target="_blank">官方论坛</a>），将有助于我们的产品改进，感谢您的支持！</p></div>
			<div class="con red"><p>联系邮箱：<a href="mailto:xweibo@vip.sina.com">mailto:xweibo@vip.sina.com</a>&nbsp;|&nbsp;技术支持：<a href="http://x.weibo.com/" target="_blank">Xweibo官网</a></p></div>
		</div>
		<div class="clear"></div>
		
		<?php } ?>
		
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

<?php
//统计上报[安装]
$xwb_statType = 'in';
$xwb_statArgs = array();
$xwb_statArgs['akey'] = $appkey;
$xwb_statArgs['uid'] = 0;
$xwb_statArgs['domain'] = str_replace( array('http://', 'https://'), '', XWB_plugin::baseUrl() );
$xwb_statArgs['qq'] = $qq;
echo XWB_plugin::statUrl( $xwb_statType, $xwb_statArgs, true );

?>

</body>
</html>
