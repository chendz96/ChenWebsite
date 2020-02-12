<?php
namespace app\index\controller;
use think\Db;
class wechatsubscribemessage extends Common
{
    public function index() {
        return view();
    }

    public function get_data() {
        $request = request();

        $data = db('wechat_take_clothes')->where('user',session('user'))
                                         ->page($request->param('page'), $request->param('limit'))
                                         ->select();
        return array('data'=>$data,'code' => 0,'count'=> db('wechat_take_clothes')->where('user',session('user'))
                                                                                  ->count() );
    }

    public function send_subscribemessage() {
        $request = request();

        $access_token = $this->get_access_token();
        trace("access_token:".$access_token);
        $openid = $request->param('openid');
        $id = $request->param('id');
        $template = db('wechat_template_info')->alias('t1')->join('wechat_msg_type t2','msg_id=t2.id')
                                              ->where('t1.user',session('user'))->find();        
        $template_id = $template['template_id'];
        $datatmp = array();
        $data = array();
        $datatmp['thing7']['value'] = $template['subscribe_msg'];
        $datatmp['date4']['value'] = date("Y年n月j日 H:i",time());
        $data['touser'] = $openid;
        $data['template_id'] = $template_id;
        $data['data'] = $datatmp;
        //trace(json_encode($data['data']) );
        $sendurl = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$access_token;

        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache");
        $ch = curl_init($sendurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $cul_result = curl_exec($ch);
        $cul_result = json_decode($cul_result,true);
        curl_close($ch);trace($cul_result);
        if($cul_result['errmsg'] == 'ok') {
            db('wechat_take_clothes')->where('id',$id)->update(['status'=>'1','finish_time'=>time()]);
        } else {
            $result['success'] = false;
            $result['msg'] = '失败';
        }
        $result['success'] = true;
        return $result;
    }

    protected function get_access_token() {
        //$secret = '820eda940aa71ff34dbf158f3e21d89d';
        //$appid = 'wx3368f6bb3f408bd2';
        $user_data = db('wechat_template_info')->where('user',session('user'))->find();
        $secret = $user_data['secret'];
        $appid = $user_data['appid'];
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $html = file_get_contents($url);
        $output = json_decode($html, true);
        $access_token = $output['access_token'];
        return $access_token;
    }

}
