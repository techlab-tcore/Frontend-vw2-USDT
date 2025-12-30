<?php namespace App\Models;

use CodeIgniter\Model;

class Sms_model extends Model
{
    protected $sms = 'http://10.148.15.251:8961/sms/sendsms';

    protected $whatsapp = 'https://verifyme.asia/api/create-message';

    protected $email = 'https://email.api.engagelab.cc/v1/mail/send';

    protected $sendwhatsapp = 'http://190.92.217.78:8001/sms/api/sendMessageMass';

    public function __construct()
	{
		$this->db = db_connect();
	}

    public function insertWhatsapp($where)
	{       
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->whatsapp,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'appkey' => '3e70d255-adcf-453a-b8ff-adfe1215fd98',
                'authkey' => 'k4dSND94XOt4CvqcETRkaum8LaVPd5upmRWMp6hkQK3pblbcOk',
                'to' => $where['to'],
                'message' => $where['message'],
                'sandbox' => 'false'
            ),
          ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function insertSms($where)
	{
		$data = array_merge(['lang'=>$_SESSION['lang'], 'agentid'=>$_ENV['host']], $where);
		$payload = json_encode($data);
        
        $ch = curl_init($this->sms);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function insertEmail($where)
	{
        $apiUser = "JGGzOMFDg8by_vw2_r117XS";
        $apiKey = "f43d6be813bd758411be4a7ad91d7b56";
        $auth = base64_encode("$apiUser:$apiKey");
        $tacode = $where['veritac'];

        $data = [
            "from" => "vw2@2833.online",
            "to" => [$where['to']],
            "body" => [
                "subject" => "REGISTRATION OTP",
                "content" => [
                    "text" => "Your OTP is $tacode"
                ]
            ],
        ];

        $payload = json_encode($data);

        $ch = curl_init($this->email);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization: Basic $auth"
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);

        // Get HTTP response code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $err = curl_error($ch);
        curl_close($ch);

        return $httpCode;
    }

    public function insertSendWhatsapp($where)
    {
        $username = "VWorldWAOTP";
        $password = md5("LxTZMExIblCn");
        $tacode = $where['veritac'];

        $timestamp = (int)(microtime(true) * 1000);

        $sign = md5($username . $timestamp . $password);

        $data = [
            "userName" => $username,
            "content" => $tacode,
            "phoneList" => [$where['to']],
            "timestamp" => $timestamp,
            "sign" => $sign,

        ];

        $payload = json_encode($data);

        $ch = curl_init($this->sendwhatsapp);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8"
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        
        $err = curl_error($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}