<?PHP (defined('IN_DISCUZ') && defined('IN_ADMINCP')) || die('Access Denied');
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: menu_synchronisation.php 25593 2011-11-15 10:56:04Z yexinhao $
 */

$topmenu['synchronisation'] = '';
$menu['synchronisation'][]  = array('menu_sync_sync', 'synchronisation');
$menu['synchronisation'][]  = array('menu_sync_batchupload', 'synchronisation_batchupload');