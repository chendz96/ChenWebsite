<?php
namespace app\index\controller;

use think\facade\Config;
class Wechatgoodsinfo extends Common
{
    public function index() {
        return view();
    }

    public function get_categories_data() {
        $data = db('categories')->where('user' , session('user'))->order('sort asc')->select();
        return array('data'=>$data,'code' => 0,'count'=> db('categories')->where('user' , session('user'))
                                                                        ->count());
    }

    public function add_categories() {
        return view('add_categories');
    }

    public function add_categories_save() {
        $request = request();
        $data = array();
        $max_id = db('categories')->where('user' , session('user'))->max('dataid');
        $data['name'] = $request->param('name');
        $data['sort'] = $request->param('sort');
        $data['user'] = session('user');
        $data['status'] = 1;
        $data['dataid'] = $max_id + 1;
        $data['scrollid'] = $max_id + 1;
        db('categories')->insert($data);
        $result['success'] = true;
        return $result;
    }

    public function del_categories() {
        $request = request();
        db('categories')->where('id' , $request->param('id'))->delete();
        $result['success'] = true;
        return $result;
    }

    public function adjust_categories_order() {
        return view('adjust_categories');
    }

    public function adjust_categories_order_save() {
        $request = request();
        db('categories')->where('id',$request->param('id'))->update(['sort'=>$request->param('sort')]);
        $result['success'] = true;
        return $result;
    }

    public function disable_categories() {
        $request = request();
        db('categories')->where('id',$request->param('id'))->update(['status'=>'0']);
        $result['success'] = true;
        return $result;
    }
    public function able_categories() {
        $request = request();
        db('categories')->where('id',$request->param('id'))->update(['status'=>'1']);
        $result['success'] = true;
        return $result;
    }
}
