<?php
/**
 * XWB_plugin类。由svn41部分重写而来
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @copyright SINA INC.
 * @version $Id: core.class.php 836 2011-06-15 01:48:00Z yaoying $
 */
class XWB_plugin {
	
	/**
	 * 构造方法（不允许，将抛出fatal error）
	 */
	function XWB_plugin () {
		trigger_error('THIS CLASS CAN ONLY CALL STATIC!', 256);
	}
	
	function init(){
	}
	
	/**
	 * 获取IP地址
	 * @since 2010-8-27
	 */
	function getIP() {
		if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" )) {
			$ip = getenv ( "HTTP_CLIENT_IP" );
		} else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" )) {
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		} else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" )) {
			$ip = getenv ( "REMOTE_ADDR" );
		} else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = "unknown";
		}
		
		return ( $ip == 'unknown' || ip2long ( $ip ) === false || ip2long ( $ip ) == -1 ) ? '0.0.0.0' : $ip;
	}
	
	/**
	 * 字符集转换。mb_convert_encoding和iconv函数必须有一
	 * @uses mb_convert_encoding|iconv
	 * @since 2010-8-27
	 * @param string $source 需要转换的字符集
	 * @param string $in 转换前的编码
	 * @param string $out 转换后的编码
	 */
	function convertEncoding($source, $in, $out){
		$in	= strtoupper($in);
		$out = strtoupper($out);
		if ($in == "UTF8"){
			$in = "UTF-8";
		}
		if ($out == "UTF8"){
			$out = "UTF-8";
		}
		if( $in==$out ){
			return $source;
		}
	
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($source, $out, $in );
		}elseif (function_exists('iconv'))  {
			return iconv($in,$out."//IGNORE", $source);
		}
		return $source;
	}
	
	/**
	 * 读取一个或者多个插件设置
	 * @param mixed $key
	 */
	function pCfg($key=false){
		static $config = array();
		if( empty($config) ){
			require XWB_P_ROOT.'/set.data.php';
			$config = (array)$__XWB_SET;
		}
		
		if( $key ){
			return isset($config[$key]) ? $config[$key] : null;
		}else{
			return $config;
		}
	}
	
	/**
	 * 保存一个或者多个插件设置
	 * @param string $k
	 * @param mixed $v
	 */
	function setPCfg($k, $v=false){
		static $config = array();
		$dFile = XWB_P_ROOT.'/set.data.php';
		if( empty($config) ){
			require $dFile;
			$config = (array)$__XWB_SET;
		}
		
		$set = $k;
		if (!is_array($k)) {
			$set = array(''.$k=>$v);
		}
		foreach ($set as $kk=>$vv){
			$config[$kk] = $vv;
		}
		
		$cFormat = "<?php\n//全局插件设置选项 --------------\n%s=%s;\n?>";
		return file_put_contents($dFile, sprintf($cFormat, '$__XWB_SET',var_export($config, 1)) ) ? true : false;
	}
	
	
	/**
	 * 返回指定编码的语言。第一个参数后可再添加多个参数，用于sprintf的处理。
	 * @since 2010-8-31
	 * @param string $k 语言key
	 * @return string
	 */
	function L($k){
		static $L = array();
		if (empty($L)){
			require XWB_P_ROOT. '/lang/'. strtolower(XWB_S_CHARSET).'.php';
			$L = $_LANG;
		}
		$s = isset($L[$k]) ? $L[$k] : $s;
		
		if ( func_num_args() > 1 ){
			$p = func_get_args();
			$p[0] = $s;
			$s = call_user_func_array('sprintf',$p);
		}
		return $s;
	}
	
	
	/**
	 * 获取还原（非转义）后的  $_GET / $_POST / $_FILES / $_COOKIE / $_REQUEST / $_SERVER / $_ENV
	 * @param string $vRoute 变量路由，规则为：“<第一个字母>[：变量索引/[变量索引]]
	 * 例:	V('g:TEST/BB'); 表示获取 $_GET['TEST']['BB']
	 * V('p'); 		表示获取 $_POST
	 * V('c:var_name');表示获取 $_COOKIE['var_name']
	 * 第一个字母需要为小写
	 * @param mixed $def_v 默认值
	 * @param boolen $setVar 强制初始化值，并返回true
	 * @return mixed 当使用强制初始化值时，返回true；否则，将返回指定变量路由规则的变量。
	 */
	function V($vRoute,$def_v=NULL,$setVar=false){
		static $v = array();
		static $vKeyMap = array('C' => '_COOKIE',
								  'G' => '_GET',
								  'P' => '_POST',
								  'R' => '_REQUEST',
								  'F' => '_FILES',
								  'S' => '_SERVER',
								  'E' => '_ENV',
							);
		$vRoute = trim($vRoute);
		
		//强制初始化值
		if ($setVar){
			$v[$vRoute] = $def_v;
			return true;
		}
		
		if (!isset($v[$vRoute])){
			
			if (empty($_REQUEST)){
				$_REQUEST = array_merge ( $_GET, $_POST, $_COOKIE );
			}
			
			if ( !preg_match("#^([cgprfse])(?::(.+))?\$#sim",$vRoute,$m) || !isset($vKeyMap[strtoupper($m[1])]) ){
				trigger_error("Can't parse var from vRoute: $vRoute ", E_USER_WARNING);
				return NULL;
			}
			
			$m[1] = strtoupper($m[1]);
			$tv = $GLOBALS[ $vKeyMap[$m[1]] ];
			
			if ( empty($tv) ) {
				$v[$vRoute] = $def_v;
			}elseif ( empty($m[2]) ) {
				$v[$vRoute] =  ( ($m[1]=='F' || $m[1]=='S') && version_compare(PHP_VERSION, '5.0.0', '>=') ) ? $tv :  XWB_plugin::_magic_var($tv);
			}else{
				$vr = explode('/',$m[2]);
				foreach( $vr as $vk ){
					if (!isset($tv[$vk])){
						$tv = $def_v;
						break;
					}
					$tv = $tv[$vk];
				}
				$v[$vRoute] = ( ($m[1]=='F'  || $m[1]=='S')  && version_compare(PHP_VERSION, '5.0.0', '>=')  )  ? $tv :  XWB_plugin::_magic_var($tv);
			}
		}
		
		return $v[$vRoute];
	}
	
	/**
	 * 获取数据库实例
	 * @return object
	 */
	function &getDB(){
		return $GLOBALS[XWB_SITE_GLOBAL_V_NAME]['site_db'];
	}
	
	/**
	 * 获取WBAPI类
	 * @return weibo weibo类
	 */
	function &getWB(){
		return XWB_plugin::O('weibo');
	}
	
	/**
	 * 获取Http通讯类
	 * @param boolen $singleton 是否获取单例，默认为是
	 * @return curlHttp|fsockopenHttp
	 */
	function &getHttp($singleton = true){
		if (! defined ('XWB_HTTP_ADAPTER')) {
			if(function_exists ('curl_init')) {
				define('XWB_HTTP_ADAPTER', 'curl');
			}else{
				define('XWB_HTTP_ADAPTER', 'fsockopen');
			}
		}
		
		if($singleton){
			return XWB_plugin::O('http/'. XWB_HTTP_ADAPTER. 'Http');
		}else{
			return XWB_plugin::N('http/'. XWB_HTTP_ADAPTER. 'Http');
		}
		
	}
	
	/**
	 * 获取clientUser类别
	 * @return clientUser clientUser类
	 */
	function &getUser(){
		return XWB_plugin::O('clientUser');
	}
	
	/**
	 * 根据用户服务器环境配置，递归还原变量
	 * @param mixed $mixed
	 * @return mixed 还原后的值
	 */
	function _magic_var($mixed) {
		if( ini_get('magic_quotes_gpc') || ini_get('magic_quotes_sybase') ) {
			if(is_array($mixed)){
				return array_map(array('XWB_plugin','_magic_var'), $mixed);
			}
			return stripslashes($mixed);
		}else{
			return $mixed;
		}	
	}
	
	
	/**
	 * 获取入口文件的url（不含base url，即域名）。形如：
	 * /bbs/xwb.php?m=aaa.vvv
	 * 警告：
	 * 本函数已经废弃！特别是以前的XWB_Plugin::baseUrl(). XWB_Plugin::URL('xxx') 用法不要使用！
	 * 请使用{@link XWB_Plugin::getEntryURL()}完成！
	 * @deprecated
	 * @param string $mRoute 路由完整名，比如xwbSiteInterface.reg
	 * @param boolen|array|string $qData 查询字符串（$_GET的其它内容）
	 * @param boolen|string $entry 入口路径，而不是通过函数自动生成。末尾必须加/。
	 * @return string
	 */
	function URL($mRoute, $qData=false, $entry=false){
		exit('USING A DEPRECATED STATIC METHOD XWB_Plugin::URL()! SYSTEM HALTED!');
	}
	
	/**
	 * 本函数用于代替{@link XWB_Plugin::URL()}，生成完整的插件入口URL。形如：
	 * http://xxxx.com/bbs/xwb.php?m=aaa.vvv
	 * @param string $mRoute 路由完整名，比如xwbSiteInterface.reg
	 * @param boolen|array|string $qData 查询字符串（$_GET的其它内容）
	 * @return string
	 */
	function getEntryURL($mRoute, $qData=false){
		if($qData){
			if(is_array($qData)){
				$qData = http_build_query( $qData );
			}else{
				$qData = trim((string)$qData, "&");
			}
		}else{
			$qData = '';
		}
		//--------------------------------------------------------------
		$rStr	= XWB_R_GET_VAR_NAME . '=' . $mRoute;
		$qData	= empty($qData) ?  $rStr  : $rStr . "&" . $qData;
		return  XWB_Plugin::siteUrl(). 'xwb.php' ."?" . $qData;
	}
	
	
	/**
	 * XWB_plugin::redirect($mRoute,$type=1);
	 * 重定向 并退出程序
	 * @param string $mRoute
	 * @param int $type 1 : 默认 ， 内部模块跳转 ,2 : 给定模块路由，通过浏览器跳转 ,3 : 给定URL
	 * @return null 调用该函数，程序将自动退出。
	 */
	function redirect($mRoute,$type=1){
		switch ($type){
			case 1:
				XWB_plugin::M($mRoute);
				break;
			case 2:
				//Note: HTTP/1.1 requires an absolute URI as argument to » Location: including the scheme, hostname and absolute path, but some clients accept relative URIs
				$url = XWB_plugin::getEntryURL($mRoute);
				header("Location: ".$url);
				break;
			case 3:
				header("Location: ".$mRoute);
				break;	
			default:
				trigger_error("Error redirect type: [ $mRoute ] ", E_USER_ERROR);
				break;
		}
		exit;
	}
	
	/**
	 * 获取HACK FILE 的路径（调用内部静态方法_getIncFile）
	 * @param string $hRoute hack文件名称
	 */
	function hackFile($hRoute){
		return XWB_plugin::_getIncFile($hRoute, 'hack');
		
	}
	
	/**
	 * 发送403 http错误，并输出指定的文本
	 * @param string $info 需要输出的文本内容
	 */
	function deny($info=''){
		header("HTTP/1.1 403 Forbidden");
		exit('Access deny: '.$info);
	}
	
	/**
	 * 出现错误的时候，显示错误模板
	 * @param string $info 错误信息
	 * @param bool $deny 是否发送403 http错误？是则表示调用本类静态方法deny
	 * @param array $extra 其他详细信息，用于debug显示（现在暂时无用）
	 */
	function showError( $info = '', $deny = false, $extra = array() ){
		if( true == $deny ){
			XWB_plugin::deny($info);
		}else{
			include XWB_P_ROOT.'/tpl/xwb_show_error.tpl.php';
		}
		exit();
	}
	
	
	/**
	 * 获取当前请求的 route 名称
	 * @param boolen $is_acc 是否以数组返回。默认为否
	 * @return string|mixed
	 */
	function getRequestRoute( $is_acc = false ){
		$m = XWB_plugin::V("g:".XWB_R_GET_VAR_NAME);
		$m = !empty($m) ? $m : XWB_R_DEF_MOD;
		
		if (!$is_acc) {
			return $m;
		}else{
			$r = XWB_plugin::_parseRoute($m);
			return array('path'=>$r[1], 'class'=>$r[2], 'function'=>$r[3]);
		}
	}
	
	/**
	 * 处理外部的请求
	 * @param boolen $halt 执行完毕是否终止。默认为否
	 */
	function request($halt=false){
		XWB_plugin::M(XWB_plugin::getRequestRoute());
		if ($halt) exit;
	}
	
	/**
	 * 执行指定模块的方法
	 * @param string $mRoute 符合本框架的模块方法名称
	 */
	function M($mRoute){
		$r = XWB_plugin::_parseRoute($mRoute);
		if (substr($r[3],0,1)=='_'){
			trigger_error("Module method: [ ".$r[3]." ]  start with '_' is private !  ", E_USER_ERROR);
		}
		
		$p = func_get_args();
		array_splice($p, 1, 0, array('mod',true));
		$m = call_user_func_array(array('XWB_plugin','_cls'),$p);
		
		if (!is_object($m)){
			trigger_error("Can't instance mRoute  [ $mRoute ] ", E_USER_ERROR);
		}
		
		if (!method_exists($m,$r[3])){
			trigger_error("Can't find method  [ ".$r[3]." ]  in  [ ".$r[2]." ] ", E_USER_ERROR);
		}
		
		// call action 
		if ($r[3]!=$r[2]) { $m->$r[3]();}
	}
	
	
	/**
	 * 根据类路由 和 类初始化参数获取一个单例
	 * 用法和function &N($oRoute)一样。请参见其注释
	 * @param $oRoute 类路由，规则与模块规则一样
	 * @return object 类实例 
	 */
	function &O($oRoute){
		$p = func_get_args();
		array_splice($p, 1, 0, array('cls',true));
		$o = call_user_func_array(array('XWB_plugin','_cls'),$p);
		return $o;
	}
	
	
	/**
	 * XWB_plugin::N($oRoute);
	 * 根据类路由 和 类初始化参数获取一个类实例
	 * 第二个以及以后的参数 将传递给类的构造函数
	 * 如： XWB_plugin::N('test/classname','a','b'); 实例化时执行的是test目录下的 new classname('a','b');
	 * @param string $oRoute 类路由，规则与模块规则一样
	 * @return object 类实例 
	 */
	function &N($oRoute){
		$p = func_get_args();
		array_splice($p, 1, 0, array('cls',false));
		return call_user_func_array(array('XWB_plugin','_cls'),$p);
	}
	
	/**
	 * 创建并返回一个类
	 * 第四个以及以后的参数 将传递给类的构造函数
	 * @param string $iRoute 类路由，规则与模块规则一样
	 * @param string $type 类型
	 * @param boolen $is_single 是否单例
	 * @return object
	 */
	function &_cls($iRoute,$type,$is_single){
		static $clsArr = array();
		$iRoute = trim($iRoute);
		$type 	= trim($type);
		
		if ( $is_single && isset($clsArr[$iRoute]) &&  is_object($clsArr[$iRoute]) ){
			return $clsArr[$iRoute];
		}else{
			
			$cFile = XWB_plugin::_getIncFile($iRoute,$type);
			require_once($cFile);
			$r = XWB_plugin::_parseRoute($iRoute);
			$class	= $r[2];
			$func	= $r[3];
			
			if(!class_exists ($class)){
				trigger_error("class [ $class ]  is not exists in file [ $cFile ] ", E_USER_ERROR);
			}
			$p = func_get_args();
			array_splice($p, 0, 3);
			if(!empty($p)){
				$prm = array();
				foreach($p as $i=>$v){
					$prm[] = "\$p[".$i."]";
				}
				eval("\$retClass =& new ".$class." (".implode(",",$prm).");");
				if ( $is_single ) { $clsArr[$iRoute] =& $retClass; }
				return $retClass;
			}else{				
				if ( $is_single ) {
					$clsArr[$iRoute] =& new $class;
					return $clsArr[$iRoute];
				}else{
					$retClass =& new $class;
					return $retClass;
				}
			}
		}
	}
	
	
	/**
	 * 执行一个函数
	 * 第二个以及以后的参数 将传递给函数
	 * @param string $fRoute 函数名
	 * @return mixed 函数结果
	 */
	function F($fRoute){
		static $_fTree = array();		
		$p = func_get_args();
		array_shift($p);
		
		if(isset($_fTree[$fRoute])){
			return call_user_func_array($_fTree[$fRoute],$p);
		}
		
		$cFile = XWB_plugin::_getIncFile($fRoute,'func');
		require_once($cFile);
		
		$pp = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim",$fRoute,$m);
		if (!$pp) { trigger_error("fRoute : [ $fRoute  ] is  invalid ", E_USER_ERROR);  return false;}
		$_fTree[$fRoute] = empty($m[3])?$m[2]:$m[3];
		if ( !function_exists($_fTree[$fRoute]) ) {
			trigger_error("Can't find function [ {$_fTree[$fRoute]} ] in file [ $cFile ]", E_USER_ERROR); 
		}
		return call_user_func_array($_fTree[$fRoute],$p);
	}
	
	/**
	 * 获取插件目录所在的完整URL访问地址
	 * @param string $path 附加URL字符串，前面不能加/
	 * @return string
	 */
	function getPluginUrl($path=''){
		return XWB_plugin::siteUrl().XWB_P_DIR_NAME."/".$path;
	}
	
	/**
	 * 获取符合框架目录的一个文件的路径
	 * @param string $fRoute 文件名
	 * @param string $type 类型，可选：cls, mod, func, hack
	 * @return sring 文件路径
	 */
	function _getIncFile($fRoute, $type='cls'){
		
		static $fileMap = array();
		$fileId = (string)$fRoute. (string)$type;
		if( isset($fileMap[$fileId]) ){
			return $fileMap[$fileId];
		}
		
		if ( !XWB_plugin::_chkPath($fRoute) ){
			trigger_error("file route: [ $fRoute  - $type  ] is  invalid ", E_USER_ERROR);
		}
		
		$m = XWB_plugin::_parseRoute($fRoute);
		$fp = $m[1].$m[2];
		
		$type = strtolower($type);
		$f = array(
				   'cls'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.class.php',
				   'mod'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.mod.php',
				   'func'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.function.php',
				   'hack'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "hack" . DIRECTORY_SEPARATOR . $fp . '.hack.php'
		);
		if ( !isset($f[$type]) ){
			trigger_error("file type: [ $type  ] is  invalid ", E_USER_ERROR);
		}
		if ( !file_exists($f[$type]) ){
			trigger_error("file:[ ".$f[$type]." ] not exists  ", E_USER_ERROR);
			
		}
		$fileMap[$fileId] = $f[$type];
		return $f[$type];
		
	}
	
	/**
	 *  检查ROUTE的有效性
	 *  @return boolen
	 */
	function _chkPath($v){
		return count(explode("..",$v))== 1 && preg_match("#^[a-z_][a-z0-9_/\.]*\$#sim",$v);
	}
	
	/**
	 * 解析ROUTE
	 * @param string route名称
	 * @return array 解析结果数组
	 */
	function _parseRoute($route){
		static $routeMap = array();
		
		$route = trim($route);
		if( isset($routeMap[$route]) ){
			return $routeMap[$route];
		}
		
		$p = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim",$route,$m);
		if (!$p) { trigger_error("route : [ $route  ] is  invalid ", E_USER_ERROR);  return false;}
		if (empty($m[3])) $m[3] = XWB_R_DEF_MOD_FUNC;
		$routeMap[$route] = $m;
		return $m;
	}
	
	/**
	 * 获取当前登录的用户是否已绑定SINA帐号
	 * @return boolen
	 */
	function isUserBinded(){
		$bInfo = XWB_plugin::getBindInfo();
		return (empty($bInfo) || !is_array($bInfo)) ? false : true;
	}
	
	/**
	 * 获取当前登录用户状态的绑定信息
	 * 若当前没有用户登录，则返回false
	 * 
	 * @param mixed $key 键值。若传入false，则表示返回所有绑定信息
	 * @param mixed $def 默认值。若没有值返回，则返回该默认值
	 * @param bool $apiUpdate
	 * @param bool $turn
	 */
	function getBindInfo($key=false, $def=null, $apiUpdate = false, $turn = false){
		static $rst = '-1';   //由于服务器可能返回false或者null，故只能用这个作标识
		if (!XWB_S_UID) {return false;}
		if( $rst === '-1' ){
			$rst = XWB_plugin::getBindUser(XWB_S_UID, 'site_uid', $apiUpdate, $turn); //远程API
		}
		if ($key===false){
			return empty($rst) ? array() :  $rst;
		}else{
			return isset($rst[$key]) ? $rst[$key] : $def;
		}
	}

	/**
	 * 是否使用指定的请求方法？
	 * @param string $method 请求方法（比如GET、HEAD、POST等）
	 * @return bool
	 */
	function isRequestBy($method){
		if( strtoupper($method) !== $_SERVER['REQUEST_METHOD']){
			return false;
		}else{
			return true;
		}
	}
	
	
	/**
	 * 获取当前脚本的完整url路径。末尾有/
	 */
	function siteUrl(){
		if( defined('XWB_S_SITEURL') ){
			return XWB_S_SITEURL;
		}
		return XWB_plugin::baseUrl(). XWB_plugin::pathUrl();
	}
	
	
	/**
	 * 获取站点的BASE URL。末尾没有/
	 * @return string
	 */
	function baseUrl(){
		static $url = '';
		
		if( empty($url) ){
			//当自动生成出错的时候，可让用户通过常量XWB_S_BASEURL自定义。末尾不需加/
			if( defined('XWB_S_BASEURL') ){
				$url = XWB_S_BASEURL;
			}else{
				$url  = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http' ). 
					'://'. ( isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : 
								(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') 
					);
			}
		}

		return $url;
	}
	
	
	/**
	 * 获取url中的路径。比如：
	 * <pre>
	 * http://www.aaa.cn 将返回 /
	 * http://www.aaa.cn/bbs 将返回 /bbs/
	 * </pre>
	 * 在本插件中，由于{@link XWB_Plugin::URL()}的存在，不能用于插件本身的伪静态（实际上插件没有伪静态）
	 * 本方法来自于Yii Framework的CHttpRequest::getScriptUrl()，依据BSD协议{@link http://www.yiiframework.com/license/}进行更改使用
	 * @author Qiang Xue <qiang.xue@gmail.com>
	 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
	 * @license http://www.yiiframework.com/license/
	 * @link http://www.yiiframework.com/
	 */
	function pathUrl(){
		static $_scriptUrl = '';
		
		if( '' != $_scriptUrl ){
			return $_scriptUrl;
		}
		$scriptName=basename($_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_NAME'])===$scriptName){
			$_scriptUrl=$_SERVER['SCRIPT_NAME'];
		}else if(basename($_SERVER['PHP_SELF'])===$scriptName){
			$_scriptUrl=$_SERVER['PHP_SELF'];
		}else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName){
			$_scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
		}else if(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false){
			$_scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
		}else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0){
			$_scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
		}else{
			$_scriptUrl='/';
		}
		
		$_scriptUrl = trim($_scriptUrl, '/');
		$_scriptUrl = (false !== ($offset = strrpos($_scriptUrl, '/'))) ? substr($_scriptUrl, 0, $offset) : '/';
		if( $_scriptUrl != '/' ){
			$_scriptUrl = '/'. $_scriptUrl . '/';
		}
		
		return $_scriptUrl;
	}
	
	
	/**
	 * 记录log日志
	 * @param string $msg 内容
	 * @param string $logName 存放log的完整文件路径。若不指定，则存放于常量XWB_P_DATA的指定目录
	 * @param bool $halt 无法记录时候是否终止整个脚本运行？默认为false
	 * @return int
	 */
	function LOG($msg, $logName='log', $halt = false){
		$logFile = strpos($logName,'/') === false ? XWB_P_DATA.'/xwb_'.$logName.'.php' : $logName;
		$msgPre = '';
		if (!file_exists($logFile)){
			$msgPre = "\r\n<?php  die('access deny!'); ?> \r\n\r\n";
		}
		
		$msg = $msgPre. sprintf("%s\t%s\r\n",date("Y-m-d H:i:s"),$msg);
		$mode = 'ab';
		
		$fp = @fopen($logFile, $mode);
		if( $fp ){
			@flock($fp, LOCK_EX);
			$len = @fwrite($fp, $msg);
			@flock($fp, LOCK_UN);
			@fclose($fp);
			return $len;
		}else{
			if( true == $halt ){
				exit("Can not open file $logFile !");
			}else{
				return 0;
			}
		}
	}
	
	
	/**
	 * 生成统计上报url（当$html参数为true时，可使用返回的内容，通过客户端进行上报）
	 * @param string $type stat类型
	 * @param array $args stat参数
	 * @param bool 生成html？默认为否
	 * @param bool 是否产生random？默认为是
	 * @return string
	 */
	function statUrl($type, $args = array(), $html = false, $random = true ){
		if( defined('XWB_P_STAT_DISABLE') ){
			return '';
		}
		
		$statUrl = 'http://beacon.x.weibo.com/a.gif';
		
		//stat参数公用部分添加
		$args['pjt'] = XWB_P_PROJECT;
		$args['dsz'] = XWB_S_VERSION;
		$args['ver'] = XWB_P_VERSION;
		$args['xt'] = $type;
		$args['akey'] = isset($args['akey']) ? $args['akey'] : XWB_APP_KEY;
		$args['ip'] = XWB_plugin::getIP();
		//新浪用户uid，最好强制传值，否则会异步计算错误
		if( !isset($args['uid']) ){
			$args['uid'] = XWB_plugin::getBindInfo("sina_uid");
		}
		$args['uid'] = ( !is_numeric($args['uid']) || 1 > $args['uid'] ) ? '' : $args['uid'];
		if( true === $random ){
			$args['random'] = rand(1,999999);
		}
		
		$statUrl .= '?'. http_build_query($args);
		
		if ( defined('XWB_P_DEBUG') && true == XWB_P_DEBUG ){
			$logmsg = "上报的URL为：". $statUrl;
			XWB_plugin::LOG( $logmsg, 'statRecord', false );
		}
		
		if( false == $html ){
			return $statUrl;
		}else{
			return '<img src="'. $statUrl. '" style="display:none" />';
		}
		
	}
	
    /**
	 * 根据UID获取用户绑定关系
	 *
	 * @param int $id 用户ID或新浪ID
	 */
    function getBindUser($id, $type='site_uid', $apiUpdate = false, $turn = true)
    {
        $db = XWB_plugin::getDB();
        $field = ('site_uid' == $type) ? 'uid' : 'sina_uid';
        $id = mysql_escape_string($id);
        $sql = "SELECT * FROM ".DB::table('xwb_bind_info')." WHERE `" . $field . "`='".$id."'";
        $rst = $db->fetch_first($sql);
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        if($turn && $switch) {
            $api = XWB_plugin::N('apixwb');
            if( !$rst) {
                $rst = $api->getBindUser($id, $type);
                if($rst && !empty($rst['uid']) && !empty($rst['sina_uid'])) {
                    //若返回的关系中微博ID在本地已有另一个绑定关系，直接更新本地
                    $query = "INSERT INTO " . DB::table('xwb_bind_info') . " (`uid`,`sina_uid`,`token`,`tsecret`,`profile`) VALUES('{$rst['uid']}','{$rst['sina_uid']}','{$rst['access_token']}','{$rst['token_secret']}','[]') ON DUPLICATE KEY UPDATE `uid`=VALUES(`uid`)";
                    $db->query($query);
                    $rst = array('uid' => $rst['uid'], 'sina_uid' => $rst['sina_uid'], 'token' => $rst['access_token'], 'tsecret' => $rst['token_secret'], 'profile' => '[]');
                } else {
                    $rst = false;
                }
            } elseif($apiUpdate) {
                $api->updateBindUser($rst['uid'], $rst['sina_uid'], $rst['token'], $rst['tsecret'], $rst['nickname']);
            }
        }
        return $rst;
    }

    /**
	 * 根据UID或新浪ID获取用户绑定关系
	 *
	 * @param int $id 用户ID或新浪ID
	 */
    function getBUById($uid, $sina_uid, $turn = true)
    {
        $db = XWB_plugin::getDB();
        $uid = mysql_escape_string($uid);
        $sina_uid = mysql_escape_string($sina_uid);
        $sql = "SELECT * FROM ".DB::table('xwb_bind_info')." WHERE `uid`='".$uid."' OR `sina_uid`='".$sina_uid."'";
        $rst = $db->fetch_first($sql);
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        if($turn && $switch && !$rst) {
            $api = XWB_plugin::N('apixwb');
            $rst = $api->getBindUser($uid, 'site_uid');
            if( !$rst) $rst = $api->getBindUser($sina_uid, 'sina_uid');
            if($rst && !empty($rst['uid']) && !empty($rst['sina_uid'])) {
                //若返回的关系中微博ID在本地已有另一个绑定关系，直接更新本地
                $query = "INSERT INTO " . DB::table('xwb_bind_info') . " (`uid`,`sina_uid`,`token`,`tsecret`,`profile`) VALUES('{$rst['uid']}','{$rst['sina_uid']}','{$rst['access_token']}','{$rst['token_secret']}','[]') ON DUPLICATE KEY UPDATE `uid`=VALUES(`uid`)";
                $db->query($query);
                $rst = array('uid' => $rst['uid'], 'sina_uid' => $rst['sina_uid'], 'token' => $rst['access_token'], 'tsecret', $rst['token_secret'], 'profile' => '[]');
            } else {
                $rst = false;
            }
        }
        return $rst;
    }

    /**
	 * 根据一组UID获取用户绑定关系
	 *
	 * @param int $uids 用户ID
	 * @param bool $turn 是否也进行远程api查询？默认为否
	 */
    function getBatchBindUser($uids, $turn = false)
    {
    	$uids = array_unique($uids);
    	foreach($uids as $k => $v){
    		if(!is_numeric($v)){
    			unset($uids[$k]);
    		}
    	}
    	
    	$rt = $dbUids = $tmpRt = array();
    	if(empty($uids)){
    		return $rt;
    	}
        
        $db = XWB_plugin::getDB();
        $sql = "SELECT * FROM ".DB::table('xwb_bind_info')." WHERE `uid` IN(" . implode(',', (array)$uids). ")";
        $rs = $db->query($sql);
        if($rs) {
            while($row = $db->fetch_array($rs)) {
                $rt[] = $row;
                $dbUids[] = $row['uid'];
            }
        }
        
        $diffUids = array_unique(array_diff($uids, $dbUids));
        $switch = $turn ? XWB_plugin::pCfg('switch_to_xweibo') : 0;

        if($switch && !empty($diffUids))
        {
            $api = XWB_plugin::N('apixwb');
            $rst = $api->getBatchBindUser(implode(',', $diffUids));
            if($rst && is_array($rst)) {
                $values = array();
                foreach($rst as $row) {
                	if(!isset($row['uid']) || $row['uid'] < 1){
                		continue;
                	}           	
                    $tmpRt[] = array('uid' => $row['uid'], 'sina_uid' => $row['sina_uid'], 'token' => $row['access_token'], 'tsecret', $row['token_secret'], 'profile' => '[]');
                    $values[] = "('{$row['uid']}','{$row['sina_uid']}','{$row['access_token']}','{$row['token_secret']}','[]')";
                }
                if(!empty($values)){
               		$values = implode(',', $values);
                	$query = "INSERT INTO " . DB::table('xwb_bind_info') . " (`uid`,`sina_uid`,`token`,`tsecret`,`profile`) VALUES{$values} ON DUPLICATE KEY UPDATE `uid`=VALUES(`uid`)";
                	$tmpRs = $db->query($query);
                	$rt = array_merge($rt, $tmpRt);
                }
            }
        }
        return $rt;
    }

    /**
	 * 根据UID删除用户绑定关系
	 *
	 * @param int $uid 用户ID
	 */
    function delBindUser($uid, $turn = true)
    {
        $db = XWB_plugin::getDB();
        $uid = mysql_escape_string($uid);
        $sql = 'DELETE FROM ' . DB::table('xwb_bind_info') . ' WHERE `uid`=' . $uid;
        $rst = $db->query($sql, 'UNBUFFERED');
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        if($turn && $switch && $rst) {
            $api = XWB_plugin::N('apixwb');
            $api->delBindUser($uid);
        }
        return $rst;
    }

    /**
	 * 更新用户绑定关系
	 *
	 */
    function updateBindUser($site_uid, $sina_uid, $access_toke, $token_secret, $nickname = false, $turn = true)
    {
        if($site_uid < 1){
    		return false;
    	}
        $db = XWB_plugin::getDB();
        $site_uid = mysql_escape_string($site_uid);
        $sina_uid = mysql_escape_string($sina_uid);
        $access_toke = mysql_escape_string($access_toke);
        $token_secret = mysql_escape_string($token_secret);
        
        $sql = "UPDATE ". DB::table('xwb_bind_info'). " SET `token`='". $access_toke. "', `tsecret`='". $token_secret. "' WHERE `uid`='".$site_uid."' AND `sina_uid`='" . $sina_uid . "'";
        $rst = $db->query($sql, 'UNBUFFERED');
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        if($turn && $switch && $rst) {
            $api = XWB_plugin::N('apixwb');
            $api->updateBindUser($site_uid, $sina_uid, $access_toke, $token_secret, $nickname);
        }
        return $rst;
    }

    /**
	 * 新增用户绑定关系
	 *
	 */
    function addBindUser($site_uid, $sina_uid, $access_toke, $token_secret, $nickname = false, $turn = true)
    {
    	if($site_uid < 1){
    		return false;
    	}
        $db = XWB_plugin::getDB();
        $site_uid = mysql_escape_string($site_uid);
        $sina_uid = mysql_escape_string($sina_uid);
        $access_toke = mysql_escape_string($access_toke);
        $token_secret = mysql_escape_string($token_secret);        
        
        $sql = "INSERT INTO " . DB::table('xwb_bind_info') . " (`uid`,`sina_uid`,`token`,`tsecret`,`profile`) VALUES('{$site_uid}','{$sina_uid}','{$access_toke}','{$token_secret}','[]')";
        $rst = $db->query($sql, 'UNBUFFERED');
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        if($turn && $switch && $rst) {
            $api = XWB_plugin::N('apixwb');
            $api->updateBindUser($site_uid, $sina_uid, $access_toke, $token_secret, $nickname);
        }
        return $rst;
    }
    
	/**
	 * 获取新浪微博或者xweibo的个人主页link
	 * @param bigint $sina_uid
	 * @return string
	 */
	function getWeiboProfileLink($sina_uid = 0){
		$xweibourl = rtrim(XWB_plugin::pCfg('baseurl_to_xweibo'), '/');
		if(XWB_plugin::pCfg('switch_to_xweibo') && !empty($xweibourl)){
			$xweibourl_ta = $xweibourl. '/index.php?m=ta&id='. $sina_uid;
		}else{
			$xweibourl_ta = 'http://weibo.com/'. $sina_uid;
		}
		return $xweibourl_ta;
	}
    
}

