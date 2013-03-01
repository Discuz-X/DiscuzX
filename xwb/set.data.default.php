<?php
/**
 * 插件设置默认配置文件和参数
 * 安装程序将根据此文件，自动创建或者合并已有的插件设置文件
 * @version $Id: set.data.default.php 791 2011-05-26 02:42:49Z yaoying $
 */
$__XWB_SET=array (
  /* 同步成功后，在同步的主题贴/日志/门户文章中加入同步的微薄地址 */
  'wb_addr_display' => 0,
  /* 个人资料页是否显示微薄秀 */
  'is_wbx_display' => 0,
  /* 微薄秀：高度（DZX固定180） */
  'wbx_width' => 180,
  /* 微薄秀：高度 */
  'wbx_height' => 500,
  /* 微薄秀：外观颜色代码 */
  'wbx_style' => 4,
  /* 微薄秀：粉丝行数 */
  'wbx_line' => 2,
  /* 微薄秀：是否显示标题 */
  'wbx_is_title' => 1,
  /* 微薄秀：是否显示微薄 */
  'wbx_is_blog' => 1,
  /* 微薄秀：是否显示粉丝 */
  'wbx_is_fans' => 1,
  /* 微薄秀：预览URL*/
  'wbx_url' => 'http://service.t.sina.com.cn/widget/WeiboShow.php?uname=xweibo%E6%B5%8B%E8%AF%95%E5%B8%90%E5%8F%B7&width=180&height=500&skin=4&isTitle=1&isWeibo=1&isFans=1&fansRow=2&__noCache=1293085731965',
  /* 是否允许使用绑定和登录功能（核心功能，不允许关闭） */
  'is_account_binding' => 1,
  /* 同步论坛主题帖子到微薄 */
  'is_synctopic_toweibo' => 1,
  /* 同步回复到微薄（如果主题/帖子/记录/分享等已经同步到微薄的话） */
  'is_syncreply_toweibo' => 1,
  /* 同步论坛记录到微薄 */
  'is_syncdoing_toweibo' => 1,
  /* 同步日志到微薄 */
  'is_syncblog_toweibo' => 1,
  /* 同步分享到微薄 */
  'is_syncshare_toweibo' => 1,
  /* 同步门户文章到微薄 */
  'is_syncarticle_toweibo' => 1,
  /* 显示微薄转发按钮 */
  'is_rebutton_display' => 1,
  /* 转发到微博时关联官方帐号 */
  'is_rebutton_relateUid_assoc' => 1,
  /* 看帖页面是否显示绑定标识 */
  'is_tips_display' => 1,
  /* 是否允许用户使用签名档 */
  'is_signature_display' => 1,
  /* 使用微薄注册时是否同步头像 */
  'is_sync_face' => 1,
  /* 将图片也同步到微薄 */
  'is_upload_image' => 1,
  /* 新增微博勋章更新时间间隔 2010-09-25 */
  'wbx_medal_update_time' => 1800,
  /* 新增微博转发间隔 2010-10-08 */
  'wbx_share_time' => 15,
  /* 是否在论坛首页显示登录按钮 */
  'is_display_login_button' => 1,
  /* 是否在快速发表处显示登录按钮 */
  'is_display_login_button_in_fastpost_box' => 1,
  /* （仅用于X2）在哪里显示登录按钮，目前可用为global_login_extra/global_cpnav_extra2/global_header */
  'display_login_button_hookname' => 'global_login_extra',
  /* 发送到微薄的链接中带有fromuid=x，从而纳入dzx的推广积分体系 */
  'link_visit_promotion' => 0,
  /* 新增绑定页活跃用户数据更新间隔 2010-12-20 */
  'wbx_huwb_update_time' => 24,
   /* 评论回推：是否开启此功能？ */
  'is_pushback_open' => 0,
  /* 评论回推通讯授权码 */
  'pushback_authkey' => '',
  /* 评论回推：回推到已经同步到微薄的主题 */
  'pushback_to_thread' => 0,
  /* 评论回推：回推到已经同步到微薄的日志 */
  'pushback_to_blog' => 0,
  /* 评论回推：回推到已经同步到微薄的记录 */
  'pushback_to_doing' => 0,
  /* 评论回推：回推到已经同步到微薄的门户文章 */
  'pushback_to_article' => 0,
  /* 评论回推：回推到已经同步到微薄的分享 */
  'pushback_to_share' => 0,
  /* 评论回推：从回推服务器获取的最后一个回推id信息数值 */
  'pushback_fromid' => 0,
  /* 已登录并在未绑定状态下，在页面的用户信息右上角，显示绑定新浪微博按钮 */
  'bind_btn_usernav' => 1,
  /* 评论回推：虚拟用户名 */
  'pushback_username' => '',
  /* 评论回推：虚拟用户的uid */
  'pushback_uid' => 0,
  /* 与Xweibo标准版的连接开关 */
  'switch_to_xweibo' => 0,
  /* Xweibo标准版的Api地址*/
  'url_to_xweibo' => '',
  /* 密钥 */
  'encrypt_key' => XWB_APP_SECRET_KEY,
  /* Xweibo标准版的根地址*/
  'baseurl_to_xweibo' => '',
  /* 是否在用户资料卡显示绑定信息 */
  'space_card_weiboinfo' => 1,
  /* （仅用于X2）是否在论坛首页显示官方微博和关注页面 */
  'display_ow_in_forum_index' => 0,
);
?>