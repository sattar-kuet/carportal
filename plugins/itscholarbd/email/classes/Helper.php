<?php 
namespace ItScholarBd\Email\Classes;
class Helper
{
   /*
      params = [message,to,attachments =>[file,name]]
   */ 

   public static function send($data = array()){
          extract($data);
          $uid = md5(uniqid(time()));
          $from_name = 'Comany Name';
          $from_mail = 'no-reply@comanymail.com';
          $replyto = 'no-reply@comanymail.com';
          // header
          $header = "From: ".$from_name." <".$from_mail.">\r\n";
          $header .= "Reply-To: ".$replyto."\r\n";
          $header .= "MIME-Version: 1.0\r\n";
          $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
          
          // message 
          $message = "--".$uid."\r\n";
          $message .= "Content-type:text/plain; charset=iso-8859-1\r\n";
          $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
          $message .= $message."\r\n\r\n";
          $message .= "--".$uid."\r\n";
          //attachments
          if(isset($attachments)){
            foreach ($attachments as  $attachment ) {
              extract($attachment);
              $content = file_get_contents( $file);
              $content = chunk_split(base64_encode($content));
              $name = basename($file);
              $message .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
              $message .= "Content-Transfer-Encoding: base64\r\n";
              $message .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
              $message .= $content."\r\n\r\n";
              $message .= "--".$uid."--";
            }
          }

          if(mail($to, $subject, $message, $header)) {
              return true; 
          } 
          return false;      
   }

}

