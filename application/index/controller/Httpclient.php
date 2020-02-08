<?php
namespace app\index\controller;

use think\Controller;
class Httpclient extends Common
{
    public function client() {
        try{
        // non-wsdl方式调用web service
        // 创建 SoapClient 对象
        $soap = new \SoapClient(APP_PATH.'Test.wsdl',array('trace'=>true,'cache_wsdl' => WSDL_CACHE_NONE));
        // 调用函数

        $parms = array(
              'EXEC_STATE' => "hujun",
             'PICTURE' => 'pictures',
             'TASK_NO' => 'task_nos',
             );
        //$result = $soap->receiveTaskResult($parms);
        $standPointInfoArray = array(
             array(
             'POINTDES' => 'hujun',
             'STANDPOINT' => 'standPoint',
             'STOR_TYPE' => 'storType',
             'WH_NO' => 'whNo1',
             ),
             array(
             'POINTDES' => 'hujun',
             'STANDPOINT' => 'standPoint',
             'STOR_TYPE' => 'storType',
             'WH_NO' => 'whNo2',
             ),
         );
        $result = $soap->receiveStandPointInfo($standPointInfoArray);
        trace($result);

        trace($soap->__getFunctions());
        //trace($result1);
        //trace($result2);
         /*var_dump($soap->__getFunctions());
         print("<br/>");
         var_dump($soap->__getTypes());//打印对应方法的参数和参数类型
         print("<br/>"); */
        //$result2 = $soap->__soapCall("getUrl",array());
        //echo $result1."<br/>";
        //echo $result2;
      } catch(SoapFault $e){
        echo $e->getMessage();
      }catch(Exception $e){
        echo $e->getMessage();
      }
    }
}
