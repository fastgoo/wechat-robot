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
    public function handler($message,$data)
    {
        $reply = $data->getFriendReplyByCommand($message['content']);
        vbot('console')->log(json_encode($reply));
        if($reply){
            $this->replay($reply['reply'],$reply['type']);
        }
    }

}