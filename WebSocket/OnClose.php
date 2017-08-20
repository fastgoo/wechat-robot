<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午4:31
 */

namespace WechatRobot\WebSocket;

class OnClose
{
    private $ws;
    private $db;
    public function __construct($ws, $db)
    {
        $this->ws = $ws;
        $this->db = $db;
    }


    public function handler($request)
    {
    }
}
