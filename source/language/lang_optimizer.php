<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_optimizer.php 32960 2013-03-28 04:36:15Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'optimizer_dbbackup_advice' => '三个月没有进行数据备份了,建议立即备份数据',
	'optimizer_dbbackup_lastback' => '上次数据备份于',
	'optimizer_dbbackup_clean_safe' => '没有检测到数据库备份文件，无安全问题',
	'optimizer_dbbackup_clean_delete' => '检测到 {filecount} 个数据备份文件(目录: ./data/backup_xxx)，<br>请尽快手工复制到安全位置备份，然后删除这些文件',
	'optimizer_filecheck_advice' => '三个月没有进行文件校验了,建议立即进行校验',
	'optimizer_filecheck_lastcheck' => '上次文件校验于',
	'optimizer_log_clean' => '有 {count} 个日志表可以清理优化',
	'optimizer_log_not_found' => '未发现可清理的日志表',
	'optimizer_patch_have' => '您有 {patchnum} 个，请尽快更新',
	'optimizer_patch_check_safe' => '检测安全',
	'optimizer_plugin_new_plugin' => '您有 {newversion} 款应用有可用更新',
	'optimizer_plugin_no_upgrade' => '不需要应用更新',
	'optimizer_post_need_split' => '帖子表及分表有 {count} 个需要优化',
	'optimizer_post_not_need' => '不需要优化',
	'optimizer_seo_advice' => '建议您完善SEO设置',
	'optimizer_seo_no_need' => '发现已经完善了seo设置',
	'optimizer_setting_cache_index' => '缓存论坛首页',
	'optimizer_setting_cache_index_desc' => '开启此功能可减轻服务器负载',
	'optimizer_setting_cache_optimize_desc' => '设置缓存时间为900秒',
	'optimizer_setting_cache_post' => '缓存帖子',
	'optimizer_setting_cache_post_desc' => '开启此功能可减轻服务器负载',
	'optimizer_setting_cache_post_optimize_desc' => '设置缓存时间为900秒',
	'optimizer_setting_optimizeviews' => '优化更新主题浏览量',
	'optimizer_setting_optimizeviews_desc' => '开启此功能可减轻更新主题浏览量时对服务器产生的负载',
	'optimizer_setting_optimizeviews_optimize_desc' => '开启此功能',
	'optimizer_setting_delayviewcount' => '附件下载量延迟更新',
	'optimizer_setting_delayviewcount_desc' => '延迟更新附件的浏览量，可明显降低访问量很大的站点的服务器负担',
	'optimizer_setting_delayviewcount_optimize_desc' => '开启此功能',
	'optimizer_setting_preventrefresh' => '查看数开启防刷新',
	'optimizer_setting_preventrefresh_desc' => '开启防刷新，可明显降低服务器压力',
	'optimizer_setting_preventrefresh_optimize_desc' => '开启此功能',
	'optimizer_setting_nocacheheaders' => '禁止浏览器缓冲',
	'optimizer_setting_nocacheheaders_desc' => '可用于解决极个别浏览器内容刷新不正常的问题，本功能会加重服务器负担',
	'optimizer_setting_nocacheheaders_optimize_desc' => '关闭此功能',
	'optimizer_setting_jspath' => 'JS 文件缓存',
	'optimizer_setting_jspath_desc' => '当脚本为缓存目录时，系统会将默认目录中的 *.js 文件进行压缩然后保存到缓存目录以提高读取速度',
	'optimizer_setting_jspath_optimize_desc' => '修改js路径到缓存目录',
	'optimizer_setting_lazyload' => '图片延时加载',
	'optimizer_setting_lazyload_desc' => '页面中的图片在浏览器的当前窗口时再加载，可明显降低访问量很大的站点的服务器负担',
	'optimizer_setting_lazyload_optimize_desc' => '开启此功能',
	'optimizer_setting_sessionclose' => '关闭session机制',
	'optimizer_setting_sessionclose_desc' => '关闭session机制以后，可明显降低站点的服务器负担，建议在线用户数超过2万时开启本功能<br>注意：游客数和用户的在线时长将不再进行统计，论坛首页和版块列表页面的在线用户列表功能将不可用',
	'optimizer_setting_sessionclose_optimize_desc' => '开启此功能',
	'optimizer_setting_need_optimizer' => '有 {count} 个设置项可以优化',
	'optimizer_setting_no_need' => '设置项无需优化',
	'optimizer_thread_need_optimizer' => '需要优化您的主题表了',
	'optimizer_thread_no_need' => '不需要优化',
	'optimizer_upgrade_need_optimizer' => '有新版本，及时更新到最新版本',
	'optimizer_upgrade_no_need' => '已经是最新版',
	'optimizer_setting_rewriteguest' => 'Rewrite仅针对游客',
	'optimizer_setting_rewriteguest_desc' => '开启此项，则 Rewrite功能只对游客和搜索引擎有效，可减轻服务器负担',
	'optimizer_setting_rewriteguest_optimize_desc' => '开启此功能',
);
?>