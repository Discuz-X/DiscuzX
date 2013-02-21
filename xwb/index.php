<?php
/*
 * @version $Id: index.php 665 2011-04-26 05:32:22Z yaoying $
 */
//-----------------------------------------------------------------------
/// 插件入口文件
//-----------------------------------------------------------------------
//生产环境建议关闭错误报告
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
/// 引入插件环境
require_once dirname(__FILE__). '/plugin.env.php';

//-----------------------------------------------------------------------
/// 初始化并执行请求
XWB_plugin::init();
XWB_plugin::request();

//echo memory_get_usage(). '<br />'. memory_get_peak_usage();