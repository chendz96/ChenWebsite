<?php
namespace app\index\controller;

use think\facade\Config;
class Songupload extends Common
{
    public function index()
    {
        return view();
    }

    public function download()
    {
        return view('download');
    }

    public function download_file() {
        $request = request();
        $file_data = db('user_upload_file')->where('id',$request->param('id'))->find();

        $dirname = ROOT_PATH . 'user_upload_file' .DS.session('user');
        $filetype = "application/octet-stream";
        $suffix = "";
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=180');
        //header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: none');
        header('Accept-Ranges:bytes');
        header('Content-type: ' . $filetype); //文件类型
        header('Content-Disposition: attachment; filename="' . iconv('UTF-8', 'GBK', $file_data['file_name']) . $suffix . '"'); //文件名称
        //if (!empty($filesize))
        header('Content-length: ' . $file_data['file_size']); //文件大小
        trace($dirname . DS . $file_data['file_name']);
        readfile($dirname . DS . $file_data['file_name_time']);
    }

    public function del_file() {
        $request = request();
        $file_data = db('user_upload_file')->where('id',$request->param('id'))->find();
        $dirname = ROOT_PATH . 'user_upload_file' .DS.session('user');
        if(unlink($dirname . DS .$file_data['file_name_time']) == true){
          db('user_upload_file')->where('id',$request->param('id'))->delete();
          $result['success'] = true;
        } else {
          $result['success'] = false;
        }
        return $result;
    }

    public function get_data() {
        $request = request();
        if(input('?get.field') == true) {
            $order_field = $request->param('field');
        } else {
            $order_field = 'id';
        }

        if(input('?get.order') == true) {
            $sort_field = $request->param('order');
        } else {
            $sort_field = 'asc';
        }

        $data = db('user_upload_file')->where('user' , session('user'))
                                      ->page($request->param('page'), $request->param('limit'))
                                      ->order($order_field , $sort_field)
                                      ->select();
        return array('data'=>$data,'code' => 0,'count'=> db('user_upload_file')->where('user' , session('user'))
                                                                        ->count());

    }

    public function upload () {
      $request = request();
      $file = request()->file('file');
      // 移动到框架应用根目录/public/uploads/ 目录下
      if($file) {
          //查看是否新用户
          $upload_time = time();
          $upload_user_dir = ROOT_PATH . 'user_upload_file'.DS.session('user');//用户区
          if (is_readable($upload_user_dir) == false) {
              mkdir($upload_user_dir);
          }
          $file_type = strrchr($_FILES["file"]["name"] , ".");
          $tmp_file = substr($_FILES["file"]["name"] , 0 , strlen($_FILES["file"]["name"]) - strlen($file_type));
          $file_name = $tmp_file."_".$upload_time.$file_type;
          $info = move_uploaded_file($_FILES["file"]["tmp_name"],$upload_user_dir.DS.$file_name);
          if($info){
              $result['filesavename'] = $_FILES["file"]["name"];
              $result['code'] = true;
              trace($_FILES["file"]);
              $file_data = array();
              $file_data['file_name_time'] =$file_name;
              $file_data['file_size'] =$_FILES["file"]["size"];
              $file_data['file_name'] = $_FILES["file"]["name"];
              $file_data['user'] = session('user');
              $file_data['upload_time'] = $upload_time;
              db('user_upload_file')->insert($file_data);
              return $result;
          }else {
              // 上传失败获取错误信息
              $result['msg'] = $file->getError();
              $result['code'] = false;
              return $result;
          }
      }
    }

    public function synchronous_devsn_chuanyun() {
      // 创建一个新cURL资源
      /*$post_data = array();

      $post_data["ActionName"] = "LoadBizObject";//CreateBizObject
      $post_data["SchemaCode"] = "D00134365a961b009744980be878d01942f9915";//D000240a1d2bf193d4e4b5d8d1cc150e841f870
      $post_data["BizObjectId"]= "1ba9d5e0-659f-4704-af1f-bf4a37b85c67";//199723ef-977f-445e-9dd0-12864f5aac6e

      $post_data["IsSubmit"] = "true";

      $headers = array("Content-Type:application/json;charset='utf-8'","EngineCode:yv3zsm47qaov1ezauaa0b4hx1","EngineSecret:3/oWjDC0AxuAe1bMDCMnIHFdzLjovZ6MuPY9C8fSRy4Vqz452aBUxw==","Content-Length:".strlen(json_encode($post_data)));
      $url = "https://www.h3yun.com/OpenApi/Invoke";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));//($post_data);serializejson_encode
      $sResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($sResult);
      trace($obj->ReturnData->BizObject->OwnerDeptId);*/
      trace(config());
      $result['success'] = true;
      return $result;
    }

    public function httpstest() {
        /*$data['number']="18959281291";
        //$data['key'] = 'b4b88a8ffc09e2fd3f24251ee19fa168';
        http_build_query($data);
        $url = "https://numvalidate.com/api/validate?".http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //curl_setopt($ch, CURLOPT_POSTFIELDS , http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        //$res = json_decode($output);trace($res);
        //trace($res->result->city);
        trace($output);*/
        phpinfo();
        //$result['success'] = true;
        //return $result;
    }


}
