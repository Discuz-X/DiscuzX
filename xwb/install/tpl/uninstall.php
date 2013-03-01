<?php !defined('IN_XWB_INSTALL_ENV') && exit('ACCESS DENIED IN INSTALL TPL'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>微博插件卸载</title>
<link href="tpl/style.css" type="text/css" rel="stylesheet" />
</head>
<script language="javascript">
function chgUninstallLink(){
	var delete_data = document.getElementById('is_delete_tb').checked ? 0 : 1;
	var url = '?step=1&delete_data='+delete_data;
	document.getElementById('uninstall_link').href=url;
}
</script>
<body>
<div class="container">
	<div class="headerUninstall">
		<h1>新浪微博<?php echo XWB_S_NAME;?>插件卸载</h1>
	</div>
	<div class="main">
		<div class="mainTxt">
			<img src="tpl/images/<?php echo $image_file;?>" class="txticon" align="absmiddle"/>
			<p class="title">插件卸载</p>
            
			<?php if ($showTab=='info') {  ?>
                <div class="con" >
                <p>
                1. 贵网站微博功能将自动失效。<br/>
                2. 页面内所有微博图标按钮将被移除。<br/>
                3. 微博插件相关文件将被还原或者删除。<br/>
                4. 用户和微博的绑定数据将被清除。<br/>
                5. 请在卸载完成后手工删除微博插件目录/并更新缓存。<br/><br/>
                <input id="is_delete_tb" onclick="chgUninstallLink();" type="checkbox" name="is_delete_tb" checked="checked" />
                保留微博插件数据<br/>
                <font color="#FF0000">升级插件的用户，请保留微博插件数据，否则用户帐号与新浪微博绑定关系将丢失。</font>
                </p>
              </div>  
            <?php } ?>
            
            <?php if ($showTab=='uninstall') {  ?>
				<?php 
					foreach ($tips as $tip) {
                    $c = empty($tip[0]) ? 'con red' : 'con';
                ?>
                <div class="<?php echo $c;?>"><p><?php echo $tip[1];?></p></div>
                <?php } ?>
            <?php } ?>
            
            <?php if ($showTab=='error') {  ?>
                <div class="con red"><p><?php echo $errorMsg;?></p></div>
            <?php } ?>
             
		</div>
		<div class="clear"></div>
		
		<div class="btnbox">
			<form>
			<a id="uninstall_link" href="<?php echo $link;?>"  target="_parent"  <?php echo $btn_enable;?> > <?php echo $btn_name;?> </a>
			<!--<a href="#" class="btn dis" onclick="window.close();">取消</a> -->
			</form>
		</div>
		<div class="footer">Copyright &copy; 1996-2010 SINA</div>
		</div>
</div>

<?php
//统计上报[卸载]
if( $showTab == 'uninstall' ){
	$xwb_statType = 'un';
	$xwb_statArgs = array();
	$xwb_statArgs['uid'] = 0;    //一定要为0，否则在卸载数据库的时候出错！
	$xwb_statArgs['domain'] = str_replace( array('http://', 'https://'), '', XWB_plugin::baseUrl() );
	if( !$st ){
		$xwb_statArgs['debug'] = 'REVERT_SITE_FILE_FAILURE'; 
	}
	
	echo XWB_plugin::statUrl( $xwb_statType, $xwb_statArgs, true );
	
}
?>

</body>
</html>
