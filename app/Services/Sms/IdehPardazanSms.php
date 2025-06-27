<?php

namespace App\Services\Sms;

use App\Contracts\SmsContract;
use App\Contracts\SmsNotificationContract;

class IdehPardazanSms extends SmsService implements SmsContract, SmsNotificationContract
{
    public function send()
    {
        $method = $this->method();
        $data   = $this->$method();

        $body = [
            'mobile'     => $this->mobile,
            'UserApiKey' => option('IDEHPARDAZAN_PANEL_APIKEY'),
            'SecretKey'  => option('IDEHPARDAZAN_PANEL_SECRET_KEY')
        ];

        $body = array_merge($data, $body);
        $body = json_encode($body, true);

        $url     = "https://RestfulSms.com/api/UltraFastSend/direct";
        $headers = array(
            'Content-Type: application/json',
        );
        $handler = curl_init($url);

        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $body);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);

        return $response;
    }

    public function verifyCode()
    {
        return [
            'TemplateId' => option('user_verify_pattern_code_idehpardazan'),
            'ParameterArray' => [
                [
                    "Parameter"      => "code",
                    "ParameterValue" => $this->data['code']
                ]
            ],
        ];
    }

    public function userCreated()
    {
        return [
            'TemplateId' => option('user_register_pattern_code_idehpardazan'),
            'ParameterArray'   => [
                [
                    "Parameter"      => "fullname",
                    "ParameterValue" => $this->data['fullname']
                ]
            ],
        ];
    }

    public function orderPaid()
    {
        return [
            'TemplateId' => option('order_paid_pattern_code_idehpardazan'),
            'ParameterArray'   => [
                [
                    "Parameter"      => "order_id",
                    "ParameterValue" => $this->data['order_id']
                ]
            ],
        ];
    }

    public function userOrderPaid()
    {
        return [
            'TemplateId' => option('user_order_paid_pattern_code_idehpardazan'),
            'ParameterArray'   => [
                [
                    "Parameter"      => "order_id",
                    "ParameterValue" => $this->data['order_id']
                ]
            ],
        ];
    }

    public function walletAmountDecreased()
    {
        return [
            'TemplateId' => option('wallet_decrease_pattern_code_idehpardazan'),
            'ParameterArray'   => [
                [
                    "Parameter"      => "amount",
                    "ParameterValue" => $this->data['amount']
                ]
            ],
        ];
    }

    public function walletAmountIncreased()
    {
        return [
            'TemplateId' => option('wallet_increase_pattern_code_idehpardazan'),
            'ParameterArray'   => [
                [
                    "Parameter"      => "amount",
                    "ParameterValue" => $this->data['amount']
                ]
            ],
        ];
    }
}
