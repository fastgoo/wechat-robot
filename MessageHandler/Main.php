<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/11
 * Time: 上午10:38
 */

namespace WechatRobot\MessageHandler;

use Hanson\Vbot\Message\Card;
use Hanson\Vbot\Message\Emoticon;
use Hanson\Vbot\Message\File;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Message\Text;
use Hanson\Vbot\Message\Video;
use Hanson\Vbot\Message\Voice;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use WechatRobot\Service\AdminData;

class Main
{

    static $message = null;

    static public function messageHandler($message, $app_id = 123456)
    {

        $adminData = new AdminData($app_id);
        self::$message = $message;
        vbot('console')->log($message['fromType']);
        switch ($message['fromType']){
            case 'Group':
                (new GroupHandler())->handler($message,$adminData);
                break;
            case 'Friend':
                (new FriendHandler())->handler($message,$adminData);
                break;
            case 'Special':
                (new RequestHandler)->handler($message,$adminData);
                break;
        }

        return;

        /**
         * 收到文本消息 @自己
         */
        if (!empty($message['isAt'])) {
            //MessageAt::handler($message,$data);
        }

        /**
         * 收到语言消息
         */
        if ($message['type'] === 'voice') {
            MessageVoice::handler($message, $data);
        }

        /**
         * 收到视频消息
         */
        if ($message['type'] === 'video') {
            MessageVideo::handler($message, $data);
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
            MessageFile::handler($message, $data);
        }

        /**
         * 收到定位消息
         */
        if ($message['type'] === 'location') {
            MessageLocation::handler($message, $data);
        }

        /**
         * 收到撤回消息
         */
        if ($message['type'] === 'recall') {
            MessageRecall::handler($message, $data);
        }

        /**
         * 收到红包消息
         */
        if ($message['type'] === 'red_packet') {
            MessageRedPacket::handler($message, $data);
        }

        /**
         * 收到分享消息
         */
        if ($message['type'] === 'share') {
            MessageShare::handler($message, $data);
        }

        /**
         * 收到转账消息
         */
        if ($message['type'] === 'transfer') {
            MessageTransfer::handler($message, $data);
        }

        /**
         * 收到公众号消息
         */
        if ($message['type'] === 'official') {
            vbot('console')->log('收到公众号消息:' . $message['title'] . $message['description'] .
                $message['app'] . $message['url']);
        }

        /**
         * 好友申请
         */
        if ($message['type'] === 'request_friend') {
            MessageRequest::handler($message, $data);
        }

        /**
         * 添加新好友
         */
        if ($message['type'] === 'new_friend') {
            MessageFriend::handler($message, $data);
        }

        /**
         * 收到群组人员变动消息
         */
        if ($message['type'] === 'group_change') {
            MessageGroup::handler($message, $data);
        }

        /**
         * 收到小程序消息
         */
        if ($message['type'] === 'mina') {
            MessageMina::handler($message, $data);
        }
    }

    /**
     * 根据类型回复对应的数据
     * 回复信息存在json对象里面，需要解析取出指定对象输出
     */
    public function replay($reply,$type)
    {
        $data = json_decode($reply,true);
        switch ($type){
            case 1:
                Text::send(self::$message['from']['UserName'], $data['text']);
                break;
            case 2:
                Voice::send(self::$message['from']['UserName'], $data['url']);
                break;
            case 3:
                Image::send(self::$message['from']['UserName'], $data['url']);
                break;
            case 4:
                Video::send(self::$message['from']['UserName'], $data['url']);
                break;
            case 5:
                Emoticon::send(self::$message['from']['UserName'], $data['url']);
                //Emoticon::sendRandom($message['from']['UserName']);
                break;
            case 6:
                File::send(self::$message['from']['UserName'], $data['url']);
                break;
            case 7:
                Text::send(self::$message['from']['UserName'], '收到定位地址信息');
                break;
            case 8:
                Card::send(self::$message['from']['UserName'], $data['alias'], $data['nickname']);
                break;
        }
    }

    public function logger($log)
    {
        $logger = new Logger('my_logger');
        $logger->pushHandler(new StreamHandler(APPPATH.'/Log/wechat-robot'.date('Ymd').'.log', Logger::DEBUG));
        $logger->pushHandler(new FirePHPHandler());
        $logger->addInfo($log);

    }
}