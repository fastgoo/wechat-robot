<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午10:09
 */
namespace WechatRobot;

require __DIR__.'/vendor/autoload.php';

use Hanson\Vbot\Foundation\Vbot;
use Illuminate\Support\Collection;
use WechatRobot\MessageHandler\Main;

class Server
{
    private $server;

    public function __construct()
    {
        $config = require_once "./config.php";
        $this->server = new Vbot($config);
    }

    public function start()
    {
        $this->server->messageHandler->setHandler(function (Collection $message){
            Main::messageHandler($message);
        });

        // 获取监听器实例
        $observer = $this->server->observer;

        /**
         * 获取二维码扫码监听器
         */
        $observer->setQrCodeObserver(function($qrCodeUrl){
            echo $qrCodeUrl;
        });

        /**
         * 登录成功监听器
         */
        $observer->setLoginSuccessObserver(function(){
            echo "登录成功了";
        });

        /**
         * 程序退出监听器
         */
        $observer->setExitObserver(function(){

        });

        /**
         * 程序异常监听器
         * 当程序判断手机端太久没打开微信时触发，则需要打开手机微信，不然系统就会断开
         */
        $observer->setNeedActivateObserver(function(){

        });

        /**
         * 消息处理前监听器
         */
        $observer->setBeforeMessageObserver(function(){
            echo "收到新消息";
        });

        $this->server->server->serve();
    }

}

$vbot = new Server();

$vbot->start();