<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        // $url = "http://113.185.0.35:8888/smsbn/api";
        $url = "http://123.31.36.151:8888/smsbn/api";

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

        $headers = ['Content-Type' => 'application/json;charset=UTF-8'];
        $options = ['verify' => false];
        $response = Http::withHeaders($headers)->withOptions($options)->post($url, $data_array)->json();

        // dd($response, $sdt, $data_array, $template_id);

        return $response;
    }

    public function sendWelcomeMessage($sdt, $params) {
        return $this->sendSms($sdt, 1, $params);
    }
}