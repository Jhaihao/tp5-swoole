<?php

namespace app\index\controller;

use app\common\lib\Redis;
use app\common\lib\redis\Predis;
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
            //redis储存验证码.
            $redis = new \Swoole\Coroutine\Redis();
            $redis->connect(config('redis.host'), config('redis.port'));
            $redis->set('sms_15992477600', '1879');
            return Util::show(config('code.success'), '验证码发送成功');
        }else {
            return Util::show(config('code.error'), '验证码发送失败');
        }
    }

    public function login()
    {
        $phoneNum = intval($_POST['phone_num']);
        $code = intval($_POST['code']);

        if(empty($phoneNum) || empty($code)) {
            return Util::show(config('code.error'), 'phoneNum或code参数为空');
        }
        // redis code
        try {
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum)); //从redis验证码
        }catch (\Exception $e) {
            echo $e->getMessage();
        }
        if($redisCode == $code) {
            // 写入redis
            $data = [
                'user' => $phoneNum,
                'srcKey' => md5(Redis::userkey($phoneNum)),
                'time' => time(),
                'isLogin' => true,
            ];
            Predis::getInstance()->set(Redis::userkey($phoneNum), $data);
            return Util::show(config('code.success'), 'ok', $data);
        } else {
            return Util::show(config('code.error'), 'login error');
        }

    }
}
