function reloadImage(obj, code) {
	obj.onclick=null;
	obj.className='img';
	obj.innerHTML=unescape(code);
}

function autoLoadNextPage(url, text, nextpage) {
	var l = new Object();
	l.url = url;
	l.text = text;
	l.nextpage = nextpage;
	l.running = false;
	l.result = {};
	l.more = 'div#moreing';
	l.morepage = 'div#morepage';
	l.py = 0;
	l.move = 0;
	l.beforeTouch = function () {
		if(!$(l.more).length) {
			$(l.morepage).append('<div id="moreing">' + l.text + '</div>');
		}
	};
	l.afterTouch = function () {
		$(l.more).remove();
	}
	l.afterUpdate = function (first) {
		var result = l.result.Variables;
		if(result.webview_page) {
			if(!first) {
				if($(l.morepage+'_'+result.page)) {
					$(l.morepage+'_'+result.page).remove();
				}
				if(result.ppp > result.posts) {
					$(l.morepage).append('<div id="morepage_'+result.page+'">'+result.webview_page+'</div>');
				} else {
					$(l.morepage).append(result.webview_page);
					this.nextpage++;
				}
			} else {
				$(l.morepage).html(result.webview_page);
				if(result.ppp == result.posts) {
					this.nextpage++;
				}
			}
		}
		imgwidth();
	};
	$(window).bind("touchstart", function(event) {
		var touch = event.originalEvent.touches[0];
		startY = touch.pageY;
		l.py = startY;
		l.beforeTouch();
	});
	$(window).bind("touchmove", function(event) {
		var touch = event.originalEvent.touches[0];
		startY = touch.pageY;
		l.move = startY - l.py;
	});
	$(window).bind("touchend", function(event) {
		if(l.running) {
			return;
		}
		l.afterTouch();
		if(l.move < -10) {
			if($(window).height() == $(document).height() || $(document).scrollTop() + $(window).height() > $(document).height() - 10){
				if(l.nextpage > 1) {
					l.running = true;
					$.getJSON(l.url + l.nextpage, function (result) {
						l.result = result;
						l.running = false;
						l.afterUpdate();
					});
				} else {
					l.running = true;
					$.getJSON(l.url + '&more=1', function (result) {
						l.result = result;
						l.running = false;
						l.afterUpdate(1);
					});
				}
			}
		}

	});
}

function imgwidth() {
	$('.img img').each(function(){
		var dwidth = $('.detail_wrap').width() - 30, rwidth = $(this).width(), rheight = $(this).height();
		if(rwidth > dwidth) {
			$(this).width(dwidth);
			if(rheight) {
				$(this).height(rheight * dwidth / rwidth);
			}
		}
	});
}