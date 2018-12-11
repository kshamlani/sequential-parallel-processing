<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Stripe API details
| -------------------------------------------------------------------
|
|  stripe_publishable_key                 string   Publishable KEY
|  stripe_secret_key             string   Secret KEY
|
|
*/
$config['stripe_publishable_key']         = 'XX';
$config['stripe_secret_key']     = 'XX';

if(ENVIRONMENT == 'development'){
    $config['stripe_publishable_key']         = 'XX';
    $config['stripe_secret_key']     = 'XX';
}



