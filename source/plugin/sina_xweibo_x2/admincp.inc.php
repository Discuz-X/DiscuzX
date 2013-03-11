<?php
/*
 * @version $Id: admincp.inc.php 376 2010-12-08 06:06:23Z yaoying $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//header('Location: http://www.baidu.com');

//echo '<iframe src="xwb.php?m=xwbSiteInterface.pluginCfg" style="position:absolute; left:0px; top:50px; height:600px; width:100%; border:0px;"></iframe>';
//http://ued.koubei.com/?p=243
echo '<iframe id="frame_content" src="xwb.php?m=xwbSiteInterface.pluginCfg" scrolling="no" frameborder="0" onload="this.height=this.contentWindow.document.documentElement.scrollHeight" style="position:absolute; left:0px; top:50px; width:100%; border:0px;"></iframe>';
