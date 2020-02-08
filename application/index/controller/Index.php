<?php
namespace app\index\controller;
use think\cache\driver\Redis;
class Index
{
    public function index()
    {
        return view('index');
    }

    public function register() {
        return view('register');
    }

    public function login() {
        $request = request();
        $account = $request->param("account");
        $passwd = $request->param("password");
        $userdata = db('user_info')->where('user_name' , $account)->find();
        if(count($userdata) == 0) {
          $result['success'] = false;
          $result['msg'] = "该账号不存在";
          return $result;
        }
        if($userdata['passwd'] == $passwd) {
          $result['success'] = true;
          $result['msg'] = "登录成功";
          session('user', $userdata['user_name']);
          session('time', time());
          $redis = new Redis();
          $redis->inc('query_num');
          return $result;
        } else {
        $result['success'] = false;
        $result['msg'] = "登录失败，密码错误";
        return $result;
      }
    }

    public function login_out() {
      session(null);

      $result['success'] = true;
      $result['msg'] = "注销成功";

      return $result;
    }

    public function act_account() {
        $request = request();
        $user = $request->param('user');
        db('user_info')->where('user_name' , $user)->update(["activ"=>"1"]);
        return "激活成功";
    }

    protected function send_mail1($send_mail , $user) {
      $mail = new Mail();
      $mail->title = "欢迎注册阿萨德";
      $mail->send_mail = $send_mail;
      $mail->content = "请点击链接激活"."http://106.53.66.148/Index/index/act_account/user/".$user;
      $mail->sendEmail();
    }

    protected function get_random_code() {
      $yuan = '123456789abcdefghigklmnopqrstyvwxyz';
      $arr = str_split($yuan);
      $v_code = '';
      for ($i=0; $i<8; $i++){
          $pos =  mt_rand(0,34);
          $v_code .= $arr[$pos];
      }
      return $v_code;

    }

    public function send_mail_get_verify_code() {
      $request = request();
      $email = $request->param('email');
      $verify_code = $this->get_random_code();
      $redis = new Redis();
      $redis->set($email,$verify_code,60);      
      $mail = new Mail();
      $mail->title = "欢迎注册阿萨德";
      $mail->send_mail = $email;
      $mail->content = "验证码为".$verify_code;
      $mail->sendEmail();
      $result['success']=true;
      return $result;
    }

    public function user_register() {
        $request = request();

        $flag = $request->param('flag');
        $account = $request->param('account');
        if($flag == "check") {
            $count = db('user_info')->where('user_name' , $account)->find();
            if($count == 0) {
              $result['success'] = true;
              $result['msg'] = '允许注册';
              return $result;
            } else {
              $result['success'] = false;
              $result['msg'] = '该账号已被注册';
              return $result;
            }
        }
        if($flag == "reg") {
            $request = request();
            $userdata = array();
            $redis = new Redis();
            trace($redis->get($request->param("email")));
            if($redis->get($request->param("email")) == false) {
                $result['success'] = false;
                $result['msg'] = "redis获取失败";
                return $result;
            }

            if($redis->get($request->param("email"))!=$request->param('verification_code')) {
              $result['success'] = false;
              $result['msg'] = "邮箱验证码校验失败";
              return $result;
            }
            $userdata['user_name'] = $account = $request->param('username');
            $userdata['passwd'] = $request->param('password');
            $userdata['email'] = $request->param('email');
            $userdata['activ'] = "1";
            db('user_info')->insert($userdata);
            $result['success'] = true;
            $result['msg'] = "注册成功";
            return $result;
        }
    }
}
