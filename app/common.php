<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿编辑
-----------------------------------------------------------------*/

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access denied');

define('PRD_CMS_NAME', 'baigo CMS');
define('PRD_CMS_URL', 'http://www.baigo.net/cms/');
define('PRD_CMS_VER', '3.0-beta-1');
define('PRD_CMS_PUB', 20200514);
define('PRD_CMS_HELP', 'http://doc.baigo.net/cms/');
define('PRD_VER_CHECK', 'http://www.baigo.net/ver_check/check.php');

defined('BG_TPL_INDEX') or define('BG_TPL_INDEX', GK_APP_TPL . 'index' . DS); //前台模板
defined('BG_TPL_CALL') or define('BG_TPL_CALL', GK_APP_TPL . 'call' . DS); //调用模板
defined('BG_PATH_CONFIG') or define('BG_PATH_CONFIG', GK_PATH_APP . GK_NAME_CONFIG . DS); //配置文件

//error_reporting(E_ALL);
