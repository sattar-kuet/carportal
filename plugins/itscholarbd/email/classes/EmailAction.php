<?php 
namespace ItScholarBd\Email\Classes;
use Spot\Shipment\Models\Settings;
class EmailAction
{
   /*
      params = [message,to,attachments =>[file,name]]
   */ 

   public static function send($data = array()){
          extract($data);
          $company = Settings::get('company', true);
          $uid = md5(uniqid(time()));
          $from_name = $company['title'];
          $companyMailParts = explode('@', $company['primary_email']);
          $noReply = 'no-reply@'.$companyMailParts[1];

          $from_mail = isset($from)? $from : $noReply;
          $replyto = isset($from)? $from : $noReply;
          // header
          $header = "From: ".$from_name." <".$from_mail.">\r\n";
          if(isset($cc)){
            $header .= 'Cc: '.$cc. "\r\n";
          }
          $header .= "Reply-To: ".$replyto."\r\n";
          $header .= "MIME-Version: 1.0\r\n";
          $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
          
          
          // message 
          $emailBody = "--".$uid."\r\n";
          $emailBody .= "Content-type:text/html; charset=iso-8859-1\r\n";
          $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
          $emailBody .= $message."\r\n\r\n";
          $emailBody .= "--".$uid."\r\n";
          //attachments
          if(isset($attachments)){
            foreach ($attachments as  $attachment ) {
              extract($attachment);
  
              $content = chunk_split(base64_encode($content));
              $emailBody .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
              $emailBody .= "Content-Transfer-Encoding: base64\r\n";
              $emailBody .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
              $emailBody .= $content."\r\n\r\n";
              $emailBody .= "--".$uid."--";
            }
          }

          if(mail($to, $subject, $emailBody, $header)) {
              return true; 
          } 
          return false;      
   }

}

