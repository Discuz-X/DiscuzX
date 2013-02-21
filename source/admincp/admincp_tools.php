<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tools.php 27301 2012-01-13 07:23:05Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'updatecache') {

	$step = max(1, intval($_GET['step']));
	shownav('tools', 'nav_updatecache');
	showsubmenusteps('nav_updatecache', array(
		array('nav_updatecache_confirm', $step == 1),
		array('nav_updatecache_verify', $step == 2),
		array('nav_updatecache_completed', $step == 3)
	));

	showtips('tools_updatecache_tips');

	if($step == 1) {
		cpmsg("<input type=\"checkbox\" name=\"type[]\" value=\"data\" id=\"datacache\" class=\"checkbox\" checked /><label for=\"datacache\">".$lang[tools_updatecache_data]."</label><input type=\"checkbox\" name=\"type[]\" value=\"tpl\" id=\"tplcache\" class=\"checkbox\" checked /><label for=\"tplcache\">".$lang[tools_updatecache_tpl]."</label><input type=\"checkbox\" name=\"type[]\" value=\"blockclass\" id=\"blockclasscache\" class=\"checkbox\" /><label for=\"blockclasscache\">".$lang[tools_updatecache_blockclass].'</label>', 'action=tools&operation=updatecache&step=2', 'form', '', FALSE);
	} elseif($step == 2) {
		$type = implode('_', (array)$_GET['type']);
		cpmsg(cplang('tools_updatecache_waiting'), "action=tools&operation=updatecache&step=3&type=$type", 'loading', '', FALSE);
	} elseif($step == 3) {
		$type = explode('_', $_GET['type']);
		if(in_array('data', $type)) {
			updatecache();
			require_once libfile('function/group');
			$groupindex['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
			$groupindex['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.commoncredits', 'ff.membernum', 'ff.icon'), 10);
			$groupindex['updateline'] = TIMESTAMP;
			$groupdata = C::t('forum_forum')->fetch_group_counter();
			$groupindex['todayposts'] = $groupdata['todayposts'];
			$groupindex['groupnum'] = $groupdata['groupnum'];
			savecache('groupindex', $groupindex);
			C::t('forum_groupfield')->truncate();
			savecache('forum_guide', '');
		}
		if(in_array('tpl', $type) && $_G['config']['output']['tplrefresh']) {
			cleartemplatecache();
		}
		if(in_array('blockclass', $type)) {
			include_once libfile('function/block');
			blockclass_cache();
		}
		cpmsg('update_cache_succeed', '', 'succeed', '', FALSE);
	}

} elseif($operation == 'fileperms') {

	$step = max(1, intval($_GET['step']));

	shownav('tools', 'nav_fileperms');
	showsubmenusteps('nav_fileperms', array(
		array('nav_fileperms_confirm', $step == 1),
		array('nav_fileperms_verify', $step == 2),
		array('nav_fileperms_completed', $step == 3)
	));

	if($step == 1) {
		cpmsg(cplang('fileperms_check_note'), 'action=tools&operation=fileperms&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('fileperms_check_waiting'), 'action=tools&operation=fileperms&step=3', 'loading', '', FALSE);
	} elseif($step == 3) {

		showtips('fileperms_tips');

		$entryarray = array(
			'data',
			'data/attachment',
			'data/attachment/album',
			'data/attachment/category',
			'data/attachment/common',
			'data/attachment/forum',
			'data/attachment/group',
			'data/attachment/portal',
			'data/attachment/profile',
			'data/attachment/swfupload',
			'data/attachment/temp',
			'data/cache',
			'data/log',
			'data/template',
			'data/threadcache',
			'data/diy'
		);

		$result = '';
		foreach($entryarray as $entry) {
			$fullentry = DISCUZ_ROOT.'./'.$entry;
			if(!is_dir($fullentry) && !file_exists($fullentry)) {
				continue;
			} else {
				if(!dir_writeable($fullentry)) {
					$result .= '<li class="error">'.(is_dir($fullentry) ? $lang['dir'] : $lang['file'])." ./$entry $lang[fileperms_unwritable]</li>";
				}
			}
		}
		$result = $result ? $result : '<li>'.$lang['fileperms_check_ok'].'</li>';
		echo '<div class="colorbox"><ul class="fileperms">'.$result.'</ul></div>';
	}
} elseif($operation == 'jsdecode') {

	

	shownav('tools', 'nav_jsdecode');
	showsubmenusteps('nav_jsdecode');
	showtableheader();

?>
<script type="text/javascript" src="/static/js/JsDecoder.js"></script>
<script type="text/javascript" src="/static/js/JsColorizer.js"></script>
<script type="text/javascript">
    var base_code = '';
    var jsdecoder;
    var jscolorizer;
    var code = '';
    var time = 0;
    function decode()
    {
        code = '';
        base_code = '';
        jsdecoder = new JsDecoder();
        jscolorizer = new JsColorizer();
        if ($('msg').innerHTML.length) {
            do_clean_init();
        } else {
            jsdecoder.s = $("a1").value;
            do_decode_init();
        }
    }
    function do_decode_init()
    {
        $('msg').innerHTML += 'Decoding .. ';
        setTimeout(do_decode, 50);
    }
    function do_decode()
    {
        time = time_start();
        try {
            code = jsdecoder.decode();
            base_code = code;
        } catch (e) {
            $('msg').innerHTML += 'error<br><br>'+new String(e).replace(/\n/g, '<br>');
            return;
        }
        $('msg').innerHTML += 'ok ('+time_end(time)+' sec)<br>';
        setTimeout(do_colorize_init, 50);
    }
    function do_colorize_init()
    {
        $('msg').innerHTML += 'Colorizing .. ';
        setTimeout(do_colorize, 50);
    }
    function do_colorize()
    {
        time = time_start();
        code = code.replace(/&/g, "&amp;");
        code = code.replace(/</g, "&lt;");
        code = code.replace(/>/g, "&gt;");
        jscolorizer.s = code;
        try {
            code = jscolorizer.colorize();
        } catch (e) {
            $('msg').innerHTML += 'error<br><br>'+new String(e).replace(/\n/g, '<br>');
            return;
        }
        $('msg').innerHTML += 'ok ('+time_end(time)+' sec)<br>';
        /* debug:
        $('msg').innerHTML += '&nbsp;&nbsp;&nbsp;&nbsp;'+jscolorizer.showTimes().replace(/\n$/, '').replace(/\n/g, '<br>&nbsp;&nbsp;&nbsp;&nbsp;')+'<br>';
        */
        setTimeout(do_insert_init, 50);
    }
    function do_insert_init()
    {
        $('msg').innerHTML += 'Inserting code .. ';
        setTimeout(do_insert, 50);
    }
    function do_insert()
    {
        time = time_start();
        try {
        
            code = new String(code);
            code = code.replace(/(\r\n|\r|\n)/g, "<br>\n");
            code = code.replace(/<font\s+/gi, '<font@@@@@');
            code = code.replace(/( |\t)/g, '&nbsp;');
            code = code.replace(/<font@@@@@/gi, '<font ');

            code = code.replace(/\n$/, '');

            var count = 0;
            var pos = code.indexOf("\n");
            while (pos != -1) {
               count++;
               pos = code.indexOf("\n", pos+1);
            }
            count++;

            pad = new String(count).length;
            var lines = '';

            for (var i = 0; i < count; i++) {
                var p = pad - new String(i+1).length;
                var no = new String(i+1);
                for (k = 0; k < p; k++) { no = '&nbsp;'+no; }
                no += '&nbsp;';
                lines += '<div style="background: #fff; color: #666;text-align:right;">'+no+'</div>';
            }
            $('lines').innerHTML = lines;

            $('code_area').style.display = 'block';
            $('sel_all').style.display = 'block';
            $("a2").innerHTML = code;

        } catch (e) {
            $('msg').innerHTML += 'error<br><br>'+new String(e).replace(/\n/g, '<br>');
            return;
        }
        
        $('msg').innerHTML += 'ok ('+time_end(time)+' sec)';
        code = '';
    }
    function do_clean_init()
    {
        //$('msg').innerHTML = 'Removing code .. ';
        //setTimeout(do_clean, 50);

        $('msg').innerHTML = '';
        do_clean();
    }
    function do_clean()
    {
        time = time_start();
        //$('lines').innerHTML = '';
        //$('a2').innerHTML = '';
        $('code_area').style.display = 'none';
        base_code = '';
        $('sel_all').style.display = 'none';
        //$('insert_area').value = '';
        $('insert_div').style.display = 'none';
        jsdecoder.s = $("a1").value;
        
        //$('msg').innerHTML += 'ok ('+time_end(time)+' sec)<br>';
        //setTimeout(do_decode_init, 50);

        do_decode_init();
    }
    function insert_textarea()
    {
        $('insert_div').style.display = 'block';
        $('insert_area').value = base_code;
        $('insert_area').focus();
        $('insert_area').select();
    }
    function $(id)
    {
        return document.getElementById(id);
    }
    function time_micro()
    {
        var micro = new String(new Date().getTime());
        micro = micro.substr(0, micro.length-3) + '.' + micro.substr(micro.length-3, 3);
        return parseFloat(micro);
    }
    function time_start()
    {
        return time_micro();
    }
    function time_get(start)
    {
        return time_micro() - start;
    }
    function time_end(start)
    {
        return time_round(time_micro() - start);
    }
    function time_round(time)
    {
        time = Math.round(time * 100) / 100;
        if (time === 0) { time = 0.01; }
        return time;
    }
    </script>

<?php
	
	showsetting('nav_jsdecode_ppp', 'a1', $setting['postperpage'], 'textarea');
	showtablerow('', array('class="td21"'), array(
		'<input type="button" class="btn" name="forumsubmit" onclick="decode()" value="'.$lang['submit'].'" />'
	));
	
?>

<div id="msg" style="font-family: courier new; font-size: 12px; background: #ffffd7; margin: 1em 0;text-align:left"></div>
					<div id="sel_all" style="display: none; margin: 1em 0; text-align:left"><a href="javascript:void(0)" onclick="insert_textarea()">在textarea中显示，容易复制，没有冗余代码(点击这里)</a></div>
					<div id="insert_div" style="display: none; margin: 1em 0;text-align:left">
						<textarea cols="80" rows="12" id="insert_area"></textarea>
					</div>
					<div id="code_area" style="display: none;">
						<table cellspacing="0" cellpadding="0" style="font-family: courier new; font-size: 12px;" width="100%">
							<tr>
								<td valign="top" id="lines" width="300"></td>
								<td nowrap id="a2" style="background: #f5f5f5;text-align:left"></td>
							</tr>
						</table>
					</div>
<?php
	
}

function jsinsertunit() {

?>
<script type="text/JavaScript">
function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function insertunit(text, obj) {
	if(!obj) {
		obj = 'jstemplate';
	}
	$(obj).focus();
	if(!isUndefined($(obj).selectionStart)) {
		var opn = $(obj).selectionStart + 0;
		$(obj).value = $(obj).value.substr(0, $(obj).selectionStart) + text + $(obj).value.substr($(obj).selectionEnd);
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		sel.text = text.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character', -strlen(text));
	} else {
		$(obj).value += text;
	}
}
</script>
<?php

}

?>