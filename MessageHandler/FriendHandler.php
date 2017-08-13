<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午4:31
 */

namespace WechatRobot\MessageHandler;

class FriendHandler extends Main
{
    /**
     * 私人消息自动匹配对应的用户的指令库
     * 如果指令匹配直接返回指定指令的回复操作
     * @param $message
     * @param $data
     */
    public function handler($message,$data)
    {
        $reply = $data->getFriendReplyByCommand($message['content']);
        if($reply){
            $this->replay($reply['reply'],$reply['type']);
        }
    }

}