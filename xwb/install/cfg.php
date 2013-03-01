<?php
/*
 * @version $Id: cfg.php 734 2011-05-13 07:06:29Z yaoying $
 */
/// 安装配置文件
//-----------------------------------------------------------------------
define('IS_IN_XWB_INSTALL',	true);
//-----------------------------------------------------------------------
$_xwb_install = array();
//-----------------------------------------------------------------------
$_xwb_install['check_succ_ck_name'] = '_XWB_ENV_CHECK_STATUS';
$_xwb_install['lock_file']		= XWB_P_DATA.'/xwb_install.lock';
//-----------------------------------------------------------------------
/// 插入内容到附属站点时的注释标识，用于安装和卸载
$_xwb_install['hack_flag_i']	= " ------ Don't delete or edit this line , it's used for  uninstall. ".date("Y-m-d H:i:s")."------ ";
$_xwb_install['hack_flag_ps']	= '/*{xwb_start';
$_xwb_install['hack_flag_pe']	= '/*{xwb_end';
$_xwb_install['hack_flag_p']	= 'xwb}*/';
$_xwb_install['hack_flag_hs']	= '<!--*{xwb_start';
$_xwb_install['hack_flag_he']	= '<!--*{xwb_end';
$_xwb_install['hack_flag_h']	= 'xwb}*-->';
$_xwb_install['hack_flag']	= array(
	'phps'	=>$_xwb_install['hack_flag_ps'].$_xwb_install['hack_flag_i'].$_xwb_install['hack_flag_p'],
	'phpe'	=>$_xwb_install['hack_flag_pe'].$_xwb_install['hack_flag_i'].$_xwb_install['hack_flag_p'],
	'htmls'	=>$_xwb_install['hack_flag_hs'].$_xwb_install['hack_flag_i'].$_xwb_install['hack_flag_h'],
	'htmle'	=>$_xwb_install['hack_flag_he'].$_xwb_install['hack_flag_i'].$_xwb_install['hack_flag_h'],
	'pc' =>"#\s*".preg_quote($_xwb_install['hack_flag_ps'])."[^\n]+?".preg_quote($_xwb_install['hack_flag_p']).
		   ".+?".preg_quote($_xwb_install['hack_flag_pe'])."[^\n]+?".preg_quote($_xwb_install['hack_flag_p'])."#sm",
	'hc' =>"#\s*".preg_quote($_xwb_install['hack_flag_hs'])."[^\n]+?".preg_quote($_xwb_install['hack_flag_h']).
		   ".+?".preg_quote($_xwb_install['hack_flag_he'])."[^\n]+?".preg_quote($_xwb_install['hack_flag_h'])."#sm"
);
//-----------------------------------------------------------------------

/// 环境的版本检查选项 >=1 <=2
$_xwb_install['site_ver']	= array('1.5','2');
$_xwb_install['php_ver']	= array('4.3','*');

/// 允许的 字符集 大写
$_xwb_install['charset']	= array('GBK', 'UTF8');

/// 路径权限检查配置 pathtype[f,d],path[无/开头]
$_xwb_install['path_chk']	= array();
$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/'. basename(XWB_P_DATA));
//$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/'. basename(XWB_P_DATA). '/backup');
$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/'. basename(XWB_P_DATA).'/temp');
$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/'. basename(XWB_P_DATA).'/api');
$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/cache');
$_xwb_install['path_chk'][] = array('d', XWB_P_DIR_NAME. '/cache/owbset');
$_xwb_install['path_chk'][] = array('f', XWB_P_DIR_NAME.'/app.cfg.php');
$_xwb_install['path_chk'][] = array('f', XWB_P_DIR_NAME.'/set.data.php');
/// 函数依赖检查 注：多选一时 用数组
$_xwb_install['func_chk'] = array();
$_xwb_install['func_chk'][] = 'preg_replace';
$_xwb_install['func_chk'][] = array("iconv","mb_convert_encoding");
$_xwb_install['func_chk'][] = array("hash_hmac","mhash","sha1");

//-----------------------------------------------------------------------
/// 配置文件模板
$_xwb_install['app_cfg_tpl'] = <<<EOT

//请勿使用Windows自带的记事本打开此文件！详情：http://bbs.x.weibo.com/forum/viewthread.php?tid=63
//用户配置文件 安装程序自动生成于 %s
define('XWB_APP_KEY',			'%s');
define('XWB_APP_SECRET_KEY',	'%s');

//是否记录新浪微博API的通讯？是为true，默认为false。
define('XWB_DEV_LOG_ALL_RESPOND'	,false);

//session操作器类型。可选值有'NATIVE'（session原生操作）、'SIMULATOR'（session模拟器操作）
define('XWB_P_SESSION_OPERATOR', 'NATIVE');

//session存储器类型。可选值有'DB'（session存储在db中）、''（即为空，跟随php.ini设置）
//请注意，XWB_P_SESSION_OPERATOR常量设置为'SIMULATOR'时，则必须指定session存储器类型
define('XWB_P_SESSION_STORAGE_TYPE', '');

//本地API
define('XWB_LOCAL_API', '%s');

//是否记录本地API的通讯？是为true，默认为false。
define('XWB_LOCAL_API_LOG', false);

//是否记录远程API的通讯？是为true，默认为false。
define('XWB_REMOTE_API_LOG', false);

//远程API通讯超时限制
define('XWB_REMOTE_API_TIME_VALIDATY', 800);

//http适配器。可选值有'curl'、'fsockopen'（默认）
define('XWB_HTTP_ADAPTER', '%s');

/*（默认不起作用）
手动配置插件所在论坛的完整访问地址，末尾加“/”
设置该值后，还需要设置下面的XWB_S_BASEURL常量
例子：http://www.sina.com.cn/bbs/ ， http://bbs.x.weibo.com/forum/
*/
//define('XWB_S_SITEURL', 'http://www.sina.com.cn/bbs/');

/*（默认不起作用）
手动配置插件所在论坛的域名（即上面常量XWB_S_SITEURL中的域名），末尾不要加“/”
例子：http://www.sina.com.cn ， http://bbs.x.weibo.com
*/
//define('XWB_S_BASEURL', 'http://www.sina.com.cn');


EOT;
//-----------------------------------------------------------------------
/// 要创建的数据表
$_xwb_install['create_table']	= array();
$_xwb_install['create_table']['xwb_bind_thread'] = <<<EOT

CREATE TABLE IF NOT EXISTS `%s` (
	`tid` bigint(20) unsigned NOT NULL default '0',
	`mid` bigint(20) unsigned NOT NULL,
	PRIMARY KEY  (`tid`,`mid`)
) ENGINE=MyISAM;
EOT;

$_xwb_install['create_table']['xwb_bind_info'] = <<<EOT

CREATE TABLE IF NOT EXISTS `%s` (
	`uid` mediumint(8) unsigned NOT NULL default '0',
	`sina_uid` bigint(20) unsigned NOT NULL,
	`token` char(32) NOT NULL,
	`tsecret` char(32) NOT NULL,
	`profile` TEXT NOT NULL,
	PRIMARY KEY  (`uid`),
	UNIQUE KEY `sina_uid` (`sina_uid`)
) ENGINE=MyISAM;

EOT;

$_xwb_install['create_table']['xwb_session'] = <<<EOT

CREATE TABLE IF NOT EXISTS `%s` (
  `sessionid` char(32) NOT NULL default '',
  `lasttime` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  UNIQUE KEY `sessionid` (`sessionid`),
  KEY `lasttime` (`lasttime`)
) ENGINE=MyISAM;

EOT;

//预先要执行的SQL语句
//旧版本在插入mid-tid关系数据时存在mid为0的问题，故进行删除，以防止干扰升级数据表结构
$_xwb_install['prepare_sql']['xwb_bind_thread'] = <<<EOT
DELETE FROM `%s` WHERE `mid` = 0;
EOT;

// 要修改的数据表
$_xwb_install['alter_table']['xwb_bind_thread'] = <<<EOT
ALTER TABLE `%s` ADD `type` ENUM( 'article', 'blog', 'doing', 'share', 'thread' ) NOT NULL DEFAULT 'thread',DROP PRIMARY KEY,ADD PRIMARY KEY ( `mid` ),ADD INDEX ( `tid` , `type` );
EOT;


//-----------------------------------------------------------------------
/// 入口文件模板
$_xwb_install['xwb_php_tpl'] = <<<EOT

if( file_exists( '%s/index.php' ) ){
	require '%s/index.php';
}else{
	exit('CAN NOT RUN THE PLUGIN!');
}

EOT;

?>