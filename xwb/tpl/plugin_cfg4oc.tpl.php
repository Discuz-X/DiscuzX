<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>官方帐号设置 - 新浪微博插件</title>
    <link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
    <script type="text/javascript" language="javascript">
        var xwb_s_version = '<?php echo  XWB_S_VERSION ; ?>';

        var _xwb_cfg_data ={
                xwb_Version: '<?php echo XWB_P_VERSION; ?>', pName:'admin_home', updateApi: '<?php  echo XWB_P_INFO_API ; ?>'
            };

        var browserName = navigator.userAgent.toLowerCase();
        mybrowser = {
            version: (browserName.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [0, '0'])[1],
            safari: /webkit/i.test(browserName) && !this.chrome,
            opera: /opera/i.test(browserName),
            firefox:/firefox/i.test(browserName),
            ie: /msie/i.test(browserName) && !/opera/.test(browserName),
            mozilla: /mozilla/i.test(browserName) && !/(compatible|webkit)/.test(browserName) && !this.chrome,
            chrome: /chrome/i.test(browserName) && /webkit/i.test(browserName) && /mozilla/i.test(browserName)
        }

        var docScrollHeight;

        function $id(id) {return document.getElementById(id);}

    	function xwbSetTips(rst){
            if (rst[0]!=1){
    			popShow(rst[1], 'error');
    		}else{
    			popShow(rst[1], 'success');
    		}
    	}

        function xwbShowRs(page)
        {
            $id('serchResultsUl').innerHTML = '<img src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/xwb_loading.gif');?>" />';
            XWBcontrol.util.connect('<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.ocSearch");?>&' + Math.random(), {
                method : 'POST',
                data : 'search=' + $id('searchStr').value + (page ? '&page=' + page : ''),
                success : function(rst) {
                    if (rst.error_no) {
                        $id('serchResultsUl').innerHTML = rst.error;
                    } else {
                        var data = new Array();
                        for(var i = 0; i < rst.length; i++)
                        {
                            if(0 == i)
                                data.push('<li class="result-line-no">');
                            else
                                data.push('<li class="result-line">');
                            data.push('<a class="it-this" href="javascript:void(0)" onclick="xwbShowHead(' + rst[i].id + ',\'' + rst[i].screen_name + '\')">就是这个</a>'
                                + '<span class="results-name">' + rst[i].screen_name + '</span>'
                                + '<span>' + rst[i].location + '</span>'
                                + '<span>粉丝数：' + rst[i].followers_count + '</span>'
                                + '</li>'
                            );
                        }
                        $id('serchResultsUl').innerHTML = data.join('');
                        parent.document.getElementById("frame_content").height  = document.documentElement.scrollHeight;
                    }
                },

                failure : function() {
                    popShow('Request Error!', 'error');
                }
            });
        }

        function xwbShowHead(id, name)
        {
            $id('headArea').innerHTML = '<img src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/xwb_loading.gif');?>" />';
            XWBcontrol.util.connect('<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doPluginCfg4oc");?>&' + Math.random(), {
                method : 'POST',
                data : 'id=' + id + '&name=' + name,
                success : function(rst) {
                    if (rst.error_no) {
                        $id('headArea').innerHTML = rst.error;
                    } else {
                        var data = new Array();
                        var domain = rst.id;
                        data.push('<div class="user-pic">');
                        if(rst.local_image_url){
                            data.push('<a href="http://weibo.com/' + domain + '" target="_blank">'
                                + '<img alt="官方微博头像" src="' + rst.local_image_url + '?' + Math.random() + '" />'
                                + '</a></div>'
                            );
                        } else {
                            data.push('<img alt="微博默认头像" src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/0.gif');?>" /></div>');
                        }
                        data.push('<div class="info">'
                            + '<span class="name">' + rst.screen_name + '</span>'
                            + '<div class="link"><a target="_blank" href="http://weibo.com/' + domain + '">http://weibo.com/' + domain + '</a></div>'
                        );
                        data.push('</div>');
                        $id('headArea').innerHTML = data.join('');
                        popShow('微博账号修改成功！', 'success');
                    }
                },

                failure : function(){
                    popShow('Request Error!', 'error');
                }
            });
        }

        function xwbSearch(e, action){
            var msie = document.all ? true : false;
            var keycode;
            if( ! msie) keycode = e.which;
            else keycode = e.keyCode;
            if (keycode == 13){
                action.blur();
                xwbShowRs();
            }
        }

        function popShow(msg, type){//alert(mybrowser.chrome);
            var popMsg = $id('popMsg');
            var msgImg = $id('msgImg');
            $id('msgSpan').innerHTML = msg;
            if('error' ==  type) msgImg.className = 'error';
            else if('success' == type) msgImg.className = 'success';
            popMsg.className = 'pop-win win-w fixed-pop';
            center(popMsg);
            popMsg.style.margin = 0;
            popMsg.style.marginTop = -popMsg.offsetHeight/2 + 'px';
            // iframe高度小于可视区域，调整iframe高度
            docScrollHeight = document.documentElement.scrollHeight;
            parent.document.getElementById("frame_content").height  = parent.document.documentElement.clientHeight;
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

        function ensure(){
            $id('popMsg').className='pop-win win-w fixed-pop hidden';
            if(docScrollHeight) parent.document.getElementById("frame_content").height  = docScrollHeight;
        }
    </script>
    </head>

    <body>
        <div id="accounts_set" class="set-wrap">
            <div class="wrap-inner">
                <div class="part-s1">
                    <h3 class="main-title">设置本站官方账号：<span>（用户绑定微博后会推荐关注本站的官方微博）</span></h3>
                    <div class="set-con">
                        <!--form action="" method="post"-->
                            <label>搜索包含以下昵称的用户：
                            <input class="input-box" id="searchStr" name="searchStr" type="text" onkeypress="xwbSearch(event, this)" /></label>
                            <input class="conmon-btn" id="search" name="search" type="button" value="搜索" onclick="xwbShowRs()" />
                        <!--/form-->
                    </div>
                    <div class="sle" id="headArea">
                        <?php if ( ! empty($owbUserRs) ):
                            $domain = $owbUserRs['id'];
                        ?>
                        <div class="user-pic">
                            <?php if(isset($owbUserRs['local_image_url'])):?>
                            <a href="<?php echo 'http://weibo.com/' . $domain;?>" target="_blank">
                                <img alt="官方微博头像" src="<?php echo $owbUserRs['local_image_url'] . '?' . mt_rand(1, 9999999999)/10000000000;?>" />
                            </a>
                            <?php else:?>
                            <img alt="微博默认头像" src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/0.gif');?>" />
                            <?php endif;?>
                        </div>
                        <div class="info">
                            <span class="name"><?php echo $owbUserRs['screen_name'];?></span>
                            <!--a href="#">修改</a-->
                            <div class="link">
                                <a href="<?php echo 'http://weibo.com/' . $domain;?>" target="_blank"><?php echo 'http://weibo.com/' . $domain;?></a>
                            </div>
                        </div>
                        <?php else:?>
                        <div class="user-pic"><a href="javascript:void(0)"><img alt="微博默认头像" src="<?php echo XWB_plugin::getPluginUrl('images/bgimg/0.gif');?>" /></a></div>
                        <div class="info">未设置官方微博账号</div>
                        <?php endif;?>
                    </div>
                </div>
                
                <div class="serch-results">
                    <ul id="serchResultsUl"></ul>
                </div>
                
            </div>
            
            <div class="wrap-inner">
            	<h3 class="main-title">其它相关设置</h3>
            	<form action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doPluginCfg4ocSet");?>" id="siteRegFrom"  method="post" target="xwbHideFrame">
            		<ul>
                    	<li>
                    		<label for="part90">
                        		<input class="chk" id="part90" name="pluginCfg[is_rebutton_relateUid_assoc]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('is_rebutton_relateUid_assoc') ? 'checked="checked"' : '' ?> />转发到微博时关联官方帐号<span>（官方账号在转发时会被@,并在转发后提示关注他）</span>
                       	    </label>
                    	</li>
                    	<?php if(version_compare(XWB_S_VERSION, '2', '>=')): ?>
                    	<li>
                    		<label for="part91">
                        		<input class="chk" id="part91" name="pluginCfg[display_ow_in_forum_index]" type="checkbox" value="1" <?php echo XWB_plugin::pCfg('display_ow_in_forum_index') ? 'checked="checked"' : '' ?> />在论坛首页显示官方帐号和关注按钮
                       	    </label>
                    	</li>
                    	<?php endif; ?>
            		</ul>
            		<div class="btn">
            			<input class="conmon-btn" name="submit" type="submit" value="保存设置" />
            		</div>
            	</form>
            	<iframe src="" name="xwbHideFrame" frameborder="0" height="0" width="0"></iframe>
            </div>
            
        </div>
        <!--修改成功提示-->
        <div class="pop-win win-w fixed-pop hidden" id="popMsg" style="top:55%;">
            <div class="pop-t">
                <div></div>
            </div>
            <div class="pop-m">
                <div class="pop-inner">
                    <h4>提示</h4>
                    <div class="add-float-content">
                        <div class="tip-success">
                            <div id="msgImg" class="success"></div>
                            <span id="msgSpan">修改成功！</span>
                        </div>
                        <div class="pop-btn-s">
                            <a class="pop-btn" href="javascript:void(0)" onclick="ensure()"><span>知道了</span></a>
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