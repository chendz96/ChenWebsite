<?php
namespace app\index\controller;
use think\Db;
class Wechatsysparam extends Common
{
    public function index() {
        return view();
    }

    public function get_data() {
        $request = request();
        $data = db('wechat_template_info')->alias('t1')->field('t1.*,t2.subscribe_msg,t2.orderlist_msg')
                                          ->join('wechat_msg_type t2','t1.msg_id=t2.id','LEFT')
                                          ->where('t1.user',session('user'))->select();
        return array('data'=>$data,'code' => 0,'count'=>db('wechat_template_info')->where('user',session('user'))
                                                                                  ->count() );
    }

    public function add_sysparam() {
        $msg = db('wechat_msg_type')->where('user',session('user'))->select();
        $this->assign('msg',$msg);
        return view('add_sysparam');
    }

    public function add_param_save() {
        $request = request();
        $data = array();
        $data['appid'] = $request->param('appid');
        $data['secret'] = $request->param('secret');
        $data['template_id'] = $request->param('template_id');
        $data['user'] = session('user');
        $data['msg_id'] = $request->param('msg_id');
        db('wechat_template_info')->insert($data);

        $result['success'] = true;
        return $result;
    }

    public function edit() {
        $request = request();
        $data = db('wechat_template_info')->where('id',$request->param('id'))->find();
        $msg = db('wechat_msg_type')->where('user',session('user'))->select();
        $this->assign('msg',$msg);
        $this->assign('data',$data);
        return view('edit');
    }

    public function del() {
        $request = request();
        db('wechat_template_info')->where('id',$request->param('id'))->delete();
        $result['success'] = true;
        return $result;
    }

    public function edid_save() {
        $request = request();
        $data = array();
        $data['appid'] = $request->param('appid');
        $data['secret'] = $request->param('secret');
        $data['template_id'] = $request->param('template_id');
        $data['msg_id'] = $request->param('msg_id');
        db('wechat_template_info')->where('user',session('user'))->update($data);
        $result['success'] = true;
        return $result;
    }
}
