<?PHP (defined('IN_DISCUZ') && defined('IN_ADMINCP')) || die('Access Denied');
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_synchronisation.php 25593 2011-11-15 10:56:04Z yexinhao $
 */


cpheader();
global $_G;

if($operation == 'batchupload'){
    if(!submitcheck('batchuploadsubmit')) {
        echo <<<HTML

<!-- Bootstrap styles -->
<link rel="stylesheet" href="static/css/bootstrap.min.css">
<style>.btn{padding: 3px 12px;}</style>
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="static/css/blueimp-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="static/css/jquery.fileupload.css">
<link rel="stylesheet" href="static/css/jquery.fileupload-ui.css">




<div class="container">
    <br>
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="http://blueimp.github.io/jQuery-File-Upload/"></noscript>
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <span>Start upload</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <span>Cancel upload</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <span>Delete</span>
                </button>
                <input type="checkbox" class="toggle">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
    <br>
</div>
<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <span>Delete</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<script type="text/javascript">
	document.writeln('<scr'+'ipt type="text/jav'+'ascript" src="static/js/jquery/jquery-'+((window['oldIE'] = !-[1, ]) ? '1.11.0' : '2.1.0')+'.min.js?{VERHASH}"><'+'/'+'scr'+'ipt>');
</script>
<script type="text/javascript">window.jQuery = jQuery.noConflict(true);</script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="static/js/jQuery-File-Upload/vendor/jquery.ui.widget.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="static/js/jQuery-File-Upload/blueimp/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="static/js/jQuery-File-Upload/blueimp/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="static/js/jQuery-File-Upload/blueimp/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="static/js/jQuery-File-Upload/blueimp/bootstrap.min.js"></script>
<!-- blueimp Gallery script -->
<script src="static/js/jQuery-File-Upload/blueimp/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="static/js/jQuery-File-Upload/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="static/js/jQuery-File-Upload/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<script>
/*
 * jQuery File Upload Plugin JS Example 8.9.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/* global $, window */

(function ($) {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: 'vizto.php'
    });

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );

    if (window.location.hostname === 'blueimp.github.io') {
        // Demo settings:
        $('#fileupload').fileupload('option', {
            url: '//jquery-file-upload.appspot.com/',
            // Enable image resizing, except for Android and Opera,
            // which actually support image resizing, but fail to
            // send Blob objects via XHR requests:
            disableImageResize: /Android(?!.*Chrome)|Opera/
                .test(window.navigator.userAgent),
            maxFileSize: 5000000,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
        });
        // Upload server status check for browsers with CORS support:
        if ($.support.cors) {
            $.ajax({
                url: '//jquery-file-upload.appspot.com/',
                type: 'HEAD'
            }).fail(function () {
                $('<div class="alert alert-danger"/>')
                    .text('Upload server currently unavailable - ' +
                            new Date())
                    .appendTo('#fileupload');
            });
        }
    } else {
        // Load existing files:
        $('#fileupload').addClass('fileupload-processing')
        .bind('fileuploaddone', function (e, data) {
            //console.log(e);
            //console.log(data);
        });
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#fileupload')[0]
        }).always(function () {
            $(this).removeClass('fileupload-processing');
        }).done(function (result) {
            $(this).fileupload('option', 'done')
                .call(this, $.Event('done'), {result: result});
        });
    }

})(jQuery);

</script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]><script src="static/js/jQuery-File-Upload/cors/jquery.xdr-transport.js"></script><![endif]-->


HTML;

        showformheader('synchronisation');
        showhiddenfields(array('operation' => $operation));
        include_once libfile('function/portalcp');
        $categoryselect = category_showselect('portal', 'catid', true, $_GET['catid']);
        echo $categoryselect;
        showsubmit('batchuploadsubmit', 'submit');
        showtablefooter();
        showformfooter();
    } else {
        error_reporting(E_ALL);
        ini_set('display_error', 1);

        $catid = intval($_GET['catid']);

        $list = glob(DISCUZ_ROOT.'data/plugindata/images/*');
        array(
            0 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/51_02.png',
            1 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/51_03.png',
            2 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/79_03.png',
            3 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/79_05.png',
            4 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/80_03.png',
            5 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/80_06.png',
            6 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/80_08.png',
            7 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_30.png',
            8 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_33.png',
            9 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_34.png',
            10 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_35.png',
            11 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_36.png',
            12 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_37.png',
            13 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_45.png',
            14 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_47.png',
            15 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_48.png',
            16 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/BB文件-28_49.png',
            17 => '/www/users/jusheng168.net/JuShengHotel/data/plugindata/images/thumbnail',
        );

        require_once libfile('function/portalcp');
        require_once libfile('function/home');
        require_once libfile('function/portal');

        foreach($list as $num => $file) {
            if(!is_file($file) || !is_image($file)) continue;



            $upload = new discuz_upload();
            $upload->init(array(
                'name' => iconv('GBK', 'UTF-8', $file),
                'tmp_name' => $file,
                'size' => filesize($file)
            ), 'portal', 0, '', false);
            $attach = $upload->attach;
            if(!$upload->error()) {
                $upload->save(0, 1);
            }
            if($upload->error()) {
                $errorcode = 4;
            }
            if(!$errorcode) {
                if($attach['isimage'] && empty($_G['setting']['portalarticleimgthumbclosed'])) {
                    require_once libfile('class/image');
                    $image = new image();
                    $category = C::t('portal_category')->fetch($catid);//搜索分类设置
                    $thumbimgwidth = $category['specialthumbsize'] && $category['thumbsizewidth'] ? $category['thumbsizewidth'] : ($_G['setting']['portalarticleimgthumbwidth'] ? $_G['setting']['portalarticleimgthumbwidth'] : 300);//应用分类特定宽值
                    $thumbimgheight = $category['specialthumbsize'] && $category['thumbsizeheight'] ? $category['thumbsizeheight'] : ($_G['setting']['portalarticleimgthumbheight'] ? $_G['setting']['portalarticleimgthumbheight'] : 300);//应用分类特定高值
                    $attach['thumb'] = $image->Thumb($attach['target'], '', $thumbimgwidth, $thumbimgheight, 3, false, true);//WebPower 版 调整缩略图生成模式
                    $image->Watermark($attach['target'], '', 'portal');
                }

                if(getglobal('setting/ftp/on') && ((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['size'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
                    if(ftpcmd('upload', 'portal/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'portal/'.getimgthumbname($attach['attachment'])))) {
                        @unlink($_G['setting']['attachdir'].'/portal/'.$attach['attachment']);
                        @unlink($_G['setting']['attachdir'].'/portal/'.getimgthumbname($attach['attachment']));
                        $attach['remote'] = 1;
                    } else {
                        if(getglobal('setting/ftp/mirror')) {
                            @unlink($attach['target']);
                            @unlink(getimgthumbname($attach['target']));
                            $errorcode = 5;
                        }
                    }
                }

                $setarr = array(
                    'uid' => $_G['uid'],
                    'filename' => $attach['name'],
                    'attachment' => $attach['attachment'],
                    'filesize' => $attach['size'],
                    'isimage' => $attach['isimage'],
                    'thumb' => $attach['thumb'],
                    'remote' => $attach['remote'],
                    'filetype' => $attach['extension'],
                    'dateline' => $_G['timestamp'],
                    'aid' => $aid
                );
                $setarr['attachid'] = C::t('portal_attachment')->insert($setarr, true);
                if($attach['isimage']) {
                    require_once libfile('function/home');
                    $smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
                    $bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
                    $coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
                    //echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"smallimg\":\"$smallimg\", \"bigimg\":\"$bigimg\", \"errorcode\":$errorcode, \"cover\":\"$coverstr\"}";

                } else {
                    $fileurl = 'portal.php?mod=attachment&id='.$attach['attachid'];
                    //echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"file\":\"$fileurl\", \"errorcode\":$errorcode}";
                }
            } else {
                //echo "{\"aid\":0, \"errorcode\":$errorcode, \"\$uploaderrorcode:$upload->errorcode}";
            }


            echo('<br />' . substr(iconv('GBK', 'UTF-8', $file), strrpos($file, '/') + 1) . '<br />');

            $_GET['conver'] = $coverstr;
            $_POST['attach_ids'] = "{$setarr[attachid]}";
            $_POST['title'] = substr(iconv('GBK', 'UTF-8', $file), strrpos($file, '/')+1);
            $_POST['content'] = '<p><a href="'.$bigimg.'"><img src="'.$smallimg.'" /></a></p>';
            $article = $article_content = array();

            loadcache('portalcategory');
            $portalcategory = $_G['cache']['portalcategory'];

            if(empty($catid) && $article) {
                $catid = $article['catid'];
            }
            $htmlstatus = !empty($_G['setting']['makehtml']['flag']) && $portalcategory[$catid]['fullfoldername'];


            check_articleperm($catid);

            $_POST['title'] = getstr(trim($_POST['title']), 80);
            if(strlen($_POST['title']) < 1) {
                showmessage('title_not_too_little');
            }
            $_POST['title'] = censor($_POST['title']);

            $_POST['pagetitle'] = getstr(trim($_POST['pagetitle']), 60);
            $_POST['pagetitle'] = censor($_POST['pagetitle']);
            $htmlname = basename(trim($_POST['htmlname']));

            $highlight_style = $_GET['highlight_style'];
            $style = '';
            $style = implode('|', $highlight_style);
            if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|\<strong\>##########NextPage(\[title=.*?\])?##########\<\/strong\>)+/", ' ', $_POST['content']);
            $summary = portalcp_get_summary($_POST['summary']);
            $summary = censor($summary);

            $_GET['author'] = dhtmlspecialchars($_GET['author']);
            $_GET['url'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['url']));
            $_GET['from'] = dhtmlspecialchars($_GET['from']);
            $_GET['fromurl'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['fromurl']));
            $_GET['dateline'] = !empty($_GET['dateline']) ? strtotime($_GET['dateline']) : TIMESTAMP;
            if(censormod($_POST['title']) || $_G['group']['allowpostarticlemod']) {
                $article_status = 1;
            } else {
                $article_status = 0;
            }

            $setarr = array(
                'title' => $_POST['title'],
                'author' => $_GET['author'],
                'from' => $_GET['from'],
                'fromurl' => $_GET['fromurl'],
                'dateline' => intval($_GET['dateline']),
                'url' => $_GET['url'],
                'allowcomment' => !empty($_POST['forbidcomment']) ? '0' : '1',
                'summary' => $summary,
                'catid' => intval($_POST['catid']),
                'tag' => article_make_tag($_POST['tag']),
                'status' => $article_status,
                'highlight' => $style,
                'showinnernav' => empty($_POST['showinnernav']) ? '0' : '1',
            );

            if(empty($setarr['catid'])) {
                showmessage('article_choose_system_category');
            }

            if($_GET['conver']) {
                $converfiles = dunserialize($_GET['conver']);
                $setarr['pic'] = $converfiles['pic'];
                $setarr['thumb'] = intval($converfiles['thumb']);
                $setarr['remote'] = intval($converfiles['remote']);
            }

            $id = 0;
            $idtype = '';

            if(empty($article)) {
                $setarr['uid'] = $_G['uid'];
                $setarr['username'] = $_G['username'];
                $setarr['id'] = intval($_POST['id']);
                $setarr['htmlname'] = $htmlname;
                $table = '';
                if($setarr['id']) {
                    if($_POST['idtype'] == 'blogid') {
                        $table = 'home_blogfield';
                        $setarr['idtype'] = 'blogid';
                        $id = $setarr['id'];
                        $idtype = $setarr['idtype'];
                    } else {
                        $table = 'forum_thread';
                        $setarr['idtype'] = 'tid';

                        require_once libfile('function/discuzcode');
                        $id = C::t('forum_post')->fetch_threadpost_by_tid_invisible($setarr['id']);
                        $id = $id['pid'];
                        $idtype = 'pid';
                    }
                }
                $aid = C::t('portal_article_title')->insert($setarr, 1);
                if($table) {
                    if($_POST['idtype'] == 'blogid') {
                        C::t('home_blogfield')->update($setarr['id'], array('pushedaid' => $aid));
                    } elseif($setarr['idtype'] == 'tid') {
                        $modarr = array(
                            'tid' => $setarr['id'],
                            'uid' => $_G['uid'],
                            'username' => $_G['username'],
                            'dateline' => TIMESTAMP,
                            'action' => 'PTA',
                            'status' => '1',
                            'stamp' => '',
                        );
                        C::t('forum_threadmod')->insert($modarr);

                        C::t('forum_thread')->update($setarr['id'], array(
                            'moderated' => 1,
                            'pushedaid' => $aid
                        ));
                    }
                }
                C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
                C::t('portal_category')->increase($setarr['catid'], array('articles' => 1));
                C::t('portal_category')->update($setarr['catid'], array('lastpublish' => TIMESTAMP));
                C::t('portal_article_count')->insert(array(
                    'aid' => $aid,
                    'catid' => $setarr['catid'],
                    'viewnum' => 1
                ));
            } else {
                if($htmlname && $article['htmlname'] !== $htmlname) {
                    $setarr['htmlname'] = $htmlname;
                    $oldarticlename = $article['htmldir'] . $article['htmlname'];
                    unlink($oldarticlename . '.' . $_G['setting']['makehtml']['extendname']);
                    for($i = 1; $i < $article['contents']; $i++) {
                        unlink($oldarticlename . $i . '.' . $_G['setting']['makehtml']['extendname']);
                    }
                }
                C::t('portal_article_title')->update($aid, $setarr);
            }

            $content = getstr($_POST['content'], 0, 0, 0, 0, 1);
            $content = censor($content);
            if(censormod($content) || $_G['group']['allowpostarticlemod']) {
                $article_status = 1;
            } else {
                $article_status = 0;
            }

            $regexp = '/(\<strong\>##########NextPage(\[title=(.*?)\])?##########\<\/strong\>)+/is';
            preg_match_all($regexp, $content, $arr);
            $pagetitle = !empty($arr[3]) ? $arr[3] : array();
            $pagetitle = array_map('trim', $pagetitle);
            array_unshift($pagetitle, $_POST['pagetitle']);
            $contents = preg_split($regexp, $content);
            $cpostcount = count($contents);

            $dbcontents = C::t('portal_article_content')->fetch_all($aid);

            $pagecount = $cdbcount = count($dbcontents);
            if($cdbcount > $cpostcount) {
                $cdelete = array();
                foreach(array_splice($dbcontents, $cpostcount) as $value) {
                    $cdelete[$value['cid']] = $value['cid'];
                }
                if(!empty($cdelete)) {
                    C::t('portal_article_content')->delete($cdelete);
                }
                $pagecount = $cpostcount;
            }

            foreach($dbcontents as $key => $value) {
                C::t('portal_article_content')->update($value['cid'], array(
                    'title' => $pagetitle[$key],
                    'content' => $contents[$key],
                    'pageorder' => $key + 1
                ));
                unset($pagetitle[$key], $contents[$key]);
            }

            if($cdbcount < $cpostcount) {
                foreach($contents as $key => $value) {
                    C::t('portal_article_content')->insert(array(
                        'aid' => $aid,
                        'id' => $setarr['id'],
                        'idtype' => $setarr['idtype'],
                        'title' => $pagetitle[$key],
                        'content' => $contents[$key],
                        'pageorder' => $key + 1,
                        'dateline' => TIMESTAMP
                    ));
                }
                $pagecount = $cpostcount;
            }

            $updatearticle = array('contents' => $pagecount);
            if($article_status == 1) {
                $updatearticle['status'] = 1;
                updatemoderate('aid', $aid);
                manage_addnotify('verifyarticle');
            }

            $updatearticle = array_merge($updatearticle, portalcp_article_pre_next($catid, $aid));
            C::t('portal_article_title')->update($aid, $updatearticle);

            $newaids = array();
            $_POST['attach_ids'] = explode(',', $_POST['attach_ids']);
            foreach($_POST['attach_ids'] as $newaid) {
                $newaid = intval($newaid);
                if($newaid) $newaids[$newaid] = $newaid;
            }
            if($newaids) {
                C::t('portal_attachment')->update_to_used($newaids, $aid);
            }

            addrelatedarticle($aid, $_POST['raids']);

            if($_GET['from_idtype'] && $_GET['from_id']) {

                $id = intval($_GET['from_id']);
                $notify = array();
                switch($_GET['from_idtype']) {
                    case 'blogid':
                        $blog = C::t('home_blog')->fetch($id);
                        if(!empty($blog)) {
                            $notify = array(
                                'url' => "home.php?mod=space&uid=$blog[uid]&do=blog&id=$id",
                                'subject' => $blog['subject']
                            );
                            $touid = $blog['uid'];
                        }
                        break;
                    case 'tid':
                        $thread = C::t('forum_thread')->fetch($id);
                        if(!empty($thread)) {
                            $notify = array(
                                'url' => "forum.php?mod=viewthread&tid=$id",
                                'subject' => $thread['subject']
                            );
                            $touid = $thread['authorid'];
                        }
                        break;
                }
                if(!empty($notify)) {
                    $notify['newurl'] = 'portal.php?mod=view&aid=' . $aid;
                    notification_add($touid, 'pusearticle', 'puse_article', $notify, 1);
                }
            }

            if(trim($_GET['from']) != '') {
                $from_cookie = '';
                $from_cookie_array = array();
                $from_cookie = getcookie('from_cookie');
                $from_cookie_array = explode("\t", $from_cookie);
                $from_cookie_array[] = $_GET['from'];
                $from_cookie_array = array_unique($from_cookie_array);
                $from_cookie_array = array_filter($from_cookie_array);
                $from_cookie_num = count($from_cookie_array);
                $from_cookie_start = $from_cookie_num - 10;
                $from_cookie_start = $from_cookie_start > 0 ? $from_cookie_start : 0;
                $from_cookie_array = array_slice($from_cookie_array, $from_cookie_start, $from_cookie_num);
                $from_cookie = implode("\t", $from_cookie_array);
                dsetcookie('from_cookie', $from_cookie);
            }
            dsetcookie('clearUserdata', 'home');
            $op = 'add_success';
            $article_add_url = 'portal.php?mod=portalcp&ac=article&catid=' . $catid;


            $article = C::t('portal_article_title')->fetch($aid);
            $viewarticleurl = $_POST['url'] ? "portal.php?mod=list&catid=$_POST[catid]" : fetch_article_url($article);
        }

    }
}


function portalcp_get_summary($message) {
    $message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i", "/\<script.*?\<\/script\>/"), '', $message);
    $message = preg_replace("/\[.*?\]/", '', $message);
    $message = getstr(strip_tags($message), 200);
    return $message;
}

function portalcp_get_postmessage($post, $getauthorall = '') {
    global $_G;
    $forum = C::t('forum_forum')->fetch($post['fid']);
    require_once libfile('function/discuzcode');
    $language = lang('forum/misc');
    if($forum['type'] == 'sub' && $forum['status'] == 3) {
        loadcache('grouplevels');
        $grouplevel = $_G['grouplevels'][$forum['level']];
        $group_postpolicy = $grouplevel['postpolicy'];
        if(is_array($group_postpolicy)) {
            $forum = array_merge($forum, $group_postpolicy);
        }
    }
    $post['message'] = preg_replace($language['post_edit_regexp'], '', $post['message']);

    $_message = '';
    if($getauthorall) {
        foreach(C::t('forum_post')->fetch_all_by_tid('tid:'.$post['tid'], $post['tid'], true, '', 0, 0, null, null, $post['authorid']) as $value){
            if(!$value['first']) {
                $value['message'] = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", '', $value['message']);
                $value['message'] = discuzcode($value['message'], $value['smileyoff'], $value['bbcodeoff'], $value['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $value['authorid'], $forum['allowmediacode'], $value['pid']);
                portalcp_parse_postattch($value);
                $_message .= '<br /><br />'.$value['message'];
            }
        }
    }

    $msglower = strtolower($post['message']);
    if(strpos($msglower, '[/media]') !== FALSE) {
        $post['message'] = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", "parsearticlemedia('\\1', '\\2')", $post['message']);
    }
    if(strpos($msglower, '[/audio]') !== FALSE) {
        $post['message'] = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", "parsearticlemedia('mid,0,0', '\\2')", $post['message']);
    }
    if(strpos($msglower, '[/flash]') !== FALSE) {
        $post['message'] = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", "parsearticlemedia('swf,0,0', '\\4');", $post['message']);
    }

    $post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $post['authorid'], $forum['allowmediacode'], $post['pid']);
    portalcp_parse_postattch($post);

    if(strpos($post['message'], '[/flash1]') !== FALSE) {
        $post['message'] = str_replace('[/flash1]', '[/flash]', $post['message']);
    }
    return $post['message'].$_message;
}
function portalcp_parse_postattch(&$post) {
    static $allpostattchs = null;
    if($allpostattchs === null) {
        foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'tid', $post['tid']) as $attch) {
            $allpostattchs[$attch['pid']][$attch['aid']] = $attch['aid'];
        }
    }
    $attachs = $allpostattchs[$post['pid']];
    if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
        $attachs = array_diff($allpostattchs[$post['pid']], $matchaids[1]);
    }
    if($attachs) {
        $add = '';
        foreach($attachs as $attachid) {
            $add .= '<br/>'.'[attach]'.$attachid.'[/attach]';
        }
        $post['message'] .= $add;
    }
}
function parsearticlemedia($params, $url) {
    global $_G;

    $params = explode(',', $params);
    $width = intval($params[1]) > 800 ? 800 : intval($params[1]);
    $height = intval($params[2]) > 600 ? 600 : intval($params[2]);
    $url = addslashes($url);
    if($flv = parseflv($url, 0, 0)) {
        if(!empty($flv) && preg_match("/\.flv$/i", $flv['flv'])) {
            $flv['flv'] = $_G['style']['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($flv['flv']);
        }
        $url = $flv['flv'];
        $params[0] = 'swf';
    }
    if(in_array(count($params), array(3, 4))) {
        $type = $params[0];
        $url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
        switch($type) {
            case 'mp3':
            case 'wma':
            case 'ra':
            case 'ram':
            case 'wav':
            case 'mid':
                return '[flash=mp3]'.$url.'[/flash1]';
            case 'rm':
            case 'rmvb':
            case 'rtsp':
                return '[flash=real]'.$url.'[/flash1]';
            case 'swf':
                return '[flash]'.$url.'[/flash1]';
            case 'asf':
            case 'asx':
            case 'wmv':
            case 'mms':
            case 'avi':
            case 'mpg':
            case 'mpeg':
            case 'mov':
                return '[flash=media]'.$url.'[/flash1]';
            default:
                return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
        }
    }
    return;
}

function portalcp_article_pre_next($catid, $aid) {
    $data = array(
        'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $aid),
        'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $aid),
    );
    if($data['preaid']) {
        C::t('portal_article_title')->update($data['preaid'], array(
                'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $data['preaid']),
                'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $data['preaid']),
            )
        );
    }
    return $data;
}

function is_image($file) {
    $imgs_arr = array("jpg", "jpeg", "png", "gif"); //图片的后缀 ，自己可以添加
    $ext      = strtolower(end(explode(".", $file)));
    return !empty($ext) && in_array($ext, $imgs_arr);
}