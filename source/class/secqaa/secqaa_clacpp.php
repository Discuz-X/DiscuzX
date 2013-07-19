<?php
/* Author:Discuz & Coxxs */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class secqaa_clacpp {

	var $version = '1.0';
	var $name = '100 &#20197;&#20869;&#21152;&#20943;&#20056;&#38500;&#27861;';
	var $description = '&#38543;&#26426;&#26174;&#31034; 100 &#20197;&#20869;&#21152;&#20943;&#20056;&#38500;&#27861;&#30340;&#39564;&#35777;&#38382;&#31572;';
	var $copyright = 'Coxxs';
	var $customname = '';

	function make(&$question) {
		$a = rand(10, 90);
		$b = rand(1, 10);
		$r = rand(0, 3);
		if($r == 0) {
			$question = $a.' + '.$b.' = ?';
			$answer = $a + $b;
		} else if ($r == 1) {
			$question = $a.' - '.$b.' = ?';
			$answer = $a - $b;
		} else if ($r == 2) {
			$a = $a % 10 + 1;
			$question = $a.' * '.$b.' = ?';
			$answer = $a * $b;
		} else {
			$a = $a % 10 + 1;
			$b = $a * $b;
			$question = $b.' / '.$a.' = ?';
			$answer = $b / $a;		
		}
		return $answer;
	}

}
?>