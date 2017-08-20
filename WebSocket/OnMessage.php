<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/12
 * Time: 下午4:31
 */

namespace WechatRobot\WebSocket;

use swoole\websocket\server;
use Symfony\Component\Cache\Simple\FilesystemCache;

class OnMessage
{
    private $ws;
    private $db;
    public $server;
    protected $fd = null;
    protected $userData;
    protected $cache;
    protected static $ins = null;

    public static function getInstance()
    {
        if (self::$ins instanceof self) {
            return self::$ins;
        }
        self::$ins = new self();
        return self::$ins;
    }

    public function init($ws, $db)
    {
        $this->ws = $ws;
        $this->db = $db;
        $this->cache = new FilesystemCache();
        return self::$ins;
    }

    /**
     * [handler websocket收到消息处理]
     * @return [type] [description]
     */
    public function handler($request)
    {
        $this->fd = $request->fd;
        $data = json_decode($request->data, true);
        //print_r($data);
        $userData = $this->checkToken($data['head']['token']);
        if (!$userData) {
            $this->pushMessage(-401, '用户token信息已失效，请重新登录');
            return;
        } else {
            $this->userData = $userData;
        }
        switch ($data['body']['type']) {
          case 'StartServer':
            $this->setServer();
            break;

          default:
            # code...
            break;
        }
    }

    /**
     * [setServer 设置机器人进程服务]
     * @param [type] $requestFd [description]
     */
    private function setServer()
    {
        $this->verifyServerAndClearProcess();
        /** 开启守护进程 */
        \Swoole\Process::daemon(true, true);
        $self = $this;
        /** 实例化已经进程服务，用于启动监听机器人 */
        $process = new \Swoole\Process(function () use ($self) {
            global $config;
            $server = new \WechatRobot\MessageHandler\Server($config, $self->userData->app_id);
            $server->setLoginSuccessCallback(function () use ($self) {
                $self->pushMessage('loginSuccess', '机器人登录成功');
            });
            $server->start();
        }, true);
        swoole_set_process_name('wechat-robot-'.$this->userData->app_id);
        $pid = $process->start();

        $this->setServerProcess($pid, $process);
        /** 监听异步管理返回的消息记录 */
        swoole_event_add($process->pipe, function ($pipe) use ($process,$self) {
            $msg = $process->read();
            if (filter_var($msg, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                $self->pushMessage('getQrcode', '获取到二维码数据', ['url'=>$msg]);
            }
            echo "RECV: " . $msg.PHP_EOL;
        });
    }

    /**
     * [verifyServerAndClearProcess 如果设置过进程信息清除进程回收资源]
     * @return [type] [description]
     */
    private function verifyServerAndClearProcess()
    {
        $pid = $this->cache->get("process.pid.{$this->userData->app_id}");
        if (!empty($pid)) {
            $flag = \Swoole\Process::kill($pid);
            var_dump("清除进程-{$pid},状态：{$flag}");
            $this->cache->delete("process.pid.{$this->userData->app_id}");
        }
    }

    /**
     * [setServerProcess 设置进程服务]
     * @param [type] $pid     [进程ID]
     * @param [type] $process [进程句柄]
     */
    private function setServerProcess($pid, $process)
    {
        $this->cache->set("process.pid.{$this->userData->app_id}", $pid);
    }

    /**
     * [checkToken 验证客户端发送过来的token是否有效]
     * @param  [type] $token [token字符串]
     * @return [type]        [正确返回对应数据，错误返回false]
     */
    private function checkToken($token)
    {
        $client = new \GuzzleHttp\Client(['headers' => ['Authorization' => $token],'base_uri'=>'https://admin.fastgoo.net']);
        $response = $client->request('POST', '/admin.api/admin_base/authCheck', []);
        $res = json_decode($response->getBody()->getContents());
        if ($res && $res->code == 1) {
            return $res->data;
        } else {
            return false;
        }
    }

    /**
     * [pushMessage 发送消息]
     * @param  integer $code [状态码]
     * @param  string  $msg  [描述消息]
     * @param  array   $data [数据]
     * @return [type]        [description]
     */
    public function pushMessage($code=1, $msg='', $data = array())
    {
        $resutl = [
          'code'=>$code,
          'msg'=>$msg,
          'data'=>$data
        ];
        $this->ws->push($this->fd, json_encode($resutl));
    }
}
