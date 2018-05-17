<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if (!defined('IN_BAIGO')) {
    exit('Access Denied');
}

/*-------------------------通用-------------------------*/
return array(

    /*------页面标题------*/
    'page' => array(
        'login' => '管理后台登录',
    ),

    'label' => array(
        'username'      => '用户名', //用户名
        'password'      => '密码', //密码
        'captcha'       => '验证码', //验证码
        'submitting'    => '正在登录 ...',
        'remenber'      => '记住登录状态',
        'remenberNote'  => '请勿在公共场合使用此选项',
    ),

    'href' => array(
        'help'      => '帮助',
        'login'     => '直接登录',
        'forgot'    => '忘记密码',
        'jumping'   => '正在跳转',
        'forward'   => '跳转',
    ),

    'btn' => array(
        'login'     => '登录', //登录
        'captcha'   => '看不清', //验证码
    ),

    'text' => array(
        'notForward'    => '如果长时间没有跳转，请点“跳转”按钮跳转！',
    ),
);
