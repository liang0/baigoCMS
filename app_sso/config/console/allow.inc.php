<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

/*-------------------------权限-------------------------*/
return array(
    /*------用户------*/
    'user' => array(
        'title' => 'Users',
        'allow' => array(
            'reg'       => 'Register',
            'edit'      => 'Edit',
            'delete'    => 'Delete',
            'global'    => 'Operate all users',
        ),
    ),
);
