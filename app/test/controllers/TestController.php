<?php
/**
 * User: suji.zhao
 * Email: zhaosuji@foxmail.com
 * Date: 2017/10/19 10:46
 */
namespace App\Test\Controllers;


use BaseController;

class TestController extends BaseController
{
    public function onConstruct()
    {
        parent::onConstruct(); // TODO: Change the autogenerated stub
    }

    public function IndexAction(){
        echo 22222;
    }
}