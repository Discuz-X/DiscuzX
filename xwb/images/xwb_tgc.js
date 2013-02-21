var _userDataSearched = [];
var oldAuthor = showauthor;
var showauthor = function (ctrlObj, menuId){
    oldAuthor.apply(this, arguments);
    var pid = menuId.replace('userinfo', '');
    var node = document.getElementById(menuId);
    var SinaIdObject = document.getElementById('UserSinaId' + pid);
    var SinaId = SinaIdObject ? SinaIdObject.value : 0;
    if(!SinaId || SinaId < 1){
    	return;
    }
    var IFNode = document.getElementById('weiboInfo' + pid);
    var ATNode = document.getElementById('weiboAT' + pid);
    var TipData = XWBcontrol.TipPanel.userData(SinaId);
    var params = {'pid':pid, 'node':node, 'SinaId':SinaId, 'IFNode':IFNode, 'ATNode':ATNode};
    if( !TipData ){
    	if(_userDataSearched[SinaId]){
    		return ;
    	}
    	_userDataSearched[SinaId] = 1;
        XWBcontrol.getUserData(SinaId, function(data, status){
            if(false !== status){
                XWBcontrol.TipPanel.userData(SinaId, data);
                TipShowSign(data, params);
            }
        });
    } else {
        TipShowSign(TipData, params);
    }
}
var TipShowSign = function(TipData, params){
    if( !params.IFNode){
        params.IFNode = document.createElement('div');
        params.IFNode.id = 'weiboInfo' + params.pid;
        params.IFNode.className = 'i y tipGetCount-top';
        params.node.appendChild(params.IFNode);
    }
    params.IFNode.innerHTML = '<img src="' + _xwb_cfg_data.loadingImgUrl + '" />';
    if(TipData)
        params.IFNode.innerHTML = '<span class="tipGetCount-text">关注</span><span class="tipGetCount-num">'
            + TipData.friends_count + '</span><span class="tipGetCount-text">粉丝</span><span class="tipGetCount-num">'
            + TipData.followers_count + '</span><span class="tipGetCount-text">微博</span><span class="tipGetCount-num">'
            + TipData.statuses_count + '</span>';
    else
        params.IFNode.innerHTML = '<span>数据获取失败</span>';
    if( !params.ATNode){
        params.ATNode = document.createElement('div');
        params.ATNode.id = 'weiboAT' + params.pid;
        params.ATNode.className = 'i y tipGetCount-top';
        params.node.appendChild(params.ATNode);
    }
    params.ATNode.innerHTML = (0 < XWBcontrol.getSiteId() && !XWBcontrol.isCurrent(params.SinaId)) ?
        TipData.isFriend ?
        '<a href="javascript:void(0)" class="tipGetCount-already-attention-btn"></a>'
        : '<a onclick="XWBcontrol.onAddFriendClick(event, \''
        + params.SinaId + '\')" href="/xwb.php?m=xwbSiteInterface.attention&amp;att_id='
        + params.SinaId + '" target="_blank" class="tipGetCount-attention-btn"></a>'
        : '';
}

//var TipUserCache = {};
//var CacheExpire = 120;
//var TipGetCache = function(key, value){
//    if( !TipUserCache) TipUserCache = {};
//    if(value === undefined){
//        var UserInfo = TipUserCache[key];
//        if(UserInfo){
//            var CreatTime = UserInfo.CreatTime;
//            if((new Date() - CreatTime)/1000 < CacheExpire)
//                return UserInfo.data;
//        }
//        return null;
//    }
//    TipUserCache[key] = {data:value,CreatTime:+new Date()};
//}