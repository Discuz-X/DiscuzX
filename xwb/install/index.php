<?php
/*
 * @version $Id: index.php 375 2010-12-08 06:06:11Z yaoying $
 */
/*
if(!defined('IN_DISCUZ')){
	exit('本安装程序必须通过DiscuzX后台安装！');
}
*/
//-----------------------------------------------------------------------
/// 插件安装入口文件
//-----------------------------------------------------------------------
define('IN_XWB_INSTALL_ENV', 1);
define('XWB_P_SESSION_OPERATOR', 'NATIVE');
define('XWB_P_SESSION_STORAGE_TYPE', '');

//生产环境建议关闭错误报告
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);

//-----------------------------------------------------------------------
/// 引入插件环境
require_once dirname(__FILE__). '/../plugin.env.php';
//-----------------------------------------------------------------------
/// 引入安装库
require_once 'cfg.php';
require_once 'xwb_install.class.php';
//-----------------------------------------------------------------------
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
$xwbIst = new xwb_install;
$xwbIst->install($step);
//-----------------------------------------------------------------------


?>