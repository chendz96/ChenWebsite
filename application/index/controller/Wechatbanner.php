<?php
namespace app\index\controller;
use think\Controller;
class Wechatbanner extends Controller

{

    public function bannerlist() {
        $request = request();
        $user = db('user_info')->where('user_name',$request->param('user'))
                       ->where('passwd',$request->param('password'))
                       ->find();
        if($user == null) {
            return false;
        }
        $banner = new banner();
        $data = db('wechat_upload_banner_img')->where('user',$request->param('user'))
                                              ->where('status','1')
                                              ->order('sort asc')
                                              ->select();
        $banner->set_data($data);
        $banner->set_result(0 , 'banner success');
        return json($banner);
    }

    public function adjust_banner_order() {
        return view('adjust_banner_order');
    }

    public function clothessubmit() {
        $request = request();
        $user = $request->param('user');
        $password = $request->param('password');
        $check_user_result = $this->check_user_pwd($user,$password);
        if($check_user_result == false) {
            $result['code'] = 1;
            $result['success'] = false;
            $result['msg'] = '账户密码不对';
            return json($result);
        }
        $data = array();
        $user_info = db('wechat_user_info')->where('id',$request->param('uid'))->find();
        $data['phone'] = $request->param('phone');
        $data['ps'] = $request->param('ps');
        $data['openid'] = $user_info['openid'];
        $data['uid'] = $request->param('uid');
        $data['user'] = $user;
        $data['time'] = time();
        $data['template_id'] = '1';
        $data['msg_type'] = '1';
        $data['status'] = 0;
        db('wechat_take_clothes')->insert($data);
        $result['code'] = 0;
        $result['success'] = true;
        return json($result);
    }

    public function safeimglist() {
        $request = request();
        $user = $request->param('user');
        $password = $request->param('password');
        $check_user_result = $this->check_user_pwd($user,$password);
        if($check_user_result == false) {
            $result['code'] = 1;
            $result['success'] = false;
            $result['msg'] = '账户密码不对';
            return json($result);
        }
        $data = db('wechat_lifisafe_page')->where('user',$user)
                                          ->where('status','1')
                                          ->select();
        $safe_img_list = new safe_img_list();
        $safe_img_list->set_result(0,"success");
        $safe_img_list->set_data($data);
        return json($safe_img_list);
    }

    public function orderList() {
        $request = request();
        $uid = $request->param('uid');
        $status = $request->param('status');
        if($status == '0') {
        $data=  db('wechat_take_clothes')->field('wechat_take_clothes.time as time,msg.orderlist_msg as msg')
                                          ->join('wechat_msg_type msg','msg.id=wechat_take_clothes.msg_type')
                                          ->where('uid',$uid)
                                         ->where('status','0')
                                         ->select();
        } else {
          $data=  db('wechat_take_clothes')->field('wechat_take_clothes.finish_time as time ,msg.subscribe_msg as msg')
                                            ->join('wechat_msg_type msg','msg.id=wechat_take_clothes.msg_type')
                                            ->where('uid',$uid)
                                           ->where('status','1')
                                           ->select();
        }

        $orderlist = new orderlist();
        $orderlist->set_result('0','success');
        $orderlist->set_data($data);
        return json($orderlist);
    }

    public function sendmsg() {
      $secret = '820eda940aa71ff34dbf158f3e21d89d';
      $appid = 'wx3368f6bb3f408bd2';
      $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
      $html = file_get_contents($url);
      $output = json_decode($html, true);
      trace($output);

      $access_token = $output['access_token'];
      $openid = 'o7ugR5dtQsktqvAS_nXJSHbqM85g';
      $template_id = 'akXDU3p8Bd_g39zDZlMdlOAoZGsKJzLq-mmUP7rDUWI';
      $datatmp = array();
      $data = array();
      $datatmp1 = array();
      $datatmp2 = array();
      $datatmp1['value'] = "衣服修补完毕";
      $datatmp2['value'] = "2019年10月1日 15:01";
      $datatmp['thing7']['value'] = $datatmp1['value'];
      $datatmp['date4']['value'] = $datatmp2['value'];
      $data['touser'] = $openid;
      $data['template_id'] = $template_id;
      $data['data'] = $datatmp;
      trace(json_encode($data['data']) );
      //$data['access_token'] = $access_token;
      //$data['data'] =  "{\"thing7\": {\"value\": \"衣服修补完毕\"} ,\"date4\": {\"value\": \"2019年10月1日\"}}";
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
      $result = curl_exec($ch);
      curl_close($ch);

      trace("result:".$result);
      //$html = file_get_contents($sendurl);
      //$output = json_decode($html, true);


    }

    public function login() {
       $request = request();
       $secret = '820eda940aa71ff34dbf158f3e21d89d';
       $appid = 'wx3368f6bb3f408bd2';
       $code = $request->param('code');
       $result = array();
       $login_rec = new logindata();
       $login_rec = json_decode($request->param('details'));
       if($code != ''){
           $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
           $html = file_get_contents($url);
           $obj = json_decode($html);
           //trace($obj);
           Vendor("wxbizdatacrypt.wxBizDataCrypt");
           $pc = new \WXBizDataCrypt($appid, $obj->session_key);
           $errCode = $pc->decryptData($login_rec->encryptedData, $login_rec->iv, $data);  //其中$data包含用户的所有数据
           $data = json_decode($data,true);trace($data);
           $userdata = db('wechat_user_info')->where('openid',$data['openId'])->find();
           $uid = $userdata['id'];
           if($userdata == null) {
              $wechat_user_info = array();
              $wechat_user_info['openid'] = $data['openId'];
              $wechat_user_info['nickname'] = $data['nickName'];
              $wechat_user_info['city'] = $data['city'];
              $wechat_user_info['avatarurl'] = $data['avatarUrl'];
              db('wechat_user_info')->insert($wechat_user_info);
              $uid = db('wechat_user_info')->getLastInsID();
           }
           $result_data['token'] = md5($data['openId']);
           $result_data['openid'] = $data['openId'];
           $result_data['avatarUrl'] = $data['avatarUrl'];
           $result_data['nickName'] = $data['nickName'];
           $result_data['uid'] = $uid;
           $result['code'] = $errCode;
           $result['data'] = $result_data;
           return json($result);
       }
    }

    function object_to_array($obj){
        if(is_array($obj)){
           return $obj;
        }
        $_arr = is_object($obj)? get_object_vars($obj) :$obj;
        foreach ($_arr as $key => $val){
        $val=(is_array($val)) || is_object($val) ? object_to_array($val) :$val;
        $arr[$key] = $val;
        }
        return $arr;
    }

    public function shopsublist() {
        $result = array();
        $data = db('shopsublist')->select();
        $shopsub = new shopsub();
        $shopsub->set_data($data);
        trace(json($shopsub));
        return json($shopsub);
        //echo json_encode($data);
    }

    public function shopsub_phone_list() {
        $result = array();
        $data = db('shopsublist_phone')->select();
        foreach ($data as $key => $value) {
          array_push($result , $value['linkPhone']);
        }
        return json($result);
        //echo json_encode($data);
    }

    public function category() {
        $categories = new categories();
        $data = db('categories')->where('user','admincdz')->where('status' ,'1')->order('sort asc')->select();
        $categories->set_data($data);
        $categories->set_result('0','sucess');
        return json($categories);
    }

    public function goods_list() {
        $goods = new goods();
        $data = db('wechat_goods_list')->where('user','admincdz')->where('status' , '1')->select();
        $goods->set_result(0,'goodsdata');
        $goods->set_data($data);
        return json($goods);
    }

    public function shopsub_display_video() {
        $result = array();
        $data = db('wechat_shop_display_video')->where('user' , 'admincdz')->find();
        /*trace($data);
        foreach ($data as $key => $value) {
          array_push($result , $value['src']);
        }*/
        return json($data['src']);
        //echo json_encode($data);
    }

    public function manger_banner() {
        $request = request();
        $flag = $request->param('flag');
        $result = array();
        $result['success'] = true;
        if($flag == "del") {
            $data = db('wechat_upload_banner_img')->where('id',$request->param('id'))->find();
            db('wechat_upload_banner_img')->where('id',$request->param('id'))->delete();
            unlink(ROOT_PATH . 'wechat_banner_img' . DS . $data['save_dir']);
        } else if($flag == "sort") {
            $data = db('wechat_upload_banner_img')->where('id',$request->param('id'))
                                                  ->update(['sort'=>$request->param('sort')]);
        } else {
          $data = db('wechat_upload_banner_img')->where('id',$request->param('id'))
                                                ->update(['status'=>'0']);
        }
        return $result;
    }

    public function add_banner() {
        return view('add_banner');
    }

    protected function check_user_pwd($user,$password) {
        $data = db('user_info')->where('user_name',$user)
                               ->where('passwd',$password)
                               ->find();
        if($data == null) {
            return false;
        } else {
            return true;
        }

    }

    public function upload_banner_img() {
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

    public function get_data() {
      $request = request();
      $data = db('wechat_upload_banner_img')->where('user' , session('user'))
                                    ->page($request->param('page'), $request->param('limit'))
                                    ->order('sort asc')
                                    ->select();
      return array('data'=>$data,'code' => 0,'count'=> db('wechat_upload_banner_img')->where('user' , session('user'))
                                                                      ->count());

    }

    public function upload_banner_save() {
        $request = request();
        $upload_banner_dir = ROOT_PATH . 'wechat_banner_img';//用户区
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
        $file_data['pic_name'] = $request->param('pic_name');
        $file_data['user'] = session('user');
        $file_data['sort'] = $request->param('sort');
        $file_data['save_dir'] = $file_name;
        db('wechat_upload_banner_img')->insert($file_data);
        $result['success'] = true;
        $result['msg'] = "!!!";
        return $result;
    }


}

/**
 *
 */
 class banner_data
 {
     public $src= array();
     function set_src($imgsrc) {
         $this->src = "http://boring1996.cn:8080/bannerimg/".$imgsrc;
     }
 }

class banner
{
    public $code='';
    public $msg='';
    public $data = array();
    function set_data($imgsrc) {
      foreach ($imgsrc as $key => $value) {
        $banner_data = new banner_data();
        $banner_data->set_src($value['save_dir']);
        array_push($this->data,$banner_data);
      }
    }
    function set_result($code , $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }
}

class shopsub_data {
    public $id='';
    public $name='';
    public $address='';
    public $openingHours='';
    public $latitude ='';
    public $longitude ='';
    public $businesscontent ='';
    public $linkphone = array();

    function set_phone_data($phone_data) {
        foreach ($phone_data as $key => $value) {
          array_push($this->linkphone,$value['linkPhone']);
        }
    }
}

class shopsub
{
    public $code='';
    public $msg = '';
    public $data = array();
    function set_data($data) {
        foreach ($data as $key => $value) {
          $shopsub_obj = new shopsub_data();
          $shopsub_obj->id = $value['id'];
          $shopsub_obj->address = $value['address'];
          $shopsub_obj->name = $value['name'];
          $shopsub_obj->openingHours = $value['openingHours'];
          $shopsub_obj->latitude = $value['latitude'];
          $shopsub_obj->longitude = $value['longitude'];
          $shopsub_obj->businesscontent = $value['businesscontent'];
          $phone_data = db('shopsublist_phone')->where('shop_id' , $value['id'])->select();
          $shopsub_obj->set_phone_data($phone_data);

          array_push($this->data , $shopsub_obj);
        }
    }
}

class categories_data {
    public $name = '';
    public $sort ='';
    public $id ='';
    function set_data($name, $sort, $dataid) {
        $this->name = $name;
        $this->sort = $sort;
        $this->id = $dataid;
    }
}
class categories {
    public $code='';
    public $msg = '';
    public $data = array();

    function set_result($code, $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }

    function set_data($data) {
        foreach ($data as $key => $value) {
            $categories_data = new categories_data();
            $categories_data->set_data($value['name'],$value['sort'],$value['dataid']);
            array_push($this->data,$categories_data);
        }
    }
}

class goods_list {
    public $categoryid;
    public $name;
    public $price;
    public $src;
    public $status;
    public $tab;
    public $id;

    function set_data($id,$categoryid ,$price ,$src ,$status ,$name,$tab) {
        $this->categoryid = $categoryid;
        $this->price = $price;
        $this->src = "http://boring1996.cn:8080/goodsimg/".$src;
        $this->status = $status;
        $this->name = $name;
        $this->tab = $tab;
        $this->id = $id;
    }
}

class goods {
    public $code='';
    public $msg = '';
    public $data = array();

    function set_result($code, $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }

    function set_data($data) {
        foreach ($data as $key => $value) {
            $goods_list = new goods_list();
            $goods_list->set_data($value['id'],$value['categoryid'],$value['price'],$value['save_dir'],$value['status'],$value['name'],$value['tab']);
            array_push($this->data,$goods_list);
        }

    }
}

class userinfo {
    public $avatarUrl;
    public $city;
    public $country;
    public $gender;
    public $language;
    public $nickName;
    public $province;

}
class logindata {
    public $encryptedData;
    public $iv;
    public $rawData;
    public $signature;
    public $userInfo;
}

class orderlist_data {
    public $msg;
    public $time;
    //public $orderList;
}

class orderlist {
    public $code='';
    public $msg = '';
    public $data = array();

    function set_result($code, $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }

    function set_data($data) {
        foreach ($data as $key => $value) {
            $orderlist_data = new orderlist_data();
            $orderlist_data->time = date("Y-m-d H:i", $value["time"]);
            $orderlist_data->msg = $value["msg"];
            array_push($this->data,$orderlist_data);
        }
    }
}

class safe_img_list_data {
    public $title;
    public $imglist = array();
    public $text;

    function set_imglist($page_id) {
      $data = db('wechat_lifesafe_pic')->where('page_id',$page_id)
                                       ->where('status','1')
                                       ->order('sort asc')->select();
      foreach ($data as $key => $value) {
        $temp_data = array();
        $temp_data['src'] = "http://boring1996.cn:8080/safeimg/".$value['save_name'];
        $temp_data['user'] = "asd";
        array_push($this->imglist,$temp_data);
      }
    }

}
class safe_img_list {
    public $code='';
    public $msg = '';
    public $data = array();

    function set_result($code, $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }

    function set_data($data) {
        foreach ($data as $key => $value) {
            $text = db('wechat_lifesafe_text')->where('page_id',$value["id"])->find();
            $safe_img_list_data = new safe_img_list_data();
            $safe_img_list_data->title = $value["title"];
            $safe_img_list_data->text = $text["text"];
            $safe_img_list_data->set_imglist($value["id"]);
            array_push($this->data,$safe_img_list_data);
        }
    }

}
