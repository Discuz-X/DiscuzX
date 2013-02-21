<?php
/**
 * Xweibo API操作类
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-03-07
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id: apixwb.class.php 724 2011-05-10 05:28:00Z yaoying $
 *
 */
class apixwb
{
    var $url;
    var $http;
    var $error_exit = false;

    ///初始化
    function apixwb($url = '')
    {
        $this->url = $url ? $url : XWB_plugin::pCfg('url_to_xweibo');
        $this->http = XWB_plugin::getHttp(false);
    }
    
    ///开启或关闭通讯
    function setNotice($value = 1, $url = '', $rst = TRUE)
    {
        $params = array($value, $url);
        $response = $this->request('xwbBBSplugin.setNotice', $params);
        return $rst ? $response['rst'] : $response;
    }

    ///更新绑定用户
    function updateBindUser($site_uid, $sina_uid, $access_toke, $token_secret, $nickname = false, $rst = TRUE)
    {
        $params = array($site_uid, $sina_uid, $access_toke, $token_secret);
        if($nickname) $params[] = $nickname;
        $response = $this->request('xwbBBSplugin.updateBindUser', $params);
        return $rst ? $response['rst'] : $response;
    }

    ///删除绑定用户
    function delBindUser($site_uid, $rst = TRUE)
    {
        $params = array($site_uid);
        $response = $this->request('xwbBBSplugin.delBindUser', $params);
        return $rst ? $response['rst'] : $response;
    }

    ///获取指定绑定用户
    function getBindUser($id, $type = 'site_uid', $rst = TRUE)
    {
        $params = array($id, $type);
        $response = $this->request('xwbBBSplugin.getBindUser', $params);
        return $rst ? $response['rst'] : $response;
    }

    ///获取指定绑定用户
    function getBatchBindUser($uids)
    {
        $params = array($uids);
        $response = $this->request('xwbBBSplugin.getBatchBindUser', $params);
        return $rst ? $response['rst'] : $response;
    }

    ///请求处理
    function request($route, $params, $toArray = TRUE)
    {
        $AppKey = XWB_APP_KEY;
        $EncryptKey = XWB_plugin::pCfg('encrypt_key');
        $paramsJSON = json_encode($params);
        $EncryptTime = time();
        $secret = md5(sprintf("#%s#%s#%s#%s#%s#", $AppKey, $route, $paramsJSON, $EncryptTime, $EncryptKey));
        $data = array(
            'A=' . $route,
            'P=' . $paramsJSON,
            'T=' . $EncryptTime,
            'F=' . $secret
        );
        $url = $this->url;
        $this->http->setUrl($url);
        $this->http->setData(implode('&', $data));

        $time_start = microtime();
		$result = $this->http->request('post');
		$time_end = microtime();
		$time_process = array_sum(explode (' ', $time_end)) - array_sum(explode (' ', $time_start));

        if($toArray) {
            $result = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $result), true);
        }

        $code = $this->http->getState();

		$this->logRespond(
                rtrim($this->url, '/') . '/' . $route, 'post', (int)$code, $result,
                array('param' => $params,
                    'time_process' => $time_process,
                    'triggered_error' => $this->http->get_triggered_error()
                    )
                );

		if (200 != $code)
        {
            $result = array("rst" => false, "errno" => "50001", "err" => "network error" );
            $this->setError($result);
		}

		return $result;
    }

    ///错误处理
    function setError($error)
    {
        if( !$this->error_exit) return;

        $err = array(
            'Access time failure' => '请求时间失效',
            'Signature is not correct' => '签名不正确',
            'Request path is not correct' => '请求路径不正确',
            'Save faileds' => '数据保存失败',
            'Update faileds' => '数据更新失败'
        );

        $errmsg = isset($error['error']) ? strtolower($error['error']) : 'UNDEFINED ERROR';
        $msg = isset($err[$errmsg]) ? $err[$errmsg] : '未知错误';

        if('utf8' != strtolower(XWB_S_CHARSET)) {
			$msg = XWB_plugin::convertEncoding( $msg, XWB_S_CHARSET, 'UTF-8' );
		}
		XWB_plugin::showError($msg);
    }

    /**
	 * 记录远程API调用
	 * 需要定义XWB_API_REMOTE_LOG并且设置为true，才记录
	 *
	 * @param string $url 完整调用OATUH的URL
	 * @param string $method 调用方法
	 * @param integer $code 返回状态代号
	 * @param mixed $result 返回结果
	 * @param mixed $extraMsg 额外需要记录的内容
	 */
	function logRespond($url, $method, $code, $result = array() , $extraMsg = array() )
    {
		if( !defined('XWB_REMOTE_API_LOG') || XWB_REMOTE_API_LOG != true ) {
			return;
		}

		//调用这个类的当前页面的url
		$callURL = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '__UNKNOWN__';

		if( $code == 0 ) {
			//timeout
			$result = '__CONNECTION MAYBE TIME OUT ?__';
		} elseif( $code == -1 ) {
			$result = '__CAN NOT CONNECT TO API SERVER; OR CREATE A WRONG OAUTH REQUEST URL. PLEASE INSPECT THE LOG__';
		}

		if(empty($result)) {
			$result = '__NO RESPOND RESULT__';
		}

		//extraMsg数组中，triggered_error是用于存放fsockopenHttp的trigger_error信息
		if(isset($extraMsg['triggered_error']) &&  empty($extraMsg['triggered_error'])) {
			unset($extraMsg['triggered_error']);
		}

		$time_process = isset($extraMsg['time_process']) ? round((float)$extraMsg['time_process'], 6) : 0;
		unset($extraMsg['time_process']);

        $data = array(
            "\r\n" . str_repeat('-', 45),
            "[METHOD]:\t\t" . $method,
            "[RESPOND_CODE]:\t\t" . $code,
            "[TIME_PORCESS]:\t\t" . $time_process . ' sec',
            "[CALL URL]:\t\t" . $callURL,
            "[REQUEST URL]:\t\t" . $url,
            "[RESPOND RESULT]:\t\t" . (($result && !is_bool($result))?"\r\n".print_r($result, TRUE):(is_bool($result)?($result?'TRUE':'false'):'_EMPTY_')),
            "[EXTRA LOG MESSAGE]:\t\t" . ($extraMsg ? "\r\n".print_r($extraMsg, TRUE) : '_EMPTY_'),
            str_repeat('-', 45) . "\r\n\r\n",
        );
		$logFile = XWB_P_DATA.'/api/api_remote_log_'. date("Y-m-d_H"). '.txt';
		XWB_plugin::LOG(implode("\r\n", $data), $logFile);
		return;
	}
}
?>
