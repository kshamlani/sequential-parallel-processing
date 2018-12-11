<?php
function generateRandomString($length)
{
    $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max = strlen($base)-1;
    $randomString = '';
    mt_srand((double)microtime()*1000000);
    while (strlen($randomString)< $length+1)
        $randomString = $randomString.$base{mt_rand(0, $max)};

    return $randomString;
}

function time_now()
{
    $CI = &get_instance();
    $time_variable_name = $CI->config->item('rest_time_key_name');
    $time_variable = 'HTTP_'.strtoupper(str_replace('-', '_', $time_variable_name));
    if($datetime = $CI->input->server($time_variable)){
        return $datetime;
    }
    else{
        $timestampInSeconds = now();

        $timezone = 'UP45';
        $daylight_saving = TRUE;

        $timestampInSeconds= gmt_to_local($timestampInSeconds, $timezone, $daylight_saving);
        $dateTimeNow= gmdate("Y-m-d H:i:s", $timestampInSeconds);

        return $dateTimeNow;
    }
}


function indent($json) {

    $tab = "    ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}

function timeDif($from_time,$to_time)
{

    $timeDiff=round(abs($to_time - $from_time) / 60,0);
    $differenceTxt=" min. ago";

    if($timeDiff>=60)
    {
        $timeDiff=round(abs($timeDiff) /60,0);
        $differenceTxt=" hours ago";
        if($timeDiff>=24)
        {
            $timeDiff=round(abs($timeDiff)/24,0);
            $differenceTxt=" days ago";
            if($timeDiff>=7)
            {
                $timeDiff=round(abs($timeDiff)/7,0);
                $differenceTxt=" weeks ago";
                if($timeDiff>4)
                {
                    $timeDiff=round(abs($timeDiff)/4,0);
                    $differenceTxt=" months ago";
                    if($timeDiff>=12)
                    {
                        $timeDiff=round(abs($timeDiff)/12,0);
                        $differenceTxt=" years ago";
                    }
                }
            }
        }
    }

    return $timeDiff.$differenceTxt;



}
/*
* Sorts json object by the specified index
*/
function sortArray($objectJson, $index)
{
    $object=json_decode($objectJson);
    $sorted=array();
    foreach($object as $obj)
    {
        $sorted[$obj->$index]=$obj;
    }
    ksort($sorted);
    return json_encode($sorted);
}

/*
* Sorts json object by the specified index in reverse order
*/
function sortArrayRev($objectJson, $index)
{
    $object=json_decode($objectJson);
    $sorted=array();
    uksort($object,"localCompare");
    foreach($object as $obj)
    {
        $sorted[$obj->$index]=$obj;
    }
    krsort($sorted);
    $retData=array();
    foreach($sorted as $item)
    {
        $retData[]=$item;
    }
    return json_encode($retData);
}

function popularityCompare($a,$b){
    log_message('info',indent(json_encode($a)));
    log_message('info',indent(json_encode($b)));
    return ($a->popularityCount > $b->popularityCount) ? -1 : (($a->popularityCount == $b->popularityCount) ? 0 : 1);
}
/*
 * FORMATS date
 */

function formatDate($unixTimestamp)
{
    return date("d M y",$unixTimestamp);
}

/*
 * Formats time
 */
function formatTime($unixTimestamp)
{
    return date('g:i A',$unixTimestamp);
}

function round_half($input)
{
    $output = round(($input*2), 0)/2;
    return $output;
}

function objectify($array)
{
    return json_decode(json_encode($array));
}

/**
 * Ajax utilities
 */

/**
 * @param $array array of post data to check
 * @return bool
 */
function checkPost($array)
{
    $status=true;
    foreach($array as $postItem)
    {
        if(!(isset($_POST[$postItem])))
        {
            $status=false;
            break;
        }

    }
    return $status;
}

/**
 *
 * Will add the calling function and file with line number
 *
 * @param $level Codeigniter debug level
 * @param $msg String message
 */
function debugPrint($level,$msg){
    $chunks = explode('/', debug_backtrace()[0]['file']);
    $header = $chunks[count($chunks)-1]."(".debug_backtrace()[0]['line'].") : ".debug_backtrace()[1]['function']."() - ";
    $msg = $header.$msg;
    log_message($level,$msg);
}

$codeigniterControllerInstance=get_instance();

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}


//returns timeAgo String from the specific ones
//Just Now , Within last hour, 2 hours ago, 3 hours ago, Earlier Today, Yesterday, Earlier this week,  Earlier this fornight, Earlier this month, Last month, Two months ago, Earlier this year
/**
 * @param $fromTime
 * @param $toTime
 * @return string
 */
function timeAgo($fromTime, $toTime){
    $diff = $toTime - $fromTime;
//    echo $diff;
    $timeAgo ='';
    switch ($diff){
        case $diff < MINUTE * 5:
            $timeAgo = 'Just now';
            break;
        case $diff < HOUR:
            $timeAgo = 'Within last hour';
            break;
        case $diff < HOUR * 3:
            $timeAgo = '2 hours ago';
            break;
        case $diff < HOUR * 4:
            $timeAgo = '3 hours ago';
            break;
        case $diff < DAY:
            $timeAgo = 'Earlier Today';
            break;
        case $diff < DAY * 2:
            $timeAgo = 'Yesterday';
            break;
        case $diff < WEEK:
            $timeAgo = 'Earlier this week';
            break;
        case $diff < FORTNIGHT:
            $timeAgo = 'Earlier this fortnight';
            break;
        case $diff < MONTH:
            $timeAgo = 'Earlier this month';
            break;
        case $diff < MONTH * 3:
            $timeAgo = 'Two months ago';
            break;
        case $diff < YEAR:
            $timeAgo = 'Earlier this year';
            break;
    }

    return $timeAgo;
}


function logHttpRequests($userID = false){
    $url = base_url(uri_string());
    $api  = stristr($url,"api");
    $method = $_SERVER['REQUEST_METHOD'];
    log_message("dev","-------------------------------------");
    logHeaders();
    log_message("dev","----------- API AND PARAMS START ---------------");
    if($userID == false){
        log_message("dev",$api."_" .strtolower($method). " User not logged in");
    } else {
        log_message("dev",$api."_" .strtolower($method). " User_id:" .$userID);
    }
    switch ($method) {
        case 'PUT':
            $params = array();
            $params = json_decode(file_get_contents("php://input"));
            break;
        case 'POST':
            $params = $_POST;
            $params = json_decode(file_get_contents('php://input'));
            break;
        case 'GET':
            $params = $_GET;
            break;
        case 'DELETE':
//            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER["CONTENT_TYPE"], "application/json")===false) {
            //$_POST = json_decode(file_get_contents("php://input"));
            $params = json_decode(file_get_contents("php://input"));
//            } else {
//                $params = (array)json_decode(file_get_contents("php://input"));
//            }
            break;
        default:
            break;
    }

    logParams(isset($params) ? $params : []);
    log_message("dev","----------- API AND PARAMS END ---------------");
    log_message("dev","-------------------------------------");
}

function logHeaders(){
    log_message("dev","----------- HEADERS START ----------------");
    $arr_main_array = getallheaders();
    $logString = " \n ";
    foreach($arr_main_array as $key => $value){
        $exp_key = explode('-', $key);
        if($exp_key[0] == 'IH'){
            $arr_result[] = $value;
            $logString .= $key." : ".$value." \n ";
        }
    }
    log_message('dev', $logString);
    log_message("dev","----------- HEADERS END ----------------");
}

function logParams($params){
    $logString = " \n ";
    foreach ($params as $key => $value){
        if($key != 'file'){
            $logString .= $key." : ".json_encode($value)." \n ";
        }

    }
    log_message('dev', $logString);
}


function validateEmail($email){
    $CI = & get_instance();
    $CI->load->library('form_validation');
    $email_data = array('email' => $email);

    $CI->form_validation->set_data($email_data);
    $CI->form_validation->set_rules('email', 'Email', 'valid_email');

    if ($CI->form_validation->run() == FALSE)
    {
        return FALSE;
    }
    return TRUE;
}

function generateRandomNumberString($length){
    $characters = "0123456789";
    $char_length = strlen($characters);
    $rand_string = "";

    foreach ( range(1, $length) as $item){
        $rand_string .= $characters[rand(0, $char_length -1)];
    }
    return $rand_string;
}

function convertUtcToLocal($time, $timediff){
    $time = strtotime($time);
    $operator = $timediff[0];
    $timediff = substr($timediff, 1);
    $timeDiffArr = explode(':', $timediff);
    $offset = "";
    if(isset($timeDiffArr[0])){
        $offset .= "{$operator} {$timeDiffArr[0]} hours";
    }
    if(isset($timeDiffArr[1])){
        $offset .= "{$operator} {$timeDiffArr[1]} minutes";
    }
    if(isset($timeDiffArr[2])){
        $offset .= "{$operator} {$timeDiffArr[2]} seconds";
    }

    return date("Y-m-d H:i:s", strtotime($offset, $time));
}

function log_unauthorised(){
    log_message('dev', '---------UNAUTHORISED----------');
    log_message('dev', print_r($_SERVER, true));
    log_message('dev', print_r(file_get_contents('php://input'), true));
}