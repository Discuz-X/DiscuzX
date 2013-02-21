<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>注册页面</title>
<link type="text/css" rel="stylesheet" href="<?php echo XWB_plugin::getPluginUrl('images/xwb_'. XWB_S_VERSION .'.css');?>" />
<script language="javascript">
	document.onkeydown = function (e){
			var ev =  e || window.event ;
			if(ev.keyCode==13 && parent.XWBcontrol.isEndReg){
				parent.XWBcontrol.close('reg');
				window.top.location='index.php';
			}
	}

	function $id (id) {return document.getElementById(id);}

	function xwbSetTips(msg){
		/// 注册成功
		if (msg[0]>0){
			// 是否提示成功
			if (msg[2]==1){
                var regBox = $id('regBox');
                var action = 'none' == regBox.style.display ? 'bind' : 'reg';
                var head = 'none' == regBox.style.display ? 'forBindH3' : 'forRegH3';
                var forHead = $id(head);
                forHead.style.display = 'none';
				$id(action+'SuccessBox').style.display	= '';
				$id(action+'Box').style.display			= 'none';
				$id(action+'SuccessTips').innerHTML		= msg[1];
				parent.XWBcontrol.isEndReg = true;
				parent.XWBcontrol.ck.del('xwb_tips_type');
			}else{
				window.top.location='index.php';
			}
		}else{
			setError(msg[1]);
		}
	}

	function submitForm(id){
        clearError();
		$id(id).submit();
	}

	function setError(msg){
		clearError();
        var action = 'none' == $id('regBox').style.display ? 'bind' : 'reg';
		$id(action+'ErrorTips').style.display = '';
		$id(action+'ErrorTips').innerHTML = msg;
	}

	function clearError(){
        var action = 'none' == $id('regBox').style.display ? 'bind' : 'reg';
		$id(action+'ErrorTips').style.display = 'none';
	}

    function forTurn(action) {
        $id(action+'ErrorTips').style.display = 'none';
        var title = parent.document.getElementById('_xwb_dlg_tle');
        var forRegH3 = $id('forRegH3');
        var forBindH3 = $id('forBindH3');
        var regBox = $id('regBox');
        var bindBox = $id('bindBox');
        //var inputIds = new Array('siteRegName', 'siteRegEmail', 'regPwd', 'siteBindName', 'bindPwd', 'questionid', 'questionanswer');
        //var x;
        //for (x in inputIds) {
            //if ('questionid' == inputIds[x]) {
                //$id(inputIds[x]).value = 0;
            //} else {
                //$id(inputIds[x]).value = ''
            //}
        //};

        if ('reg' == action) {
            title.innerHTML = '用户登录';
            forBindH3.style.display = 'none';
            bindBox.style.display = 'none';
            forRegH3.style.display = '';
            regBox.style.display = '';
        } else {
            title.innerHTML = '账号绑定';
            forRegH3.style.display = 'none';
            regBox.style.display = 'none';
            forBindH3.style.display = '';
            bindBox.style.display = '';
        }
    }

    function selectChange()
    {
        var questionid = $id('questionid');
        var bindTr = $id('bindTr');
        var lastCellIndex = bindTr.cells.length-1;
        for(var i=lastCellIndex;i>=0;i--) {bindTr.deleteCell(i);}
        if(0 == questionid.value){
            var newTd = bindTr.insertCell(0);
            newTd.colSpan = 3;
            newTd.className = 'xwb-plugin-td-msg';
            newTd.innerHTML = '<label>　</label>';
        } else {
            var newTd_1 = bindTr.insertCell(0);
            newTd_1.className = 'xwb-plugin-td-msg';
            newTd_1.innerHTML = '<label for="questionanswer">回答：</label>';
            var newTd_2 = bindTr.insertCell(1);
            newTd_2.className = 'xwb-plugin-td-input';
            newTd_2.innerHTML = '<input name="questionanswer" type="text" class="xwb-plugin-input-a" id="questionanswer" maxlength="256" autocomplete="off" />';
        }
    }
</script>
</head>

<body id="xwb-plugin-register-layer" class="xwb-plugin">
<h3 id="forRegH3" class="xwb-plugin-layer-title">完善账号信息，快速开始</h3>
<h3 id="forBindH3" class="xwb-plugin-layer-title" style="display:none;">一步登陆，绑定账号</h3>
<div id="regBox" class="xwb-plugin-form" >
    <form action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doReg");?>" id="siteRegFrom"  method="post" target="xwbSiteRegister"  >
        <table class="xwb-plugin-table">
            <tr class="xwb-plugin-tr-error">
                <td colspan="3">
                    <em id="regErrorTips" class="xwb-plugin-error" style="display:none;"></em>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="siteRegName"> 设置用户名：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteRegName" id="siteRegName" class="xwb-plugin-input-a" value="<?php echo $sina_user_info['screen_name'];?>" />
                </td>
                <td rowspan="4" class="xwb-plugin-td-right-msg">
                    已经有<?php echo XWB_S_TITLE ;?>账号？<br/><br/>
                    <a href="javascript:void(function(){})" onclick="forTurn('bind')" tabindex="-1">绑定我的账号</a>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="siteRegEmail"> 邮箱：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteRegEmail"	id="siteRegEmail" class="xwb-plugin-input-a" />
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="regPwd"> 密码：</label></td>
                <td class="xwb-plugin-td-input">
                    <input name="regPwd" type="password"  class="xwb-plugin-input-a" id="regPwd" maxlength="256" />
                </td>
            </tr>
            <tr class="xwb-plugin-tr"><td colspan="3" class="xwb-plugin-td-msg"><label>　&nbsp;</label></td></tr>
            <tr class="xwb-plugin-tr-btn">
                <td colspan="3" class="xwb-plugin-td-btn">
                    <span class="xwb-plugin-btn">
                        <input name="registerBt1" type="button" onclick="submitForm('siteRegFrom')" id="registerBt1" value="完 成" />
                    </span>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="bindBox" class="xwb-plugin-form" style="display:none;">
    <form action="<?php echo XWB_plugin::getEntryURL("xwbSiteInterface.doBindAtNotLog");?>" id="siteBindFrom"  method="post" target="xwbSiteRegister"  >
        <table class="xwb-plugin-table">
            <tr class="xwb-plugin-tr-error">
                <td colspan="3">
                    <em id="bindErrorTips" class="xwb-plugin-error" style="display:none;"></em>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="siteBindName"> 用户名：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteBindName" id="siteBindName" class="xwb-plugin-input-a" value="" />
                </td>
                <td rowspan="4" class="xwb-plugin-td-right-msg">
                    还没有<?php echo XWB_S_TITLE ;?>账号？<br/><br/>
                    <a href="javascript:void(function(){})" onclick="forTurn('reg')" tabindex="-1">设置一个账号</a>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="bindPwd"> 密码：</label></td>
                <td class="xwb-plugin-td-input">
                    <input name="bindPwd" type="password" class="xwb-plugin-input-a" id="bindPwd" maxlength="256" />
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg">
                    <label for="questionid">安全提问：</label>
                </td>
                <td class="xwb-plugin-td-input">
                    <select id="questionid" name="questionid" class="xwb-plugin-input-select" onchange="selectChange()">
                        <option value="0">未设置请忽略</option>
                        <option value="1">母亲的名字</option>
                        <option value="2">爷爷的名字</option>
                        <option value="3">父亲出生的城市</option>
                        <option value="4">你其中一位老师的名字</option>
                        <option value="5">你个人计算机的型号</option>
                        <option value="6">你最喜欢的餐馆名称</option>
                        <option value="7">驾驶执照最后四位数字</option>
                    </select>
                </td>
            </tr>
            <tr id="bindTr" class="xwb-plugin-tr"><td colspan="3" class="xwb-plugin-td-msg"><label>　</label></td></tr>
            <tr class="xwb-plugin-tr-btn">
                <td colspan="3" class="xwb-plugin-td-btn">
                    <span class="xwb-plugin-btn">
                        <input name="bindBt" type="button" onclick="submitForm('siteBindFrom')" id="bindBt" value="完 成" />
                    </span>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="regSuccessBox" style="display:none;" class="xwb-plugin-reg-successBox">
	<div class="xwb-plugin-reg-successBox-forIE6">
        <p class="xwb-plugin-regSuccess xwb-plugin-reg-successBox-p">
            <strong>恭喜，创建成功!</strong>
            <span id="regSuccessTips" ></span>
        </p>
    </div>
    <div class="xwb-plugin-reg-successBox-btn">
        <span class="xwb-plugin-btn">
            <input name="registerBt2" type="button" onclick="window.top.location='index.php';" id="registerBt2" value="确 定" />
        </span>
    </div>
</div>

<div id="bindSuccessBox" style="display:none;" class="xwb-plugin-reg-successBox">
	<div class="xwb-plugin-reg-successBox-forIE6">
        <p class="xwb-plugin-regSuccess xwb-plugin-reg-successBox-p">
            <strong>恭喜，绑定成功!</strong>
            <span id="bindSuccessTips" ></span>
        </p>
    </div>
    <div class="xwb-plugin-reg-successBox-btn">
        <span class="xwb-plugin-btn">
            <input name="bindBt2" type="button" onclick="window.top.location='index.php';" id="bindBt2" value="确 定" />
        </span>
    </div>
</div>
<iframe src="" name="xwbSiteRegister" frameborder="0" height="0" width="0"></iframe>
</body>
</html>
