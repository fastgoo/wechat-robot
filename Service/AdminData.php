<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午1:21
 */

namespace WechatRobot\Service;

class AdminData
{

    private $admin;

    public function __construct($app_id = 123456)
    {
        $redisCache = new \Predis\Client();
        $data = $redisCache->get($app_id);
        $data = '{"group":[{"id":"2","app_id":"123456","group_name":"\u6d4b\u8bd5Robot","is_at":"1","is_like":"1","is_search":"1","is_kick":"1","is_welcome_msg":"1","welcome_msg":"\u6b22\u8fce\u65b0\u4eba{nickname} \u52a0\u5165\u6d4b\u8bd5Robot","is_auto_add":"1","auth_key":"\u6d4b\u8bd5Robot","new_friend_msg":"\u8fdb\u5165\u6d4b\u8bd5Robot\u7fa4\u5427","status":"1","create_time":"0","update_time":"0","command":[]},{"id":"1","app_id":"123456","group_name":"Robot","is_at":"1","is_like":"1","is_search":"1","is_kick":"1","is_welcome_msg":"1","welcome_msg":"\u6b22\u8fce\u65b0\u4eba{nickname}\uff0c\u52a0\u5165\u6211\u4eecRobot\u5927\u5bb6\u5ead","is_auto_add":"1","auth_key":"123","new_friend_msg":"\u6765\u5427\uff0c\u8fdb\u7fa4\u5427","status":"1","create_time":"0","update_time":"0","command":[{"type":"1","command":"\u6d4b\u8bd52","reply":"{\"text\":\"2-666\"}"},{"type":"1","command":"\u6d4b\u8bd51","reply":"{\"text\":\"1-666\"}"}]}],"friend":[{"auth_key":"3","welcome_msg":"\u6b22\u8fce\u65b0\u4eba3"},{"auth_key":"2","welcome_msg":"\u6b22\u8fce\u65b0\u4eba2"},{"auth_key":"1","welcome_msg":"\u6b22\u8fce\u65b0\u4eba1"}],"commands":[{"type":"1","command":"\u6d4b\u8bd53","reply":"{\"text\":\"3-666\"}"},{"type":"1","command":"\u6d4b\u8bd52","reply":"{\"text\":\"2-666\"}"},{"type":"1","command":"\u6d4b\u8bd5","reply":"{\"text\":\"1-666\"}"}]}';
        //print_r($data);
        $this->admin = json_decode($data, true);

    }

    /**
     * 通过群组的名称获取到群组的配置信息
     * 消息来源筛选匹配到对应的群组名称，返回指定群组所有配置信息
     * @param $nickname
     * @return array
     */
    public function getGroupByNickname($nickname)
    {
        $matchKey = null;
        if (!empty($this->admin['group']) && is_array($this->admin['group'])) {
            foreach ($this->admin['group'] as $key => $value) {
                if ($nickname === $value['group_name']) {
                    $matchKey = $key;
                    break;
                }
            }
        }
        return !is_null($matchKey) && !empty($this->admin['group'][$matchKey]) ? $this->admin['group'][$matchKey] : [];
    }

    /**
     * 好友申请匹配对应的备注信息
     * 匹配到则返回对应的群组的所有配置信息
     * @param $mark
     * @return array
     */
    public function getFriendRequestToGroupByMark($mark)
    {
        $matchKey = null;
        if (!empty($this->admin['group']) && is_array($this->admin['group'])) {
            foreach ($this->admin['group'] as $key => $value) {
                if ($value['is_auto_add'] && $mark == $value['auth_key']) {
                    $matchKey = $key;
                    break;
                }
            }
        }
        return !is_null($matchKey) && !empty($this->admin['group'][$matchKey]) ? $this->admin['group'][$matchKey] : [];
    }

    /**
     * 好友申请匹配对应的备注信息
     * 匹配到则返回对应的好友设置，只有一个自动回复信息。
     * 自动回复用于区分不同渠道加入的好友申请
     * @param $mark
     * @return array
     */
    public function getFriendRequestToUserByMark($mark)
    {
        $matchKey = null;
        if (!empty($this->admin['friend']) && is_array($this->admin['friend'])) {
            foreach ($this->admin['friend'] as $key => $value) {
                if ($mark == $value['auth_key']) {
                    $matchKey = $key;
                    break;
                }
            }
        }
        return !is_null($matchKey) && !empty($this->admin['friend'][$matchKey]) ? $this->admin['friend'][$matchKey] : [];
    }

    /**
     * 通过筛选群组命令，匹配到指定命令
     * 返回对应指定命令的数据信息，包括回复以及回复类型
     * @param $group
     * @param $command
     * @return array
     */
    public function getGroupReplyByCommand($commands, $command)
    {

        $matchKey = null;
        if (!empty($commands) && is_array($commands)) {
            foreach ($commands as $key => $value) {
                if (str_contains($command, $value['command'])) {
                    $matchKey = $key;
                    break;
                }
            }
        }
        return !is_null($matchKey) && !empty($commands[$matchKey]) ? $commands[$matchKey] : [];
    }

    /**
     * 通过筛选群组命令，匹配到指定命令
     * 返回对应指定命令的数据信息，包括回复以及回复类型
     * @param $command
     * @return array
     */
    public function getFriendReplyByCommand($command)
    {
        $matchKey = null;
        if (!empty($this->admin['commands']) && is_array($this->admin['commands'])) {
            foreach ($this->admin['commands'] as $key => $value) {
                if (str_contains($command, $value['command'])) {
                    $matchKey = $key;
                    break;
                }
            }
        }
        return !is_null($matchKey) && !empty($this->admin['commands'][$matchKey]) ? $this->admin['commands'][$matchKey] : [];
    }
}