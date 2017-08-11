<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午10:38
 */
namespace WechatRobot\MessageHandler;

class Main
{

    static public function messageHandler($message)
    {

        /**
         * 收到文本消息 @自己
         */
        if(!empty($message['isAt'])){
            MessageAt::handler($message);
        }

        /**
         * 收到文本消息
         */
        if(!empty($message['pure'])){
            MessagePure::handler($message);
            MessageGroup::handler($message);
        }

        /**
         * 收到语言消息
         */
        if ($message['type'] === 'voice') {
            MessageVoice::handler($message);
        }

        /**
         * 收到视频消息
         */
        if ($message['type'] === 'video') {
            MessageVideo::handler($message);
        }

        /**
         * 收到表情图片
         */
        if ($message['type'] === 'emoticon' && random_int(0, 1)) {
            //Emoticon::sendRandom($message['from']['UserName']);
        }

        /**
         * 收到文件消息
         */
        if ($message['type'] === 'file') {
            MessageFile::handler($message);
        }

        /**
         * 收到定位消息
         */
        if ($message['type'] === 'location') {
            MessageLocation::handler($message);
        }

        /**
         * 收到撤回消息
         */
        if ($message['type'] === 'recall') {
            MessageRecall::handler($message);
        }

        /**
         * 收到红包消息
         */
        if ($message['type'] === 'red_packet') {
            MessageRedPacket::handler($message);
        }

        /**
         * 收到分享消息
         */
        if ($message['type'] === 'share') {
            MessageShare::handler($message);
        }

        /**
         * 收到转账消息
         */
        if ($message['type'] === 'transfer') {
            MessageTransfer::handler($message);
        }

        /**
         * 收到公众号消息
         */
        if ($message['type'] === 'official') {
            vbot('console')->log('收到公众号消息:'.$message['title'].$message['description'].
                $message['app'].$message['url']);
        }

        /**
         * 好友申请
         */
        if ($message['type'] === 'request_friend') {
            MessageRequest::handler($message);
        }

        /**
         * 添加新好友
         */
        if ($message['type'] === 'new_friend') {
            MessageFriend::handler($message);
        }

        /**
         * 收到群组人员变动消息
         */
        if ($message['type'] === 'group_change') {
            MessageGroup::handler($message);
        }

        /**
         * 收到小程序消息
         */
        if ($message['type'] === 'mina') {
            MessageMina::handler($message);
        }
    }

}