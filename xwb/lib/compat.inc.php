<?php
/*
 * @version $Id: compat.inc.php 697 2011-05-05 04:08:33Z yaoying $
 */
//JSON LIB
if (!function_exists('json_decode')){
	function json_decode($s, $ass = false){
		$assoc = ($ass) ? 16 : 32;
		$gloJSON = XWB_plugin::O('servicesJSON', $assoc);
		if($gloJSON->use != $assoc){
			$gloJSON->use = $assoc;
		}
		return $gloJSON->decode($s);
	}
}
if (!function_exists('json_encode')){
	function json_encode($s){
		$gloJSON = XWB_plugin::O('servicesJSON');
		$gloJSON->use = 16;
		return $gloJSON->encode($s);
	}
}



if(!function_exists('file_put_contents')) {
	
	!defined('FILE_APPEND') && define('FILE_APPEND', 8);
	
	function file_put_contents($filename, $data, $flag = false) {
		$mode = ($flag == FILE_APPEND || strtoupper ( $flag ) == 'FILE_APPEND') ? 'ab' : 'wb';
		$f = @fopen ( $filename, $mode );
		if ($f === false) {
			return 0;
		} else {
			if ( is_array ( $data )){
				$data = implode ( '', $data );
			}
			$bytes_written = @fwrite ( $f, $data );
			@fclose ($f);
			return $bytes_written;
		}
	}
	
}


//hack for is_writable
function  xwb_is_writable($path) {
	if ($path{strlen($path)-1}=='/'){
		return xwb_is_writable($path.uniqid(mt_rand()).'.tmp');
	}
	else if (is_dir($path)){
		return xwb_is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
	}else{
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ( $f===false ) return false;
		fclose($f);
		if (!$rm) unlink($path);
		return true;
	}
}

// hash_hmac 的另一种实现(借助mhash)
function _xwb_hash_hmac_compact_mhash($algo, $base_string, $key, $raw = false) {
	if (empty($algo)) {
		return false;
	}
	switch ($algo) {
		case 'md5':
			return mhash(MHASH_MD5, $base_string, $key);
			break;
		case 'sha1':
			return mhash(MHASH_SHA1, $base_string, $key);
			break;
	}
}

// hash_hmac 的另一种实现(借助pack:http://cn.php.net/manual/en/function.hash-hmac.php#93440)
function _xwb_hash_hmac_compact_pack($algo, $data, $key, $raw_output = false)
{
    $algo = strtolower($algo);
    $pack = 'H'.strlen($algo('test'));
    $size = 64;
    $opad = str_repeat(chr(0x5C), $size);
    $ipad = str_repeat(chr(0x36), $size);

    if (strlen($key) > $size) {
        $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
    } else {
        $key = str_pad($key, $size, chr(0x00));
    }
    
    for ($i = 0; $i < strlen($key) - 1; $i++) {
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }
    
    $output = $algo($opad.pack($pack, $algo($ipad.$data)));
    return ($raw_output) ? pack($pack, $output) : $output;
}




function _xwb_hash_hmac($algo, $base_string, $key, $raw = false){
	if (function_exists('hash_hmac')) {
		return 	hash_hmac($algo, $base_string, $key, $raw);
	}elseif(function_exists('mhash')){
		return _xwb_hash_hmac_compact_mhash($algo, $base_string, $key, $raw);
	}else{
		return _xwb_hash_hmac_compact_pack($algo, $base_string, $key, $raw);
	}
}



if (!function_exists('array_combine')) {
	function array_combine( $keys, $values )
	{
	   if( !is_array($keys) || !is_array($values) || empty($keys) || empty($values) || count($keys) != count($values) )
	   {
		 trigger_error( "array_combine() expects parameters 1 and 2 to be non-empty arrays with an equal number of elements", E_USER_WARNING );
		 return false;
	   }
	   $keys = array_values($keys);
	   $values = array_values($values);
	   $result = array();
	   foreach( $keys as $index => $key )
	   {
		 $result[$key] = $values[$index];
	   }
	   return $result;
	}
}



//http://www.php.net/manual/en/function.http-build-query.php
if( !function_exists('http_build_query') ){
	function http_build_query( $data, $prefix='', $sep='', $key='' ){
        $ret = array(); 
        foreach ((array)$data as $k => $v) { 
            if (is_int($k) && $prefix != null) { 
                $k = urlencode($prefix . $k); 
            } 
            if ((!empty($key)) || ($key === 0))  $k = $key.'['.urlencode($k).']'; 
            if (is_array($v) || is_object($v)) { 
                array_push($ret, http_build_query($v, '', $sep, $k)); 
            } else { 
                array_push($ret, $k.'='.urlencode($v)); 
            } 
        } 
        if (empty($sep)){
        	$sep = ini_get('arg_separator.output');
        	if(empty($sep)){
        		$sep = '&';
        	}
        }
        return implode($sep, $ret); 
	}
}

?>