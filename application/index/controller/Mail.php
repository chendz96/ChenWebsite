<?php
namespace app\index\controller;

use think\Controller;
use phpmailer\phpmailer;
class Mail extends Controller
{
    // 邮件标题
    public $title = '';
    // 收件人
    public $send_mail = '';
    // 发送内容
    public $content = '';
    public function sendEmail($data = []) {
        if ($this->send_mail == null || strlen($this->send_mail) < 5) {
	    return true;
	}
        Vendor('phpmailer.phpmailer');
        // 实例化
        $mail = new PHPMailer();
        // 启用SMTP
        $mail->IsSMTP();
        // SMTP服务器 以QQ企业邮箱为例子
        $mail->Host = 'smtp.qq.com';
        // 邮件发送端口
        $mail->Port = 465;
        // 启用SMTP认证
        $mail->SMTPAuth = true;
        // 设置安全验证方式为ssl
        $mail->SMTPSecure = "ssl";
        // 字符集
        $mail->CharSet = "UTF-8";
        // 编码方式
        $mail->Encoding = "base64";
        // 发送人邮箱
        $mail->Username = '2634314761@qq.com';
        // 发送人邮箱密码
        $mail->Password = 'imwjbqricwzcecfc';//imwjbqricwzcecfc
        // 邮件标题
        $mail->Subject = $this->title;
        // 发件人地址（也就是你的邮箱）
        $mail->From = '2634314761@qq.com';
        // 发件人姓名
        $mail->FromName = '陈德志';
        // 添加收件人（地址，昵称）
        $mail->AddAddress($this->send_mail);
        //支持html格式内容
        $mail->IsHTML(true);
        //邮件主体内容
        $mail->Body = $this->content;
        //发送成功就删除
        trace("mail title:" . $this->title);
        trace("mail to:" . $this->send_mail);
        trace("mail content:" . $this->content);
        ob_start();
        if ($mail->Send()) {
            $mail->SmtpClose();
            return true;
        }else{
            $mail->SmtpClose();
            trace("Mailer Error: " . $mail->ErrorInfo);
            ob_end_clean();
            return false;
        }
    }
}
