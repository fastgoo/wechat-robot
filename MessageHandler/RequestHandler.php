<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午4:31
 */

namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Message\Text;

class RequestHandler extends Main
{
    /**
     * 通过用户好友申请备注信息去匹配数据库的信息
     * 优先匹配好友的授权命令，如果匹配正确那么直接通过申请同时设置过欢迎消息还会回复欢迎消息
     * 其次会匹配群组的授权命令，如果群组匹配正确则直接通过好友申请，设置过欢迎消息还会推送消息，而且会把用户自动拉入到群组名称对应的群里面
     * @param $message
     * @param $data
     */
    public function handler($message,$data)
    {

        $friends = vbot('friends');
        $friendsReply = $data->getFriendRequestToUserByMark($message['info']['Content']);
        if($friendsReply){
            $friends->approve($message);
            if(!empty($friendsReply['welcome_msg'])){
                Text::send($message['info']['UserName'], $friendsReply['welcome_msg']);
            }
            return;
        }

        $groupsReply = $data->getFriendRequestToGroupByMark($message['info']['Content']);
        if($groupsReply){
            $friends->approve($message);
            if(!empty($groupsReply['new_friend_msg'])){
                Text::send($message['info']['UserName'], $groupsReply['new_friend_msg']);
            }
            $groups = vbot('groups');
            $groups->addMember($groups->getUsernameByNickname($groupsReply['group_name']), $message['info']['UserName']);
            return;
        }
    }

}