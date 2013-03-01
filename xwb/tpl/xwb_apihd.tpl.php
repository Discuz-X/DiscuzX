<style type="text/css">
* {margin: 0;padding: 0;word-wrap: break-word;}
#apihd_hd {border-bottom: 0 solid #C2D5E3;margin-bottom: 10px;}
#apihd_hd .apihd_wp {padding: 10px 0 0;margin: 0 auto;width: 960px;}
#apihd_hd .apihd_cl {}
#apihd_hd .apihd_hdc {min-height: 70px;position: relative;z-index: 3;}
#apihd_hd h2 {float: left;padding: 0 20px 8px 0;font-size: 1em;margin-bottom: 0;}
#apihd_hd a {color: #333333;text-decoration: none;}
#apihd_hd a:hover {text-decoration:underline;}
#apihd_hd a img {border: medium none;}
#apihd_hd .apihd_fastlg {bottom: 8px;position: absolute;right: 0;}
#apihd_hd .apihd_fastlg {line-height: 24px;width: 60%;}
#apihd_hd .apihd_y {float: right;}
#apihd_hd .apihd_fastlg p {padding-bottom: 2px;}
#apihd_hd .apihd_xi2, #apihd_hd .apihd_xi2 a, #apihd_hd .apihd_xi3 a {color: #336699 !important;}
#apihd_hd .apihd_fastlg_fm {border-right: 1px solid #CDCDCD;margin-right: 5px;padding-right: 5px;}
#apihd_hd .apihd_psw_w {padding-left: 5px;width: 67px;}
#apihd_hd .apihd_z {float: left;}
#apihd_hd .apihd_fastlg .apihd_pn {height: 22px;line-height: 22px;}
#apihd_hd .apihd_pns .apihd_pn {font-size: 12px;height: 24px;line-height: 24px;}
#apihd_hd input, #apihd_hd button {color: #444444;font: 12px/1.5 Tahoma,Helvetica,SimSun,sans-serif,Hei;}
#apihd_hd .apihd_vm {vertical-align: middle;}
#apihd_hd .apihd_px, #apihd_hd .apihd_pt {background: url("<?php echo dirname(XWB_plugin::siteUrl());?>/static/image/common/px.png") repeat-x scroll 0 0 #FFFFFF;border-color: #707070 #CECECE #CECECE #707070;border-style: solid;border-width: 1px;color: #666666;font-size: 14px;padding: 2px 4px;}
#apihd_hd .apihd_px {height: 20px;}
#apihd_hd .apihd_pns .apihd_px {height: 18px;line-height: 18px;}
#apihd_hd .apihd_fastlg .apihd_px, #apihd_ls_more .apihd_px {font-size: 12px;height: 22px;line-height: 22px;}
#apihd_hd .apihd_pn {background: none repeat scroll 0 0 #E5EDF2;border-color: #C2D5E3 #336699 #336699 #C2D5E3;border-style: solid;border-width: 1px;color: #336699;cursor: pointer;font-size: 14px;font-weight: 700;height: 26px;line-height: 26px;margin-right: 3px;overflow: visible;vertical-align: middle;z-index: 0;}
#apihd_hd .apihd_pn em {font-weight: 100;}
#apihd_hd .apihd_pn * {padding: 0 5px;}
#apihd_hd .apihd_vm * {vertical-align: middle;}
#apihd_hd em {font-style: normal;}
#apihd_nv {background: url("<?php echo dirname(XWB_plugin::siteUrl());?>/static/image/common/nv.png") no-repeat scroll 0 0 #2B7ACD;height: 33px;overflow: hidden;padding-left: 3px;}
#apihd_hd #apihd_nv li {background: url("<?php echo dirname(XWB_plugin::siteUrl());?>/static/image/common/nv_a.png") no-repeat scroll 100% 0 transparent;float: left;font-size: 14px;font-weight: 700;height: 33px;line-height: 33px;padding-right: 1px;}
#apihd_hd ul li, #apihd_hd .apihd_xl li {list-style: none outside none;}
#apihd_hd #apihd_nv a {color: #FFFFFF;float: left;height: 33px;padding: 0 20px;}
#apihd_hd #apihd_nv li span {display: none;}
#apihd_hd #apihd_lsform {clear:both;}
#apihd_hd .apihd_login_button {width:65px;}
</style>
<!--diz-x头部结构-->
<div id="apihd_hd">
    <div class="apihd_wp">
        <div class="apihd_hdc apihd_cl">
            <h2><a title="Xweibo" href="<?php echo dirname(XWB_plugin::siteUrl());?>"><?php echo preg_replace('#src="(?!http)(.*?)"#', 'src="'.dirname(XWB_plugin::siteUrl()).'/$1"', $_G['style']['boardlogo']);?></a></h2>
            <form action="<?php echo dirname(XWB_plugin::siteUrl());?>/member.php?mod=logging&amp;action=login&amp;loginsubmit=yes&amp;infloat=yes" id="apihd_lsform" autocomplete="off" method="post">
                <div class="apihd_fastlg apihd_cl">
                    <div class="apihd_y apihd_pns">
                        <p>
                            <label class="apihd_z apihd_psw_w" for="ls_password">用户名</label>
                            <input type="text" tabindex="901" class="apihd_px apihd_vm" autocomplete="off" id="apihd_ls_username" name="username">
                            &nbsp;<a href="<?php echo dirname(XWB_plugin::siteUrl());?>/member.php?mod=register" class="apihd_xi2">成为会员</a>
                        </p>
                        <p>
                            <label class="apihd_z apihd_psw_w" for="ls_password">密码</label> <input type="password" tabindex="902" autocomplete="off" class="apihd_px apihd_vm" id="apihd_ls_password" name="password">
                            &nbsp;<button class="apihd_pn apihd_vm apihd_login_button" type="submit"><em>登录</em></button>
                        </p>
                        <input type="hidden" value="yes" name="quickforward">
                        <input type="hidden" value="ls" name="handlekey">
                    </div>
                </div>

            </form>
        </div>
        <div id="apihd_nv">
            <ul>
                <?php
                    $tmp = array(
                        'navname'=>'微博',
                        'filename'=>'xweibo.php',
                        'available'=>1,
                        'nav'=>'id="mn_xweibo" ><a href="xweibo.php" hidefocus="true" title="Xweibo" class="current">微博<span>Xweibo</span></a',
                        'navid'=>'mn_xweibo',
                        'level'=>0
                    );
                    array_splice($_G['setting']['navs'], 1, 0, array($tmp));
                ?>
                <?php foreach($_G['setting']['navs'] as $nav):?>
                <?php $nav['nav'] = preg_replace('#href="(?!http)(.*?)"#', 'href="' . dirname(XWB_plugin::siteUrl()) . '/$1"', $nav['nav']);?>
                <?php if($nav['available'] && (!$nav['level'] || ($nav['level'] == 1 && $_G['uid']) || ($nav['level'] == 2 && $_G['adminid'] > 0) || ($nav['level'] == 3 && $_G['adminid'] == 1))):?>
                <?php echo "<li {$nav['nav']}/li>";?>
                <?php endif;?>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
</div>