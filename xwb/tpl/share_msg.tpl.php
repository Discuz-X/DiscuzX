<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>转发到微博-新浪微博-随时随地分享身边的新鲜事儿</title>
<script type="text/javascript">
	var scope = {
		$lang		  : "zh",
		$severtime    : "<?php echo time();?>",
		$PRODUCT_NAME : "miniblogplatform",
		$pageid       : "shareSucess",
		$setDomain    : false,
		$devMode	  : 99,
        $localUrl     : '<?php echo XWB_plugin::getPluginUrl('images');?>'
	};
</script>
<?php if (empty($owbUserRs)): ?>
<script type="text/javascript" src="<?php echo XWB_plugin::getPluginUrl('images/boot.js');?>"></script>
<script type="text/javascript">loadResource();renderPage();</script>
<?php endif; ?>
<link href="<?php echo XWB_plugin::getPluginUrl('images/public.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo XWB_plugin::getPluginUrl('images/shareout.css');?>" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="reg_wrap">
	<!-- 顶部 LOGO -->
	<div class="TopName">
        <div class="logo"></div>
        <a href="<?php echo ( ! empty($rst['id'])) ? XWB_plugin::getWeiboProfileLink($rst['id']) : '#';?>" target="_blank" class="logoLink"></a>
        <div class="op">
            <?php if ( ! empty($rst['id'])):?>
        	<span>你正在使用 <a href="<?php echo XWB_plugin::getWeiboProfileLink($rst['id']);?>" target="_blank"  class="userID"><?php echo $rst['screen_name'];?></a> 帐号</span>
            <span class="line">|</span>
            <span><a href="http://v.t.sina.com.cn/share/sharechg.php?appkey=<?php echo XWB_APP_KEY;?>&url=&title=&source=&sourceUrl=&%20content=gb2312&pic=">换个帐号？</a></span>
            <?php endif;?>
        </div>
    </div>
    <!-- /顶部 LOGO -->
    <div class="reg_main">
    	<b class="bg_regTop">&nbsp;</b>
         <b class="bg_deco_s">&nbsp;</b>
        <div class="reg_pub">

            <div class="n_Box">
                <div class="n_login_sc2">
                    <h2><?php echo $tipMsg;?></h2>
                    
                    <?php if (empty($owbUserRs)): ?>
                    	<p><span id="timeout">3</span>秒后窗口自动关闭，<a href="javascript:void(0);" onclick="window.opener = null; window.close();">点击这里</a>立即关闭</p>
                    	<?php if (!empty($rst['id'])):?>
                    	<div class="btn"><a href="<?php echo XWB_plugin::getWeiboProfileLink($rst['id']);?>" class="MIB_bigBtn MIB_bigBtnB" target="_blank"><cite>去我的微博</cite></a></div>
                    	<?php endif;?>
                    <?php else: ?>
                        <p>
                        <a href="javascript:void(0);" onclick="window.opener = null; window.close();">点击这里</a>立即关闭
                    	<?php if (!empty($rst['id'])):?>
                    	，或<a href="<?php echo XWB_plugin::getWeiboProfileLink($rst['id']);?>" target="_blank">去我的微博</a>看看
                    	<?php endif;?>
                    	</p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($owbUserRs)): ?>
                <div class="attention">
                    <h4>分享后再关注一下，是一种美德</h4>
                    <dl>
                    <dt><a href="<?php echo XWB_plugin::getWeiboProfileLink($owbUserRs['id']);?>" target="_blank"><img src="<?php echo isset($owbUserRs['local_image_url']) ? $owbUserRs['local_image_url'] : XWB_plugin::getPluginUrl('images/bgimg/0.gif');?>"  /></a></dt>
                    <dd class="info">
                    	<h5><?php echo $owbUserRs['screen_name'];?></h5>
                        <p class="adr"><span><a target="_blank" href="<?php echo XWB_plugin::getEntryURL('xwbSiteInterface.attention', "att_id={$owbUserRs['id']}");?>">加关注</a></span></p>
                    </dd>
                    </dl>
                </div>
                <?php endif; ?>
                
            </div>
            

            
            
            
            
            
        </div>
        <b class="bg_regBot">&nbsp;</b>
    </div>
</div>
<?php 
$xwb_sess = XWB_plugin::getUser();
$xwb_statInfo = $xwb_sess->getStat();
foreach($xwb_statInfo as $k => $stat){
	$xwb_statType = isset($stat['xt']) ? (string)$stat['xt'] : 'unknown';
	echo XWB_plugin::statUrl( $xwb_statType, $stat, true );
}
if(!empty($xwb_statInfo)){
	$xwb_sess->clearStat();
}
?>
</body>
</html>
