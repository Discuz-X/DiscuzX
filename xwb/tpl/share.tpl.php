<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
    var picList = [<?php foreach ($shareData['pics'] as $key => $pic) echo (0 != $key ? ',' : '') . '"' . $pic . '"';?>];
    var transpImg = '<?php echo XWB_plugin::getPluginUrl('images/shareimg/transparent.gif');?>';
    window.onload = function() {
        var ua = navigator.userAgent.toLowerCase();
        var areaText = document.getElementById('fw_content');
        if (ua.match(/msie ([\d.]+)/)) {
            var rng = areaText.createTextRange();
            rng.moveStart("character", areaText.value.length);
            rng.collapse(false);
            rng.select();
        } else if (ua.match(/firefox\/([\d.]+)/)) {
            areaText.focus();
        }
    }
</script>
<title>转发到微博-新浪微博-随时随地分享身边的新鲜事儿</title>
<link href="<?php echo XWB_plugin::getPluginUrl('images/public.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo XWB_plugin::getPluginUrl('images/shareout.css');?>" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="reg_wrap">
	<!-- 顶部 LOGO -->
	<div class="TopName">
        <div class="logo"></div>
        <a href="<?php echo XWB_plugin::getWeiboProfileLink($rst['id']);?>" target="_blank" class="logoLink"></a>
        <div class="op">
        	<span>你正在使用 <a href="<?php echo XWB_plugin::getWeiboProfileLink($rst['id']);?>" target="_blank"  class="userID"><?php echo $rst['screen_name'];?></a> 帐号</span>
            <span class="line">|</span>
            <span><a href="http://v.t.sina.com.cn/share/sharechg.php?appkey=<?php echo XWB_APP_KEY;?>&url=<?php echo urlencode($shareData['url']);?>&title=<?php echo urlencode($shareData['title']);?>&source=&sourceUrl=&%20content=gb2312&pic=">换个帐号？</a></span>
        </div>
    </div>
    <!-- /顶部 LOGO -->
    <div class="reg_main">
    	<b class="bg_regTop">&nbsp;</b>
        <b class="bg_deco_b">&nbsp;</b>
        <div class="reg_pub">
            <form id="shareForm" action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doShare");?>" method="post">
        	<div class="notice">
            	<h2><img src="<?php echo XWB_plugin::getPluginUrl('images/shareimg/transparent.gif');?>" class="wbIcon iconMsg" alt="" title=""/>转发到我的微博，顺便说点什么吧</h2>
              <span id="txt_count_msg">还可以输入<em>140</em>字</span>
            </div>
            <div class="inputTxt">
            	<textarea cols="20" rows="5" id="fw_content" name="message"><?php echo htmlspecialchars($shareData['message']) . ' '; ?></textarea>
            	<dl>
                    <?php if ( ! empty($shareData['pics'])):?>
                    <dt><img src="<?php echo XWB_plugin::getPluginUrl('images/shareimg/transparent.gif');?>" class="wbIcon iconImg" alt="" title="" /><a href="javascript:;" id="btn_forward">请选择一张图片转发</a></dt>
                    <!-- 附带转发图片 -->
                    <dd id="pic_ct">
                    <div class="picList">
                    	<ul id="pic_lst_ul"></ul>
                        <p style="display: none;" id="nopic" class="nopic">
                        	<img width="14" height="14" title="" alt="" class="iconWarn" src="<?php echo XWB_plugin::getPluginUrl('images/shareimg/transparent.gif');?>"/>
                            页面上没有可转发的图片.
                        </p>
                    </div>
                    <div class="pageNum">
                    	<a href="javascript:;" class="cancel" id="cancel">取消添加</a>
                        <span>
                        	<a href="javascript:;" id="pre">&lt;&lt;上一页</a>
                            <strong>共<?php echo count($shareData['pics']);?>张</strong>
                            <a href="javascript:;" id="next">下一页&gt;&gt;</a>
                        </span>
                    </div>
                    </dd>
                    <?php else:?>
                    <dt></dt>
                    <?php endif;?>
                    <input type="hidden" id="share_pic" name="share_pic" value="<?php echo isset($shareData['pics'][0]) ? $shareData['pics'][0] : '';?>"/>
                    <!-- //附带转发图片 -->
                </dl>
            </div>
            <div class="submit">
            	<p style="display:none" id="repeatTip">不要太贪心哦，发一次就够啦。</p>
                <span class="btn_turn"><a class="MIB_bigBtn MIB_bigBtnB" id="submitBtn" href="javascript:;"><cite id="siteText">转发</cite></a></span>
            </div>
            </form>
        </div>
        <b class="bg_regBot">&nbsp;</b>
    </div>
</div>
</body>
</html>
<script src="<?php echo XWB_plugin::getPluginUrl('images/xwb.js');?>"></script>
<script type="text/javascript">
        var Util = XWBcontrol.util;
        var trimReg = new RegExp("(?:^\\s*)|(?:\\s*$)", "g");
        var selectedIndex  = 0;
        
        function trim(s){
            return s.replace(trimReg, "");
        }
        
	    /**
	    * 检查输入的字数
	    *@return {Number}  返回剩余的字数
	    *@param {Number} limit  限制字数
	    */
	    function checkText(text, limit) {
	        var limit = limit || 140;
	        text = trim( text );
	        var matcher = text.match(/[^\x00-\xff]/g), 
	            cLen  = (matcher && matcher.length || 0),
	            last = Math.floor((limit*2 - text.length - cLen)/2);
	        return last;
	    }
    
    var tip = Util.$('txt_count_msg'),
        area = Util.$('fw_content'),
        warnCleaned = true;
    
    function checkWords(){
        var left = checkText(area.value);
        if(left<0){
            tip.innerHTML = '已超出<em>' + Math.abs(left) + '字';  
            Util.addClassIf(tip, 'red'); 
        }else{
            tip.innerHTML = '还可以输入<em>' + left + '字';
            Util.delClass(tip, 'red'); 
        }
        if(!warnCleaned)
            warn('');
    }

    function warn(text){
        var t = Util.$('repeatTip');
        t.innerHTML = '<font color="red">'+text+'</font>';
        t.style.display = '';
        warnCleaned = !text;
    }
    
    function shareSubmit(){
        checkWords.apply(area);
        var v = area.value;
        if(!v){
            warn ('请输入转发内容');
            area.focus();
            return;
        }
        var left = checkText ( area.value );
        if( left < 0){
            warn('已超出'+Math.abs(left)+'字');
            area.focus();
            return;
        }
        Util.$('siteText').innerHTML = '加载中...';
        Util.$('siteText').style.color = '#CCC';
        setTimeout(function(){
            Util.$('shareForm').submit();
        }, 0);
    }
    
    function tagUp(nd, tagName, p){
        var deep = 10;
        while(nd && nd != p && deep>0){
            deep --;
            if(nd.tagName == tagName)
                return nd;
            nd = nd.parentNode;
        }
    }
    
    checkWords.apply(area);
    
    Util.domEvent(area, 'keyup', checkWords);
    
    Util.$('submitBtn').onclick = shareSubmit;
    
    // 图片处理
    var list = Util.$('pic_lst_ul');
    
    if(list){
        var page = {
            
            items : picList,
            
            pageSize : 5,
    
            go : function(current){
                var len = this.items.length, 
                    total = parseInt(len/this.pageSize);
                if(len%this.pageSize)
                    total ++;
                if(current > total && len)
                    current = total;
                if(current < 1)
                    current = 1;
                var beg = (current - 1) * this.pageSize;
                var end = Math.min(beg + this.pageSize, this.items.length) - 1;
                var htmls = [];
                
                while(beg <= end){
                  
                  htmls[htmls.length] = [
                    '<li rel="'+beg+'" ' + (selectedIndex==beg ? 'class="on"':'') + '>',
                        '<a onclick="return false;" href="javascript:;" hidefocus="true">',
                            '<em>',
                                '<img class="pic" src="'+this.items[beg]+'"/>',
                                '<img alt="" class="ico_slt" src="'+transpImg+'"/>',
                            '</em>',
                        '</a>',
                    '</li>'
                    ].join('');
                    beg++;
                }
                Util.$('pic_lst_ul').innerHTML = htmls.join('');
                Util.$('pre').style.display = current>1 ? '':'none';
                Util.$('next').style.display = (current!= total) ? '':'none';
                
                this.current = current;
            }
        };
        
        page.go(1);
        
        Util.$('pre').onclick = function(){
            page.go(page.current-1);
        };
        
        Util.$('next').onclick = function(){
            page.go(page.current+1);
        };
        
        var chs = list.childNodes;
        
        function onItemClick(event){
            var chs = list.childNodes;
            for(var i=0,len=chs.length;i<len;i++){
                if(Util.hasClass(chs[i] , 'on'))
                    Util.delClass(chs[i], 'on');
            }
            event = event||window.event;
            var li = tagUp(event.target||event.srcElement, 'LI', this);
            if(li){
                selectedIndex = li.getAttribute('rel');
                Util.addClass(li, 'on');
                Util.$('share_pic').value = page.items[selectedIndex];
            }
        }
        
        list.onclick = onItemClick;
        
        var isSndImgBnd = false;
        
        Util.$('cancel').onclick = function(){
            Util.$('pic_ct').style.display = 'none';
            Util.$('share_pic').value = '';
            
            if(!isSndImgBnd){
                Util.$('btn_forward').onclick = function(){
                    Util.$('pic_ct').style.display = '';
                    Util.$('share_pic').value = page.items[selectedIndex];;
                    Util.$('btn_forward').innerHTML = '请选择一张图片转发';
                    this.blur();
                    return false;    
                };
                isSndImgBnd = true;
            }
            
            return false;
        };
    }
    
</script>