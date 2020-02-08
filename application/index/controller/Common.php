<?php
namespace app\index\controller;

use think\Controller;
class Common extends Controller
{

    public function _initialize() {
        $request = request();
        $sysmgmt = db('sysmgnt')->where('notice', "关闭系统登入")->find();
        if ($sysmgmt['action'] != 0 && session('user') != 'admin') {
            $this->error("系统维护中，暂停使用");
        }
        if (cookie('PHPSESSID')) {
            if (session('?user') && session('?time')) {
                if (time() - session('time') > 1800) {
                    // 会话超时
                    $this->redirect($request->domain() . '/Index/Index/index', 302);

                } else {
                    $user = db('user_info')->where('user_name' , session('user'))->find();
                    if($user['activ'] == 0) {
                        $this->error("邮箱未激活");
                    }
                    session('time', time());
                }
            } else {
                $this->redirect($request->domain() . '/Index/Index/index', 302);
            }
        } else {
            //初始化session
            session([ ]);
            $this->redirect($request->domain() . '/Index/Index/index', 302);
        }
    }

    function formatSizeUnits($bytes)
    {
          if ($bytes >= 1073741824)
          {
              $bytes = number_format($bytes / 1073741824, 2) . ' GB';
          }
          elseif ($bytes >= 1048576)
          {
              $bytes = number_format($bytes / 1048576, 2) . ' MB';
          }
          elseif ($bytes >= 1024)
          {
              $bytes = number_format($bytes / 1024, 2) . ' KB';
          }
          elseif ($bytes > 1)
          {
              $bytes = $bytes . ' bytes';
          }
          elseif ($bytes == 1)
          {
              $bytes = $bytes . ' byte';
          }
          else
          {
              $bytes = '0 bytes';
          }

          return $bytes;
        }

}
