<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."third_party/aws/vendor/autoload.php";
use Aws\Sns\SnsClient;
use Aws\Sns\Exception\SnsException;

class Notifications
{
    public $SNS;

    public $android_arn;

    public $ios_arn;

    public $os_type;

    public function __construct()
    {
        $this->load->config('aws');
        $this->load->model('notifications_model');

        $this->SNS = new SnsClient([
            'region' => $this->config->item('aws_sns_region'),
            'version' => 'latest',
            'credentials' => [
                'key' => $this->config->item('aws_key'),
                'secret' => $this->config->item('aws_secret')
            ]
        ]);

        $this->android_arn = $this->config->item('aws_sns_android_arn');
        $this->ios_arn = $this->config->item('aws_sns_ios_arn');
    }


    public function register_device($data){

        $this->os_type = $data->os_type;

        $result = $this->notifications_model->get_device_info([
            'user_id' => $data->user_id,
            'device_id' => $data->device_id,
            'device_token' => $data->device_token,
            'os_type' => $data->os_type,
            'is_active' => ACTIVE
        ]);

        if($result && count($result) == 1){
            return $result->id;
        }

        $arn_type = $this->_get_arn_type();
        $aws_arn = $this->_create_endpoint($arn_type, $data->device_token);

        if($aws_arn){
            $data->is_active = ACTIVE;
        }
        else{
            $data->is_active = INACTIVE;
        }

        $data->aws_arn = $aws_arn;
        $data->created_on = $data->last_seen = $data->last_login = time_now();

        return $this->notifications_model->insert_device_info($data);


    }

    public function check_endpoint_exist($data){
        $exists = $this->_check_endpoint_arn($data->aws_arn);
        if(!$exists){
            //entry doesnt exists in amazon sns, so delete existing entry from our db and register again
            $this->notifications_model->remove_device_info($data->id);
            return FALSE;
        } else {
            return TRUE;
        }
    }


    public function de_register_device($data){
        $this->_remove_endpoint($data->aws_arn);
        return $this->notifications_model->update_device_info($data->id, ['is_active' => INACTIVE]);
    }

    public function re_register_device($data){
        $this->de_register_device($data);
        return $this->register_device($data);
    }

    public function send_notifications($receiver, $data, $message){
        return $this->_send_push_notification($receiver, $data, $message);
    }


    /*** PRIVATE METHODS **/

    private function _send_push_notification($receiver, $data, $message){
        $devices = $this->notifications_model->get_device_info([
            'user_id' => $receiver,
            'is_active' => ACTIVE
        ], TRUE);
        $count[ANDROID_DEVICE] = $count[IOS_DEVICE] = 0;

        if($devices){
            foreach ($devices as $device){
                if($device->aws_arn != "" || $device->aws_arn != 0){
                    $status = $this->_publish_message($device->aws_arn, $data, $message, $device->os_type);
                    if($status){
                        $count[$device->os_type]++;
                        log_message('dev', "-----Notification sent for ID {$device->id}");
                    }
                    else{
                        log_message('dev', "-----Notification failed for ID {$device->id}");
                    }
                }
            }
        }
        else{
            log_message('dev', "-------No devices are registered for user {$receiver}");
        }
        log_message('dev', "----Total Notification sent: Android {$count[ANDROID_DEVICE]}, IOS: {$count[IOS_DEVICE]}");
        return TRUE;
    }

    private function _get_message_structure($device, $data, $message = NULL){
        if($device == ANDROID_DEVICE){
            $message = array(
                'GCM' => json_encode(array(
                    'data' => array(
                        'message' => $data['data']
                    )
                ))
            );
        } elseif ($device == IOS_DEVICE){
            $message = array(
                'default' => $message,
                //this will be replaced once platform creation will done
                'APNS' => json_encode(array(
                    'aps' => array(
                        'alert' => $message,
                        'sound' => 'default',
                        'data' => $data['data']
                    )
                )),
                //this will be replaced once platform creation will done
                'APNS_SANDBOX' => json_encode(array(
                    'aps' => array(
                        'alert' => $message,
                        'sound' => 'default',
                        'data' => $data['data']
                    )
                ))
            );
        }

        return $message;
    }

    private function _publish_message($endpoint, $data, $msg = NULL, $device){

        try {
            $message = $this->_get_message_structure($device, $data, $msg);

            $this->SNS->publish(array('Message' => json_encode($message),
                'TargetArn' => $endpoint, 'MessageStructure'=>'json'));

            log_message('dev', '-------------PUSH MESSAGE SENT-----------');
            return TRUE;
        } catch (SnsException $e) {
            log_message('dev', '-------------PUSH MESSAGE FAILURE---------');
            log_message('dev', json_encode($e->getMessage()));
            return FALSE;
        }

    }

    private function _get_arn_type(){
        if($this->os_type == ANDROID_DEVICE) {
            return $this->android_arn;
        } elseif ($this->os_type == IOS_DEVICE) {
            return $this->ios_arn;
        }
    }

    private function _check_endpoint_arn($endpoint_arn){
        try{
            $this->SNS->getEndpointAttributes(['EndpointArn' => $endpoint_arn]);
            //ARN exists
            return TRUE;
        } catch (SnsException $e){
            log_message('dev', "Error in checking endpoint arn - ".json_decode($e->getAwsErrorMessage()));
            //ARN not found
            return FALSE;
        }
    }

    private function _create_endpoint($arn, $token){
        try{
            $endpoint = $this->SNS->createPlatformEndpoint(['PlatformApplicationArn'=>$arn, 'Token'=>$token, 'CustomUserData' => ENVIRONMENT]);
            return $endpoint['EndpointArn'];
        } catch (SnsException $e){
            log_message('dev', "Error in creating endpoint arn - ".json_encode($e->getAwsErrorMessage()));
            return FALSE;
        }
    }

    private function _remove_endpoint($arn){
        try{
            $this->SNS->deleteEndpoint(array('EndpointArn' => $arn));
            return TRUE;
        } catch (SnsException $e){
            log_message('dev', "Error in removing endpoint arn - ".json_encode($e->getAwsErrorMessage()));
            return FALSE;
        }
    }

    /**
     * Enables the use of CI super-global without having to define an extra variable.
     * I can't remember where I first saw this, so thank you if you are the original author.
     *
     * Borrowed from the Ion Auth library (http://benedmunds.com/ion_auth/)
     *
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }

}