<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Wechatgoodslist extends Common
{
    public function index() {
        return view();
    }

    public function get_goods_data() {
        $request = request();
        $page = $request->param('page');
        $limit = $request->param('limit');
        $sql = "SELECT
              	`t1`.*,
              	`t2`.`name` as `categories_name`
                FROM
              	`wechat_goods_list` `t1`
              	INNER JOIN `categories` `t2` ON `t1`.`categoryid` = `t2`.`dataid`
                WHERE `t1`.`user` ='". session('user')."'
                LIMIT ".($page-1)*$limit.','.$page*$limit;
        $data = Db::query($sql);
        return array('data'=>$data,'code' => 0,'count'=> db('wechat_goods_list')->count() );
    }

    public function add_goods() {
        $categories = db('categories')->where('user',session('user'))->where('status' , '1')->select();
        $this->assign('categories',$categories);
        return view('add_categories');
    }

    public function adjust_goods_order() {
        return view('adjust_goods_order');
    }


    public function manger_goods() {
        $request = request();
        $flag = $request->param('flag');
        $id = $request->param('id');
        $result['success'] = true;
        if($flag == 'del') {
            $data = db('wechat_goods_list')->where('id',$id)->find();
            db('wechat_goods_list')->where('id',$id)->delete();
            unlink(ROOT_PATH . 'wechat_goods_img' . DS . $data['save_dir']);
        } else if($flag == 'disable') {
            db('wechat_goods_list')->where('id',$id)->update(['status'=>'0']);
        } else if($flag == 'able') {
            db('wechat_goods_list')->where('id',$id)->update(['status'=>'1']);
        } else {
            db('wechat_goods_list')->where('id',$id)->update(['sort'=>$request->param('sort')]);
        }
        return $result;
    }

    public function upload_goods_img() {
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

    public function add_goods_save() {
        $request = request();
        $upload_banner_dir = ROOT_PATH . 'wechat_goods_img';//用户区
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
        $file_data['name'] = $request->param('name');
        $file_data['user'] = session('user');
        $file_data['categoryid'] = $request->param('categoryid');
        $file_data['price'] = $request->param('price');
        $file_data['sort'] = $request->param('sort');
        $file_data['status'] = 1;
        $file_data['save_dir'] = $file_name;
        db('wechat_goods_list')->insert($file_data);
        $result['success'] = true;
        $result['msg'] = "!!!";
        return $result;
    }


}
