<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>与xweibo通信 - 新浪微博插件</title>
    <link href="<?php echo XWB_plugin::getPluginUrl('images/xwb_admin.css');?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
    <script type="text/javascript" language="javascript">
        var xwb_s_version = '<?php echo  XWB_S_VERSION ; ?>';

        var _xwb_cfg_data ={
                xwb_Version: '<?php echo XWB_P_VERSION; ?>', pName:'admin_home', updateApi: '<?php  echo XWB_P_INFO_API ; ?>'
            };

        var docScrollHeight;

        function $id(id) {return document.getElementById(id);}

        function setApi(action)
        {
            var href = action ? '<?php echo XWB_plugin::getEntryURL("xwbApiInterface.openApi");?>' : '<?php echo XWB_plugin::getEntryURL("xwbApiInterface.closeApi");?>';
            var param = action ? 'url=' + encodeURIComponent($id('url').value) : '';
            var sign = action ? true : confirm('确定要关闭吗？');
            if(sign) {
                XWBcontrol.util.connect(href + '&' + Math.random(), {
                    method : 'POST',
                    data : param,
                    success : function(rst) {
                        if (rst.errno) {
                            var err = getMsg(rst.errno) ? getMsg(rst.errno) : rst.err;
                            popShow(err, 'error');
                        } else {
                            window.location.reload();
                        }
                    },

                    failure : function() {
                        popShow('请求失败！', 'error');
                    }
                });
            }
        }

        function getMsg(key)
        {
            var msg = '';
            switch(key.toString()) {
                case '5010000': msg = '参数为空';break;
                case '5010001': msg = '请求时间失效';break;
                case '5010002': msg = '签名不正确';break;
                case '5010003': msg = '请求路径不正确';break;
                case '5010004': msg = '数据保存失败';break;
                case '5010005': msg = '数据更新失败';break;
                case '5010006': msg = '帐号已绑定';break;
                case '5010007': msg = '帐号未绑定';break;
                case '5010008': msg = '通信关闭';break;
            }
            return msg;
        }

        function popShow(msg, type){
            var popMsg = $id('popMsg');
            var msgImg = $id('msgImg');
            $id('msgSpan').innerHTML = msg;
            if('error' ==  type) msgImg.className = 'error';
            else msgImg.className = 'success';
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
        <div id="comment_back" class="set-wrap push-div-height">
            <div class="wrap-inner">
                <h3 class="main-title">与xweibo通信 </h3>
                <form action="<?php echo XWB_plugin::getEntryURL("xwbApiInterface.apiCfg");?>" id="apiFrom"  method="post">
                    <ul>
                        <li>
                        	<p>如果你安装了Xweibo，可以通过设置共享双方账号绑定关系和内容的互推。<a href="http://x.weibo.com" target="_blank">我要了解下Xweibo</a></p>
                            <p>在开始设置之前请先确定：</p>
                            <p>1、你的xweibo和xweibo for Discuz 插件使用的是同一个appkey；</p>
                            <p>2、你的论坛所使用的程序是Discuz 6.0-7.2或者DiscuzX1.5；</p>
                            <p>3、你所装的xweibo版本是1.2或者以上的；</p>
                            <p>4、你所装的xweibo for Discuz 插件版本在2.0或以上；</p>
                        </li>
                        <li>
                            <?php if(XWB_plugin::pCfg('switch_to_xweibo')):?>
                            <p>你的xweibo已经和你的论坛（<?php echo str_replace('http://', '', trim($GLOBALS['_G']['siteurl'], '/'));?>）通信成功。</p>
                            <a onclick="setApi(0);return false;" href="void(0)" class="binding-btn back-on ">
                                <span>关闭通信</span>
                            </a>
                            <?php echo XWB_plugin::pCfg('url_to_xweibo');?>（关闭通信后，论坛和微博将不再共享用户信息、互相推送内容）
                            <?php else:?>
                            xweibo接口地址：<input type="text" id="url" name="url" value="<?php echo XWB_plugin::pCfg('url_to_xweibo');?>"/>
                            <a onclick="setApi(1);return false;" href="void(0)" class="binding-btn back-on ">
                                <span>开启通信</span>
                            </a>&nbsp;（示例：http://xweibo地址/api/xplugin.php）
                            <?php endif;?>
                        </li>
                    </ul>
                </form>
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