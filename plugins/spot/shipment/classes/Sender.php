<?php namespace Spot\Shipment\Classes;

#use Services_Twilio;
use Twilio\Rest\Client as Services_Twilio;
use Exception;
use Tiipiik\SmsSender\Models\Setting;
use Tiipiik\SmsSender\Models\MessageHistory;

/*
        steps for Twilio Integration:
        1- sign up in Twilio and get (SID, Token, phone number for the test)
        2- in your Twilio account go to (path: programmable SMS/SMS/settings/geo permission) and choose number key for all countries you will use it
        3-follow steps in Twilio rest API:
        install Twilio SDK by composer or manually in Composer.json file include rest SDK in your code(example:use Twilio\Rest\Client)
        *write in your code:
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
            $to_number,
            array(
                'from' => $from_number,
                'body' => 'I sent this message in under 10 minutes!'
            )
        );
*/

class Sender
{
    /*
     * Send SMS trough provider
     * @param integer the phone number to send the message
     * @param string the test message to send
     * return bool
     *
     */
    public static function sendMessage($to, $message)
    {
        /* Find settings for provider datas */
        $gateway = Setting::get('gateway');
        $from = Setting::get('from'); // Need to change that name

        if ($gateway == 'clickatell') {
            $providerUsername = Setting::get('clickatell_user_name');
            $providerPasswd = Setting::get('clickatell_passwd');
            $providerApiId = Setting::get('clickatell_api_id');
            $baseUrl = Setting::get('clickatell_base_url');
            $sessId = '';
            $codeStatus = '';
            $text = $message;
            $text = self::hex_chars($text);
            $text = urlencode($text);

            // auth call
            $url = $baseUrl.'/http/auth?user='.$providerUsername.'&password='.$providerPasswd.'&api_id='.$providerApiId;

            // do auth call
            $ret = file($url);

            // explode our response. return string is on first line of the data returned
            $sess = explode(':', $ret[0]);

            $url = $baseUrl.'/http/sendmsg?session_id='.$sessId.'&to='.$to.'&unicode=1&text='.$text;

            $status = '';

            if ($sess[0] == 'OK') {
                $sessId = trim($sess[1]); // remove any whitespace
                $url = "$baseUrl/http/sendmsg?session_id=$sessId&to=$to&unicode=1&text=$text";

                // do sendmsg call
                $ret = file($url);
                $send = explode(':', $ret[0]);

                if ($send[0] == "ID") {
                    // All is fine, the message is sent
                    $codeStatus = 1;
                    $status = 'Sent';
                    $sessId = $send[1];

                } else {
                    // Hum, sending failed
                    $codeStatus = 2;
                    $status = $ret[0];
                }
            } else {
               // Authentication failure, got to check Api Id, user credentials and co.
                $codeStatus = 3;
                $status = $ret[0];
            }
            // Store datas in table
            $messageDatas = [
                'from' => $from,
                'to' => $to,
                'sess_id' => $sessId,
                'message' => $message,
                'status' => $status,
                'short_status' => (int) $codeStatus,
            ];
            MessageHistory::saveHistory($messageDatas);
        } elseif ($gateway == 'twilio') {
            $providerAccountSid = Setting::get('twilio_sid');
            $providerAuthToken = Setting::get('twilio_token');
            $codeStatus = 2;
            $status = '';
            $text = $message;
            $messageId = 0;

            $client = new Services_Twilio($providerAccountSid, $providerAuthToken);

            $message = $client->messages->create(
                    // Where to send a text message (your cell phone?)
                    $to,
                    array(
                        'from' => $from,
                        'body' => $text
                    )
                );
                $codeStatus = 1;
                $status = 'Sent';
                $messageId = $message->sid;

            // try {
            //     $message = $client->messages->create(
            //         // Where to send a text message (your cell phone?)
            //         $to,
            //         array(
            //             'from' => $from,
            //             'body' => $text
            //         )
            //     );
            //     $codeStatus = 1;
            //     $status = 'Sent';
            //     $messageId = $message->sid;

            // }
            // catch (Services_Twilio_RestException $e) {
            //     echo $e->getMessage();
            // }

            // Store datas in table
            $messageDatas = [
                'from' => $from,
                'to' => $to,
                'message' => $message,
                'sess_id' => $messageId,
                'status' => $status,
                'short_status' => (int) $codeStatus,
            ];
            MessageHistory::saveHistory($messageDatas);
        } elseif ($gateway == 'smsala') {
            $api_id                 = Setting::get('smsala_api_id');
            $api_password           = Setting::get('smsala_api_password');
            $sms_type               = Setting::get('smsala_sms_type');
            $encoding               = Setting::get('smsala_encoding');
            $sender_id              = Setting::get('smsala_sender_id');
            $sender_id              = urlencode($sender_id);
            $codeStatus             = 2;
            $status                 = '';
            $text                   = $message;
            $text                   = urlencode($text);
            $messageId              = 0;
            if (substr($to, 0, 2) == '00') {
                $to                     = substr($to, 2);
            }else{
                $to                     = $to;
            }
            $serviceurl             = 'http://api.smsala.com/api/SendSMS?api_id='.$api_id.'&api_password='.$api_password.'&sms_type='.$sms_type.'&encoding='.$encoding.'&sender_id='.$sender_id.'&phonenumber='.$to.'&textmessage='.$text;

            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $serviceurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $curl_response = curl_exec($ch);
            curl_close($ch);
            $curl_response = json_decode($curl_response);
            $codeStatus = 1;

            // Store datas in table
            $messageDatas = [
                'from' => $from,//
                'to' => $to, //
                'message' => $message,
                'sess_id' => $curl_response->message_id,
                'status' => $curl_response->status,
                'short_status' => (int) $codeStatus,
            ];
            MessageHistory::saveHistory($messageDatas);
        }

        ($codeStatus == 1 ? true : false);
    }

    /*
     * Get all messages
     * @param int number of days to get messages
     * @param int status of messages (display only sent or failed messages)
     *
     * return array messages
     *
     */
    public static function getMessages($days, $rows, $status)
    {
        // Convert days to correct date formatting
        $fromDays = date("Y-m-d 00:00:01", time() - (1 * ($days * 24) * 60 * 60));

        $messages = MessageHistory::where('created_at', '>', $fromDays);
        if ($status != 0) {
            $messages = $messages->where('short_status', '!=', $status);
        }
        $messages = $messages->orderBy('created_at', 'desc')
            ->take($rows)
            ->get();

        return $messages;
    }

    /*
     * Transform text to unicode usable by Clickatell, witch seems to use specific unicode
     * @param string text to convert
     * @return string text converted
     *
     * Find on Internet, but I have lost the page.
     */
    public static function hex_chars($data)
    {
        $mb_hex = '';
        for ($i = 0; $i < mb_strlen($data, 'UTF-8'); $i++) {
            $c = mb_substr($data, $i, 1, 'UTF-8');
            $o = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
            $mb_hex .= sprintf('%04X', $o[1]);
        }
        return $mb_hex;
    }
}
