<?php

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 16/5/17
 * Time: 3:39 PM
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{
    /* User ID */
    public $id;

    /* Email address */
    public $email;

    /* Login Type */
    public $login_type;

    /*
     * User Type
     */
    public $group_id;

    /** Constructor **/
    function __construct(){
        parent::__construct();
    }

    function get(){

        $this->db->select(array('auth_users.id','group_id','name', 'profile_pic_url', 'email', 'last_login', 'phone_number', 'dob', 'gender', 'status', 'is_first_time', 'is_phone_verified', 'is_tnc_accepted', 'is_profile_complete'));
        $this->db->from('auth_users')
                ->join('auth_users_groups', 'auth_users_groups.user_id = auth_users.id', 'LEFT');

        $params = array();

        if($this->group_id){
            $params['auth_users_groups.group_id'] = $this->group_id;
        }
        if($this->id){
            $params['auth_users.id'] = $this->id;
            //$this->db->where('auth_users.id', $this->id);
        }
        elseif($this->email){
            $params['auth_users.email'] = $this->email;
            //$this->db->where('auth_users.email', $this->email);
        }
        elseif($this->social_id && $this->login_type){
            $params['auth_users.social_id'] = $this->social_id;
            $params['auth_users.login_type'] = ($this->login_type == 2) ? 'google' : 'facebook';
            //$this->db->where(array('auth_users.social_id' => $this->social_id, 'auth_users.login_type' => ($this->login_type == 2) ? 'google' : 'facebook'));
        }
        if($params){
            $this->db->where($params);
        }

        return $this->db->get()->result();
    }

    function getOtp(){
        if($this->id){
            $this->db->select('id, otp, created_on');
            $this->db->from('auth_users_otp');
            $this->db->where(array('user_id' => $this->id, 'is_active' => 1));
            $this->db->order_by('created_on', 'DESC');
            return $this->db->get()->result();
        }
        return NULL;
    }

    function deactivateOtp($status = FALSE){
        if($this->id){
            $this->db->where('user_id', $this->id);
            $this->db->update('auth_users_otp', array('is_active' => 0));
        }
        $this->db->where('id', $this->id);
        if($status){
            $this->db->update('auth_users', array('is_phone_verified' => 1));
        }
        else{
            $this->db->update('auth_users', array('is_phone_verified' => 0));
        }
        return TRUE;
    }


    function get_group_id($user = NULL){
        if(!$user){
            $user = $this->id;
        }
        $data = $this->db->get_where('auth_users_groups', array('user_id' => $user));
        if($data){
            return $data->row()->group_id;
        }
        return FALSE;
    }


    function update_users($id, $content){
        $to_update = ['name', 'email', 'phone_number', 'dob', 'status', 'gender','profile_pic_url', 'is_phone_verified', 'is_first_time', 'is_tnc_accepted', 'is_profile_complete'];

        foreach($to_update as $key){
            if(isset($content[$key])){
                $data[$key] = $content[$key];
            }
        }
        $data['updated_on'] = time_now();

        return $this->ion_auth_model->update($id, $data);
    }

}