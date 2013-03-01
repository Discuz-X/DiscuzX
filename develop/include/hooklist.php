<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: hooklist.php 30392 2012-05-25 07:36:52Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$generalhook = Array(
	'common' => Array(
		'lang' => '全局(common)',
		'extcredits.htm' => Array(
				'spacecp_credit_extra' => Array('return' => 'string')
			),
		'faq.htm' => Array(
				'faq_extra' => Array('return' => 'string')
			),
		'footer.htm' => Array(
				'global_footer' => Array('return' => 'string'),
				'global_footerlink' => Array('return' => 'string')
			),
		'header.htm' => Array(
				'global_cpnav_top' => Array('return' => 'string'),
				'global_cpnav_extra1' => Array('return' => 'string'),
				'global_cpnav_extra2' => Array('return' => 'string'),
				'global_usernav_extra1' => Array('return' => 'string'),
				'global_usernav_extra2' => Array('return' => 'string'),
				'global_usernav_extra3' => Array('return' => 'string'),
				'global_usernav_extra4' => Array('return' => 'string'),
				'global_nav_extra' => Array('return' => 'string'),
				'global_header' => Array('return' => 'string')
			),
		'userabout.htm' => Array(
				'global_userabout_top' => Array('return' => 'array'),
				'userapp_menu_top' => Array('return' => 'string'),
				'userapp_menu_middle' => Array('return' => 'string'),
				'global_userabout_bottom' => Array('return' => 'array')
			)
	),
	'forum' => Array(
		'lang' => '论坛(forum)',
		'collection_all.htm' => Array(
				'collection_index_top' => Array('return' => 'string','version' => 'X2.5'),
				'collection_index_bottom' => Array('return' => 'string','version' => 'X2.5')
			),
		'collection_comment.htm' => Array(
				'collection_nav_extra' => Array('return' => 'string','version' => 'X2.5')
			),
		'collection_index.htm' => Array(
				'collection_index_top' => Array('return' => 'string','version' => 'X2.5'),
				'collection_index_bottom' => Array('return' => 'string','version' => 'X2.5')
			),
		'collection_mycollection.htm' => Array(
				'collection_index_top' => Array('return' => 'string','version' => 'X2.5'),
				'collection_index_bottom' => Array('return' => 'string','version' => 'X2.5')
			),
		'collection_nav.htm' => Array(
				'collection_nav_extra' => Array('return' => 'string','version' => 'X2.5')
			),
		'collection_view.htm' => Array(
				'collection_viewoptions' => Array('return' => 'string','version' => 'X2.5'),
				'collection_view_top' => Array('return' => 'string','version' => 'X2.5'),
				'collection_threadlistbottom' => Array('return' => 'string','version' => 'X2.5'),
				'collection_relatedop' => Array('return' => 'string','version' => 'X2.5'),
				'collection_view_bottom' => Array('return' => 'string','version' => 'X2.5'),
				'collection_side_bottom' => Array('return' => 'string','version' => 'X2.5')
			),
		'discuz.htm' => Array(
				'index_status_extra' => Array('return' => 'string'),
				'index_nav_extra' => Array('return' => 'string'),
				'index_top' => Array('return' => 'string'),
				'index_catlist_top' => Array('return' => 'string'),
				'index_favforum_extra' => Array('return' => 'array'),
				'index_catlist' => Array('return' => 'array'),
				'index_forum_extra' => Array('return' => 'array'),
				'index_middle' => Array('return' => 'string'),
				'index_bottom' => Array('return' => 'string'),
				'index_side_top' => Array('return' => 'string'),
				'index_side_bottom' => Array('return' => 'string')
			),
		'discuzcode.htm' => Array(
				'viewthread_attach_extra' => Array('return' => 'array')
			),
		'editor_menu_forum.htm' => Array(
				'post_image_btn_extra' => Array('return' => 'string'),
				'post_image_tab_extra' => Array('return' => 'string'),
				'post_attach_btn_extra' => Array('return' => 'string'),
				'post_attach_tab_extra' => Array('return' => 'string')
			),
		'forumdisplay.htm' => Array(
				'forumdisplay_leftside_top' => Array('return' => 'string'),
				'forumdisplay_leftside_bottom' => Array('return' => 'string'),
				'forumdisplay_forumaction' => Array('return' => 'string'),
				'forumdisplay_modlink' => Array('return' => 'string'),
				'forumdisplay_top' => Array('return' => 'string'),
				'forumdisplay_middle' => Array('return' => 'string'),
				'forumdisplay_postbutton_top' => Array('return' => 'string'),
				'forumdisplay_threadtype_inner' => Array('return' => 'string'),
				'forumdisplay_filter_extra' => Array('return' => 'string'),
				'forumdisplay_threadtype_extra' => Array('return' => 'string'),
				'forumdisplay_bottom' => Array('return' => 'string'),
				'forumdisplay_side_top' => Array('return' => 'string'),
				'forumdisplay_side_bottom' => Array('return' => 'string')
			),
		'forumdisplay_fastpost.htm' => Array(
				'forumdisplay_fastpost_content' => Array('return' => 'string'),
				'forumdisplay_fastpost_func_extra' => Array('return' => 'string'),
				'forumdisplay_fastpost_ctrl_extra' => Array('return' => 'string'),
				'global_login_text' => Array('return' => 'string'),
				'forumdisplay_fastpost_btn_extra' => Array('return' => 'string'),
				'forumdisplay_fastpost_sync_method' => Array('return' => 'string')
			),
		'forumdisplay_list.htm' => Array(
				'forumdisplay_filter_extra' => Array('return' => 'string'),
				'forumdisplay_thread' => Array('return' => 'array'),
				'forumdisplay_thread_subject' => Array('return' => 'array'),
				'forumdisplay_author' => Array('return' => 'array'),
				'forumdisplay_threadlist_bottom' => Array('return' => 'string'),
				'forumdisplay_postbutton_bottom' => Array('return' => 'string')
			),
		'forumdisplay_sort.htm' => Array(
				'forumdisplay_postbutton_bottom' => Array('return' => 'string','version' => 'X2.5')
			),
		'forumdisplay_subforum.htm' => Array(
				'forumdisplay_subforum_extra' => Array('return' => 'array','version' => 'X2.5')
			),
		'guide.htm' => Array(
				'guide_nav_extra' => Array('return' => 'string'),
				'guide_top' => Array('return' => 'string'),
				'guide_bottom' => Array('return' => 'string')
			),
		'guide_list_row.htm' => Array(
				'forumdisplay_thread' => Array('return' => 'array','version' => 'X2.5')
			),
		'index_navbar.htm' => Array(
				'index_navbar' => Array('return' => 'string')
			),
		'post.htm' => Array(
				'post_top' => Array('return' => 'string'),
				'post_middle' => Array('return' => 'string'),
				'post_btn_extra' => Array('return' => 'string'),
				'post_sync_method' => Array('return' => 'string'),
				'post_bottom' => Array('return' => 'string')
			),
		'post_activity.htm' => Array(
				'post_activity_extra' => Array('return' => 'string')
			),
		'post_debate.htm' => Array(
				'post_debate_extra' => Array('return' => 'string')),
		'post_editor_body.htm' => Array(
				'post_editorctrl_right' => Array('return' => 'string'),
				'post_editorctrl_left' => Array('return' => 'string'),
				'post_editorctrl_top' => Array('return' => 'string'),
				'post_editorctrl_bottom' => Array('return' => 'string')
			),
		'post_editor_option.htm' => Array(
				'post_side_top' => Array('return' => 'string'),
				'post_side_bottom' => Array('return' => 'string')
			),
		'post_infloat.htm' => Array(
				'post_infloat_top' => Array('return' => 'string'),
				'post_infloat_middle' => Array('return' => 'string'),
				'post_infloat_btn_extra' => Array('return' => 'string')
			),
		'post_poll.htm' => Array(
				'post_poll_extra' => Array('return' => 'string')
			),
		'post_reward.htm' => Array(
				'post_reward_extra' => Array('return' => 'string')
			),
		'post_trade.htm' => Array(
				'post_trade_extra' => Array('return' => 'string')
			),
		'topicadmin_modlayer.htm' => Array(
				'forumdisplay_modlayer' => Array('return' => 'string'),
				'modcp_modlayer' => Array('return' => 'string')
			),
		'trade_info.htm' => Array(
				'viewthread_tradeinfo_extra' => Array('return' => 'string')
			),
		'viewthread.htm' => Array(
				'viewthread_top' => Array('return' => 'string'),
				'viewthread_postbutton_top' => Array('return' => 'string'),
				'viewthread_modoption' => Array('return' => 'string'),
				'viewthread_beginline' => Array('return' => 'string'),
				'viewthread_title_extra' => Array('return' => 'string'),
				'viewthread_title_row' => Array('return' => 'string'),
				'viewthread_middle' => Array('return' => 'string'),
				'viewthread_bottom' => Array('return' => 'string')
			),
		'viewthread_activity.htm' => Array(
				'viewthread_activity_extra1' => Array('return' => 'string'),
				'viewthread_activity_extra2' => Array('return' => 'string')
			),
		'viewthread_fastpost.htm' => Array(
				'viewthread_fastpost_side' => Array('return' => 'string'),
				'viewthread_fastpost_content' => Array('return' => 'string'),
				'viewthread_fastpost_func_extra' => Array('return' => 'string'),
				'viewthread_fastpost_ctrl_extra' => Array('return' => 'string'),
				'global_login_text' => Array('return' => 'string'),
				'viewthread_fastpost_btn_extra' => Array('return' => 'string')
			),
		'viewthread_from_node.htm' => Array(
				'viewthread_postheader' => Array('return' => 'array'),
				'viewthread_endline' => Array('return' => 'array')
			),
		'viewthread_node.htm' => Array(
				'viewthread_profileside' => Array('return' => 'array'),
				'viewthread_imicons' => Array('return' => 'array'),
				'viewthread_magic_user' => Array('return' => 'array'),
				'viewthread_avatar' => Array('return' => 'array'),
				'viewthread_sidetop' => Array('return' => 'array'),
				'viewthread_sidebottom' => Array('return' => 'array'),
				'viewthread_postheader' => Array('return' => 'array'),
				'viewthread_modaction' => Array('return' => 'string'),
				'viewthread_share_method' => Array('return' => 'string'),
				'viewthread_useraction' => Array('return' => 'string'),
				'viewthread_postsightmlafter' => Array('return' => 'array'),
				'viewthread_postfooter' => Array('return' => 'array'),
				'viewthread_postaction' => Array('return' => 'array'),
				'viewthread_magic_thread' => Array('return' => 'string'),
				'viewthread_magic_post' => Array('return' => 'array'),
				'viewthread_endline' => Array('return' => 'array')
			),
		'viewthread_node_body.htm' => Array(
				'viewthread_posttop' => Array('return' => 'array'),
				'global_login_text' => Array('return' => 'string'),
				'viewthread_postbottom' => Array('return' => 'array')
			),
		'viewthread_poll.htm' => Array(
				'viewthread_poll_top' => Array('return' => 'string'),
				'viewthread_poll_bottom' => Array('return' => 'string')
			),
		'viewthread_portal.htm' => Array(
				'viewthread_useraction_prefix' => Array('return' => 'string'),
				'viewthread_useraction' => Array('return' => 'string'),
				'viewthread_side_bottom' => Array('return' => 'string')
			),
		'viewthread_trade.htm' => Array(
				'viewthread_trade_extra' => Array('return' => 'array')
			)
	),
	'group' => Array(
		'lang' => '群组(group)',
		'group.htm' => Array(
				'group_navlink' => Array('return' => 'string'),
				'forumdisplay_navlink' => Array('return' => 'string'),
				'group_top' => Array('return' => 'string'),
				'forumdisplay_top' => Array('return' => 'string'),
				'group_nav_extra' => Array('return' => 'string'),
				'forumdisplay_nav_extra' => Array('return' => 'string'),
				'group_bottom' => Array('return' => 'string'),
				'forumdisplay_bottom' => Array('return' => 'string'),
				'group_side_bottom' => Array('return' => 'string'),
				'forumdisplay_side_bottom' => Array('return' => 'string')
			),
		'group_list.htm' => Array(
				'forumdisplay_postbutton_top' => Array('return' => 'string'),
				'forumdisplay_filter_extra' => Array('return' => 'string'),
				'forumdisplay_thread' => Array('return' => 'array'),
				'forumdisplay_postbutton_bottom' => Array('return' => 'string')
			),
		'group_my.htm' => Array(
				'my_header' => Array('return' => 'string'),
				'my_bottom' => Array('return' => 'string'),
				'my_side_top' => Array('return' => 'string'),
				'my_side_bottom' => Array('return' => 'string')
			),
		'group_right.htm' => Array(
				'group_index_side' => Array('return' => 'string'),
				'group_side_top' => Array('return' => 'string'),
				'forumdisplay_side_top' => Array('return' => 'string')
			),
		'index.htm' => Array(
				'index_header' => Array('return' => 'string'),
				'index_top' => Array('return' => 'string'),
				'index_bottom' => Array('return' => 'string'),
				'index_side_top' => Array('return' => 'string'),
				'index_side_bottom' => Array('return' => 'string')
			),
		'type.htm' => Array(
				'index_top' => Array('return' => 'string'),
				'index_grouplist' => Array('return' => 'array'),
				'index_bottom' => Array('return' => 'string'),
				'index_side_top' => Array('return' => 'string'),
				'index_side_bottom' => Array('return' => 'string')
			)
	),
	'home' => Array(
		'lang' => '家园(home)',
		'follow_feed.htm' => Array(
				'follow_nav_extra' => Array('return' => 'string','version' => 'X2.5'),
				'follow_top' => Array('return' => 'string','version' => 'X2.5')
			),
		'spacecp_avatar.htm' => Array(
				'spacecp_avatar_top' => Array('return' => 'string'),
				'spacecp_avatar_bottom' => Array('return' => 'string')
			),
		'spacecp_blog.htm' => Array(
				'spacecp_blog_top' => Array('return' => 'string'),
				'spacecp_blog_middle' => Array('return' => 'string'),
				'spacecp_blog_bottom' => Array('return' => 'string')
			),
		'spacecp_credit_base.htm' => Array(
				'spacecp_credit_top' => Array('return' => 'string'),
				'spacecp_credit_extra' => Array('return' => 'string'),
				'spacecp_credit_bottom' => Array('return' => 'string')
			),
		'spacecp_credit_log.htm' => Array(
				'spacecp_credit_top' => Array('return' => 'string'),
				'spacecp_credit_bottom' => Array('return' => 'string')
			),
		'spacecp_privacy.htm' => Array(
				'spacecp_privacy_top' => Array('return' => 'string'),
				'spacecp_privacy_base_extra' => Array('return' => 'string'),
				'spacecp_privacy_feed_extra' => Array('return' => 'string'),
				'spacecp_privacy_bottom' => Array('return' => 'string')
			),
		'spacecp_profile.htm' => Array(
				'spacecp_profile_top' => Array('return' => 'string'),
				'spacecp_profile_extra' => Array('return' => 'string'),
				'spacecp_profile_bottom' => Array('return' => 'string')
			),
		'spacecp_promotion.htm' => Array(
				'spacecp_promotion_top' => Array('return' => 'string'),
				'spacecp_promotion_bottom' => Array('return' => 'string')
			),
		'spacecp_usergroup.htm' => Array(
				'spacecp_usergroup_top' => Array('return' => 'string'),
				'spacecp_usergroup_bottom' => Array('return' => 'string')
			),
		'space_album_pic.htm' => Array(
				'space_album_pic_top' => Array('return' => 'string'),
				'space_album_pic_op_extra' => Array('return' => 'string'),
				'space_album_pic_bottom' => Array('return' => 'string'),
				'space_album_pic_face_extra' => Array('return' => 'string')
			),
		'space_album_view.htm' => Array(
				'space_album_op_extra' => Array('return' => 'string')
			),
		'space_blog_list.htm' => Array(
				'space_blog_list_status' => Array('return' => 'array')
			),
		'space_blog_view.htm' => Array(
				'space_blog_title' => Array('return' => 'string'),
				'space_blog_share_method' => Array('return' => 'string'),
				'space_blog_op_extra' => Array('return' => 'string'),
				'space_blog_face_extra' => Array('return' => 'string')
			),
		'space_card.htm' => Array(
				'space_card_top' => Array('return' => 'string'),
				'space_card_baseinfo_middle' => Array('return' => 'string'),
				'space_card_baseinfo_bottom' => Array('return' => 'string'),
				'space_card_option' => Array('return' => 'string'),
				'space_card_magic_user' => Array('return' => 'string'),
				'space_card_bottom' => Array('return' => 'string')
			),
		'space_comment_li.htm' => Array(
				'space_blog_comment_op' => Array('return' => 'array'),
				'space_blog_comment_bottom' => Array('return' => 'string')
			),
		'space_doing.htm' => Array(
				'space_doing_top' => Array('return' => 'string'),
				'space_doing_bottom' => Array('return' => 'string')
			),
		'space_favorite.htm' => Array(

				'space_favorite_nav_extra' => Array('return' => 'string','version' => 'X2.5')
			),
		'space_friend.htm' => Array(
				'space_interaction_extra' => Array('return' => 'string')
			),
		'space_header.htm' => Array(
				'global_usernav_extra1' => Array('return' => 'string'),
				'global_usernav_extra2' => Array('return' => 'string')
			),
		'space_home.htm' => Array(
				'space_home_side_top' => Array('return' => 'string'),
				'space_home_side_bottom' => Array('return' => 'string'),
				'space_home_top' => Array('return' => 'string'),
				'space_home_navlink' => Array('return' => 'string'),
				'space_home_bottom' => Array('return' => 'string')
			),
		'space_magic.htm' => Array(
				'magic_nav_extra' => Array('return' => 'string','version' => 'X2.5')
			),
		'space_medal.htm' => Array(
				'medal_nav_extra' => Array('return' => 'string','version' => 'X2.5')
			),
		'space_menu.htm' => Array(
				'space_menu_extra' => Array('return' => 'string')
			),
		'space_profile_body.htm' => Array(
				'space_profile_baseinfo_top' => Array('return' => 'string'),
				'follow_profile_baseinfo_top' => Array('return' => 'string'),
				'space_profile_baseinfo_middle' => Array('return' => 'string'),
				'follow_profile_baseinfo_middle' => Array('return' => 'string'),
				'space_profile_baseinfo_bottom' => Array('return' => 'string'),
				'follow_profile_baseinfo_bottom' => Array('return' => 'string'),
				'space_profile_extrainfo' => Array('return' => 'string'),
				'follow_profile_extrainfo' => Array('return' => 'string')
			),
		'space_share_li.htm' => Array(
				'space_share_comment_op' => Array('return' => 'array')
			),
		'space_status.htm' => Array(
				'space_home_doing_sync_method' => Array('return' => 'string')
			),
		'space_wall.htm' => Array(
				'space_wall_face_extra' => Array('return' => 'string')
			)
	),
	'member' => Array(
		'lang' => '注册/登录(member)',
		'login.htm' => Array(
				'logging_side_top' => Array('return' => 'string'),
				'logging_top' => Array('return' => 'string'),
				'logging_input' => Array('return' => 'string'),
				'logging_method' => Array('return' => 'string')
			),
		'login_simple.htm' => Array(
				'global_login_extra' => Array('return' => 'string')
			),
		'register.htm' => Array(
				'register_side_top' => Array('return' => 'string'),
				'register_top' => Array('return' => 'string'),
				'register_input' => Array('return' => 'string'),
				'register_logging_method' => Array('return' => 'string'),
				'register_bottom' => Array('return' => 'string')
			)
	),
	'portal' => Array(
		'lang' => '门户(portal)',
		'portalcp_article.htm' => Array(
				'portalcp_top' => Array('return' => 'string'),
				'portalcp_extend' => Array('return' => 'string'),
				'portalcp_middle' => Array('return' => 'string'),
				'portalcp_bottom' => Array('return' => 'string')
			),
		'view.htm' => Array(
				'view_article_top' => Array('return' => 'string'),
				'view_article_subtitle' => Array('return' => 'string'),
				'view_article_summary' => Array('return' => 'string'),
				'view_article_content' => Array('return' => 'string'),
				'view_share_method' => Array('return' => 'string'),
				'view_article_op_extra' => Array('return' => 'string'),
				'view_article_side_top' => Array('return' => 'string'),
				'view_article_side_bottom' => Array('return' => 'string')
			)
	),
	'ranklist' => Array(
		'lang' => '排行榜(ranklist)',
		'side_left.htm' => Array(
				'ranklist_nav_extra' => Array('return' => 'string')
			)
	),
	'search' => Array(
		'lang' => '搜索(search)',
		'album.htm' => Array(
				'album_top' => Array('return' => 'string'),
				'album_bottom' => Array('return' => 'string')
			),
		'blog.htm' => Array(
				'blog_top' => Array('return' => 'string'),
				'blog_bottom' => Array('return' => 'string')
			),
		'footer.htm' => Array(
				'global_footer' => Array('return' => 'string'),
				'global_footerlink' => Array('return' => 'string')
			),
		'forum.htm' => Array(
				'forum_top' => Array('return' => 'string'),
				'forum_bottom' => Array('return' => 'string')
			),
		'group.htm' => Array(
				'group_top' => Array('return' => 'string'),
				'group_bottom' => Array('return' => 'string')
			),
		'header.htm' => Array(
				'global_usernav_extra1' => Array('return' => 'string'),
				'global_usernav_extra2' => Array('return' => 'string')
			),
		'portal.htm' => Array(
				'portal_top' => Array('return' => 'string'),
				'portal_bottom' => Array('return' => 'string')
			)
	),
	'userapp' => Array(
		'lang' => '应用(userapp)',
		'userapp_app.htm' => Array(
				'userapp_app_top' => Array('return' => 'string'),
				'userapp_app_bottom' => Array('return' => 'string')
			),
		'userapp_index.htm' => Array(
				'userapp_index_top' => Array('return' => 'string'),
				'userapp_index_bottom' => Array('return' => 'string')
			),
		'userapp_menu_list.htm' => Array(
				'userapp_menu_top' => Array('return' => 'string'),
				'userapp_menu_middle' => Array('return' => 'string'),
				'userapp_menu_bottom' => Array('return' => 'string')
			)
	)
);
$mobilehook = array(
	'common' => Array(
		'lang' => '手机全局(mobile/common)',
		'footer.htm' => Array(
				'global_footer_mobile' => Array('return' => 'string')
			),
		'header.htm' => Array(
				'global_header_mobile' => Array('return' => 'string')
			)
	),
	'forum' => Array(
		'lang' => '手机论坛(mobile/forum)',
		'discuz.htm' => Array(
				'index_top_mobile' => Array('return' => 'string'),
				'index_middle_mobile' => Array('return' => 'string'),
				'index_bottom_mobile' => Array('return' => 'string')
			),
		'forumdisplay.htm' => Array(
				'forumdisplay_top_mobile' => Array('return' => 'string'),
				'forumdisplay_thread_mobile' => Array('return' => 'array'),
				'forumdisplay_bottom_mobile' => Array('return' => 'string')
			),
		'viewthread.htm' => Array(
				'viewthread_top_mobile' => Array('return' => 'string'),
				'viewthread_posttop_mobile' => Array('return' => 'array'),
				'viewthread_postbottom_mobile' => Array('return' => 'array'),
				'viewthread_bottom_mobile' => Array('return' => 'string')
			)
	)
);
