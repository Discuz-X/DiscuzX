<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: seccode.php 27959 2012-02-17 09:52:22Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

header('Content-Type: image');
readfile('static/image/common/none.gif');
exit;

?>