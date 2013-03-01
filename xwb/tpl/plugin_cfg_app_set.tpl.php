<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博应用设置 - 新浪微博插件</title>
<link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />

<script language="javascript">
    var xwb_s_version = '<?php echo  XWB_S_VERSION ; ?>';

	var _xwb_cfg_data ={
			xwb_Version:	'<?php echo XWB_P_VERSION; ?>',pName:'admin_home',updateApi:	'<?php  echo XWB_P_INFO_API ; ?>'
		};
	
	function $id (id) {return document.getElementById(id);}
	
	function isXwbPrevEnable(){
	    return xwb_s_version.charAt(0) != '6';
	}
	
	function xwbSetTips(rst){
		if (rst[0]!=1){
			popShow(rst[1], 'error');
		}else{
			popShow(rst[1], 'success');
		}
	}

	function getPreviewData(autoFocus){
        var f = $id('siteRegFrom'), 
            param = {uid:'xweibo测试帐号'},
            v = 0;

        param.width = 180;
        
        v = f['pluginCfg[wbx_height]'].value;
        if(!v || v < 75 || v > 500){
            // alert('高度大小应该处于75像素与500像素之间。');
            if(autoFocus)
              f['pluginCfg[wbx_height]'].focus();
            $id('size_warn_tip').className = 'setting-tips warning';
            return false;
        }

        param.height = parseInt(v);
        
        $id('size_warn_tip').className = '';
        
        param.skin = f['pluginCfg[wbx_style]'].value;

        if(!param.skin){
          alert('请选择皮肤样式。');
          return false;
        }
        
        v = f['pluginCfg[wbx_line]'].value;
        if(!v || v < 1 || v > 7){
            // alert('行数应该处于1至7行内。');
            $id('line_warn_tip').className = 'setting-tips warning';
            if(autoFocus)
              f['pluginCfg[wbx_line]'].focus();
            return false;
        }
        
        param.line = v;
        $id('line_warn_tip').className = '';
        
        // display stuff
        param.dtitle = f['pluginCfg[wbx_is_title]'].checked|0;
        param.dblog  = f['pluginCfg[wbx_is_blog]'].checked|0;
        param.dfans  = f['pluginCfg[wbx_is_fans]'].checked|0;
        
        return param;
	}
	
	function beforeSubmit() {
	  if(!isXwbPrevEnable())
	        return true;
	  var data = getPreviewData(true);
	  if(data !== false)
	    $id('siteRegFrom')['pluginCfg[wbx_url]'].value = getPreviewUrl(data);
	    
	  return data !== false;
	}
	
	function getPreviewUrl(data){
	  var url = ('http://service.t.sina.com.cn/widget/WeiboShow.php?'+ 
	            'uname={uid}&width={width}&height={height}&skin={skin}&isTitle={dtitle}&isWeibo={dblog}&isFans={dfans}&fansRow={line}&__noCache='+(+new Date()))
	          .replace(/\{([\w_$]+)\}/g, function(s, s1){
	             return data[s1] !== undefined ? encodeURIComponent( data[s1] ) : '';
	          });
	  return url;
	}
	
	function updatePreview(param){
	  var param = param || getPreviewData();
	  if(param !== false){
	    var frame = $id('wbx_preview_iframe');
	    frame.width  = param.width;
	    frame.height = param.height;
	    frame.src    = getPreviewUrl(param);
	  }
	}
	
	function eachNode(p, tagName, callback){
	   var chs = p.childNodes, item, idx = 0;
	   for(var i=0,len=chs.length;i<len;i++){
	      item = chs[i];
	      if(item.tagName === tagName){
	        idx ++;
	        if(callback(item, idx) === false)
	            break;
	      }
	   }
	}
	
	function onskinclick(a){
	    var skin;
	    
	    eachNode(a.parentNode, 'A', function(item, idx){
	       if(item.className.indexOf('on')>=0)
	            item.className = '';
	       if(item === a)
	            skin = idx;
	    });
	    
        a.className = 'on';
        $id('siteRegFrom')['pluginCfg[wbx_style]'].value = skin;
        updatePreview();
	}
	
	function oncheckboxchange(){
	    setTimeout(function(){updatePreview()}, 0);
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

    function copyCode(obj){
        var txt = obj.innerText;
        if( copyToClipboard(txt) !== false ) {
        	alert('代码已经复制到粘贴板，你可以使用Ctrl+V 贴到需要的地方去了哦！');
        }else {                                                 
          alert("复制失败，请手动复制代码。\n失败原因：不支持该浏览器或者被该浏览器安全设置拒绝。");                                 
        }
    }

    /**
     * 复制代码，支持IE/Firefox/NS
     * http://conkeyn.javaeye.com/blog/240568
     */
    function copyToClipboard(txt) {
    	if (window.clipboardData) {
    		window.clipboardData.clearData();
    		return window.clipboardData.setData("Text", txt);
    	} else if (navigator.userAgent.indexOf("Opera") != -1) {
    		window.location = txt;
    		return true;
    	} else if (window.netscape) {
    		try {
    			netscape.security.PrivilegeManager
    					.enablePrivilege("UniversalXPConnect");
    		} catch (e) {
    			//alert("你使用的FireFox浏览器，复制功能被浏览器拒绝！\n如有需要自动复制功能，请在浏览器地址栏输入“about:config”并回车。\n然后将“signed.applets.codebase_principal_support”双击，设置为“true”");
    			return false;
    		}
    		var clip = Components.classes['@mozilla.org/widget/clipboard;1']
    				.createInstance(Components.interfaces.nsIClipboard);
    		if (!clip)
    			return false;
    		var trans = Components.classes['@mozilla.org/widget/transferable;1']
    				.createInstance(Components.interfaces.nsITransferable);
    		if (!trans)
    			return false;
    		trans.addDataFlavor('text/unicode');
    		var str = new Object();
    		var len = new Object();
    		var str = Components.classes["@mozilla.org/supports-string;1"]
    				.createInstance(Components.interfaces.nsISupportsString);
    		var copytext = txt;
    		str.data = copytext;
    		trans.setTransferData("text/unicode", str, copytext.length * 2);
    		var clipid = Components.interfaces.nsIClipboard;
    		if (!clip)
    			return false;
    		clip.setData(trans, null, clipid.kGlobalClipboard);
    		return true;
    	}
    }
    

</script>
<script  src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
</head>
<body>
    <div id="app_set" class="set-wrap">
        <form action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doPluginCfg");?>" onsubmit="return beforeSubmit();" id="siteRegFrom"  method="post" target="xwbHideFrame">
    	<div class="wrap-inner">
        	<h3 class="main-title">帐号相关</h3>
			<div class="set-s1">
            	<label for="one">
                    <input class="chk" id="one" name="pluginCfg[is_display_login_button]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_display_login_button') ? 'checked="checked"' : '' ?> />1、在论坛首页显示新浪微博登录按钮
                </label>
                <div class="code-box">
                	<div class="login-btn">
               	    	<img src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/sina_login_btn.png');?>" />
                    	<span>（该按钮默认显示在导航条的最右端，你也可以把代码放在需要的地方）</span>
                    </div>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="code-t">
  						<tr>
    						<th class="code-left">
                            	<p>登录按钮代码</p>
                        		<a class="conmon-btn copy-code" href="#" onclick="copyCode($id('xwb-login-btn-code'));">复制代码</a>
                            </th>
    						<th class="code-right" id="xwb-login-btn-code">
                                <?php 
                                    echo htmlspecialchars('<a href="xwb.php?m=xwbAuth.login"><img src="'. XWB_plugin::getPluginUrl('images/bgimg/sina_login_btn.png'). '" /></a>');
                                ?>
                            </th>
  						</tr>
					</table>
                </div>
                <p><label for="two">
                    <input class="chk" id="two" name="pluginCfg[bind_btn_usernav]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('bind_btn_usernav') ? 'checked="checked"' : ''; ?> />2、在header显示绑定微博的提示按钮
                </label></p>
                <p><label for="three">
                    <input class="chk" id="three" name="pluginCfg[is_sync_face]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_sync_face') ? 'checked="checked"' : ''; ?> />3、用户用微博登录后设置本站帐号时读取微博的头像和昵称
                </label></p>
                <?php if(version_compare(XWB_S_VERSION, '2', '>=')): ?>
                <p><label for="pdx2-2">
                    <input class="chk" id="pdx2-2" name="pluginCfg[is_display_login_button_in_fastpost_box]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_display_login_button_in_fastpost_box') ? 'checked="checked"' : ''; ?> />4、在快速发表框显示显示新浪微博登录按钮
                </label></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="wrap-inner">
        	<h3 class="main-title">微博应用设置</h3>
            <div class="set-s2">
                <div class="conmon">
                	<label for="p2">
                        <input class="chk" id="p2" name="pluginCfg[is_tips_display]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_tips_display') ? 'checked="checked"' : '' ?> />在帖子详细页的个人信息栏（用户组旁）显示微博小标示
                    </label>
                </div>
                <div class="conmon">
                	<label for="p4">
                        <input class="chk" id="p4" name="pluginCfg[is_tgc_display]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_tgc_display') ? 'checked="checked"' : '' ?> />在帖子详细页的用户资料页显示微博粉丝数等
                    </label>
                </div>
                <div class="conmon">
                	<label for="p5">
                        <input class="chk" id="p5" name="pluginCfg[space_card_weiboinfo]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('space_card_weiboinfo') ? 'checked="checked"' : '' ?> />在用户名片中显示个人微博信息
                    </label>
                </div>                
                <div class="conmon">
                	<label for="p3">
                        <input class="chk" id="p3" name="pluginCfg[is_signature_display]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_signature_display') ? 'checked="checked"' : '' ?> />允许用户使用微博签名
                    </label>
                </div>
            </div>
        </div>
        <div class="wrap-inner">
        	<h3 class="main-title">其他设置</h3>
            <div class="set-s3">
            	<div class="set-s3-one">
            		<p>微博勋章及标识数据更新间隔:</p>
                	<input class="input-box box-w1" name="pluginCfg[wbx_medal_update_time]" type="text" value="<?php echo intval(XWB_plugin::pCfg('wbx_medal_update_time'));?>" />
                	<label>秒</label>
            	</div>
            	<div class="set-s3-one reset-mar">
            		<p>微博转发间隔:</p>
                	<input class="input-box box-w1" name="pluginCfg[wbx_share_time]" type="text" value="<?php echo intval(XWB_plugin::pCfg('wbx_share_time'));?>" />
                	<label>秒</label>
            	</div>
            	<div class="set-s3-one reset-mar">
            		<p>绑定页活跃用户数据更新间隔:</p>
                	<input class="input-box box-w1" name="pluginCfg[wbx_huwb_update_time]" type="text" value="<?php echo intval(XWB_plugin::pCfg('wbx_huwb_update_time'));?>" />
                	<label>小时</label>
            	</div>
            </div>
        </div>
        <div class="btn">
            <input class="conmon-btn" name="submit" type="submit" value="保存设置" />
        </div>
        </form>
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

<iframe src="" name="xwbHideFrame" frameborder="0" height="0" width="0"></iframe>

</body>
</html>
