<?php
if( !class_exists('apiBase')) exit('Forbidden');
/**
 * API：用户
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-01-26
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id$
 *
 */
class apiUser extends apiBase
{
    var $apiRoute = 'apiRelate';
    
    /// 初始化
    function apiUser() {
        parent::apiBase();
    }

    ///根据id查询昵称
    function apiGetNick($id, $type = 'uid') {
        $FTParams = array('numid_id' => $id, 'idtype_type' => $type);
        if($this->_FTHelper($FTParams)) {
            $Query = "SELECT a.uid,a.sina_uid,b.username FROM `" . DB::table('xwb_bind_info') . "` a, `" . DB::table('common_member') . "` b WHERE a.`" . ('sina_uid'==strtolower($type)?'sina_uid':'uid') . "`='{$id}' AND a.`uid`=b.`uid`";
            $RS = $this->_DBHelper($Query, 1);
            if($RS) {
                $RS['url'] = $this->_getSiteSpaceUrl($this->rst['uid']);
                $this->rst = $RS;
            }
        }
        $this->_LogHelper($this->apiRoute . '/apiGetNick');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    ///根据一组id查询昵称
    function apiGetNicks($ids, $type = 'uid') {
        $FTParams = array('strids_ids' => $ids, 'idtype_type' => $type);
        if($this->_FTHelper($FTParams)) {
            $Query = "SELECT a.`uid`,a.`sina_uid`,b.`username` FROM `" . DB::table('xwb_bind_info') . "` a, `" . DB::table('common_member') . "` b WHERE a.`" . ('sina_uid'==strtolower($type)?'sina_uid':'uid') . "` IN ({$ids}) AND a.`uid`=b.`uid`";
            $RS = $this->_DBHelper($Query, 3);
            if($RS) {
                foreach($RS as $key => $row) {
                    $RS[$key]['url'] = $this->_getSiteSpaceUrl($row['uid']);
                }
                $this->rst = $RS;
            }
        }
        $this->_LogHelper($this->apiRoute . '/apiGetNicks');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    ///根据用户id查询好友关系
    function apiGetFriend($uid) {
        $FTParams = array('numid_id' => $uid);
        if($this->_FTHelper($FTParams)) {
            $Query = "SELECT main.fuid AS uid,main.fusername AS username FROM " . DB::table('home_friend') . " main WHERE main.uid='" . $uid . "' ORDER BY main.num DESC, main.dateline DESC";
            $RS = $this->_DBHelper($Query, 3);
            if($RS) {
                $this->rst = $RS;
            }
        }
        $this->_LogHelper($this->apiRoute . '/apiGetFriend');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    ///根据用户id查询最新主题，默认10个
    function apiGetNewPost($uid, $limit = 10) {
        $FTParams = array('numid_id' => $uid, 'num_limit' => $limit);
        if($this->_FTHelper($FTParams)) {
            $Query = "SELECT `pid`,`fid`,`tid`,`author`,`authorid`,`subject`,`message` FROM " . DB::table(getposttable('p')) . " WHERE `authorid`='" . $uid . "' AND `first`=1 ORDER BY `pid` DESC LIMIT " . $limit;
            $RS = $this->_DBHelper($Query, 3);
            if($RS) {
                //去除同步提示信息
                foreach($RS as $key => $row) {
                    $RS[$key]['message'] = preg_replace("|(\n\n)?\[size=2\]\[color=gray\] \[img\](.*?)\[/url\]\[/color\]\[/size\]$|", '', $row['message']);
                }
                $this->rst = $RS;
            }
        }
        $this->_LogHelper($this->apiRoute . '/apiGetNewPost');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    ///查询论坛的最新活跃用户，默认20个
    function apiGetActiveUser($timezone = 1, $limit = 20) {
        $FTParams = array('tznum_timezone' => $timezone, 'num_limit' => $limit);
        if($this->_FTHelper($FTParams)) {
            $dateline =  strtotime(date('Y-n-') . (date('j') - (1 == $timezone ? 1 : 7)));
            $Query = "SELECT m.uid,m.username FROM " . DB::table('common_member') . " m," . DB::table('common_member_count') . " mc," . DB::table('common_member_status') . " ms WHERE mc.uid=m.uid AND ms.uid=m.uid AND ms.lastpost>'" . $dateline . "' ORDER BY mc.posts DESC LIMIT " . $limit;
            $RS = $this->_DBHelper($Query, 3);
            if($RS) {
                $this->rst = $RS;
            }
        }
        $this->_LogHelper($this->apiRoute . '/apiGetActiveUser');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }
    
    /**
     * 获取site的个人页面链接
     * @param int $uid
     * @param string
     */
    function _getSiteSpaceUrl($uid){
		if(defined('XWB_S_SITEURL')) {
			return XWB_S_SITEURL. "/home.php?mod=space&uid=". $uid;
		}else{
			return dirname(XWB_plugin::siteUrl()). "/home.php?mod=space&uid=". $uid;
		}
    }    
    
}

