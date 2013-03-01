<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class Hooker{
	
	private $msg = '&#20026;&#20102;&#27491;&#24120;&#20351;&#29992;&#27492;&#25554;&#20214;&#65292;&#24744;&#21487;
	&#33021;&#36824;&#38656;&#35201;&#19978;&#20256;&#25110;&#20462;&#25913;&#30456;&#24212;&#30340;&#25991;
	&#20214;&#25110;&#27169;&#26495;&#65292;&#35814;&#24773;&#35831;&#26597;&#30475;&#26412;&#25554;&#20214;&#30340;&#23433;&#35013;&#35828;&#26126;';
	public $file = '';
	public $hooker = '';
	public $pattern = "";
	public $replacement = "";
	public $method = '';
	
	function __construct(/**/$file){
		//
		$this->file = $file;
	}
	function __destruct() { 
		//
	} 
	
	public function xm_file_exists(){
		return (file_exists($this->file));
	}
	public function xm_hooker_tag_exists(){
		return $this->xm_file_content_exists($this->hooker);
	}
	public function xm_hooker_pattern_exists(){
		return $this->xm_file_content_exists($this->pattern);
	}
	public function xm_file_content_exists($message, $method = 'stripos') {
		if($this->xm_file_exists()) {
			$content = file_get_contents($this->file);
			if($method == 'stripos') {
				return stripos($content, $message);// !== false
			}elseif($method == 'preg_match'){
				return preg_match($message, $content);
			}
		}
		return false;
	}
	public function xm_hooker_auto_add(){
		return $this->xm_file_replace($this->pattern, $this->replacement, $this->methord);
	}
	public function xm_file_replace($pattern, $replace, $method = 'str_replace', $limit = -1) {
		if($this->xm_file_exists()) {
			$content = file_get_contents($this->file);
			if($method == 'str_replace') {
				$content = str_replace($pattern, $replace, $content, $limit);
			}elseif($method == 'preg_replace_callback' || $method == 'preg' && is_callable($replace)) {
				$content = preg_replace_callback($pattern, $replace, $content, $limit);
			}else{
				$content = preg_replace($pattern, $replace, $content, $limit);
			}
			if($content !== false) {
				file_put_contents($this->file, $content);
				return true;
			}/**/
		}
		return false;
	}
	
	
	
}
	/*
	$hooker = '{hook/global_header_xmlns}';
	$pattern = "/<html(( xmlns(\:[a-z0-9]+)?\=(\'|\")[a-z0-9\:\/\.]+(\'|\"))*)>/i";
	$replacement = "<html$1$hooker>";
	$method = "preg_replace";
	if(!xm_file_content_exists($file, $hooker)){
		if(!xm_file_content_exists($file, $pattern, "preg_match") || !xm_file_replace($file, $pattern, $replacement, $method))
			cpmsg($msg, 'action=plugins&operation=config&do='.$pluginid.'&identifier='.$identifier.'&pmod=footercp', 'error');
	}
	
	
	
	
	$hooker = "<!--{hook/global_header_meta}-->";
	$pattern = "/(\t*)(<!--{csstemplate}-->)/i";
	$replacement = "$1$2\r\n$1$hooker";
	if(!xm_file_content_exists($file, $hooker)){
		if(!xm_file_content_exists($file, $pattern, "preg_match") || !xm_file_replace($file, $pattern, $replacement, $method))
			cpmsg($msg, 'action=plugins&operation=config&do='.$pluginid.'&identifier='.$identifier.'&pmod=footercp', 'error');
	}*/


?>