<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\validate\gen;

use ginkgo\Validate;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

/*-------------管理员模型-------------*/
class Article extends Validate {

    protected $rule     = array(
        'article_id' => array(
            '>' => 0,
            'format'  => 'int',
        ),
        '__token__' => array(
            'require' => true,
            'token'   => true,
        ),
    );


    function v_init() { //构造函数

        $_arr_attrName = array(
            'article_id'       => $this->obj_lang->get('ID'),
            '__token__'        => $this->obj_lang->get('Token'),
        );

        $_arr_typeMsg = array(
            'require'   => $this->obj_lang->get('{:attr} require'),
            'gt'        => $this->obj_lang->get('{:attr} require'),
            'token'     => $this->obj_lang->get('Form token is incorrect'),
        );

        $_arr_formatMsg = array(
            'int' => $this->obj_lang->get('{:attr} must be integer'),
        );

        $this->setAttrName($_arr_attrName);
        $this->setTypeMsg($_arr_typeMsg);
        $this->setFormatMsg($_arr_formatMsg);
    }

}
