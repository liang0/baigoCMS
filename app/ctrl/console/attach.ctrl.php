<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\ctrl\console;

use app\classes\console\Ctrl;
use ginkgo\Loader;
use ginkgo\Config;
use ginkgo\Func;
use ginkgo\File;
use ginkgo\Upload;
use ginkgo\Image;
use ginkgo\Ftp;
use ginkgo\Json;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

class Attach extends Ctrl {

    protected function c_init($param = array()) {
        parent::c_init();

        if ($this->ftpOpen && !$this->ftpInit) {
            //print_r($this->config['upload']);

            $_arr_configFtp = $this->config['var_extra']['upload'];

            $_config_ftp = array(
                'host' => $_arr_configFtp['ftp_host'],
                'port' => $_arr_configFtp['ftp_port'],
                'user' => $_arr_configFtp['ftp_user'],
                'pass' => $_arr_configFtp['ftp_pass'],
                'path' => $_arr_configFtp['ftp_path'],
                'pasv' => $_arr_configFtp['ftp_pasv'],
            );

            if (!Func::isEmpty($_config_ftp['host']) && !Func::isEmpty($_config_ftp['user']) && !Func::isEmpty($_config_ftp['pass'])) {
                $this->obj_ftp = Ftp::instance($_config_ftp);
                $this->ftpInit   = true;
            }
        }

        $this->obj_qlist        = Loader::classes('Qlist');

        $this->mdl_admin        = Loader::model('Admin');
        $this->mdl_thumb        = Loader::model('Thumb');
        $this->mdl_article      = Loader::model('Article');
        $this->mdl_album        = Loader::model('Album');
        $this->mdl_albumView    = Loader::model('Album_View');
        $this->mdl_albumBelong  = Loader::model('Album_Belong');

        $this->mdl_attach       = Loader::model('Attach');

        $this->generalData['box']    = $this->mdl_attach->arr_box;
    }


    function index() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['attach']['browse']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x070301');
        }

        if (Func::isEmpty($this->allowExts)) {
            return $this->error('MIME has not been set', 'x070405');
        }

        $_arr_searchParam = array(
            'key'   => array('str', ''),
            'box'   => array('str', 'normal'),
            'year'  => array('str', ''),
            'month' => array('str', ''),
            'ext'   => array('str', ''),
            'admin' => array('int', 0),
            'ids'   => array('str', ''),
        );

        $_arr_search = $this->obj_request->param($_arr_searchParam);

        if (Func::isEmpty($_arr_search['ids'])) {
            $_arr_search['attach_ids'] = false;
        } else {
            $_arr_search['attach_ids'] = explode(',', $_arr_search['ids']);
        }

        $_arr_search['admin_id'] = $_arr_search['admin'];

        $_num_attachCount  = $this->mdl_attach->count($_arr_search); //统计记录数
        $_arr_pageRow      = $this->obj_request->pagination($_num_attachCount); //取得分页数据
        $_arr_attachRows   = $this->mdl_attach->lists($this->config['var_default']['perpage'], $_arr_pageRow['except'], $_arr_search); //列出

        foreach ($_arr_attachRows as $_key=>$_value) {
            if (!isset($_value['thumb_default'])) {
                $_arr_attachRows[$_key]['thumb_default'] = $this->url['dir_static'] . 'image/file_' . $_value['attach_ext'] . '.png';
            }

            $_arr_attachRows[$_key]['adminRow'] = $this->mdl_admin->read($_value['attach_admin_id']);
        }

        $_arr_searchAll = array(
            'box' => 'normal',
        );

        $_arr_searchRecycle = array(
            'box' => 'recycle',
        );

        $_arr_searchReserve = array(
            'box' => 'reserve',
        );

        $_arr_attachCount['all']        = $this->mdl_attach->count($_arr_searchAll);
        $_arr_attachCount['recycle']    = $this->mdl_attach->count($_arr_searchRecycle);
        $_arr_attachCount['reserve']    = $this->mdl_attach->count($_arr_searchReserve);
        $_arr_yearRows                  = $this->mdl_attach->year(100);
        $_arr_extRows                   = $this->mdl_attach->ext();

        $_arr_tplData = array(
            'pageRow'       => $_arr_pageRow,
            'search'        => $_arr_search,
            'attachCount'   => $_arr_attachCount,
            'attachRows'    => $_arr_attachRows,
            'yearRows'      => $_arr_yearRows, //目录列表
            'extRows'       => $_arr_extRows, //扩展名列表
            'token'         => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_attachRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function choose() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['attach']['browse']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x070301');
        }

        $_num_articleId = 0;

        if (isset($this->param['article'])) {
            $_num_articleId = $this->obj_request->input($this->param['article'], 'int', 0);
        }

        $_arr_articleRow = array();

        if ($_num_articleId > 0) {
            $_arr_articleRow = $this->mdl_article->read($_num_articleId);
        }

        $_arr_yearRows  = $this->mdl_attach->year(100);
        $_arr_extRows   = $this->mdl_attach->ext();

        foreach ($this->mdl_thumb->arr_type as $_key=>$_value) {
            $_arr_thumbType[$_value] = $this->obj_lang->get($_value);
        }

        $_arr_tplData = array(
            'articleRow'    => $_arr_articleRow,
            'yearRows'      => $_arr_yearRows, //目录列表
            'extRows'       => $_arr_extRows, //扩展名列表
            'thumbType'     => Json::encode($_arr_thumbType),
            'token'         => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_attachRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function form() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        $_num_attachId = 0;

        if (isset($this->param['id'])) {
            $_num_attachId = $this->obj_request->input($this->param['id'], 'int', 0);
        }

        $_arr_attachRow = array(
            'attach_id' => 0,
        );
        $_arr_albumRow      = array(
            'album_id' => 0,
        );
        $_arr_adminRow      = array();
        $_arr_albumRows     = array();
        $_arr_attachRows    = array();

        if ($_num_attachId > 0) {
            if (!isset($this->groupAllow['attach']['edit']) && !$this->isSuper) { //判断权限
                return $this->error('You do not have permission', 'x070303');
            }

            $_arr_attachRow = $this->mdl_attach->read($_num_attachId);

            if ($_arr_attachRow['rcode'] != 'y070102') {
                return $this->error($_arr_attachRow['msg'], $_arr_attachRow['rcode']);
            }

            if ($_arr_attachRow['attach_type'] == 'image') {
                //print_r($_arr_url);
                foreach ($_arr_attachRow['thumbRows'] as $_key_thumb=>$_value_thumb) {
                    if (Func::isFile($_value_thumb['thumb_path'])) {
                        $_arr_attachRow['thumbRows'][$_key_thumb]['thumb_exists'] = 'exists';
                    } else {
                        $_arr_attachRow['thumbRows'][$_key_thumb]['thumb_exists'] = 'notfound';
                    }
                }
            } else {
                $_arr_attachRow['attach_url'] = $this->url['dir_static'] . 'image/file_' . $_arr_attachRow['attach_ext'] . '.png';
            }

            if (Func::isFile($_arr_attachRow['attach_path'])) {
                $_arr_attachRow['attach_exists'] = 'exists';
            } else {
                $_arr_attachRow['attach_exists'] = 'notfound';
            }

            $_arr_adminRow = $this->mdl_admin->read($_arr_attachRow['attach_admin_id']);

            $_arr_searchAlbum = array(
                'attach_id'    => $_arr_attachRow['attach_id'],
            );
            $_arr_albumRows = $this->mdl_albumView->lists(1000, 0, $_arr_searchAlbum);
        } else {
            if (!isset($this->groupAllow['attach']['add']) && !$this->isSuper) { //判断权限
                return $this->error('You do not have permission', 'x070302');
            }

            $_num_albumId  = 0;

            if (isset($this->param['album'])) {
                $_num_albumId = $this->obj_request->input($this->param['album'], 'int', 0);
            }

            if ($_num_albumId > 0) {
                $_arr_albumRow = $this->mdl_album->read($_num_albumId);

                if ($_arr_albumRow['rcode'] != 'y060102') {
                    return $this->error($_arr_albumRow['msg'], $_arr_albumRow['rcode']);
                }

                $_arr_albumRows[]   = $_arr_albumRow;

                $_arr_searchBelong = array(
                    'album_id' => $_num_albumId,
                );

                $_mdl_attachAlbumView  = Loader::model('Attach_Album_View');

                $_num_attachCount   = $_mdl_attachAlbumView->count($_arr_searchBelong); //统计记录数
                $_arr_pageRow       = $this->obj_request->pagination($_num_attachCount); //取得分页数据
                $_arr_attachRows    = $_mdl_attachAlbumView->lists($this->config['var_default']['perpage'], $_arr_pageRow['except'], $_arr_searchBelong); //列出

                foreach ($_arr_attachRows as $_key=>$_value) {
                    if (!isset($_value['thumb_default'])) {
                        $_arr_attachRows[$_key]['thumb_default'] = $this->url['dir_static'] . 'image/file_' . $_value['attach_ext'] . '.png';
                    }
                }
            }
        }

        //print_r($_arr_albumIds);

        $_arr_tplData = array(
            'attachRows'    => $_arr_attachRows,
            'albumRows'     => $_arr_albumRows,
            'albumRow'      => $_arr_albumRow,
            'attachRow'     => $_arr_attachRow,
            'token'         => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_attachRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function show() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->error($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['attach']['browse']) && !$this->isSuper) { //判断权限
            return $this->error('You do not have permission', 'x070303');
        }

        $_num_attachId = 0;

        if (isset($this->param['id'])) {
            $_num_attachId = $this->obj_request->input($this->param['id'], 'int', 0);
        }

        if ($_num_attachId < 1) {
            return $this->error('Missing ID', 'x070202');
        }

        $_arr_attachRow = $this->mdl_attach->read($_num_attachId);

        if ($_arr_attachRow['rcode'] != 'y070102') {
            return $this->error($_arr_attachRow['msg'], $_arr_attachRow['rcode']);
        }

        if ($_arr_attachRow['attach_type'] == 'image') {
            //print_r($_arr_url);
            foreach ($_arr_attachRow['thumbRows'] as $_key_thumb=>$_value_thumb) {
                if (Func::isFile($_value_thumb['thumb_path'])) {
                    $_arr_attachRow['thumbRows'][$_key_thumb]['thumb_exists'] = 'exists';
                } else {
                    $_arr_attachRow['thumbRows'][$_key_thumb]['thumb_exists'] = 'notfound';
                }
            }
        } else {
            $_arr_attachRow['attach_url'] = $this->url['dir_static'] . 'image/file_' . $_arr_attachRow['attach_ext'] . '.png';
        }

        if (Func::isFile($_arr_attachRow['attach_path'])) {
            $_arr_attachRow['attach_exists'] = 'exists';
        } else {
            $_arr_attachRow['attach_exists'] = 'notfound';
        }

        $_arr_adminRow = $this->mdl_admin->read($_arr_attachRow['attach_admin_id']);

        $_arr_searchAlbum = array(
            'attach_id'    => $_arr_attachRow['attach_id'],
        );
        $_arr_albumRows = $this->mdl_albumView->lists(1000, 0, $_arr_searchAlbum);

        $_arr_tplData = array(
            'adminRow'  => $_arr_adminRow,
            'albumRows' => $_arr_albumRows,
            'attachRow' => $_arr_attachRow,
            'token'     => $this->obj_request->token(),
        );

        $_arr_tpl = array_replace_recursive($this->generalData, $_arr_tplData);

        //print_r($_arr_attachRows);

        $this->assign($_arr_tpl);

        return $this->fetch();
    }


    function lists() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!isset($this->groupAllow['attach']['browse']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x070301');
        }

        if (Func::isEmpty($this->allowExts)) {
            return $this->fetchJson('MIME has not been set', 'x070405');
        }

        $_arr_searchParam = array(
            'key'       => array('str', ''),
            'year'      => array('str', ''),
            'month'     => array('str', ''),
            'ext'       => array('str', ''),
            'article'   => array('int', 0),
        );

        $_arr_search = $this->obj_request->param($_arr_searchParam);

        if ($_arr_search['article'] > 0) {
            $_arr_articleRow            = $this->mdl_article->read($_arr_search['article']);
            $_arr_search['attach_ids']  = $this->obj_qlist->getAttachIds($_arr_articleRow['article_content']);
        }

        $_arr_search['box'] = 'normal';

        $_num_perPage      = 12;
        $_num_attachCount  = $this->mdl_attach->count($_arr_search); //统计记录数
        $_arr_pageRow      = $this->obj_request->pagination($_num_attachCount, $_num_perPage); //取得分页数据
        $_arr_attachRows   = $this->mdl_attach->lists($_num_perPage, $_arr_pageRow['except'], $_arr_search); //列出

        foreach ($_arr_attachRows as $_key=>$_value) {
            if (!isset($_value['thumb_default'])) {
                $_arr_attachRows[$_key]['thumb_default'] = $this->url['dir_static'] . 'image/file_' . $_value['attach_ext'] . '.png';
            }
        }

        $_arr_tplData = array(
            'pageRow'       => $_arr_pageRow,
            'search'        => $_arr_search,
            'attachRows'    => $_arr_attachRows,
        );

        return $this->json($_arr_tplData);
    }


    function submit() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        $_arr_inputSubmit = $this->mdl_attach->inputSubmit();

        if ($_arr_inputSubmit['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputSubmit['msg'], $_arr_inputSubmit['rcode']);
        }

        if (!isset($this->groupAllow['attach']['edit']) && !$this->isSuper) {
            return $this->fetchJson('You do not have permission', 'x070303');
        }

        $_arr_submitResult = $this->mdl_attach->submit();

        $_arr_searchAlbum = array(
            'album_ids' => $_arr_inputSubmit['attach_album_ids'],
        );
        $_arr_albumIds = $this->mdl_album->ids($_arr_searchAlbum);

        $_num_submitCount = 0;

        if (Func::isEmpty($_arr_albumIds)) {
            $_num_deleteCount = $this->mdl_albumBelong->delete(0, $_arr_inputSubmit['attach_id']);
        } else {
            foreach ($_arr_albumIds as $_key=>$_value) {
                $_arr_submitResultBelong = $this->mdl_albumBelong->submit($_arr_inputSubmit['attach_id'], $_value);

                if ($_arr_submitResultBelong['rcode'] == 'y290101' || $_arr_submitResultBelong['rcode'] == 'y290103') {
                    ++$_num_submitCount;
                }
            }

            $_num_deleteCount = $this->mdl_albumBelong->delete(0, $_arr_inputSubmit['attach_id'], false, false, $_arr_albumIds);
        }

        if ($_arr_submitResult['rcode'] == 'x070103') {
            if ($_num_submitCount > 0 || $_num_deleteCount > 0) {
                $_arr_submitResult = array(
                    'rcode' => 'y070103',
                    'msg'   => 'Update attachment successfully',
                );
            }
        }

        return $this->fetchJson($_arr_submitResult['msg'], $_arr_submitResult['rcode']);
    }


    function upload() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->obj_request->isPost()) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['add']) && !$this->isSuper) {
            return $this->fetchJson('You do not have permission', 'x070302');
        }

        if (Func::isEmpty($this->allowExts)) {
            return $this->fetchJson('MIME has not been set', 'x070405');
        }

        $_arr_inputUpload = $this->mdl_attach->inputUpload();

        if ($_arr_inputUpload['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputUpload['msg'], $_arr_inputUpload['rcode']);
        }

        $_obj_upload = Upload::instance();

        $_obj_upload->setMime($this->mimeRows);

        if (!$_obj_upload->create('attach_files')) {
            $_str_error  = $_obj_upload->getError();
            return $this->fetchJson($_str_error, 'x070403');
        }

        $this->mdl_attach->inputSubmit = array(
            'attach_name'       => $_obj_upload->name(),
            'attach_ext'        => $_obj_upload->ext(),
            'attach_mime'       => $_obj_upload->mime(),
            'attach_admin_id'   => $this->adminLogged['admin_id'],
            'attach_size'       => $_obj_upload->size(),
        );

        $_arr_submitResult = $this->mdl_attach->submit();

        if ($_arr_submitResult['rcode'] != 'y070101') {
            return $this->fetchJson($_arr_submitResult['msg'], $_arr_submitResult['rcode']);
        }

        /*$_arr_submitResult['attach_url_name']   = $_str_attachName;
        $_arr_submitResult['attach_path']       = GK_PATH_ATTACH . $_str_attachName;*/

        $_arr_attachPath = pathinfo($_arr_submitResult['attach_path']);

        if (!$_obj_upload->move($_arr_attachPath['dirname'], $_arr_attachPath['basename'])) {
            $this->mdl_attach->inputReserve['attach_id'] = $_arr_submitResult['attach_id'];
            $this->mdl_attach->reserve();

            $_str_error = $_obj_upload->getError();

            return $this->fetchJson($_str_error, 'x070401');
        }

        $_arr_attachRow = $this->uploadProcess($_arr_submitResult);

        if ($_arr_attachRow['rcode'] != 'y070101') {
            return $this->fetchJson($_arr_attachRow['msg'], $_arr_attachRow['rcode']);
        }

        $_arr_searchAlbum = array(
            'album_ids' => $_arr_inputUpload['attach_album_ids'],
        );
        $_arr_albumIds = $this->mdl_album->ids($_arr_searchAlbum);

        if (Func::isEmpty($_arr_albumIds)) {
            $_num_deleteCount = $this->mdl_albumBelong->delete(0, $_arr_submitResult['attach_id']);
        } else {
            foreach ($_arr_albumIds as $_key=>$_value) {
                $this->mdl_albumBelong->submit($_arr_submitResult['attach_id'], $_value);
            }

            $_num_deleteCount = $this->mdl_albumBelong->delete(0, $_arr_submitResult['attach_id'], false, false, $_arr_albumIds);
        }

        $_arr_return = array(
            'rcode' => 'y070401',
            'msg'   => $this->obj_lang->get('Upload attachment successfully'),
        );

        $_arr_return = array_replace_recursive($_arr_submitResult, $_arr_return);

        return $this->json($_arr_return);
    }


    function regen() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['thumb']) && !$this->isSuper) {
            return $this->fetchJson('You do not have permission', 'x090304');
        }

        $_arr_inputRegen = $this->mdl_attach->inputRegen();

        if ($_arr_inputRegen['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputRegen['msg'], $_arr_inputRegen['rcode']);
        }

        $_arr_thumbRow = $this->mdl_thumb->read($_arr_inputRegen['thumb_id']);
        if ($_arr_thumbRow['rcode'] != 'y090102') {
            return $this->fetchJson($_arr_thumbRow['msg'], $_arr_thumbRow['rcode']);
        }

        $_arr_search = array(
            'box'       => 'normal',
            'min_id'    => $_arr_inputRegen['min_id'],
            'max_id'    => $_arr_inputRegen['max_id'],
        );

        $_arr_order = array(
            array('attach_id', 'ASC'),
        );

        $_num_perPage     = 10;
        $_num_attachCount = $this->mdl_attach->count($_arr_search);
        $_arr_pageRow     = $this->obj_request->pagination($_num_attachCount, $_num_perPage, 'post');
        $_arr_attachRows  = $this->mdl_attach->lists($_num_perPage, $_arr_pageRow['except'], $_arr_search, $_arr_order);

        //$_obj_finfo       = new finfo();

        $_str_status = 'complete';
        $_str_msg    = 'Complete';

        if ($_arr_pageRow['page'] < $_arr_pageRow['total']) {
            foreach ($_arr_attachRows as $_key=>$_value) {
                if (Func::isFile($_value['attach_path'])) {
                    $this->uploadProcess($_value);
                } else {
                    $this->mdl_attach->inputReserve['attach_id'] = $_value['attach_id'];
                    $this->mdl_attach->reserve();
                }

            }
            $_str_status = 'loading';
            $_str_msg    = 'Submitting';
        } else if ($_arr_pageRow['page'] == $_arr_pageRow['total']) {
            foreach ($_arr_attachRows as $_key=>$_value) {
                if (Func::isFile($_value['attach_path'])) {
                    $this->uploadProcess($_value);
                } else {
                    $this->mdl_attach->inputReserve['attach_id'] = $_value['attach_id'];
                    $this->mdl_attach->reserve();
                }
            }
        }

        $_arr_return = array(
            'page'      => $_arr_pageRow['page'],
            'msg'       => $this->obj_lang->get($_str_msg),
            'count'     => $_arr_pageRow['total'],
            'status'    => $_str_status,
            'min_id'    => $_arr_inputRegen['min_id'],
            'max_id'    => $_arr_inputRegen['max_id'],
            //'attach_id' => $_value['attach_id'],
        );

        return $this->json($_arr_return);
    }


    function fix() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['add']) && !$this->isSuper) {
            return $this->fetchJson('You do not have permission', 'x070302');
        }

        $_arr_inputFix = $this->mdl_attach->inputFix();

        if ($_arr_inputFix['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputFix['msg'], $_arr_inputFix['rcode']);
        }

        $_arr_attachRow = $this->mdl_attach->read($_arr_inputFix['attach_id']);

        if ($_arr_attachRow['rcode'] != 'y070102') {
            return $this->fetchJson($_arr_attachRow['msg'], $_arr_attachRow['rcode']);
        }

        if (!Func::isFile($_arr_attachRow['attach_path'])) {
            $this->mdl_attach->inputReserve['attach_id'] = $_arr_attachRow['attach_id'];
            $this->mdl_attach->reserve();

            return $this->fetchJson('File not found', 'x070102');
        }

        $_arr_attachRow = $this->uploadProcess($_arr_attachRow);

        if ($_arr_attachRow['rcode'] != 'y070102') {
            return $this->fetchJson($_arr_attachRow['msg'], $_arr_attachRow['rcode']);
        }

        return $this->fetchJson('Attachment fixed successful', 'y070410');
    }


    function box() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['delete']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x070304');
        }

        $_arr_inputBox = $this->mdl_attach->inputBox();

        if ($_arr_inputBox['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputBox['msg'], $_arr_inputBox['rcode']);
        }

        $_arr_boxResult = $this->mdl_attach->box();

        $_arr_langReplace = array(
            'count' => $_arr_boxResult['count'],
        );

        return $this->fetchJson($_arr_boxResult['msg'], $_arr_boxResult['rcode'], '', $_arr_langReplace);
    }


    function clear() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['delete']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x070304');
        }

        $_arr_inputClear = $this->mdl_attach->inputClear();

        if ($_arr_inputClear['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputClear['msg'], $_arr_inputClear['rcode']);
        }

        $_num_maxId = $_arr_inputClear['max_id'];

        $_arr_searchCount = array(
            'box'   => 'normal',
        );

        $_arr_searchList = array(
            'box'       => 'normal',
            'max_id'    => $_num_maxId,
        );

        $_num_perPage     = 10;
        $_num_attachCount = $this->mdl_attach->count($_arr_searchCount);
        $_arr_pageRow     = $this->obj_request->pagination($_num_attachCount, $_num_perPage, 'post');
        $_arr_attachRows  = $this->mdl_attach->lists($_num_perPage, 0, $_arr_searchList);

        if (Func::isEmpty($_arr_attachRows)) {
            $_str_status    = 'complete';
            $_str_msg       = 'Complete';
        } else {
            foreach ($_arr_attachRows as $_key=>$_value) {
                $_arr_attachRow = $this->mdl_attach->clearChk($_value);
                //print_r($_arr_attachRow);
                if ($_arr_attachRow['rcode'] == 'x070406') {
                    $this->mdl_attach->inputBox['act']           = 'recycle';
                    $this->mdl_attach->inputBox['attach_ids']    = array($_value['attach_id']);
                    $_arr_boxResult = $this->mdl_attach->box();
                }
            }
            $_str_status    = 'loading';
            $_str_msg       = 'Submitting';
            $_num_maxId     = $_value['attach_id'];
        }

        $_arr_return = array(
            'count'     => $_arr_pageRow['total'],
            'msg'       => $this->obj_lang->get($_str_msg),
            'status'    => $_str_status,
            'max_id'    => $_num_maxId,
        );

        return $this->json($_arr_return);
    }


    function delete() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['delete']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x070304');
        }

        $_arr_inputDelete = $this->mdl_attach->inputDelete();

        if ($_arr_inputDelete['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputDelete['msg'], $_arr_inputDelete['rcode']);
        }

        $_arr_deleteResult = $this->deleteProcess($_arr_search);

        if ($_arr_deleteResult['rcode'] == 'y070104') {
            return $this->fetchJson($_arr_deleteResult['msg'], $_arr_deleteResult['rcode']);
        }

        $_arr_langReplace = array(
            'count' => $_arr_deleteResult['count'],
        );

        return $this->fetchJson($_arr_deleteResult['msg'], $_arr_deleteResult['rcode'], '', $_arr_langReplace);
    }


    function emptyRecycle() {
        $_mix_init = $this->init();

        if ($_mix_init !== true) {
            return $this->fetchJson($_mix_init['msg'], $_mix_init['rcode']);
        }

        if (!$this->isAjaxPost) {
            return $this->fetchJson('Access denied', '', 405);
        }

        if (!isset($this->groupAllow['attach']['delete']) && !$this->isSuper) { //判断权限
            return $this->fetchJson('You do not have permission', 'x070304');
        }

        $_arr_inputCommon = $this->mdl_attach->inputCommon();

        if ($_arr_inputCommon['rcode'] != 'y070201') {
            return $this->fetchJson($_arr_inputCommon['msg'], $_arr_inputCommon['rcode']);
        }

        $_arr_search = array(
            'box' => 'recycle',
        );

        $_arr_attachIds   = array();
        $_num_perPage     = 10;
        $_num_attachCount = $this->mdl_attach->count($_arr_search);
        $_arr_pageRow     = $this->obj_request->pagination($_num_attachCount, $_num_perPage, 'post');
        $_arr_attachRows  = $this->mdl_attach->lists(1000, 0, $_arr_search);

        if ($_num_attachCount > 0) {
            foreach ($_arr_attachRows as $_key=>$_value) {
                $_arr_attachIds[] = $_value['attach_id'];
            }

            $_arr_search = array(
                'box'        => 'recycle',
                'attach_ids' => $_arr_attachIds,
            ); //搜索设置

            $this->mdl_attach->inputDelete['attach_ids']   = $_arr_attachIds;

            $this->deleteProcess($_arr_search);

            //$_arr_deleteResult = $this->mdl_attach->delete();

            $_str_status     = 'loading';
            $_str_msg        = 'Submitting';
        } else {
            $_str_status     = 'complete';
            $_str_msg        = 'Complete';
        }

        $_arr_return = array(
            'msg'    => $this->obj_lang->get($_str_msg),
            'count'  => $_arr_pageRow['total'],
            'status' => $_str_status,
        );

        return $this->json($_arr_return);
    }


    private function uploadProcess($attachRow) {
        if ($attachRow['attach_type'] == 'image') {
            $_obj_image = Image::instance();

            //$_obj_image->quality = 99;

            if (!$_obj_image->open($attachRow['attach_path'])) {
                $_str_error = $_obj_image->getError();
                return array(
                    'msg'   => $_str_error,
                    'rcode' => 'x070402',
                );
            }

            if (!$_obj_image->batThumb($this->thumbRows)) {
                $_str_error = $_obj_image->getError();
                return array(
                    'msg'   => $_str_error,
                    'rcode' => 'x070402',
                );
            }
        }

        if ($this->ftpInit) {
            if (!$this->obj_ftp->init()) {
                $_str_error = $this->obj_ftp->getError();
                return array(
                    'msg'   => $_str_error,
                    'rcode' => 'x070410',
                );
            }

            /*print_r($attachRow['attach_path']);
            print_r(PHP_EOL);
            print_r('/' . $attachRow['attach_url_name']);*/

            if (!$this->obj_ftp->fileUpload($attachRow['attach_path'], '/' . $attachRow['attach_url_name'], false, FTP_BINARY)) {
                $_str_error = $this->obj_ftp->getError();
                return array(
                    'msg'   => $_str_error,
                    'rcode' => 'x070410',
                );
            }

            if ($attachRow['attach_type'] == 'image') {
                $_arr_thumbs = $_obj_image->getThumbs();

                foreach ($_arr_thumbs as $_key=>$_value) {
                    $_str_remoteThumb = str_ireplace(GK_PATH_ATTACH, '', $_value);

                    if (!$this->obj_ftp->fileUpload($_value, '/' . $_str_remoteThumb, false, FTP_BINARY)) {
                        return array(
                            'msg'   => 'Upload thumbnail to remote directory failed',
                            'rcode' => 'x070410',
                        );
                    }
                }
            }
        }

        return $attachRow;
    }


    private function deleteProcess($search) {
        $_obj_file         = File::instance();

        $_arr_attachRows  = $this->mdl_attach->lists(1000, 0, $search);

        foreach ($_arr_attachRows as $_key=>$_value) {
            if (isset($_value['thumbRows']) && !Func::isEmpty($_value['thumbRows'])) {
                foreach ($_value['thumbRows'] as $_key_thumb=>$_value_thumb) {
                    $_obj_file->fileDelete($_value_thumb['thumb_path']);
                }
            }

            $_obj_file->fileDelete($_value['attach_path']);
        }

        if ($this->ftpInit) {
            if (!$this->obj_ftp->init()) {
                $_str_error = $this->obj_ftp->getError();
                return array(
                    'msg'   => $_str_error,
                    'rcode' => 'x070401',
                );
            }

            foreach ($_arr_attachRows as $_key=>$_value) {
                if (isset($_value['thumbRows']) && !Func::isEmpty($_value['thumbRows'])) {
                    foreach ($_value['thumbRows'] as $_key_thumb=>$_value_thumb) {
                        $this->obj_ftp->fileDelete('/' . $_value_thumb['thumb_url_name']);
                    }
                }

                $this->obj_ftp->fileDelete('/' . $_value['attach_url_name']);
            }
        }

        if (!$this->isSuper) {
            $this->mdl_attach->inputDelete['admin_id'] = $this->adminLogged['admin_id'];
        }

        return $this->mdl_attach->delete();
    }


    protected function init($chk_admin = true) {
        $_mdl_mime          = Loader::model('Mime');

        $_arr_mimes         = array();
        $_arr_allowMimes    = array();
        $_arr_allowExts     = array();
        $_arr_mimeRows      = $_mdl_mime->lists(100);
        $_arr_thumbRows     = $this->mdl_thumb->lists(1000);

        foreach ($_arr_mimeRows as $_key=>$_value) {
            $_arr_allowExts[] = strtolower($_value['mime_ext']);
            if (is_array($_value['mime_content'])) {
                if (Func::isEmpty($_arr_allowMimes)) {
                    $_arr_allowMimes  = $_value['mime_content'];
                } else {
                    $_arr_allowMimes  = array_merge($_arr_allowMimes, $_value['mime_content']);
                }

                $_arr_mimes[strtolower($_value['mime_ext'])] = $_value['mime_content'];
            }
        }

        $this->thumbRows     = $_arr_thumbRows;
        $this->mimeRows      = $_arr_mimes;
        $this->allowExts     = Func::arrayFilter($_arr_allowExts);
        $this->allowMimes    = Func::arrayFilter($_arr_allowMimes);

        //print_r($this->allowMimes);
        $_str_limitUnit = strtolower($this->config['var_extra']['upload']['limit_unit']);
        $_num_sizeUnit  = 1;

        switch ($_str_limitUnit) { //初始化单位
            case 'tb':
                $_num_sizeUnit = GK_TB;
            break;

            case 'gb':
                $_num_sizeUnit = GK_GB;
            break;

            case 'mb':
                $_num_sizeUnit = GK_MB;
            break;

            case 'kb':
                $_num_sizeUnit = GK_KB;
            break;

            default:
                $_num_sizeUnit = 1;
            break;
        }

        $this->generalData['allow_exts']    = implode(',', $this->allowExts);
        $this->generalData['allow_mimes']   = implode(',', $this->allowMimes);
        $this->generalData['limit_size']    = $this->config['var_extra']['upload']['limit_size'] * $_num_sizeUnit;

        return parent::init();
    }
}
