<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 18/5/17
 * Time: 10:20 AM
 */
require_once APPPATH.'third_party/google/vendor/autoload.php';

Class Google
{
    /**
     * @var Google
     */
    private $google;

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
        $this->load->config('google');

        if (!isset($this->google))
        {
            $this->google = new Google_Client();
        }

    }

    public function validate_token(){

        try {
            $this->data = $this->google->verifyIdToken($this->token);
            if(!$this->data){
                return FALSE;
            }

        } catch (Google_Exception $ex) {
            return FALSE;
            // Session not valid, Graph API returned an exception with the reason.
        } catch (Google_Service_Exception $ex) {
            return FALSE;
            // Graph API returned info, but it may mismatch the current app or have expired.
        } catch( UnexpectedValueException $ex){
            return FALSE;
        }

        if($this->data){
            return TRUE;
        }

    }


    public function parseGoogleData(){
        $data = array();
        if(!$this->data){
            return NULL;
        }
        $data['name'] = $this->data['name'] ? $this->data['name'] : $this->data['given_name'] . " ". $this->data['family_name'];
        $data['email'] = $this->data['email'];
        $data['profile_pic_url'] = $this->data['picture'];
        $data['social_id'] = $this->data['sub'];
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
