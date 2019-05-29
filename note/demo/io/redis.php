<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/3/13
 * Time: 上午1:12
 */

$redis = new Swoole\Redis;
$redis->connect('127.0.0.1', 6379, function ($redis, $result) {
	sleep(10);
    $redis->set('test_key', 'value', function ($redis, $result) {
        $redis->get('test_key', function ($redis, $result) {
            var_dump($result);
        });
    });
});
redis->send('1234');