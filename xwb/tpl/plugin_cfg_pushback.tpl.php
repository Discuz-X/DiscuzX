<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博评论回推 - 新浪微博插件</title>
<link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
<script>

var xwb_s_version = '<?php echo  XWB_S_VERSION ; ?>';

var _xwb_cfg_data ={
		xwb_Version:	'<?php echo XWB_P_VERSION; ?>',pName:'admin_home',updateApi:	'<?php  echo XWB_P_INFO_API ; ?>'
	};

function $id (id) {return document.getElementById(id);}

function xwbSetTips(rst){
	if (rst[0]!=1){
		popShow(rst[1], 'error');
	}else{
		popShow(rst[1], 'success');
	}
}

function xwbRefresh(){
	window.location.reload();
}

function popShow(msg, type){
    var popMsg = $id('popMsg');
    var msgImg = $id('msgImg');
    $id('msgSpan').innerHTML = msg;
    if('error' ==  type) msgImg.className = 'error';
    else if('success' == type) msgImg.className = 'success';
    popMsg.className = 'pop-win win-w fixed-pop';
    popMsg.style.display = '';
}

function xwbSetPushbackAuthKey()
{
    $id('loading').innerHTML = '<img src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/xwb_loading.gif');?>" />';
    XWBcontrol.util.connect('<?php echo XWB_plugin::getEntryURL("pushbackInterface.doCfg4setAuthKey");?>', {
        method : 'POST',
        data : '',
        success : function(rst) {
            $id('loading').innerHTML = '';
            if (rst[0] != 1) {
            	popShow(rst[1], 'error');
            } else {
                parent.document.getElementById("frame_content").height = document.documentElement.scrollHeight;
            	popShow(rst[1], 'success');
                $id('ensure').onclick = function(){
                    document.location.reload();
                }
//            	$id('not-set-authkey-link').className = 'hidden';
//            	$id('has-set-authkey-link').className = 'binding-btn back-off';
            }
        },
        failure : function() {
            $id('loading').innerHTML = '';
            popShow(rst[1], 'error');
        }
    });
}
</script>
</head>
<body>
    <div id="comment_back" class="set-wrap push-div-height">
    	<div class="wrap-inner">
        	<h3 class="main-title">评论回推设置</h3>
            <form action="<?php echo XWB_plugin::getEntryURL("pushbackInterface.doCfg4pushback");?>" id="siteRegFrom"  method="post" target="xwbHideFrame">
            	<ul>
                	<li>
                		<a id="not-set-authkey-link" class="binding-btn back-on <?php if( 1 == $isOpen ){ ?>hidden<?php } ?>" href="#" onclick="xwbSetPushbackAuthKey();return false;"><span>开启评论回流</span></a>
                    	<a id="has-set-authkey-link"  class="binding-btn back-off <?php if( 0 == $isOpen ){ ?>hidden<?php } ?>" href="#"><span>已经开启</span></a>
                        <span class="tips">（从本站同步到微博的内容，如有评论，则评论回到本站）</span>
                        <span id="loading"></span>
                    </li>
                    <?php if(1 == $isOpen):?>
                	<li>
                        <span class="tips">
                                   <?php if($lastUpdateTime > 0):?>
                                                                                                         上次更新时间：<?php echo date('Y-m-d H:i:s', $lastUpdateTime ); ?>。
                                   <?php endif;?>
                                   <?php if($nextUpdateTime > 0):?>
                                                                                                         下次更新时间：<?php echo date('Y-m-d H:i:s', $nextUpdateTime ); ?>。
                                   <?php endif;?>
                                   <?php if($fromid > 0):?>
                                                                                                         请求起始id：<?php echo $fromid; ?>。
                                   <?php endif;?>
                        </span>
                    </li>
                    <li>
                    	<label for="pushback_to_thread">
                        	<input class="chk" id="pushback_to_thread" name="pushback_to_thread" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('pushback_to_thread') ? 'checked="checked"' : '' ?> />论坛帖子同步到微博后，微博评论回到本站
                        </label>
                    </li>
                    <li>
                    	<label for="pushback_to_blog">
                        	<input class="chk" id="pushback_to_blog" name="pushback_to_blog" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('pushback_to_blog') ? 'checked="checked"' : '' ?> />日志同步到微博后，微博评论回到本站
                        </label>
                    </li>
                    <li>
                    	<label for="pushback_to_doing">
                        	<input class="chk" id="pushback_to_doing" name="pushback_to_doing" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('pushback_to_doing') ? 'checked="checked"' : '' ?> />记录同步到微博后，微博评论回到本站
                        </label>
                    </li>
                    <li>
                    	<label for="pushback_to_share">
                        	<input class="chk" id="pushback_to_share" name="pushback_to_share" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('pushback_to_share') ? 'checked="checked"' : '' ?> />分享同步到微博后，微博评论回到本站
                        </label>
                    </li>
                    <?php endif;?>
                </ul>
                <?php if(1 == $isOpen):?>
                <div class="btn">
                    <input class="conmon-btn" name="" type="submit" value="保存设置" />
                </div>
                <?php endif;?>
            </form>
            
                
        </div>
    </div>
<!--保存设置成功提示-->
<div class="pop-win win-w fixed-pop hidden" id="popMsg">
	<div class="pop-t">
		<div></div>
	</div>
	<div class="pop-m">
		<div class="pop-inner">
			<h4>提示</h4>
			<div class="add-float-content">
            	<div class="tip-success">
                	<div id="msgImg" class="success"></div>
                    <span id="msgSpan">应用设置保存成功！</span>
                </div>
                <div class="pop-btn-s">
                    <a id="ensure" class="pop-btn" href="javascript:void(0)" onclick="$id('popMsg').className='pop-win win-w fixed-pop hidden';"><span>知道了</span></a>
                </div>
			</div>
    	</div>
		<div class="pop-inner-bg"></div>
	</div>
	<div class="pop-b">
		<div></div>
	</div>
</div>

<iframe src="" name="xwbHideFrame" frameborder="0" height="0" width="0"></iframe>
</body>
</html>
