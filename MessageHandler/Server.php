<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午10:09
 */
namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Foundation\Vbot;
use Illuminate\Support\Collection;
use WechatRobot\MessageHandler\Main;

class Server
{
    private $server;
    private $key;
    private $loginSuccess;
    public function __construct($config, $appId = '123456')
    {
        $this->server = new Vbot($config);
        $this->key = $appId;
    }

    public function start()
    {
        $this->server->messageHandler->setHandler(function (Collection $message) {
            Main::messageHandler($message, $this->key);
        });

        // 获取监听器实例
        $observer = $this->server->observer;

        /**
         * 获取二维码扫码监听器
         */
        $observer->setQrCodeObserver(function ($qrCodeUrl) {
            $qrCodeUrl = str_replace('https://login.weixin.qq.com/l', 'https://login.weixin.qq.com/qrcode', $qrCodeUrl);
            //(new Main())->logger($qrCodeUrl);
            // $nowTime = time();
            // global $pdo;
            // if ($pdo -> exec("insert into robot_qrcode_list(app_id,qrcode,create_time) values('{$this->key}','{$qrCodeUrl}',$nowTime)")) {
            //     echo "插入成功！";
            //     echo $pdo -> lastinsertid();
            // }
            echo $qrCodeUrl;
        });

        /**
         * 登录成功监听器
         */
        $observer->setLoginSuccessObserver(function () {
            $call = $this->loginSuccess;
            $call();
        });

        /**
         * 程序退出监听器
         */
        $observer->setExitObserver(function () {
            echo '程序退出监听器';
        });

        /**
         * 程序异常监听器
         * 当程序判断手机端太久没打开微信时触发，则需要打开手机微信，不然系统就会断开
         */
        $observer->setNeedActivateObserver(function () {
        });

        /**
         * 消息处理前监听器
         */
        $observer->setBeforeMessageObserver(function () {
        });

        $this->server->server->serve();
    }

    public function setLoginSuccessCallback($callback)
    {
        $this->loginSuccess = $callback;
    }
}
