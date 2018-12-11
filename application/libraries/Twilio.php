<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 19/5/17
 * Time: 7:43 PM
 */
require_once APPPATH.'third_party/twilio/Twilio/autoload.php';
use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\IpMessagingGrant;

Class Twilio
{
    private $twilio;

    private $twilio_phone;

    private $service_id;

    private $api_key_sid;

    private $api_key_secret;

    private $account_sid;

    private $account_token;

    public $message;

    public $toSend;

    /**
     * Facebook constructor.
     */
    public function __construct()
    {
        // Load config
        $this->load->config('twilio');
        $this->account_sid = $this->config->item('twilio_account_sid');
        $this->account_token = $this->config->item('twilio_auth_token');
        $this->twilio_phone = $this->config->item('twilio_phone_number');
        $this->service_id = $this->config->item('twilio_service_id');
        $this->api_key_sid = $this->config->item('twilio_api_key_sid');
        $this->api_key_secret = $this->config->item('twilio_api_key_secret');

        $this->twilio = new Client($this->account_sid, $this->account_token);


    }

    public function get_token($data){
        if(!is_array($data)){
            $data = (array) $data;
        }

        $token = new AccessToken(
            $this->account_sid,
            $this->api_key_sid,
            $this->api_key_secret,
            18000,
            $data['email']
        );

        $chatGrant = new IpMessagingGrant();
        $chatGrant->setServiceSid($this->service_id);
        $chatGrant->setEndpointId($data['device_id'].':'.$data['email']);

        $token->addGrant($chatGrant);

        return [
            'identity' => $data['email'],
            'token' => $token->toJWT()
        ];

    }

    public function send(){
        try{
            $this->twilio->messages->create(
            // the number you'd like to send the message to
                $this->toSend,
                array(
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => $this->twilio_phone,
                    // the body of the text message you'd like to send
                    'body' => $this->lang->line('welcome').', Your OTP is : '.$this->message
                )
            );

        }
        catch(Exception $e){
            log_message('dev', print_r($e, true));
            return FALSE;
        }

        return TRUE;
    }

    public function register_user($data = NULL){
        if(!$data) return NULL;

        $identity = $data['email'];
        $options = ['friendlyName' => $data['name']];

        try{
            $user = $this->twilio->chat
                ->services($this->service_id)
                ->users
                ->create($identity, $options);
        }
        catch(\Twilio\Exceptions\RestException $e){
            // here 50201 for user already exists
            if($e->getCode() == 50201){
                $user = $this->twilio->chat
                    ->services($this->service_id)
                    ->users($identity)->fetch();

                $user->sid;
            }
        }

        return $user->sid;

    }

    public function create_channel($name = NULL, $uniqueName = NULL){
        if(!$name){
            $name = 'channel-'.time();
        }
        if(!$uniqueName){
            $uniqueName = 'chat-'.time();
        }

        $channel = $this->twilio->chat
            ->services($this->service_id)
            ->channels
            ->create(
                [
                    'uniqueName' => $uniqueName,
                    'friendlyName' => $name,
                    'type' => 'private'
                ]
            );

        return $channel->sid;
    }

    public function add_members_to_channel($channel = NULL, $ids = []){
        if(!$ids || !$channel) return false;

        foreach ($ids as $id){
            $member = $this->twilio->chat
                ->services($this->service_id)
                ->channels($channel)
                ->members
                ->create($id);
        }
        return true;


    }

    public function delete_channel($channels = NULL){
        if(!$channels) return false;

        foreach ($channels as $channel){
            $this->twilio->chat
                ->services($this->service_id)
                ->channels($channel)
                ->delete();
        }
        return true;
    }

    public function delete_users($ids = []){
        if($ids){
            foreach ($ids as $id){
                $this->twilio->chat
                    ->services($this->service_id)
                    ->users($id)
                    ->delete();
            }
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
