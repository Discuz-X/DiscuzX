<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_makehtml.php 32684 2013-02-28 09:46:29Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$operation = in_array($operation, array('all', 'index', 'category', 'article', 'topic', 'aids', 'catids', 'topicids', 'makehtmlsetting')) ? $operation : 'all';

cpheader();
shownav('portal', 'HTML管理');

$css = '<style>
		#mk_result {width:100%; margin-top:10px; border: 1px solid #ccc; margin: 0 auto; font-size:16px; text-align:center; display:none; }
		#mk_article, #mk_category, #mk_index{ line-height:30px;}
		#progress_bar{ width:400px; height:25px; border:1px solid #09f; margin: 10px auto 0; display:none;}
		.mk_msg{ width:100%; line-height:120px;}
		</style>';

$result = '<tr><td colspan="15"><div id="mk_result">
			<div id="progress_bar"></div>
			<div id="mk_topic" mktitle="专题"></div>
			<div id="mk_article" mktitle="文章"></div>
			<div id="mk_category" mktitle="频道"></div>
			<div id="mk_index" mktitle="首页"></div>
			</div></td></tr>';

if(!in_array($operation, array('aids', 'catids', 'topicids'))) {
	showsubmenu('html',  array(
		array('生成全部', 'makehtml&operation=all', $operation == 'all'),
		array('生成首页', 'makehtml&operation=index', $operation == 'index'),
		array('生成频道', 'makehtml&operation=category', $operation == 'category'),
		array('生成文章', 'makehtml&operation=article', $operation == 'article'),
		array('生成专题', 'makehtml&operation=topic', $operation == 'topic'),
		array('设置', 'makehtml&operation=makehtmlsetting', $operation == 'makehtmlsetting')
	), '');
}
if($operation == 'all') {

	showtips('<li>生成指定起始时间以后发布的文章的HTML文件</li><li>生成指定起始时间以后发布过文章的频道HTML文件</li><li>生成门户首页的HTML文件</li>');

	showformheader('makehtml&operation=all');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;
	showsetting('起始时间', 'starttime', dgmdate(TIMESTAMP, 'Y-m-d'), 'calendar', '', '', '', '1');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">生成全部</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '重新生成';
	var starttime = form['starttime'].value;
	if(starttime){
		make_html_article(starttime);
	}
	return false;
});

function make_html_ok() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">全部文件生成成功</div>';
}
function make_html_index() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在生成首页...</div>';
	dom.style.display = 'block';
	new make_html_batch('portal.php?', 0, make_html_ok, dom, 1);
}

function make_html_category(starttime){
	var dom = $('mk_category');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在检查可生成的频道页面...</div>';
	dom.style.display = 'block';
	starttime = starttime || form['starttime'].value;
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=catids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s) {
			new make_html_batch('portal.php?mod=list&catid=', s.split(','), make_html_index, dom);
		} else {
			dom.innerHTML = '没有可生成的频道页面<br/>现在开始生成主页文件...<br /><a href="javascript:void(0);" onclick="\$(\'mk_category\').style.display = \'none\';make_html_index();">如果您的浏览器没有反应，请点击继续...</a>';
			setTimeout(function(){\$('mk_category').style.display = 'none'; make_html_index();}, 1000);
		}
	});
}

function make_html_article(starttime) {
	var dom = $('mk_article');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在检查可生成的文章页面...</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=aids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s){
			new make_html_batch('portal.php?mod=view&aid=', s.split(','), make_html_category, dom);
		} else {
			dom.innerHTML = '没有可生成的文章页面<br/>现在开始生成频道文件...<br /><a href="javascript:void(0);" onclick="\$(\'mk_article\').style.display = \'none\';make_html_category();">如果您的浏览器没有反应，请点击继续...</a>';
			setTimeout(function(){\$('mk_article').style.display = 'none'; make_html_category();}, 1000);
		}
	});
}

</script>
EOT;
	showtablefooter();
	showformfooter();

} elseif($operation == 'index') {

	showtips('<li>生成门户首页的HTML文件</li>');

	showformheader('makehtml&operation=index');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>', $css;
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">生成首页</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '重新生成';
	this.disabled = true;
	make_html_index();
	return false;
});

function make_html_index() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在生成首页...</div>';
	dom.style.display = 'block';
	new make_html_batch('portal.php?', 0, null, dom, 1);
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif($operation == 'category') {

	loadcache('portalcategory');
	showtips('<li>生成指定频道首页HTML文件</li><li>生成指定起始时间以后发布过文章的频道首页HTML文件</li>');
	showformheader('makehtml&operation=category');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('起始时间', 'starttime', '', 'calendar', '', '', '', '1');
	$selectdata = array('category', array(array(0, '生成所有频道')));
	mk_format_category(array_keys($_G['cache']['portalcategory']));
	showsetting('选择频道', $selectdata, 0, 'mselect');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">生成频道</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '重新生成';
	var starttime = form['starttime'].value;
	if(starttime){
		make_html_category(starttime);
	} else {
		var category = form['category'];
		var allcatids = [];
		var selectedids = [];
		for(var i = 0; i < category.options.length; i++) {
			var option = category.options[i];
			allcatids.push(option.value);
			if(option.selected) {
				selectedids.push(option.value);
			}
		}
		if(selectedids.length) {
			new make_html_batch('portal.php?mod=list&catid=', selectedids[0] == 0 ? allcatids : selectedids, make_html_category_ok, $('mk_category'));
		} else {
			var dom = $('mk_index');
			dom.style.display = 'block';
			dom.innerHTML = '没有可生成的频道页面';
		}
	}
	return false;
});

function make_html_category_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">选择的频道全部生成成功</div>';
}
function make_html_category(starttime){
	var dom = $('mk_category');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在检查可生成的频道页面...</div>';
	dom.style.display = 'block';
	starttime = starttime || form['starttime'].value;
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=catids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s) {
			new make_html_batch('portal.php?mod=list&catid=', s.split(','), make_html_category_ok, dom);
		} else {
			dom.innerHTML = '没有可生成的频道页面';
			setTimeout(function(){\$('mk_category').style.display = 'none'; make_html_index();}, 1000);
		}
	});
}

</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif($operation == 'article') {

	loadcache('portalcategory');
	showtips('<li>生成指定起始时间以后发布的文章的HTML文件</li><li>生成指定频道下所有文章的HTML文件</li><li>生成指定起始时间以后发布的文章的HTML文件</li>');
	showformheader('makehtml&operation=category');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('起始时间', 'starttime', dgmdate(TIMESTAMP - 86400, 'Y-m-d'), 'calendar', '', '', '', '1');
	$selectdata = array('category', array(array(0, '生成所有频道')));
	mk_format_category(array_keys($_G['cache']['portalcategory']));
	showsetting('选择频道', $selectdata, 0, 'mselect');
	showsetting('起始ID(空或0表示从头开始)', 'startid', 0, 'text');
	showsetting('结束ID(空或0表示直到结束)', 'endid', 0, 'text');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">生成文章</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '重新生成';
	var starttime = form['starttime'].value;
	var category = form['category'];
	var allcatids = [];
	var selectedids = [];
	for(var i = 0; i < category.options.length; i++) {
		var option = category.options[i];
		allcatids.push(option.value);
		if(option.selected) {
			selectedids.push(option.value);
		}
	}
	var startid = parseInt(form['startid'].value);
	var endid = parseInt(form['endid'].value);
	if(starttime || selectedids.length || startid || endid) {
		make_html_article(starttime, selectedids[0] == 0 ? -1 : selectedids, startid, endid);
	} else {
		var dom = $('mk_index');
		dom.style.display = 'block';
		dom.innerHTML = '没有可生成的文章页面';
	}
	return false;
});

function make_html_article_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">全部文章生成成功</div>';
}

function make_html_article(starttime, catids, startid, endid) {
	catids = catids || -1;
	startid = startid || 0;
	endid = endid || 0;
	var dom = $('mk_article');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在检查可生成的文章页面...</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=aids&inajax=1&frame=no&starttime='+starttime+'&catids='+(catids == -1 ? '' : catids.join(','))+'&startid='+startid+'&endid='+endid, function (s) {
		if(s && s.indexOf('<') < 0){
			new make_html_batch('portal.php?mod=view&aid=', s.split(','), make_html_article_ok, dom);
		} else {
			dom.innerHTML = '没有可生成的文章页面';
		}
	});
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif ($operation == 'aids') {
	$starttime = strtotime($_GET['starttime']);
	$catids = $_GET['catids'];
	if($catids) {
		$catids = array_map('intval', explode(',', $catids));
	}
	$startid = intval($_GET['startid']);
	$endid = intval($_GET['endid']);
	$data = array();
	if($starttime || $catids || $startid || $endid) {
		$data = C::t('portal_article_title')->fetch_all_aid_by_dateline($starttime, $catids, $startid, $endid);
	}

	helper_output::xml($data ? implode(',', array_keys($data)) : '');

} elseif($operation == 'topic') {

	showtips('<li>生成指定起始时间以后发布的专题的HTML文件</li>');
	showformheader('makehtml&operation=topic');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('起始时间', 'starttime', '', 'calendar', '', '', '', '1');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">生成专题</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '重新生成';
	var starttime = form['starttime'].value;
	if(starttime) {
		make_html_topic(starttime);
	} else {
		var dom = $('mk_index');
		dom.style.display = 'block';
		dom.innerHTML = '没有可生成的专题页面';
	}
	return false;
});

function make_html_topic_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">全部专题生成成功</div>';
}

function make_html_topic(starttime) {
	var dom = $('mk_topic');
	dom.innerHTML = '<div class="mk_msg">请稍等，正在检查可生成的专题页面...</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=topicids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s && s.indexOf('<') < 0){
			new make_html_batch('portal.php?mod=topic&topicid=', s.split(','), make_html_topic_ok, dom);
		} else {
			dom.innerHTML = '没有可生成的专题页面';
		}
	});
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif ($operation == 'topicids') {
	$starttime = strtotime($_GET['starttime']);
	$data = array();
	if($starttime) {
		$data = C::t('portal_topic')->fetch_all_topicid_by_dateline($starttime);
	}

	helper_output::xml($data ? implode(',', array_keys($data)) : '');

} elseif ($operation == 'catids') {
	$starttime = strtotime($_GET['starttime']);
	$data = array();
	if($starttime) {
		loadcache('portalcategory');
		foreach ($_G['cache']['portalcategory'] as $key => $value) {
			if($value['lastpublish'] >= $starttime) {
				$data[$key] = $key;
			}
		}
	}
	helper_output::xml($data ? implode(',', $data) : '');

} elseif ($operation == 'makehtmlsetting') {

	if(!submitcheck('makehtmlsetting')) {
		$setting = $_G['setting'];
		showformheader("makehtml&operation=makehtmlsetting");
		showtableheader('', 'nobottom', 'id="makehtml"'.($_GET['operation'] != 'makehtmlsetting' ? ' style="display: none"' : ''));
		showsetting('setting_functions_makehtml', 'settingnew[makehtml][flag]', $setting['makehtml']['flag'], 'radio', 0, 1);
		showsetting('setting_functions_makehtml_extendname', 'settingnew[makehtml][extendname]', $setting['makehtml']['extendname'] ? $setting['makehtml']['extendname'] : 'html', 'text');
		showsetting('setting_functions_makehtml_articlehtmldir', 'settingnew[makehtml][articlehtmldir]', $setting['makehtml']['articlehtmldir'], 'text');
		$dirformat = array('settingnew[makehtml][htmldirformat]',
				array(array(0, dgmdate(TIMESTAMP, '/Ym/')),
					array(1, dgmdate(TIMESTAMP, '/Ym/d/')),
					array(2, dgmdate(TIMESTAMP, '/Y/m/')),
					array(3, dgmdate(TIMESTAMP, '/Y/m/d/')))
			);
		showsetting('setting_functions_makehtml_htmldirformat', $dirformat, $setting['makehtml']['htmldirformat'], 'select');
		showsetting('setting_functions_makehtml_topichtmldir', 'settingnew[makehtml][topichtmldir]', $setting['makehtml']['topichtmldir'], 'text');
		showsetting('setting_functions_makehtml_indexname', 'settingnew[makehtml][indexname]', $setting['makehtml']['indexname'] ? $setting['makehtml']['indexname'] : 'index', 'text');
		showtagfooter('tbody');
		showtablefooter();
		showsubmit('makehtmlsetting', 'submit');
		showformfooter();
	} else {
		$settingnew = $_GET['settingnew'];
		if(isset($settingnew['makehtml'])) {
			$settingnew['makehtml']['flag'] = intval($settingnew['makehtml']['flag']);
			if(!$settingnew['makehtml']['extendname']) {
				$settingnew['makehtml']['extendname'] = 'html';
			} else {
				$re = NULL;
				preg_match_all('/[^\w\d\_\.]/',$settingnew['makehtml']['extendname'],$re);
				if(!empty($re[0]) || strpos('..', $settingnew['makehtml']['extendname']) !== false) {
					cpmsg(cplang('setting_functions_makehtml_extendname_invalid').','.cplang('return'), NULL, 'error');
				}
			}
			if(!$settingnew['makehtml']['indexname']) {
				$settingnew['makehtml']['indexname'] = 'index';
			} else {
				$re = NULL;
				preg_match_all('/[^\w\d\_]/',$settingnew['makehtml']['indexname'],$re);
				if(!empty($re[0]) || strpos('..', $settingnew['makehtml']['indexname']) !== false) {
					cpmsg(cplang('setting_functions_makehtml_indexname_invalid').','.cplang('return'), NULL, 'error');
				}
			}
			$settingnew['makehtml']['articlehtmldir'] = trim($settingnew['makehtml']['articlehtmldir'], ' /');
			$re = NULL;
			preg_match_all('/[^\w\d\_\\]/',$settingnew['makehtml']['articlehtmldir'],$re);
			if(!empty($re[0])) {
				cpmsg(cplang('setting_functions_makehtml_articlehtmldir_invalid').','.cplang('return'), NULL, 'error');
			}
			$settingnew['makehtml']['topichtmldir'] = trim($settingnew['makehtml']['topichtmldir'], ' /');
			$re = NULL;
			preg_match_all('/[^\w\d\_\\]/',$settingnew['makehtml']['topichtmldir'],$re);
			if(!empty($re[0])) {
				cpmsg(cplang('setting_functions_makehtml_topichtmldir_invalid').','.cplang('return'), NULL, 'error');
			}
			$settingnew['makehtml']['htmldirformat'] = intval($settingnew['makehtml']['htmldirformat']);
			C::t('common_setting')->update('makehtml', $settingnew['makehtml']);
			updatecache('setting');
		}
		cpmsg('setting_update_succeed', 'action=makehtml&operation=makehtmlsetting', 'succeed');
	}


}

function mk_format_category($catids) {
	global $_G, $selectdata;
	foreach($catids as $catid) {
		if(!isset($selectdata[1][$catid])) {
			$cate = $_G['cache']['portalcategory'][$catid];
			if($cate['level'] == 0) {
				$selectdata[1][$catid] = array($catid, $cate['catname']);
				mk_format_category($cate['children']);
			} elseif ($cate['level'] == 1) {
				$selectdata[1][$catid] = array($catid, '&nbsp;&nbsp;&nbsp;'.$cate['catname']);
				mk_format_category($cate['children']);
			} elseif ($cate['level'] == 2) {
				$selectdata[1][$catid] = array($catid, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cate['catname']);
			}
		}
	}
}
?>