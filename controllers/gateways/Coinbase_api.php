<?php

use CoinbaseCommerce\Webhook;

use function GuzzleHttp\json_decode;

defined('BASEPATH') or exit('No direct script access allowed');

class Coinbase_api extends App_Controller
{
    public function notify()
    {
        $secret = $this->coinbase_gateway->secret();
        $headerName = 'X-Cc-Webhook-Signature';
        $headers = getallheaders();
        $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
        $payload = trim(file_get_contents('php://input'));

        try {
            $event = Webhook::buildEvent($payload, $signraturHeader, $secret);
            http_response_code(200);
            print_r(($event));

            $id = $event->metadata['id'];
            $hash = $event->metadata['hash'];
            $amount = $event->metadata['amount'];
            $transId = $event->data->code;
            check_invoice_restrictions($id,$hash);
            $this->record($amount,$id,$transId);

        } catch (\Exception $exception) {
            http_response_code(400);
            echo 'Error occured. ' . $exception->getMessage();
        }
    }

    private function record($amount, $id, $transId)
    {
        $success = $this->coinbase_gateway->addPayment(
            [
                'amount'        => $amount,
                'invoiceid'     => $id,
                'transactionid' => $transId,
                'paymentmethod' => 'Coinbases',
            ]
        );
        if ($success) {
            log_activity('online_payment_recorded_success');
            set_alert('success', _l('online_payment_recorded_success'));
        } else {
            log_activity('online_payment_recorded_success_fail_database' . var_export($this->input->get(), true));
            set_alert('success', _l('online_payment_recorded_success_fail_database'));
        }
    }
}
