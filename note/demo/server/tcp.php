<?php

//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("127.0.0.1", 9501);

$serv->set([
    'worker_num' => 6 , // worker进程数 cpu 1-4
    'max_request' => 10000,
]);
//监听连接进入事件
/**
 * $fd 客户端连接的唯一标示
 * $reactor_id 线程id
 */
$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "线程id=: {$reactor_id} -客户端唯一标示是 ". $fd .PHP_EOL;
});

//监听数据接收事件
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "服务器线程: {$reactor_id} 接受者是唯一标示 {$fd}"."数据是=>" . $data);
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd) {
    $res = $serv->exist($fd);
    echo $res;
});

//启动服务器
$serv->start();