<?php !defined('IN_XWB_INSTALL_ENV') && exit('ACCESS DENIED IN INSTALL TPL'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博插件安装</title>
<link href="tpl/style.css" type="text/css" rel="stylesheet" />
</head>
<script language="javascript">


function chkAndPost(){
	
	if (document.getElementById('appkey').value==''){
		alert('请输入appkey');
		document.getElementById('appkey').focus();
		return false;
	}
	
	if (document.getElementById('appsecret').value==''){
		alert('请输入appsecret');
		document.getElementById('appsecret').focus();
		return false;
	}

    if (document.getElementById('qq').value==''){
		alert('请输入QQ');
		document.getElementById('qq').focus();
		return false;
	}

	/*
	if (document.getElementById('sync_username').value==''){
		alert('请输入用户称号');
		document.getElementById('sync_username').focus();
		return false;
	}
	
	if (document.getElementById('sync_email').value==''){
		alert('请输入用户email');
		document.getElementById('sync_email').focus();
		return false;
	}
	*/
	
	document.getElementById('appCfgForm').submit();
	return false;
}
</script>
<body>
<div class="container">
	<div class="header">
		<h1>欢迎使用新浪微博<?php echo XWB_S_NAME;?>插件</h1>
	</div>
	<div class="main">
		<form action='index.php?step=3' target='_self' method='post' id="appCfgForm">
		<div class="mainTxt">
			<div class="con">
				<ul class="list">
				  <li class="note"><p class="th">&nbsp;</p><p class="td">&nbsp;</p></li>
				  <li>
                  	<div><p class="th">App Key：</p><p class="td"><input id="appkey" name="appkey" type="input" value="<?php echo $appkey;?>" class="input_s" /></p></div>
                    <p class="tips">App Key 是网站与新浪微博通信的标识</p></li>
				  <li>
                  	<div><p class="th">App Secret：</p><p class="td"><input id="appsecret"  name="appsecret" type="input" value="<?php echo $appsecret;?>" class="input_s" /></p></div>
                    <p class="tips">App Secret 是网站与新浪微博通信的密钥</p></li>
                  <li style="height:1px;margin-top: 10px; width: 380px; margin-left: 30px; border-top: 1px dotted #ccc;"></li>
                  <li>
                  	<div><p class="th">QQ：</p><p class="td"><input id="qq"  name="qq" type="input" value="<?php echo $qq;?>" class="input_s" /></p></div>
                    <p class="tips">用来处理异常和技术支持等的重要渠道之一</p></li>
				</ul>
			</div>
		</div>
		<div style="float: left; margin-left: 20px;"><a href="http://open.t.sina.com.cn/userSetting.php?xweibo=1" target="_blank">申请Appkey</a></div>
		<div class="clear"></div>
		
		<!--评论推送暂时隐藏-->
		<!--
		<div class="mainTxt">
			<p class="title title1">同步获取新浪微博评论设置</p>	
			<div class="con">
				<ul class="list">
                  
				  <li><p class="tips">启用此功能后，发帖时已同时发布到新浪微博的帖子，微博评论信息将同时为帖子回复。同步到论坛帖子页中显示。</p></li>
				  <li>
					<div><p class="th">同步设置：</p><p class="td"><input type="radio" name="is_rsync_comment" id="is_rsync_comment" value="1"  checked="checked" /> 是 &nbsp; <input type="radio" name="is_rsync_comment" id="is_rsync_comment" value="0"  /> 否</p></div>
					<p class="tips">请设置是否启用同步获取新浪微博评论功能。</p>
				  </li>     
				  <li>
					<div><p class="th">用户称号：</p><p class="td"><input id="sync_username"  name="sync_username" type="input" value="新浪微博" class="input_s" /></p></div>
					<p class="tips">请设置从新浪微博评论同步到的帖子回复，用户在论坛中的昵称/称号。</p>
				</li>
				
				<li>
				  	<input id="sync_email"  name="sync_email" type="hidden" value="<?php /*echo $sync_email;*/ ?>"  />
				</li>
				</ul>
			</div>
		</div>
		-->
		<!--评论推送暂时隐藏-->
		
		</form>
		<div class="clear"></div>

		<div class="btnbox">
			<form>
			<a href="index.php?step=1" class="btn">&lt;&lt;上一步</a>
			<a href="#" class="btn" onclick="return chkAndPost();">下一步&gt;&gt;</a>
			<!--<a href="#" class="btn dis" onclick="window.close();">取消</a> -->
			</form>
		</div>
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

</body>
</html>
