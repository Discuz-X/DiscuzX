var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser'});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

//note loadcss
var CSSLOADED = [];
var JSLOADED = [];
//note JSMENU
var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':200,'menu':300,'dialog':400,'prompt':500};
JSMENU['float'] = '';

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
	HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}

//note FixPrototypeForGecko
if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
			var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

function $(id) {
	return !id ? null : document.getElementById(id);
}

function setcookie(cookieName, cookieValue, seconds) {
	var expires = new Date();
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	expires.setTime(expires.getTime() + seconds * 1000);
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ '; path=/; domain=addon.discuz.com';
}

function doane(event, preventDefault, stopPropagation) {
	var preventDefault = isUndefined(preventDefault) ? 1 : preventDefault;
	var stopPropagation = isUndefined(stopPropagation) ? 1 : stopPropagation;
	e = event ? event : window.event;
	if(!e) {
		e = getEvent();
	}
	if(!e) {
		return null;
	}
	if(preventDefault) {
		if(e.preventDefault) {
			e.preventDefault();
		} else {
			e.returnValue = false;
		}
	}
	if(stopPropagation) {
		if(e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	return e;
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}

		}
	}
	return false;
}

function ajaxinnerhtml(showid, s) {
	if(showid.tagName != 'TBODY') {
		showid.innerHTML = s;
	} else {
		while(showid.firstChild) {
			showid.firstChild.parentNode.removeChild(showid.firstChild);
		}
		var div1 = document.createElement('DIV');
		div1.id = showid.id+'_div';
		div1.innerHTML = '<table><tbody id="'+showid.id+'_tbody">'+s+'</tbody></table>';
		$('append_parent').appendChild(div1);
		var trs = div1.getElementsByTagName('TR');
		var l = trs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(trs[0]);
		}
		var inputs = div1.getElementsByTagName('INPUT');
		var l = inputs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(inputs[0]);
		}
		div1.parentNode.removeChild(div1);
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

function Ajax(recvType, waitId) {

	var aj = new Object();

	aj.loading = '请稍候...';//note public
	aj.recvType = recvType ? recvType : 'XML';//note public
	aj.waitId = waitId ? $(waitId) : null;//note public

	aj.resultHandle = null;//note private
	aj.sendString = '';//note private
	aj.targetUrl = '';//note private

	//note loading 为 null 时，表示使用默认，为空字符串时表示不显示。
	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};

	//note 默认为 XML 方式
	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};

	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : $(waitid);
	};

	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};

	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	//note private
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + aj.loading + '</span>';
		}
	};

	//note private
	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
					aj.resultHandle('<a href="' + aj.targetUrl + '" target="_blank" style="color:red">内部错误，无法显示此内容</a>' , aj);
				} else {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
				}
			}
		}
	};

	//note public
	aj.get = function(targetUrl, resultHandle) {
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		if(window.XMLHttpRequest) {
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);
		} else {
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();
		}
	};
	//note public
	aj.post = function(targetUrl, sendString, resultHandle) {
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	return aj;
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /^\w+\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

//note 封装函数参数
function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

var evalscripts = [];
function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $(id)) {
		$(id).parentNode.removeChild($(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

function ajaxupdateevents(obj, tagName) {
	tagName = tagName ? tagName : 'A';
	var objs = obj.getElementsByTagName(tagName);
	for(k in objs) {
		var o = objs[k];
		ajaxupdateevent(o);
	}
}

function ajaxupdateevent(o) {
	if(typeof o == 'object' && o.getAttribute) {
		if(o.getAttribute('ajaxtarget')) {
			if(!o.id) o.id = Math.random();//note 该标签唯一标识
			var ajaxevent = o.getAttribute('ajaxevent') ? o.getAttribute('ajaxevent') : 'click';//note 默认为 click 事件
			var ajaxurl = o.getAttribute('ajaxurl') ? o.getAttribute('ajaxurl') : o.href;//note 默认为 click 事件
			_attachEvent(o, ajaxevent, newfunction('ajaxget', ajaxurl, o.getAttribute('ajaxtarget'), o.getAttribute('ajaxwaitid'), o.getAttribute('ajaxloading'), o.getAttribute('ajaxdisplay')));
			if(o.getAttribute('ajaxfunc')) {
				o.getAttribute('ajaxfunc').match(/(\w+)\((.+?)\)/);
				_attachEvent(o, ajaxevent, newfunction(RegExp.$1, RegExp.$2));
			}
		}
	}
}

/*
 * url: 需求请求的 url
 * id : 显示的 id
 * waitid: 等待的 id，默认为显示的 id，如果 waitid 为空字符串，则不显示 loading...， 如果为 null，则在 showid 区域显示
 * linkid: 是哪个链接触发的该 ajax 请求，该对象的属性(如 ajaxdisplay)保存了一些 ajax 请求过程需要的数据。
*/
function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = typeof waitid == 'undefined' || waitid === null ? showid : waitid;
	var x = new Ajax();
	x.setLoading(loading);
	x.setWaitId(waitid);
	x.display = typeof display == 'undefined' || display == null ? '' : display;
	x.showId = $(showid);//note 自定义属性。

	if(url.substr(strlen(url) - 1) == '#') {
		url = url.substr(0, strlen(url) - 1);
		x.autogoto = 1;
	}

	var url = url + '&inajax=1&ajaxtarget=' + showid;
	x.get(url, function(s, x) {
		var evaled = false;
		if(s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			//note 隐藏 Loading... 切换内容显示状态
			if(x.showId) {
				x.showId.style.display = x.display;
				ajaxinnerhtml(x.showId, s);
				ajaxupdateevents(x.showId);
				if(x.autogoto) scroll(0, x.showId.offsetTop);
			}
		}

		ajaxerror = null;
		if(recall && typeof recall == 'function') {
			recall();
		} else if(recall) {
			eval(recall);
		}
		if(!evaled) evalscript(s);
	});
}

//note id 为 menuid
function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $(ajaxframeid);
	var formtarget = $(formid).target;

	var handleResult = function() {
		var s = '';
		var evaled = false;

		try {
			s = $(ajaxframeid).contentWindow.document.XMLDocument.text;
		} catch(e) {
			try {
				s = $(ajaxframeid).contentWindow.document.documentElement.firstChild.wholeText;
			} catch(e) {
				try {
					s = $(ajaxframeid).contentWindow.document.documentElement.firstChild.nodeValue;
				} catch(e) {
					s = '内部错误，无法显示此内容';
				}
			}
		}
		if(s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			if(showidclass != 'onerror') {
				$(showid).className = showidclass;
			} else {
				showError(s);
				ajaxerror = true;
			}
		}
		if(submitbtn) {
			submitbtn.disabled = false;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml($(showid), s);
		}
		ajaxerror = null;
		if($(formid)) $(formid).target = formtarget;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		$('append_parent').removeChild(ajaxframe.parentNode);
	};
	//note 避免重复创建iframe，判断IFRAME对象是否存在
	if(!ajaxframe) {
		var div = document.createElement('div');
		div.style.display = 'none';
		div.innerHTML = '<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1"></iframe>';
		$('append_parent').appendChild(div);
		ajaxframe = $(ajaxframeid);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	$(formid).target = ajaxframeid;
	var action = $(formid).getAttribute('action');
	$(formid).action = action.replace(/\&inajax\=1/g, '')+'&inajax=1';
	$(formid).submit();
	if(submitbtn) {
		submitbtn.disabled = true;
	}
	doane();
	return false;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall, idclass, contentclass) {
	if(!ctrlObj.getAttribute('mid')) {
		var ctrlid = ctrlObj.id;
		if(!ctrlid) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	} else {
		var ctrlid = ctrlObj.getAttribute('mid');
		if(!ctrlObj.id) {

			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	}
	var menuid = ctrlid + '_menu';
	var menu = $(menuid);
	if(isUndefined(timeout)) timeout = 3000;
	if(isUndefined(cache)) cache = 1;
	if(isUndefined(pos)) pos = '43';
	if(isUndefined(duration)) duration = timeout > 0 ? 0 : 3;
	if(isUndefined(idclass)) idclass = 'p_pop';
	if(isUndefined(contentclass)) contentclass = 'p_opt';
	var func = function() {
		showMenu({'ctrlid':ctrlObj.id,'menuid':menuid,'duration':duration,'timeout':timeout,'pos':pos,'cache':cache,'layer':2});
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
	};

	if(menu) {
		if(menu.style.display == '') {
			hideMenu(menuid);
		} else {
			func();
		}
	} else {
		menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = idclass;
		menu.innerHTML = '<div class="' + contentclass + '" id="' + menuid + '_content"></div>';
		$('append_parent').appendChild(menu);
		var url = (!isUndefined(ctrlObj.href) ? ctrlObj.href : ctrlObj.attributes['href'].value);
		url += (url.indexOf('?') != -1 ? '&' :'?') + 'ajaxmenu=1';
		ajaxget(url, menuid + '_content', 'ajaxwaitid', '', '', func);
	}
	doane();
}

//note 得到一个定长的hash值， 依赖于 stringxor()
function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

//note 将两个字符串进行异或运算，结果为英文字符组合
function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

/*
 * 加载css
 */
function loadcss(cssname) {
	if(!CSSLOADED[cssname]) {
		if(!$('css_' + cssname)) {
			css = document.createElement('link');
			css.id = 'css_' + cssname,
			css.type = 'text/css';
			css.rel = 'stylesheet';
			css.href = 'template/default2/common/' + cssname + '.css';
			var headNode = document.getElementsByTagName("head")[0];
			headNode.appendChild(css);
		} else {
			$('css_' + cssname).href = 'template/default2/common/' + cssname + '.css?' + VERHASH;
		}
		CSSLOADED[cssname] = 1;
	}
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = $(ctrlid);
	var menuObj = $(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 600 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && $(drag) ? $(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	var ctrlclass = isUndefined(v['ctrlclass']) ? '' : v['ctrlclass'];
	var winhandlekey = isUndefined(v['win']) ? '' : v['win'];
	zindex = cover ? zindex + 500 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if(ctrlObj) {
		//note 初始化ctrlObj
		if(!ctrlObj.getAttribute('initialized')) {
			ctrlObj.setAttribute('initialized', true);
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				}
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	//note 初始化menuObj
	if(!menuObj.getAttribute('initialized')) {
		menuObj.setAttribute('initialized', true);
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			return doane(e, 0, 1);
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
			coverObj.style.opacity = 0.5;
			coverObj.onclick = function () {hideMenu();};
			$('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}

	if(cover) $(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O > 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 20;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 40);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	menuObj.style.display = '';
	if(ctrlObj && ctrlclass) {
		ctrlObj.className += ' ' + ctrlclass;
		menuObj.setAttribute('ctrlid', ctrlid);
		menuObj.setAttribute('ctrlclass', ctrlclass);
	}
	if(pos != '*') {
		setMenuPosition(showid, menuid, pos);
	}
	if(BROWSER.ie && BROWSER.ie < 7 && winhandlekey && $('fwin_' + winhandlekey)) {
		$(menuid).style.left = (parseInt($(menuid).style.left) - parseInt($('fwin_' + winhandlekey).style.left)) + 'px';
		$(menuid).style.top = (parseInt($(menuid).style.top) - parseInt($('fwin_' + winhandlekey).style.top)) + 'px';
	}
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
}

function setMenuPosition(showid, menuid, pos) {
	var showObj = $(showid);
	var menuObj = menuid ? $(menuid) : $(showid + '_menu');
	if(isUndefined(pos) || !pos) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var important = pos.indexOf('!') != -1 ? 1 : 0;
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0, ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0, bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = fetchOffset(showObj);
		sx = sxy['left'];
		sy = sxy['top'];
		sw = showObj.offsetWidth;
		sh = showObj.offsetHeight;
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:
			bpl = sx;
			bpt = sy;
			break;
		case 2:
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			break;
		case 1:
			ml = bpl - mw;
			mt = bpt - mh;
			break;
		case 2:
			ml = bpl;
			mt = bpt - mh;
			break;
		case 3:
			ml = bpl;
			mt = bpt;
			break;
		case 4:
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(!important) {
		if(in_array(direction, [1, 4]) && ml < 0) {
			ml = bpl;
			if(in_array(basePoint, [1, 4])) ml += sw;
		} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
			ml = bpl - mw;
			if(in_array(basePoint, [2, 3])) {
				ml -= sw;
			} else if(basePoint == 4) {
				ml += sw;
			}
		}
		if(in_array(direction, [1, 2]) && mt < 0) {
			mt = bpt;
			if(in_array(basePoint, [1, 2])) mt += sh;
		} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
			mt = bpt - mh;
			if(in_array(basePoint, [3, 4])) mt -= sh;
		}
	}
	if(pos.substr(0, 3) == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	if(ml) menuObj.style.left = ml + 'px';
	if(mt) menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = $(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		var ctrlObj = '', ctrlclass = '';
		if((ctrlObj = $(menuObj.getAttribute('ctrlid'))) && (ctrlclass = menuObj.getAttribute('ctrlclass'))) {
			var reg = new RegExp(' ' + ctrlclass);
			ctrlObj.className = ctrlObj.className.replace(reg, '');
		}
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(menuObj.cache) {
				if(menuObj.style.visibility != 'hidden') {
					menuObj.style.display = 'none';
					if(menuObj.cover) $(attr + '_cover').style.display = 'none';
				}
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) $(attr + '_cover').parentNode.removeChild($(attr + '_cover'));
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 20;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 40);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			position = getCurrentStyle(obj, 'position', 'position');
			if(position == 'relative') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}

function bbcode2html(str) {
	str = str.replace(/\[url\]\s*((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)([^\[\"']+?)\s*\[\/url\]/ig, function($1, $2, $3, $4) {return cuturl($2 + $4);});
	str = str.replace(/\[url=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?)\]([\s\S]+?)\[\/url\]/ig, '<a href="$1$3" target="_blank">$4</a>');
	str = str.replace(/\[color=([^\[\<]+?)\]/ig, '');
	str = str.replace(/\[@([\w-]{6,20}),?(\d+)?\]/ig, function($1, $2, $3) {return '<iframe src="http://follow.v.t.qq.com/index.php?c=follow&a=quick&name=' + $2 + '&style=3&t='+new Date().getTime()+'&f=1" width="'+(!$3 || $3> 300 ? 168 : $3)+'" height="20" frameborder="0" scrolling="no" allowtransparency="true" style="margin:0 auto;"></iframe>';});
	str = str.replace(/\[title\](.+?)\[\/title\]/ig, '<div class="bm_h"><h2>$1</h2></div>');
	s = '';
	for(i = 0;i < str.length;i++) {
		s += str.charCodeAt(i - 1) + ' ';
	}
	str = preg_replace(
		['\\\[\\\/color\\\]', '\\\[b\\\]', '\\\[\\\/b\\\]', '\\\[i\\\]', '\\\[\\\/i\\\]', '\\\[u\\\]', '\\\[\\\/u\\\]', '\\\[s\\\]', '\\\[\\\/s\\\]', '\\\[hr\\\]', '\\\[list\\\]', '\\\[list=1\\\]', '\\\[list=a\\\]', '\\\[list=A\\\]', '\\s?\\\[\\\*\\\]', '\\\[\\\/list\\\]', '(\r\n|\n|\r)'],
		['', '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<strike>', '</strike>', '<hr class="line" />', '<ul class="li type0">', '<ul type=1 class="li type1">', '<ul type=a class="li type2">', '<ul type=A class="li type3">', '<li>', '</ul>', '<br />'], str, 'g');
	return '<div class="bbcode">' + str + '</div>';
}

function cuturl(url) {
	var length = 65;
	var urllink = '<a href="' + (url.toLowerCase().substr(0, 4) == 'www.' ? 'http://' + url : url) + '" target="_blank">';
	if(url.length > length) {
		url = url.substr(0, parseInt(length * 0.5)) + ' ... ' + url.substr(url.length - parseInt(length * 0.3));
	}
	urllink += url + '</a>';
	return urllink;
}

function showbbcode(id) {
	var obj = $(id);
	obj.outerHTML = bbcode2html(obj.value);
	obj.style.display = '';
}

function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function display(id) {
	var obj = $(id);
	if(obj.style.visibility) {
		obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
	} else {
		obj.style.display = obj.style.display == '' ? 'none' : '';
	}
}

function altStyle(obj, disabled) {
	function altStyleClear(obj) {
		var input, lis, i;
		lis = obj.parentNode.getElementsByTagName('li');
		for(i=0; i < lis.length; i++){
			lis[i].className = '';
		}
	}
	var disabled = !disabled ? 0 : disabled;
	if(disabled) {
		return;
	}

	var input, lis, i, cc, o;
	cc = 0;
	lis = obj.getElementsByTagName('li');
	for(i=0; i < lis.length; i++){
		lis[i].onclick = function(e) {
			o = BROWSER.ie ? event.srcElement.tagName : e.target.tagName;
			altKey = BROWSER.ie ? window.event.altKey : e.altKey;
			if(cc) {
				return;
			}
			cc = 1;
			input = this.getElementsByTagName('input')[0];
			if(input.getAttribute('type') == 'checkbox' || input.getAttribute('type') == 'radio') {
				if(input.getAttribute('type') == 'radio') {
					altStyleClear(this);
				}

				if(BROWSER.ie || o != 'INPUT' && input.onclick) {
					input.click();
				}
				if(this.className != 'checked') {
					this.className = 'checked';
					input.checked = true;
				} else {
					this.className = '';
					input.checked = false;
				}
				if(altKey && input.name.match(/^multinew\[\d+\]/)) {
					miid = input.id.split('|');
					mi = 0;
					while($(miid[0] + '|' + mi)) {
						$(miid[0] + '|' + mi).checked = input.checked;
						if(input.getAttribute('type') == 'radio') {
							altStyleClear($(miid[0] + '|' + mi).parentNode);
						}
						$(miid[0] + '|' + mi).parentNode.className = input.checked ? 'checked' : '';
						mi++;
					}
				}
			}
		};
		lis[i].onmouseup = function(e) {
			cc = 0;
		}
	}
}

function showanchor(obj) {
	var navs = $('submenu').getElementsByTagName('a');
	for(var i = 0; i < navs.length; i++) {
		if(navs[i].id.substr(0, 4) == 'nav_' && navs[i].id != obj.id) {
			if($(navs[i].id.substr(4))) {
				navs[i].className = '';
				$(navs[i].id.substr(4)).style.display = 'none';
			}
		}
	}
	obj.className = 'a';
	$(obj.id.substr(4)).style.display = '';
}

var itemanchor_comment = 0;
function itemanchor(obj, type) {
	showanchor(obj);
	if(type == 0 && $('tb_revmemo')) {
		$('tb_revmemo').style.display = '';
	}
	if(type != 2) {
		$('tb_comment').style.display = '';
	}
}

function editorsize(obj, msg) {
	if($(msg).style.height != '500px') {
		$(msg).style.height= '500px';
		obj.innerHTML = '缩小';
	} else {
		$(msg).style.height= '100px';
		obj.innerHTML = '放大';
	}
}

function getapp(param) {
	if(siteurl) {
		sitekey = hex_md5(param + sitekey);
		location.href = siteurl + 'api/addons/channel.htm?' + param + '/' + sitekey + '/' + t;
	} else {
		alert('站点错误，无法安装应用');
	}
}

function resetEscAndF5(e) {
	e = e ? e : window.event;
	actualCode = e.keyCode ? e.keyCode : e.charCode;
	if(actualCode == 116) {
		location.reload();
		if(document.all) {
			e.keyCode = 0;
			e.returnValue = false;
		} else {
			e.cancelBubble = true;
			e.preventDefault();
		}
	}
}

var rateitemscore = 0;
function ratehover(obj, e) {
	var objpos = fetchOffset(obj);
	var pos = parseInt((e.clientX - objpos.left) / obj.offsetWidth / 0.2) + 1;
	obj.v = pos;
	obj.childNodes[0].style.width = (pos * 20) + '%';
	obj.nextSibling.innerHTML = pos;
	rateitemscore = pos;
}

function rateop(obj, href) {
	ajaxget(href + '&score=' + rateitemscore, 'rateret');
	obj.onmousemove = null;
}

var images = new Array();
function showimage() {
	setTimeout(function () {
		showimageST();
	}, 100);
}

function showimageST() {
	var imagecomplete = 0, loaded = 0;
	for(i in images) {
		if($('image_' + i).complete && $('image_' + i).status) {
			imagecomplete++;
		}
		if(!$('image_' + i).status && !loaded) {
			$('image_' + i).src = images[i];
			$('image_' + i).status = 1;
			loaded = 1;
		}
	}
	if(imagecomplete < images.length) {
		setTimeout(function () {
			showimageST();
		}, 100);
	}
}

function redirect(url) {
	window.location.replace(url);
}

document.documentElement.onkeydown = resetEscAndF5;

function shareTo(type){
	var shareURL = {
		'tencent': '"http://share.v.t.qq.com/index.php?c=share&a=index&assname=discuzaddon&appkey=08d170f431bb4736bac7dcf67c6d0995&pic="+(_pic?_pic.join("|"):"")+"&"',
		'qzone': '"http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?site='+encodeURI('Discuz! 应用中心')+'&pics="+(_pic?_pic.join("|"):"")+"&"',
		'pengyou': '"http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?site='+encodeURI('Discuz! 应用中心')+'&to=pengyou&pics="+(_pic?_pic.join("|"):"")+"&"',
		'sina': '"http://service.weibo.com/share/share.php?pic="+(_pic?_pic[0]:"")+"&"'
	};
	var _url = encodeURIComponent($('share_url').innerHTML);
	var _pic = function(area) {
		if(!area) {
			return '';
		}
		var _imgarr = area.getElementsByTagName("img");
		var _srcarr = [];
		for (var i = 0; i < _imgarr.length; i++) {
			_srcarr.push(encodeURIComponent(_imgarr[i].src));
		}
		return _srcarr;
	} ($('share_pic'));
	url = eval(shareURL[type])
	var _t = $('share_txt').innerHTML;
	if(_t.length > 120){
		_t= _t.substr(0,117)+'...';
	}
	_t = '%23' + encodeURI('Discuz应用中心') + '%23 ' + encodeURI(_t);
	var _u = url + 'url=' + _url + '&title=' + _t;
	var left = (window.screen.width - 700) / 2;
	var top = (window.screen.height - 680) / 2;
	window.open( _u,'', 'width=700,height=680,left='+left+',top='+top+',toolbar=0,menubar=0,scrollbars=0,location=1,resizable=0,status=0' );
};

function QQLogin() {
	var left = (window.screen.width - 560) / 2;
	var top = (window.screen.height - 460) / 2;
	var A=window.open('index.php?mod=developer&ac=connect', 'TencentLogin', 'left='+left+',top='+top+',width=560,height=460,menubar=0,scrollbars=1,resizable=1,status=1,titlebar=0,toolbar=0,scrollbars=0,location=1');
}

var ListPage = 1;
function ListMore(url) {
	var Nextpage = ListPage + 1;
	var x = new Ajax();
	x.get(url + '&page=' + Nextpage + '&inajax=yes', function(s, x) {
		if(s) {
			$('listbody_' + ListPage).outerHTML = s;
			ListPage = Nextpage;
			if(!$('listbody_' + ListPage)) {
				$('ListMoreLink').outerHTML = '';
			}
			$('ListPage').innerHTML = '';
		} else {
			$('ListMoreLink').outerHTML = '';
		}
	});
}
/*
 * 全选
 * form 表单对象
 * prefix name 前缀，默认空
 * checkall checkall对象自己的name，默认 chkall
 */
function checkAll(form, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != checkall && !e.disabled && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
			if(e.checked) {
				count++;
			}
		}
	}
	return count;
}
/*
 * 输出 Flash
 */
function AC_FL_RunContent() {
	var str = '';
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if(BROWSER.ie && !BROWSER.opera) {
		str += '<object ';
		for (var i in ret.objAttrs) {
			str += i + '="' + ret.objAttrs[i] + '" ';
		}
		str += '>';
		for (var i in ret.params) {
			str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
		}
		str += '</object>';
	} else {
		str += '<embed ';
		for (var i in ret.embedAttrs) {
			str += i + '="' + ret.embedAttrs[i] + '" ';
		}
		str += '></embed>';
	}
	return str;
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}

function dplaceholder(obj, classname) {
	obj.className = classname;
	obj.innerHTML = '';
	obj.onfocus = null;
}

function display(id) {
	var obj = $(id);
	if(obj.style.visibility) {
		obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
	} else {
		obj.style.display = obj.style.display == '' ? 'none' : '';
	}
}

var addrowdirect = 0;
var addrowkey = 0;
function addrow(obj, type) {
	var table = obj.parentNode.parentNode.parentNode.parentNode;
	if(!addrowdirect) {
		var row = table.insertRow(obj.parentNode.parentNode.rowIndex);
	} else {
		var row = table.insertRow(obj.parentNode.parentNode.rowIndex + 1);
	}
	var typedata = rowtypedata[type];
	for(var i = 0; i <= typedata.length - 1; i++) {
		var cell = row.insertCell(i);
		cell.colSpan = typedata[i][0];
		var tmp = typedata[i][1];
		if(typedata[i][2]) {
			cell.className = typedata[i][2];
		}
		tmp = tmp.replace(/\{(n)\}/g, function($1) {return addrowkey;});
		tmp = tmp.replace(/\{(\d+)\}/g, function($1, $2) {return addrow.arguments[parseInt($2) + 1];});
		cell.innerHTML = tmp;
	}
	addrowkey ++;
	addrowdirect = 0;
}

function check_identifier(id, resultid) {
	var msg = '';
	var x = new Ajax('HTML');
	x.setWaitId(resultid);
	x.get('develop.php?mod=plugin&action=edit&operation=check_identifier&id='+$(id).value, function(s,x){
		if(s == '1') {
			msg = '<font color="red">插件ID已经存在，会影响对外发布，建议更改</font>';
		} else if(s == '2') {
			msg = '插件前缀已被占用，不会影响发布，但建议更改';
		} else if(s == '0') {
			msg = '<font color="green">插件前缀目前没有被占用</font>';
		} else {
			msg = '检测失败，请稍后重试。';
		}
		$(resultid).innerHTML = msg;
		$(resultid).style.display = '';
	}
	);
}
