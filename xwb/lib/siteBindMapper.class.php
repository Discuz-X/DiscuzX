<?php
/**
 * mapper映射管理器，用于存储各类mid,tid之间的绑定和映射关系
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: siteBindMapper.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class siteBindMapper{
	
	/**
	 * db实例。由于本类使用非常多的db操作，故干脆用一个属性保存
	 */
	var $_db;
	
	/**
	 * mid数组地图，方便循环的时候记住哪些mid已经查询到绑定了tid，以及该tid的类型
	 * @var array 格式 array( '类型' => array( mid => tid )  )
	 * @see siteBindMapper::midMapper2all()
	 */
	var $midMap = array();
	
	/**
	 * 帖子tid数组地图，方便循环的时候记住哪些tid已经查询过了，以及该tid的信息
	 * @var array
	 */
	var $tidMap = array();
	
	/**
	 * 板块fid数组地图，方便循环的时候记住哪些fid已经查询过了，以及该fid的信息
	 * @var array
	 */
	var $fidMap = array();
	
	
	/**
	 * 日志blogid数组地图，方便循环的时候记住哪些blogid已经查询过了，以及该blogid的信息
	 * @var array
	 */
	var $blogidMap = array();
	
	/**
	 * 分享sid数组地图，方便循环的时候记住哪些sid已经查询过了，以及该sid的信息
	 * @var array
	 */
	var $sidMap = array();
	
	
	/**
	 * 记录doid数组地图，方便循环的时候记住哪些doid已经查询过了，以及该sid的信息
	 * @var array
	 */
	var $doidMap = array();
	
	
	/**
	 * 构造函数
	 */
	function siteBindMapper(){
		$this->_db = XWB_plugin::getDB();
	}
	
	/**
	 * 对mid进行对应映射
	 * @param array $mids 要映射的mid集合
	 * @return bool 映射结果
	 */
	function midMapper2all($mids){
		if( empty($mids) || !is_array($mids) ){
			return false;
		}
		$mids = array_unique($mids);
		$query = $this->_db->query('SELECT * FROM '. DB::table('xwb_bind_thread'). " WHERE `mid` IN (". implode(',', $mids). ")");
		while( $row = $this->_db->fetch_array($query) ){
			$row['type'] = strtolower($row['type']);
			$this->midMap[$row['type']][(string)$row['mid']] = (int)$row['tid'];
		}
		return !empty($this->midMap);
	}
	
	
	
	/**
	 * 对tid进行tid和fid信息映射
	 * 根据DX1.5的说明，主题分表中存档表不可回复，故仅采取扫描主题非存档表
	 * @param array $tids 要映射的tid集合
	 * @return bool 映射成功与否
	 */
	function tidMapper($tids){
		if( empty($tids) || !is_array($tids)  ){
			return false;
		}
		$tids = array_unique($tids);
		$fidMap = array();
		$query = $this->_db->query('SELECT * FROM '. DB::table('forum_thread'). " WHERE `tid` IN (". implode(',', $tids). ')');
		while( $row = $this->_db->fetch_array($query) ){
			$this->tidMap[$row['tid']] = $row;
			$fidMap[$row['fid']] = (int)$row['fid'];
		}
		if( !empty($fidMap) ){
			$this->fidMapper($fidMap);
		}
		return !empty($this->tidMap);
	}
	
	
	/**
	 * 对fid进行fid信息映射
	 * @param array $fids 要映射的fid集合
	 * @return bool 映射成功与否
	 */
	function fidMapper( $fids ){
		if( empty($fids) || !is_array($fids)  ){
			return false;
		}
		$fids = array_unique($fids);
		$query = $this->_db->query('SELECT fid, fup, type FROM '. DB::table('forum_forum'). " WHERE `fid` IN (". implode(',', $fids). ')');
		while( $row = $this->_db->fetch_array($query) ){
			$this->fidMap[$row['fid']] = $row;
		}
		
		return !empty($this->fidMap);
	}
	
	
	/**
	 * 对blogid进行blogid信息映射
	 * @param array $blogids 要映射的blogid集合
	 * @return bool 映射成功与否
	 */
	function blogMapper($blogids){
		if( empty($blogids) || !is_array($blogids)  ){
			return false;
		}
		$blogids = array_unique($blogids);
		$query = $this->_db->query('SELECT blogid,uid FROM '. DB::table('home_blog'). " WHERE `blogid` IN (". implode(',', $blogids). ')');
		while( $row = $this->_db->fetch_array($query) ){
			$this->blogidMap[$row['blogid']] = $row;
		}
		return !empty($this->blogidMap);
	}
	
	
	/**
	 * 对sid进行sid信息映射
	 * @param array $sids 要映射的sid集合
	 * @return bool 映射成功与否
	 */
	function shareMapper($sids){
		if( empty($sids) || !is_array($sids)  ){
			return false;
		}
		$sids = array_unique($sids);
		$query = $this->_db->query('SELECT sid, uid FROM '. DB::table('home_share'). " WHERE `sid` IN (". implode(',', $sids). ')');
		while( $row = $this->_db->fetch_array($query) ){
			$this->sidMap[$row['sid']] = $row;
		}
		return !empty($this->sidMap);
	}
	
	
	/**
	 * 对记录doid进行doid信息映射
	 * @param array $doids 要映射的doid集合
	 * @return bool 映射成功与否
	 */
	function doingMapper($doids){
		if( empty($doids) || !is_array($doids)  ){
			return false;
		}
		$doids = array_unique($doids);
		$query = $this->_db->query('SELECT doid, uid FROM '. DB::table('home_doing'). " WHERE `doid` IN (". implode(',', $doids). ')');
		while( $row = $this->_db->fetch_array($query) ){
			$this->doidMap[$row['doid']] = $row;
		}
		
		return !empty($this->doidMap);
	}
	
	
	/**
	 * 获取一个midMap对应的tid
	 * @param string $type 类型，可选值'article','blog','doing','share','thread'。不传入则表示返回整个$this->midMap
	 * @param string|float $mid 如果不传入则表示返回所有$type下的数组
	 * @return integer|array tid值|$type下的数组
	 */
	function midMapGet( $type = null, $mid = null ){
		if( empty($type) ){
			return $this->midMap;
		}
		
		$type = strtolower($type);
		
		if( empty($mid) ){
			return isset($this->midMap[$type]) ? $this->midMap[$type] : array();
		}
		
		if( isset($this->midMap[$type][(string)$mid]) ){
			return $this->midMap[$type][(string)$mid];
		}else{
			return 0;
		}
	}
	
	/**
	 * 获取一个tidMap
	 * @param int $tid
	 * @return array
	 */
	function tidMapGet($tid){
		return isset($this->tidMap[$tid]) ? $this->tidMap[$tid] : array();
	}
	
	/**
	 * 获取一个fidMap
	 * @param int $fid
	 * @return array
	 */
	function fidMapGet($fid){
		return isset($this->fidMap[$fid]) ? $this->fidMap[$fid] : array();
	}
	
	/**
	 * 获取一个blogidMap
	 * @param int $blogid
	 * @return array
	 */
	function blogidMapGet($blogid){
		return isset($this->blogidMap[$blogid]) ? $this->blogidMap[$blogid] : array();
	}
	
	
	/**
	 * 获取一个sidMap
	 * @param int $sid
	 * @return array
	 */
	function sidMapGet($sid){
		return isset($this->sidMap[$sid]) ? $this->sidMap[$sid] : array();
	}
	
	
	/**
	 * 获取一个doidMap
	 * @param int $doid
	 * @return array
	 */
	function doidMapGet($doid){
		return isset($this->doidMap[$doid]) ? $this->doidMap[$doid] : array();
	}
	
	/**
	 * 删除一个tidMap
	 * @param int $tid
	 */
	function tidMapDelete($tid){
		unset($this->tidMap[$tid]);
	}
	
	
}