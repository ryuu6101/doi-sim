<?php

namespace App\Services;

class BrandNameService
{
    protected $api_accounts = [
        1 => [
            'apiuser' => 'cnpt',
            'apipass' => 'cnpt@123',
        ],
        2 => [
            'apiuser' => 'fivestar_bs',
            'apipass' => 'fivestar_bs@6666',
        ],
        3 => [
            'apiuser' => 'anhduc',
            'apipass' => 'Anhduc@6666',
        ],
        4 => [
            'apiuser' => 'cnpt',
            'apipass' => 'cnpt@7777',
        ],
    ];
    
    protected $template_ids = [
        1 => '11469719' //Welcome message
    ];

    public function __construct() {}

    public function sendSms($sdt, $template, $params = []) {
        $api_account = $this->api_accounts[4];
        $template_id = $this->template_ids[$template];

        $url = "http://113.185.0.35:8888/smsbn/api";

        $param_array = [];

        $count = 1;
        foreach($params as $param) {
            $param_array[] = [
                "NUM" => strval($count++),
                "CONTENT" => strval($param)
            ];
        }

        $data_array = [
            "RQST" => [
                "name" => "send_sms_list",
                "REQID" => "1",
                "LABELID" => "155020",
                "CONTRACTID" => "13886",
                "CONTRACTTYPEID" => "1",
                "TEMPLATEID" => $template_id,
                "PARAMS" => $param_array,
                "SCHEDULETIME" => "",
                "MOBILELIST" => $sdt,
                "ISTELCOSUB" => "1",
                "DATACODING" => "8",
                "AGENTID" => "121",
                "APIUSER" => $api_account['apiuser'],
                "APIPASS" => $api_account['apipass'],
                "USERNAME" => "DN_CS"
            ]
        ];

        // dd($data_array);

        $data_string = json_encode($data_array);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;charset=UTF-8',
            'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($result, true);

        // dd($response, $sdt, $data_array, $template_id);

        return $response;
    }

    public function sendWelcomeMessage($sdt, $params) {
        return $this->sendSms($sdt, 1, $params);
    }
}