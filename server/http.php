<?php

class Http
{
    CONST HOST = "0.0.0.0";
    CONST PORT = 9501;

    public $http = null;

    public function __construct()
    {
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        $this->http->set(
            [
                'enable_static_handler' => true,
                'document_root' => "/www/tp5-swoole/public/static",
                'worker_num' => 5,
               // 'task_worker_num' => 4,
            ]
        );

        $this->http->on("workerstart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
      //  $this->http->on("finish", [$this, 'onFinish']);
       // $this->http->on("close", [$this, 'onClose']);

        $this->http->start();
    }

    public function onWorkerStart(swoole_server $server, int $worker_id){
        define('APP_PATH', __DIR__ . '/../application/'); //重点3
        // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php'; //重点1
        //Container::get('app')->run()->send();  加上这句会直接把tp默认index输出出来
    }


    public function onTask($serv, $taskId, $workerId, $data){

        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\lib\task\Task;
        $method = $data['method'];
        $flag = $obj->$method($data['data']);
        return $flag; // 告诉worker
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response) {
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
        $_POST = [];
        if(isset($request->post)) {
            foreach($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $_POST['http_server'] = $this->http;

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
}

new Http();


