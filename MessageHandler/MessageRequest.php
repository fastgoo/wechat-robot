<?php
/**
 * Created by PhpStorm.
 * 描述：处理好友申请的钩子
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午11:08
 */
namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Contact\Friends;
use Illuminate\Support\Collection;


class MessageRequest
{
    static public function handler($message)
    {
        /** @var Friends $friends */
        $friends = vbot('friends');

        vbot('console')->log('收到好友申请:'.$message['info']['Content'].$message['avatar']);
        if (in_array($message['info']['Content'], ['echo', 'print_r', 'var_dump', 'print'])) {
            $friends->approve($message);
        }
    }


}