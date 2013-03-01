<?php
/**
 * 记录回复操作器
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: sitePushback2doing.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class sitePushback2doing{

	//插入的用户信息
	var $_userConfig = array();
	
	//siteBindMapper实例
	var $_mapper = null;
	
	//写入home_docomment表的预处理数据
	var $_docommentData = array();
	
	//写入home_doing表的预处理数据
	var $_doingCountData = array();
	
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
	function sitePushback2doing(){
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
		return $this->_mapper->doingMapper( $this->_mapper->midMapGet('doing') );
	}
	
	
	
	/**
	 * 运行插入预处理
	 * @param array $data 评论回推发送过来的数据
	 * @return int 结果
	 */
	function prepareInsert( $comment ){
		$doidData = $this->_checkMid($comment['mid']);
		if( empty($doidData) ){
			return -1;
		}
		$content = $this->_createContent($comment);
		if( !empty($content) ){
			$time = isset($comment['time']) ? (int)$comment['time'] : time();
			return $this->_prepareSqlData( (int)$doidData['doid'],  $content, $time );
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
		$content = $this->_replaceSinaUrlToHTML($content);
		$content = $this->_filterContent($content);
		if( empty($content) ){
			return '';
		}
		
		$content =  '<img src="' . XWB_plugin::getPluginUrl('images/bgimg/icon_logo.png') . '" />'.
							$nickname .
							'(<a href="' . XWB_plugin::getWeiboProfileLink($data['uid']) . '" target="_blank">'.
							XWB_plugin::L('xwb_weibo').
							'</a>): '. 
							$content
							;
		
		
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
	 * 将评论回推返回的URL链接转换为HTML
	 * @param $content
	 */
	function _replaceSinaUrlToHTML($content){
		$pattern = '/&lt;sina:link[ ]+src=&quot;([a-zA-Z0-9]+)&quot;[ a-zA-Z0-9="&;]*\/&gt;/';
		$replace = "<a href=\"http://t.cn/\${1}\" target=\"_blank\">http://t.cn/\${1}</a>";
		return preg_replace($pattern, $replace, $content);
	}
	
	/**
	 * 生成回帖插入的内容到指定tid
	 *
	 * @param integer $doid
	 * @param string $content
	 * @param integer $time 评论时间
	 * @return int 总为1
	 */
	function _prepareSqlData( $doid, $content, $time ){
		$content = mysql_real_escape_string($content);
		//(`upid`(总为0), `doid`(记录id), `uid`(评论者id), `username`(评论者), `dateline`(发表时间), `message`(评论内容), `ip`(ip), `grade`(总为1))
		$this->_docommentData[] = "('0', '{$doid}', '{$this->_userConfig['uid']}', '{$this->_userConfig['username']}', '{$time}', '{$content}', '{$this->_userConfig['ip']}', '1' )";
		if ( !isset($this->_doingCountData[$doid]) ){
			$this->_doingCountData[$doid]['replynum'] = 1;
		}else{
			$this->_doingCountData[$doid]['replynum']++;
		}
	}
	
	/**
	 * 运行插入
	 */
	function execInsert(){
		if( !empty($this->_docommentData) ){
			$this->_db->query("INSERT INTO ". DB::table('home_docomment'). " 
								(`upid`, `doid`, `uid`, `username`, `dateline`, `message`, `ip`, `grade`)
								VALUES ". implode(',', $this->_docommentData). ';'
			);
		}
		foreach( $this->_doingCountData as $doid => $data ){
			$this->_db->query("UPDATE ". DB::table('home_doing'). " 
								SET replynum = replynum + {$data['replynum']}
								WHERE doid = {$doid};"
			);
		}
	}
	
	
	/**
	 * 检查mid的blogid，并返回对应blogid信息数组
	 * @param float $mid
	 * @return array
	 */
	function _checkMid( $mid ){
		return $this->_mapper->doidMapGet( $this->_mapper->midMapGet('doing', $mid) );
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