<?php
use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router as Router;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Http\Response\Cookies as Cookie;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Collection\Manager as CollectionManager;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Logger\Adapter\File as FileLog;

$di = new FactoryDefault();

/**
 * Registering a router
 */
$di->set('router', $router);

//注册SESSION服务
$di->setShared("session", function () {
    $session = new Session();
    $session->start();

    return $session;
});

//注册COOKIE服务
$di->set('cookies', function () {
    $cookie = new Cookie();
    $cookie->useEncryption(false);
    return $cookie;
});

$di->set("url", function () use ($config) {
    $url = new UrlProvider();
    if (DEBUG_MODEL === 1) {
        $url->setBaseUri($config->application->baseUrl);
    } else {
        $url->setBaseUri('http://shop.qing-ju.com/');
    }

    return $url;
});


/*//注册模板路径
$di->set("view", function () use ($config){
    $view = new View();
    $view->setViewsDir("../app/views/");
    $view->registerEngines(
        [
            ".phtml" => function($view,$di) use ($config){
                $volt = new VoltEngine($view,$di);
                $volt->setOptions(
                    [
                        'compiledPath' => '../app/cache/'   // this directory EXISTS
                    ]
                );
                return $volt;
            }
        ]
    );

    return $view;
},true);*/

$di->set('profiler', function () {
    return new \Phalcon\Db\Profiler();
}, true);

//配置数据库
$di->setShared('db', function () use ($config) {

    //新建一个事件管理器
    $eventsManager = new \Phalcon\Events\Manager();

    $config = $config->get('database')->toArray();
    $connection = new PdoMysql($config);

//    $logger = new FileLog('db.log');
//    $eventsManager->attach('db', function ($event, $connection) use ($logger) {
//        if ($event->getType() == 'beforeQuery') {
//            $logger->info($connection->getSQLStatement());
//        }
//    });

    //将事件管理器绑定到db实例中
//    $connection->setEventsManager($eventsManager);

    return $connection;
});


//配置数据库
$di->set('mysql', function () use ($config) {
    return MysqlDatabase::getIns('database', $config);
}, true);


//注册配置元数据
$di->set('modelsMetadata', function () {
    return new MetaData();
});

$di->set('collectionManager', function () {
    $eventsManager = new EventsManager();
    $modelsManager = new CollectionManager();
    $modelsManager->setEventsManager($eventsManager);
    return $modelsManager;
}, true);

//注册错误代码类
$di->set('errmsg', function () {
    return new GetErrMsg();
});

/*$di->set('memcache',function() use ($config){
    $config = $config->get('memcache_server')->toArray();
    $frontCache = new Phalcon\Cache\Frontend\Data(array(
        "lifetime" => 86400
    ));

    //Create the Cache setting memcached connection options
    $cache = new Phalcon\Cache\Backend\Memcache($frontCache, $config);

    return $cache;
});*/

$di->setShared('memcache', function () {
    $mem = XMemCache::getInstance();
    return $mem;
});

$di->setShared('zRedis', function () {
    $redis = ZRedis::getIns();
    return $redis;
});

//注册配置文件
$di->setShared('config', $config);

//注册常用类
$di->setShared('common', function () {
    return new Common();
});

//注册常用工具类
$di->set('util', function () {
    return new LibUtil();
});

//事务
$di->setShared("transactions", function () {
    return new TransactionManager();
}
);

$di->setShared('flashSession', function () {
    return new FlashSession([
        'error' => 'alert alert-danger fade in',
        'success' => 'alert alert-success fade in',
        'notice' => 'alert alert-info fade in',
        'warning' => 'alert alert-warning fade in',
    ]);
});

$di->setShared('importantCommon', function () {
    return new ImportantCommon();
});


$di->set('exportExcel',function(){
    return new ExportExcel();
});

$di->setShared('cloudMessage', function () {
    return new CloudMessage();
});

$di->set('aliMessage', function () {
    return new AliMessage();
});

$di->set('ossUpload',function(){
    return new OssUpload();
});