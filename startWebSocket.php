<?php

date_default_timezone_set('Asia/Shanghai');

require __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);

$ws->set(array(
    'worker_num' => 4,
    //'daemonize' => true,
    'backlog' => 128,
));

/** 数据库类 */
$pdo = new PDO("mysql:host={$config['database']['mysql']['host']};dbname={$config['database']['mysql']['dbname']}", "{$config['database']['mysql']['username']}", "{$config['database']['mysql']['password']}");

/** 实例化处理类，减少实例化的开销 */
$connect = new \WechatRobot\WebSocket\OnConnect($ws, $pdo);
$message = \WechatRobot\WebSocket\OnMessage::getInstance()->init($ws, $pdo);
$close = new \WechatRobot\WebSocket\OnClose($ws, $pdo);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) use ($connect) {
    $connect->handler($request);
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use ($message) {
    $message->handler($frame);
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($close) {
    $close->handler($fd);
});

$ws->start();
