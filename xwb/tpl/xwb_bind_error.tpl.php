<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>绑定错误提示</title>
<link type="text/css" rel="stylesheet" href="<?php echo XWB_plugin::getPluginUrl('images/xwb_'. XWB_S_VERSION .'.css');?>" />
<link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
<script language="javascript" language="javascript">
	function xwb_unbind(){
		if(window.confirm('解除绑定？')){
			document.getElementById('unbindFrm').submit();
			setTimeout("window.location.reload();", 500);
		}
	}
</script>

</head>

<body>
<div class="bind-setting xwb-plugin">
	<p class="alert-tips">与新浪微博API通讯时发生错误！</p>
	<div class="bing-text">
            <?php if ( 'api' == $errorType ) { ?>
				<p>服务器无法连接到新浪微博API服务器；或新浪微博API服务器无响应。</p>
				<p>稍候一下，然后重新打开此页面；如果此错误信息重复出现，<strong>请联系网站管理员处理。</strong></p>
			<?php } elseif ('file' == $errorType) { ?>
				<p>请确保拥有权限，无法创建数据缓存文件。</p>
			<?php } ?>
    </div>
    
    <div class="setting-box">
        <form id="unbindFrm" action="<?php echo XWB_plugin::getEntryURL('xwbSiteInterface.unbind');?>" method="post" target="xwbSiteRegister" >
			<h3>解除绑定</h3>
			<div class="xwb-plugin-btn"><input type="button" class="button" value="解除绑定" onclick="xwb_unbind();return false;" ></div>
			<p class="tips"></p>
		</form>
    </div>
    
</div>
<iframe src="" name="xwbSiteRegister" frameborder="0" height="0" width="0"></iframe>
</body>
</html>
