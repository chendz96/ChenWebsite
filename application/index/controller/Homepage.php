<?php
namespace app\index\controller;
class Homepage extends Common
{
    public function index()
    {        
      $this->assign("session" , session("user"));
      return view();
    }
}
