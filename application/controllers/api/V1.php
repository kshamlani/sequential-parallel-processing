<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User: Kapil Shamlani
 * Date: 11/12/18
 * Time: 12:23
 */

require APPPATH . '/libraries/REST_Controller.php';
class V1 extends REST_Controller
{

    public $api_user;

    public $language;

    public $device;
    public $limit = 10;
    public $offset = 0;

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

    function __destruct()
    {
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
    function test_get()
    {
        $this->response(['message' => $this->lang->line('welcome')], SUCCESS_CODE);
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
    private function _validateJwtToken()
    {
        $jwt_token_name = $this->config->item('rest_token_key_name');

        $jwt_token_variable = 'HTTP_' . strtoupper(str_replace('-', '_', $jwt_token_name));
        if ($token = $this->input->server($jwt_token_variable)) {
            try {
                $data = JWT::decode($token, $this->config->item('jwt_key'));
            } catch (Exception $e) {
                log_unauthorised();
                $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
                return false;
            }
            if ($data->exp < time()) {
                //Token has expired
                $this->_deactivate_token($token);
                $this->response(['message' => $this->lang->line('token_expired')], TOKEN_EXPIRED);
                return false;
            }
            if (isset($data->user_id)) {
                $user = $this->generic_model->getGenericData('auth_tokens', array('user_id' => $data->user_id, 'auth_token' => $token, 'is_active' => 1));
                if ($user) {
                    $this->load->model('user_model');
                    $this->user_model->id = $data->user_id;
                    $data = $this->user_model->get();
                    return $data[0];
                } else {
                    log_unauthorised();
                    $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
                    return false;
                }
            }
        }
        log_unauthorised();
        $this->response(['message' => $this->lang->line('unauthorized_api_access')], UNAUTHORIZED_CODE);
        return false;

    }



    /**
     * isAdmin
     * Checks Whether user is admin or not
     * @param int
     * @return object
     */
    private function _isAdmin($user)
    {
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
    private function _deactivate_tokens_by_key($key, $value)
    {
        if ($key && $value) {
            $this->load->model('user_model');
            $this->user_model->{$key} = $value;
            $user = $this->user_model->get();
            if ($user) {
                return $this->db->where('user_id', $user[0]->id)->update('auth_tokens', array('is_active' => 0));
            }
            return false;
        }
        return false;
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
        return null;
    }

    /*----------------------------------------------------------   Flight Store Start -----------------------------------------------------------*/
    /**
     * adds flight store data
     */
    public function flightStore_post()
    {

        $data['year'] = $this->input->post('year');
        $data['month'] = $this->input->post('month');
        $data['day'] = $this->input->post('day');
        $data['week'] = $this->input->post('week');
        $data['departure_time'] = $this->input->post('departure_time');
        $data['actual_departure_time'] = $this->input->post('actual_departure_time');
        $data['arrival_time'] = $this->input->post('arrival_time');
        $data['carrier'] = $this->input->post('carrier');
        $data['flight_number'] = $this->input->post('flight_number');
        $data['departure_delay'] = $this->input->post('departure_delay');
        $data['arrival_delay'] = $this->input->post('arrival_delay');
        $data['cancellation'] = strtolower($this->input->post('cancellation'));
        $data['weather_delay'] = $this->input->post('weather_delay');

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('year', 'year', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'year')));
        $this->form_validation->set_rules('month', 'month', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'month')));
        $this->form_validation->set_rules('day', 'day', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'day')));
        $this->form_validation->set_rules('week', 'week', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'week')));
        $this->form_validation->set_rules('departure_time', 'departure_time', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'departure time')));
        $this->form_validation->set_rules('actual_departure_time', 'actual_departure_time', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'actual departure time')));
        $this->form_validation->set_rules('arrival_time', 'arrival_time', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'arrival time')));
        $this->form_validation->set_rules('carrier', 'carrier', 'required|is_unique[flight_store.carrier]', array('required' => sprintf($this->lang->line('please_enter'), 'carrier')));
        $this->form_validation->set_rules('flight_number', 'flight_number', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'flight number')));
        $this->form_validation->set_rules('departure_delay', 'departure_delay', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'departure delay')));
        $this->form_validation->set_rules('arrival_delay', 'arrival_delay', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'arrival delay')));
        $this->form_validation->set_rules('cancellation', 'cancellation', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'valid cancellation')));
        $this->form_validation->set_rules('weather_delay', 'weather_delay', 'required', array('required' => sprintf($this->lang->line('please_enter'), 'weather delay')));

        if ($this->form_validation->run() == false) {
            $this->response(["message" => $this->validation_errors()[0]], INSUFFICIENT_DATA);
        }

        $tbl_fields = ['year', 'month', 'day', 'week', 'departure_time', 'actual_departure_time', 'arrival_time', 'carrier', 'flight_number', 'departure_delay', 'arrival_delay', 'cancellation', 'weather_delay'];

        foreach ($tbl_fields as $key) {
            if (!empty($data[$key])) {
                $params[$key] = $data[$key];
            }
        }
        $params['created_on'] = time();

        $result = $this->generic_model->addGenericData('flight_store', $params);

        if ($result) {
            $message = sprintf($this->lang->line('add_success'), 'Flight details');
            $code = 200;
        } else {
            $message = sprintf($this->lang->line('add_fail'), 'flight details');
            $code = 403;
        }

        $this->response(['message' => $message], $code);
    }

    /**
     * Flight Store GET
     *
     * This function fetches the flight Store Data
     *
     */
    public function flightStore_get()
    {
        // for pagination
        if ($this->input->get('limit')) {
            $limit = $this->input->get('limit');
        } else {
            $limit = $this->limit;
        }

        if ($this->input->get('page') == 'false') {
            $limit = $offset = null;
        } else if ($this->input->get('page') && $this->input->get('page') > 0) {
            $offset = ($this->input->get('page') - 1) * $limit;
        } else {
            $offset = $this->offset;
        }

        //for sorting
        $col_sort = 'flight_store.id';
        $col_order = 'desc';
        if ($this->input->get('sort')) {
            $col_sort = 'flight_store.' . $this->input->get('sort');
        }
        if ($this->input->get('order') == 'asc' || $this->input->get('order') == 'desc') {
            $col_order = $this->input->get('order');
        }
        $sortParams['col'] = $col_sort;
        $sortParams['order'] = $col_order;

        $paramWhere = array();
        // fetching data for specific coloumn
        if (!empty($this->input->get('id'))) {
            $paramWhere['flight_store.id'] = $this->input->get('id');
        }
        if (!empty($this->input->get('year'))) {
            $paramWhere['flight_store.year'] = $this->input->get('year');
        }
        if (!empty($this->input->get('month'))) {
            $paramWhere['flight_store.month'] = $this->input->get('month');
        }
        if (!empty($this->input->get('carrier'))) {
            $paramWhere['flight_store.carrier'] = $this->input->get('carrier');
        }

        $data['flight_details'] = $this->generic_model->getGenericData('flight_store', $paramWhere, 'id,year, month, day, week, departure_time, actual_departure_time, arrival_time, carrier, flight_number, departure_delay, arrival_delay, cancellation, weather_delay,DATE_FORMAT(FROM_UNIXTIME(flight_store.created_on), "%d-%m-%Y") as created_on', $sortParams);
        $data['delayed_flights'] = array();

        // fetching number of flights delayed
        foreach ($data['flight_details'] as $flight_key => $flight) {
            if(($flight->departure_time != $flight->actual_departure_time) || ($flight->departure_delay != 0) || ($flight->arrival_delay != 0) || ($flight->weather_delay != 0)){
                array_push($data['delayed_flights'], $flight);
            }
        }

        $data['total_flights'] = count($data['flight_details']);
        $data['total_delayed_flights'] = count($data['delayed_flights']);

        $data['searched'] = $paramWhere;
        if ($data) {
            $message = $this->lang->line('success');
            $code = 200;
        } else {
            $message = $this->lang->line('data_empty');
            $code = 200;
        }

        $result['data'] = $data;
        $this->load->view('pages/flightStoreView', $result);
    }

    /**
     * Flight Store DELETE
     *
     * This function deletes the flight store details 
     *
     * @return json
     */

    public function flightStore_delete($id = null)
    {
        if ($id) {
            if ($this->generic_model->removeGenericData('flight_store', array('id' => $id))) {
                $message = sprintf($this->lang->line('delete_success'), 'Flight store');
                $code = 200;
            } else {
                $message = sprintf($this->lang->line('delete_fail'), 'flight store');
                $code = 403;
            }
        } else {
            $message = $this->lang->line('data_empty');
            $code = 403;
        }

        $this->response(['message' => $message], $code);
    }

    public function flightStoreForm_get(){
        $this->load->view('pages/flightStoreForm');
    }
    /*----------------------------------------------------------   Flight Store End -----------------------------------------------------------*/


}