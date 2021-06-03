<?php 
namespace ItScholarBd\Api\Classes;
use ItScholarBd\Api\Models\Car;
use ItScholarBd\Api\Models\UserDevice;
use ItScholarBd\Api\Models\NotificationHistory;

class Utility 
{
    public static function sendOTPforPasswordRecovery($email)
    {
        $actualCode = mt_rand(1000, 9999);
        $message  = 'Your password recovery code is : ';
        $message .= '<h1>'.$actualCode.'</h1>';

        $data = [
            'subject' => 'Password Recovery',
            'message' => $message,
            'to' => $email
          ];
        if (!EmailAction::send($data)){
            return ['status' => 0, 'msg' => 'Something is wrong.'];
        }
        self::saveOTP($email, $actualCode);
        return ['status' => 1, 'msg' => 'Verification CODE is sent successfully!. Please check your email.'];
    }
    public static function saveOTP($email, $actualCode){
        $passwordOtpObj = PasswordOTP::where('email', $email);
        $passwordOtp = $passwordOtpObj->count()?  $passwordOtpObj->first() : new PasswordOTP;
        $passwordOtp->email = $email;
        $passwordOtp->otp = $actualCode;
        $passwordOtp->status = 0;
        $passwordOtpObj->count() ? $passwordOtp->update() : $passwordOtp->save();
    }

    public static function getStatusUpdateActionList($status){
        $ctionList = [];
        $statusList = Car::STATUSLIST;
        foreach($statusList as $key => $value ){
             if($key <= $status)
              continue;
             array_push($ctionList, [ $key => $value ] ); 
        }
        return $ctionList;
    }

    public static function addNotification($data){
        $notification = new NotificationHistory;
        foreach( $data as $key => $value ){
         $notification->$key = $value;
        }
        $notification->is_read = 0;
        $notification->save();
        $response = self::sendNotification($data);
        return $response;
        //dd($response); exit;
     }

    public static function sendNotification($data)
    {
        $server_key = 'AAAAy-YHDr8:APA91bHlOcsxIvh_lyItWxe6PLq4ESt60DLtHL6Fi-Hu7cMXObV2_J4vXmIneDUxORvmGZ5bQLvRBJ1K4_R4h8S0nmZW55Yh3ARTBxiL2mtYSn1QuECUDNvTV1VNxACsnhgIEM64nQXd';
  
        extract($data);
        $records = UserDevice::where('user_id', $user_id)->get();
        $result = [];
        foreach ($records as $key => $value) {
            // dd($server_key);
            $token = $value->device_token;
            $notification['title'] = $title;
            $notification['body'] = $description;
            $notification['vibrate'] = 1;
            $notification['sound'] = 'notification.wav';
            $notification['android_channel_id'] = "500";

            $json_data = array(
                'to' => $token,
                'priority' => "high",
                'notification' => $notification,
                'data' => [
                    'key' => "value",
                ],
            );
            $json_data = json_encode($json_data);
            $url = 'https://fcm.googleapis.com/fcm/send';
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key=' . $server_key,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            $result[] = curl_exec($ch);
        }
        return $result;  
    }

}

