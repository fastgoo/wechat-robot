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
use Hanson\Vbot\Message\Card;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;


class MessageGroup
{
    static public function handler($message)
    {
        $groups = vbot('groups');
        $friends = vbot('friends');
        //$members = vbot('members');

        if ($message['from']['NickName'] === 'Robot') {
            if ($message['type'] === 'group_change' && $message['action'] === 'ADD') {
                Text::send($message['from']['UserName'], '欢迎新人 '.$message['invited']);
            }

            if ($message['type'] === 'text') {
                if (str_contains($message['content'], '搜人')) {
                    $nickname = str_replace('搜人 ', '', $message['content']);
                    $members = $groups->getMembersByNickname($message['from']['UserName'], $nickname, true);
                    $result = '搜索结果 数量：'.count($members)."\n";
                    foreach ($members as $member) {
                        $result .= $member['NickName'].' '.$member['UserName']."\n";
                    }
                    Text::send($message['from']['UserName'], $result);
                }

                if (str_contains($message['content'], '踢人')) {
                    $nickname = str_replace('踢人 ', '', $message['content']);
                    $members = $groups->getMembersByNickname($message['from']['UserName'], $nickname, true);
                    $member = [];
                    foreach ($members as $value){
                        $member = $value;
                        break;
                    }
                    $groups->deleteMember($message['from']['UserName'], $member['UserName']);
                    Text::send($message['from']['UserName'], "已踢除：".$member['NickName']);
                }

                if(str_contains($message['content'], '获取公众号')){
                    Card::send($message['from']['UserName'], 'fastgoo_app', '快捷行软件');
                }

                if(str_contains($message['content'], '获取管理员')){
                    Card::send($message['from']['UserName'], 'huoniaojugege', '周先生');
                }

            }
        }
    }


}