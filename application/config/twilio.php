<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API details
| -------------------------------------------------------------------
|
|  twilio_account_sid                 string   ACCOUNT SID
|  twilio_auth_token             string   Account Token
|  twilio_phone_number              string   Twilio phone number
|
|
*/

$config['twilio_account_sid']         = 'XX';
$config['twilio_auth_token']     = 'XX';
$config['twilio_phone_number']      = 'XX';
$config['twilio_service_id'] = 'XX';
$config['twilio_api_key_sid'] = 'XX';
$config['twilio_api_key_secret'] = 'XX';

if(ENVIRONMENT == 'development'){
    $config['twilio_service_id'] = 'XX';
    $config['twilio_account_sid']         = 'XX';
    $config['twilio_auth_token']     = 'XX';
    $config['twilio_phone_number']      = 'XX ';
    $config['twilio_api_key_sid'] = 'XX';
    $config['twilio_api_key_secret'] = 'XX';
}


