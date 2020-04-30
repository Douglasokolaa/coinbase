<?php

use CoinbaseCommerce\Resources\Charge;

defined('BASEPATH') or exit('No direct script access allowed');

class Coinbase extends App_Controller
{
    public function cancelled($id,$hash)
    {
        check_invoice_restrictions($id,$hash);
        set_alert('warning','payment cancelled');
        redirect(site_url(`invoice/{$id}/{$hash}`));
    }

    public function success($id,$hash)
    {
        check_invoice_restrictions($id,$hash);
        set_alert('success','payment successfull');
        redirect(site_url(`invoice/{$id}/{$hash}`));
    }
}