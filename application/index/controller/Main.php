<?php
namespace app\index\controller;
use think\cache\driver\Redis;
class Main extends Common
{
    public function index() {
        $redis = new Redis();
        $query_num = $redis->get('query_num');
        $this->assign("query_num" , $query_num);
        return view();
    }

}
