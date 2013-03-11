<?php
/*
 * @version $Id: install.php 733 2011-05-13 07:05:45Z yaoying $
 */
if( !defined('IN_DISCUZ') || !defined('IN_ADMINCP') ) {
	exit('Access Denied');
}

$_GET['finish'] = (isset($_GET['finish']) && $_GET['finish'] == 1) ? 1 : 0;

$xwb_p_root = 'xwb';

if( !$_GET['finish']  ){
	echo '<iframe src="'. $xwb_p_root. '/install/index.php" scrolling="no" frameborder="0" onload="this.height=this.contentWindow.document.documentElement.scrollHeight" style="position:absolute; left:0px; top:50px; width:100%; border:0px;"></iframe>';
}else{
	$finish = true;
}