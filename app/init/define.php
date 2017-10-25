<?php
//时区定义
ini_set('date.timezone', 'Asia/Shanghai');
ini_set('date.default_latitude', 31.5167);
ini_set('date.default_longitude', 121.4500);

//定义COOKIE
define("COOKIE_DOMAIN", 'qj.com');
// ini_set('session.cookie_domain', COOKIE_DOMAIN);
define('BASE_URL', '');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath('..') . DS);
define('LOG_PATH',ROOT_PATH.DS.'log');


//app
define('DEBUG_MODEL','dev');
define('HOT_LINE','400-1136-777');
define('COMPANY_SLOGAN','测试环境...');
define('PARAM_ERROR_PROMPT','请给我正确的参数,OK?');//参数错误统一提示
define('SERVER_ERROR_PROMPT','服务器小哥开小差了,告诉她媳妇去:'.HOT_LINE);
define('APPLICATION_PATH',ROOT_PATH.'app'.DS);
define('APPLICATION_PUBLIC',ROOT_PATH.'public'.DS);
define('CONFIG_PATH',APPLICATION_PATH.'config'.DS);
define('KEY_PATH',CONFIG_PATH.'key'.DS);
define('INIT_PATH',APPLICATION_PATH.'init'.DS);
define('LOGS_DIR_PATH',APPLICATION_PATH.'logs'.DS);
define('ERRLOG_PATH',LOGS_DIR_PATH.'error'.DS);
define('PUBLIC_PATH',ROOT_PATH.'public'.DS);

define('UPLOAD_PATH',ROOT_PATH.'upload'.DS);
define('UPLOAD_IMG',UPLOAD_PATH.'img'.DS);
define('UPLOAD_IMG_TEMP',UPLOAD_PATH.'img_temp'.DS);
