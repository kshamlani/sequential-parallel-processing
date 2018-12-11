<?php

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 03/7/17
 * Time: 4:37 PM
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notifications_model extends CI_Model
{
    /**
     * Notification ID
     * @var integer $id
     */
    public $id;

    /**
     * Notification Receiver's ID
     * @var integer $receiver_id
     */
    public $receiver_id;

    /**
     * @var integer $limit
     */
    public $limit = 15;

    /**
     * @var integer $offset
     */
    public $offset = 0;

    /**
     * @var integer $status
     */
    public $status;

    /** Constructor **/
    function __construct(){
        parent::__construct();
    }

    function create($sender, $receiver, $opcode, $message, $title, $details = NULL){

        $data = [
            'sender_id' => $sender,
            'receiver_id' => $receiver,
            'opcode' => $opcode,
            'message' => $message,
            'title' => $title,
            'created_on' => time_now()
        ];

        $status = $this->db->insert('notifications', $data);
        $notification_id = $this->db->insert_id();

        if($details && $notification_id){
            //detailed notification to be add
            $details['data']['notification_id'] = $notification_id;
            $status = $this->db->insert($details['table'], $details['data']);
        }

        /* send push notification */
        $data = [
            'data' => [
                'opcode' => $opcode,
                'title' => $title,
                'data' => [
                    'notification_id' => $notification_id,
                    'message' => $message,
                    'sender_id' => $sender,
                    'receiver_id' => $receiver,
                    'created_on' => time_now()
                ]
            ]
        ];

        $this->load->library('notifications');
        $this->notifications->send_notifications($receiver, $data, $message);

        return $status;

    }


    function update($status = 1){
        if(!$this->id) return FALSE;
        if($this->status){
            $status = $this->status;
        }
        $status = $this->db->update('notifications', ['status' => $status, 'updated_on' => time_now()]);
        if($status){
            return TRUE;
        }
        else{
            return FALSE;
        }

    }

    function get_all(){
        if(!$this->receiver_id){
            return FALSE;
        }
        if($this->id){
            return $this->get_single_notification($this->id);
        }

        $this->db->select("*, IF(notifications.status='unread', 0, 1) as readYN");
        if($this->limit && $this->offset > -1){
            $this->db->limit($this->limit, $this->offset);
        }

        return $this->db->get_where('notifications', ['receiver_id' => $this->receiver_id])->result();

    }

    function get_single_notification($id = NULL){
        if(!$id){
            if($this->id){
                $id = $this->id;
            }
            else{
                return FALSE;
            }
        }
        $this->update();
        $this->db->select('*');
        $notification = $this->db->get_where('notifications', ['id' => $id, 'receiver_id' => $this->receiver_id])->row();
        if(!$notification) return FALSE;


        $this->db->select("*, IF(notifications.status='unread', 0, 1) as readYN");
        switch ($notification->opcode){
            case OC_BOOKING_NEW:
            case OC_BOOKING_ACCEPTED:
            case OC_BOOKING_REJECTED:
                $this->db->join('notifications_bookings as nb', 'nb.notification_id = notifications.id', 'LEFT');
                $this->db->join('bookings', 'bookings.id = nb.booking_id', 'LEFT');
                break;
            case OC_TOUR_STARTED:
            case OC_TOUR_COMPLETED:
                $this->db->join('notifications_tours as nt', 'nt.notification_id = notifications.id', 'LEFT');
                $this->db->join('bookings', 'bookings.id = nt.booking_id', 'LEFT');
                break;
            case OC_ITINERARY_CREATED:
                $this->db->join('notifications_itineraries as ni', 'ni.notification_id = notifications.id', 'LEFT');
                $this->db->join('bookings', 'bookings.id = ni.booking_id', 'LEFT');
                break;
            case OC_PAYMENT_CONFIRM:
            case OC_PAYMENT_APPROVED:
            case OC_PAYMENT_REJECTED:
                $this->db->join('notifications_payments as np', 'np.notification_id = notifications.id', 'LEFT');
                $this->db->join('bookings', 'bookings.id = np.booking_id', 'LEFT');
                break;

        }

        $this->db->from('notifications');
        $this->db->where('notifications.id', $id);
        $result = $this->db->get()->row();

        return $result;


    }


    public function get_device_info($where, $array = FALSE){
        $result = $this->db->get_where('auth_devices', $where)->result();
        if($array){
            return $result;
        }
        if(count($result) == 1){
            return $result[0];
        }
        else {
            return $result;
        }

    }

    public function insert_device_info($data){
        $this->db->insert('auth_devices', $data);
        return $this->db->insert_id();
    }

    public function update_device_info($id, $data){
        $this->db->where('id', $id);
        return $this->db->update('auth_devices', $data);
    }

    public function remove_device_info($id){
        return $this->db->delete('auth_devices', ['id' => $id]);
    }


}