<?php

/**
 * Author: Derek
 * Date: 2018.10
 */
class Sms {

    private $smsApiUrl = "https://rest.nexmo.com/sms/json";

    public function __construct() {
        //
    }

    public function sendSMS($text, $mobile) {
        $sms = [
            'from' => '12362665800', //'Peak Power', '12362665800'
            'text' => $text,
            'to' => $mobile,
            'api_key' => 'cd2571b8',
            'api_secret' => 'a2ab46c91db75a36'
        ];
        $postData = json_encode($sms);
        $resp = $this->postHttpCurl($postData);
        return $resp;
    }

    private function postHttpCurl($data) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->smsApiUrl,
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
                "Accept: application/json",
                "Content-Type: application/json",
                "Postman-Token: f1d1f005-e8fb-46d7-8043-97cbb2842847",
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

}
