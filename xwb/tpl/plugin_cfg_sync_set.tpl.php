<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博同步设置 - 新浪微博插件</title>
<link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
<script language="javascript">
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
	
    function popShow(msg, type){
        var popMsg = $id('popMsg');
        var msgImg = $id('msgImg');
        $id('msgSpan').innerHTML = msg;
        if('error' ==  type) msgImg.className = 'error';
        else if('success' == type) msgImg.className = 'success';
        popMsg.className = 'pop-win win-w fixed-pop';
        center(popMsg);
        popMsg.style.margin = 0;
        popMsg.style.marginTop = -popMsg.offsetHeight/2 + 'px';
        popMsg.style.display = '';
    }

    function getScroll(){
        var ua = navigator.userAgent.toLowerCase();
        var strict = document.compatMode === "CSS1Compat";
        var opera = ua.indexOf("opera") > -1;
        var ie = !opera && ua.indexOf("msie") > -1;
        var l, t;
        var doc = parent.document;
        var win = parent.window;
        if(ie && strict){
            l = doc.documentElement.scrollLeft || (doc.body.scrollLeft || 0);
            t = doc.documentElement.scrollTop || (doc.body.scrollTop || 0);
        }else{
            l = win.pageXOffset || (doc.body.scrollLeft || 0);
            t = win.pageYOffset || (doc.body.scrollTop || 0);
        }
        return {left: l, top: t};
    }

    function center(action){
         var sz  = {width:parent.document.documentElement.clientWidth, height:parent.document.documentElement.clientHeight};
         var dsz = {width:action.offsetWidth, height:action.offsetHeight};
         action.style.left = parseInt((sz.width - dsz.width)/2 + getScroll().left) + 'px';
         action.style.top = parseInt((sz.height - dsz.height)/2 + getScroll().top) + 'px';
    }
</script>
<script src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
</head>
<body>
    <div id="synch_set" class="set-wrap">
    	<div class="wrap-inner">
        	<h3 class="main-title">内容同步关联</h3>
            <form action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doPluginCfg4Sync");?>" id="siteRegFrom"  method="post" target="xwbHideFrame">
            	<ul>
                	<li>
                    	<p>开启以下内容同步功能。<span>（用户发信息时可选择，开启默认为同步。需要该用户绑定新浪微博）</span></p>
                        <label for="posts">
                        	<input class="chk" id="posts" name="pluginCfg[is_synctopic_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_synctopic_toweibo') ? 'checked="checked"' : '' ?> />论坛帖子
                        </label>
                        <label for="records">
                        	<input class="chk" id="records" name="pluginCfg[is_syncdoing_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_syncdoing_toweibo') ? 'checked="checked"' : '' ?> />记录
                        </label>
                        <label for="log">
                        	<input class="chk" id="log" name="pluginCfg[is_syncblog_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_syncblog_toweibo') ? 'checked="checked"' : '' ?> />日志
                        </label>
                        <label for="share">
                        	<input class="chk" id="share" name="pluginCfg[is_syncshare_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_syncshare_toweibo') ? 'checked="checked"' : '' ?> />添加分享
                        </label>
                        <label for="share">
                        	<input class="chk" id="share" name="pluginCfg[is_syncarticle_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_syncarticle_toweibo') ? 'checked="checked"' : '' ?> />门户文章
                        </label>
                    </li>
                    <li>
                    	<label for="part2">
                        	<input class="chk" id="part2" name="pluginCfg[is_upload_image]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_upload_image') ? 'checked="checked"' : '' ?> />帖子中如有图片，图片同步到新浪微博<span>（开启同步帖子时有效）</span>
                        </label>
                    </li>
                    <li>
                    	<label for="part3">
                        	<input class="chk" id="part3" name="pluginCfg[wb_addr_display]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('wb_addr_display') ? 'checked="checked"' : '' ?> />在同步的内容中显示该帖子同步到微博的提示<span>（链接显示为该用户的微博地址）</span>
                        </label>
                    </li>
                    <li>
                    	<label for="part4">
                        	<input class="chk" id="part4" name="pluginCfg[is_rebutton_display]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_rebutton_display') ? 'checked="checked"' : '' ?> />开启转发到微博功能<span>（开启后在主题贴、日志和相册页面会出现转发按钮）</span>
                        </label>
                    </li>
                    <li>
                    	<label for="part5">
                        	<input class="chk" id="part5" name="pluginCfg[is_syncreply_toweibo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_syncreply_toweibo') ? 'checked="checked"' : '' ?> />用户在论坛的回复（回帖、日志评论等）作为微博评论回复到新浪微博<span>（在该主题贴、日志等同步到新浪微博，且回帖人绑定新浪微博有效）</span>
                        </label>
                    </li>
                    <li>
                    	<label for="part6">
                        	<input class="chk" id="part6" name="pluginCfg[link_visit_promotion]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('link_visit_promotion') ? 'checked="checked"' : '' ?> />把本站推送到微博上的链接纳入推广积分体系<span>（可在论坛后台“全局 » 积分设置 » 积分策略”设置）</span>
                        </label>
                    </li>
                </ul>
            <div class="btn">
            	<input class="conmon-btn" name="submit" type="submit" value="保存设置" />
            </div>
        </div>
        </form>
    </div>
<iframe src="" name="xwbHideFrame" frameborder="0" height="0" width="0"></iframe>
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
                    <span id="msgSpan">同步设置保存成功！</span>
                </div>
                <div class="pop-btn-s">
                    <a class="pop-btn" href="javascript:void(0)" onclick="$id('popMsg').className='pop-win win-w fixed-pop hidden';"><span>知道了</span></a>
                </div>
			</div>
    	</div>
		<div class="pop-inner-bg"></div>
	</div>
	<div class="pop-b">
		<div></div>
	</div>
</div>
</body>
</html>
