<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Coinbase Payment   
Description: cryptocurrency payment for Perfex CRM's invoices
Version: 1.0
Author: Boxvibe technologies
Author URI: https://boxvibe.com/support
Requires at least: 2.4.4
*/

require( __DIR__ . '/vendor/autoload.php');


register_payment_gateway('coinbase_gateway','coinbase');

