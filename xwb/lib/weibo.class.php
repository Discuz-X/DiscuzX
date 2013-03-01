<?php

require_once dirname(__FILE__). "/ns_oauth.class.php";

/**
 * 微博api操作类
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @since 2010-06-08
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id: weibo.class.php 832 2011-06-07 00:58:24Z yaoying $
 *
 */
class weibo
 {

	var $http;
	var $token = null;
	var $shal_method;
	var $consumer;
	var $storage;
	var $format = 'json';
	var $error;
	
	var $is_exit_error = true;
	var $last_req_url = '';
	//记录一个php周期中。所出现的request错误次数。
	var $req_error_count = 0;
	
	/**
	 * 构造函数
	 *
	 * @param @oauth_token
	 * @param @oauth_token_secret
	 * @return
	 */
	function weibo($oauth_token = NULL, $oauth_token_secret = NULL)
	{
        $this->sha1_method = new ns_OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new ns_OAuthConsumer(XWB_APP_KEY, XWB_APP_SECRET_KEY);
		$this->setConfig();
		$this->http = XWB_plugin::getHttp(false);
	}
	
	/// 指定USER TOKEN
	function setTempToken($oauth_token, $oauth_token_secret){
		$this->token = new ns_OAuthConsumer($oauth_token, $oauth_token_secret);
	}
	
	/**
	 * 设置
	 */
	function setConfig()
	{	
		/// 用户实例
        $sess = XWB_plugin::getUser();
		$tk = $sess->getToken();
		//var_dump($tk);exit;
		if (!empty($tk['oauth_token']) && !empty($tk['oauth_token_secret'])) {
            $this->token = new ns_OAuthConsumer($tk['oauth_token'], $tk['oauth_token_secret']);
        }
	}
	
	/**
	 * 设置错误提示
	 *
	 * @param string $error
	 * @return unknown
	 */
	function setError($error)
	{
		$errmsg = isset($error['error']) ? strtolower($error['error']) : 'UNDEFINED ERROR';
		if (strpos($errmsg, 'token_')) {
			$msg = XWB_plugin::L('xwb_token_error');
		}elseif (strpos($errmsg, 'user does not exists')) {
			$msg = XWB_plugin::L('xwb_user_not_exists');
		} elseif (strpos($errmsg, 'target weibo does not exist')) {
			$msg = XWB_plugin::L('xwb_target_weibo_not_exist');
		} elseif (strpos($errmsg, 'weibo id is null')) {
			$msg = XWB_plugin::L('xwb_weibo_id_null');
		} elseif (strpos($errmsg, 'system error')) {
			$msg = XWB_plugin::L('xwb_system_error');
		} elseif (strpos($errmsg, 'consumer_key')) {
			$msg = XWB_plugin::L('xwb_app_key_error');
		} elseif (strpos($errmsg, 'ip request')) {
			$msg = XWB_plugin::L('xwb_request_reach_api_maxium');
		} elseif (strpos($errmsg, 'update comment')) {
			$msg = XWB_plugin::L('xwb_comment_reach_api_maxium');
		} elseif (strpos($errmsg, 'update weibo')) {
			$msg = XWB_plugin::L('xwb_update_reach_api_maxium');
		} elseif (strpos($errmsg, 'high level')){
			$msg = XWB_plugin::L('xwb_access_resource_api_denied');
		} else {
			$msg = XWB_plugin::L('xwb_system_error');
		}
		
		//DEBUG 日志
		$req_url = $this->last_req_url;
		XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$this->req_error_count}\t{$msg}\t{$req_url}\tERROR ARRAY:\r\n".print_r($error, 1));
		//DEBUG END
		
		if (!$this->is_exit_error) {return false;}
		
		if( 'utf8' != strtolower(XWB_S_CHARSET) ){
			$msg = XWB_plugin::convertEncoding( $msg, XWB_S_CHARSET, 'UTF-8' );
		}
		XWB_plugin::showError($msg);
		
	}
	
	/**
	 * 获取错误提示
	 *
	 * @param $useType string
	 * @return unknown
	 */
	function getError($useType = 'array')
	{
		if ('array' == $useType) {
			return json_decode($this->error, true);
		}
		return $this->error;
	}


	//数据集(timeline)接口

	/**
	 * 获取最新更新的公共微博消息
	 *
	 * @param $useType string
	 * @return array|string
	 */
	 function getPublicTimeline($useType = true)
	 {
		$url = XWB_API_URL.'statuses/public_timeline.'.$this->format;
		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户所关注用户的最新微博信息
	 *
	 * @param $count int
	 * @param page int
	 * @param since_id int
	 * @param max_id int
	 * @return array|string
	 */
	 function getHomeTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/home_timeline.'.$this->format;
		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户所关注用户的最新微博信息
	 *
	 * @param $count int
	 * @param $page int
	 * @param $since_id int
	 * @param $max_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function getFriendsTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		return $this->getHomeTimeline($count, $page, $since_id, $max_id, $useType);
	 }


	/**
	 * 获取用户发布的微博信息列表
	 *
	 * @param $id int|string
	 * @param $user_id int
	 * @param $name string
	 * @param $since_id int
	 * @parmas $max_id int
	 * @param $count int
	 * @param $page int
	 * @param $useType string
	 * @return array|string
	 */
	 function getUserTimeline($id = null, $user_id = null, $name = null, $since_id = null, $max_id = null, $count = null, $page = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/user_timeline/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/user_timeline.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	 /**
	  * 获取@当前用户的微博列表
	  *
	  * @param $count int
	  * @param page int
	  * @param since_id int
	  * @param max_id int
	  * @param @useType string
	  * @return array|string
	  */
	 function getMentions($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/mentions.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户发送及收到的评论列表
	 *
	 * @param $count int
	 * @param $page int
	 * @param $since_id int
	 * @param $max_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function getCommentsTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments_timeline.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户发出的评论
	 *
	 * @param $count int
	 * @param $page int
	 * @param $since_id int
	 * @param $max_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function getCommentsByMe($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments_by_me.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户收到的评论列表
	 *
	 * @param $list
	 * @param $count
	 * @param $page
	 * @param $since_id
	 * @param $max_id
	 * @return array
	 */
	function getCommentsToMe($list = null, $count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	{
		if (empty($list)) {
			$url = XWB_API_URL.'statuses/comments_timeline.'.$this->format;

			$params = array();
			if ($since_id) {
				$params['since_id'] = $since_id;
			}
			if ($max_id) {
				$params['max_id'] = $max_id;
			}
			if ($count) {
				$params['count'] = $count;
			}
			if ($page) {
				$params['page'] = $page;
			}

			$response = $this->oAuthRequest($url, 'get', $params, $useType);
		} else {
			$response = $list;
		}

		if (is_array($response) && $response) {
			//实例化存储
			$storage = XWB_plugin::getUser();
			$result = array();
			foreach ($response as $var) {
				if ($var['user']['id'] == $storage->getInfo('sina_uid')) {
					continue;
				}
				$result[] = $var;
			}
			return $result;
		}
		return $response;
	}


	/**
	 * 获取指定微博的评论列表
	 *
	 * @param $id int
	 * @param $count int
	 * @param $page int
	 * @param $useType string
	 * @return array|string
	 */
	 function getComments($id, $count = null, $page = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments.'.$this->format;

		$params = array();
		$params['id'] = $id;

		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 批量获取一组微博的评论数及转发数
	 *
	 * @param $ids string
	 * @param $useType string
	 * @return array|string
	 */
	 function getCounts($ids, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/counts.'.$this->format;

		$params = array();
		if (is_array($ids)) {
			$params['ids'] = implode(',', $ids);
		} else {
			$params['ids'] = $ids;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户未读消息数
	 *
	 * @param int|string $with_new_status 默认为0。1表示结果包含是否有新微博，0表示结果不包含是否有新微博
	 * @param int|string $since_id 微博id，返回此条id之后，是否有新微博产生，有返回1，没有返回0
	 * @param $useType string
	 * @return array|string
	 */
	 function getUnread($with_new_status = null, $since_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'/statuses/unread.'.$this->format;

		$params = array();
	 	if ($with_new_status) {
			$params['with_new_status'] = $with_new_status;
		}
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	 //访问接口

	/**
	 * 根据ID获取单条微博信息内容
	 *
	 * @param $id int
	 * @param $user_id int
	 * @param $name string
	 * @param $useType string
	 * @return array|string
	 */
	 function getStatuseShow($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/show/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 发布一条微博信息
	 *
	 * @param $status string
	 * @param $useType string
	 * @return array|string
	 */
	 function update($status, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/update.'.$this->format;

		$params = array();
		$params['status'] = urlencode($status);

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 /**
	  * 上传图片并发布一条微博信息
	  *
	  * @param $status string
	  * @param $pid string
	  * @param $lat string
	  * @param $long string
	  * @param $useType string
	  * @return array|string
	  */
	 function upload($status, $pic, $lat = null, $long = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/upload.'.$this->format;

		$params = array();
		$params['status'] = urlencode($status);
		$params['pic'] = '@'.$pic;

		if ($lat) {
			$params['lat'] = $lat;
		}
		if ($long) {
			$params['long'] = $long;
		}
		$response = $this->oAuthRequest($url, 'post', $params, $useType, true);

		return $response;
	 }


	/**
	 * 删除微博
	 *
	 * @param $id int
	 * @param $useType string
	 * @return array|string
	 */
	 function destroy($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 转发一条微博信息（可加评论）
	 *
	 * @param $id int
	 * @param $status string
	 * @param $useType string
	 * @return array|string
	 */
	 function repost($id, $status = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/repost.'.$this->format;

		$params = array();
		$params['id'] = $id;
		if ($status) {
			$params['status'] = urlencode($status);
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 对一条微博信息进行评论
	 *
	 * @param $id int
	 * @param $comment string
	 * @param $useType string
	 * @return array|string
	 */
	 function comment($id, $comment, $cid = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comment.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['comment'] = urlencode($comment);
		if ($cid) {
			$params['cid'] = $cid;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 删除当前用户的微博评论信息
	 *
	 * @param $id int
	 * @param $useType string
	 * @return array|string
	 */
	 function comment_destroy($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comment_destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 /**
	  * 回复微博评论信息
	  *
	  * @param $id int
	  * @param $cid int
	  * @param $comment string
	  * @param $useType string
	  * @return array|string
	  */
	 function reply($id, $cid, $comment, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/reply.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['cid'] = $cid;
		$params['comment'] = urlencode($comment);

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 //用户接口

	/**
	 * 根据用户ID获取用户资料（授权用户）
	 *
	 * @param $id int|string
	 * @param $user_id int
	 * @param $name string
	 * @param $useType string
	 * @return array|string
	 */
	function getUserShow($id = null, $user_id = null, $name = null, $useType = true)
	{
		if ($id) {
			$url = XWB_API_URL.'users/show/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'users/show.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	/**
	 * 获取当前用户关注对象列表及最新一条微博信息
	 *
	 * @param $id int|string
	 * @parmas $user_id int
	 * @param $name string
	 * @param $cursor
	 * @param $count
	 * @param $useType string
	 * @return array|string
	 */
	 function getFriends($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/friends/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/friends.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}


		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户粉丝列表及最新一条微博信息
	 *
	 * @param $id int|string
	 * @param $user_id int
	 * @param $name string
	 * @param $cursor string
	 * @param $count int
	 * @param $useType string
	 * @return array|string
	 */
	 function getFollowers($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/followers/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/followers.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}


		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 //私信接口

	/**
	 * 获取当前用户最新私信列表
	 *
	 * @param $count int
	 * @param $page int
	 * @param $since_id int
	 * @param $max_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function getDirectMessages($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户发送的最新私信列表
	 *
	 * @param $count int
	 * @param $page int
	 * @param $since_id int
	 * @param $max_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function getSentDirectMessages($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/sent.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 发送一条私信
	 *
	 * @param $id int|string
	 * @param $text string
	 * @param $name string
	 * @param $user_id int
	 * @param $useType string
	 * @return array|string
	 */
	 function sendDirectMessage($id, $text, $name = null, $user_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/new.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['text'] = $text;
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($user_id) {
			$params['user_id'] = $user_id;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 删除一条私信
	 *
	 * @param $id int
	 * @param $useType string
	 * @return array|string
	 */
	 function deleteDirectMessage($id, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 //关注接口

	/**
	 * 关注某用户
	 *
	 * @param $id int|string
	 * @param $user_id int
	 * @param $name string
	 * @param $follow string
	 * @param $useType string
	 * @return array|string
	 */
	 function createFriendship($id = null, $user_id = null, $name = null, $follow = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friendships/create/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friendships/create.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($follow) {
			$params['follow'] = $follow;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 取消关注
	 *
	 * @param $id int|string
	 * @param $user_id int
	 * @param $name string
	 * @param $useType string
	 * @return array|string
	 */
	 function deleteFriendship($id = null, $user_id = null, $name = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friendships/destroy/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friendships/destroy.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 是否关注某用户
	 *
	 * @param $user_a int
	 * @param $user_b int
	 * @param $useType string
	 * @return array|string
	 */
	 function existsFriendship($user_a, $user_b, $useType = true)
	 {
		$url = XWB_API_URL.'friendships/exists.'.$this->format;

		$params = array();
		$params['user_a'] = $user_a;
		$params['user_b'] = $user_b;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 获取两个用户关系的详细情况
	 *
	 * @param $target_id int
	 * @param $target_screen_name string
	 * @param $source_id int
	 * @param $source_screen_name string
	 * @param $useType string
	 * @return array|string
	 */
	 function getFriendship($target_id = null, $target_screen_name = null, $source_id = null, $source_screen_name = null, $useType = true)
	 {
		$url = XWB_API_URL.'friendships/show.'.$this->format;

		$params = array();
		if ($target_id) {
			$params['target_id'] = $target_id;
		}
		if ($target_screen_name) {
			$params['target_screen_name'] = $target_screen_name;
		}
		if ($source_id) {
			$params['source_id'] = $source_id;
		}
		if ($source_screen_name) {
			$params['source_screen_name'] = $source_screen_name;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 //Social Graph接口

	/**
	 * 获取用户关注对象uid列表
	 *
	 * @param $id int
	 * @param $user_id int
	 * @param $name string
	 * @param $cursor string
	 * @param $count int
	 * @param $useType string
	 * @return array|string
	 */
	 function getFriendIds($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friends/ids/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friends/ids.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取用户粉丝对象uid列表
	 *
	 * @param $id int
	 * @param $user_id int
	 * @param $name string
	 * @param $useType string
	 * @return array|string
	 */
	 function getFollowerIds($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'followers/ids/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'followers/ids.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 //帐号接口

	/**
	 * 验证当前用户身份是否合法
	 *
	 * @param $useType string
	 * @return array|string
	 */
	 function verifyCredentials($useType = true)
	 {
		$url = XWB_API_URL.'account/verify_credentials.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 获取当前用户API访问频率限制
	 *
	 * @param $useType string
	 * @return array|string
	 */
	 function getRateLimitStatus($useType = true)
	 {
		$url = XWB_API_URL.'account/rate_limit_status.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 当前用户退出登录
	 *
	 * @param $useType string
	 * @return array|string
	 */
	 function endSession($useType = true)
	 {
		$url = XWB_API_URL.'account/end_session.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 更改头像
	 *
	 * @param $image string
	 * @param $useType string
	 * @return array|string
	 */
	 function updateProfileImage($image, $useType = true)
	 {
		$url = XWB_API_URL.'account/update_profile_image.'.$this->format;

		$params = array();
		$params['image'] = '@'.$image;

		$response = $this->oAuthRequest($url, 'post', $params, $useType, true);

		return $response;
	 }


	/**
	 * 更改资料
	 *
	 * @param $name string
	 * @param $gender string
	 * @param $province int
	 * @param $city int
	 * @param $description string
	 * @param $params
	 * @param $useType string
	 * @return array|string
	 */
	 function updateProfile($params, $useType = true)
	 {
		$url = XWB_API_URL.'account/update_profile.'.$this->format;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 注册新浪微博帐号
	 *
	 * @param $params array
	 * @return array|string
	 */
	 function register($params, $useType = true)
	 {
		$url = XWB_API_URL.'account/register.'.$this->format;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 //收藏接口

	/**
	 * 获取当前用户的收藏列表
	 *
	 * @param $page int
	 * @param $useType string
	 * @return array|string
	 */
	 function getFavorites($page = null, $useType = true)
	 {
		$url = XWB_API_URL.'favorites.'.$this->format;

		$params = array();
		if ($page) {
			$params['page'] = $page;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	/**
	 * 添加收藏
	 *
	 * @param $id int
	 * @param $useType string
	 * @return array|string
	 */
	 function createFavorite($id, $useType = true)
	 {
		$url = XWB_API_URL.'favorites/create.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	/**
	 * 删除当前用户收藏的微博信息
	 *
	 * @param $id int
	 * @param $useType string
	 * @return array|string
	 */
	 function deleteFavorite($id, $useType = true)
	 {
		$url = XWB_API_URL.'favorites/destroy/'.$id.'.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 //oauth

    /**
     * Set API URLS
     */
    /**
     * @ignore
     */
    function accessTokenURL()  { return XWB_API_URL.'oauth/access_token'; }
    /**
     * @ignore
     */
    function authenticateURL() { return XWB_API_URL.'oauth/authenticate'; }
    /**
     * @ignore
     */
    function authorizeURL()    { return XWB_API_URL.'oauth/authorize'; }
    /**
     * @ignore
     */
    function requestTokenURL() { return XWB_API_URL.'oauth/request_token'; }

    /**
     * Get a request_token from Weibo
     *
     * @return array a key/value array containing oauth_token and oauth_token_secret
     */
    function getRequestToken($oauth_callback = NULL, $useType = 'string')
	{
        $parameters = array();
        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }

        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters, $useType);
        $token = ns_OAuthUtil::parse_parameters($request);
	    if(isset($token['oauth_token']) && isset($token['oauth_token_secret'])){
        	$this->token = new ns_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }
        return $token;
    }

    /**
     * Get the authorize URL
     *
     * @return array
     */
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url)
	{
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_Weibo)) {
            return $this->authorizeURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url). '&from=xweibo&xwb_cb=login';
        } else {
            return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=". urlencode($url). '&from=xweibo&xwb_cb=login';
        }
    }

	/**
	 * Get the authorize Token
	 *
	 * @param string $token
	 * @param string $user
	 * @param string $password
	 * @param string $useType
	 *
	 * @return array
	 */
	function getAuthorizeToken($token, $user, $password, $useType = 'json')
	{
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }

		$url = $this->authorizeURL();
		$params = array();
		$params['oauth_token'] = $token;
		$params['oauth_callback'] = $useType;
		$params['display'] = 'web';
		$params['userId'] = $user;
		$params['passwd'] = $password;

		$this->http->setUrl($url);
		$this->http->setData($params);
		$response = $this->http->request();

		$code = $this->http->getState();
		if (200 != $code) {
			$this->setError($response);
			//返回出错代码
			//return $code;
		}else{
			$response = json_decode($response, true);
		}
		return $response;
	}

    /**
     * Exchange the request token and secret for an access token and
     * secret, to sign API calls.
     *
     * @return array array("oauth_token" => the access token,
     *                "oauth_token_secret" => the access secret)
     */
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false, $useType = 'string')
	{
        $parameters = array();
        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }
        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters, $useType);
        $token = ns_OAuthUtil::parse_parameters($request);
        if(isset($token['oauth_token']) && isset($token['oauth_token_secret'])){
        	$this->token = new ns_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }
        return $token;
    }

    /**
     * Format and sign an OAuth / API request
     * 目前仅支持get和post方法
     *
     * @return array
     */
    function oAuthRequest($url, $method, $parameters , $useType = true, $multi = false)
	{
		
		$request = ns_OAuthRequest::from_consumer_and_token ( $this->consumer, $this->token, $method, $url, $parameters );
		$request->sign_request ( $this->sha1_method, $this->consumer, $this->token );
		$method = strtoupper ( $method );
		switch ($method) {
			case 'GET' :
				//echo $request->to_url();
				$this->last_req_url = $request->to_url ();
				$this->http->setUrl ( $request->to_url () );
				break;
			
			case 'POST' :
				$this->last_req_url = $request->get_normalized_http_url ();
				$this->http->setUrl ( $request->get_normalized_http_url () );
				$this->http->setData ( $request->to_postdata ( $multi ) );
				if ($multi) {
					$header_array = array ();
					$header_array2 = array ();
					if ($multi)
						$header_array2 = array ("Content-Type: multipart/form-data; boundary=" . $GLOBALS['__CLASS']['ns_OAuthRequest']['__STATIC']['boundary'], "Expect: " );
					foreach ( $header_array as $k => $v ) {
						array_push ( $header_array2, $k . ': ' . $v );
					}
					if( !defined('CURLOPT_HTTPHEADER') ){
						define ('CURLOPT_HTTPHEADER', 10023);
					}
					$config = array (CURLOPT_HTTPHEADER => $header_array2 );
					$this->http->setConfig ( $config );
				}
				break;
				
			default:
				trigger_error('WRONG REQUEST METHOD IN WEIBO CLASS!', E_USER_ERROR);
				break;
		}
		
		$this->http->setHeader('API-RemoteIP', (string)XWB_plugin::getIP());
		$time_start = microtime ();
		$result = $this->http->request( strtolower($method) );
		$time_end = microtime ();
		$time_process = array_sum ( explode ( " ", $time_end ) ) - array_sum ( explode ( " ", $time_start ) );
		
		if ($useType === false || $useType === true) {
			$result = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $result), true);
		}
		$code = $this->http->getState ();
		
		if( 200 != $code ){
			$this->_delBindCheck( isset($result['error']) ? (string)$result['error'] : (string)$result );
			$this->req_error_count++;
		}
		
		if( defined('XWB_DEV_LOG_ALL_RESPOND') && XWB_DEV_LOG_ALL_RESPOND == true ){
			$this->logRespond ( $this->last_req_url,
							$method,
							( int ) $code,
							$result,
							array ('param' => $parameters,
									'time_process' => $time_process,
									'triggered_error' => $this->http->get_triggered_error (),
									'base_string' => $request->base_string,
									'key_string' => $request->key_string,
									)
			);
		}
		
		if (200 != $code) {
			if (0 == $code) {
				$result = array("error_code" => "50000", "error" => "timeout" );
			}
			
			if( $useType === true ) {
				if ( !is_array( $result ) ) {
					$result = array ('error' => (string)$result, 'error_code'=> $code ) ;
				}
				$this->setError( $result );
			}
		}
		
		return $result;
        
    }
    
    
	/**
	 * 搜索微博用户
	 *
	 * @param $params array
	 * @param $useType bool
	 * @return array|string
	 */
	function searchUser($params, $useType = true)
	{
		$url = XWB_API_URL.'users/search.'.$this->format;
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	/**
	 * 搜索微博文章
	 *
	 * @param $q string
	 * @param $page int
	 * @param $rpp string
	 * @param $callback string
	 * @param $geocode string
	 * @param $useType string
	 * @return array|string
	 */
	function search($q = null, $page = null, $rpp = null, $callback = null, $geocode = null, $useType = true)
	{
		$url = XWB_API_URL.'search.'.$this->format;
		$params = array();
		if ($q) {
			$params['q'] = urlencode($q);
		}
		if ($page) {
			$params['page'] = $page;
		}
		if ($rpp) {
			$params['rpp'] = $rpp;
		}
		if ($callback) {
			$params['callback'] = $callback;
		}
		if ($geocode) {
			$params['geocode'] = $geocode;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	/**
	 * 搜索微博文章
	 *
	 * @param $q string
	 * @param $filter_ori stirng
	 * @param $filter_pic string
	 * @param $province int
	 * @param $city int
	 * @param $starttime string
	 * @param $endtime string
	 * @param $page int
	 * @param $count int
	 * @param $callback string
	 * @param $useType string
	 * @return array|string
	 */
	function searchStatuse($params, $useType = true)
	{
		$url = XWB_API_URL.'statuses/search.'.$this->format;
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	/**
	 * 获取省份及城市编码ID与文字对应
	 *
	 * @param $useType bool
	 * @return array|string
	 */
	function getProvinces($useType = true)
	{
		$url = XWB_API_URL.'provinces.'.$this->format;
		$params = array();

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}
	

	/**
	 * 将respond给log下来，以作为OAUTH DEBUG证据
	 * 需要定义XWB_DEV_LOG_ALL_RESPOND并且设置为true，才记录
	 * 
	 * @param string $url 完整调用OATUH的URL
	 * @param string $method 调用方法
	 * @param integer $respondCode 返回状态代号
	 * @param mixed $respondResult 返回结果
	 * @param mixed $extraMsg 额外需要记录的内容
	 */
	function logRespond( $url, $method, $respondCode, $respondResult = array() , $extraMsg = array() ){
		//调用这个类的当前页面的url
		$callURL = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '__UNKNOWN__';
		
		//oauth url简略提取，以用作统计
		$oauth_short_url = str_replace( XWB_API_URL, '', ( strpos($url, '?') !== false ? substr( $url, 0, strpos($url, '?') ) : $url) );
		
		if( $respondCode == 0 ){
			//timeout
			$respondResult = '__CONNECTION MAYBE TIME OUT ?__';
		}elseif ( $respondCode == -1 ){
			$respondResult = '__CAN NOT CONNECT TO API SERVER; OR CREATE A WRONG OAUTH REQUEST URL. PLEASE INSPECT THE LOG__';
		}
		
		if( empty($respondResult) ){
			$respondResult = '__NO RESPOND RESULT__';
		}
		
		//extraMsg数组中，triggered_error是用于存放fsockopenHttp的trigger_error信息
		if( isset($extraMsg['triggered_error']) &&  empty($extraMsg['triggered_error']) ){
			unset($extraMsg['triggered_error']);
		}
		if(isset($extraMsg['key_string'])){
			$extraMsg['key_string'] = strtr($extraMsg['key_string'], array(XWB_APP_SECRET_KEY => '%APP_SKEY%'));
		}
		
		$time_process = isset($extraMsg['time_process']) ? round((float)$extraMsg['time_process'], 6) : 0;
		unset($extraMsg['time_process']);
		
		$error_count_log = '';
		if( $this->req_error_count > 0 ){
			$error_count_log = '[REQUEST ERROR COUNT IN THIS PHP LIFETIME] '. $this->req_error_count."\r\n";
		}
		
		$msg = $method. "\t".
				$respondCode. "\t".
				$time_process. " sec.\t".
				$oauth_short_url. "\t".
				"\r\n". str_repeat('-', 5). '[EXTRA MESSAGE START]'. str_repeat('-', 5)."\r\n".
				$error_count_log.
				'[CALL URL]'. $callURL. "\r\n".
				'[OAUTH REQUEST URL]'. $url. "\r\n".
				'[RESPOND RESULT]'. "\r\n". print_r($respondResult, 1). "\r\n\r\n".
				'[EXTRA LOG MESSAGE]'. "\r\n". print_r($extraMsg, 1). "\r\n".
				str_repeat('-', 5). '[EXTRA MESSAGE END]'. str_repeat('-', 5)."\r\n\r\n\r\n"
				;
		
		$logFile = XWB_P_DATA.'/oauth_respond_log_'. date("Y-m-d_H"). '.txt.php';
		XWB_plugin::LOG($msg, $logFile);
		
		return 1;
		
	}
	
	/**
	 * 当发现用户取消授权后，对其进行解绑操作
	 * 属于本插件特殊用途的函数，仅用于方法oAuthRequest中
	 */
	function _delBindCheck( $errmsg ){
		if( XWB_S_UID <= 0 ){
			return false;
		}
		
		if( false === strpos(str_replace(' ', '', strtolower($errmsg)), 'accessorwasrevoked') ){
			return false;
		}
		
		XWB_plugin::delBindUser(XWB_S_UID); //远程API
		$sess = XWB_plugin::getUser();
		$sess->clearToken();
		dsetcookie($this->_getBindCookiesName(XWB_S_UID) , -1, 604800);
		return true;
	}
	
 	/**
	 * 获取Bind cookies名称
	 * 属于本插件特殊用途的函数，仅用于方法_delBindCheck中
	 * @param integer $uid
	 * @return string
	 */
	function _getBindCookiesName($uid){
		return 'sina_bind_'. $uid;
	}
	
}
