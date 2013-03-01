<?php
/**
 * 安装控制类 For DiscuzX
 * @author xionghui
 * @author yaoying
 * @version $Id: xwb_install.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class xwb_install {
	var $tpl_dir	= '';
	var $v			= array();
	var $hack_files = array();
	var $_sess = null;
	function xwb_install(){
		global $_xwb_install;
		$this->tpl_dir = dirname(__FILE__).'/tpl';
		$this->v = $_xwb_install;
		$this->_sess = XWB_plugin::getUser();
		$this->_chkLock();
		$this->_chkIsAdmin();
	}
	
	function install($st){
		
		$st*=1;
		if (!in_array($st,array(0,1,2,3))){
			$this->error('步骤参数错误！');
		}
		$func = 'step'.$st;
		$this->$func();
	}
	
	// 欢迎页
	function step0(){
		
		//检测安装来源
		if( isset($_SERVER['HTTP_REFERER']) && false !== strpos( $_SERVER['HTTP_REFERER'], 'operation' ) ){
			//从dz后台启动
			$this->_sess->setInfo('boot_referer', 'admincp');
		}else{
			//自启动（即在地址栏直接输入）
			$this->_sess->setInfo('boot_referer', 'self');
		}
		
		include $this->tpl_dir.'/step0.php';
	}
	
	// 步骤1 环境检查
	function step1(){
		$evnChk = $this->_envCheck();
		
		if($evnChk[0]){
			$btn_enable = 'class="btn"';
			$image_file = "sucess.png";
			$this->_sess->setInfo('check_succ_ck_name', 1);
		}else{
			$btn_enable = 'class="btn dis" onclick="return false"';
			$image_file = "icon.gif";
			$this->_sess->setInfo('check_succ_ck_name', 0);
		}
		include $this->tpl_dir.'/step1.php';
	}
	
	function step2(){
		$this->_isPassEnvCheck();
		$appkey 	= defined('XWB_APP_KEY') ? XWB_APP_KEY : '';
		$appsecret	= defined('XWB_APP_SECRET_KEY') ? XWB_APP_SECRET_KEY : '';
		include $this->tpl_dir.'/step2.php';
		exit;
	}
	
	function step3(){
		global $_G;
		$this->_isPassEnvCheck();
		if( !isset($_SERVER['REQUEST_METHOD'])  || ($_SERVER['REQUEST_METHOD'] != 'POST') ){
			$this->error('错误的提交方式！请返回重新提交！');
		}elseif( !isset($_POST['appkey']) || !isset($_POST['appsecret'])  /* || !isset($_POST['is_rsync_comment']) || !isset($_POST['sync_username']) || !isset($_POST['sync_email']) */ ){
			$this->error('输入信息不完整！请返回重新填写！');
		}
		
		$appkey 	= trim((string)$_POST['appkey']);
		$appsecret	= trim((string)$_POST['appsecret']);
        $qq         = trim((string)$_POST['qq']);
		if (!preg_match("#^[a-z0-9]+\$#sim",$appkey) || !preg_match("#^[a-z0-9]+\$#sim",$appsecret) || !preg_match("#^[1-9][0-9]{4,}\$#sim", $qq)){
			$this->error('输入的配置参数格式不对，请检查你的 appkey 与 appsecret 及 QQ 是否正确！');
		}
		
		//生成系统配置文件
		$apiurl = str_replace('/install', '', $_G['siteurl']. 'xapi.php');
		if(function_exists('fsockopen')){
			$http_adp_name = 'fsockopen';
		}else{
			$http_adp_name = 'curl';
		}
		$appCfg = "<?php\n".sprintf($this->v['app_cfg_tpl'], date("Y-m-d H:i:s "), $appkey, $appsecret, $apiurl, $http_adp_name )."\n?>";
		if (!file_put_contents(XWB_P_ROOT.'/app.cfg.php', $appCfg)){
			$this->error('无法生成系统定义配置文件，请检查文件 '. XWB_P_ROOT.'/app.cfg.php' .'的权限');
		}
		
		//更新插件设置文件
		if( false == $this->_updateSetData($appsecret) ){
			$this->error('无法更新插件设置文件，请检查文件 '. XWB_P_ROOT.'/set.data.php' .'的权限');
		}
		
		//修改文件
		$tips = array();
		$dbSt = true;    //db安装结果。根据db类的写法，肯定总为true（因为如果sql写错将db类直接fatal error报错）
		$dbTips = array();
		$dbRst = $this->_dataInit();
		$dbTips = $dbRst[0];
		$dbTips = $dbRst[1];
		
		if($dbSt){
			if ( !file_put_contents($this->v['lock_file'], date("Y-m-d H:i:s")) ){
				$tips[] = array(0,'插件已安装成功，但无法写入LOCK文件：['.$this->v['lock_file'].']，请自行删除 install/index.php ');
			}
			$image_file = "sucess.png";
			$this->_sess->delInfo('check_succ_ck_name');
			
			//根据安装来源给出完成跳转链接
			if( $this->_sess->getInfo('boot_referer') == 'admincp'){
				$installtype = 'SC_'. XWB_S_CHARSET;
				if (1.5 == XWB_S_VERSION) {
					//X1.5
					$finish_link = '../../admin.php?action=plugins&operation=plugininstall&dir=sina_xweibo&installtype='. $installtype. '&finish=1';
				}else{
					//X2
					$finish_link = '../../admin.php?action=plugins&operation=plugininstall&dir=sina_xweibo_x2&installtype='. $installtype. '&finish=1';
				}
			}else{
				$finish_link = '../../index.php';
			}
			
			
		}else{
			$image_file = "icon.gif";
		}
		
		include $this->tpl_dir.'/step3.php';
				
	}
	
	/**
	 * 更新set.data.php
	 */
	function _updateSetData($appsecret = ''){
		$setDataFile = XWB_P_ROOT.'/set.data.php';
		$oldDataExist = 0;
		if( file_exists($setDataFile) ){
			$oldData = file_get_contents($setDataFile);
			if( false !== strpos($oldData, '$__XWB_SET')  ){
				$oldDataExist = 1;
			}
		}
		
		$setData = $this->_getDefaultSetData();
		if(1 == $oldDataExist){
			include $setDataFile;
			$setData = array_merge($setData, (array)$__XWB_SET);
		}
		$setData['encrypt_key'] = $appsecret;
		
		$code = "<?php\n\r//". date('Y-m-d H:i:s')." Created\n\r\$__XWB_SET=". var_export($setData, true). ";";
		$byte = file_put_contents($setDataFile, $code);
		return $byte > 0 ? true : false;
		
	}
	
	/**
	 * 获取默认的插件设置
	 */
	function _getDefaultSetData(){
		include XWB_P_ROOT.'/set.data.default.php';
		return (array)$__XWB_SET;
	}
	
	//-----------------------------------------------------------------------
	
	function _chkLock(){
		if ( file_exists($this->v['lock_file']) ) {
			$lock_file_output = '论坛目录'. str_replace( dirname(dirname(XWB_P_DATA)), '', XWB_P_DATA ). '/xwb_install.lock';
			$this->error('你已经安装过此插件，你可以删除LOCK [ '.$lock_file_output.'] 文件后重新安装！');
		}
	}
	
	//-----------------------------------------------------------------------
	function error($msg){
		include $this->tpl_dir.'/error.php';
		exit;
	}
	//-----------------------------------------------------------------------
	/// 数据初始化 创建数据表， 初始化某些数据
	function _dataInit(){
		$tips = array();
		$db = XWB_plugin::getDB();
		$tbCfg = $this->v['create_table'];
		foreach ($tbCfg as $name=>$format){
			$tbSql = sprintf($format, DB::table($name));
			$st = $db->query($tbSql);
			$tips[] = array(1, "创建数据表 [PRE_]$name 成功");
		}
		
		foreach($this->v['prepare_sql'] as $name => $format){
			$prepareSql = sprintf($format,  DB::table($name));
			$st = $db->query($prepareSql);
		}
		
        $tmpRs = $db->query('SHOW COLUMNS FROM ' . DB::table('xwb_bind_thread') . ' LIKE "type"');
        if ( !$db->num_rows($tmpRs))
        {
            $tbAlter = $this->v['alter_table'];
            foreach ($tbAlter as $name => $format)
            {
                $alterSql = sprintf($format, DB::table($name));
                $st = $db->query($alterSql);
            }
        }
		
		return array(true,$tips);
	}
	
	//-----------------------------------------------------------------------
	///  检测是否通过了环境检查
	function _isPassEnvCheck(){
		if( $this->_sess->getInfo('check_succ_ck_name') != 1 ){
			$this->error('你还没有通过环境检查，请重新检查你的服务器环境！<br /><br />
							如果此错误是在通过环境检查后出现，说明服务器无法启动session、或session启动错误。<br />
							此情况下插件将不能正常工作。<br />
							请检查php.ini中有关session的设置（<a href="http://bbs.x.weibo.com/viewthread.php?tid=25" target="_blank">排错文档下载</a>），或者到论坛反馈。');
		}
	}
	
	//-----------------------------------------------------------------------
	// 环境检查
	function _envCheck(){
		$tips = array();
		$st = true;
		//-------------------------------------------------------------------
		$tips[] = array(1,'当前系统为: '.PHP_OS.' ');
		//各环境版本检查
		if ( $this->_verChk(PHP_VERSION,$this->v['php_ver']) ){
			$tips[] = array(1,'当前PHP版本为: '.PHP_VERSION.' ');
		}else{
			$st = false;
			$tips[] = array(0,'当前PHP版本为: '.PHP_VERSION.' 当前插件支持版本： '.$this->v['php_ver'][0].' - '. $this->v['php_ver'][1]);
		}
		
		
		
		$s_charset = str_replace('-','',strtoupper(XWB_S_CHARSET));
		if ( in_array($s_charset,$this->v['charset']) ){
			$tips[] = array(1,'当前'.XWB_S_NAME.'字符集为: '.$s_charset.' ');
		}else{
			$st = false;
			$tips[] = array(0,'当前'.XWB_S_NAME.'字符集为: '.$s_charset.' 当前插件支持字符集为： '.implode(',',$this->v['charset']));
		}
		//-------------------------------------------------------------------
		//函数依赖检查
		foreach ($this->v['func_chk'] as $func){
			if (!is_array($func)) {
				if (function_exists($func)){
					$tips[] = array(1,'函数: '.$func.' 可用 ');
				}else{
					$st = false;
					$tips[] = array(0,'函数: '.$func.' 不可用，请开启此函数 ');
				}
			}else{
				$t = false;
				foreach ($func as $fu){
					if (function_exists($fu)){ $t = true; break;}
				}
				if ($t){
					$tips[] = array(1,'函数: '.$fu.' 可用 ');
				}else{
					$st = false;
					$tips[] = array(0,'函数: '.implode(',',$func).' 都不可用，插件要求至少有一个可用 ');
				}
			}
		}
		//http适配器特别检查
		if(function_exists('fsockopen')){
			$tips[] = array(1,'函数: fsockopen 可用 ');
		}elseif(function_exists('curl_exec') && function_exists('curl_init')){
			$tips[] = array(1,'函数: curl_exec + curl_init 可用 ');
		}else{
			$st = false;
			$tips[] = array(0,'函数fsockopen、或者扩展curl中的curl_exec+curl_init都不可用，插件要求至少有一个组合可用');
		}
		//-------------------------------------------------------------------
		//文件权限检查
		foreach ($this->v['path_chk'] as $p){
			$t = $this->_writeableChk($p);
			if (!$t[0]) {$st = false;}
			$tips[] = $t[1]; 
		}
		
		//-------------------------------------------------------------------
		$db = XWB_plugin::getDB();
		if (!empty($db) && is_object($db)){
			$tips[] = array(1,'数据库链接成功！ ');
		}else{
			$st = false;
			$tips[] = array(0,'无法使用数据库句柄！ ');
		}
		//-------------------------------------------------------------------
		return array($st,$tips);
	}
	
	/// 版本检查
	function _verChk($ver,$opt){
		$ver = preg_replace('/[^0-9.]/','', $ver);
		if (empty($opt)) {return true;}
		if (!is_array($opt)) {return version_compare($ver, $opt, "=");}
		$st = true;
		if (isset($opt[0]) &&  $opt[0]!='*') {$st = $st && version_compare($ver, $opt[0], ">="); }
		if (isset($opt[1]) &&  $opt[1]!='*') {$st = $st && version_compare($ver, $opt[1], "<="); }
		return $st;	
	}
	
	function _writeableChk($p){
		$st = true;
		$ft = '目录';
		$f = XWB_S_ROOT.'/'.$p[1];
		if (strtolower($p[0])=='f'){
			if ( !$this->_fWriteable($f) ){
				$st = false;
			}
			$ft = '文件';
		}
		
		if (strtolower($p[0])=='d'){
			if ( !$this->_dWriteable($f) ){
				$st = false;
			}
		}
		
		if (empty($p[1])) $p[1]='/';
		if ($st) {
			return array($st,array(1,$ft." : ".$p[1]." 可写 "));
		}else{
			return array($st,array(0,$ft." : ".$p[1]." 无法创建或者不可写 "));
		}
	}
	
	function _dWriteable($dir) {
		if(!is_dir($dir)) {
			@mkdir($dir, 0777);
		}
		$writeable = 0;
		$testfile = "writeable_test_".time().".test";
		
		if(is_dir($dir)) {
			$byte = file_put_contents("$dir/$testfile", '111');
			if( $byte ){
				@unlink("$dir/$testfile");
				$writeable = 1;
			}else{
				$writeable = 0;
			}
		}
		return $writeable;
	}
	
	function _fWriteable($f) {
		if(!file_exists($f)) {
			@file_put_contents($f, time());
			return file_exists($f) && @unlink($f);
		}else{
			if($fp = @fopen($f, 'a+')) {
				@fclose($fp);
				return true;
			} else {
				return false;
			}
		}
	}
	
	function _chkIsAdmin(){
		if( XWB_S_IS_ADMIN != 1 ){
			$this->error('只有管理员才能执行安装程序！');
		}
	}
	
}
?>