<?php

/**
 * 同步到微博中
 * 
 * @author xionghui
 * @author junxiong
 * @since 2010-6
 * @version $Id: xwb_plugins_publish.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class xwb_plugins_publish{
	function xwb_plugins_publish(){}
	
	/**
	 * 同步主题 For DiscuzX
	 * @param $pid int 论坛posts id
	 * @param $msg string 发布内容
	 * @param $num int 需要返回的图片数，默认为1张
	 * @return array
	 */
	function thread( $tid, $pid, $subject = '', $message = '' ) {
		global $_G;
        
		$db = XWB_plugin::getDB();
		//dz分表
		$posttable = getposttablebytid($tid);
		if( !$posttable ){
			return false;
		}
		
		if( empty($subject) || empty($message) ){
			//可以通过$tid, $pid进行查询
			//但由于此方法主要用于hook，而hook已经实施了拦截，因此可以无需通过数据查询即可进行获取
			return false;
		}else{
			// 转码前的内容保存
			$postinfo = array();
			$postinfo['subject'] = $subject;
			$postinfo['message'] = $message;
		}
		
		// 转码
		$subject = $this->_convert($postinfo['subject']);
		$message = $this->_convert($postinfo['message']);
		
		// 过滤UBB与表情
		$subject = $this->_filter($subject);
		$message = $this->_filter($message);
		
		//去重和合并处理
		$message = $this->_mergeMessage( $subject, $message );
		
		$link = ' ' . $this->getThreadUrl($tid);
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		
		//将最后附带的url给删除。
		$message = preg_replace("|\s*http://[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);
		
		$message .= $link;
		
		// 取出第一张图片
		$first_img_url = '';
		if( XWB_plugin::pCfg('is_upload_image') ){
			$image_list = $this->_getImage($pid, $postinfo['message']);
			if( isset( $image_list[0] ) ){
				$first_img_url = $image_list[0];
			}
		}
		
		$wb = XWB_plugin::getWB();
		$ret = array();
		
		// 同步到微博
		if (!empty($first_img_url)) {
			$ret = $wb->upload($message, $first_img_url, null, null, false);
			
			if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] ) {
				$ret = $wb->update($message, false);
			}
		} else {
			$ret = $wb->update($message, false);
		}
		
		//同步微博后的ID
		if (!empty($ret['id'])) {
			//@todo json_decode可能存在解析超过int最大数的错误（#47644）问题
			$mid = $ret['id'];
			$this->insertSyncId($tid, $mid, 'thread');
			
			//发帖同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $mid, 'type' => 1 ));
			
			//插入“已同步到......”到指定pid中。
			if( XWB_plugin::pCfg('wb_addr_display') && (!isset($GLOBALS['_G']['gp_special']) || $GLOBALS['_G']['gp_special'] != 127) ){
				$redirectURL = (XWB_plugin::pCfg('switch_to_xweibo') && XWB_plugin::pCfg('baseurl_to_xweibo')) ? rtrim(XWB_plugin::pCfg('baseurl_to_xweibo'), '/') . "/index.php?m=show&id={$mid}" : XWB_API_URL. $ret['user']['id']. '/statuses/'. $mid;

				$insertSyncUBB = '[size=2][color=gray]'. ' [img]' . XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png') . '[/img] '.
                            XWB_plugin::L('xwb_topic_has_sycn_to_new') . ' [url=' . $redirectURL . ']' . $_G['username'] .
                            XWB_plugin::L('xwb_topic_has_sycn_to_new_end') . '[/url][/color][/size]';
				$messageAppend = mysql_real_escape_string( "\n\n" . $insertSyncUBB );
				$db->query( 'UPDATE '. DB::table($posttable).  ' SET `bbcodeoff` = 0, `message` = CONCAT(`message`, \''.$messageAppend . '\') WHERE `pid` = \'' . $pid .'\''  );
			}
		}
		
	}

    /**
	 * 获取转发主题信息 For DiscuzX1.5
	 * @param $tid int 论坛thread id
	 * @return array
	 */
    function forShare($tid)
    {
        $threadURL = $this->getThreadUrl($tid);
        $url = ' ' . $threadURL;

        /* 分表 */
        $posttable = getposttablebytid($tid);
        if (empty($posttable)) return FALSE;

        /* 获取主题信息 */
        $db = XWB_plugin::getDB();
        $query = "SELECT pid, subject, message FROM " . DB::table($posttable) . " t WHERE tid='{$tid}' AND invisible='0' AND first='1'";
        $post = $db->fetch_first($query);
        if (empty($post)) return FALSE;

        /* 转码 */
		$subject = $this->_convert(trim($post['subject']));

		/* 过滤UBB与表情 */
		$subject = $this->_filter($subject);

        /* 将最后附带的url给删除 */
		$subject = preg_replace("|\s*http://[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $subject);

        /* 合并标题和链接 */
        $message = $subject . $url;

        // 取出所有图片
		$img_urls = array();
		if(XWB_plugin::pCfg('is_upload_image'))
        {
			$image_list = $this->_getImage($post['pid'], $post['message'], 999999);
            
			/* 增加新浪帖子同步图标过滤 2010-11-1 */
            $iconLogo = XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png');
            if (in_array($iconLogo, $image_list))
            {
                $unKey = array_search($iconLogo, $image_list);
                unset($image_list[$unKey]);
            }
            /* END */

			if( ! empty($image_list))
            {
				$img_urls = $image_list;
			}
		}
        return array(
            'url' => $threadURL,
            'title' => $subject,
            'message' => $message,
            'pics' => array_map('trim', $img_urls)
        );
    }

    /**
	 * 转发主题 For DiscuzX1.5
	 * @param $message 发布内容
     * @param $pic 发布图片
	 * @return bool
	 */
    function sendShare($message, $pic = '')
    {
        if (empty($message)) return FALSE;

        // 转码及过滤UBB与表情
		$message = $this->_filter($message);

        // 同步到微博
        $wb = XWB_plugin::getWB();
		$ret = array();
        
		if ( ! empty($pic))
        {
			$ret = $wb->upload($message, $pic, null, null, false);
            if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] )
            {
				$ret = $wb->update($message, false);
			}
		}
        else
        {
			$ret = $wb->update($message, false);
		}
		
		if(!empty($ret['id'])){
			//转发统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $ret['id'], 'type' => 6 ));
		}

        return $ret;
    }

	/**
	 * 同步回复for discuzx1
	 */
	function reply( $tid, $pid, $message = '' ) {
		$mid = $this->isSync($tid, 'thread');
		// 如果帖子没有被设置为同步到微博，或者如果用户没有绑定微博帖号，则退出此同步过程
		if (!$mid || !XWB_plugin::isUserBinded()) {
			return;
		}
		$db = XWB_plugin::getDB();
		
		if( empty($message) ){
			//可以通过$tid, $pid进行查询
			//但由于此方法主要用于hook，而hook已经实施了拦截，因此可以无需通过数据查询即可进行获取
			return false;
		}else{
			$postinfo = array();
			$postinfo['message'] = $message;
		}
		
		// 转码
		$message = $this->_convert($postinfo['message']);

        // 过滤引用内容 2011-01-04
        $message = trim(preg_replace("|\[quote\].*?\[/quote\]|s", '', $message));

        // 过滤回复提示 2011-01-04
        $message = trim(preg_replace("|\[b\]回复 \[url=.*? 的帖子\[/url\]\[/b\]|s", '', $message));
        
		// 过滤UBB和表情
		$message = $this->_filter($message);
		$link = ' ' . $this->getThreadUrl($tid);
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		//将最后附带的url给删除。
		$message = preg_replace("|\s*http://[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);
		
		$message .= $link;
		
		//同步到微博
		$wb = XWB_plugin::getWB();
		$rs = $wb->comment($mid, $message,null, false);
		
		if ( isset($rs['id']) && !empty($rs['id']) ) {
			//发帖同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $rs['id'] ));
		}

	}
	
	
	/**
	 * 转换为微博可以使用的编码
	 */
	function _convert($msg) {
		return XWB_plugin::convertEncoding($msg, XWB_S_CHARSET, 'UTF-8');
	}
	
	
	/**
	 * 过滤发布内容
	 */
	function _filter($content) {
		global $_G;
		//将[attachimg]和[attach]的UBB标签连同内容给全部删除
		$content = preg_replace('!\[(attachimg|attach)\]([^\[]+)\[/(attachimg|attach)\]!', '', $content);

        /* 过滤[img]标签，在其后面添加空格，防止粘连 2010-10-12 */
        $content = preg_replace('|\[img(?:=[^\]]*)?\](.*?)\[/img\]|', '\\1 ', $content);
        
		// 过滤UBB
		$re ="#\[([a-z]+)(?:=[^\]]*)?\](.*?)\[/\\1\]#sim";
		while(preg_match($re, $content)) {
			$content = preg_replace($re, '\2', $content);
		}

		// 过滤表情
		$re = isset($_G['cache']['smileycodes']) ? (array)$_G['cache']['smileycodes'] : array();
		$smiles_searcharray = isset($_G['cache']['smilies']['searcharray']) ? (array)$_G['cache']['smilies']['searcharray'] : array();
		$content = str_replace($re, '', $content);
		$content = preg_replace($smiles_searcharray, '', $content);
		//多个空格合为一个空格；前后空格去掉
		$content = preg_replace("#\s+#", ' ', $content);
		$content = trim($content);
		
		return $content;
	}
	
	
	
	/**
	 * 标题和内容去重，然后合并
	 *
	 */
	function _mergeMessage( $subject, $message ){
		$result = '';
		
		if( $subject != '' ){
			//当处理完成的帖子内容，全部去掉前后空格 包含于 帖子标题 ，则仅取帖子标题作为微博内容。并且返回。
			if( false !== strpos( $subject , $message ) ){
				$result = $subject;
				return $result;
			}

			//当处理完成的帖子内容，开头与帖子标题重复时，去掉帖子标题，仅取帖子内容作为微博内容。并且返回。
			if( 0 === strpos( $message, $subject ) ){
				$result = $message;
				return $result;
			}
		}
		
		//以上皆不符合，就直接进行整合。
		$result = $subject . ' | ' . $message;
		return $result;
	}
	
	
	/**
	 * 取得指定帖子图片的数组
     * For DiscuzX1.5 增加去掉网络图片宽高值功能
	 * @param $pid int 论坛posts id
	 * @param $msg string 发布内容
	 * @param $num int 需要返回的图片数，默认为1张
	 * @return array
	 */
	function _getImage($pid, $msg, $num = 1) {
		global $_G;
		require_once XWB_S_ROOT . '/source/function/function_attachment.php';
		
		$db = XWB_plugin::getDB();
		
		$attachfind = $attachreplace = $attachments = array();
		
		//X2开始attachment才分表，摘抄getattachtablebypid函数
		if(version_compare(XWB_S_VERSION, '2', '>=')){
			$tableid = DB::result_first("SELECT tableid FROM ".DB::table('forum_attachment')." WHERE pid='{$pid}' LIMIT 1");
			$attachmentTableName = 'forum_attachment_'.($tableid >= 0 && $tableid < 10 ? intval($tableid) : 'unused');
		}else{
			$attachmentTableName = 'forum_attachment';
		}
		
		$query = $db->query("SELECT * FROM ". DB::table($attachmentTableName). " WHERE pid='{$pid}'");
		while($attach = $db->fetch_array($query)) {
			// 只使用附件为图片、没有阅读权限和金钱权限、并且大小小于1Ｍ的发送到微博
			if($attach['isimage'] && $attach['price'] == 0 && $attach['readperm'] == 0 && $attach['filesize'] <= 1024 * 1024) {
				if(!isset($_G['xwb_ftp_remote_url'])){
					$_G['xwb_ftp_remote_url'] = isset($_G['setting']['ftp']['attachurl']) ? $_G['setting']['ftp']['attachurl'] : '';
				}
				$attach['url'] = ($attach['remote'] ? $_G['xwb_ftp_remote_url'] : $_G['setting']['attachurl']).'forum/';
				$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
				if ( strpos($attach['url'], "://") != false ){
					$attachreplace[] = '[attachimg]'. $attach['url'] . '/' . $attach['attachment'].'[/attachimg]';
				}else{
					$attachreplace[] = '[attachimg]'.XWB_plugin::siteUrl() . $attach['url'] . '/' . $attach['attachment'].'[/attachimg]';
				}
			}
			
			$attachments[] = $attach;
		}
		if($attachfind) {
			$msg = preg_replace($attachfind, $attachreplace, $msg);
		}
        
        /* 去掉网络图片宽高值 For DiscuzX1.5 2010-09-25 */
        $msg = preg_replace('|\[img=\d+,\d+](.*?)\[/img\]|', '[img]\\1[/img]', $msg);
        
		// 还原<img>为[img]
		$msg = preg_replace('/<img[^>]+src="([^\'"]+)"[^>]+>/',"[img]\\1[/img]", $msg);
		$image_list = array();
		if (preg_match_all('!\[(attachimg|img)\]([^\[]+)\[/(attachimg|img)\]!', $msg, $match, PREG_PATTERN_ORDER)) {
			if( count($match[2]) > $num ){
				$image_list = array_slice($match[2], 0, $num);
			}else{
				$image_list = $match[2];
			}
		}
		return $image_list;
	}
	
	/**
	 * 对utf-8编码截取
	 * @param $str string 要截取的源内容
	 * @param $length int 要截取的长度
	 */
	function _substr($str, $length) {
		//防止后面的操作导致内存溢出
		if( strlen($str) > $length + 600 ){
			$str = substr($str, 0, $length + 600);
		}
		
		$p = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
		preg_match_all($p,$str,$o);
		$size = sizeof($o[0]);
		$count = 0;
		for ($i=0; $i<$size; $i++) {
			if (strlen($o[0][$i]) > 1) {
				$count += 1;
			} else {
				$count += 0.5;
			}
			
			if ($count  > $length) {
				$i-=1;
				break;
			}
			
		}
		return implode('', array_slice($o[0],0, $i));
	}
	
	
	/**
	 * 获取指定tid的链接地址
	 * @param $tid
	 */
	function getThreadUrl($tid){
		global $_G;
		$enableFromuid = XWB_plugin::pcfg('link_visit_promotion');
		if( 1 != $enableFromuid && $_G['setting']['rewritestatus'] && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$siteUrl = $_G['siteurl'];
			if(version_compare(XWB_S_VERSION, '2', '<')){
				$siteUrl = str_replace(array('http://', 'https://', '//'), array('', '', '/'), $siteUrl);
			}
			
			$threadURL = rewriteoutput('forum_viewthread', 1, $siteUrl, $tid);
		} else {
			$threadURL = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
			if( 1 == $enableFromuid ){
				$threadURL .= ('&fromuid='. $_G['uid']);
			}
		}
		
		return $threadURL;
		
	}
	
	/**
	 * 同步日志 For DiscuzX
	 * @param $id int 日志id
	 * @param $subject string 发布的主题
	 */
	function blogSync( $id, $subject = '' ){
		global $_G;
		if( empty($subject) ){
			$subject = XWB_plugin::L('xwb_blog_no_subject');
		}
		$message = XWB_plugin::L('xwb_blog_publish_message', $subject);
		$message = $this->_convert($message);
		$link = ' ' . $_G['siteurl']. "home.php?mod=space&uid={$_G['uid']}&do=blog&id={$id}";
		if( 1 == XWB_plugin::pcfg('link_visit_promotion') ){
			$link .= ('&fromuid='. $_G['uid']);
		}
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		$message .= $link;
		
		$db = XWB_plugin::getDB();
		$wb = XWB_plugin::getWB();
		$ret = array();
		
		// 同步到微博
		$ret = $wb->update($message, false);
		
		//同步微博后的ID
		if (!empty($ret['id'])) {
			//@todo json_decode可能存在解析超过int最大数的错误（#47644）问题
			$mid = $ret['id'];
			$this->insertSyncId($id, $ret['id'], 'blog');
			
			//日志同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $mid, 'type'=>2 ));
			
			//插入“已同步到......”到指定id中。
			if( XWB_plugin::pCfg('wb_addr_display') ){
				$redirectURL = (XWB_plugin::pCfg('switch_to_xweibo') && XWB_plugin::pCfg('baseurl_to_xweibo')) ? rtrim(XWB_plugin::pCfg('baseurl_to_xweibo'), '/') . "/index.php?m=show&id={$mid}" : XWB_API_URL. $ret['user']['id']. '/statuses/'. $mid;

				$insertSyncHTML = "\n\r". '<DIV><FONT color="#808080" size=2><IMG src="'. XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png') . '">&nbsp;'. XWB_plugin::L('xwb_topic_has_sycn_to'). '&nbsp;<a href="'. $redirectURL. '" target="_blank">'. $_G['username']. XWB_plugin::L('xwb_topic_has_sycn_to_new_end'). '</A></FONT></DIV>';
				$insertSyncHTML = mysql_real_escape_string( $insertSyncHTML );
				
				$db->query( 'UPDATE '. DB::table('home_blogfield'). ' SET `message` = CONCAT(`message`, \''.$insertSyncHTML . '\') WHERE `blogid` = \'' . $id .'\''  );

			}
		}
		
	}
	
	
	/**
	 * 日志评论同步
	 * @param int $id 日志id
	 * @param string $message 内容
	 */
	function blogCommentSync($id, $topicUid, $message){
		global $_G;
		$mid = $this->isSync($id, 'blog');
		if (!$mid || !XWB_plugin::isUserBinded() ) {
			return;
		}
		
		$message = $this->_convert($message);
		$link = ' ' . $_G['siteurl']. "home.php?mod=space&uid={$topicUid}&do=blog&id={$id}";
		if( 1 == XWB_plugin::pcfg('link_visit_promotion') ){
			$link .= ('&fromuid='. $_G['uid']);
		}
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		$message .= $link;
		
		//同步到微博
		$wb = XWB_plugin::getWB();
		$rs = $wb->comment($mid, $message,null, false);
		
	}
	
	/**
	 * 同步记录 For DiscuzX
	 * @param $id int 记录id
	 * @param $message string 记录内容
	 * @return bool
	 */
	function doingSync( $id, $message = '' ){
		global $_G;
                file_put_contents('c:/aa.txt', $message);
		$message = $this->_convert($message);
        $message = preg_replace('|<img src=\\\\"static/image/smiley/.*?>|', '', $message); //过滤UBB码及表情 fallrain 2010-12-30
		$message = $this->_substr($message, 140);
		
		$db = XWB_plugin::getDB();
		$wb = XWB_plugin::getWB();
		$ret = array();
		
		// 同步到微博
		$ret = $wb->update($message, false);
		
		//同步微博后的ID
		if (!empty($ret['id'])) {
			//@todo json_decode可能存在解析超过int最大数的错误（#47644）问题
			$mid = $ret['id'];
			$this->insertSyncId($id, $ret['id'], 'doing');
			
			//日志同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $mid, 'type'=>3 ));
			
		}
		
	}
	
	
	/**
	 * 记录评论同步
	 * @param int $id 记录id
	 * @param string $message 内容
	 */
	function doingCommentSync($id, $message){
		global $_G;
		$mid = $this->isSync($id, 'doing');
		if (!$mid || !XWB_plugin::isUserBinded() ) {
			return;
		}
		
		$message = $this->_convert($message);
        $message = preg_replace('|<img src=\\\\"static/image/smiley/.*?>|', '', $message); //过滤UBB码及表情 fallrain 2010-12-30
		$message = $this->_substr($message, 140);
		
		//同步到微博
		$wb = XWB_plugin::getWB();
		$rs = $wb->comment($mid, $message,null, false);
		
		if ( isset($rs['id']) && !empty($rs['id']) ) {
			//发帖同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $rs['id'] ));
		}
		
	}
	
	/**
	 * 门户文章同步到微博
	 * @param int $id 文章id
	 * @param int $subject 门户主题
	 */
	function articleSync( $id, $subject ){
		global $_G;
		if( empty($subject) ){
			$subject = XWB_plugin::L('xwb_article_no_subject');
		}
		$message = XWB_plugin::L('xwb_article_publish_message', $subject);
		$message = $this->_convert($message);
		$link = ' ' . $_G['siteurl']. "portal.php?mod=view&aid={$id}";
		if( 1 == XWB_plugin::pcfg('link_visit_promotion') ){
			$link .= ('&fromuid='. $_G['uid']);
		}
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		$message .= $link;
		
		$db = XWB_plugin::getDB();
		$wb = XWB_plugin::getWB();
		$ret = array();
		
		// 同步到微博
		$ret = $wb->update($message, false);
		
		//同步微博后的ID
		if (!empty($ret['id'])) {
			//@todo json_decode可能存在解析超过int最大数的错误（#47644）问题
			$mid = $ret['id'];
			$this->insertSyncId($id, $ret['id'], 'article');
			
			//日志同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $mid, 'type'=>5 ));
			
			//插入“已同步到......”到指定id中。
			if( XWB_plugin::pCfg('wb_addr_display') ){
				$redirectURL = (XWB_plugin::pCfg('switch_to_xweibo') && XWB_plugin::pCfg('baseurl_to_xweibo')) ? rtrim(XWB_plugin::pCfg('baseurl_to_xweibo'), '/') . "/index.php?m=show&id={$mid}" : XWB_API_URL. $ret['user']['id']. '/statuses/'. $mid;

				$insertSyncHTML = "\n\r". '<DIV><FONT color="#808080" size=2><IMG src="'. XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png') . '">&nbsp;'. XWB_plugin::L('xwb_topic_has_sycn_to'). '&nbsp;<a href="'. $redirectURL. '" target="_blank">'. $_G['username']. XWB_plugin::L('xwb_topic_has_sycn_to_new_end'). '</A></FONT></DIV>';
				$insertSyncHTML = mysql_real_escape_string( $insertSyncHTML );
				
				$db->query( 'UPDATE '. DB::table('portal_article_content'). ' SET `content` = CONCAT(`content`, \''.$insertSyncHTML . '\') WHERE `aid` = \'' . $id .'\''  );

			}
		}
	}
	
	
	/**
	 * 分享同步
	 * @param integer $sid
	 * @param array $arr
	 */
	function shareSync($sid, $arr){
		global $_G;
		$type = $title = $pic = '';
		
		if( isset($arr['image']) && !empty($arr['image']) && XWB_plugin::pCfg('is_upload_image') ){
			$pic = str_replace('.thumb.jpg', '', $arr['image']);
		}
		
		switch (strtolower($arr['type'])){
			case 'space':
				$type = 'username';
				break;
			case 'blog':
				$type = 'subject';
				break;
			case 'album':
				$type = 'albumname';
				break;
			case 'pic':
				$type = 'albumname';
				break;
			case 'thread':
				$type = 'subject';
				break;
			case 'article':
				$type = 'title';
				break;
			case 'link':
			case 'video':
			case 'music':
			case 'flash':
				$type = 'link';
				break;
			default:
				break;
		}
		
		$arr['body_data'] = unserialize($arr['body_data']);
		if( empty($type) ){
			return false;
		}elseif( 'link' != $type ){
			$pattern = '/^<a[ ]+href[ ]*=[ ]*"([a-zA-Z0-9\/\\\\@:%_+.~#*?&=\-]+)"[ ]*>(.+)<\/a>$/';
			preg_match($pattern, $arr['body_data'][$type], $match);
			if( 3 !== count($match) ){
				return false;
			}
			$link = $_G['siteurl']. $match[1];
			if( 1 == XWB_plugin::pcfg('link_visit_promotion') ){
				$link .= ('&fromuid='. $_G['uid']);
			}
			$title = ('pic' == $type) ? $arr['body_data']['title'] : $match[2];
		}else{
			$link = $arr['body_data']['data'];
		}
		
		$message = !empty($arr['body_general']) ? (string)$arr['body_general'] : (string)$arr['title_template'];
		if( !empty($title) ){
			$message = $this->_convert($message. ' | '. $title);
		}else{
			$message = $this->_convert($message);
		}
		$link = ' '. $link;
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;
		$message = $this->_substr($message, $length);
		$message .= $link;
		
		$wb = XWB_plugin::getWB();
		// 同步到微博
		if ( !empty($pic) ) {
			$ret = $wb->upload($message, $pic, null, null, false);
			if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] ) {
				$ret = $wb->update($message, false);
			}
		} else {
			$ret = $wb->update($message, false);
		}
		
		
		//同步微博后的ID
		if (!empty($ret['id'])) {
			//@todo json_decode可能存在解析超过int最大数的错误（#47644）问题
			$mid = $ret['id'];
			$this->insertSyncId($sid, $ret['id'], 'share');
			
			//日志同步统计上报
			$sess = XWB_plugin::getUser();
			$sess->appendStat('ryz', array( 'uid' => XWB_plugin::getBindInfo("sina_uid"), 'mid' => $mid, 'type'=> 4 ));
			
		}
		
	}
	
	
	/**
	 * 分享评论同步
	 * @param int $id 分享id
	 * @param string $message 内容
	 */
	function shareCommentSync($id, $message){
		global $_G;
		$mid = $this->isSync($id, 'share');
		if (!$mid || !XWB_plugin::isUserBinded() ) {
			return;
		}
		
		$message = $this->_convert($message);
		$link = ' ' . $_G['siteurl']. "home.php?mod=space&do=share&id={$id}";
		if( 1 == XWB_plugin::pcfg('link_visit_promotion') ){
			$link .= ('&fromuid='. $_G['uid']);
		}
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);
		$message .= $link;
		
		//同步到微博
		$wb = XWB_plugin::getWB();
		$rs = $wb->comment($mid, $message,null, false);
		
	}
	
	
	/**
	 * 查询某种类型是否已经同步到微博？
	 * @param $id 要查询的id
	 * @param $type 要查询的$id所属的类型，可选值'thread','blog','doing', 'article', 'share'
	 * @return false|int
	 */
	function isSync($id, $type){
		$type = trim(strtolower($type));
		if( !in_array($type, array('thread', 'blog', 'doing', 'article', 'share')) ){
			return false;
		}
		
		$db = XWB_plugin::getDB();
		
		$sql = 'SELECT * FROM ' . DB::table('xwb_bind_thread') . ' WHERE `tid`=' . (int)$id. ' AND `type`="' . $type . '" LIMIT 1 ';
		$rs = $db->fetch_first($sql);
		
		if (!$rs) {
			return false;
		}
		return $rs['mid'];
	}
	
	
	/**
	 * 记录某种类型的某个id的微博同步关系
	 * @param int $id 要记录的id
	 * @param string|float $mid 微博id
	 * @param string $type $id所属的类型，可选值'thread','blog','doing', 'article', 'share'
	 * @return false|int
	 */
	function insertSyncId($id, $mid, $type){
		if(!is_numeric($mid)){
			return false;
		}
		
		$type = trim(strtolower($type));
		if( !in_array($type, array('thread', 'blog', 'doing', 'article', 'share')) ){
			return false;
		}
		$db = XWB_plugin::getDB();
		$id = (int)$id;
		$mid = mysql_real_escape_string($mid);
		$sql = "INSERT IGNORE INTO " . DB::table('xwb_bind_thread') . " (`tid`, `mid`, `type`) VALUES('{$id}', '{$mid}', '{$type}')";
		$db->query($sql);
		if ($db->affected_rows()) {
			return true;
		}
		return false;
	}
	
	
}
