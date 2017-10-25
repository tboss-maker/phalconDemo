<?php
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Config;

header("Content-type: text/html; charset=utf-8");
//header("Access-Control-Allow-Origin: *");
try {
    date_default_timezone_set('PRC');
    //引入常量定义文件
//    require_once realpath('..').'/app/init/define.php';
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'init' . DIRECTORY_SEPARATOR . 'define.php';

    //读取配置文件  prod 正式环境 test测试环境 dev开发环境
    $config = array();
    switch (DEBUG_MODEL) {
        case 'prod':
            $config = new ConfigIni(CONFIG_PATH . 'config.ini');
            break;
        case 'test':
            $config = new ConfigIni(CONFIG_PATH . 'config_test.ini');
            break;
        case 'dev':
            $config = new ConfigIni(CONFIG_PATH . 'config_dev.ini');
            break;
        default:
            break;
    }

    if (is_readable(CONFIG_PATH . 'config_extend.ini')) {
        $override = new ConfigIni(CONFIG_PATH . 'config_extend.ini');
        $config->merge($override);
    }

    //引入初始化文件
    require_once INIT_PATH . 'init.php';

    $application = new Application($di);

    //注册模块
    $application->registerModules(array(
        'Shop' => array(
            'className' => 'App\Shop\Module',
            'path' => ROOT_PATH . 'app/shop/Module.php',
        ),
        'Test' => array(
            'className' => 'App\Test\Module',
            'path' => ROOT_PATH . 'app/test/Module.php',
        )
    ));

    $response = $application->handle();
    $response->send();

} catch (Exception $e) {
    echo "Exception: ", $e->getMessage();
}