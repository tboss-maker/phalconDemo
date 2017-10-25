<?php
namespace App\Test;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

class Module implements ModuleDefinitionInterface
{
    /**
 * 注册自定义加载器
 */
    public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = NULL)
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            array(
                'App\Test\Controllers' => '../app/test/controllers/',
                'App\Test\Models'      => '../app/test/models/'
            )
        );

        $loader->register();
    }

    /**
     * 注册自定义服务
     */
    public function registerServices(\Phalcon\DiInterface $di )
    {

        // Registering a dispatcher
        $di->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("App\\Test\\Controllers");
            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function () {
            $view = new View();

            $view->setViewsDir('../app/shop/views/');
            $view->registerEngines(
                [
                    ".phtml" => function($view,$di){
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
        });
    }
}
