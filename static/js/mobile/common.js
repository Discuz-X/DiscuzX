(function($, window, undefined) {

	var supporttouch = "ontouchend" in document;
	!supporttouch && (window.location.href = 'forum.php?mobile=1');


	function triggercustomevent(obj, eventtype, event) {
		var origtype = event.type;
		event.type = eventtype;
		$.event.handle.call(obj, event);
		event.type = origtype;
	}

	$.event.special.tap = {
		setup : function() {
			var thisobj = this;
			var obj = $(thisobj);
			obj.bind('touchstart', function(e) {
				if(e.which && e.which !== 1) {
					return false;
				}
				var origtarget = e.target;
				var origevent = e.originalEvent;
				var timer;

				function cleartaptimer() {
					clearTimeout(timer);
				}
				function cleartaphandlers() {
					cleartaptimer();
					obj.unbind('touchend', clickhandler);
					$(document).unbind('touchcancel', cleartaphandlers);
				}

				function clickhandler(e) {
					cleartaphandlers();
					if(origtarget === e.target) {
						triggercustomevent(thisobj, 'tap', e);
					}
					return false;
				}

				obj.bind('touchend', clickhandler);
				$(document).bind('touchcancel', cleartaphandlers);

				timer = setTimeout(function() {
					triggercustomevent(thisobj, 'taphold', $.Event('taphold', {target:origtarget}));
				}, 750);
				return false;
			});
		}
	};
	$.each(('tap').split(' '), function(index, name) {
		$.fn[name] = function(fn) {
			return this.bind(name, fn);
		};
	});

})(jQuery, this);

var page = {
	converthtml : function() {
		var prevpage = $('div.pg .prev').prop('href');
		var nextpage = $('div.pg .nxt').prop('href');
		var lastpage = $('div.pg label span').text().replace(/[^\d]/g, '') || 0;
		var curpage = $('div.pg input').val() || 1;

		if(!lastpage) {
			prevpage = $('div.pg .pgb a').prop('href');
		}

		var prevpagehref = nextpagehref = '';
		if(prevpage == undefined) {
			prevpagehref = 'javascript:;" class="grey';
		} else {
			prevpagehref = prevpage;
		}
		if(nextpage == undefined) {
			nextpagehref = 'javascript:;" class="grey';
		} else {
			nextpagehref = nextpage;
		}

		var selector = '';
		if(lastpage) {
			selector += '<a id="select_a" style="margin:0 2px;padding:1px 0 0 0;border:0;display:inline-block;position:relative;width:100px;height:31px;line-height:27px;background:url('+STATICURL+'/image/mobile/images/pic_select.png) no-repeat;text-align:left;text-indent:20px;">';
			selector += '<select id="dumppage" style="position:absolute;left:0;top:0;height:27px;opacity:0;width:100px;">';
			for(var i=1; i<=lastpage; i++) {
				selector += '<option value="'+i+'" '+ (i == curpage ? 'selected' : '') +'>第'+i+'页</option>';
			}
			selector += '</select>';
			selector += '<span>第'+curpage+'页</span>';
		}

		$('div.pg').removeClass('pg').addClass('page').html('<a href="'+ prevpagehref +'">上一页</a>'+ selector +'<a href="'+ nextpagehref +'">下一页</a>');
		$('#dumppage').bind('change', function() {
			var href = (prevpage || nextpage);
			window.location.href = href.replace(/page=\d+/, 'page=' + $(this).val());
		});
	},
};

var scrolltop = {
	obj : null,
	init : function(obj) {
		this.obj = obj;
		var fixed = this.isfixed();
		obj.css('opacity', '.618');
		if(fixed) {
			obj.css('bottom', '8px');
		} else {
			obj.css({'visibility':'visible', 'position':'absolute'});
		}
		$(window).bind('resize', function() {
			if(fixed) {
				obj.css('bottom', '8px');
			} else {
				obj.css('top', ($(document).scrollTop() + $(window).height() - 40) + 'px');
			}
		});
		obj.bind('tap', function() {
			$(document).scrollTop($(document).height());
		});
		$(document).bind('scroll', function() {
			if(!fixed) {
				obj.css('top', ($(document).scrollTop() + $(window).height() - 40) + 'px');
			}
			if($(document).scrollTop() >= 400) {
				obj.removeClass('bottom')
				.unbind().bind('tap', function() {
					window.scrollTo('0', '1');
				});
			} else {
				obj.addClass('bottom')
				.unbind().bind('tap', function() {
					$(document).scrollTop($(document).height());
				});
			}
		});

	},
	isfixed : function() {
		var offset = this.obj.offset();
		var scrollTop = $(window).scrollTop();
		var screenHeight = document.documentElement.clientHeight;
		if(offset == undefined) {
			return false;
		}
		if(offset.top < scrollTop || (offset.top - scrollTop) > screenHeight) {
			return false;
		} else {
			return true;
		}
	}
};

var img = {
	init : function(is_err_t) {
		var errhandle = this.errorhandle;
		$('img').bind('load', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			if(obj.width() < 5 && obj.height() < 10 && obj.css('display') != 'none') {
				return errhandle(obj, is_err_t);
			}
			obj.css('display', 'inline');
			obj.css('visibility', 'visible');
			if(obj.width() > window.innerWidth) {
				obj.css('width', window.innerWidth);
			}
			obj.parent().find('.loading').remove();
			obj.parent().find('.error_text').remove();
		})
		.bind('error', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			errhandle(obj, is_err_t);
		});
	},
	errorhandle : function(obj, is_err_t) {
		obj.css('visibility', 'hidden');
		obj.css('display', 'none');
		var parentnode = obj.parent();
		parentnode.find('.loading').remove();
		parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
		var loadnums = parseInt(obj.attr('load')) || 0;
		if(loadnums < 3) {
			obj.attr('src', obj.attr('zsrc'));
			obj.attr('load', ++loadnums);
			return false;
		}
		if(is_err_t) {
			var parentnode = obj.parent();
			parentnode.find('.loading').remove();
			parentnode.append('<div class="error_text">点击重新加载</div>');
			parentnode.find('.error_text').one('click', function() {
				obj.attr('load', 0).find('.error_text').remove();
				parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
				obj.attr('src', obj.attr('zsrc'));
			});
		}
		return false;
	}
};

var atap = {
	init : function() {
		$('.atap').bind('tap', function() {
			var obj = $(this);
			obj.css({'background':'#6FACD5', 'color':'#FFFFFF', 'font-weight':'bold', 'text-decoration':'none', 'text-shadow':'0 1px 1px #3373A5'});
			return false;
		});
		$('.atap a').unbind('click');
	}
};


var JSMENU = new Object;
var popup = {
	init : function() {
		var $this = this;
		$('.popup').each(function(index, obj) {
			obj = $(obj);
			var pop = $(obj.attr('href'));
			if(pop && pop.attr('popup')) {
				pop.css({'display':'none'});
				obj.bind('tap click', function(e) {
					$this.open(pop);
				});
			}
		});
		this.maskinit();
	},
	maskinit : function() {
		var $this = this;
		$('#mask').unbind().bind('tap click', function() {
			$this.close();
		});
	},

	open : function(pop, type, url) {
		this.close();
		this.maskinit();
		if(typeof pop == 'string') {
			$('#ntcmsg').remove();
			if(type == 'alert') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><input class="button2" type="button" value="确定" onclick="popup.close();"></dd></div>'
			} else if(type == 'confirm') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><input class="redirect button2" type="button" value="确定" href="'+ url +'"><a href="javascript:;" onclick="popup.close();">取消</a></dd></div>'
			}
			$('body').append('<div id="ntcmsg" style="display:none;">'+ pop +'</div>');
			pop = $('#ntcmsg');
		}
		if(JSMENU[pop.attr('id')]) {
			$('#' + pop.attr('id') + '_jsmenu').html(pop.html()).css({'height':pop.height()+'px', 'width':pop.width()+'px'});
		} else {
			pop.parent().append('<div class="dialogbox" id="'+ pop.attr('id') +'_jsmenu" style="height:'+ pop.height() +'px;width:'+ pop.width() +'px;">'+ pop.html() +'</div>');
		}
		var popupobj = $('#' + pop.attr('id') + '_jsmenu');
		var left = (window.innerWidth - popupobj.width()) / 2;
		var top = (document.documentElement.clientHeight - popupobj.height()) / 2;
		popupobj.css({'display':'block','position':'fixed','left':left,'top':top,'z-index':120,'opacity':1});
		$('#mask').css({'display':'block','width':'100%','height':'100%','position':'fixed','top':'0','left':'0','background':'black','opacity':'0.2','z-index':'100'});
		JSMENU[pop.attr('id')] = pop;
	},
	close : function() {
		$('#mask').css('display', 'none');
		$.each(JSMENU, function(index, obj) {
			$('#' + index + '_jsmenu').css('display','none');
		});
	}
};

var dialog = {
	init : function() {
		$('.dialog').live('click', function() {
			var obj = $(this);
			popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
			$.ajax({
				type : 'GET',
				url : obj.attr('href') + '&inajax=1',
				dataType : 'xml'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
			})
			.error(function() {
				window.location.href = obj.attr('href');
				popup.close();
			});
			return false;
		});
	},

};

var formdialog = {
	init : function() {
		$('.formdialog').live('click', function() {
			popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
			var obj = $(this);
			var formobj = $(this.form);
			$.ajax({
				type:'POST',
				url:formobj.attr('action') + '&handlekey='+ formobj.attr('id') +'&inajax=1',
				data:formobj.serialize(),
				dataType:'xml'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
			})
			.error(function() {
				window.location.href = obj.attr('href');
				popup.close();
			});
			return false;
		});
	}
};

var redirect = {
	init : function() {
		$('.redirect').live('click', function() {
			var obj = $(this);
			popup.close();
			window.location.href = obj.attr('href');
		});
	}
};

var display = {
	init : function() {
		$('.display').each(function(index, obj) {
			obj = $(obj);
			var dis = $(obj.attr('href'));
			if(dis && dis.attr('display')) {
				dis.css({'display':'none'});
				obj.bind('click', function(e) {
					if(dis.attr('display') == 'true') {
						dis.css('display', 'block');
						dis.attr('display', 'false');
					} else {
						dis.css('display', 'none');
						dis.attr('display', 'true');
					}
					return false;
				});
			}
		});
	}
};

var geo = {
	latitude : null,
	longitude : null,
	errmsg : null,
	timeout : 5000,
	getcurrentposition : function() {
		if(!!navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(this.locationSuccess, this.locationError, {
				enableHighAcuracy : true,
				timeout : this.timeout,
				maximumAge : 3000
			});
		}
	},
	locationError : function(error) {
		this.errmsg = 'error';
		switch(error.code) {
			case error.TIMEOUT:
				this.errmsg = "获取位置超时，请重试";
				break;
			case error.POSITION_UNAVAILABLE:
				this.errmsg = '无法检测到您的当前位置';
			    break;
		    case error.PERMISSION_DENIED:
		        this.errmsg = '请允许能够正常访问您的当前位置';
		        break;
		    case error.UNKNOWN_ERROR:
		        this.errmsg = '发生未知错误';
		        break;
		}
	},
	locationSuccess : function(position) {
		this.latitude = position.coords.latitude;
		this.longitude = position.coords.longitude;
		this.errmsg = '';
	}
};

function mygetnativeevent(event) {

	while(event && typeof event.originalEvent !== "undefined") {
		event = event.originalEvent;
	}
	return event;
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

var safescripts = {}, evalscripts = [];

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $('#' + id)[0]) {
		$('#' + id)[0].parentNode.removeChild($('#' + id)[0]);
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (!document.charset ? document.characterSet : document.charset);
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

$(document).ready(function() {

	if($('div.pg')) {
		page.converthtml();
	}
	if($('.scrolltop')) {
		scrolltop.init($('.scrolltop'));
	}
	if($('img')) {
		img.init(1);
	}
	if($('.popup')) {
		popup.init();
	}
	if($('.dialog')) {
		dialog.init();
	}
	if($('.formdialog')) {
		formdialog.init();
	}
	if($('.redirect')) {
		redirect.init();
	}
	if($('.display')) {
		display.init();
	}
	if($('.atap')) {
		atap.init();
	}
});