<?php

namespace Shetabit\Multipay\Drivers\Toman;

use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Illuminate\Support\Facades\Http;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class Toman extends Driver
{

    protected $invoice; // Invoice.

    protected $settings; // Driver settings.

    protected $base_url;

    protected $shop_slug;

    protected $auth_code;

    protected $code;

    protected $auth_token;

    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice); // Set the invoice.
        $this->settings = (object) $settings; // Set settings.
        $this->base_url = $this->settings->base_url;
        $this->shop_slug = $this->settings->shop_slug;
        $this->auth_code = $this->settings->auth_code;
        $this->code = $this->shop_slug . ':' . $this->auth_code;
        $this->auth_token  = base64_encode($this->code);
    }

    // Purchase the invoice, save its transactionId and finaly return it.
    public function purchase()
    {
        $url = $this->base_url . "/users/me/shops/" . $this->shop_slug . "/deals";
        $data = $this->settings->data;

        $response =  Http::withHeaders([
            'Authorization' => "Basic {$this->auth_token}",
            "Content-Type" => 'application/json'
        ])->post($url, $data);

        $result = json_decode($response->getBody()->getContents(), true);

        if(isset($result['trace_number'])){
            $this->invoice->transactionId($result['trace_number']);

        // return the transaction's id
        return $this->invoice->getTransactionId();
        }else{
            throw new InvalidPaymentException('پرداخت با مشکل مواجه شد، لطفا با ما در ارتباط باشید');
        }
    }

    // Redirect into bank using transactionId, to complete the payment.
    public function pay(): RedirectionForm
    {

        $transactionId = $this->invoice->getTransactionId();
        $redirect_url = $this->base_url . '/deals/' . $transactionId . '/redirect';

        return $this->redirectWithForm($redirect_url, [], 'GET');
    }

    // Verify the payment (we must verify to ensure that user has paid the invoice).
    public function verify(): ReceiptInterface
    {
        $inputs = request()->all();

        $transactionId = $this->invoice->getTransactionId();
        $verifyUrl = $this->base_url . "/users/me/shops/" . $this->shop_slug . "/deals/" . $transactionId . "/verify";

        if($inputs['state'] == 'funded'){
            $result =  Http::withHeaders([
                'Authorization' => "Basic {$this->auth_token}",
                "Content-Type" => 'application/json'
            ])->patch($verifyUrl);
            return $this->createReceipt($transactionId);
        }else{
            throw new InvalidPaymentException('پرداخت انجام نشد');
        }

    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    public function createReceipt($referenceId)
    {
        return new Receipt('toman', $referenceId);
    }
}
