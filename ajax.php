<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 13-2-17
 * Time: 下午4:29
 * To change this template use File | Settings | File Templates.
 */

//echo file_get_contents("php://input");

require './source/class/class_core.php';
//print_r(file_get_contents('php://input'));
$discuz = C::app();
//print_r($discuz);
$discuz->init();

$___update = file_get_contents('php://input');
$___update = explode('&', $___update);
foreach($___update as $___item){
	//$___item = $___update[1];
	//print_r($___update);
	//print_r('item:'.$___item.'<br />');
	//print_r('value:'.$value);
	$regex = '/^uid([\d]+)=d([\d]+)g([\d]+)$/i';

	if(preg_match($regex, $___item, $matches)){
		//print_r($matches);
		C::t('custom_usefullinks')->update_by_uid($matches[1],array('displayorder'=>$matches[2], 'groupid'=>$matches[3]));
	}
}


?>