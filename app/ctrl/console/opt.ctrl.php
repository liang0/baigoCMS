<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\ctrl\console;

use app\classes\console\Ctrl;
use ginkgo\Loader;
use ginkgo\Config;
use ginkgo\File;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

class Opt extends Ctrl {

    protected function c_init($param = array()) {
        parent::c_init();

        $this->mdl_opt    = Loader::model('Opt');
    }


    function form() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['opt'][$this->routeOrig['act']]) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x030301');
        }

        $_arr_consoleOpt  = Config::get($this->routeOrig['act'], 'console.opt');
        $_arr_consoleAct  = $_arr_consoleOpt['lists'];

        $_arr_tplData = array(
            'token' => $this->obj_request->token(),
        );

        if ($this->routeOrig['act'] == 'base') {
            $_arr_tplData['tplRows']  = File::instance()->dirList(BG_TPL_INDEX);

            $_str_configTimezone    = BG_PATH_CONFIG . 'console' . DS . 'timezone' . GK_EXT_INC;
            $_arr_timezoneRows      = Config::load($_str_configTimezone, 'timezone', 'console');

            $_str_current           = $this->obj_lang->getCurrent();
            $_str_langPath          = GK_APP_LANG . $_str_current . DS . 'console' . DS . 'timezone' . GK_EXT_LANG;
            $_arr_timezoneLang      = $this->obj_lang->load($_str_langPath, 'console.timezone');

            $_arr_timezone[] = '';

            //print_r($this->config['var_extra']['base']);

            if (!isset($this->config['var_extra']['base']['site_timezone']) || !strpos($this->config['var_extra']['base']['site_timezone'], '/')) {
                $this->config['var_extra']['base']['site_timezone'] = $this->config['var_default']['timezone'];
            }

            if (strpos($this->config['var_extra']['base']['site_timezone'], '/')) {
                $_arr_timezone = explode('/', $this->config['var_extra']['base']['site_timezone']);
            }

            $_arr_tplData['timezoneRows']       = $_arr_timezoneRows;
            $_arr_tplData['timezoneRowsJson']   = json_encode($_arr_timezoneRows);
            $_arr_tplData['timezoneLangJson']   = json_encode($_arr_timezoneLang);
            $_arr_tplData['timezoneType']       = strtolower($_arr_timezone[0]);

            $_mdl_thumb     = Loader::model('Thumb');
            $_arr_thumbRows = $_mdl_thumb->lists(1000);

            $_arr_thumbs = array();

            foreach ($_arr_thumbRows as $_key=>$_value) {
                $_arr_thumbs[$_value['thumb_id']] = $_value['thumb_width'] . ' X ' . $_value['thumb_height'] . ' [ ' . $this->obj_lang->get($_value['thumb_type']) . ' ]';
            }

            $_arr_consoleAct['site_thumb_default']['option'] = $_arr_thumbs;
        }

        foreach ($_arr_consoleAct as $_key=>$_value) {
            if (isset($this->config['var_extra'][$this->routeOrig['act']][$_key])) {
                $_arr_consoleAct[$_key]['this'] = $this->config['var_extra'][$this->routeOrig['act']][$_key];
            } else {
                $_arr_consoleAct[$_key]['this'] = '';
            }

            if (isset($_value['option']) && is_array($_value['option'])) {
                foreach ($_value['option'] as $_key_opt=>$_value_opt) {
                    if (isset($_value['date_param'])) {
                        $_str_replace = date($_key_opt);
                    } else {
                        $_str_replace = $_key_opt;
                    }

                    $_arr_consoleAct[$_key]['lang_replace'][$_key_opt] = $_str_replace;
                    $_arr_consoleAct[$_key]['lang_replace']['value']   = $_str_replace;
                }
            }

            $_arr_consoleAct[$_key]['lang_replace']['visit_default'] = $this->obj_request->root(true) . 'index.php/aritcle/' . date('Y') . '/' . date('m') . '/123/';
            $_arr_consoleAct[$_key]['lang_replace']['visit_pstatic'] = $this->obj_request->root(true) . 'aritcle/' . date('Y') . '/' . date('m') . '/123/';
            $_arr_consoleAct[$_key]['lang_replace']['visit_static']  = $this->obj_request->root(true) . 'aritcle/' . date('Y') . '/' . date('m') . '/123.html';
        }

        $_arr_tplData['consoleOpt'] = $_arr_consoleAct;

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

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

        if (!isset($this->groupAllow['opt'][$this->routeOrig['act']]) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x030301');
        }

        $_arr_inputSubmit = $this->mdl_opt->inputSubmit();

        if ($_arr_inputSubmit['rcode'] != 'y030201') {
            return $this->fetchJson($_arr_inputSubmit['msg'], $_arr_inputSubmit['rcode']);
        }

        $_arr_submitResult = $this->mdl_opt->submit();

        return $this->fetchJson($_arr_submitResult['msg'], $_arr_submitResult['rcode']);
    }


    function dbconfig() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['opt']['dbconfig']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x030301');
        }

        $_arr_tplData = array(
            'token' => $this->obj_request->token(),
        );


        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function dbconfigSubmit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['opt']['dbconfig']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x030301');
        }

        $_arr_inputDbconfig = $this->mdl_opt->inputDbconfig();

        if ($_arr_inputDbconfig['rcode'] != 'y030201') {
            return $this->fetchJson($_arr_inputDbconfig['msg'], $_arr_inputDbconfig['rcode']);
        }

        $_arr_dbconfigResult = $this->mdl_opt->dbconfig();

        return $this->fetchJson($_arr_dbconfigResult['msg'], $_arr_dbconfigResult['rcode']);
    }

    function upload() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['opt']['upload']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x030301');
        }

        $_arr_tplData = array(
            'token'     => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function uploadSubmit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['opt']['upload']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x030301');
        }

        $_arr_inputUpload = $this->mdl_opt->inputUpload();

        if ($_arr_inputUpload['rcode'] != 'y030201') {
            return $this->fetchJson($_arr_inputUpload['msg'], $_arr_inputUpload['rcode']);
        }

        $_arr_uploadResult = $this->mdl_opt->upload();

        return $this->fetchJson($_arr_uploadResult['msg'], $_arr_uploadResult['rcode']);
    }


    function dataUpgrade() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['opt']['dbconfig']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x030301');
        }

        $_str_configInstall = BG_PATH_CONFIG . 'install' . DS . 'common' . GK_EXT_INC;
        $_arr_installRows   = Config::load($_str_configInstall, 'common', 'install');

        $_arr_tplData = array(
            'config_upgrade'    => $_arr_installRows['data']['upgrade'],
            'token'             => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function dataSubmit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['opt']['dbconfig']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x030301');
        }

        $_arr_inputData = $this->mdl_opt->inputData();

        if ($_arr_inputData['rcode'] != 'y030201') {
            return $this->fetchJson($_arr_inputData['msg'], $_arr_inputData['rcode']);
        }

        switch ($_arr_inputData['type']) {
            case 'index':
                $_arr_dataResult = $this->createIndex($_arr_inputData['model']);
            break;

            case 'view':
                $_arr_dataResult = $this->createView($_arr_inputData['model']);
            break;

            case 'alter':
                $_arr_dataResult = $this->alterTable($_arr_inputData['model']);
            break;

            case 'rename':
                $_arr_dataResult = $this->renameTable($_arr_inputData['model']);
            break;

            case 'copy':
                $_arr_dataResult = $this->copyTable($_arr_inputData['model']);
            break;

            case 'drop':
                $_arr_dataResult = $this->dropColumn($_arr_inputData['model']);
            break;

            default:
                $_arr_dataResult = $this->createTable($_arr_inputData['model']);
            break;
        }

        return $this->fetchJson($_arr_dataResult['msg'], $_arr_dataResult['rcode']);
    }


    function chkver() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['opt']['chkver']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x030301');
        }

        if (isset($this->config['ui_ctrl']['update_check']) && $this->config['ui_ctrl']['update_check'] != 'on') {
            return $this->error('Check for updated module being disabled', 'x030301');
        }

        $_arr_configBase    = Config::get('base', 'var_extra');
        $_arr_installed     = Config::get('installed'); //当前安装的
        $_arr_latest        = $this->mdl_opt->chkver();

        if (!isset($_arr_configBase['site_date'])) {
            $_arr_configBase['site_date'] = 'Y-m-d';
        }

        if (!isset($_arr_configBase['site_date_short'])) {
            $_arr_configBase['site_date_short'] = 'm-d';
        }

        if (!isset($_arr_configBase['site_time_short'])) {
            $_arr_configBase['site_time_short'] = 'H:i';
        }

        if (!isset($_arr_installed['prd_installed_pub'])) {
            $_arr_installed['prd_installed_pub'] = PRD_CMS_PUB;
        }

        if (!isset($_arr_latest['prd_pub'])) {
            $_arr_latest['prd_pub'] = PRD_CMS_PUB;
        }

        $_arr_installed['prd_installed_pub_datetime']   = date($_arr_configBase['site_date'], strtotime($_arr_installed['prd_installed_pub']));
        $_arr_installed['prd_installed_datetime']       = date($_arr_configBase['site_date'] . ' ' . $_arr_configBase['site_time_short'], $_arr_installed['prd_installed_time']);

        $_arr_latest['prd_pub_datetime']        = date($_arr_configBase['site_date'], strtotime($_arr_latest['prd_pub']));

        $_arr_tplData = array(
            'installed' => $_arr_installed,
            'latest'    => $_arr_latest,
            'token'     => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function chkverSubmit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['opt']['chkver']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x030301');
        }

        if (isset($this->config['ui_ctrl']['update_check']) && $this->config['ui_ctrl']['update_check'] != 'on') {
            return $this->fetchJson('Check for updated module being disabled', 'x030301');
        }

        $_arr_inputCommon = $this->mdl_opt->inputCommon();

        if ($_arr_inputCommon['rcode'] != 'y030201') {
            return $this->fetchJson($_arr_inputCommon['msg'], $_arr_inputCommon['rcode']);
        }

        $_arr_latestResult = $this->mdl_opt->latest('manual');

        return $this->fetchJson($_arr_latestResult['msg'], $_arr_latestResult['rcode']);
    }


    protected function createTable($table) {
        $_mdl_table          = Loader::model($table, '', 'install');
        $_arr_createResult   = $_mdl_table->createTable();

       return array(
            'rcode'   => $_arr_createResult['rcode'],
            'msg'     => $_arr_createResult['msg'],
        );
    }


    protected function createIndex($index) {
        $_mdl_index          = Loader::model($index, '', 'install');
        $_arr_createResult   = $_mdl_index->createIndex();

        return array(
            'rcode'   => $_arr_createResult['rcode'],
            'msg'     => $_arr_createResult['msg'],
        );
    }


    protected function createView($view) {
        $_mdl_view           = Loader::model($view, '', 'install');
        $_arr_createResult   = $_mdl_view->createView();

        return array(
            'rcode'   => $_arr_createResult['rcode'],
            'msg'     => $_arr_createResult['msg'],
        );
    }


    protected function alterTable($table) {
        $_mdl_table          = Loader::model($table, '', 'install');
        $_arr_alterResult    = $_mdl_table->alterTable();

       return array(
            'rcode'   => $_arr_alterResult['rcode'],
            'msg'     => $_arr_alterResult['msg'],
        );
    }


    protected function copyTable($table) {
        $_mdl_table         = Loader::model($table, '', 'install');
        $_arr_copyResult    = $_mdl_table->copyTable();

       return array(
            'rcode'   => $_arr_copyResult['rcode'],
            'msg'     => $_arr_copyResult['msg'],
        );
    }


    protected function renameTable($table) {
        $_mdl_table          = Loader::model($table, '', 'install');
        $_arr_renmaeResult   = $_mdl_table->renameTable();

       return array(
            'rcode'   => $_arr_renmaeResult['rcode'],
            'msg'     => $_arr_renmaeResult['msg'],
        );
    }


    protected function dropColumn($table) {
        $_mdl_table        = Loader::model($table, '', 'install');
        $_arr_dropResult   = $_mdl_table->dropColumn();

       return array(
            'rcode'   => $_arr_dropResult['rcode'],
            'msg'     => $_arr_dropResult['msg'],
        );
    }
}