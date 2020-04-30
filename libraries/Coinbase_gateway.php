<?php
defined('BASEPATH') or exit('No direct script access allowed');

use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use Stripe\ApiOperations\Retrieve;

class Coinbase_gateway extends App_gateway
{
    public function __construct()
    {
        $this->ci = &get_instance();

        /**
         * REQUIRED
         * Gateway unique id
         * The ID must be alpha/alphanumeric
         */
        $this->setId('coinbase');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Coinbase');

        /**
         * Add gateway settings
         */
        $this->setSettings(
            [
                [
                    'name'      => 'api_Key',
                    'encrypted' => true,
                    'label'     => 'Api Key',
                ],
                [
                    'name'      => 'webhook_secret',
                    'encrypted' => true,
                    'label'     => 'Shared  Secret',
                ],
                [
                    'name'      => 'webhook_endpoint',
                    'label'     => 'endpoint',
                    'default_value'     => site_url('coinbase/gateways/coinbase_api/notify'),
                    'field_attributes' => ['disabled' => true],
                ],
                [
                    'name'             => 'currencies',
                    'label'            => 'settings_paymentmethod_currencies',
                    'default_value'    => 'USD',
                ],
            ]
        );
    }

    public function process_payment($data)
    {
        $id = $data['invoiceid'];
        $hash = $data['hash'];
        $cancel_url = site_url(`coinbase/cancelled/{$id}/{$hash}`);
        $redirect_url = site_url(`coinbase/success/{$id}/{$hash}`);

        $invoiceNumber = format_invoice_number($id);

        $apiClientObj = ApiClient::init($this->api_key());
        $apiClientObj->verifySsl(false);
        $chargeObj = new Charge();

        $chargeObj->name = $invoiceNumber;
        $chargeObj->description = `payment for Invoice {$invoiceNumber}`;
        $chargeObj->local_price = [
            'amount' => $data['amount'],
            'currency' => $data['invoice']->currency_name,
            'invoiceId' => $id,
            'hash' => $hash,
        ];
        $chargeObj->pricing_type = 'fixed_price';
        $chargeObj->cancel_url   = $cancel_url;
        $chargeObj->redirect_url   =  $redirect_url;

        try {
            $chargeObj->save();
        } catch (\Exception $exception) {
            set_alert("Unable to create charge. Error: %s \n", $exception->getMessage());
        }

        if ($chargeObj->id) {

            try {
                $retrievedCharge = Charge::retrieve($chargeObj->id);
                header('Location: ' . $retrievedCharge['hosted_url']);
            } catch (\Exception $exception) {
                set_alert("Unable to retrieve charge. Error: %s \n", $exception->getMessage());
            }
        }
    }

    public function api_key()
    {
        return $this->decryptSetting('api_key');
    }

    public function secret()
    {
        return $this->decryptSetting('webhook_secret');
    }
}
