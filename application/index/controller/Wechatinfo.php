<?php
namespace app\index\controller;
use think\Db;
class Wechatinfo extends Common
{
    public function index() {
        return view();
    }

    public function add_shopinfo() {
        return view('add_shopinfo');
    }

    public function get_data() {

        $sql = "SELECT
                	*
                FROM
              	`shopsublist` `t1`
                	INNER JOIN (
              		SELECT
              		GROUP_CONCAT(`linkPhone`) as `linkPhone`,
              		`shop_id`
              		FROM
              			`shopsublist_phone`
              		GROUP BY `shop_id`
                	) as `t2` ON `t2`.`shop_id` = `t1`.`id`
                  WHERE
  	              `t1`.`user` = '" . session('user') . "'";

        $data = Db::query($sql);
        
        return array('data'=>$data,'code' => 0,'count'=> count($data));
    }

    public function del_shopinfo() {
        $request = request();
        $id = $request->param('id');
        db('shopsublist')->where('id',$id)->delete();
        db('shopsublist_phone')->where('shop_id',$id)->delete();
        $result['success'] = true;
        return $result;
    }

    public function shopinfo_save() {
        $request = request();
        $data = array();
        $data['name'] = $request->param('name');
        $data['user'] = session('user');
        $data['address'] = $request->param('address');
        $data['longitude'] = $request->param('longitude');
        $data['latitude'] = $request->param('latitude');
        $data['openingHours'] = $request->param('openingHours');
        $data['businesscontent'] = $request->param('businesscontent');
        db('shopsublist')->insert($data);
        $lastid = db('shopsublist')->getLastInsID();

        $phone_num = $request->param('phone_num');
        for($i=1;$i<=$phone_num;$i++) {
            $phone_data = array();
            $phone_data['linkPhone'] = $request->param('linkPhone'.$i);
            $phone_data['shop_id'] = $lastid;
            db('shopsublist_phone')->insert($phone_data);
        }

        $result['success'] = true;
        return $result;
    }


}
