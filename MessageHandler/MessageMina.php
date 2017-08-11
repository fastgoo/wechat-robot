<?php
/**
 * Created by PhpStorm.
 * 描述：当有一个新好友的时候触发该钩子事件
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午11:08
 */
namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;


class MessageMina
{
    static public function handler($message)
    {
        $msg = '收到小程序消息';
        Text::send($message['from']['UserName'], $msg);
    }


}