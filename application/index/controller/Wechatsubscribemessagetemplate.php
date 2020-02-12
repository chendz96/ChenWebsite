<?php
namespace app\index\controller;
use think\Db;
class Wechatsubscribemessagetemplate extends Common
{
    public function index() {
        return view();
    }

    public function get_data() {
        $data = db('wechat_msg_type')->where('user',session('user'))->select();
        return array('data'=>$data,'code' => 0,'count'=> db('wechat_msg_type')->where('user',session('user'))
                                                                                  ->count() );
    }

    public function add() {
      return view('add');
    }

    public function del() {
      $request = request();
      db('wechat_msg_type')->where('id',$request->param('id'))->delete();
      $result['success'] = true;
      return $result;
    }

    public function add_save() {
      $request = request();
      $data = array();
      $data['subscribe_msg'] = $request->param('subscribe_msg');
      $data['orderlist_msg'] = $request->param('orderlist_msg');
      $data['user'] = session('user');
      db('wechat_msg_type')->insert($data);

      $result['success'] = true;
      return $result;
    }
}
