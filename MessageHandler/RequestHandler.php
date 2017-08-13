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
    public function handler($message,$data)
    {

        $friends = vbot('friends');
        $friendsReply = $data->getFriendRequestToUserByMark($message['info']['Content']);
        print_r($friendsReply);
        if($friendsReply){
            $friends->approve($message);
            if(!empty($friendsReply['welcome_msg'])){
                Text::send($message['info']['UserName'], $friendsReply['welcome_msg']);
            }
        }

        $groupsReply = $data->getFriendRequestToGroupByMark($message['info']['Content']);
        print_r($groupsReply);
        if($groupsReply){
            $friends->approve($message);
            if(!empty($groupsReply['new_friend_msg'])){
                Text::send($message['info']['UserName'], $groupsReply['new_friend_msg']);
            }
            $groups = vbot('groups');
            $groups->addMember($groups->getUsernameByNickname($groupsReply['group_name']), $message['info']['UserName']);
        }
    }

}