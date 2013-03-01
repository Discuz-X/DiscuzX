<?php
/**
 * 帖子回复操作器
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: sitePushback2thread.class.php 846 2011-06-22 06:22:37Z yaoying $
 *
 */
class sitePushback2thread{

	//插入的用户信息
	var $_userConfig = array();
	
	//siteBindMapper实例
	var $_mapper = null;
	
	var $_forceInsert = false;
	
	//写入帖子forum_post表的预处理数据
	var $_postData = array();
	
	//更新主帖表相关信息
	var $_threadData = array();
	
	//个人回帖数
	var $_userReplyCount = 0;
	
	//个人最后发表时间
	var $_userLastReplyTime = 0;
	
	//更新板块表相关预处理数据
	var $_forumData = array();
	
	//db实例
	var $_db;
	
	/**
	 * 是否在发帖审核时间段？
	 * @see sitePushback2thread::_checkPostPeriods()
	 * @var null|bool
	 */
	var $_postPeriods = null;
	
	
	/**
	 * 构造函数
	 */
	function sitePushback2thread(){
		$this->_userConfig['ip'] = mysql_real_escape_string( XWB_plugin::getIP() );
		$this->_userConfig['uid'] = (int)XWB_plugin::pCfg('pushback_uid');
		$this->_userConfig['username'] = mysql_real_escape_string( XWB_plugin::convertEncoding((string)XWB_plugin::pCfg('pushback_username'), 'UTF-8', XWB_S_CHARSET) );
		$this->_userConfig['timestamp'] = (int)TIMESTAMP;   //DZ已有的变量，直接使用之
		if( $this->_userConfig['uid'] < 1 ){
			$this->_userConfig['uid'] = 0;
			$this->_userConfig['username'] = 'Guest';
		}
		$this->_db = XWB_plugin::getDB();
	}
	
	/**
	 * 导入siteBindMapper实例，并运行之
	 * @param siteBindMapper $mapperInstance
	 * @return bool 运行结果
	 */
	function importMapper(&$mapperInstance){
		$this->_mapper =& $mapperInstance;
		return $this->_runMapper();
	}
	
	/**
	 * 运行mapper，让其查找对应的tid、fid映射关系
	 * @return bool 运行结果
	 */
	function _runMapper(){
		return $this->_mapper->tidMapper( $this->_mapper->midMapGet('thread') );
	}
	
	
	
	/**
	 * 运行插入预处理
	 * @param array $data 评论回推发送过来的数据
	 * @return int 结果
	 */
	function prepareInsert( $comment ){
		$tid = $this->_checkMid($comment['mid']);
		if( $tid < 1 ){
			return $tid;
		}
		$content = $this->_createContent($comment);
		if( !empty($content) ){
			$time = isset($comment['time']) ? (int)$comment['time'] : time();
			return $this->_prepareSqlData( $tid, $content, $time );
		}else{
			return -10;
		}
		
	}
	
	
	
	/**
	 * 根据发送过来的数据，组装出已经转码的、要插入对应数据库的回帖内容
	 *
	 * @param array $data API发送过来的数据
	 * @return string 要插入的回帖内容（已经转码）
	 */
	function _createContent( $data ){
		//转换为论坛所需要的字符集
		if( empty($data['nick']) ){
			$data['nick'] = '回推';
		}
		$nickname = XWB_plugin::convertEncoding( (string)$data['nick'], 'UTF-8', XWB_S_CHARSET);
		$content = XWB_plugin::convertEncoding( (string)$data['text'], 'UTF-8', XWB_S_CHARSET);
		
		//DZ函数
		$content = dhtmlspecialchars($content);
		$content = $this->_replaceSinaUrlToUBB($content);
		$content = $this->_filterContent($content);
		if( empty($content) ){
			return '';
		}
		
		if( isset($data['pic']) && !empty($data['pic']) ){
			$content .= "\n\n".
						'[img]http://ww3.sinaimg.cn/large/' . $data['pic'] . '.jpg[/img]';
		}
		
		$content = $content. "\n\n". 
							'[img]' . XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png') . '[/img] '.
							'[size=2][color=gray]'. 
							'[url=' . XWB_plugin::getWeiboProfileLink($data['uid']) . ']' . 
							XWB_plugin::L('xwb_reply_from_2', $nickname) .
							'[/url][/color][/size]';
		
		
		return $content;
	}
	
	
	/**
	 * 根据DX设置，过滤帖子内容和拦截论坛设置禁用词
	 *
	 * @param string $message 已经转码的内容
	 * @return string 正常则返回过滤的帖子内容，否则将返回空值''，表示因为含有论坛设置禁用词而不能通过检查
	 */
	function _filterContent( $message ){
		
		$message = censor($message, null, true);
		if( is_array($message) ){
			return '';
		}else{
			$message = trim($message);
			return $message;
		}
		
	}
	
	/**
	 * 将评论回推返回的URL链接转换为
	 * @param $content
	 */
	function _replaceSinaUrlToUBB($content){
		$pattern = '/&lt;sina:link[ ]+src=&quot;([a-zA-Z0-9]+)&quot;[ a-zA-Z0-9="&;]*\/&gt;/';
		$replace = "[url=http://t.cn/\${1}]http://t.cn/\${1}[/url]";
		return preg_replace($pattern, $replace, $content);
	}
	
	/**
	 * 生成回帖插入的内容到指定tid
	 *
	 * @param integer $tid
	 * @param string $content
	 * @param integer $time 评论时间
	 * @return int 总为1
	 */
	function _prepareSqlData( $tid, $content, $time ){
		$tidInfo = $this->_mapper->tidMapGet($tid);
		//安全性过滤
		$uid = $this->_userConfig['uid'];
		$username = $this->_userConfig['username'];
		$ip = $this->_userConfig['ip'];		
		$fid =(int)$tidInfo['fid'];
		$content = mysql_real_escape_string($content);
		
		//设置一些默认值
		$subject = '';
		$isanonymous = 0;
		$bbcodeoff = 0;
		$smileyoff = 0;
		$parseurloff = 0;
		$htmlon = 0;
		$usesig = 1;
		$invisible = 0;
		$attachment = 0;
		
		
		$data = array(
			'fid' => $fid,
			'tid' => $tid,
			'first' => '0',
			'author' => $username,
			'authorid' => $uid,
			'subject' => $subject,
			'dateline' => $time,
			'message' => $content,
			'useip' => $ip,
			'invisible' => 0,
			'anonymous' => 0,
			'usesig' => 0,
			'htmlon' => 0,
			'bbcodeoff' => 0,
			'smileyoff' => 0,
			'parseurloff' => 0,
			'attachment' => 0,
		);
		
		
		//帖子cdb_posts表SQL
		$this->_postData[] = $data;
		
		//主帖表相关信息
		if( !isset($this->_threadData[$tid]) ){
			$this->_threadData[$tid] = array('lastposter' => $username, 'lastpost' => $time, 'replies'=> 1, );
		}else{
			$this->_threadData[$tid]['lastposter'] = $username;
			$this->_threadData[$tid]['lastpost'] = $time;
			$this->_threadData[$tid]['replies']++;
		}
		
		//个人回帖数
		$this->_userReplyCount++;
		$this->_userLastReplyTime = $time;
		
		//板块表相关信息
		if( $tidInfo['displayorder'] == -4 ){
			return 1;
		}
		$lastpost = mysql_real_escape_string( "{$tid}\t{$tidInfo['subject']}\t{$time}\t{$username}" );
		if( !isset($this->_forumData[$fid]) ){
			$this->_forumData[$fid] = array('lastpost' => $lastpost, 'posts' => 1, 'todayposts'=> 1, );
		}else{
			$this->_forumData[$fid]['lastpost'] = $lastpost;
			$this->_forumData[$fid]['posts']++;
			$this->_forumData[$fid]['todayposts']++;
		}
		
		$fidInfo = $this->_mapper->fidMapGet($fid);
		if( $fidInfo['type'] == 'sub' ){
			$fup = (int)$fidInfo['fup'];
			$this->_forumData[$fup] = array('lastpost' => $lastpost, 'posts' => 0, 'todayposts'=> 0, );
		}
		return 1;
	}
	
	/**
	 * 运行插入
	 */
	function execInsert(){
		if( empty($this->_postData) ){
			return false;
		}
		
		if(version_compare(XWB_S_VERSION, 2, '>=')){
			require_once libfile('function/forum');
		}
		
		
		//写入帖子表(由于分表的存在，故只能采取DX自带函数insertpost)
		foreach ( $this->_postData as $post ){
			insertpost($post);
		}
		
		//更新主帖表相关信息
		if ( false == $this->_checkPostPeriods() ){
			foreach ($this->_threadData as $tid => $tidInfo){
				$this->_db->query("UPDATE ". DB::table('forum_thread'). " SET lastposter='{$tidInfo['lastposter']}', lastpost='{$tidInfo['lastpost']}', replies=replies+{$tidInfo['replies']} WHERE tid='{$tid}'", 'UNBUFFERED');
			}
		}
		
		
		//更新个人回帖数
		if( $this->_userConfig['uid'] > 0 && $this->_userReplyCount > 0  ){
			$this->_db->query("UPDATE ". DB::table('common_member_count'). " SET posts=posts+{$this->_userReplyCount} WHERE uid='{$this->_userConfig['uid']}'", 'UNBUFFERED');
		}
		
		
		//更新板块表相关信息
		foreach ( $this->_forumData as $fid => $fidInfo ){
			if( false == $this->_checkPostPeriods() ){
				$this->_db->query("UPDATE ". DB::table('forum_forum'). " SET lastpost='{$fidInfo['lastpost']}', posts=posts+{$fidInfo['posts']}, todayposts=todayposts+{$fidInfo['posts']} WHERE fid='$fid'", 'UNBUFFERED');
			}else{
				$this->_db->query("UPDATE ". DB::table('forum_forum'). " SET todayposts=todayposts+{$fidInfo['todayposts']} ,modworks='1' WHERE fid='$fid'", 'UNBUFFERED');
			}
			
		}
		return true;
	}
	
	
	/**
	 * 检查mid的tid，并返回对应tid
	 * @param float $mid
	 * @return int 大于0表示真实的tid，否则：
	 * <pre>
	 * -1: 帖子不存在
	 * -2: 帖子已关闭
	 * -3: 帖子已放入回收站
	 * -4: 帖子所在板块不存在
	 * </pre>
	 */
	function _checkMid( $mid ){
		$return = 0;
		$tid = $this->_mapper->midMapGet( 'thread', $mid);
		
		if( $tid < 1 ){
			return -1;
		}
		
		$return = $tid;
		
		$tidInfo = $this->_mapper->tidMapGet($tid);
		if( empty($tidInfo) ){
			$return = -1;
		//检查tid是否已关闭？
		}elseif( $tidInfo['closed'] != 0 ){
			$return = -2;
		//检查tid是否已经放入回收站？
		}elseif( $tidInfo['displayorder'] < 0 ){
			$return = -3;
		}
		
		
		//检查板块是否存在？
		$fidInfo = !empty($tidInfo) ? $this->_mapper->fidMapGet($tidInfo['fid']) : array();
		if( empty($fidInfo) ){
			$return = -4;
		}
		
		if( $return < 1 ){
			$this->_mapper->tidMapDelete($tid);
		}
		
		return $return;
		
	}
	
	
	/**
	 * 检查是否需要发帖审核？
	 * @todo 目前有问题，故暂时设置为false，待以后进行修正
	 */
	function _checkPostPeriods(){
		/*
		if( null == $this->_postPeriods ) {
			$this->_postPeriods = periodscheck('postmodperiods', 0);
		}
		*/
		$this->_postPeriods = false;
		return $this->_postPeriods;
	}
	
	
}