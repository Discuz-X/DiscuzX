<?php
/*
 * @version $Id: gbk.php 743 2011-05-16 09:39:01Z yaoying $
 */

if(XWB_S_VERSION >= 2){
	$_pluginid = 'sina_xweibo_x2';
}else{
	$_pluginid = 'sina_xweibo';
}

$_LANG = array(
	'xwb_bind_sina_set' => '新浪微博绑定设置',
	'xwb_bind_sina_set_tips' => '绑定新浪微博帐号，你的发帖可同时发布到新浪微博。',
	'xwb_bind_sina_set_btn' => '绑定新浪微博',
	'xwb_off_bind_sina' => '你还未绑定新浪微博',
	'xwb_have_bind_privilege' => '绑定后，你将获得以下特权：',
	'xwb_have_bind_privilege_1' => '&middot;可使用新浪微博帐号登录$bbname',
	'xwb_have_bind_privilege_2' => '&middot;在&nbsp;' . XWB_S_NAME . '&nbsp;发表的内容可同时发表至新浪微博',
	'xwb_have_bind_privilege_3' => '&middot;在&nbsp;' . XWB_S_NAME . '&nbsp;浏览并使用新浪微博',
	'xwb_bind_now' => '立即绑定微博',
	'xwb_off_mblog_account' => '还没有新浪微博帐号？',
	'xwb_30sec_register' => '30秒完成免费注册',

	'xwb_new_is_auto2sinamblog' => '新发帖是否自动发到新浪微博',
	'xwb_save_setting' => '保存设置',
	'xwb_del_bind' => '解除绑定',
	'xwb_have_bind_sinamblog' => '已绑定新浪微博',
	'xwb_off_bind_sinamblog' => '未绑定新浪微博',
	'xwb_sina_nick' => '昵称',
	'xwb_del_bind' => '解除绑定',
	'xwb_del_bind_txt' => '解除绑定后，您在&nbsp;论坛&nbsp;发表的内容将不再同步到新浪微博',
	'xwb_process_binding' => '进入绑定流程中，请稍候.....<br />若看不到绑定提示窗口，<a href="javascript:XWBcontrol.%s()">请点击这里启动</a>。<br />如果仍无法启动，请检查是否禁止了Javascript，然后回到首页重新操作。',
	'xwb_have_sinamblog' => '已有新浪微博帐号，可直接登录。',
	'xwb_login_by_sinamblog_account' => '用微博帐号登录',
	'xwb_forward' => '转发到微博',
	'xwb_sycn_to_sina' => '同时发表至新浪微博',
	'xwb_sycn_open' => '开通此功能（将打开新窗口）',
	'xwb_topic_has_sycn_to' => '已同步至',
	'xwb_topic_has_sycn_to_new' => '该贴已经同步到',
    'xwb_topic_has_sycn_to_new_end' => '的微博',
	'xwb_reply_from' => '来自',
	'xwb_admin_settings' => '微博设置',
	'xwb_sina_mblog' => '新浪微博',
	'xwb_use_sina_signer'=>'使用新浪微博签名',
	'xwb_his_sina_mblog' => '%s 的新浪微博',
	'xwb_bind_my_sina_mblog' => '绑定我的新浪微博',

	'xwb_system_error' => '系统内部错误，请稍后重试',
	'xwb_user_not_exists' => '用户不存在',
	'xwb_target_weibo_not_exist' => '微博已删除',
	'xwb_weibo_id_null' => '获取微博ID失败',
	'xwb_app_key_error' => '来源APP_KEY错误',
	'xwb_request_reach_api_maxium' => '请求次数超过API限制，请稍后再试',
	'xwb_comment_reach_api_maxium' => '评论次数超过API限制，请稍后再试',
	'xwb_update_reach_api_maxium' => '发布微博次数超过API限制，请稍后再试',
	'xwb_access_resource_api_denied' => '访问API资源被拒绝，原因：该资源需要appkey拥有更高级的授权',
	'xwb_access_resource_api_denied' => '访问API资源被拒绝，原因：该资源需要appkey拥有更高级的授权',
	'xwb_token_error' => '你可能已经取消该应用站点的授权。请先解除当前绑定，然后重新绑定，以便自动重新建立授权',


	'xwb_register_pwd_notice_pm_subject' => "欢迎您的注册！",
	'xwb_register_pwd_notice_pm_msg' => "尊敬的%s：\n您的帐号已经绑定到新浪微博帐号，下次您可点击新浪微博登录的图片进行登录。\n同时您也可以在本站使用用户名登录，密码为：%s\n为了您的帐号安全，请尽快删除本消息。",

	'xwb_site_user_not_exist' => '所绑定的论坛用户不存在或者被管理员删除。<br />请重新用微博帐号登录到本论坛注册，或者联系网站管理员。',

	'xwb_blog_no_subject' => '未命名日志',
	'xwb_blog_publish_message' => '发表了一篇新日志：%s',

	'xwb_article_no_subject' => '未命名文章',
	'xwb_article_publish_message' => '发表了一篇新文章：%s',

	'xwb_want_to_share2weibo' => '<a href="home.php?mod=spacecp&ac=plugin&id='. $_pluginid. ':home_binding">我要把分享同步到新浪微博</a>',
	'xwb_allow_share2weibo' => '您发布的分享会同步到您的新浪微博。<a href="home.php?mod=spacecp&ac=plugin&id='. $_pluginid. ':home_binding">不想同步？</a>',

	'xwb_want_to_doing2weibo' => '<a href="home.php?mod=spacecp&ac=plugin&id='. $_pluginid. ':home_binding">我要把记录同步到新浪微博</a>',
	'xwb_allow_doing2weibo' => '您发布的记录会同步到您的新浪微博。<a href="home.php?mod=spacecp&ac=plugin&id='. $_pluginid. ':home_binding">不想同步？</a>',

	'xwb_reply_from_2' => '来自 %s 的新浪微博',

	'xwb_weibo' => '微博',

	);

