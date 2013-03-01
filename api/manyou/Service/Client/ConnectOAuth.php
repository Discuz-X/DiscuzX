<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ConnectOAuth.php 32196 2012-11-28 02:34:36Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Connect');
Cloud::loadFile('Service_Client_OAuth');

class Cloud_Service_Client_ConnectOAuth extends Cloud_Service_Client_OAuth {

	private $_requestTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';

	private $_oAuthAuthorizeURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize';

	private $_accessTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token';

	private $_getUserInfoURL = 'http://openapi.qzone.qq.com/user/get_user_info';

	private $_addShareURL = 'http://openapi.qzone.qq.com/share/add_share';

	private $_addWeiBoURL = 'http://openapi.qzone.qq.com/wb/add_weibo';

	private $_addTURL = 'http://openapi.qzone.qq.com/t/add_t';

	private $_addPicTURL = 'http://openapi.qzone.qq.com/t/add_pic_t';

	private $_getReportListURL = 'http://openapi.qzone.qq.com/t/get_repost_list';

	// 用于请求失败或返回空时抛出的异常
	const RESPONSE_ERROR = 999;
	const RESPONSE_ERROR_MSG = 'request failed';

	/**
	 * $_instance
	 */
	protected static $_instance;

	/**
	 * getInstance
	 *
	 * @return self
	 */
	public static function getInstance($connectAppId = '', $connectAppKey = '', $apiIp = '') {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($connectAppId = '', $connectAppKey = '', $apiIp = '');
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct($connectAppId = '', $connectAppKey = '', $apiIp = '') {

		if(!$connectAppId || !$connectAppKey) {
			global $_G;
			$connectAppId = $_G['setting']['connectappid'];
			$connectAppKey = $_G['setting']['connectappkey'];
		}
		$this->setAppkey($connectAppId, $connectAppKey);
		if(!$this->_appKey || !$this->_appSecret) {
			throw new Exception('connectAppId/connectAppKey Invalid', __LINE__);
		}

		if(!$apiIp) {
			global $_G;
			$apiIp = $_G['setting']['connect_api_ip'] ? $_G['setting']['connect_api_ip'] : '';
		}

		if($apiIp) {
			$this->setApiIp($apiIp);
		}
	}

	/**
	 * connectGetRequestToken
	 * 	向Qzone发送request，请求临时token
	 *
	 * @param string $clientIp 用户的IP地址（可选）
	 *
	 * @return array
	 *  + oauth_token => *************	未授权的临时token
	 *  + oauth_token_secret => *************	未授权的临时token对应的密钥
	 */
	public function connectGetRequestToken($callback, $clientIp = '') {

		//$extra = $clientIp ? array('oauth_client_ip' => $clientIp) : array();
		$extra = array();

		$extra['oauth_callback'] = rawurlencode($callback);

		if ($clientIp) {
			$extra['oauth_client_ip'] = $clientIp;
		}

		$this->setTokenSecret('');
		$response = $this->_request($this->_requestTokenURL, $extra);

		parse_str($response, $params);
		if($params['oauth_token'] && $params['oauth_token_secret']) {
			return $params;
		} else {
			$params['error_code'] = $params['error_code'] ? $params['error_code'] : self::RESPONSE_ERROR;
			throw new Exception($params['error_code'], __LINE__);
		}

	}

	/**
	 * getOAuthAuthorizeURL
	 * 获取OAuthAuthorizeURL
	 *
	 * @param string $requestToken 通过connectGetRequestToken()获取的requestToken
	 *
	 * @return string 引导用户授权页的OAuthAuthorizeURL
	 */
	public function getOAuthAuthorizeURL($requestToken) {

		$params = array(
			'oauth_consumer_key' => $this->_appKey,
			'oauth_token' => $requestToken,
			//'oauth_callback' => rawurlencode($callback),
		);
		$utilService = Cloud::loadClass('Service_Util');
		$oAuthAuthorizeURL = $this->_oAuthAuthorizeURL.'?'.$utilService->httpBuildQuery($params, '', '&');

		return $oAuthAuthorizeURL;
	}

	private function _connectIsValidOpenid($openId, $timestamp, $sig) {

		$key = $this->_appSecret;
		$str = $openId.$timestamp;
		$signature = $this->customHmac($str, $key);
		return $sig == $signature;
	}

	/**
	 * connectGetAccessToken
	 * 获取具有Qzone访问权限的Access Token
	 *
	 * @param array $params Qzone引导用户跳转回callback带回的参数组成的数组
	 *  + oauth_token => *************	用户授权过的requestToken
	 *  + openid => *************	与QQ号码一一对应，访问OpenAPI时必需的OpenID
	 *  + oauth_signature => *************	验证openid以及来源的可靠性的签名值
	 *  + timestamp => *************	openid的timestamp
	 *  + oauth_vericode => *************	用户授权requestToken回传回来的验证码
	 * @param string $requestTokenSecret requestToken对应的requestTokenSecret
	 *
	 * @return array
	 *  + oauth_signature => *************
	 *  + oauth_token => *************	具有访问权限的access_token
	 *  + oauth_token_secret => *************	access_token的密钥
	 *  + openid => *************	与QQ号码一一对应，访问OpenAPI时必需的OpenID
	 *  + timestamp => *************	openid的时间戳
	 */
	public function connectGetAccessToken($params, $requestTokenSecret) {

		// debug 验证来源可靠
		if(!$this->_connectIsValidOpenid($params['openid'], $params['timestamp'], $params['oauth_signature'])) {
			throw new Exception('openId signature invalid', __LINE__);
		}

		if(!$params['oauth_token'] || !$params['oauth_vericode']) {
			throw new Exception('requestToken/vericode invalid', __LINE__);
		}

		$extra = array(
			'oauth_token' => $params['oauth_token'],
			'oauth_vericode' => $params['oauth_vericode'],
		);
		$this->setTokenSecret($requestTokenSecret);
		$response = $this->_request($this->_accessTokenURL, $extra);

		parse_str($response, $result);
		if($result['oauth_token'] && $result['oauth_token_secret'] && $result['openid']) {
			return $result;
		} else {
			$result['error_code'] = $result['error_code'] ? $result['error_code'] : self::RESPONSE_ERROR;
			throw new Exception($result['error_code'], __LINE__);
		}
	}

	/**
	 * connectGetUserInfo
	 * 获取登录用户信息，目前可获取用户昵称及头像信息
	 *
	 * @param string $openId 访问OpenAPI时必需的OpenID
	 * @param string $accessToken 具有访问权限的access_token
	 * @param string $accessTokenSecret access_token的密钥
	 *
	 * @return array
	 *  + nickname => *************		QQ昵称
	 *  + figureurl => *************	头像信息，尺寸30
	 *  + figureurl_1 => *************	头像信息，尺寸50
	 *  + figureurl_2 => *************	头像信息，尺寸100
	 *  + gender => *************	性别
	 */
	public function connectGetUserInfo($openId, $accessToken, $accessTokenSecret) {

		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);
		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_getUserInfoURL, $extra);

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

	private function _request($requestURL, $extra = array(), $oauthMethod = 'GET', $multi) {

		if(!$this->_appKey || !$this->_appSecret) {
			throw new Exception('appKey or appSecret not init');
		}

		if(strtoupper(CHARSET) != 'UTF-8') {
			foreach((array)$extra as $k => $v) {
				$extra[$k] = diconv($v, CHARSET, 'UTF-8');
			}
		}

		return $this->getRequest($requestURL, $extra, $oauthMethod, $multi);
	}

	private function _xmlParse($data) {

		$connectService = Cloud::loadClass('Service_Connect');
		$data = $connectService->connectParseXml($data);
		if (strtoupper(CHARSET) != 'UTF-8') {
			$data = $this->_iconv($data, 'UTF-8', CHARSET);
		}

		if(!isset($data['ret']) && !isset($data['errcode'])) {
			$data = array(
				'ret' => self::RESPONSE_ERROR,
				'msg' => self::RESPONSE_ERROR_MSG
			);
		}

		return $data;
	}

	private function _iconv($data, $inputCharset, $outputCharset) {
		if (is_array($data)) {
			foreach($data as $key => $val) {
				$value = array_map(array(__CLASS__, '_iconv'), array($val), array($inputCharset), array($outputCharset));
				$result[$key] = $value[0];
			}
		} else {
			$result = diconv($data, $inputCharset, $outputCharset);
		}
		return $result;

	}

	/**
	 * connectAddShare
	 * 在用户授权的情况下，可以以用户的名义发布一条动态（feeds）到QQ空间中，展现给好友
	 *
	 * @param string $openId 访问OpenAPI时必需的OpenID
	 * @param string $accessToken 具有访问权限的access_token
	 * @param string $accessTokenSecret access_token的密钥
	 * @param array $params 私有参数组成的数组
	 *  + title		必须，feed的标题
	 *  + url		必须，以http:// 开头的分享所在网页资源的链接
	 *  + comment	用户评论内容，也叫发表分享时的分享理由，最长40个中文字，超出部分会被截断。
	 *  + summary	所分享的网页资源的摘要内容，或者是网页的概要描述，最长80个中文字，超出部分会被截断。
	 *  + images	所分享的网页资源的代表性图片链接，请以http://开头，长度限制255字符。
	 *  + source	分享的场景，取值说明：1.通过网页 2.通过手机 3.通过软件 4.通过IPHONE 5.通过 IPAD。
	 *  + type		分享内容的类型。4表示网页；5表示视频（type=5时，必须传入playurl）。
	 *  + playurl	长度限制为256字节。仅在type=5的时候有效。
	 *  + nswb		值为1时，表示分享不默认同步到微博，其他值或者不传此参数表示默认同步到微博。
	 *
	 * @return array
	 */
	public function connectAddShare($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['title'] || !$params['url']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('title', 'url', 'comment', 'summary', 'images', 'source', 'type', 'playurl', 'nswb');

		if($params['title']) {
			$params['title'] = cutstr($params['title'], 72, '');
		}

		if($params['comment']) {
			$params['comment'] = cutstr($params['comment'], 80, '');
		}

		if($params['summary']) {
			$params['summary'] = cutstr($params['summary'], 160, '');
		}

		if($params['images']) {
			$params['images'] = cutstr($params['images'], 255, '');
		}

		if($params['playurl']) {
			$params['playurl'] = cutstr($params['playurl'], 256, '');
		}

		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addShareURL, $extra, 'POST');

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

	/**
	 * connectAddPicT
	 * 上传一张图片，并发布一条消息到腾讯微博平台上。
	 *
	 * @param string $openId 访问OpenAPI时必需的OpenID
	 * @param string $accessToken 具有访问权限的access_token
	 * @param string $accessTokenSecret access_token的密钥
	 * @param array $params 私有参数组成的数组
	 *  + content		必须，表示要发表的微博内容
	 *  + pic			必须，图片路径
	 *  + remote		boolean 标识图片为远程地址还是本地路径
	 *  + clientip		用户ip
	 *  + jing			用户所在地理位置的经度
	 *  + wei			用户所在地理位置的纬度
	 *  + syncflag		标识是否将发布的微博同步到QQ空间（0：同步； 1：不同步；），默认为0。
	 *
	 * @return array
	 */
	public function connectAddPicT($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['content'] || !$params['pic']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('content', 'pic', 'clientip', 'jing', 'wei', 'syncflag');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}
		$pic = $extra['pic'];
		unset($extra['pic']);

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addPicTURL, $extra, 'POST', array('pic' => $pic, 'remote' => $params['remote'] ? true : false));

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}


	public function connectAddT($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['content']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('content', 'clientip', 'jing', 'wei');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addTURL, $extra, 'POST');

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

	/**
	 * connectGetRepostList
	 * 获取一条微博的转播或评论信息列表
	 *
	 * @param string $openId 访问OpenAPI时必需的OpenID
	 * @param string $accessToken 具有访问权限的access_token
	 * @param string $accessTokenSecret access_token的密钥
	 * @param array $params 私有参数组成的数组
	 *  + flag		必须，标识获取的是转播列表还是点评列表。0：获取转播列表；1：获取点评列表；2：转播列表和点评列表都获取。
	 *  + rootid		必须，转发或点评的源微博的ID。
	 *  + pageflag		必须，分页标识。0：第一页；1：向下翻页；2：向上翻页。
	 *  + pagetime		必须，本页起始时间。第一页：0；向下翻页：上一次请求返回的最后一条记录时间；向上翻页：上一次请求返回的第一条记录的时间。
	 *  + reqnum		必须，每次请求记录的条数。取值为1-100条。
	 *  + twitterid		必须，翻页时使用。第1-100条：0；继续向下翻页：上一次请求返回的最后一条记录id。
	 *
	 * @return array
	 */
	public function connectGetRepostList($openId, $accessToken, $accessTokenSecret, $params) {
		if(!isset($params['flag']) || !$params['rootid'] || !isset($params['pageflag']) || !isset($params['pagetime']) || !$params['reqnum'] || !isset($params['twitterid'])) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('flag', 'rootid', 'pageflag', 'pagetime', 'reqnum', 'twitterid');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}
		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_getReportListURL, $extra, 'GET');
		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

}
