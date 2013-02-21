<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link type="text/css" rel="stylesheet" href="<?php echo XWB_plugin::getPluginUrl('images/xwb_'. XWB_S_VERSION .'.css');?>" />
<link type="text/css" rel="stylesheet" href="<?php echo XWB_plugin::getPluginUrl('images/xwb_base.css');?>" />
</head>

<body class="xwb-plugin">
	<div class="inner-window">
    	<h3>皮肤主题</h3>
    	<div class="sign-skin-setting" id="skin-list" onclick="checkSkin(event, this)">
        	<a href="javascript:void(0)"><span class="skin-01"></span></a>
            <a href="javascript:void(0)"><span class="skin-02"></span></a>
            <a href="javascript:void(0)"><span class="skin-03"></span></a>
            <a href="javascript:void(0)"><span class="skin-04"></span></a>
            <a href="javascript:void(0)"><span class="skin-05"></span></a>
            <a href="javascript:void(0)"><span class="skin-06 sign-setting-bg"></span></a>
            <a href="javascript:void(0)"><span class="skin-07 sign-setting-bg"></span></a>
            <a href="javascript:void(0)"><span class="skin-08 sign-setting-bg"></span></a>
            <a href="javascript:void(0)"><span class="skin-09 sign-setting-bg"></span></a>
            <a href="javascript:void(0)"><span class="skin-10 sign-setting-bg"></span></a>
        </div>
        <h3>效果预览</h3>
        <div class="sign-skin-preview">
            <a href="<?php echo XWB_plugin::getWeiboProfileLink(XWB_plugin::getBindInfo('sina_uid'));?>" target="_blank" id="view_img_wr">
                <img border="0" alt="" src="" id="view_img">
            </a>
            <div class="sign-skin-loading hidden" id="view_img_load"><p>正在加载，请稍候...</p></div>
        </div>
        <div class="btn-area">
        	<span class="xwb-plugin-btn"><input type="submit" value="取消" onclick="parent.XWBcontrol.close('signer');" class="button"></span>
        	<span class="xwb-plugin-btn"><input type="submit" value="确定" onclick="parent.XWBcontrol.profile.onSignerDlgOk(gUid, gSkinNum, keyStr)" class="button"></span>
        </div>
    </div>
    <script type="text/javascript">
        var gUid = "<?php echo $myid;?>", 
			keyStr = '<?php echo $myKeyStr;?>',
            gSkinNum = 1,
            util = parent.XWBcontrol.util,
            gViewWr  = util.$('view_img_wr', document.body),
            gLoadEl  = util.$('view_img_load', document.body),
            gViewImg = document.getElementById('view_img');
        
        var skinId = parent.XWBcontrol.profile.getSignerSkinNum(),
            pEl = document.getElementById('skin-list');
        
        if(skinId === false)
            skinId = 1;
        
        function onload(){
        
        }
        
        gViewImg.onload = function(){
           util.delClass(gViewWr,'hidden');
           util.addClass(gLoadEl, 'hidden');
        };
        
        function loadViewImg(url){
           // a link href will terminate the next action in ie6, start this action in a timeout.
           setTimeout(function(){
               util.addClass(gViewWr,'hidden');
               util.delClass(gLoadEl, 'hidden');
               gViewImg.src = url;
           }, 0);
        }
        
        function checkSkin(event, pEl){
          var el  = event.srcElement || event.target,
              cls = el.className,
              skinNum,
              re  = /skin-0?(\d+)\s*/i;
          if(re.test(cls)){
            skinNum = re.exec(cls)[1]||1;
            var chs = pEl.childNodes;
            for(var i=0,len=chs.length;i<len;i++){
              if(chs[i].className === 'current')
                chs[i].className = '';
            }
            el.parentNode.className = 'current';
            loadViewImg(parent.XWBcontrol.profile.getSignerSkinUrl(gUid, skinNum, keyStr));
            gSkinNum = skinNum;
          }
        }
      
      loadViewImg(parent.XWBcontrol.profile.getSignerSkinUrl(gUid, skinId, keyStr));

      var chs = pEl.childNodes, idx = 0, ch;
      for(var i=0,len=chs.length;i<len;i++){
        ch = chs[i];
        if(ch.tagName === 'A'){
          idx++;
          if(idx == skinId){
            ch.className = 'current';
            break;
          }
        }
      }
    </script>

<?php
//统计上报[卸载]
$xwb_statType = 'sat';
$xwb_statArgs = array();
echo XWB_plugin::statUrl( $xwb_statType, $xwb_statArgs, true );
?>
</body>
</html>