<?php
/**
 * 卸载控制类 For DiscuzX
 * 
 * @author xionghui
 * @author yaoying
 * @version $Id: xwb_uninstall.class.php 836 2011-06-15 01:48:00Z yaoying $
 */
class xwb_uninstall {
	var $v = array();
	var $tpl_dir = "";
	var $error = false;
	var $tips = array();
	var $_sess = null;
	
	function xwb_uninstall(){
		global $_xwb_install;
		$this->tpl_dir = dirname(__FILE__).'/tpl';
		$this->v = $_xwb_install;
		$this->_sess = XWB_plugin::getUser();
		$this->_chkIsAdmin();
	}
	
	function getCfg(){
		$cfg = array();
		$ver = preg_replace('/[^0-9.]/','', XWB_S_VERSION);
		
		$cfg['db_data'] = array();
		$cfg['db_data']['xwb_bind_thread']	= "DROP TABLE IF EXISTS `%s`";
		$cfg['db_data']['xwb_bind_info'] 	= "DROP TABLE IF EXISTS `%s`";
		$cfg['db_data']['xwb_session'] 	= "DROP TABLE IF EXISTS `%s`";
		
		//根据不同的版本配置不同的HAC
		switch ($ver) {
			case '1.5' :
			case '2' :
				break;
			default :
				$this->error('不支持的站点版本： '.XWB_S_VERSION);
			break;
		}
		
		return $cfg ;
		
	}
	
	
	function uninstall($st){
		$st*=1;
		if (!in_array($st,array(0,1))){
			$this->error('非法操作，步骤参数错误！');
		}
		
		$func = 'step'.$st;
		$this->$func();
	}
	
	function step0(){
		
		//检测安装来源
		if( isset($_SERVER['HTTP_REFERER']) && false !== strpos( $_SERVER['HTTP_REFERER'], 'operation' ) ){
			//从dz后台启动
			$this->_sess->setInfo('boot_referer', 'admincp');
		}else{
			//自启动（即在地址栏直接输入）
			$this->_sess->setInfo('boot_referer', 'self');
		}
		
		$image_file = "icon.gif";
		$showTab = 'info';
		$btn_enable = 'class="btn"';
		$btn_name = '确定卸载';
		$link = '?step=1&delete_data=0';
		include $this->tpl_dir.'/uninstall.php';
		exit;
	}
	
	function step1(){
		
		$cfg = $this->getCfg();
		
		$tips = array();
		$st = true;
		
		if (!empty($_GET['delete_data'])){
			//delete db data
			$db = XWB_plugin::getDB();
			foreach ($cfg['db_data'] as $name=>$format){
				$tbSql = sprintf($format, DB::table($name));
				$db->query($tbSql);
				$tips[] = array(1, "删除数据表 [PRE_]$name 成功");
			}
			$_GET['delete_data'] = 1;
		}else{
			$tips[] = array(1, "已保留微博插件数据");
			$_GET['delete_data'] = 0;
		}
		
		$lock_file_output = '论坛目录'. str_replace( dirname(dirname(XWB_P_DATA)), '', $this->v['lock_file'] );
		if( false == @unlink($this->v['lock_file']) ){
			$tips[] = array(1, "无法删除或找不到安装锁定文件（位于：{$lock_file_output}）。如果文件存在，请自行删除。");
		}
		
		$showTab	= 'uninstall';
		$btn_enable = 'class="btn"';
		$btn_name	= $st ? '完成' : '重试';
		
		if( $st ){
			//根据安装来源给出完成跳转链接
			if( $this->_sess->getInfo('boot_referer') == 'admincp'){
				$installtype = 'SC_'. XWB_S_CHARSET;
				if (1.5 == XWB_S_VERSION) {
					//X1.5
					$link= '../../admin.php?action=plugins&operation=pluginuninstall&dir=sina_xweibo&installtype='. $installtype. '&finish=1';
				}else{
					//X2
					$link= '../../admin.php?action=plugins&operation=pluginuninstall&dir=sina_xweibo_x2&installtype='. $installtype. '&finish=1';
				}				
				
			}else{
				$link = '../../index.php';
			}
		}else{
			$link = 'uninstall.php?step=1&delete_data='.$_GET['delete_data'];
		}
		
		$image_file = $st ? 'sucess.png' : "icon.gif";
		
		include $this->tpl_dir.'/uninstall.php';
		exit;
	}
	
	function error($msg){
		$image_file = "icon.gif";
		$showTab = 'error';
		$errorMsg = $msg;
		$btn_enable = 'class="btn"';
		$btn_name = '重试';
		$link = '?step=0';
		include $this->tpl_dir.'/uninstall.php';
		exit;
	}
	
	
	
	function _chkIsAdmin(){
		if( XWB_S_IS_ADMIN != 1 ){
			$this->error('只有管理员才能执行安装程序！');
		}
	}
	
}
?>