<?php
namespace app\index\controller;

use think\Controller;
class Httpserver extends Controller
{
    /**
     *    返回网站名称
     *    @return string
     *
     */
     public function server(){
        ini_set('soap.wsdl_cache_enabled','0');    //关闭WSDL缓存
        $server = new \SoapServer(APP_PATH.'Test.wsdl',array('cache_wsdl' => WSDL_CACHE_NONE));

        //Log::record("=====================server==========".date('Y-m-d H:i:s'),"info");
        $class = 'app\index\controller\Payment';
        $server->setClass($class);
        $server->handle();
        //trace($server);
        /*$soapXml = ob_get_contents();
        //Log::record($soapXml,'info');
        ob_end_clean();
        $soapXml = trim($soapXml);
        //去掉特殊字符 不然客户端可能解析不了
        $soapXml = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $soapXml);
        $length = strlen($soapXml);
        header("Content-Length: ".$length);
        return $soapXml;*/
    }
}







?>
