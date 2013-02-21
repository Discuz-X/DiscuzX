
var url = window.location.href;
var siteUrl = url.substr(0, url.indexOf('api/manyou/cloud_channel.htm'));

var params = getUrlParams(url);
var action = params['action'];
var identifier = params['identifier'];

setTopFrame();
changPageStatus(identifier);

function changPageStatus(identifier) {

	var navText = '';
	var cloudText = '';
	var menuItem;


	try {
		var discuzIframe = window.top.window;
		menuItem = discuzIframe.document.getElementById('menu_cloud').getElementsByTagName('li');
		cloudText = 'Discuz!' +  discuzIframe.document.getElementById('header_cloud').innerHTML;
	} catch(e) {
		return false;
	}

	for (i=0; i < menuItem.length; i ++) {
		if (menuItem[i].innerHTML.indexOf('operation=' + identifier) != -1) {

			menuItem[i].getElementsByTagName('a')[0].className = 'tabon';

			navText = menuItem[i].innerHTML;
			navText = navText.replace(/<em.*<\/em>/i, '');
			p = /<a.+?href="(.+?)".+?>(.+?)<\/a>/i;
			arr = p.exec(navText);

			if (arr) {
				link = arr[1];
				text = arr[2];
				if(discuzIframe.document.getElementById('admincpnav')) {
					title = discuzIframe.document.title;
					if (title.indexOf(' - ') > 0) {
						title = title.substr(0, title.indexOf(' - '));
					}
					discuzIframe.document.title = title + ' - ' + cloudText + ' - ' + text;
					discuzIframe.document.getElementById('admincpnav').innerHTML= cloudText + '&nbsp;&raquo;&nbsp;' + text + '&nbsp;&nbsp;<a target="main" title="&#28155;&#21152;&#21040;&#24120;&#29992;&#25805;&#20316;" href="' + link + '">[+]</a>';
				}
			}
		} else {
			menuItem[i].getElementsByTagName('a')[0].className = '';
		}
	}
}

function setTopFrame() {

	try {
		var topUrl = top.location.href;
	} catch(e) { }

	if (typeof(topUrl) == 'undefined' || topUrl.indexOf(siteUrl) == -1) {
		top.location = siteUrl + 'admin.php?frames=yes&action=cloud&operation=' + identifier;
	}
}

function getUrlParams(url) {

	var pos = url.indexOf('?');

	if (pos < 0) {
		return false;
	}

	var query = url.substr(pos + 1);
	var arr = query.split('&');
	var item = '';
	var _params = [];

	for (k in arr) {
		item = arr[k].split('=');
		_params[item[0]] = item[1];
	}

	return _params;
}