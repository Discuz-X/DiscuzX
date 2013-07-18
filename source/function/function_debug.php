<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_debug.php 28557 2012-03-05 02:50:58Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function debugmessage($ajax = 0) {
	if(!defined('DISCUZ_DEBUG') || !DISCUZ_DEBUG || defined('IN_ARCHIVER') || defined('IN_MOBILE')) {
		return;
	}
	$m = function_exists('memory_get_usage') ? number_format(memory_get_usage()) : '';
	$mt = function_exists('memory_get_peak_usage') ? number_format(memory_get_peak_usage()) : '';
	if($m) {
		$m = '<em>内存:</em> <s>'.$m.'</s> bytes'.($mt ? ', 峰值 <s>'.$mt.'</s> bytes' : '').'<br />';
	}
	global $_G, $_ERRORS;//包括自定义全局页面处理错误集合
	$debugfile = $_G['adminid'] == 1 ? '_debugadmin.php' : '_debug.php';
	$akey = md5(microtime(TRUE));
	$phpinfok = 'I';
	$viewcachek = 'C';
	$mysqlplek = 'P';
	$errortruck = 'E';
	$includes = get_included_files();
	require_once DISCUZ_ROOT.'./source/discuz_version.php';

	$sqldebug = '';
	$n = $discuz_table = 0;
	$sqlw = array();
	$db = & DB::object();
	$queries = count($db->sqldebug);
	$links = array();
	foreach($db->link as $k => $link) {
		$links[(string)$link] = $k;
	}
	$sqltime = 0;
	foreach ($db->sqldebug as $string) {
		$sqltime += $string[1];
		$extra = $dt = '';
		$n++;
		$sql = preg_replace('/'.preg_quote($_G['config']['db']['1']['tablepre']).'[\w_]+/', '<font color=blue>\\0</font>', nl2br(htmlspecialchars($string[0])));
		$sqldebugrow = '<div id="sql_'.$n.'" style="display:none;padding:0">';
		if(preg_match('/^SELECT /', $string[0])) {
			$query = @mysql_query("EXPLAIN ".$string[0], $string[3]);
			$i = 0;
			$sqldebugrow .= '<table style="border-bottom:none">';
			while($row = DB::fetch($query)) {
				if(!$i) {
					$sqldebugrow .= '<tr style="border-bottom:1px dotted gray"><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', array_keys($row)).'&nbsp;</td></tr>';
					$i++;
				}
				if(strexists($row['Extra'], 'Using filesort')) {
					$sqlw['Using filesort']++;
					$extra .= $row['Extra'] = str_replace('Using filesort', '<font color=red>Using filesort</font>', $row['Extra']);
				}
				if(strexists($row['Extra'], 'Using temporary')) {
					$sqlw['Using temporary']++;
					$extra .= $row['Extra'] = str_replace('Using temporary', '<font color=red>Using temporary</font>', $row['Extra']);
				}
				$sqldebugrow .= '<tr><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', $row).'&nbsp;</td></tr>';
			}
			$sqldebugrow .= '</table>';
		}
		$sqldebugrow .= '<table><tr style="border-bottom:1px dotted gray"><td width="400">File</td><td width="80">Line</td><td>Function</td></tr>';
		foreach($string[2] as $error) {
			$error['file'] = isset($error['file']) ? str_replace(array(DISCUZ_ROOT, '\\'), array('', '/'), $error['file']) : 'NULL';
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			!isset($error['line']) && $error['line'] = '0';
			$sqldebugrow .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
			if(strexists($error['file'], 'discuz/discuz_table') || strexists($error['file'], 'table/table')) {
				$dt = ' &bull; '.$error['file'];
				$discuz_table++;
			}
		}
		$sqldebugrow .= '</table></div>'.($extra ? $extra.'<br />' : '').'<br />';

		$sqldebug .= '<li><span style="cursor:pointer" onclick="document.getElementById(\'sql_'.$n.'\').style.display = document.getElementById(\'sql_'.$n.'\').style.display == \'\' ? \'none\' : \'\'">'.$string[1].'s &bull; DBLink '.$links[(string)$string[3]].$dt.'<br />'.$sql.'</span><br /></li>'.$sqldebugrow;
	}
	$ajaxhtml = 'data/'.$debugfile.'_ajax.php';
	if($ajax) {
		$idk = substr(md5($_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']), 0, 4);
		$sqldebug = '<b style="cursor:pointer" onclick="document.getElementById(\''.$idk.'\').style.display=document.getElementById(\''.$idk.'\').style.display == \'\' ? \'none\' : \'\'">Queries: </b> '.$queries.' ('.$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].')<ol id="'.$idk.'" style="display:none">'.$sqldebug.'</ol><br>';
		file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, $sqldebug, FILE_APPEND);
		return;
	}
	file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, '<?php if(empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') { exit; } ?><style>body,table { font-size:12px; }table { width:90%;border:1px solid gray; }</style><a href="javascript:;" onclick="location.href=location.href">Refresh</a><br />');
	foreach($sqlw as $k => $v) {
		$sqlw[$k] = $k.': '.$v;
	}
	$sqlw = '('.($discuz_table ? 'discuz_table: '.$discuz_table.($sqlw ? ', ' : '') : '').($sqlw ? '<s>'.implode(', ', $sqlw).'</s>' : '').')';
	$errorhtml = '';
	foreach($_ERRORS as $k => $err){
		$errorhtml .= '<div class="content"><h1 class="title">Error <span class="errlevel">['.$err[0].']</span>: '.$err[1].'</h1>'
		.'<span class="file">'.$err[2].'['.$err[3].']</span>'.source($err[2], $err[3]).''.$err[5].'</div>';
	}

	$debug = '<?php (empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') && exit; ?>'."\n";
	if($_G['adminid'] == 1 && !$ajax) {
		$debug .= '<?php
if(isset($_GET[\''.$phpinfok.'\'])) { phpinfo(); exit; }

chdir(\'../\');
require \'./source/class/class_core.php\';
C::app()->init();

if(isset($_GET[\''.$viewcachek.'\'])) {
	echo \'<style>body { font-size:12px; }</style>\';
	if(!isset($_GET[\'c\'])) {
		$query = DB::query("SELECT cname FROM ".DB::table("common_syscache"));
		while($names = DB::fetch($query)) {
			echo \'<a href="'.$debugfile.'?k='.$akey.'&'.$viewcachek.'&c=\'.$names[\'cname\'].\'" target="_blank" style="float:left;width:200px">\'.$names[\'cname\'].\'</a>\';
		}
	} else {
		$cache = DB::fetch_first("SELECT * FROM ".DB::table("common_syscache")." WHERE cname=\'$_GET[c]\'");
		echo \'$_G[\\\'cache\\\'][\'.$_GET[\'c\'].\']<br>\';
		debug($cache[\'ctype\'] ? dunserialize($cache[\'data\']) : $cache[\'data\']);
	}
	exit;
}elseif(isset($_GET[\''.$errortruck.'\'])) {?>
<style>
body { margin: 10px; font-size: 10px; font-family: "微软雅黑"; }
.title { background: #911; color: white; padding:10px; margin-top:0; }
.errlevel { font-size: 12px; color: yellow; }
.content { background:#ddd;overflow: hidden;  }
.file { font-size:18px; margin: 0 25px; }
.source {
margin: 0px 20px 0px;
padding: 0.4em;
background: white;
border: dotted 1px #B7C680;
line-height: 1.2em;
}
tt, code, kbd, samp {
font-family: "Courier New";
font-size: 14px;
}
.number {
color: #666;
}
.highlight {
background: #F0EB96;
}
.line {
display: block;
line-height:130%;
}
</style>'
.$errorhtml.
'<?php exit;
}elseif(isset($_GET[\''.$mysqlplek.'\'])) {
	if(!empty($_GET[\'Id\'])) {
		$query = DB::query("KILL ".floatval($_GET[\'Id\']), \'SILENT\');
	}
	$query = DB::query("SHOW FULL PROCESSLIST");
	echo \'<style>table { font-size:12px; }</style>\';
	echo \'<table style="border-bottom:none">\';
	while($row = DB::fetch($query)) {
		if(!$i) {
			echo \'<tr style="border-bottom:1px dotted gray"><td>&nbsp;</td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', array_keys($row)).\'&nbsp;</td></tr>\';
			$i++;
		}
		echo \'<tr><td><a href="'.$debugfile.'?k='.$akey.'&P&Id=\'.$row[\'Id\'].\'">[Kill]</a></td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', $row).\'&nbsp;</td></tr>\';
	}
	echo \'</table>\';
	exit;
}
?>'."\n\n\n\n";
	}
	$debug .= '<!DOCTYPE html><html><head>';
	$debug .= "<script src='../static/js/common.js?".VERHASH."'></script><script>
	function switchTab(prefix, current, total, activeclass) {
	activeclass = !activeclass ? 'a' : activeclass;
	for(var i = 1; i <= total;i++) {
		if(!$(prefix + '_' + i)) {
			continue;
		}
		var classname = ' '+$(prefix + '_' + i).className+' ';
		$(prefix + '_' + i).className = classname.replace(' '+activeclass+' ','').substr(1);
		$(prefix + '_c_' + i).style.display = 'none';
	}
	$(prefix + '_' + current).className = $(prefix + '_' + current).className + ' '+activeclass;
	$(prefix + '_c_' + current).style.display = '';
	parent.$('_debug_iframe').height = (Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 100) + 'px';
	}
	</script>";

	if(!defined('IN_ADMINCP') && file_exists(DISCUZ_ROOT.'./static/image/common/temp-grid.png')) $debug .= <<<EOF
<script type="text/javascript">
var s = '<button style="position: fixed; width: 40px; right: 0; top: 30px; border: none; border:1px solid orange;background: yellow; color: red; cursor: pointer;" onclick="var pageHight = top.document.body.clientHeight;$(\'tempgrid\').style.height = pageHight + \'px\';$(\'tempgrid\').style.visibility = top.$(\'tempgrid\').style.visibility == \'hidden\'?\'\':\'hidden\';o.innerHTML = o.innerHTML == \'网格\'?\'关闭\':\'网格\';">网格</button>';
s += '<div id="tempgrid" style="position: absolute; top: 0px; left: 50%; margin-left: -500px; width: 1000px; height: 0; background: url(static/image/common/temp-grid.png); visibility :hidden;"></div>';
top.$('_debug_div').innerHTML = s;
</script>
EOF;

	$_GS = $_GA = '';
	if($_G['adminid'] == 1) {
		foreach($_G as $k => $v) {
			if(is_array($v)) {
				if($k != 'lang') {
					$_GA .= "<li><a name=\"S_$k\"></a><br />['$k'] => ".nl2br(str_replace('  ','&nbsp;', htmlspecialchars(print_r($v, true)))).'</li>';
				}
			} elseif(is_object($v)) {
				$_GA .= "<li><br />['$k'] => <i>object of ".get_class($v)."</i></li>";
			} else {
				$_GS .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
			}
		}
	}
	$modid = $_G['basescript'].(!defined('IN_ADMINCP') ? '::'.CURMODULE : '');
	$svn = '';
	if(file_exists(DISCUZ_ROOT.'./.svn/entries')) {
		$svn = @file(DISCUZ_ROOT.'./.svn/entries');
		$time = $svn[9];
		preg_match('/([\d\-]+)T([\d:]+)/', $time, $a);
		$svn = '.r'.$svn[10].' (最后由 '.$svn[11].' 于 '.dgmdate(strtotime($a[1].' '.$a[2]) + $_G['setting']['timeoffset'] * 3600).' 提交)';
	}
	$max = 10;
	$mc = $mco = '';
	if(class_exists('C') && C::memory()->enable) {
		$mcarray = C::memory()->debug;
		$i = 0;
		$max += count($mcarray);
		foreach($mcarray as $key => $value) {
			$mco .= '<div id="__debug_c_'.(7 + $i).'" style="display:none"><br /><pre>'.print_r($value, 1).'</pre></div>';
			$mc .= '<a id="__debug_7" href="#debugbar" onclick="switchTab(\'__debug\', 7, '.$max.')">['.$key.']</a>'.($value ? '<s>('.count($value).')</s>' : '');
		}
	}
	$debug .= '
		<style>#__debugbarwrap__ { line-height:10px; text-align:left;font:12px Monaco,Consolas,"Lucida Console","Courier New",serif;}
		body { font-size:12px; }
		a, a:hover { color: black;text-decoration:none; }
		s { text-decoration:none;color: red; }
		img { vertical-align:middle; }
		.w td em { margin-left:10px;font-style: normal; }
		#__debugbar__ { padding: 80px 1px 0 1px;  }
		#__debugbar__ table { width:90%;border:1px solid gray; }
		#__debugbar__ div { padding-top: 40px; }
		#__debugbar_s { border-bottom:1px dotted #EFEFEF;background:#FFF;width:100%;font-size:12px;position: fixed; top:0px; left:5px; }
		#__debugbar_s a { color:blue; }
		#__debugbar_s a.a { border-bottom: 1px dotted gray; }
		#__debug_c_1 ol { margin-left: 20px; padding: 0px; }
		#__debug_c_4_nav { background:#FFF; border:1px solid black; border-top:none; padding:5px; position: fixed; top:0px; right:0px }
		</style></head><body>'.
		'<div id="__debugbarwrap__">'.
		'<div id="__debugbar_s">
			<table class="w" width=99%><tr><td valign=top width=50%>'.
				'<b style="float:left;width:1em;height:4em">文件</b>'.
					'<em>版本:</em> Discuz! '.DISCUZ_VERSION.($svn ? $svn : ' '.DISCUZ_RELEASE).'<br />'.
					'<em>ModID:</em> <s>'.$modid.'</s><br />'.
					'<em>包含:</em> '.
						'<a id="__debug_3" href="#debugbar" onclick="switchTab(\'__debug\', 3, '.$max.')">[文件列表]</a>'.
						' <s>'.(count($includes) - 1).($_G['debuginfo']['time'] ? ' in '.number_format(($_G['debuginfo']['time'] - $sqltime), 6).'s' : '').'</s><br />'.
			'<td valign=top>'.
				'<b style="float:left;width:1em;height:5em">服务器</b>'.
					'<em>环境:</em> '.PHP_OS.', '.$_SERVER['SERVER_SOFTWARE'].' MySQL/'.DB::result_first("SELECT VERSION()").'<br />'.
					$m.
					'<em>SQL:</em> '.
						'<a id="__debug_1" href="#debugbar" onclick="switchTab(\'__debug\', 1, '.$max.')">[SQL列表]</a>'.
						'<a id="__debug_4" href="#debugbar" onclick="switchTab(\'__debug\', 4, '.$max.');sqldebug_ajax.location.href = sqldebug_ajax.location.href;">[AjaxSQL列表]</a>'.
						' <s>'.$queries.$sqlw.($_G['debuginfo']['time'] ? ' in '.$sqltime.'s' : '').'</s><br />'.
					'<em>内存缓存:</em> '.$mc.
			'<tr><td valign=top colspan="2">'.
				'<b>客户端</b> <a id="__debug_2" href="#debugbar" onclick="switchTab(\'__debug\', 2, '.$max.')">[详情]</a> <span id="__debug_b"></span>'.
			'<tr><td colspan=2><a name="debugbar">&nbsp;</a>'.
		'<a href="javascript:;" onclick="parent.scrollTo(0,0)" style="float:right">[TOP]&nbsp;&nbsp;&nbsp;</a>'.
		'<img src="../static/image/common/arw_r.gif" /><a id="__debug_5" href="#debugbar" onclick="switchTab(\'__debug\', 5, '.$max.')">$_COOKIE</a>'.
		($_G['adminid'] == 1 ? '<img src="../static/image/common/arw_r.gif" /><a id="__debug_6" href="#debugbar" onclick="switchTab(\'__debug\', 6, 6)">$_G</a>' : '').
		($_G['adminid'] == 1 ?
			'<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$phpinfok.'" target="_blank">phpinfo()</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$mysqlplek.'" target="_blank">MySQL 进程列表</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$viewcachek.'" target="_blank">查看缓存</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$errortruck.'" target="_blank">查看页面错误</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="../misc.php?mod=initsys&formhash='.formhash().'" target="_debug_initframe" onclick="parent.$(\'_debug_initframe\').onload = function () {parent.location.href=parent.location.href;}">更新缓存</a>' : '').
			'<img src="../static/image/common/arw_r.gif" /><a href="../install/update.php" target="_blank">执行 update.php</a>'.
		'</table>'.
		'</div>'.
		'<div id="__debugbar__" style="clear:both">'.
		'<div id="__debug_c_1" style="display:none"><b>Queries: </b> '.$queries.'<ol>';
	$debug .= $sqldebug.'';
	$debug .= '</ol></div>'.
		'<div id="__debug_c_4" style="display:none"><iframe id="sqldebug_ajax" name="sqldebug_ajax" src="../'.$ajaxhtml.'?k='.$akey.'" frameborder="0" width="100%" height="800"></iframe></div>'.
		'<div id="__debug_c_2" style="display:none"><b>IP: </b>'.$_G['clientip'].'<br /><b>User Agent: </b>'.$_SERVER['HTTP_USER_AGENT'].'<br /><b>BROWSER.x: </b><script>for(BROWSERi in BROWSER) {var __s=BROWSERi+\':\'+BROWSER[BROWSERi]+\' \';$(\'__debug_b\').innerHTML+=BROWSER[BROWSERi]!==0?__s:\'\';document.write(__s);}</script></div>'.
		'<div id="__debug_c_3" style="display:none"><ol>';
	foreach ($includes as $fn) {
		$fn = str_replace(array(DISCUZ_ROOT, "\\"), array('', '/'), $fn);
		$debug .= '<li>';
		if(preg_match('/^source\/plugin/', $fn)) {
			$debug .= '[插件]';
		} elseif(preg_match('/^source\//', $fn)) {
			$debug .= '[脚本]';
		} elseif(preg_match('/^data\/template\//', $fn)) {
			$debug .= '[模板]';
		} elseif(preg_match('/^data/', $fn)) {
			$debug .= '[缓存]';
		} elseif(preg_match('/^config/', $fn)) {
			$debug .= '[配置]';
		}
		$debug .= $fn.'</li>';
	}
	$debug .= '<ol></div><div id="__debug_c_5" style="display:none"><ol>';
	foreach($_COOKIE as $k => $v) {
		if(strexists($k, $_G['config']['cookie']['cookiepre'])) {
			$k = '<font color=blue>'.$k.'</font>';
		}
		$debug .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
	}
	$debug .= '</ol></div><div id="__debug_c_6" style="display:none">'.
		'<div id="__debug_c_4_nav"><a href="#S_config">Nav:<br />
			<a href="#top">#top</a><br />
			<a href="#S_config">$_G[\'config\']</a><br />
			<a href="#S_setting">$_G[\'setting\']</a><br />
			<a href="#S_member">$_G[\'member\']</a><br />
			<a href="#S_group">$_G[\'group\']</a><br />
			<a href="#S_cookie">$_G[\'cookie\']</a><br />
			<a href="#S_style">$_G[\'style\']</a><br />
			<a href="#S_cache">$_G[\'cache\']</a><br />
			</div>'.
		'<ol><a name="top"></a>'.$_GS.$_GA.'</ol></div>'.$mco.'</body></html>';
	$fn = 'data/'.$debugfile;
	file_put_contents(DISCUZ_ROOT.'./'.$fn, $debug);
	echo '<iframe src="'.$fn.'?k='.$akey.'" name="_debug_iframe" id="_debug_iframe" style="border-top:1px solid gray;overflow-x:hidden;overflow-y:auto" width="100%" height="120" frameborder="0"></iframe><div id="_debug_div"></div><iframe name="_debug_initframe" id="_debug_initframe" style="display:none"></iframe>';
}
function source($file, $line_number, $padding = 5) {
	if(!$file OR !is_readable($file)) {
		// Continuing will cause errors
		return FALSE;
	}

	// Open the file and set the line position
	$file = fopen($file, 'r');
	$line = 0;

	// Set the reading range
	$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

	// Set the zero-padding amount for line numbers
	$format = '% '.strlen($range['end']).'d';

	$source = '';
	while(($row = fgets($file)) !== FALSE) {
		// Increment the line number
		if(++$line > $range['end'])
			break;

		if($line >= $range['start']) {
			// Make the row safe for output
			$row = htmlspecialchars($row, ENT_NOQUOTES);

			// Trim whitespace and sanitize the row
			$row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

			if($line === $line_number) {
				// Apply highlighting to this row
				$row = '<span class="line highlight">'.$row.'</span>';
			} else {
				$row = '<span class="line">'.$row.'</span>';
			}

			// Add to the captured source
			$source .= $row;
		}
	}

	// Close the file
	fclose($file);

	return '<pre class="source"><code>'.$source.'</code></pre>';
}
?>