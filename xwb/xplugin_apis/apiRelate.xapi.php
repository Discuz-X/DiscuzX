<?php
if( !class_exists('apiBase')) exit('Forbidden');
/**
 * API：绑定关系[新增、更新、删除、获取]
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-01-21
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id$
 *
 */
class apiRelate extends apiBase
{
    var $apiRoute = 'apiRelate';

    /// 初始化
    function apiRelate() {
        parent::apiBase();
    }

    /// 更新绑定关系
    function updateRelate($uid, $sid, $token, $tsecret) {
    	if( !$this->_HasUser($uid)){
    		$this->_ERHelper('4020003', true, 'updateRelate');
    	}
        $FTParams = array('numid_uid' => $uid, 'numid_sid' => $sid, 'nstr_token' => $token, 'nstr_tsecret' => $tsecret);
        if($this->_FTHelper($FTParams)) {
            $RS = $this->_IsBind($uid, $sid, 'or', true);
            $count = $RS ? count($RS) : 0;
            if( !$count) {
                $fields = "`uid`,`sina_uid`,`token`,`tsecret`,`profile`";
                $values = "'{$uid}','{$sid}','{$token}','{$tsecret}','[]'";
                $Query = "INSERT INTO `" . DB::table('xwb_bind_info') . "` ({$fields}) VALUES ({$values})";
            } else {
                $row = array_shift($RS);
                foreach($RS as $value) {
                    $Query = "DELETE FROM `" . DB::table('xwb_bind_info') . "` WHERE `uid`='{$value['uid']}' AND `sina_uid`='{$value['sina_uid']}'";
                    $this->rst = $this->_DBHelper($Query, 2);
                }
                $Query = "UPDATE `" . DB::table('xwb_bind_info') . "` SET `uid`='{$uid}',`sina_uid`='{$sid}',`token`='{$token}',`tsecret`='{$tsecret}' WHERE `uid`='{$row['uid']}' AND `sina_uid`='{$row['sina_uid']}'";
            }
            $this->rst = $this->_DBHelper($Query, 2);
        }
        $this->_LogHelper($this->apiRoute . '/updateRelate');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }
    
    /// 删除绑定关系
    function deleteRelate($uid, $sid) {
        $FTParams = array('numid_uid' => $uid, 'numid_sid' => $sid);
        if($this->_FTHelper($FTParams)) {
            $Query = "DELETE FROM `" . DB::table('xwb_bind_info') . "` WHERE `uid`='{$uid}' AND `sina_uid`='{$sid}'";
            if($this->_IsBind($uid, $sid)){
                $this->rst = $this->_DBHelper($Query, 2);
            } else {
                $this->_ERHelper('4020002');
            }
        }
        $this->_LogHelper($this->apiRoute . '/deleteRelate');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    /// 获取绑定关系
    function fetchRelate($id, $type = 'uid') {
        $FTParams = array('numid_id' => $id, 'idtype_type' => $type);
        if($this->_FTHelper($FTParams)) {
            $Query = "SELECT uid,sina_uid,token,tsecret FROM `" . DB::table('xwb_bind_info') . "` WHERE `" . ('sina_uid'==strtolower($type)?'sina_uid':'uid') . "`='{$id}'";
            $this->rst = ($tmp = $this->_DBHelper($Query, 1)) ? $tmp : array();
        }
        $this->_LogHelper($this->apiRoute . '/fetchRelate');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    /// 检查绑定关系
    function _IsBind($uid, $sid, $type = 'and', $DataType = false) {
        $Query = "SELECT `uid`,`sina_uid` FROM `" . DB::table('xwb_bind_info') . "` WHERE `uid`='{$uid}' " . ('or'==strtolower($type)?'OR':'AND') . " `sina_uid`='{$sid}'";
        $RS = $this->_DBHelper($Query, 3);
        return $RS ? ($DataType?$RS:true) : ($DataType?array():false);
    }

    ///检查是否存在用户
    function _HasUser($uid) {
        $Query = "SELECT * FROM ".DB::table('common_member')." WHERE uid='" . $uid . "'";
        $RS = $this->_DBHelper($Query, 1);
        return $RS ? true: false;
    }
    
}
