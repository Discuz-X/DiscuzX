<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_error.php 21027 2011-03-10 07:47:41Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_error
{

	function system_error($message, $show = true, $save = true, $halt = true) {
		if(!empty($message)) {
			$message = lang('error', $message);
		} else {
			$message = lang('error', 'error_unknow');
		}

		list($showtrace, $logtrace) = discuz_error::debug_backtrace();

		if($save) {
			$messagesave = '<b>'.$message.'</b><br><b>PHP:</b>'.$logtrace;
			discuz_error::write_error_log($messagesave);
		}

		if($show) {
			if(!defined('IN_MOBILE')) {
				discuz_error::show_error('system', "<li>$message</li>", $showtrace, 0);
			} else {
				discuz_error::mobile_show_error('system', "<li>$message</li>", $showtrace, 0);
			}
		}

		if($halt) {
			exit();
		} else {
			return $message;
		}
	}

	function template_error($message, $tplname) {
		$message = lang('error', $message);
		$tplname = str_replace(DISCUZ_ROOT, '', $tplname);
		$message = $message.': '.$tplname;
		discuz_error::system_error($message);
	}

	function debug_backtrace() {
		$skipfunc[] = 'discuz_error->debug_backtrace';
		$skipfunc[] = 'discuz_error->db_error';
		$skipfunc[] = 'discuz_error->template_error';
		$skipfunc[] = 'discuz_error->system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::_execute';

		$show = $log = '';
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(DISCUZ_ROOT, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			if(in_array($func, $skipfunc)) {
				break;
			}
			$error[line] = sprintf('%04d', $error['line']);

			$show .= "<li>[Line: $error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? ' -> ' : '';$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
		}
		return array($show, $log);
	}

	function db_error($message, $sql) {
		global $_G;

		list($showtrace, $logtrace) = discuz_error::debug_backtrace();

		$title = lang('error', 'db_'.$message);
		$title_msg = lang('error', 'db_error_message');
		$title_sql = lang('error', 'db_query_sql');
		$title_backtrace = lang('error', 'backtrace');
		$title_help = lang('error', 'db_help_link');

		$db = &DB::object();
		$dberrno = $db->errno();
		$dberror = str_replace($db->tablepre,  '', $db->error());
		$sql = htmlspecialchars(str_replace($db->tablepre,  '', $sql));

		$msg = '<li>[Type] '.$title.'</li>';
		$msg .= $dberrno ? '<li>['.$dberrno.'] '.$dberror.'</li>' : '';
		$msg .= $sql ? '<li>[Query] '.$sql.'</li>' : '';

		discuz_error::show_error('db', $msg, $showtrace, false);
		unset($msg, $phperror);

		$errormsg = '<b>'.$title.'</b>';
		$errormsg .= "[$dberrno]<br /><b>ERR:</b> $dberror<br />";
		if($sql) {
			$errormsg .= '<b>SQL:</b> '.$sql;
		}
		$errormsg .= "<br />";
		$errormsg .= '<b>PHP:</b> '.$logtrace;

		discuz_error::write_error_log($errormsg);
		exit();

	}

	function show_error($type, $errormsg, $phpmsg = '') {
		global $_G;

		ob_end_clean();
		$gzip = getglobal('gzipcompress');
		ob_start($gzip ? 'ob_gzhandler' : null);

		$host = $_SERVER['HTTP_HOST'];
		$phpmsg = trim($phpmsg);
		$title = $type == 'db' ? 'Database' : 'System';
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>$host - $title Error</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$_G['config']['output']['charset']}" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
	body { background-color: white; color: black; }
	#container { width: 650px; }
	#message   { width: 650px; color: black; background-color: #FFFFCC; }
	#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
	.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
	.help  { font: 12px verdana, arial, sans-serif; color: red;}
	.red  {color: red;}
	a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
	a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
	-->
	</style>
</head>
<body>
<table cellpadding="1" cellspacing="5" id="container">
<tr>
	<td id="bodytitle" width="100%">Discuz! $title Error </td>
</tr>
EOT;

		if($type == 'db') {
			$helplink = "http://faq.comsenz.com/?type=mysql&dberrno=".rawurlencode(DB::errno())."&dberror=".rawurlencode(DB::error());
			echo <<<EOT
<tr>
	<td class="bodytext">The database has encountered a problem. <a href="$helplink" target="_blank"><span class="red">Need Help?</span></a></td>
</tr>
EOT;
		} else {
			echo <<<EOT
<tr>
	<td class="bodytext">Your request has encountered a problem. </td>
</tr>
EOT;
		}

		echo <<<EOT
<tr><td><hr size="1"/></td></tr>
<tr><td class="bodytext">Error messages: </td></tr>
<tr>
	<td class="bodytext" id="message">
		<ul> $errormsg</ul>
	</td>
</tr>
EOT;

		if(!empty($phpmsg)) {
			echo <<<EOT
<tr><td class="bodytext">&nbsp;</td></tr>
<tr><td class="bodytext">Program messages: </td></tr>
<tr>
	<td class="bodytext">
		<ul> $phpmsg </ul>
	</td>
</tr>
EOT;
		}

		$endmsg = lang('error', 'error_end_message', array('host'=>$host));
		echo <<<EOT
<tr>
	<td class="help"><br /><br />$endmsg</td>
</tr>
</table>
</body>
</html>
EOT;
		$exit && exit();

	}

	function mobile_show_error($type, $errormsg, $phpmsg) {
		global $_G;

		ob_end_clean();
		ob_start();

		$host = $_SERVER['HTTP_HOST'];
		$phpmsg = trim($phpmsg);
		$title = 'Mobile '.($type == 'db' ? 'Database' : 'System');
		echo <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html>
<head>
	<title>$host - $title Error</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
	body { background-color: white; color: black; }
	UL, LI { margin: 0; padding: 2px; list-style: none; }
	#message   { color: black; background-color: #FFFFCC; }
	#bodytitle { font: 11pt/13pt verdana, arial, sans-serif; height: 20px; vertical-align: top; }
	.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
	.help  { font: 12px verdana, arial, sans-serif; color: red;}
	.red  {color: red;}
	a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
	a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
	-->
	</style>
</head>
<body>
<table cellpadding="1" cellspacing="1" id="container">
<tr>
	<td id="bodytitle" width="100%">Discuz! $title Error </td>
</tr>
EOT;

		echo <<<EOT
<tr><td><hr size="1"/></td></tr>
<tr><td class="bodytext">Error messages: </td></tr>
<tr>
	<td class="bodytext" id="message">
		<ul> $errormsg</ul>
	</td>
</tr>
EOT;
		if(!empty($phpmsg)  && $type == 'db') {
			echo <<<EOT
<tr><td class="bodytext">&nbsp;</td></tr>
<tr><td class="bodytext">Program messages: </td></tr>
<tr>
	<td class="bodytext">
		<ul> $phpmsg </ul>
	</td>
</tr>
EOT;
		}
		$endmsg = lang('error', 'mobile_error_end_message', array('host'=>$host));
		echo <<<EOT
<tr>
	<td class="help"><br />$endmsg</td>
</tr>
</table>
</body>
</html>
EOT;
		$exit && exit();
	}

	function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}

	function write_error_log($message) {

		$message = discuz_error::clear($message);
		$time = time();
		$file =  DISCUZ_ROOT.'./data/log/'.date("Ym").'_errorlog.php';
		$hash = md5($message);

		$uid = getglobal('uid');
		$ip = getglobal('clientip');

		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.htmlspecialchars(discuz_error::clear($_SERVER['REQUEST_URI']));
		$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 10000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?PHP exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		error_log($message, 3, $file);
	}

}