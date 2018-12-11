<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Amit Ahire
 * Date: 16/5/17
 * Time: 2:33 PM
 */

require APPPATH . '/libraries/REST_Controller.php';

class Auth extends REST_Controller
{

    public $api_user;

    public $language;

    public $os_type;

    function __construct()
    {

        parent::__construct();
        $this->benchmark->mark('api_start');

        $this->language = $this->_detect_language();

        $this->lang->load('api', $this->language);

        log_message('dev', '------------------------------------------');
        log_message('dev', '--------------API CALL STARTS-------------');
        log_message('dev', '------------------------------------------');
        logHttpRequests(false);
    }

    function __destruct(){
        log_message('dev', '------------------------------------------');
        log_message('dev', '---------------API CALL ENDS--------------');
        log_message('dev', '------------------------------------------');
        $this->benchmark->mark('api_stop');
    }


    /* APIs start */

    /**
     * Test
     *
     * This function is used to check the connectivity between application and backend
     *
     * @return json
     */
    function test_get(){
        $this->response(['message' =>$this->lang->line('welcome')], SUCCESS_CODE);
    }

    /**
     * Login POST
     *
     * This function is to login into the app using email and password
     *
     * @return json
     */
    public function login_post()
    {

        $data = array();

        $data['email'] = $this->post('email');
        $data['password'] = $this->post('password');
        $data['user_group'] = $this->post('user_group');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required');
        $this->form_validation->set_rules('user_group', 'User Group', 'required|regex_match[/^[1|2|3]$/]', array('regex_match'=> $this->lang->line('user_group_invalid')));
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $errorCode = FAIL_CODE;
        $loginData = $this->ion_auth_model->login($data['email'], $data['password'], $data['user_group']);
        $authToken = "";
        $data = null;
        if($loginData['status'] == 1){
            //login successful
            $this->load->model('user_model');
            $this->user_model->id = $loginData['id'];
            $data = $this->user_model->get();
            $authToken = $this->generateJwtToken($loginData['id']);
            $errorCode = SUCCESS_CODE;
        }
        elseif($loginData['status'] == 7){
            //If otp not verified
            $errorCode = OTP_NOT_VERIFIED;
            $this->load->model('user_model');
            $this->user_model->id = $loginData['id'];
            $this->user_model->deactivateOtp();
            $this->ion_auth_model->generate_otp($loginData['id']);
            $data['id'] = $loginData['id'];
            $data['phone_number'] = $loginData['phone'];
        }
        $message = $this->lang->line($loginData['key']);
        $this->response(['message' => $message,'auth_token' => $authToken, 'data' => $data, 'is_verified' => ($errorCode == SUCCESS_CODE) ? 1 : 0], $errorCode);

    }

    /**
     * Register POST
     *
     * This function is used to create new account
     * using name, last_name, email, password, user_group
     * For tourist - user_group = 2
     * For Tour Guide - user_group = 3
     *
     * @return json
     */
    public function register_post()
    {

        $this->load->config('ion_auth', true);
        // all fields req for registration
        $data = array();
        $data['name'] = $this->post('name');
        $data['email'] = $this->post('email');
        $data['password'] = $this->post('password');
        $data['confirm_password'] = $this->post('confirm_password');
        $data['phone_number'] = $this->post('phone_number');
        $data['user_group'] = $this->post('user_group');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required');
        $this->form_validation->set_rules('user_group', 'User Group', 'required|regex_match[/^[2|3]$/]', array('regex_match'=> $this->lang->line('user_group_invalid')));
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('phone_number', 'Phone number', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $additionalData = array(
            'name' => $data['name'],
            'phone_number' => $data['phone_number']
        );

        $user_group = $data['user_group'];

        $id = 0;

        if ($user_group == 2 || $user_group == 3) {
            $registerData = $this->ion_auth->register($data['email'], $data['password'], $data['email'], $additionalData, array($user_group));
            $id = $registerData['id'];
            if($registerData['status'] != 5){
                $message = $this->lang->line($registerData['key']);
                $code = FAIL_CODE;
            }
            else{
                $this->load->model('ion_auth_model');
                if($this->ion_auth_model->generate_otp($id)){
                    $message = $this->lang->line('otp_verification_sent');
                    $code = SUCCESS_CODE;
                }
                else{
                    $message = $this->lang->line('otp_not_sent');
                    $code = PARTIAL_FAIL_CODE;
                }

            }
        } else {
            $message = $this->lang->line('user_group_invalid');
            $code = FAIL_CODE;
        }

        $this->response(['message' => $message, 'data' => ['id' => $id, 'phone_number' => $additionalData['phone_number']], 'is_verified' => 0], $code);
    }


    /**
     * Social AUTH POST
     *
     * This function is used to create new account from social login
     *
     * For tourist - user_group = 2
     * For Tour Guide - user_group = 3
     * Login Type
     * For Facebook - login_type = 3
     * For Google - login_type = 2
     *
     * @return json
     */
    public function social_auth_post()
    {


        $data = array();
        $data['user_group'] = $this->post('user_group');
        $data['email'] = $this->post('email');
        $data['social_token'] = $this->post('social_token');
        $data['social_id'] = $this->post('social_id');
        $data['login_type'] = $this->post('login_type');
        $data['phone_number'] = $this->post('phone_number');

        $this->form_validation->set_data($data);
        //************** In case of email found while login then we can search directly for user using email
        if($data['email']){
            $this->form_validation->set_rules('email', 'Email', 'valid_email');
        }
        $this->form_validation->set_rules('user_group', 'User Group', 'required|regex_match[/^[2|3]$/]', array('regex_match'=> $this->lang->line('user_group_invalid')));
        $this->form_validation->set_rules('social_id', 'Social ID', 'required');
        $this->form_validation->set_rules('social_token', 'Social Token', 'required');
        $this->form_validation->set_rules('login_type', 'Login Type', 'required|regex_match[/^[2|3]$/]', array('regex_match'=> $this->lang->line('login_type_invalid')));

        //******* In case of phone number found,
        // It is confirm that user has entered his phone number manually so this is the second call for registration
        if($data['phone_number']){
            $this->form_validation->set_rules('phone_number', 'Phone number', 'required');
        }

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
            return;
        }

        $this->load->model('user_model');

        //****** Try to fetch user details using email or social id
        if($data['email']){
            $this->user_model->email = $data['email'];
        }
        else{
            $this->user_model->social_id = $data['social_id'];
            $this->user_model->login_type = $data['login_type'];
        }
        $user = $this->user_model->get();

//        if(!$user && !$data['phone_number']){
//            $this->response(["message" => $this->lang->line('email_not_exists'), 'status' => 2],FAIL_CODE);
//            return;
//        }

        //******************* Social TOKEN Validation START ---------------
        $socialData = NULL;

        if($data['login_type'] == 3){
            //********************** Proceed for facebook token validation
            $this->load->library('facebook');
            $this->facebook->token = $data['social_token'];
            $socialStatus = $this->facebook->validate_token();
            $socialData = $this->facebook->parseFacebookData();

        }
        elseif($data['login_type'] == 2){

            //********************* Proceed for Google token validation
            $this->load->library('google');
            $this->google->token = $data['social_token'];
            $socialStatus = $this->google->validate_token();
            $socialData = $this->google->parseGoogleData();

        }

        //******************** Social TOKEN validation END ------------------

        if(!$socialStatus){
            //*****************  If social validation failed
            $this->response(["message" => $this->lang->line('social_valid_fail')],VALIDATION_FAIL);
            return;
        }

        if(!$user && $socialData){
            $reqParams = [];
            //ask for required parameters
            if(!$socialData['email']) $reqParams[] = 'email';
            if(!$socialData['phone_number']) $reqParams[] = 'phone_number';
            if(!$socialData['name']) $reqParams[] = 'name';

            //************** If the above parameters found in post data then removed from required parameters,
            // as considering that In second API call the required parameters are added as mention in first API call

            if($reqParams){
                foreach ($reqParams as $key => $param){
                    if($data[$param] || $socialData[$param]){
                        unset($reqParams[$key]);
                    }
                }
            }

            if($reqParams){
                //*****************  If required more params
                $this->response(["message" => $this->lang->line('social_extra_param_call'), 'data' => $reqParams],EXTRA_PARAMS_CODE);
                return;
            }

            //When this parameters are already in social data then don't add from post parameters
            if($socialData['email']){
                $data['email'] = $socialData['email'];
            }
            if($socialData['phone_number']){
                $data['phone_number'] = $socialData['phone_number'];
            }
            if(!$socialData['name']){
                $socialData['name'] = $data['name'];
            }


        }

        $this->load->library('ion_auth');

        //If user exists then proceed for login else signup
        if($user && $socialData){
            //Proceed for login

            /**************   Process for social auth login  ----start---    *************/

            $this->load->model('user_model');
            $this->load->model('ion_auth_model');
            $this->user_model->id = $user[0]->id;
            $errData = ['id' => $user[0]->id];
            if ($user[0]->status == "inactive" || $user[0]->status == "blocked")
            {
                //User is either inactive or blocked by admin
                $errorCode = FAIL_CODE;
                $messageKey = 'login_user_'.$user[0]->status;
            }
            elseif ($user[0]->group_id != $data['user_group'])
            {
                //User has login with different group
                $errorCode = FAIL_CODE;
                $messageKey = 'login_with_different_group';
            }
            elseif (!$user[0]->is_phone_verified)
            {
                //User has login with different group
                $errorCode = OTP_NOT_VERIFIED;
                $messageKey = 'otp_not_verified';
                $this->user_model->deactivateOtp();
                $this->ion_auth_model->generate_otp($user[0]->id);
                $errData['phone_number'] = $user[0]->phone_number;
            }

            if(isset($errorCode)){
                $this->response(['message' => $this->lang->line($messageKey), 'data' => $errData, 'is_verified' => 0], $errorCode);
            }

            $this->ion_auth_model->update($user[0]->id, ['social_auth_token' => $data['social_token'], 'social_id' => $socialData['social_id']]); //Update token
            $this->ion_auth_model->update_last_login($user[0]->id); //Update last login
            $data = $this->user_model->get_by_userid();
            $authToken = $this->generateJwtToken($user[0]->id);

            $this->response(['message' => $this->lang->line('success'),'auth_token' => $authToken, 'data' => $data, 'is_verified' => 1], SUCCESS_CODE);

            /**************   Process for social auth login  ----end---    *************/

        }
        elseif($socialData){
            //Proceed for signup
            //Move social data to additional data
            $additionalData = $socialData;

            /**************   Process for social auth registration  ----start---    *************/

            $additionalData['login_type'] = $data['login_type'];
            if($data['phone_number']){
                $additionalData['phone_number'] = $data['phone_number'];
            }
            $additionalData['social_auth_token'] = $data['social_token'];
            if(isset($additionalData['link'])){
                $social_link = $additionalData['link'];
                unset($additionalData['link']);
            }
            $add_social_link = false;
            if($data['user_group'] == TOUR_GUIDE && isset($social_link)){
                if($data['login_type'] == 3){
                    //Add link to social links of tour_guides
                    $add_social_link = true;
                }
            }

            $registerData = $this->ion_auth->register($data['email'], "", $data['email'], $additionalData, [$data['user_group']]);
            $id = $registerData['id'];
            if($registerData['status'] != 5){
                $message = $this->lang->line($registerData['key']);
                $code = FAIL_CODE;
            }
            else{
                if($add_social_link){
                    $this->load->model('guide_model');
                    $this->guide_model->user_id = $id;
                    $this->guide_model->id = $this->guide_model->get_tour_guide_id();
                    $this->guide_model->social = [
                        [ 'type' => 1, 'url' => $social_link ]
                    ];
                    $this->guide_model->update_social_links();
                }

                if($this->ion_auth_model->generate_otp($id)){
                    $message = $this->lang->line('otp_verification_sent');
                    $code = SUCCESS_CODE;
                }
                else{
                    $message = $this->lang->line('otp_not_sent');
                    $code = PARTIAL_FAIL_CODE;
                }

            }

            $this->response(['message' => $message, 'data' => ['id' => $id, 'phone_number' => $additionalData['phone_number']], 'is_verified' => 0], $code);

            /**************   Process for social auth registration  ----end---    *************/
        }


    }


    /**
     * Validate OTP POST
     *
     * This function validates OTP that are generated after creating account
     *
     * @return json
     */
    public function validate_otp_post(){

        $data = array();

        $data['id'] = $this->post('id');
        $data['otp'] = $this->post('otp');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('id', 'User id', 'required');
        $this->form_validation->set_rules('otp', 'OTP', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }
        $id = $data['id'];


        $status = $this->validateOtp($id, $data['otp']);
        $data = $auth_token = NULL;
        if($status['status'] != 1){
            $message = $this->lang->line($status['key']);
            $code = FAIL_CODE;
        }
        else{
            $this->load->model('ion_auth_model');
            $this->ion_auth_model->update_last_login($id);
            $this->load->model('user_model');
            $this->user_model->id = $id;
            $data = $this->user_model->get();
            $auth_token = $this->generateJwtToken($id);
            $message = $this->lang->line('registered_success');
            $code = SUCCESS_CODE;
        }

        $this->response(['message' => $message, 'data' => $data[0], 'auth_token' => $auth_token], $code);

    }

    /**
     * Resend OTP POST
     *
     * This function resend OTP in case user don't receive OTP at first
     *
     * @return json
     */
    public function resend_otp_post(){

        $data = array();

        $data['id'] = $this->post('id');
        $data['phone_number'] = $this->post('phone_number');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('id', 'User id', 'required');
        /*if($data['phone_number']){
            $this->form_validation->set_rules('phone_number', 'Phone number', 'regex_match[/^[+][0-9]{2}[-][0-9]{10}$/]')));
        }*/

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $id = $data['id'];

        $this->load->model('user_model');
        $this->user_model->id = $id;
        $user = $this->user_model->get();
        $is_verified = 0;

        if($user){
            //Valid user
            if(!$user[0]->is_phone_verified){
                //OTP not verified
                $this->load->model('ion_auth_model');

                if($phone_number = $data['phone_number']){
                    //If phone number is set => have to change the number
                    if($user[0]->phone_number != $phone_number){
                        //If new phone number is different from the existing phone number
                        $this->ion_auth_model->update($id, ['phone_number' => $phone_number]);
                        $user[0]->phone_number = $phone_number;
                    }
                }
                //deactivate old otp and generate new one
                $this->user_model->deactivateOtp();
                if($this->ion_auth_model->generate_otp($id)){
                    $code = SUCCESS_CODE;
                    $message_key = 'otp_verification_sent';
                }
                else{
                    $code = FAIL_CODE;
                    $message_key = 'otp_sending_failed';
                }

            }
            else{
                //OTP already verified
                $message_key = 'otp_already_verified';
                $code = FAIL_CODE;
                $is_verified = 1;
            }
        }
        else{
            //Invalid user
            $message_key = 'invalid_user';
            $code = FAIL_CODE;
        }

        $this->response(['message' => $this->lang->line($message_key), 'data' => $data, 'is_verified' => $is_verified], $code);

    }


    /**
     * Update Phone Number POST
     *
     * This function update phone number if the user has problem in receiving OTP or want to change number
     *
     * @return json
     */
    public function update_phone_post(){

        $data = array();

        $data['id'] = $this->post('id');
        $data['phone_number'] = $this->post('phone_number');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('id', 'User id', 'required');
        $this->form_validation->set_rules('phone_number', 'Phone number', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $id = $data['id'];
        $phone = $data['phone_number'];

        $this->load->model('user_model');
        $this->user_model->id = $id;
        $user = $this->user_model->get();
        $code = FAIL_CODE;
        if($user){
            //Valid user
            if($user[0]->phone_number == $phone){
                //Updating number is same as previous one
                $message_key = 'phone_update_same';
            }
            else{
                //Updated number not same as previous one
                $this->load->model('ion_auth_model');
                print_r($this->ion_auth_model->mobile_identity_check($phone));
                //exit;
                if($this->ion_auth_model->mobile_identity_check($phone)){
                   //phone number matched with other user
                    $message_key = 'phone_already_exists';
                }
                else{
                    //Phone number can be updated
                    if($this->ion_auth_model->update($id, ['phone_number' => $phone])){
                        //Phone number updated
                        $this->user_model->deactivateOtp();
                        $this->ion_auth_model->generate_otp($id);
                        $message_key = 'phone_updated';
                        $code = SUCCESS_CODE;
                    }
                    else{
                        //Unable to update phone number
                        $message_key = 'fail';
                    }

                }
            }
        }
        else{
            //Invalid user
            $message_key = 'invalid_user';
            $code = FAIL_CODE;
        }

        $this->response(['message' => $this->lang->line($message_key)], $code);

    }


    /**
     * Forgot password POST
     *
     * This function send reset link to email id for resetting the password
     *
     * @return json
     */
    public function forgot_password_post()
    {


        $email = $this->post('email');

        $this->form_validation->set_data(['email' => $email]);
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $user = $this->ion_auth_model->identity_check($email);
        $error_code = FAIL_CODE;
        if($user){
            $result = $this->ion_auth->forgotten_password($email);
            if($result['status'] == 1){
                $error_code = SUCCESS_CODE;
            }
            $message = $this->lang->line($result['key']);

        }
        else{
            $message = $this->lang->line('email_not_exists');
        }

        $this->response(array('message' => $message), $error_code);
    }

    /**
     * Reset Password POST
     *
     * This function reset the password using code i.e. receive in email after forgot password click and using new password
     *
     * @return json
     */
    public function reset_password_post()
    {

        $data = array();
        $data['code'] = $this->post('code');
        $data['confirm_password'] = $this->post('confirm_password');
        $data['password'] = $this->post('password');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('code', 'Reset Code', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $user = $this->ion_auth->forgotten_password_check($data['code']);
        $error_code = FAIL_CODE;
        if($user){
            $identity = $user->{$this->config->item('identity', 'ion_auth')};
            $result = $this->ion_auth_model->reset_password($identity, $data['password']);
            if($result['status'] == 1){
                $error_code = SUCCESS_CODE;
            }
            $message = $this->lang->line($result['key']);
        }
        else{
            $message = $this->lang->line('invalid_user');

        }

        $this->response(array('message' => $message), $error_code);
    }

    /**
     * Change password POST
     *
     * This function change password using email and old password
     *
     * @return object
     */
    public function change_password_post()
    {

        $data = array();
        $data['email'] = $this->post('email');
        $data['old'] = $this->post('old_password');
        $data['new'] = $this->post('new_password');

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required');
        $this->form_validation->set_rules('old', 'Old password', 'required');
        $this->form_validation->set_rules('new', 'New password', 'required|min_length[8]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->response(["message" => $this->validation_errors()],INSUFFICIENT_DATA);
        }

        $result = $this->ion_auth_model->change_password($data['email'], $data['old'], $data['new']);
        $error_code = FAIL_CODE;
        if($result['status'] == 1){
            $error_code = SUCCESS_CODE;
            // If password changed successfully then logout from all devices
            $this->_deactivate_tokens_by_key('email', $data['email']);
        }
        $message = $this->lang->line($result['key']);

        $this->response(array('message' => $message), $error_code);

    }


    /**
     * Logout POST
     *
     * This function will deactivate the JWT token that was using for communication with API
     *
     * @return json
     */
    function logout_post()
    {
        $jwt_token_name = $this->config->item('rest_token_key_name');
        $jwt_token_variable = 'HTTP_'.strtoupper(str_replace('-', '_', $jwt_token_name));
        if($token =  $this->input->server($jwt_token_variable)){
            $this->_deactivate_token($token);
            $this->response(array('message' => $this->lang->line('logout_success')), SUCCESS_CODE);
        }
        else{
            $this->response(array('message' => $this->lang->line('logout_fail')), FAIL_CODE);
        }

    }

    /**
     * @Override
     * @param null $data
     * @param null $http_code
     * @param bool $continue
     */
    public function response($data = null, $http_code = null, $continue = false)
    {

        $this->benchmark->mark('api_end');
//        log_message('dev', "<< ELAPSED TIME FOR API for - (" . $this->router->method . " - " . $this->input->server('REQUEST_METHOD') . ") >> : " . $this->benchmark->elapsed_time('api_start', 'api_end') . " seconds");

        //log_message('info',$this->db->last_query() );
        parent::response($data, $http_code, $continue);

    }

    /* Helper Methods */

    private function validateOtp($userid, $otp){

        $this->load->model('user_model');
        $this->user_model->id = $userid;
        $user = $this->user_model->get();

        if($user){
            //Valid user
            if(!$user[0]->is_phone_verified){
                //OTP not verified
                $otpData = $this->user_model->getOtp();
                if($otpData){
                    if($otpData[0]->otp == $otp){
                        //OTP matched
                        $this->user_model->deactivateOtp(TRUE);
                        return ['status' => 1, 'key' => 'success'];
                    }
                    else{
                        //OTP not matched
                        return ['status' => 2, 'key' => 'otp_not_matched'];
                    }
                }
                else{
                    //OTP not valid or expired
                    return ['status' => 3, 'key' => 'otp_not_valid'];
                }
            }
            else{
                //OTP already verified
                return ['status' => 4, 'key' => 'otp_already_verified'];
            }
        }
        else{
            //Invalid user
            return ['status' => 5, 'key' => 'invalid_user'];
        }
    }

    private function generateJwtToken($userid){
        $data['user_id'] = $userid;
        $data['iat'] = time();
        $data['exp'] = strtotime("+30 days");
        $token = JWT::encode($data, $this->config->item('jwt_key'));
        $this->_store_token($userid, $token);
        return $token;
    }

    /**
     * Checks if a auth_token already exists
     * @param $auth_token
     * @return mixed
     */
    private function _token_exists($auth_token)
    {
        return $this->db->where('auth_token', $auth_token)->count_all_results('auth_tokens');
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    private function _store_token($user_id, $token)
    {
        $data['user_id'] = $user_id;
        $data['auth_token'] = $token;
        $data['is_active'] = 1;
        $data['created_on'] = time_now();
        return $this->db->insert('auth_tokens', $data);
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    private function _update_token($id, $data)
    {
        return $this->db->where('id', $id)->update('auth_tokens', $data);
    }

    /**
     * @param $token
     * @return mixed
     */
    private function _deactivate_token($token)
    {
        return $this->db->where(array('auth_token' => $token))->update('auth_tokens', array('is_active' => 0));
    }

    /**
     * Deactivate tokens by Key
     *
     * @param $key string email|id
     * @param $value string|number
     * @return bool|CI_DB_active_record|CI_DB_result
     */
    private function _deactivate_tokens_by_key($key, $value){
        if($key && $value){
            $this->load->model('user_model');
            $this->user_model->{$key} = $value;
            $user = $this->user_model->get();
            if($user){
                return $this->db->where('user_id', $user[0]->id)->update('auth_tokens', array('is_active' => 0));
            }
            return FALSE;
        }
        return FALSE;
    }

    public function _detect_language()
    {
        $api_key_variable = config_item('rest_language_key_name');
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));

        if (($key = isset($this->_args[$api_key_variable]) ? $this->_args[$api_key_variable] : $this->input->server($key_name))) {

            return strtolower($key);
        }
        // No key has been sent
        return 'english';
    }

}