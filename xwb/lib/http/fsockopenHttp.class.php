<?php
/**
 * 基于fsockopen的http Client请求类。后经heli改写用于Xweibo中。
 * 本文件可单独使用。原文件注释：
 * Full featured HTTP/1.1 client object
 * CVS ID: http_client.class.php,v 1.9 2010/06/17 08:49:33 hightman Exp
 * @package	xweibo_plugin_core
 * @subpackage adapter_http
 * @author hightman
 * @author heli
 * @copyright Copyright &copy; 2008-2010 Twomice Studio
 * @link http://www.hightman.cn/bbs/showthread.php?tid=838
 * @version $Id: fsockopenHttp.class.php 708 2011-05-09 01:30:22Z yaoying $
 */

/*
 GET, POST, HEAD supported
 POST also can be used to upload some file!!(enctype=multipart/form-data)

 [[[ API List ]]]

 Object Http_Client([bool verbose = false]);

 // set a HTTP header for the next request
 void Http_Client::setHeader(string key, string value);

 // set a cookie for the next request
 void Http_Client::setCookie(string key, string value);

 // do a get, when redirect is true, it will parse the 302/301 HTTP status
 string Http_Client::Get(string url[, bool redirect = true]);

 // do a head method for the URL
 bool Http_Client::Head(string url);

 // add post field for the next request
 void Http_Client::addPostField(string key, string value);

 // add post file(as uploaded) for the next request
 void Http_Client::addPostFile(string key, string filename[, string filedata = null]);

 // do a post
 string Http_Client::Post(string url);

 // get the HTTP status code of the last request.
 int Http_Client::getStatus();

 // get a special or all HTTP headers of the last request
 string Http_Client::getHeader([string key]);

 // get the cookie of the last request when host = null or special cookie value of `Host` by string key
 string Http_Client::getCookie(string key[, string host = null]);
 */
class fsockopenHttp
{
    // local variables
    var $headers, $status, $resttl, $cookies, $socks, $verbose;
    var $post_files, $post_fields;
    var $triggered_error = array();
    //此值至少要大于等于2
	var $max_retries = 3;
	var $_serverUrl;
	var $mimes = array(
						'gif' => 'image/gif',
						'png' => 'image/png',
						'bmp' => 'image/bmp',
						'jpeg' => 'image/jpeg',
						'pjpg' => 'image/pjpg',
						'jpg' => 'image/jpeg',
						'tif' => 'image/tiff',
						'htm' => 'text/html',
						'css' => 'text/css',
						'html' => 'text/html',
						'txt' => 'text/plain',
						'gz' => 'application/x-gzip',
						'tgz' => 'application/x-gzip',
						'tar' => 'application/x-tar',
						'zip' => 'application/zip',
						'hqx' => 'application/mac-binhex40',
						'doc' => 'application/msword',
						'pdf' => 'application/pdf',
						'ps' => 'application/postcript',
						'rtf' => 'application/rtf',
						'dvi' => 'application/x-dvi',
						'latex' => 'application/x-latex',
						'swf' => 'application/x-shockwave-flash',
						'tex' => 'application/x-tex',
						'mid' => 'audio/midi',
						'au' => 'audio/basic',
						'mp3' => 'audio/mpeg',
						'ram' => 'audio/x-pn-realaudio',
						'ra' => 'audio/x-realaudio',
						'rm' => 'audio/x-pn-realaudio',
						'wav' => 'audio/x-wav',
						'wma' => 'audio/x-ms-media',
						'wmv' => 'video/x-ms-media',
						'mpg' => 'video/mpeg',
						'mpga' => 'video/mpeg',
						'wrl' => 'model/vrml',
						'mov' => 'video/quicktime',
						'avi' => 'video/x-msvideo',
						'xml' => 'text/xml',
						'bin' => 'application/octet-stream',
						'js' => 'application/x-javascript',
					);


    // construct function
    function fsockopenHttp($verbose = false)
    {
        $this->__construct($verbose);
    }

    function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->cookies = array();
        $this->socks = array();

        $this->_reset_status();
    }

    function __destruct()
    {
        foreach ($this->socks as $host => $sock) { @fclose($sock); }
    }

	function setUrl($url)
	{
		$this->_serverUrl = $url;
		return $this;
	}

	function setData($data)
	{
        $this->_reset_status();
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				$this->post_fields[] = array($key, $var);
			}
		} else {
			$this->post_fields = $data;
		}

		return $this;
	}

	function setConfig($config)
	{
		foreach ($config as $var) {
			foreach($var as $k) {
				$headers = explode(':', $k);
				$headers[1] = trim($headers[1]);
				if (empty($headers[1])) {
					continue;
				}
				$this->setHeader($headers[0], $headers[1]);
			}
		}
	}

	function getState()
	{
        return $this->status;
	}

	function request($method = null, $https = false)
	{
		$method = empty($method) ? 'GET' : strtoupper($method);
        switch ($method) {
			case 'GET':
				$result = $this->Get($this->_serverUrl);
				break;
			case 'POST':
				$result = $this->Post($this->_serverUrl);
				break;
			case 'HEAD':
				$result = $this->Head($this->_serverUrl);
				break;
			default:
				$result = $this->_do_url($this->_serverUrl, $method);
				break;
        }
		return $result;
	}

    // get the HTTP status of the last request!!
    function getStatus()
    {
        return $this->status;
    }

    // get the HTTP respond Ttitle
    function getResttl()
    {
        return $this->resttl;
    }

    // set a http header for the next request!
    function setHeader($key, $value)
    {
        $this->_reset_status();
        $key = strtolower($key);
        $this->headers[$key] = $value;
    }

    // set a cookie for the next request!
    function setCookie($key, $value)
    {
        if (!isset($this->headers['cookie'])) $this->headers['cookie'] = array();
        $this->headers['cookie'][$key] = $value;
    }

    // get the HTTP header from the last request!
    function getHeader($key = null)
    {
        if (is_null($key)) return $this->headers;
        $key = strtolower($key);
        if (!isset($this->headers[$key])) return null;
        return $this->headers[$key];
    }

    // get the cookie from the last request or by host
    function getCookie($key = null, $host = null)
    {
        if (is_null($host))
        {
            if (!isset($this->headers['cookie'])) return null;
            if (is_null($key)) return $this->headers['cookie'];
            if (isset($this->headers['cookie'][$key])) return $this->headers['cookie'][$key];
            return null;
        }
        else
        {
            if (!isset($this->cookies[$host])) return null;
            if (is_null($key)) return $this->cookies[$host];
            if (isset($this->cookies[$host][$key])) return $this->cookies[$host][$key];
            return null;
        }
    }

    // save cookie to external place
    function saveCookie($fpath)
    {
        if ($fd = @fopen($fpath, 'w'))
        {
            $data = serialize($this->cookies);
            fwrite($fd, $data);
            fclose($fd);
            return true;
        }
        return false;
    }

    // restore cookie from external place
    function loadCookie($fpath)
    {
        if (file_exists($fpath)) $this->cookies = unserialize(@file_get_contents($fpath));
    }

    // add post field for next request
    function addPostField($key, $value)
    {
        $this->_reset_status();
        $this->post_fields[] = array($key, $value);
    }

    // add a multipart post file for the next request
    function addPostFile($key, $fname, $content = null)
    {
        $this->_reset_status();
        if (is_null($content) && is_file($fname))
        {
            $content = file_get_contents($fname);
            $fname = basename($fname);
        }
        $this->post_files[] = array($key, $fname, $content);
    }

    // do a HTTP/get
    function Get($url, $redir = true)
    {
        return $this->_do_url($url, 'get', null, $redir);
    }

    // do a HTTP/head
    function Head($url)
    {
        return $this->_do_url($url, 'head');
    }

    // do a HTTP/post
    function Post($url, $redir = true)
    {
        $data = '';
        if (count($this->post_files) > 0)
        {
            $boundary = md5($url . microtime());
            foreach ($this->post_fields as $tmp)
            {
                $data .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$tmp[0]}\"\r\n\r\n{$tmp[1]}\r\n";
            }
            foreach ($this->post_files as $tmp)
            {
                $type = 'application/octet-stream';
                $ext = strtolower(substr($tmp[1], strrpos($tmp[1],'.')+1));
                if (isset($this->mimes[$ext])) $type = $this->mimes[$ext];
                $data .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$tmp[0]}\"; filename=\"{$tmp[1]}\"\r\nContent-Type: $type\r\nContent-Transfer-Encoding: binary\r\n\r\n";
                $data .= $tmp[2] . "\r\n";
            }
            $data .= "--{$boundary}--\r\n";
            $this->setHeader('content-type', 'multipart/form-data; boundary=' . $boundary);
        }
        else
        {
			if (!is_array($this->post_fields)) {
				$data = $this->post_fields;
			} else {
				foreach ($this->post_fields as $tmp)
				{
					$data .= '&' . $this->_format_field($tmp[0], $tmp[1]);
				}
			}
            $data = ltrim($data, '&');
        }
        $dlen = strlen($data);
        $this->setHeader('content-length', $dlen);
		if (empty($this->headers['content-type'])) {
			$this->setHeader('content-type', 'application/x-www-form-urlencoded');
		}
        return $this->_do_url($url, 'post', $data, $redir);
    }

    // -------------------------------------------------
    // private functions
    // -------------------------------------------------
    // read data from socket
    function _sock_read($fd, $maxlen = 4096)
    {
        $rlen = 0;
        $data = '';
        $ntry = $this->max_retries;
        while (!feof($fd))
        {
            $part = fread($fd, $maxlen - $rlen);
            if ($part === false || $part === '') $ntry--;
            else $data .= $part;
            $rlen = strlen($data);
            if ($rlen == $maxlen || $ntry == 0) break;
        }
        if ($ntry == 0) fclose($fd);
        return $data;
    }

    // write data to socket
    function _sock_write($fd, $buf)
    {
        $wlen = 0;
        $tlen = strlen($buf);
        $ntry = $this->max_retries;
        while ($wlen < $tlen)
        {
            $nlen = fwrite($fd, substr($buf, $wlen), $tlen - $wlen);
            if (!$nlen) { if (--$ntry == 0) return false; }
            else $wlen += $nlen;
        }
        return true;
    }

    // reset some request data (status)
    function _reset_status()
    {
        if ($this->status !== 0)
        {
            $this->status = 0;
            $this->headers = $this->post_files = $this->post_fields = array();
        }
    }

    // format post field
    function _format_field($key, $value)
    {
        if (!is_array($value))
            $ret = $key . '=' . rawurlencode($value);
        else
        {
            $ret = '';
            foreach ($value as $k => $v)
            {
                $ret .= '&' . $this->_format_field($key . '[' . $k . ']', $v);
            }
            $ret = substr($ret, 1);
        }
        return $ret;
    }

    // do a url method
    function _do_url($url, $method, $data = null, $redir = true)
    {
        // check the url
        if (strncasecmp($url, 'http://', 7) && strncasecmp($url, 'https://', 8))
        {
            $base = 'http://' . $_SERVER['HTTP_HOST'];
            if (substr($url, 0, 1) != '/')
                $url = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1) . $url;
            $url = $base . $url;
        }

        // parse the url
        $url = str_replace('&amp;', '&', $url);
        $pa = @parse_url($url);
        if ($pa['scheme'] && $pa['scheme'] != 'http' && $pa['scheme'] != 'https')
        {
            $this->_trigger_error("Unsupported scheme `{$pa['scheme']}`", E_USER_WARNING);
            return false;
        }
        if (!isset($pa['port'])) $pa['port'] = ($pa['scheme'] == 'https' ? 443 : 80);
        if (!isset($pa['path'])) $pa['path'] = '/';
        $host = strtolower($pa['host']);
        $port = intval($pa['port']);
        $skey = $host . ':' . $port;
        if ($pa['scheme'] && $pa['scheme'] == 'https') $host_conn = 'ssl://' . $host;
        else $host_conn = 'tcp://' . $host;

        // make the query buffer
        $method = strtoupper($method);
        $buf = $method . ' ' . $pa['path'];
        if (isset($pa['query'])) $buf .= '?' . $pa['query'];
        $buf .= " HTTP/1.1\r\nHost: {$host}\r\n";

        // set default HTTP/headers
        $this->_reset_status();
//        if (!isset($this->headers['user-agent'])) $buf .= "User-Agent: Xweibo 1.0\r\n";
        if (!isset($this->headers['accept'])) $buf .= "Accept: */*\r\n";
//        if (!isset($this->headers['accept-language'])) $buf .= "Accept-Language: zh-cn,zh\r\n";
//        if (!isset($this->headers['connection'])) $buf .= "Connection: Keep-Alive\r\n";
        if (isset($this->headers['accept-encoding'])) unset($this->headers['accept-encoding']);
        if (isset($this->headers['host'])) unset($this->headers['host']);

        // saved cookies (session data)
        $now = time();
        $ck_str = '';
        foreach ($this->cookies as $ck_host => $ck_list)
        {
            if (!stristr($host, $ck_host)) continue;
            foreach ($ck_list as $ck => $cv)
            {
                if ($cv['expires'] > 0 && $cv['expires'] < $now) continue;
                if (strncmp($pa['path'], $cv['path'], strlen($cv['path']))) continue;
                $ck_str .= '; ' . rawurlencode($ck) . '=' . rawurlencode($cv['value']);
            }
        }
        if ($ck_str != '') $buf .= 'Cookie:' . substr($ck_str, 1) . "\r\n";
        foreach ($this->headers as $k => $v)
        {
            if ($k != 'cookie')
                $buf .= ucfirst($k) . ": " . $v . "\r\n";
            else
            {
                $vv = '';
                foreach ($v as $ck => $cv) $vv .= '; ' . rawurlencode($ck) . '=' . rawurlencode($cv);
                if ($vv != '') $buf .= 'Cookie:' . substr($vv, 1) . "\r\n";
            }
        }
        $buf .= "\r\n";
        if ($method == 'POST') $buf .= $data . "\r\n";

        // force reset status for next query even if failed this time.
        $this->status = -1;

        // show the header buf
        if ($this->verbose)
        {
            echo "[SEND] request buffer\r\n----\r\n";
            echo $buf;
            echo "----\r\n";
        }

        // create the sock & send the header
        $ntry = $this->max_retries;
        $sock = isset($this->socks[$skey]) ? $this->socks[$skey] : false;
        do
        {
            if ($sock && $this->_sock_write($sock, $buf)) break;
            if ($sock) @fclose($sock);
            $sock = @fsockopen($host_conn, $port, $errno, $error, 3);
            if ($sock)
            {
                stream_set_blocking($sock, 1);
                stream_set_timeout($sock, 10);
            }
        }
        while (--$ntry);
        if (!$sock)
        {
            if (isset($this->socks[$skey])) unset($this->socks[$skey]);
            $this->_trigger_error("Cann't connect to `$host:$port'", E_USER_WARNING);
            return false;
        }
        $this->socks[$skey] = $sock;
        if ($this->verbose)
        {
            echo "[SEND] using socket = {$sock}\r\n";
            echo "[RECV] http respond header\r\n----\r\n";
        }

        // read the respond header
        $this->headers = array();
        while ($line = fgets($sock, 2048))
        {
            if ($this->verbose) echo $line;
            $line = trim($line);
            if ($line === '') break;
            if (!strncasecmp('HTTP/', $line, 5))
            {
                $line = trim(substr($line, strpos($line, ' ')));
                list($this->status, $this->resttl) = explode(' ', $line, 2);
                $this->status = intval($this->status);
            }
            else if (!strncasecmp('Set-Cookie: ', $line, 12))
            {
                $ck_key = '';
                $ck_val = array('value' => '', 'expires' => 0, 'path' => '/', 'domain' => $host);
                $tmpa = explode(';', substr($line, 12));
                foreach ($tmpa as $tmp)
                {
                    $tmp = trim($tmp);
                    if (empty($tmp)) continue;
                    list($tmpk, $tmpv) = explode('=', $tmp, 2);
                    $tmpk2 = strtolower($tmpk);
                    if ($ck_key == '')
                    {
                        $ck_key = rawurldecode($tmpk);
                        $ck_val['value'] = rawurldecode($tmpv);
                    }
                    else if ($tmpk2 == 'expires')
                    {
                        $ck_val['expires'] = strtotime($tmpv);
                        if ($ck_val['expires'] < $now)
                        {
                            $ck_val['value'] = '';
                            break;
                        }
                    }
                    else if (isset($ck_val[$tmpk2]) && $tmpv != '')
                    {
                        $ck_val[$tmpk2] = $tmpv;
                    }
                }

                // delete cookie?
                if ($ck_key == '') continue;
                if ($ck_val['value'] == '') unset($this->cookies[$ck_val['domain']][$ck_key]);
                else $this->cookies[$ck_val['domain']][$ck_key] = $ck_val;

                // headers.
                $this->headers['cookie'][$ck_key] = $ck_val;
            }
            else
            {
                list($k, $v) = explode(':', $line, 2);
                $k = strtolower(trim($k));
                $v = trim($v);
                $this->headers[$k] = $v;
            }
        }
        if ($this->verbose) echo "----\r\n";

        // get body
        if ($method == 'HEAD') return ($this->status == 200);
        $connection = $this->getHeader('connection');
        $encoding = $this->getHeader('transfer-encoding');
        $length = $this->getHeader('content-length');
        if ($encoding && !strcasecmp($encoding, 'chunked'))
        {
            $body = '';
            while (true)
            {
                if (!($line = fgets($sock, 1024))) break;
                if ($this->verbose) echo "[RECV] Chunk Line: " . $line;
                if ( false !== ( $p1 = strpos($line, ';') ) ) $line = substr($line, 0, $p1);
                $chunk_len = hexdec(trim($line));
                if ($chunk_len <= 0) break;    // end the chunk
                $body .= $this->_sock_read($sock, $chunk_len);
                fread($sock, 2);            // eat the CRLF
            }

            // trailer header
            if ($this->verbose) echo "[RECV] chunk tailer\r\n----\r\n";
            while ($line = fgets($sock, 2048))
            {
                if ($this->verbose) echo $line;
                $line = trim($line);
                if ($line === '') break;
                list($k, $v) = explode(':', $line, 2);
                $k = strtolower(trim($k));
                $v = trim($v);
                $this->headers[$k] = $v;
            }
            if ($this->verbose) echo "----\r\n";
        }
        else if (isset($length))
        {
            $length = intval($length);
            if ($length > 0) $body = $this->_sock_read($sock, $length);
            else $body = '';
        }
        else
        {
            $body = '';
            $ntry = $this->max_retries;
            while (!feof($sock) && $ntry > 0)
            {
                $part = fread($sock, 8192);
                if ($part === false || $part === '') $ntry--;
                else $body .= $part;
            }
            $connection = 'close';
        }

        // check close connection?
        if ($connection && !strcasecmp($connection, 'close'))
        {
            @fclose($sock);
            unset($this->socks[$skey]);
        }

        // check redirect
        if ($redir && $this->status != 200 && ($location = $this->getHeader('location')))
        {
            if (!preg_match('/^http[s]?:\/\//i', $location))
            {
                $url2 = $pa['scheme'] . '://' . $pa['host'];
                if (strpos($url, ':', 8)) $url2 .= ':' . $pa['port'];
                if (substr($location, 0, 1) == '/') $url2 .= $location;
                else $url2 .= substr($pa['path'], 0, strrpos($pa['path'], '/') + 1) . $location;
                $location = $url2;
            }
            return $this->_do_url($location, 'get');
        }

        // return the body buf
        return $body;
    }
    
    /**
     * 添加一个错误触发器，主要为了方便外部debug
     * 
     * @since 2010-08-24 15:39
     * @param $errmsg
     * @param $errno
     */
    function _trigger_error( $errmsg, $errno ){
    	$this->triggered_error[] = array('errmsg' => $errmsg, 'errno' => $errno );
    	trigger_error($errmsg, $errno);
    }
    
    /**
     * 获取已经触发的错误信息
     * @since 2010-08-24 15:39
     */
    function get_triggered_error(){
    	return $this->triggered_error;
    }
    
    
}
