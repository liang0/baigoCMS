<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\ctrl\console;

use app\classes\console\Ctrl;
use ginkgo\Loader;
use ginkgo\Func;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

class Admin extends Ctrl {

    protected function c_init($param = array()) {
        parent::c_init();

        $this->obj_reg      = Loader::classes('Reg', 'sso');
        $this->obj_user     = Loader::classes('User', 'sso');
        $this->mdl_cate     = Loader::model('Cate');
        $this->mdl_group    = Loader::model('Group');
        $this->mdl_admin    = Loader::model('Admin');

        $this->generalData['status']    = $this->mdl_admin->arr_status;
        $this->generalData['type']      = $this->mdl_admin->arr_type;
    }


    function index() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['admin']['browse']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x020301');
        }

        $_arr_searchParam = array(
            'key'       => array('str', ''),
            'status'    => array('str', ''),
            'type'      => array('str', ''),
        );

        $_arr_search = $this->obj_request->param($_arr_searchParam);

        $_num_adminCount  = $this->mdl_admin->count($_arr_search); //统计记录数
        $_arr_pageRow     = $this->obj_request->pagination($_num_adminCount); //取得分页数据
        $_arr_adminRows   = $this->mdl_admin->lists($this->config['var_default']['perpage'], $_arr_pageRow['except'], $_arr_search); //列出

        $_arr_tplData = array(
            'pageRow'    => $_arr_pageRow,
            'search'     => $_arr_search,
            'adminRows'  => $_arr_adminRows,
            'token'      => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_adminRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function show() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['admin']['browse']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x020301');
        }

        $_num_adminId = 0;

        if (isset($this->param['id'])) {
            $_num_adminId = $this->obj_request->input($this->param['id'], 'int', 0);
        }

        if ($_num_adminId < 1) {
            return $this->error('Missing ID', 'x020202');
        }

        $_arr_userRow = $this->obj_user->read($_num_adminId);

        if ($_arr_userRow['rcode'] != 'y010102') {
            return $this->error($_arr_userRow['msg'], $_arr_userRow['rcode']);
        }

        $_arr_adminRow = $this->mdl_admin->read($_num_adminId);

        if ($_arr_adminRow['rcode'] != 'y020102') {
            return $this->error($_arr_adminRow['msg'], $_arr_adminRow['rcode']);
        }

        $_arr_groupRow    = $this->mdl_group->read($_arr_adminRow['admin_group_id']);

        $_arr_search = array(
            'parent_id' => 0
        );
        $_arr_cateRows    = $this->mdl_cate->listsTree(1000, 0, $_arr_search);

        $_arr_tplData = array(
            'cateRows'  => $_arr_cateRows,
            'groupRow'  => $_arr_groupRow,
            'userRow'   => $_arr_userRow,
            'adminRow'  => $_arr_adminRow,
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_adminRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function addon() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['admin']['addon']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x020307');
        }

        $_num_adminId = 0;

        if (isset($this->param['id'])) {
            $_num_adminId = $this->obj_request->input($this->param['id'], 'int', 0);
        }

        if ($_num_adminId < 1) {
            return $this->error('Missing ID', 'x020202');
        }

        $_arr_adminRow = $this->mdl_admin->read($_num_adminId);

        if ($_arr_adminRow['rcode'] != 'y020102') {
            return $this->error($_arr_adminRow['msg'], $_arr_adminRow['rcode']);
        }

        $_arr_searchGroup = array(
            'group_target' => 'admin',
        );
        $_arr_groupRows   = $this->mdl_group->lists(1000, 0, $_arr_searchGroup);

        $_arr_tplData = array(
            'groupRows' => $_arr_groupRows,
            'adminRow'  => $_arr_adminRow,
            'token'     => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_adminRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function addonSubmit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        $_arr_inputAddon = $this->mdl_admin->inputAddon();

        if ($_arr_inputAddon['rcode'] != 'y020201') {
            return $this->fetchJson($_arr_inputAddon['msg'], $_arr_inputAddon['rcode']);
        }

        if (!isset($this->groupAllow['admin']['addon']) && !$this->isSuper) {
            return $this->fetchJson('You do not have permission', 'x020303');
        }

        if ($_arr_inputAddon['admin_id'] == $this->adminLogged['admin_id'] && !$this->isSuper) {
            return $this->fetchJson('Prohibit editing yourself', 'x020306');
        }

        if ($_arr_inputAddon['admin_group_id'] > 0) {
            $_arr_groupRow = $this->mdl_group->check($_arr_inputAddon['admin_group_id'], 'group_id', 'admin');

            if ($_arr_groupRow['rcode'] != 'y040102') {
                return $this->fetchJson($_arr_groupRow['msg'], $_arr_groupRow['rcode']);
            }
        }

        $_arr_submitResult = $this->mdl_admin->addon();

        return $this->fetchJson($_arr_submitResult['msg'], $_arr_submitResult['rcode']);
    }


    function form() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        $_num_adminId = 0;

        if (isset($this->param['id'])) {
            $_num_adminId = $this->obj_request->input($this->param['id'], 'int', 0);
        }

        if ($_num_adminId > 0) {
            if (!isset($this->groupAllow['admin']['edit']) && !$this->isSuper) { //判断权限
                return $this->error('You do not have permission', 'x020303');
            }
            if ($_num_adminId == $this->adminLogged['admin_id'] && !$this->isSuper) {
                return $this->error('Prohibit editing yourself', 'x020306');
            }

            $_arr_userRow = $this->obj_user->read($_num_adminId);

            if ($_arr_userRow['rcode'] != 'y010102') {
                return $this->error($_arr_userRow['msg'], $_arr_userRow['rcode']);
            }

            $_arr_adminRow = $this->mdl_admin->read($_num_adminId);

            if ($_arr_adminRow['rcode'] != 'y020102') {
                return $this->error($_arr_adminRow['msg'], $_arr_adminRow['rcode']);
            }
        } else {
            if (!isset($this->groupAllow['admin']['add']) && !$this->isSuper) { //判断权限
                return $this->error('You do not have permission', 'x020302');
            }
            $_arr_adminRow = array(
                'admin_id'          => 0,
                'admin_nick'        => '',
                'admin_note'        => '',
                'admin_status'      => $this->mdl_admin->arr_status[0],
                'admin_type'        => $this->mdl_admin->arr_type[0],
                'admin_allow_cate'  => array(),
            );

            $_arr_userRow = array(
                'user_mail' => '',
            );
        }

        $_arr_search = array(
            'parent_id' => 0
        );
        $_arr_cateRows    = $this->mdl_cate->listsTree(1000, 0, $_arr_search);

        $_arr_tplData = array(
            'cateRows'  => $_arr_cateRows,
            'userRow'   => $_arr_userRow,
            'adminRow'  => $_arr_adminRow,
            'token'     => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_adminRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function submit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        $_arr_inputSubmit = $this->mdl_admin->inputSubmit();

        if ($_arr_inputSubmit['rcode'] != 'y020201') {
            return $this->fetchJson($_arr_inputSubmit['msg'], $_arr_inputSubmit['rcode']);
        }

        if ($_arr_inputSubmit['admin_id'] > 0) {
            if (!isset($this->groupAllow['admin']['edit']) && !$this->isSuper) {
                return $this->fetchJson('You do not have permission', 'x020303');
            }

            if ($_arr_inputSubmit['admin_id'] == $this->adminLogged['admin_id'] && !$this->isSuper) {
                return $this->fetchJson('Prohibit editing yourself', 'x020306');
            }

            $_arr_userSubmit = array();

            if (!Func::isEmpty($_arr_inputSubmit['admin_mail_new'])) {
                $_arr_userSubmit['user_mail_new'] = $_arr_inputSubmit['admin_mail_new'];
            }

            if (!Func::isEmpty($_arr_inputSubmit['admin_pass'])) {
                $_arr_userSubmit['user_pass'] = $_arr_inputSubmit['admin_pass'];
            }

            if (!Func::isEmpty($_arr_userSubmit)) {
                $_arr_editResult = $this->obj_user->edit($_arr_inputSubmit['admin_id'], 'user_id', $_arr_userSubmit);
            }
        } else {
            if (!isset($this->groupAllow['admin']['add']) && !$this->isSuper) {
                return $this->fetchJson('You do not have permission', 'x020302');
            }

            $_arr_userSubmit = array(
                'user_name' => $_arr_inputSubmit['admin_name'],
                'user_pass' => $_arr_inputSubmit['admin_pass'],
                'user_mail' => $_arr_inputSubmit['admin_mail'],
                'user_nick' => $_arr_inputSubmit['admin_nick'],
            );

            $_arr_regResult = $this->obj_reg->reg($_arr_userSubmit);

            if ($_arr_regResult['rcode'] != 'y010101') {
                return $this->fetchJson($_arr_regResult['msg'], $_arr_regResult['rcode']);
            }

            $this->mdl_admin->inputSubmit['admin_id'] = $_arr_regResult['user_id'];
        }

        $_arr_submitResult = $this->mdl_admin->submit();

        return $this->fetchJson($_arr_submitResult['msg'], $_arr_submitResult['rcode']);
    }


    function delete() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['admin']['delete']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x020304');
        }

        $_arr_inputDelete = $this->mdl_admin->inputDelete();

        if ($_arr_inputDelete['rcode'] != 'y020201') {
            return $this->fetchJson($_arr_inputDelete['msg'], $_arr_inputDelete['rcode']);
        }

        $_arr_deleteResult = $this->mdl_admin->delete();

        $_arr_langReplace = array(
            'count' => $_arr_deleteResult['count'],
        );

        return $this->fetchJson($_arr_deleteResult['msg'], $_arr_deleteResult['rcode'], '', $_arr_langReplace);
    }


    function status() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['admin']['edit']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x020303');
        }

        $_arr_inputStatus = $this->mdl_admin->inputStatus();

        if ($_arr_inputStatus['rcode'] != 'y020201') {
            return $this->fetchJson($_arr_inputStatus['msg'], $_arr_inputStatus['rcode']);
        }

        $_arr_statusResult = $this->mdl_admin->status();

        $_arr_langReplace = array(
            'count' => $_arr_statusResult['count'],
        );

        return $this->fetchJson($_arr_statusResult['msg'], $_arr_statusResult['rcode'], '', $_arr_langReplace);
    }


    function chkname() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        $_arr_return = array(
            'msg' => '',
        );

        $_str_adminName = $this->obj_request->get('admin_name');

        if (!Func::isEmpty($_str_adminName)) {
            $_arr_userRow   = $this->obj_user->read($_str_adminName, 'user_name');

            if ($_arr_userRow['rcode'] == 'y010102') {
                $_arr_adminRow = $this->mdl_admin->check($_arr_userRow['user_id']);
                if ($_arr_adminRow['rcode'] == 'y020102') {
                    $_arr_return = array(
                        'rcode' => 'x020404',
                        'error' => $this->obj_lang->get('Administrator already exists'),
                    );
                } else {
                    $_arr_return = array(
                        'rcode' => 'x010404',
                        'error' => $this->obj_lang->get('User already exists, please use authorization as administrator'),
                    );
                }
            }
        }

        return $this->json($_arr_return);
    }


    function chkmail() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        $_arr_return = array(
            'msg' => '',
        );

        $_str_adminMail = $this->obj_request->get('admin_mail');

        if (!Func::isEmpty($_str_adminMail)) {
            $_arr_userRow   = $this->obj_user->read($_str_adminMail, 'user_mail');

            if ($_arr_userRow['rcode'] == 'y010102') {
                $_arr_adminRow = $this->mdl_admin->check($_arr_userRow['user_id']);
                if ($_arr_adminRow['rcode'] == 'y020102') {
                    $_arr_return = array(
                        'rcode' => 'x020404',
                        'error' => $this->obj_lang->get('Administrator already exists'),
                    );
                } else {
                    $_arr_return = array(
                        'rcode' => 'x010404',
                        'error' => $this->obj_lang->get('User already exists, please use authorization as administrator'),
                    );
                }
            }
        }

        return $this->json($_arr_return);
    }
}
