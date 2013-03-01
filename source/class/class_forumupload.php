<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_forumupload.php 26912 2011-12-27 09:01:29Z svn_project_zhangjie $
 */

class forum_upload {

	var $uid;
	var $aid;
	var $simple;
	var $statusid;
	var $attach;
	var $error_sizelimit;

	function forum_upload() {
		global $_G;

		$_G['uid'] = $this->uid = intval($_G['gp_uid']);
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$this->uid);
		$this->aid = 0;
		$this->simple = !empty($_G['gp_simple']) ? $_G['gp_simple'] : 0;

		if($_G['gp_hash'] != $swfhash) {
			$this->uploadmsg(10);
		}

		$_G['member'] = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='".$this->uid."'");
		$_G['groupid'] = $_G['member']['groupid'];
		loadcache('usergroup_'.$_G['groupid']);
		$_G['group'] = $_G['cache']['usergroup_'.$_G['groupid']];

		require_once libfile('class/upload');

		$upload = new discuz_upload();
		$upload->init($_FILES['Filedata'], 'forum');
		$this->attach = &$upload->attach;

		if($upload->error()) {
			$this->uploadmsg(2);
		}

		$allowupload = !$_G['group']['maxattachnum'] || $_G['group']['maxattachnum'] && $_G['group']['maxattachnum'] > getuserprofile('todayattachs');;
		if(!$allowupload) {
			$this->uploadmsg(6);
		}

		if($_G['group']['attachextensions'] && (!preg_match("/(^|\s|,)".preg_quote($upload->attach['ext'], '/')."($|\s|,)/i", $_G['group']['attachextensions']) || !$upload->attach['ext'])) {
			$this->uploadmsg(1);
		}

		if(empty($upload->attach['size'])) {
			$this->uploadmsg(2);
		}

		if($_G['group']['maxattachsize'] && $upload->attach['size'] > $_G['group']['maxattachsize']) {
			$this->error_sizelimit = $_G['group']['maxattachsize'];
			$this->uploadmsg(3);
		}

		if($type = DB::fetch_first("SELECT maxsize FROM ".DB::table('forum_attachtype')." WHERE extension='".addslashes($upload->attach['ext'])."'")) {
			if($type['maxsize'] == 0) {
				$this->error_sizelimit = 'ban';
				$this->uploadmsg(4);
			} elseif($upload->attach['size'] > $type['maxsize']) {
				$this->error_sizelimit = $type['maxsize'];
				$this->uploadmsg(5);
			}
		}

		if($upload->attach['size'] && $_G['group']['maxsizeperday']) {
			$todaysize = getuserprofile('todayattachsize') + $upload->attach['size'];
			if($todaysize >= $_G['group']['maxsizeperday']) {
				$this->error_sizelimit = 'perday|'.$_G['group']['maxsizeperday'];
				$this->uploadmsg(11);
			}
		}
		updatemembercount($_G['uid'], array('todayattachs' => 1, 'todayattachsize' => $upload->attach['size']));
		$upload->save();
		if($upload->error() == -103) {
			$this->uploadmsg(8);
		} elseif($upload->error()) {
			$this->uploadmsg(9);
		}
		$thumb = $remote = $width = 0;
		if($_G['gp_type'] == 'image' && !$upload->attach['isimage']) {
			$this->uploadmsg(7);
		}
		if($upload->attach['isimage']) {
			if($_G['setting']['thumbstatus']) {
				require_once libfile('class/image');
				$image = new image;
				$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], $_G['setting']['thumbsource']) ? 1 : 0;
				$width = $image->imginfo['width'];
			}
			if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
				list($width) = @getimagesize($upload->attach['target']);
			}
		}
		if($_G['gp_type'] != 'image' && $upload->attach['isimage']) {
			$upload->attach['isimage'] = -1;
		}
		$this->aid = $aid = getattachnewaid($this->uid);
		DB::query("INSERT INTO ".DB::table('forum_attachment_unused')." (aid, dateline, filename, filesize, attachment, isimage, uid, thumb, remote, width)
			VALUES ('$aid', '$_G[timestamp]', '".$upload->attach['name']."', '".$upload->attach['size']."', '".$upload->attach['attachment']."', '".$upload->attach['isimage']."', '".$this->uid."', '$thumb', '$remote', '$width')");
		$this->uploadmsg(0);
	}

	function uploadmsg($statusid) {
		global $_G;
		$this->error_sizelimit = !empty($this->error_sizelimit) ? $this->error_sizelimit : 0;
		if($this->simple == 1) {
			echo 'DISCUZUPLOAD|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'].'|'.$this->error_sizelimit;
		} elseif($this->simple == 2) {
			echo 'DISCUZUPLOAD|'.($_G['gp_type'] == 'image' ? '1' : '0').'|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'].'|'.$this->attach['attachment'].'|'.$this->attach['name'].'|'.$this->error_sizelimit;
		} else {
			echo $statusid ? 'error' : $this->aid;
		}
		exit;
	}
}

?>