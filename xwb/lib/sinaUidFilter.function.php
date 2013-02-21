<?php
/**
 * 过滤已经绑定到新浪微博的discuz!帐号
 * @version $Id: sinaUidFilter.function.php 817 2011-06-02 07:38:51Z yaoying $
 * @param array $uid 要过滤的uid号码数组。传参前，请自行保证里面的全是int。此处不作检查
 * @param bool $remote 是否也进行远程api查询？默认为否
 * @return array
 */
function sinaUidFilter($uid, $remote = false) {
	$sina_uid = array();
	if( empty($uid) ){
		return $sina_uid;
	}
	
	$rs = XWB_plugin::getBatchBindUser($uid, $remote); //远程API
	foreach($rs as $row) {
		$sina_uid[$row['uid']] = $row['sina_uid'];
	}
	return $sina_uid;
}
