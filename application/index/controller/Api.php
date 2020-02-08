<?
namespace app\index\controller;
use think\Controller;

class Api extends Controller
{
      public function getName(){
             return "菜鸟教程";
         }

     public function getUrl(){
         return "www.runoob.com";
     }
}
