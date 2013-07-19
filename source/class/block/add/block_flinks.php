<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_thread.php 31847 2012-10-17 04:38:16Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_flinks {


	function block_flinks(){
		$this->setting = array(
			'param1' => array(
				'title' => '数据调用参数1',
				'type' => 'text',
				'default' => ''
			),
			'param2' => array(
				'title' => '数据调用参数2',
				'type' => 'mcheckbox',
				'value' => array(
					array('1', '选项1'),
					array('2', '选项2'),
				),
				'default' => '1'
			),
			'groupid'	=> array(
				'title' => '链接组',
				'type' => 'select',
				'value' => array()
			),
		);
	}
	/**
	 * 必须！
	 * 返回本数据调用类的显示名称（显示在创建模块时选择“模块数据”的下拉列表里）
	 * @return <type>
	 */
	function name() {
		return '资源链接类';
	}

	/**
	 * 必须！
	 * 返回一个数组： 第一个值为本数据类所在的模块分类；第二个值为模块分类显示的名称（显示在 DIY 模块面板）
	 * @return <type>
	 */
	function blockclass() {
		return array('sample', '资源链接');
	}

	/**
	 * 必须！
	 * 返回数据类中可供“模块样式”使用的字段。
	 * 格式见示例：
	 * name 为该字段的显示名称
	 * formtype 决定编辑单条数据时该字段的显示方式： 类型有： text, textarea, date, title, summary, pic； 详见 portalcp_block.htm 模板（搜 $field[formtype] ）
	 * datatype 决定该字段的数据展示，类型有： string, int, date, title, summary, pic； 详见 function_block.php 中 block_template 函数
	 * @return <type>
	 */
	function fields() {
		return array(
			'field1' => array('name' => '示例字段1', 'formtype' => 'text', 'datatype' => 'string'),
			'field2' => array('name' => '示例字段2', 'formtype' => 'title', 'datatype' => 'title'),
		);
	}

	/**
	 * 必须！
	 * 返回使用本数据类调用数据时的设置项
	 * 格式见示例：
	 * title 为显示的名称
	 * type 为表单类型， 有： text, password, number, textarea, radio, select, mselect, mradio, mcheckbox, calendar； 详见 function_block.php 中 block_makeform() 函数
	 * @return <type>
	 */
	function getsetting() {
		$settings = $this->setting;
		$query = DB::query("SELECT title,pid FROM ".DB::table('vizto_flink'));
		while($group = DB::fetch($query)) {
			$settings['groupid']['value'][] = array($group['pid'], $group['title']);
		}

		return $settings;/**/
	}

	/**
	 * 必须！
	 * 处理设置参数，返回数据
	 * 返回数据有两种：
	 * 一种是返回 html，放到模块 summary 字段，直接显示； 返回格式为： array('html'=>'返回内容', 'data'=>null)
	 * 一种是返回 data，通过模块样式渲染后展示，返回的数据应该包含 fields() 函数中指定的所有字段； 返回格式为： array('html'=>'', 'data'=>array(array('title'=>'value1'), array('title'=>'value2')))
	 * 特别的：
	 * parameter 参数包含 getsetting() 提交后的内容； 并附加了字段：
	 * items ，为用户指定显示的模块数据条数；
	 * bannedids ，为用户选择屏蔽某数据时记录在模块中的该数据 id。 应该在获取数据时屏蔽该数据；
	 *
	 * 如果返回的数据给 data， 那么应该包含 fields() 函数指定的所有字段。并附加以下字段：
	 * id 标志该数据的 id，如果用户屏蔽某数据时，会将该数据的 id 添加到 parameter[bannedids] 里
	 * idtype 标志该数据的 idtype
	 *
	 * @param <type> $style 模块样式（见 common_block_style 表）。 可以根据模块样式中用到的字段来选择性的获取/不获取某些数据
	 * @param <type> $parameter 用户对 getsetting() 给出的表单提交后的内容。
	 * @return <type>
	 */
	function getdata($style, $parameter) {

		// 返回summary
//		return array('html' => '<p>这是一个演示模块数据类</p>', 'data' => null);

		// 返回数据
		// 需要注意： 除 id，idtype， title， url， pic， picflag， summary 几个字段外，其它字段需要放到 fields 数组里。 可以参考系统内置模块类 source/class/block/block_thread.php
		/*return array('html'=>'', 'data' => array(
			array(
				'id' => '1',
				'idtype' => 'sampleid',
				'title' => 'title1',
				'url' => '#',
				'pic' => 'nophoto.gif',
				'picflag' => '1',
				'summary' => '',
				'fields' => array(
					'field1' => 'value1'
				)
			)
		));*/
		$returndata = array('html' => '', 'data' => null);
		//$query = DB::query("SELECT * FROM ".DB::table('custom_usefullinks')." WHERE linktype='0' AND groupid=".$parameter['groupid']." ORDER BY displayorder ASC");
		//$titleData = DB::fetch_first("SELECT title FROM ".DB::table('custom_usefullinks')." WHERE linktype='1' AND groupid=".$parameter['groupid']);
		$query = DB::query('SELECT * FROM '.DB::table('vizto_flink').' WHERE pid='.$parameter['groupid']);
		$data = DB::fetch($query);
		$returndata['html'] .= '<div class="flink_block"><div class="title flinks" groupid="'.$parameter['groupid'].'"><span class="titletext">'.$data['title'].'</span></div><ul>';
		$links = dunserialize($data['data']);
		foreach($links as $key => $data){
			$returndata['html'] .= '<li><a href="'.$data->href.'" title="'.$data->text.'" target="_blank">'.$data->text.'</a> </li>';
		}
		$returndata['html'] .= '</ul></div>';
		return $returndata;
	}

}

?>