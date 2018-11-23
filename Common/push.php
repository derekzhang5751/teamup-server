<?php

/**
 * Author: Derek
 * Date: 2018.10
 */
class Push {

    //private $pushApiUrl = "https://fcm.googleapis.com/v1/projects/peak-bip/messages:send";
    private $pushFcmUrl = "https://fcm.googleapis.com/fcm/send";
    
    //private $pushApnUrl = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
    private $pushApnUrl = "ssl://gateway.push.apple.com:2195"; // For Product
    private $pem = '/var/www/html/iot-client-serve/cert/apns_product.pem';
    private $pass = 'Peakpower123!';

    public function __construct() {
        //
    }

    public function sendMsg($token, $title, $message, $data, $pushType) {
        if ($pushType == 'APN') {
            $resp = $this->sendAPNMessage($token, $title, $message, $data);
        } else if ($pushType == 'FCM') {
            $resp = $this->sendFCMMessage($token, $title, $message, $data);
        } else {
            $resp = false;
        }
        return $resp;
    }
    
    private function sendFCMMessage($token, $title, $message, $data) {
        $pushData = [
            'name' => '',
            'priority' => 'NORMAL',
            'data' => $data,
            'notification' => [
                'title' => $title,
                'body' => $message
            ],
            'to' => $token
        ];
        $postData = json_encode($pushData);
        $resp = $this->postFcmHttpCurl($postData);
        return $resp;
    }

    private function postFcmHttpCurl($data) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->pushFcmUrl,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "Authorization: key=AIzaSyADVmLxkueHFdSoRFLX23bVWYTJ1_L0fU4",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            //echo "cURL Error #:" . $err;
            return false;
        } else {
            //echo $response;
            return $response;
        }
    }

    private function sendAPNMessage($token, $title, $message, $data) {
        $pushData = [
            'name' => '',
            'priority' => 'NORMAL',
            'data' => $data,
            'aps' => [
                'alert' => $message,
                'badge' => 5,
                'sound' => 'default'
            ],
            'to' => $token,
            'url' => ''
        ];
        //$postData = json_encode($pushData);
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $this->pem);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->pass);
        $fp = stream_socket_client($this->pushApnUrl, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            echo "Failed to connect $err $errstr";
            return FALSE;
        }
        //print "Connection OK\n";
        $payload = json_encode($pushData);
        $msg = chr(0) . pack("n",32) . pack("H*", str_replace(' ', '', $token)) . pack("n",strlen($payload)) . $payload;
        //echo "sending message :" . $payload ."\n";
        fwrite($fp, $msg);
        fclose($fp);
        
        return TRUE;
    }

}
