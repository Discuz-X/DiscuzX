<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>绑定插件 - 新浪微博插件</title>
<link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
</head>
<body>
    <div id="unbound" class="set-wrap">
    	<!--h3>新浪微博绑定设置</h3-->
        <div class="main">
        	<div class="con-l">
                <div class="binding">
                	<a class="binding-btn binding-w" href="javascript:void(0)" onclick="window.top.location='<?php echo XWB_plugin::getEntryURL('xwbAuth.login');?>'"><span>绑定新浪微博</span></a>
                    <a href="http://weibo.com/reg.php" target="_blank">注册新浪微博</a>
                </div>
            </div> 
            <div class="con-r">
				<h4>绑定新浪微博账号后，您可以:</h4>
                <p>发帖，日志、记录等能同步到新浪微博<br />直接使用微博账号登陆<br />使用微博签名和微博秀</p>
            </div>
        </div>
        <?php if ( XWB_S_UID > 0 && ! empty($huwbUserRs) ):?>
        <div class="active-s1">
        	<h4>他们已经绑定微博了，你还不行动？</h4>
            <?php foreach ($huwbUserRs as $value):?>
            <div class="users">
                <a href="<?php echo XWB_plugin::getWeiboProfileLink($value['sina_uid']); ?>" target="_blank"><?php echo $value['avatar'];?></a>
                <div class="user-info">
                    <p><?php echo XWB_plugin::convertEncoding($value['username'], XWB_S_CHARSET, 'UTF-8');?></p>
                    <a class="addfollow-btn" href="<?php echo XWB_plugin::getWeiboProfileLink($value['sina_uid']); ?>" target="_blank"></a>
                    <a class="already-addfollow-btn hidden" href="javascript:void(0)#"></a>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <?php endif;?>
    </div>
</body>
</html>