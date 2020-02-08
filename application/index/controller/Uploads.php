<?php
namespace app\index\controller;

class Uploads extends Common
{
    public function index() {
        return view();
    }

    public function download() {
        $img_data = db('user_upload_img')->where('user' , session('user'))->select();
        $this->assign('img_data' , $img_data);
        $this->assign('num' , count($img_data));
        return view('download');
    }

    public function get_img_data() {
      $request = request();
      $data = db('user_upload_img')->where('user' , session('user'))
                                    ->page($request->param('page'), $request->param('limit'))
                                    ->order('time asc')
                                    ->select();
      $i=0;
      foreach ($data as $key => $value) {
          //$data[$i]['src'] = "../../../../user_upload_img/".$value['filename'];
          $data[$i]['src'] = "http://boring1996.cn:8080/uploadimg/".$value['thumb_filename'];
          $i++;
      }
      //trace($data);
      return array('data'=>$data,'code' => 0,'count'=> db('user_upload_img')->where('user' , session('user'))
                                                                      ->count());
    }

    public function upload_img() {
        $file = request()->file('file');
        $upload_dir = 'user_upload_img';
        trace('文件上传开始：'.time());
        if($file){
          if (is_readable(ROOT_PATH . DS . $upload_dir) == false) {
              mkdir(ROOT_PATH . DS . $upload_dir);
          }
            //trace($_FILES["file"]["tmp_name"].'---'.ROOT_PATH . $upload_dir.$_FILES["file"]["name"]);
            $file_type = strrchr($_FILES["file"]["name"] , ".");
            $tmp_file = substr($_FILES["file"]["name"] , 0 , strlen($_FILES["file"]["name"]) - strlen($file_type));
            $file_name = $tmp_file."_".time().$file_type;

            if(true == move_uploaded_file($_FILES["file"]["tmp_name"],ROOT_PATH . $upload_dir.DS.$file_name)) {
                $result['code'] = true;
                $result['file_name'] = $_FILES["file"]["name"];
                $result['file_size'] = $_FILES["file"]["size"];
                $result['msg'] = "上传成功";
                trace('文件移动结束：'.time());
                //生成缩略图像
                $thumb_filename = $tmp_file."_thumb_".time().$file_type;
                $image = \think\Image::open(ROOT_PATH . $upload_dir.DS.$file_name);
                $image->thumb(400, 400,\think\Image::THUMB_SCALING)->save(ROOT_PATH . $upload_dir.DS. $thumb_filename);
                trace('文件压缩结束：'.time());
                $file_data = array();
                $file_data['filename'] = $file_name;
                $file_data['thumb_filename'] = $thumb_filename;
                $file_data['user'] = session('user');
                $file_data['time'] = time();
                $file_data['filesize'] = $_FILES["file"]["size"];
                db('user_upload_img')->insert($file_data);
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

    public function downloadimg() {
        $request = request();
        $user = $request->param('user');
        $id = $request->param('id');
        $filetype = "application/octet-stream";
        $suffix = "";
        $file = db('user_upload_img')->where('id',$id)->find();
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=180');
        header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: none');
        header('Content-type: ' . $filetype); //文件类型
        header('Content-Disposition: attachment; filename="' . iconv('UTF-8', 'GBK', $file['filename']) . $suffix . '"'); //文件名称
        header('Content-length: ' . $file['filesize']); //文件大小
        readfile(ROOT_PATH . 'user_upload_img' . DS . $file['filename']);
    }

    public function del_file() {
        $request = request();
        $file = db('user_upload_img')->where('id',$request->param('id'))->find();
        $dirname = ROOT_PATH . 'user_upload_img';
        if((unlink($dirname . DS .$file['filename']) == true) &&
           (unlink($dirname . DS .$file['thumb_filename']) == true)){
          db('user_upload_img')->where('id',$request->param('id'))->delete();
          $result['success'] = true;
        } else {
          $result['success'] = false;
        }
        return $result;
    }

}
