<?php

use app\common\lib\task\Task;
use think\facade\Config;

class Ws {
    CONST HOST = "0.0.0.0";
    CONST PORT = 9501;
    CONST CHART_PORT = 9502;

    public $ws = null;
    public function __construct() {
        // 获取 key 有值 del
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        $this->ws->listen(self::HOST, self::CHART_PORT, SWOOLE_SOCK_TCP);

        $this->ws->set(
            [
                'enable_static_handler' => true,
                'document_root' => "/www/tp5-swoole/public/static",
                'worker_num' => 4,
                'task_worker_num' => 4,
               // 'daemonize' => true
            ]
        );
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
     * @param $server
     */
    public function onStart($server) {
        swoole_set_process_name("live_master");
    }
    /**
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server,  $worker_id) {
        define('APP_PATH', __DIR__ . '/../application/'); //重点3


        // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php'; //重点1

        \app\common\lib\redis\Predis::getInstance()->del('live_game_key');

    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response) {
        if($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return ;
        }
        $_SERVER  =  [];
        if(isset($request->server)) {
            foreach($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if(isset($request->header)) {
            foreach($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        $_GET = [];
        if(isset($request->get)) {
            foreach($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_FILES = [];
        if(isset($request->files)) {
            foreach($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }
        $_POST = [];
        if(isset($request->post)) {
            foreach($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $this->writeLog();
        $_POST['http_server'] = $this->ws;

        ob_start();
        // 执行应用并响应
        try {
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e) {
            // todo
        }

        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     */
    public function onTask($serv, $taskId, $workerId, $data) {

        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\lib\task\Task;

        $method = $data['method'];
        $flag = $obj->$method($data['data'], $serv);
        /*$obj = new app\common\lib\ali\Sms();
        try {
            $response = $obj::sendSms($data['phone'], $data['code']);
        }catch (\Exception $e) {
            // todo
            echo $e->getMessage();
        }*/

        return $flag; // 告诉worker
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request){
        // fd redis [1]
       \app\common\lib\redis\Predis::getInstance()->sAdd('live_game_key', $request->fd);
       echo $request->fd.'上线了'.PHP_EOL;
      // print_r($ws);
    }

    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        // fd del  客户端连接关闭 ,去redis 删除有序集合里面的$fd
        \app\common\lib\redis\Predis::getInstance()->sRem('live_game_key', $fd);
        echo $fd.'客户端关闭了' . PHP_EOL;
    }

    /**
     * 记录日志
     */
    public function writeLog() {
        $datas = array_merge(['date' => date("Ymd H:i:s")],$_GET, $_POST, $_SERVER);
        print_r($datas);
        $logs = "";
        foreach($datas as $key => $value) {
            $logs .= $key . ":" . $value . " ";
        }


//
//        $res = swoole_async_writefile(__DIR__."/../runtime/".date("Ym") ."/"."access.log", $logs.PHP_EOL, function($filename) {
//
//        }, FILE_APPEND);



    }
}

new Ws();

// 20台机器    agent -> spark (计算) - 》 数据库   elasticsearch  hadoop

// sigterm sigusr1 usr2
