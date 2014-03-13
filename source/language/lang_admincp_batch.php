<?PHP (defined('IN_DISCUZ') && defined('IN_ADMINCP')) || die('Access Denied');
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_batchr.php 29721 2012-04-26 07:01:08Z zhengqingpeng $
 */

$extend_lang = array(
    'menu_batch_publishone' => '批量合并发布',
    'menu_batch_publisheach' => '批量单个发布',
    'batch_publishone_title' => '发布的标题',
    'menu_sitemanager_links' => '链接资源',
    'menu_sitemanager_phpmyadmin' => 'phpMyAdmin',
    'menu_sitemanager_hooker'=> '嵌入点检查',
);

$GLOBALS['admincp_actions_normal'][] = 'batch';