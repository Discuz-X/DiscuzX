<?php
/**
 * 本文件为新浪微博插件和Discuz! X的对接文件
 * 类型为符合Discuz!X插件规范的hook
 * 本文件For Discuz! X2，代码部分重写
 * @author yaoying
 * @author junxiong
 * @since 2011-05-13
 * @version $Id: hook.class.php 848 2011-06-22 07:09:17Z yaoying $
 */

/**
 * 总钩子类
 *
 */
class plugin_sina_xweibo_x2{
	
	//在论坛目录下的xwb插件目录名称
	var $xwb_p_rootname = 'xwb';
	
	//插件名称
	var $pluginid = 'sina_xweibo_x2';
	
	//登录按钮显示了么？
	var $_loginbuttonDisplayed = false;
	
	/**
	 * 在footer那里输出js脚本
	 * 只有插件启动了才能运行
	 * @return string
	 */
	function global_footer(){
		global $_G;
		
		$return = $this->_get_unread_global_footer_content();
		if( true != $this->_start_xweibo( $this->_isModuleAllowXweiboRun() ) ){
			//$return .= '<div class="wp cl"><div class="notice"><font color="red">没有输出新浪微博插件Footer-Javascript。</font></div></div>';
			return $return;
		}
		
		$return .= $this->_get_global_footer_content();
		//$return .= '<div class="wp cl"><div class="notice">已经输出新浪微博插件Footer-Javascript。</div></div>';
		
		return $return;
		
	}
	
	/**
	 * 获取footer脚本内容
	 */
	function _get_global_footer_content(){
		global $_G;
		$return = '';
		include dirname(__FILE__).'/template/global_footer.htm';
		return $return;
	}
	
	/**
	 * 获取footer的未读消息脚本内容
	 * 仅供{@link plugin_sina_xweibo_x2::global_footer()}使用
	 */
	function _get_unread_global_footer_content(){
		global $_G;
		if(!$_G['uid'] || !core_sina_xweibo_x2::pCfg('switch_to_xweibo')){
			return '';
		}
		$std_url = core_sina_xweibo_x2::pCfg('baseurl_to_xweibo');
		include template($this->pluginid. ':global_footer_unread');
		return $return;
	}
	
	/**
	 * 在没有登录的情况下，在header那里输出登录微博的连接
	 * @uses plugin_sina_xweibo_x2::_get_header_login_button()
	 * @return string
	 */
	function global_header(){
		return $this->_get_header_login_button('global_header');
	}
	
	/**
	 * 在没有登录的情况下，在页面快速登录左边输出登录微博的连接
	 * @uses plugin_sina_xweibo_x2::_get_header_login_button()
	 * @return string
	 */
	function global_login_extra(){
		return $this->_get_header_login_button('global_login_extra');
	}
	
	/**
	 * 在没有登录的情况下，在页面右上角输出登录微博的连接
	 * @uses plugin_sina_xweibo_x2::_get_header_login_button()
	 * @return string
	 */
	function global_cpnav_extra2(){
		return $this->_get_header_login_button('global_cpnav_extra2');
	}
	
	/**
	 * 获取header登录按钮（三个地方选择其一）
	 * @param string $hookname
	 * @return string
	 */
	function _get_header_login_button($hookname){
		global $_G;
		if($_G['uid'] || (isset($_G['gp_mod']) && in_array($_G['gp_mod'], array('logging', 'register', 'connect')) && $_G['setting']['regstatus'])){
			return '';
		}
		
		if(!core_sina_xweibo_x2::pCfg('is_display_login_button')){
			$this->_loginbuttonDisplayed = true;
			return '';
		}
		$setHookname = core_sina_xweibo_x2::pCfg('display_login_button_hookname');
		if(true == $this->_loginbuttonDisplayed){
			return '';
		}elseif( empty($setHookname) ){
			$this->_loginbuttonDisplayed = true;
			return '';
		}elseif($hookname != $setHookname){
			return '';
		}
		
		$this->_loginbuttonDisplayed = true;
		include template($this->pluginid. ':login_button_'. $hookname);
        return $return;	
		
	}
	
	/**
	 * 论坛版块快速发表处显示登录到新浪微博
	 * @return string
	 */
	function global_login_text(){
		if(!core_sina_xweibo_x2::pCfg('is_display_login_button_in_fastpost_box')){
			return '';
		}
		return tpl_sina_xweibo_x2::get_login_button();
	}
	
	/**
	 * 登陆后在顶部右上角，未绑定时显示绑定按钮
	 */
	function global_usernav_extra1(){
		global $_G;
		if( !$_G['uid'] || !core_sina_xweibo_x2::pCfg('bind_btn_usernav') ){
			return $this->_get_unread_global_usernav_extra1_content();
		}
		
		if($this->_get_bind_sina_uid_by_cookie_cache() > 0){
			return $this->_get_unread_global_usernav_extra1_content();
		}else{
			return $this->_get_unbind_global_usernav_extra1_content();
		}
		
	}
	
	/**
	 * 家园首页左边显示Xweibo
	 * @return array
	 */
	function global_userabout_top(){
		if(!defined('CURSCRIPT') || CURSCRIPT != 'home'){
			return array();
		}
		
		$result = array();
		$switch = core_sina_xweibo_x2::pCfg('switch_to_xweibo');
		$xweibourl = core_sina_xweibo_x2::pCfg('baseurl_to_xweibo');
		if($switch && !empty($xweibourl)){
			$result['home::space'] = '<ul><li><a href="'. $xweibourl. '"><img height="16" width="16" src="xwb/images/bgimg/icon_logo_xweibo.png">&#24494;&#21338;</a></li></ul>';
		}
		
		return $result;
	}
	
	/**
	 * 删除帖子的时候删除帖子－微博绑定关系
	 * @param array $param X2功能集
	 * @return bool
	 */
	function deletethread($param){
		static $_deleted = false;
		if(true == $_deleted){
			return true;
		}
		if('delete' != $param['step']){
			return false;
		}
		
		if(is_array($param['param'][0]) && !empty($param['param'][0])){
			$tids = $param['param'][0];
			foreach($tids as $k => $tid){
				if(!is_numeric($tid)){
					unset($tids[$k]);
				}
			}
			$tids = dimplode($tids);
			DB::delete('xwb_bind_thread', "tid IN ($tids) AND type='thread'");
		}
		
		$_deleted = true;
		
		return true;
	}
	
	/**
	 * 获取global_usernav_extra1的未读消息脚本内容
	 * 仅允许plugin_sina_xweibo_x2::global_usernav_extra1()调用
	 * @return string
	 */
	function _get_unread_global_usernav_extra1_content(){
		global $_G;
		if(!core_sina_xweibo_x2::pCfg('switch_to_xweibo')){
			return '';
		}
		return <<<EOF
		<span id="xwb_allsum_{$_G['uid']}_container" style="display: none">
		    <span class="pipe">|</span><a id="xwb_unread_{$_G['uid']}" href="#" onmouseover="showMenu(this.id)" class="new" style="background-image: url(xwb/images/bgimg/icon_logo_xweibo.png);">&#24494;&#21338;(<span id="xwb_allsum_{$_G['uid']}">0</span>)</a>
		</span>
EOF;

	}
	
	/**
	 * 获取global_usernav_extra1的已登录但未绑定状态下的内容
	 * 仅允许plugin_sina_xweibo_x2::global_usernav_extra1()调用
	 * @return string
	 */
	function _get_unbind_global_usernav_extra1_content(){
		global $_G;
		if(isset($_G['gp_mod']) && $_G['gp_mod'] == 'space'){
			$addStyle = 'vertical-align:-5px;margin-top: 2px;';
		}else{
			$addStyle = 'vertical-align:-5px;';
		}
		return <<<EOF
			<span class="pipe">|</span>
			<a href="home.php?mod=spacecp&ac=plugin&id={$this->pluginid}:home_binding" target="_blank"><img style="{$addStyle}" src="xwb/images/bgimg/sina_bind_btn.png" /></a>
			&nbsp;
EOF;
	}
	
	/**
	 * 兼容X1.5的pCfg调用方法
	 * @see core_sina_xweibo_x2::pCfg()
	 */
	function pCfg( $key = null ){
		return core_sina_xweibo_x2::pCfg($key);
	}	
	
	/**
	 * 兼容X1.5的getRequestMethod调用方法
	 * @see core_sina_xweibo_x2::getRequestMethod()
	 */
	function getRequestMethod(){
		return core_sina_xweibo_x2::getRequestMethod();
	}
	
	/**
	 * 兼容X1.5的_start_xweibo调用方法
	 * @see singleton_sina_xweibo_x2::start_xweibo()
	 */
	function _start_xweibo( $force = false ){
		$i =& singleton_sina_xweibo_x2::getInstance();
		return $i->start_xweibo($force);
	}
	
	/**
	 * 兼容X1.5的isModuleAllowXweiboRun调用方法
	 * @see core_sina_xweibo_x2::isModuleAllowXweiboRun()
	 */
	function _isModuleAllowXweiboRun(){
		$i =& singleton_sina_xweibo_x2::getInstance();
		return $i->isModuleAllowXweiboRun();
	}
	
	/**
	 * 兼容X1.5的_get_bind_sina_uid_by_cookie_cache调用方法
	 * @see singleton_sina_xweibo_x2::get_bind_sina_uid_by_cookie_cache()
	 */
	function _get_bind_sina_uid_by_cookie_cache(){
		$i =& singleton_sina_xweibo_x2::getInstance();
		return $i->get_bind_sina_uid_by_cookie_cache();		
	}
	
	/**
	 * @see tpl_sina_xweibo_x2::getWeiboProfileLink()
	 */
	function getWeiboProfileLink($sina_uid){
		return tpl_sina_xweibo_x2::getWeiboProfileLink($sina_uid);
	}	
	
}

/**
 * “member"钩子
 *
 */
class plugin_sina_xweibo_x2_member extends plugin_sina_xweibo_x2{
	
	/**
	 * 注册钩子“快速登录”处增加新浪微博登录入口
	 * @return string
	 */
	function register_logging_method(){
		return tpl_sina_xweibo_x2::get_login_button();
	}
	
	/**
	 * 登录钩子“快速登录”处增加新浪微博登录入口
	 * @return string
	 */
	function logging_method(){
		return tpl_sina_xweibo_x2::get_login_button();
	}
	
}

/**
 * 论坛forum钩子
 */
class plugin_sina_xweibo_x2_forum extends plugin_sina_xweibo_x2{

    var $viewthread_sidetop_return; // 勋章HTML
    var $viewthread_imicons_return; // 状态页图标HTML
    var $viewthread_postfooter_return; // 资料页微博标识HTML
    var $viewthread_subject = '';
    var $_parsed_postlist = false;

    /**
     * 初始化
     *
     */
    function plugin_sina_xweibo_x2_forum()
    {
        $this->viewthread_sidetop_return = array();
        $this->viewthread_imicons_return = array();
    }
    
    /**
     * 在论坛首页，显示官方微博帐号，和加关注的按钮
     * @return string
     */
    function index_top_output(){
    	if(!core_sina_xweibo_x2::pCfg('display_ow_in_forum_index')){
			return '';
		}
        
        $i =& singleton_sina_xweibo_x2::getInstance();
        $owbUserRs = $i->_getCacheOfficialWeiboUser();
        
        if(!isset($owbUserRs['id'])){
        	return '';
        }
        
		if(!isset($owbUserRs['screen_name_local_encode'])){
			$owbUserRs['screen_name_local_encode'] = '&#23448;&#26041;&#24494;&#21338;';
		}else{
			$owbUserRs['screen_name_local'] = htmlspecialchars($owbUserRs['screen_name_local']);
		}
		$profile_url = tpl_sina_xweibo_x2::getWeiboProfileLink($owbUserRs['id']);
        include template($this->pluginid. ':ow_index_top');
        return $return;
    }
    
    /**
     * 发帖回帖截获钩子：（X2 showmessage()执行时调用）截获pid和tid，用于同步帖子内容到新浪微博
     * @param array $param X2在showmessage传入的参数集合
     */
	function post_sync_to_weibo_aftersubmit_message($param){
		global $_G;
		
		switch ($this->_checkIsForumPost($param)){
			//假如是发主题贴
			case 1:
				require XWB_plugin::hackFile('newthread');
				break;
			//假如是发回复
			case 2:
				require XWB_plugin::hackFile('newreply');
				break;
			//上述都不是，则什么都没有发生
			default:
				break;
		}
		
	}
	
	/**
	 * 发帖回帖截获钩子检查：是否成功进行了帖子发表操作？是否可以启动插件？用户是否在绑定？
	 * @access protected
	 * @param array $param X2在showmessage传入的参数集合
	 * @return integer 检查结果。0：没有进行任何操作或者检查不通过；1：发主题；2：发回复
	 */
	function _checkIsForumPost($param){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !$_G['uid'] || 'POST' != core_sina_xweibo_x2::getRequestMethod() || substr($param['param'][0], -8) != '_succeed'  || true != $this->_start_xweibo(true) || !XWB_plugin::isUserBinded() ){
			$result = 0;
		}elseif( $_G['gp_action'] == 'newthread' /*&& getgpc('topicsubmit')*/ && isset($param['param'][2]['tid']) && ($param['param'][2]['tid'] > 0) && XWB_plugin::pCfg('is_synctopic_toweibo') ){
			$result = 1;
		}elseif(  $_G['gp_action'] == 'reply'  /*&& getgpc('replysubmit')*/ && isset($param['param'][2]['pid']) && ($param['param'][2]['pid'] > 0) && XWB_plugin::pCfg('is_syncreply_toweibo') ){
			$result = 2;
		}else{
			$result = 0;
		}
		
		return $result;
	}
	
	/**
	 * 发主题帖界面显示复选框“同步到微博”
	 * @return string
	 */
	function post_middle_output(){
		global $_G;
		$return = '';
		
		if(  !$_G['uid'] || $_G['gp_action'] !== 'newthread' || 'GET' != core_sina_xweibo_x2::getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_synctopic_toweibo') ){
			return $return;
		}
		
		$lang['xwb_sycn_to_sina'] = XWB_plugin::L('xwb_sycn_to_sina');
		$lang['xwb_sycn_open'] = XWB_plugin::L('xwb_sycn_open');
		
		$p = XWB_plugin::O('xwbUserProfile');
		$html_checked = (int)($p->get('topic2weibo_checked',1));
		
		include template($this->pluginid. ':post_newthread');
		return $return;
	}
	
    /**
     * 发主题帖界面（浮动窗口）显示复选框“同步到微博”
     */
    function post_infloat_middle_output(){
    	global $_G;
		$return = '';
		
		if( !$_G['uid'] || $_G['gp_action'] !== 'newthread' || 'GET' != core_sina_xweibo_x2::getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_synctopic_toweibo') ){
			return $return;
		}
		
		if(!XWB_plugin::isUserBinded()){
			return tpl_sina_xweibo_x2::getUnBindSyncCheckbox();
		}else{
			return tpl_sina_xweibo_x2::getBindSyncCheckbox();
		}
    }	
    
    /**
     * 论坛版块快速发表处显示同步到新浪微博
     */
    function forumdisplay_fastpost_btn_extra_output(){
    	global $_G;
    	$return = '';
    	if(!$_G['uid'] || 'GET' != core_sina_xweibo_x2::getRequestMethod() || !core_sina_xweibo_x2::pCfg('is_synctopic_toweibo')){
    		return $return;
    	}
    	
    	if($this->_get_bind_sina_uid_by_cookie_cache() < 1){
    		return tpl_sina_xweibo_x2::getUnBindSyncCheckbox();
    	}else{
    		return tpl_sina_xweibo_x2::getBindSyncCheckbox();
    	}
    	
    }
    
	/**
	 * 分享到按钮位置
	 * @return string
	 */
	function viewthread_useraction_output(){
		
		global $_G;
		$return = '';
		if( false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_rebutton_display') ){
			return $return;
		}
		
		$this->_parse_postlist_in_viewthread();
		if( XWB_S_UID < 1 || !XWB_plugin::isUserBinded() ){
			//没有绑定状态下，调用sina自己的转发按钮
			$link = tpl_sina_xweibo_x2::get_sina_share_link( $this->viewthread_subject );
		}else{
			$tid = isset($_G['gp_tid']) ? (int)$_G['gp_tid'] : 0; 
			$link = XWB_plugin::getEntryURL('xwbSiteInterface.share', array('tid' => $tid) );
			$link = "javascript:void( window.open('". urlencode($link). "', '', 'toolbar=0,status=0,resizable=1,width=680,height=500') );";
		}
		include template($this->pluginid. ':share_button_viewthread');
		
		return $return;
	}
	
	/**
	 * 勋章显示和资料页
	 * @return array $return
	 */
	function viewthread_sidetop_output()
    {
		if ( false == $this->_start_xweibo(true) ){
			return array();
		}

        $this->_parse_postlist_in_viewthread();
        return $this->viewthread_sidetop_return;
	}

    /**
     * 状态页图标
     * @return array $return
     */
    function viewthread_imicons_output()
    {
        if ( false == $this->_start_xweibo(true) ){
        	return array();
        }
        
		$this->_parse_postlist_in_viewthread();
        return $this->viewthread_imicons_return;
    }

    /**
     * 资料页微博标识
     * @return array $return
     */
    function viewthread_postfooter_output()
    {
        if ( false == $this->_start_xweibo(true) ){
        	return array();
        }

        $this->_parse_postlist_in_viewthread();
        return $this->viewthread_postfooter_return;
    }

    /**
     * 解析$GLOBALS['postlist']
     * @access protected
     */
    function _parse_postlist_in_viewthread()
    {
    	global $_G;
        if ( empty($GLOBALS['postlist']) || !is_array($GLOBALS['postlist']) || true === $this->_parsed_postlist )
        {
            return $this->_parsed_postlist;
        }
        
        require XWB_plugin::hackFile('viewthread');
        
        foreach ($GLOBALS['postlist'] as $pid => $onePost)
        {
            $this->viewthread_sidetop_return[] = $this->_html_sidetop($onePost, $sina_uid);
            $this->viewthread_imicons_return[] = $this->_html_imicons($onePost, $sina_uid);
            $this->viewthread_postfooter_return[] = $this->_html_postfooter($pid, $onePost, $sina_uid);
        }
        
        if ( ! empty($_G['gp_viewpid']))
        {
            global $post;
            $post['signature'] = isset($post['signature']) ? XWB_plugin::F('xwb_format_signature', $post['signature']) : '';
        }
        
        $this->_parsed_postlist = true;
        return true;
        
    }

    /**
     * 返回勋章HTML字符串
     * @access protected
     * @return string $str
     */
    function _html_sidetop($post, $sina_uid)
    {
    	
    	global $_G;
    	
    	if( !XWB_plugin::pCfg('is_tips_display') ){
    		return '';
    	}
    	
        if (isset($sina_uid[$post['authorid']]))
        {
            return '<a href="' . tpl_sina_xweibo_x2::getWeiboProfileLink($sina_uid[$post['authorid']]) . '" target="_blank" class="xwb-plugin-medal-sinawb"><img onmouseout="XWBcontrol.TipPanel.setHideTimer();" onmouseover="XWBcontrol.TipPanel.showLayer(this, \'' . $sina_uid[$post['authorid']] . '\');" src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_on.gif') . '" /></a>';
        }
        else
        {
        	if( $_G['uid'] > 0 && $post['authorid'] == $_G['uid'] ){
        		return '<a href="home.php?mod=spacecp&ac=plugin&id='. $this->pluginid. ':home_binding" ><img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_off.gif') . '" class="xwb-plugin-medal-sinawb"  title="' . XWB_plugin::L('xwb_bind_my_sina_mblog') . '" /></a>';
        	}else{
        		return '<img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_off.gif') . '" class="xwb-plugin-medal-sinawb"  title="' . XWB_plugin::L('xwb_off_bind_sinamblog') . '" />';
        	}
        }
    }

    /**
     * 返回状态页图标HTML字符串
     * @access protected
     * @return string $str
     */
    function _html_imicons($post, $sina_uid)
    {
        global $_G;
        
        if (isset($sina_uid[$post['authorid']]))
        {
            return '<a href="' . tpl_sina_xweibo_x2::getWeiboProfileLink($sina_uid[$post['authorid']]) . '" target="_blank"><img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_on.gif') . '" title="' . XWB_plugin::L('xwb_his_sina_mblog',  $post['author']) . '" /></a>';
        }
        else
        {
            if( $_G['uid'] > 0 && $post['authorid'] == $_G['uid'] ){
        		return '<a href="home.php?mod=spacecp&ac=plugin&id='. $this->pluginid. ':home_binding" ><img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_off.gif') . '"  title="' . XWB_plugin::L('xwb_bind_my_sina_mblog') . '" /></a>';
        	}else{
        		return '<img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_off.gif') . '" title="' . XWB_plugin::L('xwb_off_bind_sinamblog') . '" />';
        	}
        }
    }

    /**
     * 返回微博标识HTML字符串
     * @access protected
     * @return string $str
     */
    function _html_postfooter($pid, $post, $sina_uid)
    {
        global $_G;

        if( !XWB_plugin::pCfg('is_tgc_display') ){
    		return '';
    	}

        if (isset($sina_uid[$post['authorid']]))
        {
            return '<input id="UserSinaId' . $pid . '" type="hidden" value="' . $sina_uid[$post['authorid']]  . '" />';
        }
        else
        {
            return '<input id="UserSinaId' . $pid . '" type="hidden" value="0" />';
        }
    }
    
}

/**
 * 群组group钩子，继承论坛forum钩子
 *
 */
class plugin_sina_xweibo_x2_group extends plugin_sina_xweibo_x2_forum{
	
}

class plugin_sina_xweibo_x2_home extends plugin_sina_xweibo_x2{
	
	/**
	 * 家园－主页页面：显示“记录同步到微博”的设置链接
	 * @return string
	 */
	function space_home_top_output(){
		return $this->_tpl_home_doing_bindtip('home_doing_bindtip_home');
	}
	
	/**
	 * 家园－记录页面：显示“记录同步到微博”的设置链接
	 * @return string
	 */
	function space_doing_bottom_output(){
		return $this->_tpl_home_doing_bindtip('home_doing_bindtip_doing');
	}
	
	/**
	 * [模板]家园相关记录页面显示“记录同步到微博”的设置链接
	 * @access protected
	 * @param string $tpl
	 * @return string
	 */
	function _tpl_home_doing_bindtip($tpl){
		global $_G;
		$return = '';
		if( !$_G['uid'] || isset($_G['cookie']['disable_bindtip_do']) || false == $this->_start_xweibo(true) ){
			return $return;
		}
		
		if( XWB_plugin::isUserBinded() ){
			$p = XWB_plugin::O('xwbUserProfile');
			$doing2weibo = (int)($p->get('doing2weibo',0));
		}else{
			$doing2weibo = -1;
		}
		
		$lang['xwb_want_to_doing2weibo'] = XWB_plugin::L('xwb_want_to_doing2weibo');
		$lang['xwb_allow_doing2weibo'] = XWB_plugin::L('xwb_allow_doing2weibo');
		include template($this->pluginid. ':'. $tpl);
		return $return;
	}
	
	/*
	 * 家园－分享页面：显示“记录同步到微博”的设置链接
	 * X2 RC不存在此钩子，暂时注释
	 * @return string
	 */
	/*
	function space_share_bottom_output(){
	}
	*/
	
	/**
	 * 家园－个人资料页面：显示“是否已经绑定到新浪微博”
	 * @return string
	 */
	function space_profile_baseinfo_top_output(){
		global $_G;
		
		if( empty($GLOBALS['space']) || true != $this->_start_xweibo(true) ){
			return $return;
		}
		
		if(isset($GLOBALS['space']['uid'])){
			$uid = (int)$GLOBALS['space']['uid'];
		}elseif(isset($_G['gp_uid'])){
			$uid = (int)$_G['gp_uid'];
		}else{
			$uid = 0;
		}
		
		if( $uid < 1 ){
			return $return;
		}
		
		require XWB_plugin::hackFile('space');
		
		$setting['is_wbx_display'] = 0;
		$PluginUrl['icon_on'] = XWB_plugin::getPluginUrl('images/bgimg/icon_on.gif');
		$PluginUrl['icon_off'] = XWB_plugin::getPluginUrl('images/bgimg/icon_off.gif');
		
		$lang['xwb_off_bind_sinamblog'] = XWB_plugin::L('xwb_off_bind_sinamblog');
		$lang['xwb_have_bind_sinamblog'] = XWB_plugin::L('xwb_his_sina_mblog', $GLOBALS['space']['username']);
        $lang['xwb_bind_my_sina_mblog'] = XWB_plugin::L('xwb_bind_my_sina_mblog');
		$weibo_profile_link = '';
        if(isset($sina_uid[$uid])){
			$weibo_profile_link = tpl_sina_xweibo_x2::getWeiboProfileLink($sina_uid[$uid]);
		}
        
		include template($this->pluginid. ':home_space_profile');
		
		return $return;
	}
	
	/**
	 * 用户名片：显示个人微博信息
	 * @return string
	 */
	function space_card_baseinfo_bottom_output(){
		global $_G;
		$output = '';
		
		$uid = isset($_G['gp_uid']) ? (int)$_G['gp_uid'] : 0;
		if($uid < 1 || !$this->pCfg('space_card_weiboinfo') || false == $this->_start_xweibo(true)){
			return $output;
		}
		$result = XWB_plugin::getBindUser($uid);
		
		if(is_array($result) && isset($result['uid'])){
			$xweibourl_ta = tpl_sina_xweibo_x2::getWeiboProfileLink($result['sina_uid']);
			$switch = $this->pCfg('switch_to_xweibo');
			if($switch){
				$xweibourl = $this->pCfg('baseurl_to_xweibo');
				$xweibourl_ta_friends = $xweibourl. '/index.php?m=ta.follow&id='. $result['sina_uid'];
				$xweibourl_ta_followers = $xweibourl. '/index.php?m=ta.fans&id='. $result['sina_uid'];
			}else{
				$xweibourl = $xweibourl_ta_friends = $xweibourl_ta_followers = '';
			}
			
			$profile = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $result['profile']), true);
			if(isset($profile['tipUserInfo']['friends_count'])){
				if($switch){
					$output = '<img height="16" width="16" src="'. $this->xwb_p_rootname. '/images/bgimg/icon_logo.png" />&nbsp;<a href="'. 
							$xweibourl_ta_friends. '" target="_blank">&#20851;&#27880;</a>:'. $profile['tipUserInfo']['friends_count']. '&nbsp;&nbsp;<a href="'. 
							$xweibourl_ta_followers. '" target="_blank">&#31881;&#19997;</a>:'. $profile['tipUserInfo']['followers_count']. '&nbsp;&nbsp;<a href="'. 
							$xweibourl_ta. '" target="_blank">&#24494;&#21338;</a>:'. $profile['tipUserInfo']['statuses_count'];
				}else{
					$output = '<img height="16" width="16" src="'. $this->xwb_p_rootname. '/images/bgimg/icon_logo.png" />&nbsp;&#20851;&#27880;:'. $profile['tipUserInfo']['friends_count']. '&nbsp;&nbsp;&#31881;&#19997;:'. $profile['tipUserInfo']['followers_count']. '&nbsp;&nbsp;&#24494;&#21338;:'. $profile['tipUserInfo']['statuses_count'];
				}
			}else{
				$output = '<img height="16" width="16" src="'. $this->xwb_p_rootname. '/images/bgimg/icon_logo.png" />&nbsp;<a href="'. $xweibourl_ta. '" target="_blank">&#35775;&#38382;&#24494;&#21338;&#39029;&#38754;</a>';
			}
			
		}elseif($_G['uid'] == $uid){
			$output = '<a href="home.php?mod=spacecp&ac=plugin&id='. $this->pluginid. ':home_binding">&#23578;&#26410;&#32465;&#23450;&#26032;&#28010;&#24494;&#21338;&#65292;&#28857;&#20987;&#32465;&#23450;</a>';
		}else{
			$output = 'TA&#23578;&#26410;&#32465;&#23450;&#26032;&#28010;&#24494;&#21338;';
		}
		
		return $output;
	}
	
	/**
	 * 设置-个人资料-个人信息：新浪微博签名工具
	 */
	function spacecp_profile_bottom_output(){
		
		global $_G;
		
		$return = '';
		
		//不允许签名中带img(暂时忽略该设置)，不开启签名或者开启过短，均将不允许使用新浪签名
		if ( !$_G['uid'] || 'info'!= $GLOBALS['_G']['gp_op'] || /* !$_G['group']['allowsigimgcode'] || */ $_G['group']['maxsigsize'] < 30 ){
			return $return;
		}
		
		if( 'GET' != $this->getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_signature_display') ){
			return $return;
		}

        include template($this->pluginid. ':signature_js_spacecp_profile_bottom_output');
		return $return;
		
	}
	
	/**
	 * 家园－个人空间－相册(space_album)：在相册专辑显示页面显示转发按钮
	 * @see this::_album_get_share_button()
	 */
	function space_album_op_extra_output(){
		return $this->_album_get_share_button('album');
	}
	
	/**
	 * 家园－个人空间－相册(space_album)：在图片显示页面显示转发按钮
	 * @see this::_album_get_share_button()
	 */
	function space_album_pic_op_extra_output(){
		return $this->_album_get_share_button('albumpic');
	}
	
	/**
	 * 家园－个人空间－相册(space_album)：获取图片地址
	 * @access protected
	 * @param string $type 类型，可选值'album'，'albumpic'
	 * @return string
	 */
	function _album_pic_get_url( $type = 'album' ){
		
		global $album, $pic, $_G;
		$pic_url = '';
		if( $type == 'album' ){
			require_once libfile('function/home');
			$pic_url = isset($album['pic']) & isset($album['picflag']) ? pic_cover_get($album['pic'], $album['picflag']) : '';
		}elseif( $type == 'albumpic' ){
			$pic_url = isset($pic['pic']) ? (string)$pic['pic'] : '';
		}
		return ( 0 === strpos($pic_url, 'http') || 0 === strpos($pic_url, 'ftp') ) ? $pic_url : ($_G['siteurl']. $pic_url);
	}
	
	/**
	 * 家园－个人空间－相册(space_album)：通用的转发按钮代码
	 * @param string $type 类型，可选值'album'，'albumpic' 
	 */
	function _album_get_share_button( $type = 'album' ){
		global $album;
		$return = '';
		
		if( !$this->pCfg('is_rebutton_display') || false == $this->_start_xweibo(true) ){
			return $return;
		}else{
			$albumname = isset($album['albumname']) ? (string)$album['albumname'] : '';
			$albumUsername = isset($album['username']) ? (string)$album['username'] : '';
			$link = tpl_sina_xweibo_x2::get_sina_share_link( $albumUsername. ' - '. $albumname, $this->_album_pic_get_url($type) );
			include template($this->pluginid. ':share_button_blog');
			return $return;
		}
	}
	
	/**
	 * 家园－日志：在日志显示页面显示转发按钮
	 */
	function space_blog_op_extra_output(){
		global $blog;
		$return = '';
		if( !$this->pCfg('is_rebutton_display') || false == $this->_start_xweibo(true) ){
			return $return;
		}else{
			$subject = isset($blog['subject']) ? $blog['subject'] : '';
			$link = tpl_sina_xweibo_x2::get_sina_share_link($subject);
			include template($this->pluginid. ':share_button_blog');
			return $return;
		}
	}
	
	/**
	 * 家园－日志：显示发表同步按钮
	 */
	function spacecp_blog_middle_output(){
		global $_G;
		$return = '';
		
		if( 'blog' != $_G['gp_ac'] || !$_G['uid'] || 'GET' != $this->getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_syncblog_toweibo') ){
			return $return;
		}

		$lang['xwb_sycn_to_sina'] = XWB_plugin::L('xwb_sycn_to_sina');
		$lang['xwb_sycn_open'] = XWB_plugin::L('xwb_sycn_open');
		
		$p = XWB_plugin::O('xwbUserProfile');
		$html_checked = (int)($p->get('blog2weibo_checked',1));
		
		include template($this->pluginid. ':spacecp_newblog');
		return $return;
		
	}	
	
	/**
	 * 家园－日志：日志发表截获钩子：日志同步到微博
	 * @param array $param DX传递的参数集
	 */
	function spacecp_blog_sync_to_weibo_aftersubmit_message($param){
		global $_G;
		
		switch ($this->_checkIsBlogPost($param)){
			case 1:
				require XWB_plugin::hackFile('newblog');
				break;
			default:
				break;
		}
		
	}
	
	/**
	 * 家园－日志：日志发表截获钩子检查：是否是在进行日志发表操作、是否可以启动插件、用户是否在绑定状态？
	 * @param array $param DX传递的参数集
	 * @return integer 检查结果：0：不通过；1：发表日志操作
	 */
	function _checkIsBlogPost($param){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !in_array($_G['gp_ac'], array('blog', 'comment')) || !$_G['uid'] || 'POST' != core_sina_xweibo_x2::getRequestMethod() || substr($param['param'][0], -8) != '_success' || false == $this->_start_xweibo(true) || !XWB_plugin::isUserBinded() ){
			$result = 0;
		}elseif(getgpc('blogsubmit') && XWB_plugin::pCfg('is_syncblog_toweibo')) {
			$result = 1;
		}else{
			$result = 0;
		}
		
		return $result;
	}
	
	/**
	 * 家园－记录：发表截获：同步记录到微博
	 * @param array $param DX2传递的参数集
	 */
	function spacecp_doing_aftersubmit_message($param){
		global $_G;
		
		switch ($this->_checkIsDoingPost($param)){
			case 1:
				require XWB_plugin::hackFile('newdoing');
				break;
			case 2:
				require XWB_plugin::hackFile('newcomment2doing');
				break;
			default:
				break;
		}
	}
	
	/**
	 * 家园－记录：记录发表截获钩子检查：是否是在进行记录发表操作、是否可以启动插件、用户是否在绑定状态
	 * @param array $param DX2传递的参数集
	 * @return integer 检查结果。0：检查失败；1：发表记录；2：对记录进行评论
	 */
	function _checkIsDoingPost($param){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !$_G['uid'] || 'POST' != $this->getRequestMethod() || substr($param['param'][0], -8) != '_success' 
			|| 'doing' != $_G['gp_ac'] || false == $this->_start_xweibo(true) || !XWB_plugin::isUserBinded() ){
			$result = 0;
		}elseif( getgpc('addsubmit') && isset($param['param'][2]['doid']) && $param['param'][2]['doid'] > 0 && XWB_plugin::pCfg('is_syncdoing_toweibo') ){
			$result = 1;
		}elseif( getgpc('commentsubmit') && XWB_plugin::pCfg('is_syncreply_toweibo') ){
			$result = 2;
		}else{
			$result = 0;
		}
		
		return $result;
	}
	
	/**
	 * 家园－分享：发表截获：同步分享到微博
	 * @param array $param DX2传递的参数集
	 */
	function spacecp_share_aftersubmit_message($param){
		switch ($this->_checkIsSharePost($param)){
			case 1:
				require XWB_plugin::hackFile('newshare');
				break;
			default:
				break;
		}
	}
	
	/**
	 * 家园－分享：发表截获钩子检查：是否是在进行分享发表操作、是否可以启动插件、用户是否在绑定状态
	 * @param array $param DX2传递的参数集
	 * @return integer 检查结果。0：检查失败；1：发表分享
	 */
	function _checkIsSharePost($param){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !$_G['uid'] || 'POST' != $this->getRequestMethod() || substr($param['param'][0], -8) != '_success' 
			|| $_G['gp_ac'] != 'share' || false == $this->_start_xweibo(true) || !XWB_plugin::isUserBinded() ){
			$result = 0;
		}elseif( getgpc('sharesubmit') && isset($param['param'][2]['sid']) && $param['param'][2]['sid'] > 0 && XWB_plugin::pCfg('is_syncshare_toweibo') ){
			$result = 1;
		}else{
			$result = 0;
		}
		
		return $result;
	}	
	
	/**
	 * 各类评论同步到微博
	 * @param array $param DX2传递的集合
	 */
	function spacecp_comment_aftersubmit_message($param){
		global $_G;
		switch ($this->_checkIsCommentPost($param)){
			case 1:
				//对日志进行评论
				require XWB_plugin::hackFile('newcomment2blog');
				break;
			case 2:
				//对分享进行评论
				require XWB_plugin::hackFile('newcomment2share');
				break;
			default:
				break;
		}
	}
	
	/**
	 * 家园－记录：记录发表截获钩子检查：是否是在进行评论发表操作、是否可以启动插件、用户是否在绑定状态
	 * @param array $param DX传递的参数集
	 * @return integer 检查结果。0：检查失败；1：对日志进行评论；2：对分享进行评论
	 */
	function _checkIsCommentPost($param){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !$_G['uid'] || 'POST' != $this->getRequestMethod() || substr($param['param'][0], -8) != '_success'
		 || false == $this->_start_xweibo(true)  || !XWB_plugin::isUserBinded() || !XWB_plugin::pCfg('is_syncreply_toweibo') ){
		 	$result = 0;
		}elseif( isset($_G['gp_idtype']) && $_G['gp_idtype'] == 'blogid' && getgpc('commentsubmit') ){
			$result = 1;
		}elseif( isset($_G['gp_idtype']) && $_G['gp_idtype'] == 'sid' && getgpc('commentsubmit') ){
			$result = 2;
		}else{
			$result = 0;
		}
		
		return $result;
	}	
	
}

/**
 * 门户钩子
 *
 */
class plugin_sina_xweibo_x2_portal extends plugin_sina_xweibo_x2{
	
	/**
	 * 发表文章页面钩子：同步按钮显示
	 */
	function portalcp_bottom_output(){
		global $_G;
		$return = '';
		
		if( 'article' != $_G['gp_ac'] || !$_G['uid'] || (!isset($GLOBALS['op']) || 'add' != $GLOBALS['op']) || 'GET' != core_sina_xweibo_x2::getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::pCfg('is_syncarticle_toweibo') ){
			return $return;
		}

		$lang['xwb_sycn_to_sina'] = XWB_plugin::L('xwb_sycn_to_sina');
		$lang['xwb_sycn_open'] = XWB_plugin::L('xwb_sycn_open');
		
		$p = XWB_plugin::O('xwbUserProfile');
		$html_checked = (int)($p->get('article2weibo_checked',1));
		
		include template($this->pluginid. ':portalcp_newarticle');
		return $return;
	}
	
	/**
	 * 发表文章截获钩子：同步到微博
	 * 由于发表文章的提示不是采用showmessage，而是用专用模板（template/default/portal/portalcp_article.htm；搜索add_success），
	 * 故仍然只能采取老方法截获
	 */
	function portalcp_article_sync_to_weibo_aftersubmit_output(){
		global $_G;
		switch ($this->_checkIsArticlePost()){
			case 1:
				require XWB_plugin::hackFile('newarticle');
				break;
			default:
				break;
		}
	}
	
	/**
	 * 门户文章发表截获钩子检查：是否是在进行文章发表操作、是否可以启动插件、用户是否在绑定状态
	 * @return integer 检查结果。0：检查失败；1：发表文章
	 */
	function _checkIsArticlePost(){
		global $_G;
		static $result = -999;
		if( $result >= 0 ){
			return $result;
		}
		
		if( !in_array($_G['gp_ac'], array('article')) || !$_G['uid'] || 'POST' != $this->getRequestMethod() || false == $this->_start_xweibo(true) || !XWB_plugin::isUserBinded() ){
			$result = 0;
		}elseif(getgpc('articlesubmit') && XWB_plugin::pCfg('is_syncarticle_toweibo') ){
			$result = 1;
		}else{
			$result = 0;
		}
		
		return $result;
	}	
	
}

/**
 * 相对独立于钩子的、有关新浪微博插件的各种核心静态方法集合
 */
class core_sina_xweibo_x2{
	
	/**
	 * 获取请求方法
	 * @static
	 * @return string 返回英文大写的请求方法
	 */
	function getRequestMethod(){
		static $_requestMethod = null;
		if( null == $_requestMethod ){
			$_requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
		}
		return $_requestMethod;
	}
	
	/**
	 * 获取插件一个或者多个设置
	 * 用于在插件框架没有启动时，读取相应的设置值
	 * @static
	 * @param mixed $key
	 * @return mixed
	 */
	function pCfg( $key = null ){
		//插件已经初始化过，就使用插件的方法获取设置值
		if(defined('IS_IN_XWB_PLUGIN')){
			return XWB_plugin::pCfg($key);
		}
		
		static $_configImported = false;
		static $_config = null;
		
		//否则就自己读取插件的设置
		if( false == $_configImported ){
			$configFile = DISCUZ_ROOT.'./'. core_sina_xweibo_x2::getXwbRootName() .'/set.data.php';
			if( file_exists($configFile) ){
				require $configFile;
				$_config= (array)$__XWB_SET;
			}
			$_configImported = true;
		}
		if( null !== $key ){
			return isset($_config[$key]) ? $_config[$key] : null;
		}else{
			return $_config;
		}
	}
	
	/**
	 * 获取xweibo的根目录名称
	 * @return string
	 */
	function getXwbRootName(){
		return 'xwb';
	}
	
	/**
	 * 获取钩子的根目录名称
	 * @return string
	 */
	function getXwbHookRootName(){
		return 'sina_xweibo_x2';
	}

 	/**
	 * 获取Bind cookies名称
	 * @param integer $uid
	 * @return string
	 */
	function getBindCookiesName($uid){
		return 'sina_bind_'. $uid;
	}
	
}

/**
 * Xweibo插件版钩子内部使用的单例操作类
 * 所有分散在各个钩子内、但需要用单例进行操作的统一放在这里，
 * 以避免各个钩子子类实例化所引发的隐性资源消耗
 * 请使用{@link singleton_sina_xweibo_x2::getInstance()}获取一个单例，然后再作其他操作！
 */
class singleton_sina_xweibo_x2{
	
	//允许运行插件的模块。以 CURSCRIPT => CURMODUULE 进行定义
	var $allowModuleRun = array( 
									'forum' => array('post','viewthread'),
									//'member' => array('logging', 'register'),
									'portal' => array('portalcp'),
									'home' => array('spacecp', 'space'),
								);
	
	//标识是否允许Xweibo运行。
	//默认不允许，需要强制在方法_start_xweibo中传入true才允许运行
	var $_allowXweiboRun = false;
	
	
	//绑定的sina uid用户：-1未绑定状态、大于0表示绑定的用户
	var $_bind_sina_uid = null;
	
	//官方微博帐号数据
	var $owbUserRs = null;
	
	/**
	 * 获取一个单例
	 * @return singleton_sina_xweibo_x2
	 */
	function &getInstance(){
		static $_i = null;
		if(null == $_i){
			$_i =& new singleton_sina_xweibo_x2();
		}
		return $_i;
	}
	
	/**
	 * Xweibo插件版完整核心启动方法（初始化插件运行环境）
	 * 若允许插件运行、或者已经初始化，则返回true；否则返回false，表示不可以运行插件。
	 * 除非特殊情况，否则在使用新浪微博相关钩子前，请自行运行一遍该方法。并根据返回的bool值作相应的判断处理。
	 * 不能够将该方法放入__construct方法中！否则将引起大面积的无意义资源消耗！
	 * @param boolen $force 是否强制运行插件。一般需要传入true
	 * @return boolen
	 */
	function start_xweibo( $force = false ){
		if( true === $force ){
			$this->_allowXweiboRun = true;
		}
		
		//已经载入网站运行环境了
		if( defined('XWB_PLUGIN_SITE_ENV_LOADED') ){
			return true;
		}
		
		/*
		 * 插件内部没有允许xweibo运行，不允许运行
		 * 此情况出现在没有显著在类方法内允许插件运行
		 * 或者已经运行过初始化插件环境，但因为不符合要求而失败，被禁止运行
		 */
		if( true !== $this->_allowXweiboRun ){
			return false;
		}
		
		//插件目录不存在，禁止运行
		$xwb_start_file = DISCUZ_ROOT.'./'. core_sina_xweibo_x2::getXwbRootName() .'/plugin.env.php';
		if( !is_file($xwb_start_file) ){
			$this->_allowXweiboRun = false;
			return false;
		}
		
		//初始化插件环境
		require_once $xwb_start_file;
		
		//再次检查是否载入网站运行环境了（防止直接运行xwb.php时，站点关闭导致程序出错）
		if( !defined('XWB_PLUGIN_SITE_ENV_LOADED') ){
			$this->_allowXweiboRun = false;
			return false;
		}
		
		//第一次从此处启动插件的debug信息存储（开发时使用）
		//register_shutdown_function(array(&$this, '_showXweiboIsStart'), debug_backtrace());
		
		return true;
		
	}
	
	/**
	 * 显示xweibo插件第一次启动的信息（开发时使用）
	 */
	function _showXweiboIsStart($debug_traceinfo){
		global $_G;
		if($_G['gp_inajax']){
			return false;
		}
		//file_put_contents( 'R:/debug_'.date("Y-m-d-H-i-s") , var_export($debug_traceinfo, true) );
		echo '<div class="wp cl">
					<div class="notice">新浪微博插件曾经被初始化。初始化信息：
						<br />'. nl2br(print_r($debug_traceinfo, true)).'
					</div>
				</div>';
	}
	
	/**
	 * 检测当前的页面模块(CURSCRIPT下的CURMODUULE)是否允许运行插件
	 * 此方法主要给方法名为“global xxx”调用方法“_start_xweibo”使用，因为“global xxx”是最早调用的
	 * @return bool
	 */
	function isModuleAllowXweiboRun(){
		global $_G;
		
		$curscript = strtolower(CURSCRIPT);
		$curmodule = strtolower(CURMODULE);
		
		//整体运行模块禁止
		if( !isset($this->allowModuleRun[$curscript]) || !in_array( $curmodule, $this->allowModuleRun[$curscript] )  ){
			return false;
		}
		
		//部分特殊模块的特定区域禁止
		
		if( 'home' == $curscript && 'space' == $curmodule ){
			//个人空间中仅允许个人资料页面、日志显示页面、相册页面运行
			if( !isset($_G['gp_do']) || !in_array($_G['gp_do'], array('profile', 'blog', 'album') )  ){
				return false;
			}
			
		}elseif( 'home' == $curscript && 'spacecp' == $curmodule ){
			//家园设置仅允许2个地方运行（签名档和发表日志）
			if(  ( !isset($_G['gp_op']) || 'info' != $_G['gp_op'] )  && (!isset($_G['gp_ac']) ||  !in_array($_G['gp_ac'], array('blog', 'doing', 'share'))  ) ){
				return false;
			}
			
		}elseif( 'portal' == $curscript && 'portalcp' == $curmodule ){
			//家园设置仅允许2个地方运行（签名档和发表日志）
			if( !isset($_G['gp_ac']) ||  !in_array($_G['gp_ac'], array('article') ) ){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 用cookies缓存进行当前登录uid的绑定用户查询
	 * 没有绑定状态cookies，查询后就发送一个cookies
	 * @return bigint
	 */
	function get_bind_sina_uid_by_cookie_cache(){
		global $_G;
		if(is_numeric($this->_bind_sina_uid)){
			return $this->_bind_sina_uid;
		}
		
		$this->_bind_sina_uid = -1;
		if($_G['uid'] < 1){
			return $this->_bind_sina_uid;
		}
		$bind_status_cookiename = core_sina_xweibo_x2::getBindCookiesName($_G['uid']);
		if( !isset($_G['cookie'][$bind_status_cookiename]) ){
			//echo 'running db check';  //@todo 用于检测cookies是否起作用
            if(true === $this->start_xweibo()) {
                $bInfo = XWB_plugin::getBindUser($_G['uid'], 'site_uid'); //远程API
                if( isset($bInfo['sina_uid']) && $bInfo['sina_uid'] > 0 ){
                    $this->_bind_sina_uid = (string)$bInfo['sina_uid'];
                }
            } else {
                $sina_uid = DB::result_first('SELECT `sina_uid` FROM '. DB::table('xwb_bind_info'). ' WHERE `uid` = '. $_G['uid'] );
                if( is_numeric($sina_uid) && $sina_uid > 0 ){
                    $this->_bind_sina_uid = (string)$sina_uid;
                }
            }
			dsetcookie($bind_status_cookiename, $this->_bind_sina_uid, 604800);
		}elseif( is_numeric($_G['cookie'][$bind_status_cookiename]) && $_G['cookie'][$bind_status_cookiename] > 0 ){
			$this->_bind_sina_uid = (string)$_G['cookie'][$bind_status_cookiename];
		}
		return $this->_bind_sina_uid;
	}
	
	/**
     * 获取官方微博数据
     * @return array
     */
    function _getCacheOfficialWeiboUser(){
		if(!is_array($this->owbUserRs)){
			// 处理官方微博数据
        	$owbCacheFile = DISCUZ_ROOT.'./'. core_sina_xweibo_x2::getXwbRootName() . '/cache/owbset/owbCache.data.php'; //定义官方微博数据缓存文件路径
        	// 缓存文件存在
        	if ( is_file($owbCacheFile) ){
            	require $owbCacheFile; //调用官方微博数据缓存文件
            	if(!isset($owbUserRs) || !is_array($owbUserRs)){
            		$this->owbUserRs = array();
            	}else{
            		$this->owbUserRs = $owbUserRs;
            	}
        	}else{
            	$this->owbUserRs = array(); //官方微博数据未定义
        	}
		}
		
        return $this->owbUserRs;
	}
	
}

/**
 * 各类不宜采用单独模板文件的模板集合
 */
class tpl_sina_xweibo_x2{
	
	/**
	 * 获取“新浪微博登录”按钮(链接+24高的登录标识)
	 * 目前用于member相关钩子、以及global_login_text钩子
	 * @static
	 * @return string
	 */
	function get_login_button(){
		global $_G;
		if (!$_G['uid']) {
			//用新浪微博连接(已有新浪微博账号，可直接登录)
			return '<a href="xwb.php?m=xwbAuth.login" rel="nofollow" alt="&#19968;&#27493;&#25630;&#23450;"><img src="'. $this->xwb_p_rootname. '/images/bgimg/sina_login_btn.png" onerror="this.onerror=null;this.src=\'static/image/common/none.gif\'" class="vm" /></a>';
		}else{
			return '';
		}
	}
	
	/**
	 * 获取新浪官方的转发链接
	 * @param string $msg 转发信息，默认为空，表示由新浪转发服务器根据网页内容决定
	 * @param string $pic 转发图片，默认为空，表示由新浪转发服务器根据网页内容决定
	 */
	function get_sina_share_link( $msg = '', $pic= '' ){
		//采用DX的常量CHARSET
		$msg = (addslashes((string)$msg));
		$pic = (addslashes($pic));
		
		//官方帐号
		if(core_sina_xweibo_x2::pCfg('is_rebutton_relateUid_assoc')){
			$i =& singleton_sina_xweibo_x2::getInstance();
			$owbUserRs = $i->_getCacheOfficialWeiboUser();
			$owId = isset($owbUserRs['id']) ? addslashes($owbUserRs['id']) : '';
		}else{
			$owId = '';
		}
		
		
		return "javascript:void((function(s,d,e,r,l,p,t,z,c,o) {var f='http://service.weibo.com/share/share.php?appkey=". XWB_APP_KEY. "',u=z||d.location,p=['&url=',e(u),'&title=',e(t||d.title),'&ralateUid=',o||'','&sourceUrl=',e(l),'&content=',c||'gb2312','&pic=',e(p||'')].join('');function a(){if(!window.open([f,p].join(''),'mb', ['toolbar=0,status=0,resizable=1,width=440,height=430,left=',(s.width- 440)/2,',top=',(s.height-430)/2].join('')))u.href=[f,p].join('');}; if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0);}else{a();}}) (screen,document,encodeURIComponent,'','','{$pic}','{$msg}','','". CHARSET. "', '{$owId}'));";
	}
	
	/**
	 * 获取新浪微博或者xweibo的个人主页link
	 * @static
	 * @param bigint $sina_uid
	 * @return string
	 */
	function getWeiboProfileLink($sina_uid = 0){
		$xweibourl = rtrim(core_sina_xweibo_x2::pCfg('baseurl_to_xweibo'), '/');
		if(core_sina_xweibo_x2::pCfg('switch_to_xweibo') && !empty($xweibourl)){
			$xweibourl_ta = $xweibourl. '/index.php?m=ta&id='. $sina_uid;
		}else{
			$xweibourl_ta = 'http://weibo.com/'. $sina_uid;
		}
		return $xweibourl_ta;
	}
	
	/**
	 * 获取未绑定状态下的灰色不可选“同时发表至新浪微博”复选框+开通绑定按钮
	 * @return string
	 */
	function getUnBindSyncCheckbox(){
		//开通同步到新浪微博
		$pluginid = core_sina_xweibo_x2::getXwbHookRootName();
		return <<<EOF
		<input type="checkbox" disabled="disabled" checked="checked" value="1" id="syn" name="syn"><a target="_blank" href="home.php?mod=spacecp&ac=plugin&id={$pluginid}:home_binding">&#24320;&#36890;&#21516;&#27493;&#21040;&#26032;&#28010;&#24494;&#21338;</a>
EOF;
	}
	
	/**
	 * 获取绑定状态下的可选“同时发表至新浪微博”复选框
	 * 效率考虑，不考虑用户的具体设置
	 * @return string
	 */
	function getBindSyncCheckbox(){
		//同时发表至新浪微博
		return <<<EOF
		<input type="checkbox" value="1" id="syn" name="syn"><label for="syn">&#21516;&#26102;&#21457;&#34920;&#33267;&#26032;&#28010;&#24494;&#21338;</label>
EOF;
	}
	
}
