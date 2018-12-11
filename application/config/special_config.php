<?php

/* developer mode */
$config['developer_mode_on']=true;

/* jquery version */
define('JQUERY_VER','1.11.3');
define('JQUERYUI_VER','1.11.4');

/* bootstrap version */
define('BOOTSTRAP_VER','3.3.5');

/* OAuth2 keys */
$config['facebook']['key'] = '';
$config['facebook']['secret']='';

$config['google']['key']='';
$config['google']['secret']='';

/* Tracker config */
$config['HISTORY_LIMIT_DAYS'] = 14;


/**
 * DO NOT EDIT
 *
 * These settings are based on definitions above
 *
 * If you really want to edit you better know what you are doing
 *
 */
if($config['developer_mode_on']){
    define('JS_PATH',$this->config['base_url']."debug/js/");
    define('JS_EXT',".js");
    define('CSS_PATH',$this->config['base_url']."debug/css/");
    define('CSS_EXT',".css");
    define('IMG_PATH',$this->config['base_url']."debug/img/");
}else{
    define('JS_PATH',$this->config['base_url']."release/js/");
    define('JS_EXT',".min.js");
    define('CSS_PATH',$this->config['base_url']."release/css/");
    define('CSS_EXT',".min.css");
    define('IMG_PATH',$this->config['base_url']."release/img/");
}

/**
 * Admin and developer details
 */

$config['admin_contact_email']="contact@iglulabs.com";
$config['admin_contact_name']="IgluLabs Admin";
$config['debug_notification_email']="contact@iglulabs.com";
$config['debug_notification_name']="Developer Sitename";

/*
 * One time password length
 */
$config['otp_length'] = 6;


//S3 config

//S3Bucket URL
if(ENVIRONMENT == 'production'){
    define('S3BUCKETNAME', 'XXXXX');
} else {
    define('S3BUCKETNAME', 'XXXXXX');
}

define('S3BUCKETURL', 'https://s3-ap-southeast-1.amazonaws.com/'.S3BUCKETNAME.'/');


//JWT-KEY
$config['jwt_key'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

//Response Codes
define('UNAUTHORIZED_CODE', 405);
define('TOKEN_EXPIRED', 410);
define('SUCCESS_CODE', 200);
define('EMPTY_CODE', 200);
define('INSUFFICIENT_DATA', 406);
define('FAIL_CODE', 403);
define('VALIDATION_FAIL', 401);
define('EXTRA_PARAMS_CODE', 206);
define('PARTIAL_FAIL_CODE', 201);
define('OTP_NOT_VERIFIED', 412);

//Upload config
define('IMAGE_MAX_SIZE', 10);
define('VIDEO_MAX_SIZE', 20);
define('DOC_MAX_SIZE', 20);

//Upload allowed types
$config['allowed_image'] = 'image/jpeg|image/png|image/jpg|image/bmp';
$config['allowed_image_ext'] = 'jpeg|jpg|png|bmp';
$config['allowed_video'] = 'video/mp4';
$config['allowed_video_ext'] = 'mp4';
$config['allowed_doc'] = 'application/pdf|text/plain|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|image/jpeg|image/png|image/jpg|image/bmp';
$config['allowed_doc_ext'] = 'pdf|txt|doc|docx|jpeg|png|jpg';

//For image thumbnail & compression
define('THUMBNAIL_WIDTH', 200);
define('THUMBNAIL_HEIGHT', 200);

//Device groups
define('ANDROID_DEVICE', 1);
define('IOS_DEVICE', 2);

//Status
define("ACTIVE", 1);
define("INACTIVE", 0);
