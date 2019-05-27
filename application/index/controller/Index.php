<?php

namespace app\index\controller;

use app\common\lib\Util;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return 'index';
    }

    /**
     *发送验证码
     */
    public function send()
    {

        // $phoneNum = request()->post('phone_num', 0, 'intval'); //这种参数一直是第一次输入的
        $phoneNum = $_POST['phone_num'];
        if ($phoneNum) {
            // status 0 1  message data
            return Util::show(config('code.error'), 'error');
        }

        //生成一个随机数
        $code = rand(1000, 9999);

        try {
            $response = $code;  //这里是用第三方短信jdk  返回code
        } catch (\Exception $e) {
            return Util::show(config('code.error'), '短信jdk内部异常');
        }

        if (true) {  //模拟返回状态等于 ok
            //异步mysql储存验证码.

            $redis = new \Swoole\Coroutine\Redis();
            $redis->connect(config('redis.host'), config('redis.port'));
            $redis->set('sms', '123');

        }

        return '1';

    }
}
