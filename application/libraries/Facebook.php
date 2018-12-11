<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 17/5/17
 * Time: 2:43 PM
 */
require_once APPPATH.'third_party/facebook/vendor/autoload.php';
use Facebook\Facebook as FB;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookBatchResponse;
use Facebook\Helpers\FacebookCanvasHelper;
use Facebook\Helpers\FacebookJavaScriptHelper;
use Facebook\Helpers\FacebookPageTabHelper;
use Facebook\Helpers\FacebookRedirectLoginHelper;

Class Facebook
{
    /**
     * @var FB
     */
    private $fb;

    /**
     * @var FacebookRedirectLoginHelper|FacebookCanvasHelper|FacebookJavaScriptHelper|FacebookPageTabHelper
     */
    private $helper;

    /**
     * @var TOKEN
     */
    public $token;

    /**
     * @var Data
     */
    private $data;

    /**
     * Facebook constructor.
     */
    public function __construct()
    {
        // Load config
        $this->load->config('facebook');

        if (!isset($this->fb))
        {
            $this->fb = new FB([
                'app_id'                => $this->config->item('facebook_app_id'),
                'app_secret'            => $this->config->item('facebook_app_secret'),
                'default_graph_version' => $this->config->item('facebook_graph_version')
            ]);
        }


    }

    public function validate_token(){

        try {
            $this->data = $this->fb->get('/me?fields=id,name,first_name,last_name,email,picture.width(300),birthday,gender,link', $this->token);
        } catch (FacebookRequestException $ex) {
            return FALSE;
            // Session not valid, Graph API returned an exception with the reason.
        } catch (\Exception $ex) {
            return FALSE;
            // Graph API returned info, but it may mismatch the current app or have expired.
        }

        if($this->data){
            $this->data = $this->data->getGraphUser();
            return TRUE;
        }

    }

    public function parseFacebookData(){
        $data = array();
        if(!$this->data){
            return NULL;
        }

        $birthdate = (array) $this->data->getBirthday();
        if($birthdate){
            $data['dob'] = substr($birthdate['date'], 0, 10);
        }

        if($this->data['gender']){
            switch ($this->data['gender']){
                case 'male' : $data['gender'] = 1;
                    break;
                case 'female' : $data['gender'] = 2;
                    break;
                case 'others' : $data['gender'] = 3;
                    break;
            }
        }

        if($this->data['picture']['url']){
            $data['profile_pic_url'] = $this->data['picture']['url'];
        }
        $data['name'] = $this->data['name'];
        $data['link'] = $this->data['link'];
        //$data['first_name'] = $this->data['first_name'];
        //$data['last_name'] = $this->data['last_name'];
        if($this->data['email']){
            $data['email'] = $this->data['email'];
        }
        $data['social_id'] = $this->data['id'];
        $data['phone_number'] = "";

        return $data;
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
