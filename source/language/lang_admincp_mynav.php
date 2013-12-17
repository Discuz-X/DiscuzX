<?PHP defined('IN_DISCUZ') || die('Access Denied');


$extend_lang = array(
	'menu_mynav_mytest' => '我的项目',
	'menu_mynav_templateflag' => '模板处理标识',
	'flags_template' => '特殊模板处理标识',
	'flags_flags_template' => '特殊模板处理标识',
	'flags_flags_template_comment' => '用于非标准模板（common/header,common/footer）标识识别,用逗号(,)分隔',
);
$GLOBALS['admincp_actions_normal'][] = 'mynav';