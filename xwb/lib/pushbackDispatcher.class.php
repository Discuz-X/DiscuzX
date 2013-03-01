<?php
/**
 * 评论回推调度器 FOR DX
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: pushbackDispatcher.class.php 837 2011-06-16 04:18:41Z yaoying $
 *
 */
class pushbackDispatcher{
	
	/**
	 * 评论回推返回的最大id
	 * @var float
	 */
	var $_maxid = 0;
	
	/**
	 * 记录评论回推最大id是否大于当前服务器所存储的值
	 * @var bool
	 */
	var $_maxidDetect = false;

	/**
	 * 记录这次运行评论回推的时间
	 * @var integer
	 */
	var $_thistime = 0;
	
	/**
	 * 记录下次运行评论回推的时间
	 * @var integer
	 */
	var $_nexttime = 0;
	
	/**
	 * 记录评论回推的更新间隔
	 * @var integer
	 */
	var $_updatesec = 1800;
	
	/**
	 * 记录进程锁时间
	 * @var integer
	 */
	var $_processlocktime = 0;
	
	/**
	 * db实例。由于本类使用非常多的db操作，故干脆用一个属性保存
	 */
	var $_db;
	
	/**
	 * 返回的评论数据
	 * @var array
	 */
	var $_comments = array();
	
	/**
	 * 记录已经取到的评论条数
	 */
	var $_commentCount = 0;
	
	/**
	 * 记录运行状态
	 * @var integer
	 */
	var $_runStatus = 0;
	
	/**
	 * @var siteBindMapper
	 */
	var $_mapper = null;
	
	/**
	 * @return pushbackDispatcher
	 */
	function pushbackDispatcher(){
		$this->_db = XWB_plugin::getDB();
		$this->_mapper = XWB_Plugin::O('siteBindMapper');
		$this->_thistime = time();
	}
	
	/**
	 * 预处理
	 * 检查是否能够进行评论回推。如果能，则初始化评论回推操作
	 * @param bool $registerShutdown 是否将评论回推主要进程方法processMain进行register_shutdown_function？默认为是，否则必须自行启动processMain
	 * @return bool 是否能够进行评论回推
	 */
	function prepare($registerShutdown = true){
		if( 0 !== $this->_runStatus ){
			return false;
		}
		
		if( false == $this->_checkSetting() ){
			//$this->_log('预处理启动失败！原因：可能没有去后台设置相关选项？');
			return false;
		}
		
		$lockStatus = $this->_checkProcess();
		
		//测试用的无限制插入
		//$lockStatus = 2;
		
		if( $lockStatus < 0 ){
			if(-1 == $lockStatus){
				$this->_log('预处理启动失败！原因：存在进程锁');
			}
			return false;
		}
		
		$this->_updateProcessLock( $lockStatus == 2 ? true : false );
		
		if( true == $registerShutdown ){
			register_shutdown_function( array(&$this, 'processMain') );
		}
		
		$this->_runStatus = 1;
		
		return true;
	}
	
	
	/**
	 * 运行评论回推主要进程
	 */
	function processMain(){
		//没有首先运行prepare方法而直接运行此方法、或者已经运行此方法，则不再运行之
		if( 1 != $this->_runStatus ){
			return false;
		}
		$this->_runStatus = 2;
		
		ignore_user_abort(true);
		set_time_limit(80);
		
		$pushInstance = XWB_Plugin::O('pushbackCommunicator');
		$maxid = $this->_maxid == 0 ? 0 : $this->_maxid + 1;
		$res = $pushInstance->getComments($maxid, 200);
		if( empty($res['data']) ){
			$this->_updateNextProcess(3600);
			$this->_log('评论回推错误：评论回推服务器可能无法响应或者出现错误。已强制1小时后才运行评论回推！');
			return false;
		}
		
		//立刻断开无关的变量连接，以释放内存
		unset($pushInstance);
		$this->_commentCount = (int)$res['data']['count'];
		$parseResult = $this->_parsecomments($res['data']['comments']);
		//立刻断开无关的变量连接，以释放内存
		unset($res);
		
		$this->_updateNextProcess();
		if( true == $parseResult ){
			$this->_classifyComments();
			$this->_beginInsertComment();
			//$this->_log('评论回推执行记录：运行_beginInsertComment成功。');
		}
		
		return true;
	}
	
	
	/**
	 * 检查设置是否允许评论回推？
	 * @return bool
	 */
	function _checkSetting(){
		if( !XWB_plugin::pCfg('pushback_authkey') || !XWB_plugin::pCfg('is_pushback_open') ){
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * 检查进程状态
	 * @return int 检查结果：
	 * <pre> 
	 * -1：存在进程锁（正在运行中）；
	 * -2：尚未到达下次运行时间；
	 * 1：可以运行（不存在进程锁）；
	 * 2：可以运行（检测到的进程锁被判为死锁）；
	 * </pre>
	 * 
	 */
	function _checkProcess(){
		$this->_readProcessData();
		
		//检查是否存在进程锁或者进程锁不是死锁范围
		if( $this->_processlocktime > 0 && $this->_thistime < $this->_processlocktime + 300 ){
			$return = -1;
		//检查下次运行时间是否已经达到
		}elseif( $this->_nexttime > 0 && $this->_thistime < $this->_nexttime ){
			$return = -2;
		}else{
			$return = 1;
			if( $this->_processlocktime > 0 ){
				$return = 2;
			}
		}
		return $return;
	}
	
	
	/**
	 * 读取进程数据
	 * （利用DX的common_cache表模拟进程锁功能）
	 */
	function _readProcessData(){
		$query = $this->_db->query('SELECT * FROM '. DB::table('common_cache'). " WHERE `cachekey` IN ('xwb_pushback_processlock', 'xwb_pushback_nexttime', 'xwb_pushback_updatesec')");
		$res = array();
		while( $row = $this->_db->fetch_array($query) ){
			$res[$row['cachekey']] = (int)$row['cachevalue'];
		}
		
		if(isset($res['xwb_pushback_processlock'])){
			$this->_processlocktime = $res['xwb_pushback_processlock'];
		}
		if(isset($res['xwb_pushback_nexttime'])){
			$this->_nexttime = $res['xwb_pushback_nexttime'];
		}
		if(isset($res['xwb_pushback_updatesec'])){
			$this->_updatesec = $res['xwb_pushback_updatesec'];
		}
		
		$this->_maxid = XWB_plugin::pCfg('pushback_fromid');
		if( !is_numeric($this->_maxid) ){
			$this->_maxid = 0;
		}
		
	}
	
	
	/**
	 * 更新进程锁
	 * @param bool $force 强制解锁？
	 */
	function _updateProcessLock($force = false){
		if( true == $force ){
			$this->_db->query('REPLACE INTO '. DB::table('common_cache'). " (`cachekey`, `cachevalue`, `dateline`) VALUES ('xwb_pushback_processlock', '{$this->_thistime}', '{$this->_thistime}')");
		}else{
			$this->_db->query('INSERT IGNORE INTO '. DB::table('common_cache'). " (`cachekey`, `cachevalue`, `dateline`) VALUES ('xwb_pushback_processlock', '{$this->_thistime}', '{$this->_thistime}')");
		}
	}
	
	
	/**
	 * 更新下一进程信息（同时解锁）
	 * @param int|null $nextUpdateSec 下次更新时间应该在这次更新时间的多少秒之后？传入空值表示由程序决定
	 */
	function _updateNextProcess($nextUpdateSec = null){
		if( true == $this->_maxidDetect ){
			XWB_plugin::setPCfg('pushback_fromid', $this->_maxid);
		}
		$this->_determineNextUpdateTime($nextUpdateSec);
		$this->_db->query('REPLACE INTO '. DB::table('common_cache'). " (`cachekey`, `cachevalue`, `dateline`) VALUES  
		                        ('xwb_pushback_nexttime', '{$this->_nexttime}', '{$this->_thistime}'),
		                        ('xwb_pushback_lasttime', '{$this->_thistime}', '{$this->_thistime}'),
		                        ('xwb_pushback_updatesec', '{$this->_updatesec}', '{$this->_thistime}'),
		                        ('xwb_pushback_processlock', '0', '{$this->_thistime}')
		");
		if( true == $this->_maxidDetect ){
			$this->_log('评论回推更新下一进程信息：下次更新时间：'. date("Y-m-d H:i:s",$this->_nexttime). '；下次评论回推的最大id：'. $this->_maxid);
		}else{
			$this->_log('评论回推更新下一进程信息：不更新评论回推的最大id；下次更新时间：'. date("Y-m-d H:i:s",$this->_nexttime));
		}
	}
	
	/**
	 * 下次更新时间应该是多少？
	 * 若不传参，则表示让程序根据评论回推返回的信息，动态调整：
	 * 原方案假定初始要更新的数据比较少，越多更新就进行缩短；
	 * 新方案假定初始要更新的数据比较多，越少更新就进行延长。
	 * @param int|null $nextUpdateSec 下次更新时间应该在这次更新时间的多少秒之后？传入空值表示由程序决定
	 * 警告！请不要传入过小的值，否则会造成评论回推服务器认为在恶意访问。推荐至少1800秒。
	 */
	function _determineNextUpdateTime($nextUpdateSec = null){
		if( !is_numeric($nextUpdateSec) ){
			$this->_updatesec = $this->_ckeckUpdateSec($this->_updatesec);
			
			if( $this->_commentCount > 0 ){
				$pushInstance = XWB_Plugin::O('pushbackCommunicator');
				$res = $pushInstance->getCommentCount($this->_maxid + 1);
				$count = isset($res['data']['count']) ? (int)$res['data']['count'] : 0;
			}else{
				$count = 0;
			}
			
			if( $count < 100 ){
				$step = 900;  //增加15min一次
			}elseif( $count < 200 ){
				$step = 480;  //增加8min一次
			}else{
				$step = (-1) * floor($count/200) * 480 ;  //超过1次更新的，每次超过数都可获得8min的减少
			}
			$this->_updatesec = $this->_ckeckUpdateSec($this->_updatesec + $step);
		}else{
			$this->_updatesec = (int)$nextUpdateSec;
		}
		
		$this->_nexttime = $this->_thistime + $this->_updatesec;
		
	}
	
	/**
	 * 检查更新时间是否合理
	 * @param integet $sec
	 * @return integer
	 */
	function _ckeckUpdateSec($sec){
		/*
		 * 警告：
		 * 这两个值不要随意更改。
		 * 您的更改很可能会影响评论回推服务器的稳定性，从而造成别的站长甚至是自身无法使用评论回推功能。
		 * 如果因为更改此值、访问过于频繁，而被评论回推服务器认为恶意访问、进行了极为严厉的封锁，后果自负。
		 */
		static $maxSec = 5400;  //最长1.5个小时更新1次
		static $miniSec = 1800;  //最短30分钟更新1次
		if($sec >= $maxSec){
			if(rand(1, 100) <= 45){	//如果最长1.5小时更新1次，那就有45%的概率可被重置到最短时间检查
				$sec =  $miniSec;
			}else{
				$sec =  $maxSec;
			}
		}elseif($sec < $miniSec){
			$sec =  $miniSec;
		}
		return $sec;
	}
	
	/**
	 * 对获取的数据进行分析
	 * @param array &$comments 获取到的评论数据
	 * @return bool 分析结果
	 */
	function _parsecomments( &$comments ){
		$mids = array();
		foreach ($comments as $key => $comment){
			$pushbackid = $comment['id'];
			//进行maxid记录
			$this->_logMaxid($pushbackid);
			
			//评论回推有时候不会有mid，需要舍弃
			if( !isset($comment['mid']) || !is_numeric($comment['mid']) ){
				unset($comments[$key]);
				continue;
			}
			
			$comment['mid'] = (string)$comment['mid'];
			$mids[] = $comment['mid'];
			//注意，由于后面还需要查询这个mid是属于哪个类型的评论回推，因此此处和DZ的处理不同！
			//将会影响到pushbackDispatcher::_classifyComments()和pushbackDispatcher::_beginInsertComment()
			$this->_comments[$comment['mid']][] = $comment;
		}
		
		//[特殊保护]如果评论回推返回数据的最大id，并没有大于当前服务器所存储的值，则认为没有数据要更新
		if( false == $this->_maxidDetect ){
			$this->_log('评论回推数据解析错误：评论回推返回数据的最大id，并没有大于当前服务器所存储的值，故认为没有数据要更新。');
			return false;
		}
		
		if(empty($mids)){
			$this->_log('评论回推数据解析错误：评论回推返回的相关数据中，无法获取任何一个mid值');
			return false;
		}

		
		//映射mid 到 各类结果
		$mapResult = $this->_mapper->midMapper2all($mids);
		if( false == $mapResult ){
			return false;
		}
		
		return true;
	}
	
	/**
	 * 记录最大id
	 * @param $id
	 */
	function _logMaxid( $id ){
		if( $id > $this->_maxid ){
			$this->_maxid = $id;
			$this->_maxidDetect = true;
		}
	}
	
	/**
	 * 对$this->_comments按照type进行分类
	 */
	function _classifyComments(){
		$comment = array();
		foreach ( $this->_mapper->midMapGet() as $type => $mids  ){
			foreach ( $mids as $mid => $tid ){
				if( isset($this->_comments[(string)$mid]) ){
					$comment[$type][(string)$mid] = $this->_comments[(string)$mid];
				}
			}
		}
		$this->_comments = $comment;
		
	}
	
	/**
	 * 开始进行Comment插入到论坛
	 */
	function _beginInsertComment(){
		foreach( $this->_comments as $type => $commentList ){
			$className = $this->_getInsertClassName($type);
			if( empty($className) ){
				unset($this->_comments[$type]);
				continue;
			}
			$postInstance = XWB_Plugin::O($className);
			$result = $postInstance->importMapper($this->_mapper);
			if( true == $result ){
				foreach( $commentList as $mid => $comments ){
					foreach ($comments as $comment){
						$postInstance->prepareInsert($comment);
					}
					unset($this->_comments[$type][$mid]);
				}
				$postInstance->execInsert();
				$this->_log('评论回推数据插入成功！');
			}else{
				$this->_log('评论回推数据插入错误： '. $className .' 的importMapper返回失败值');
			}
			$postInstance = null;
			unset($this->_comments[$type]);
		}
	}
	
	/**
	 * 获取用户名
	 * @param $type
	 */
	function _getInsertClassName($type){
		
		if( !in_array($type, array('thread', 'blog', 'share', 'doing')) ){
			return '';
		}
		
		if( !XWB_plugin::pCfg('pushback_to_'. $type) ){
			return '';
		}
		
		return 'sitePushback2'. $type;
	}
	
	/**
	 * log记录
	 * @param string $message
	 */
	function _log($message){
		if( !defined('XWB_DEV_LOG_ALL_RESPOND') || XWB_DEV_LOG_ALL_RESPOND != true ){
			return false;
		}
		XWB_plugin::LOG("[PUSHBACK LOG]\t{$message}");
		return true;
	}
	
	
}