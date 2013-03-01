<?php 
/**
 * 附属站点函数操作集合。
 * 从site.env.php分离而来
 * 
 * @since 2010-07-22
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @author  yaoying <yaoying@staff.sina.com.cn>
 * @version $Id: xwbSite.inc.php 435 2010-12-22 02:20:18Z yaoying $
 */

if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}


/// 在附属站点中登录
function xwb_setSiteUserLogin($uid)
{
    global $_G;
    if (empty($uid)) return false;

    $db = XWB_plugin::getDB();

    //登录
    $member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='" . $uid . "'");
	if( ! $member ) {
		return false;
	}
    setloginstatus($member, time() + 60*60*24 ? 2592000 : 0);
    DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='$uid'");

    include_once libfile('function/stat');
    updatestat('login');
    updatecreditbyaction('daylogin', $uid);
    checkusergroup($uid);
    return true;
}