<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午4:31
 */

namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Message\Text;

class GroupHandler extends Main
{
    /**
     * 优先检测群组的名称定为来源
     * 监听群组的人员变更，开启了新人加入消息提醒，同时提醒内容不为空那么就会触发自动回复欢迎新人的消息
     * 同时监听文字消息的，默认开启@我，搜人、踢人等等命令系统会自动做出指定的操作
     * @param $message
     * @param $data
     */
    public function handler($message, $data)
    {
        $groupData = $data->getGroupByNickname($message['from']['NickName']);
        if ($groupData) {
            /** 新人加入群组，自动提示信息 */
            if ($groupData['is_welcome_msg'] && $groupData['welcome_msg'] && $message['type'] === 'group_change' && $message['action'] === 'ADD') {
                Text::send($message['from']['UserName'], str_replace("{nickname}", $message['invited'], $groupData['welcome_msg']));
                return;
            }
            if ($message['type'] === 'text') {
                /** 开启@我，但是用户信息并未@我直接return */
                if ($groupData['is_at'] && empty($message['isAt'])) {
                    return;
                }
                $myselfData = json_decode(json_encode(vbot('myself')), true);
                $message['content'] = trim(str_replace([$myselfData['nickname'], ' ', ' '], ['', '', ''], $message['content']));

                /** 是否开启搜索人员 */
                if ($groupData['is_search']) {
                    $status = $this->search($message, $groupData['is_like'] ? true : false);
                    if ($status) return;
                }

                /** 是否开启踢人 */
                if ($groupData['is_kick']) {
                    $status = $this->kick($message, $groupData['is_like'] ? true : false);
                    if ($status) return;
                }

                /** 消息触发命令，自动回复 */
                $message['content'] = trim(str_replace(['@ ', ' ', '@'], ['', '', ''], $message['content']));
                $reply = $data->getGroupReplyByCommand($groupData['command'], $message['content']);
                if ($reply) {
                    $this->replay($reply['reply'], $reply['type']);
                }
            }
        }
    }

    /**
     * 通过用户昵称 踢出用户
     * @param $message
     * @param bool $isLikeSearch
     * @return bool
     */
    private function kick($message, $isLikeSearch = true)
    {
        if (str_contains($message['content'], '踢人')) {
            $groups = vbot('groups');
            $strArray = array('@ 踢人', " ", "　", "\n", "\r", "\t");
            $replace = array("", "", "", "", "", "");

            $nickname = str_replace($strArray, $replace, $message['content']);
            $nickname = str_replace('@踢人', '', $nickname);
            $members = $groups->getMembersByNickname($message['from']['UserName'], $nickname, $isLikeSearch);
            $member = [];
            foreach ($members as $value) {
                $member = $value;
                break;
            }
            vbot('console')->log($nickname);
            if (!$member) {
                Text::send($message['from']['UserName'], "未搜索到 " . $nickname);
                return false;
            }
            $groups->deleteMember($message['from']['UserName'], $member['UserName']);
            Text::send($message['from']['UserName'], "已踢除：" . $member['NickName']);
            return true;
        }
        return false;
    }

    /**
     * 搜索包含对应昵称的列表
     * @param $message
     * @param bool $isLikeSearch
     * @return bool
     */
    private function search($message, $isLikeSearch = true)
    {
        if (str_contains($message['content'], '搜人')) {
            $groups = vbot('groups');
            $nickname = str_replace('@ 搜人', '', $message['content']);
            $nickname = str_replace('@搜人', '', $nickname);
            //$nickname = $this->filter($nickname);
            $members = $groups->getMembersByNickname($message['from']['UserName'], $nickname, $isLikeSearch);
            $result = '搜索结果 数量：' . count($members) . "\n";
            foreach ($members as $member) {
                $result .= '@' . $member['NickName'] . "\n";
            }
            vbot('console')->log($nickname);
            Text::send($message['from']['UserName'], $result);
            return true;
        }
        return false;
    }
}