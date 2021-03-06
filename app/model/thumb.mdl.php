<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\model;

use app\classes\Model;
use ginkgo\Func;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access Denied');

/*-------------缩略图模型-------------*/
class Thumb extends Model {

    public $arr_type = array('ratio', 'cut');

    function check($num_thumbId = 0, $thumbWidth = 0, $thumbHeight = 0, $thumbType = '', $notId = 0) {
        if ($num_thumbId === 0 || ($thumbWidth == 100 && $thumbHeight == 100 && $thumbType == 'cut')) {
            return array(
                'thumb_id'      => 0,
                'rcode'         => 'y090102', //存在记录
            );
        }

        $_arr_thumbSelect = array(
            'thumb_id',
        );

        $_arr_where = array();

        if ($num_thumbId > 0) {
            $_arr_where[] = array('thumb_id', '=', $num_thumbId);
        }

        if ($thumbWidth > 0) {
            $_arr_where[] = array('thumb_width', '=', $thumbWidth);
        }

        if ($thumbHeight > 0) {
            $_arr_where[] = array('thumb_height', '=', $thumbHeight);
        }

        if (!Func::isEmpty($thumbType)) {
            $_arr_where[] = array('thumb_type', '=', $thumbType);
        }

        if ($notId > 0) {
            $_arr_where[] = array('thumb_id', '<>', $notId);
        }

        $_arr_thumbRow = $this->where($_arr_where)->find($_arr_thumbSelect);

        if (!$_arr_thumbRow) {
            return array(
                'msg'   => 'Thumbnail not found',
                'rcode' => 'x090102', //不存在记录
            );
        }

        $_arr_thumbRow['rcode'] = 'y090102';
        $_arr_thumbRow['msg']   = '';

        return $_arr_thumbRow;
    }


    function read($num_thumbId) {
        $_arr_thumbSelect = array(
            'thumb_id',
            'thumb_width',
            'thumb_height',
            'thumb_type',
            'thumb_quality',
        );

        $_arr_thumbRow = $this->where('thumb_id', '=', $num_thumbId)->find($_arr_thumbSelect);

        if (!$_arr_thumbRow) {
            return array(
                'msg'   => 'Thumbnail not found',
                'rcode' => 'x090102', //不存在记录
            );
        }

        $_arr_thumbRow['rcode'] = 'y090102';
        $_arr_thumbRow['msg']   = '';

        return $_arr_thumbRow;
    }


    /*============列出缩略图============
    返回多维数组
        thumb_id 缩略图 ID
        thumb_width 缩略图宽度
        thumb_height 缩略图高度
    */
    function lists($num_no, $num_except = 0) {
        $_arr_thumbSelect = array(
            'thumb_id',
            'thumb_width',
            'thumb_height',
            'thumb_type',
            'thumb_quality',
        );

        $_arr_thumbRows = $this->order('thumb_id', 'DESC')->limit($num_except, $num_no)->select($_arr_thumbSelect);

        $_arr_thumbRow = array(
            'thumb_id'       => 0,
            'thumb_width'    => 100,
            'thumb_height'   => 100,
            'thumb_type'     => 'cut',
            'thumb_quality'  => 90,
        );

        array_unshift($_arr_thumbRows, $_arr_thumbRow);

        return $_arr_thumbRows;
    }


    function count() {
        $_num_thumbCount = $this->count();

        return $_num_thumbCount;
    }
}
