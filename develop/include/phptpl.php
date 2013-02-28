<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: phptpl.php 30694 2012-06-12 09:26:01Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$tplyear = dgmdate(TIMESTAMP, 'Y');
$nowdate = dgmdate(TIMESTAMP);
$phptpl['emptyfile'] = <<<EOF
<?php
/**
 *	[$plugin[name]($plugin[identifier].{modulename})] (C)$tplyear-2099 Powered by $plugin[copyright].
 *	Version: $plugin[version]
 *	Date: $nowdate
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//==={code}===
?>
EOF;

$phptpl['baseclass'] = <<<EOF
class plugin_{modulename} {
	//TODO - Insert your code here
//==={code}===
}

EOF;

$phptpl['extendclass'] = <<<EOF

class plugin_{modulename}_{curscript} extends plugin_{modulename} {
	//TODO - Insert your code here
//==={code}===
}

EOF;

$phptpl['specialclass'] = <<<EOF

class threadplugin_$plugin[identifier] {

	public \$name = 'XX主题';			//主题类型名称
	public \$iconfile = 'icon.gif';	//发布主题链接中的前缀图标
	public \$buttontext = '发布xx主题';	//发帖时按钮文字

	/**
	 * 发主题时页面新增的表单项目
	 * @param Integer \$fid: 版块ID
	 * @return string 通过 return 返回即可输出到发帖页面中 
	 */
	public function newthread(\$fid) {
		//TODO - Insert your code here
		
		return 'TODO:newthread';
	}

	/**
	 * 主题发布前的数据判断 
	 * @param Integer \$fid: 版块ID
	 */
	public function newthread_submit(\$fid) {
		//TODO - Insert your code here
		
	}

	/**
	 * 主题发布后的数据处理 
	 * @param Integer \$fid: 版块ID
	 * @param Integer \$tid: 当前帖子ID
	 */
	public function newthread_submit_end(\$fid, \$tid) {
		//TODO - Insert your code here
		
	}

	/**
	 * 编辑主题时页面新增的表单项目
	 * @param Integer \$fid: 版块ID
	 * @param Integer \$tid: 当前帖子ID
	 * @return string 通过 return 返回即可输出到编辑主题页面中 
	 */
	public function editpost(\$fid, \$tid) {
		//TODO - Insert your code here
		
		return 'TODO:editpost';
	}

	/**
	 * 主题编辑前的数据判断 
	 * @param Integer \$fid: 版块ID
	 * @param Integer \$tid: 当前帖子ID
	 */
	public function editpost_submit(\$fid, \$tid) {
		//TODO - Insert your code here
		
	}

	/**
	 * 主题编辑后的数据处理 
	 * @param Integer \$fid: 版块ID
	 * @param Integer \$tid: 当前帖子ID
	 */
	public function editpost_submit_end(\$fid, \$tid) {
		//TODO - Insert your code here
		
	}

	/**
	 * 回帖后的数据处理 
	 * @param Integer \$fid: 版块ID
	 * @param Integer \$tid: 当前帖子ID
	 */
	public function newreply_submit_end(\$fid, \$tid) {
		//TODO - Insert your code here
		
	}

	/**
	 * 查看主题时页面新增的内容
	 * @param Integer \$tid: 当前帖子ID
	 * @return string 通过 return 返回即可输出到主题首贴页面中
	 */
	public function viewthread(\$tid) {
		//TODO - Insert your code here
		
		return 'TODO:viewthread';
	}
}

EOF;

$phptpl['methodtpl'] = <<<EOF
	/**
	 * @Methods describe
	 * @return {returncomment} type
	 */
	public function {methodName}() {
		//TODO - Insert your code here
		
		return {return};	//TODO modify your return code here
	}

EOF;

$phptpl['magic'] = <<<EOF
class magic_{name} {
	public \$version = '$plugin[version]';	//脚本版本号
	public \$name = '{name}';				//道具名称 (可填写语言包项目)
	public \$description = '{desc}';		//道具说明 (可填写语言包项目)
	public \$price = '20';	//道具默认价格
	public \$weight = '20';	//道具默认重量
	public \$useevent = 0;
	public \$targetgroupperm = false;
	public \$copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';	//版权 (可填写语言包项目)
	public \$magic = array();
	public \$parameters = array();

	/**
	 * 返回设置项目
	 */
	public function getsetting(&\$magic) {
		//TODO - Insert your code here
	}

	/**
	 * 保存设置项目
	 */
	public function setsetting(&\$magicnew, &\$parameters) {
		//TODO - Insert your code here
	}

	/**
	 * 道具使用
	 */
	public function usesubmit() {
		//TODO - Insert your code here
	}

	/**
	 * 道具显示
	 */
	public function show() {
		//TODO - Insert your code here
	}
}
EOF;
$phptpl['cron'] = <<<EOF
<?php
/**
 *	[$plugin[name]($plugin[identifier].{modulename})] (C)$tplyear-2099 Powered by $plugin[copyright].
 *	Version: $plugin[version]
 *	Date: $nowdate
 *	Warning: Don't delete this comment
 *
 *	cronname:{name}
 *	week:{weekday}
 *	day:{day}
 *	hour:{hour}
 *	minute:{minute}
 *	desc:{desc}
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//TODO - Insert your code here
?>

EOF;
$phptpl['adv'] = <<<EOF
class adv_{name} {

	public \$version = '$plugin[version]';	//脚本版本号
	public \$name = '{name}';				//广告类型名称 (可填写语言包项目)
	public \$description = '{desc}';		//广告类型说明 (可填写语言包项目)
	public \$copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';	//版权 (可填写语言包项目)
	public \$targets = array('portal', 'home', 'member', 'forum', 'group', 'userapp', 'plugin', 'custom');	//广告类型适用的投放范围
	public \$imagesizes = array();	//广告规格例：array('468x60', '658x60', '728x90', '760x90', '950x90')

	/**
	 * 返回设置项目
	 */
	public function getsetting() {
		//TODO - Insert your code here
	}

	/**
	 * 保存设置项目
	 */
	public function setsetting(&\$advnew, &\$parameters) {
		//TODO - Insert your code here
	}

	/**
	 * 广告显示时的运行代码
	 */
	public function evalcode() {
		//TODO - Insert your code here
	}

}
EOF;
$phptpl['task'] = <<<EOF
class task_{name} {

	public \$version = '$plugin[version]';	//脚本版本号
	public \$name = '{name}';	//任务名称 (可填写语言包项目)
	public \$description = '{desc}';	//任务说明 (可填写语言包项目)
	public \$copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';	//版权 (可填写语言包项目)
	public \$icon = '';		//默认图标
	public \$period = '';	//默认任务间隔周期
	public \$periodtype = 0;//默认任务间隔周期单位
	public \$conditions = array();	//任务附加条件

	/**
	 * 申请任务成功后的附加处理
	 */
	public function  preprocess(\$task) {
		//TODO - Insert your code here
	}

	/**
	 * 判断任务是否完成 (返回 TRUE:成功 FALSE:失败 0:任务进行中进度未知或尚未开始  大于0的正数:任务进行中返回任务进度)
	 */
	public function csc(\$task = array()) {
		//TODO - Insert your code here
	}

	/**
	 * 完成任务后的附加处理
	 */
	public function sufprocess(\$task) {
		//TODO - Insert your code here
	}

	/**
	 * 任务显示
	 */
	public function view() {
		//TODO - Insert your code here
	}

	/**
	 * 任务安装的附加处理
	 */
	public function install() {
		//TODO - Insert your code here
	}

	/**
	 * 任务卸载的附加处理
	 */
	public function uninstall() {
		//TODO - Insert your code here
	}

	/**
	 * 任务升级的附加处理
	 */
	public function upgrade() {
		//TODO - Insert your code here
	}
}
EOF;
$phptpl['secqaa'] = <<<EOF
class secqaa_{name} {

	public \$version = '$plugin[version]';	//脚本版本号
	public \$name = '{name}';	//验证问答名称 (可填写语言包项目)
	public \$description = '{desc}';	//验证问答说明 (可填写语言包项目)
	public \$copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';	//版权 (可填写语言包项目)
	public \$customname = '';

	/**
	 * 返回安全问答的答案和问题 (\$question 为问题，函数返回值为答案)
	 */
	public function make(&\$question) {
		//TODO - Insert your code here
	}
}
EOF;
$phptpl['seccode'] = <<<EOF
class seccode_{name} {

	public \$version = '$plugin[version]';
	public \$name = '{name}';
	public \$description = '{desc}';
	public \$copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	public \$customname = '';

	/**
	 * 检查输入的验证码，返回 true 表示通过
	 */
	public function check(\$value, \$idhash) {
		//TODO - Insert your code here
	}

	/**
	 * 输出验证码，echo 输出内容将显示在页面中
	 */
	public function make() {
		//TODO - Insert your code here
	}
}
EOF;
$phptpl['sqlcode'] = <<<EOFSQL

\$sql = <<<EOF
{sql}
EOF;

runquery(\$sql);
\$finish = true;
EOFSQL;
?>