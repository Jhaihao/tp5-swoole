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

        return 'send';

        $phoneNum = request()->post('phone_num', 0, 'intval');
        if(empty($phoneNum)) {
            // status 0 1  message data
            return Util::show(config('code.error'), 'error');
        }
    }
}
