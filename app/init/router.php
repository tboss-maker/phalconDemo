<?php
//路由重写
use Phalcon\Mvc\Router as Router;

$router = new Router(false);
$router->setDefaultModule("Shop");
$router->setUriSource(Router::URI_SOURCE_GET_URL);
$router->removeExtraSlashes(true);
//$router->add('/:controller/:action',
//    array(
//        'module' => 'Shop',
//        'controller' => 1,
//        'action' => 2,
//    ));
//
//
//$router->add(
//    '/:module/:controller/:action/:params',
//    array(
//        'module'     => 1,
//        'controller' => 2,
//        'action'     => 3,
//        'params' =>4
//    )
//);
//
//$router->handle();

if(isset($_REQUEST['_url'])){
    $urlStr = $_REQUEST['_url'];
    $baseUrlStr =  explode('?',$urlStr);
    $baseUrlArr = explode('/',trim($baseUrlStr[0],'/'));

    $paramNum = count($baseUrlArr);

    if($baseUrlArr[0] == 'Shop'){
        $moduleName = $baseUrlArr[0];
        switch($paramNum){
            case 1:
                $controllerName = 'IndexController';
                $actionName     = 'indexAction';
                break;
            case 2:
                $controllerName = ucfirst($baseUrlArr[1]).'Controller';
                $actionName     = 'indexAction';
                break;
            default :
                $controllerName = ucfirst($baseUrlArr[1]).'Controller';
                $actionName     = $baseUrlArr[2].'Action';
                break;
        }
    }else{
        die(json_encode(COMPANY_SLOGAN,JSON_UNESCAPED_UNICODE));
    }

    $controllerFile = APPLICATION_PATH.$moduleName.DS.'controllers'.DS.$controllerName.'.php';

    if(!file_exists($controllerFile)){
        die(json_encode(COMPANY_SLOGAN,JSON_UNESCAPED_UNICODE));
    }

}