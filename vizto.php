<?PHP
/**
 * Created by IntelliJ IDEA.
 * User: 航波
 * Date: 14-2-21
 * Time: 上午9:08
 */

define('IN_ADMINCP', TRUE);
define('NOROBOT', TRUE);
define('ADMINSCRIPT', basename(__FILE__));
define('CURSCRIPT', 'vizto');
define('HOOKTYPE', 'hookscript');
define('APPTYPEID', 2);


require './source/class/class_core.php';
require './source/function/function_misc.php';
require './source/function/function_forum.php';
require './source/function/function_admincp.php';
require './source/function/function_cache.php';

$discuz = C::app();
$discuz->init();

$admincp = new discuz_admincp();
$admincp->core  = & $discuz;
$admincp->init();


error_reporting(E_ALL | E_STRICT);
$upload_handler = new UploadHandler();