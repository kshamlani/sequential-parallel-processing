<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 18/5/17
 * Time: 5:41 PM
 */

require APPPATH . '/libraries/REST_Controller.php';

class V1 extends REST_Controller
{

    public $api_user;

    public $language;

    public $device;

    function __construct()
    {

        parent::__construct();
        $this->benchmark->mark('api_start');

        $this->language = $this->_detect_language();

        $this->lang->load('api', $this->language);

        $this->api_user = $this->_validateJwtToken();

        $this->device = $this->_detect_device();

        log_message('dev', '------------------------------------------');
        log_message('dev', '--------------API CALL STARTS-------------');
        log_message('dev', '------------------------------------------');
        logHttpRequests(false);


    }

    function __destruct(){
        log_message('dev', '------------------------------------------');
        log_message('dev', '---------------API CALL ENDS--------------');
        log_message('dev', '------------------------------------------');
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
     * @Override
     * @param null $data
     * @param null $http_code
     * @param bool $continue
     */
    public function response($data = null, $http_code = null, $continue = false)
    {

        $this->benchmark->mark('api_end');

        parent::response($data, $http_code, $continue);

    }


    /***************** Private Methods ******************/

    /**
     * Validated JWT token for existence in database with user
     * @return object
     *
     * */
    private function _validateJwtToken(){
        $jwt_token_name = $this->config->item('rest_token_key_name');

        $jwt_token_variable = 'HTTP_'.strtoupper(str_replace('-', '_', $jwt_token_name));
        if($token = $this->input->server($jwt_token_variable)){
            try{
                $data = JWT::decode($token, $this->config->item('jwt_key'));
            }
            catch (Exception $e){
                log_unauthorised();
                $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
                return FALSE;
            }
            if($data->exp < time()){
                //Token has expired
                $this->_deactivate_token($token);
                $this->response(['message' => $this->lang->line('token_expired')], TOKEN_EXPIRED);
                return FALSE;
            }
            if(isset($data->user_id)){
                $user = $this->generic_model->getGenericData('auth_tokens', array('user_id'=> $data->user_id, 'auth_token' => $token, 'is_active' => 1));
                if($user){
                    $this->load->model('user_model');
                    $this->user_model->id = $data->user_id;
                    $data = $this->user_model->get();
                    return $data[0];
                }
                else{
                    log_unauthorised();
                    $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
                    return FALSE;
                }
            }
        }
        log_unauthorised();
        $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
        return FALSE;

    }



    /**
     * isAdmin
     * Checks Whether user is admin or not
     * @param int
     * @return object
     */
    private function _isAdmin($user){
        return $this->db->where(['user_id' => $user, 'group_id' => 1])->get('auth_users_groups')->row();
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

    public function _detect_device()
    {
        $api_key_variable = config_item('rest_device_key_name');
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));

        if (($key = $this->input->server($key_name))) {

            return explode("|", $key);
        }
        // No key has been sent
        return NULL;
    }



}