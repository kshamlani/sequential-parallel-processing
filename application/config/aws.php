<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  AWS details
| -------------------------------------------------------------------
*/

$config['aws_key'] = '';
$config['aws_secret'] = '';
$config['aws_sns_region'] = 'us-east-1';
$config['aws_sns_android_arn'] = '';
$config['aws_sns_ios_arn'] = '';

if(ENVIRONMENT == "production"){
    $config['aws_sns_android_arn'] = '';
    $config['aws_sns_ios_arn'] = '';
}
elseif(ENVIRONMENT == "testing" || ENVIRONMENT == "development"){
    $config['aws_sns_android_arn'] = '';
    $config['aws_sns_ios_arn'] = '';
}

