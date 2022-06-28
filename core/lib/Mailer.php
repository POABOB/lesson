<?php
namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
	public function __construct() {
        $this->mailer = new PHPMailer(true);

        //Server settings
        $this->mailer->CharSet = "UTF-8";
        // $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mailer->SMTPDebug = false;
        // $this->mailer->isSMTP();                                            //Send using SMTP
        // $this->mailer->Host       = config::get('host', 'mailer');                     //Set the SMTP server to send through
        // $this->mailer->SMTPAuth   = true;                                   //Enable SMTP authentication
        // $this->mailer->Username   = config::get('username', 'mailer');                     //SMTP username
        // $this->mailer->Password   = config::get('password', 'mailer');                               //SMTP password
        // $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        // $this->mailer->Port       = config::get('port', 'mailer');                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mailer->isSMTP();
        $this->mailer->Host = 'localhost';
        $this->mailer->SMTPAuth = false;
        $this->mailer->SMTPAutoTLS = false; 
        $this->mailer->Port = 25; 
        $this->mailer->Encoding = 'base64';
        
    }
    
    public function setMessage($header = '', $from = '', $to = '', $body = '', $attach = null) {
        //Recipients
        $this->mailer->addAddress($from);     //Add a recipient\
        $this->mailer->addCC($to);
        $this->mailer->addBCC('m0920173456@gmail.com');
        $this->mailer->addReplyTo($to);
        $this->mailer->setFrom($to);
        //Content
        $this->mailer->isHTML(true);                                  //Set email format to HTML
        $this->mailer->Subject = $header;
        $this->mailer->Body    = $body;
        if($attach != null) {
            $this->mailer->addAttachment($attach);         //Add attachments
        }
    }

    public function send() {
        try {
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
        }
	}
}