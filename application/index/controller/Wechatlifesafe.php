<?php
namespace app\index\controller;
class Wechatlifesafe extends Common {
    public function index() {
        return view();
    }
    public function add_page() {
        return view('add_page');
    }

    public function add_pic() {
        $page_id = db('wechat_lifisafe_page')->where('user',session('user'))->where('status' , '1')->select();
        $this->assign('page_id',$page_id);
        return view('add_pic');
    }

    public function add_text() {
        $page_id = db('wechat_lifisafe_page')->where('user',session('user'))->where('status' , '1')->select();
        $this->assign('page_id',$page_id);
        return view('add_text');
    }

    public function add_text_save() {
        $request = request();
        $data = array();
        $data['page_id'] = $request->param('page_id');
        $data['text'] = $request->param('text');
        db('wechat_lifesafe_text')->insert($data);
        $result['success'] = true;
        return $result;
    }

    public function adjust_pic_order() {
        return view('adjust_pic_sort');
    }

    public function manger_pic() {
        $request = request();
        $flag = $request->param('flag');
        $result = array();
        $result['success'] = true;
        if($flag == "del") {
            $data = db('wechat_lifesafe_pic')->where('id',$request->param('id'))->find();
            db('wechat_lifesafe_pic')->where('id',$request->param('id'))->delete();
            unlink(ROOT_PATH . 'wechat_lifesafe_img' . DS . $data['save_name']);
        } else if($flag == "sort") {
            $data = db('wechat_lifesafe_pic')->where('id',$request->param('id'))
                                                  ->update(['sort'=>$request->param('sort')]);
        } else if($flag == "disable"){
          $data = db('wechat_lifesafe_pic')->where('id',$request->param('id'))
                                                ->update(['status'=>'0']);
        } else {
          $data = db('wechat_lifesafe_pic')->where('id',$request->param('id'))
                                                ->update(['status'=>'1']);
        }
        return $result;
    }

    public function add_page_save() {
        $request = request();
        $data = array();
        $data['user'] = session('user');
        $data['title'] = $request->param('title');
        $data['sort'] = $request->param('sort');
        $data['status'] = '1';
        db('wechat_lifisafe_page')->insert($data);
        $result['success'] = true;
        return $result;
    }

    public function get_page_data() {
        $request = request();
        $data = db('wechat_lifisafe_page')->where('user',session('user'))
                                          ->page($request->param('page'), $request->param('limit'))
                                          ->select();

        return array('data'=>$data,'code' => 0,'count'=> db('wechat_lifisafe_page')->where('user',session('user'))
                                                        ->count());
    }

    public function get_text_data() {
        $request = request();
        $data = db('wechat_lifisafe_page')->field('wechat_lifisafe_page.title,t1.text,t1.id')
                                          ->join('wechat_lifesafe_text t1','t1.page_id=wechat_lifisafe_page.id')
                                          ->where('user',session('user'))
                                          ->page($request->param('page'), $request->param('limit'))
                                          ->select();

        return array('data'=>$data,'code' => 0,'count'=> db('wechat_lifisafe_page')->join('wechat_lifesafe_text t1','t1.page_id=wechat_lifisafe_page.id')
                                                                                   ->where('user',session('user'))->count());
    }

    public function del_text() {
        $request = request();
        db('wechat_lifesafe_text')->where('id',$request->param('id'))->delete();
        $result['success'] = true;
        return $result;
    }

    public function del_title() {
        $request = request();
        db('wechat_lifisafe_page')->where('id',$request->param('id'))->delete();
        $result['success'] = true;
        return $result;
    }

    public function get_page_pic() {
        $request = request();
        $data = db('wechat_lifesafe_pic')->where('user',session('user'))
                                          ->page($request->param('page'), $request->param('limit'))
                                          ->select();
        for($i=0 ; $i<count($data) ; $i++) {
            $data[$i]['url'] = "http://boring1996.cn:8080/safeimg/".$data[$i]['save_name'];
        }
        trace($data);
        return array('data'=>$data,'code' => 0,'count'=> db('wechat_lifesafe_pic')->where('user',session('user'))
                                                        ->count());
    }

    public function upload_lifesafe_img() {
      $file = request()->file('file');

      $result = array();

      if($file){
          $info = $file->move(ROOT_PATH . 'runtime' . DS . 'uploads', '');
          if($info){
              $ret = true;
              if ($ret == true) {
                  $result['code'] = true;
                  $result['file_name'] = $info->getInfo('name');
                  $result['file_size'] = $info->getInfo('size');
                  $result['msg'] = "上传成功";
              } else {
                  $result['code'] = false;
                  $result['msg'] = "上传文件失败";
              }
          }else{
              $result['code'] = false;
              $result['msg'] = "获取上传文件信息失败";
          }
      } else {
          $result['code'] = false;
          $result['msg'] = "上传失败，" . $file->getError();
      }

      return $result;
    }

    public function add_pic_save() {
        $request = request();
        $upload_banner_dir = ROOT_PATH . 'wechat_lifesafe_img';//用户区
        trace($upload_banner_dir);
        if (is_readable($upload_banner_dir) == false) {
            mkdir($upload_banner_dir,0777);
        }
        $file_type = strrchr($request->param('upload_file_name') , ".");
        $tmp_file = substr($request->param('upload_file_name') , 0 , strlen($request->param('upload_file_name')) - strlen($file_type));
        $file_name = $tmp_file."_". session('user') . "_" .time().$file_type;
        copy(ROOT_PATH . 'runtime' . DS . 'uploads' . DS . $request->param('upload_file_name'),
            $upload_banner_dir . DS . $file_name);
        $image = \think\Image::open($upload_banner_dir . DS . $file_name);
        $image->thumb(400, 400,\think\Image::THUMB_SCALING)->save($upload_banner_dir . DS . $file_name);
        $file_data = array();
        $file_data['user'] = session('user');
        $file_data['page_id'] = $request->param('page_id');
        $file_data['sort'] = $request->param('sort');
        $file_data['status'] = 1;
        $file_data['save_name'] = $file_name;
        db('wechat_lifesafe_pic')->insert($file_data);
        $result['success'] = true;
        $result['msg'] = "!!!";
        return $result;
    }


}
