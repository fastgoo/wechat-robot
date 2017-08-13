<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatRobot;

use WechatRobot\MessageHandler\Main;

class Console
{
    private $config   = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $this->getOpt();
    }

    public function start()
    {
        //启动
        $process = new Process($this->config);
        $process->start();
    }

    /**
     * 给主进程发送信号：
     *  SIGUSR1 自定义信号，让子进程平滑退出
     *  SIGTERM 程序终止，让子进程强制退出.
     *
     * @param [type] $signal
     */
    public function stop($signal=SIGTERM)
    {
        $masterPidFile=$this->config['path'] . Process::PID_FILE;
        if (file_exists($masterPidFile)) {
            $ppid=file_get_contents($masterPidFile);
            if (empty($ppid)) {
                exit('service is not running' . PHP_EOL);
            }
            if (function_exists('posix_kill')) {
                $return=posix_kill($ppid, $signal);
                if ($return) {
                    (new Main())->logger('[pid: ' . $ppid . '] has been stopped success');
                } else {
                    (new Main())->logger('[pid: ' . $ppid . '] has been stopped fail');
                }
            } else {
                system('kill -' . $signal . $ppid);
                (new Main())->logger('[pid: ' . $ppid . '] has been stopped success');

            }
        } else {
            exit('service is not running' . PHP_EOL);
        }
    }

    public function restart()
    {
        (new Main())->logger('restarting...');
        $this->stop();
        sleep(3);
        $this->start();
    }

    public function reload()
    {
        (new Main())->logger('reload...');
        $this->stop(SIGUSR1);
        sleep(3);
        $this->start();
    }

    public function getOpt()
    {
        global $argv;
        if (empty($argv[1])) {
            $this->printHelpMessage();
            exit(1);
        }
        $opt=$argv[1];
        switch ($opt) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'help':
                $this->printHelpMessage();
                break;

            default:
                $this->printHelpMessage();
                break;
        }
    }

    public function printHelpMessage()
    {
        $msg=<<<'EOF'
NAME
      run.php - manage swoole-bot

SYNOPSIS
      run.php command [options]
          Manage swoole-bot daemons.


WORKFLOWS


      help [command]
      Show this help, or workflow help for command.


      restart
      Stop, then start the standard daemon loadout.

      start
      Start the standard configured collection of Phabricator daemons. This
      is appropriate for most installs. Use phd launch to customize which
      daemons are launched.


      stop
      Stop all running daemons, or specific daemons identified by PIDs. Use
      run.php status to find PIDs.

EOF;
        echo $msg;
    }
}


class Process
{
    const PROCESS_NAME_LOG = ' php: swoole-bot'; //shell脚本管理标示
    const PID_FILE         = 'master.pid';
    private $reserveProcess;
    private $workers;
    private $workNum = 1;
    private $config  = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function start()
    {
        \Swoole\Process::daemon(true, true);
        isset($this->config['swoole']['workNum']) && $this->workNum=$this->config['swoole']['workNum'];

        //根据配置信息，开启多个进程
        for ($i = 0; $i < $this->workNum; $i++) {
            $this->reserveBot($i);
            sleep(2);
        }
        $this->registSignal($this->workers);
    }

    //独立进程
    public function reserveBot($workNum)
    {
        $self = $this;
        $ppid = getmypid();
        file_put_contents($this->config['path'] . self::PID_FILE, $ppid);
        $this->setProcessName('job master ' . $ppid . self::PROCESS_NAME_LOG);
        $reserveProcess = new \Swoole\Process(function () use ($self, $workNum) {
            //设置进程名字
            $this->setProcessName('job ' . $workNum . self::PROCESS_NAME_LOG);
            try {
                $self->config['session']='swoole-bot' . $workNum;
                global $argv;
                $vbot = new Server(!empty($argv[2])?$argv[2]:123456,$this->config);
                $vbot->start();

            } catch (Exception $e) {
                echo $e->getMessage();
            }

            echo 'reserve process ' . $workNum . " is working ...\n";
        });
        $pid                 = $reserveProcess->start();
        $this->workers[$pid] = $reserveProcess;
        echo "reserve start...\n";
    }

    //监控子进程
    public function registSignal($workers)
    {
        \Swoole\Process::signal(SIGTERM, function ($signo) {
            $this->exit();
        });
        \Swoole\Process::signal(SIGCHLD, function ($signo) use (&$workers) {
            while (true) {
                $ret = \Swoole\Process::wait(false);
                if ($ret) {
                    $pid           = $ret['pid'];
                    $child_process = $workers[$pid];
                    //unset($workers[$pid]);
                    echo "Worker Exit, kill_signal={$ret['signal']} PID=" . $pid . PHP_EOL;
                    $new_pid           = $child_process->start();
                    $workers[$new_pid] = $child_process;
                    unset($workers[$pid]);
                } else {
                    break;
                }
            }
        });
    }

    private function exit()
    {
        @unlink($this->config['path'] . self::PID_FILE);
        (new Main())->logger('Time: ' . microtime(true) . '主进程退出' . "\n");
        foreach ($this->workers as $pid => $worker) {
            //平滑退出，用exit；强制退出用kill
            \Swoole\Process::kill($pid);
            unset($this->workers[$pid]);
            (new Main())->logger("主进程收到退出信号,[' . $pid . ']子进程跟着退出 \n".'Worker count: ' . count($this->workers));
        }
        exit();
    }

    /**
     * 设置进程名.
     *
     * @param mixed $name
     */
    private function setProcessName($name)
    {
        //mac os不支持进程重命名
        if (function_exists('swoole_set_process_name') && PHP_OS !== 'Darwin') {
            swoole_set_process_name($name);
        }
    }
}
