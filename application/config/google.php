<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API details
| -------------------------------------------------------------------
|
|  google_client_id                 string   Your Google Client ID.
|  google_client_secret             string   Your Google Client secret.
|  google_redirect_url              string   Set Redirect login url
|  google_application_name          string   Google Application name
|  google_scope                     array   scope to access variables
|
*/

$config['google_client_id']         = '174874852077-h0ea1o87rlonp9m79j500fm8fkh65c40.apps.googleusercontent.com';
$config['google_client_secret']     = 'Js71hOHKVMXwNIFRVhlPu6B8';
$config['google_redirect_url']      = 'http://localhost/template/www/social/login_google';
$config['google_application_name']  = 'codeigniter_template';
$config['google_scopes']            = ['email', 'profile'];

