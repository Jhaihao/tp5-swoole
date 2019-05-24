<?php
/**
 * Created by PhpStorm.
 * User: JiangHaiHao
 * Date: 2019/5/24
 * Time: 15:15
 */


use think\Container;

$http = new swoole_http_server("0.0.0.0", 9501);
$http->set(
    [
        'enable_static_handler' => true,
        'document_root' => "/www/tp5demo/public/static",
        'worker_num' => 5,
    ]
);
$http->on('WorkerStart', function ($serv, $worker_id){
    define('APP_PATH', __DIR__ . '/../application/'); //重点3
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php'; //重点1
    //Container::get('app')->run()->send();  加上这句会直接把tp默认index输出出来
});

$http->on('request', function ($request, $response) use($http){


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

    ob_start();
    try{
        // 执行应用并响应
        think\Container::get('app',[APP_PATH])->run()->send();  //重点2
    } catch (\Exception $e) {
        $response->end(json_encode('错误'));
    }
  //  echo 'action ==' . request()->action() . PHP_EOL;
    $res = ob_get_contents();
    ob_end_clean();
    $response->end($res);
    //$http->close();
});

$http->start();