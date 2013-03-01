<?php
/*
 * 备忘：
 * 1. 所有数据都必须编码为UTF-8再传递
 * 2. 添加调试开关，控制输出信息
 * 3. 添加日志记录，包括本地及远程Api
 * 4. 归纳错误和过滤规则，另成文件      OK
 */
error_reporting(0);

require_once 'plugin.env.php';
require_once XWB_P_ROOT . '/xplugin_apis/apiLoader.php';

XWB_plugin::init();
$apiLoader = new apiLoader();

if(0 === strcasecmp('post', $_SERVER['REQUEST_METHOD']))
{		
    $A = dstripslashes($_POST['A']);
    $P = dstripslashes($_POST['P']);
    $T = dstripslashes($_POST['T']);
    $F = dstripslashes($_POST['F']);
    $RT = $apiLoader->load($A, $P, $T, $F);

} else {
    $A = 'apiSystem.checkApi';
    $P = json_encode(dstripslashes(isset($_POST['params']) ? (array)$_POST['params'] : array()));
    $T = time();
    $F = md5(sprintf("#%s#%s#%s#%s#%s#", XWB_APP_KEY, $A, $P, $T, XWB_plugin::pCfg('encrypt_key')));
    $RT = $apiLoader->load($A, $P, $T, $F);
}

exit(json_encode($RT));

function dump($var, $desc = false) {
    echo '<pre>';
    if($desc) {
        var_dump($var);
    } else {
        print_r($var);
    }
    echo '</pre>';
    exit;
}
