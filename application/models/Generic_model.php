<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Generic_model extends CI_Model
{

    /** Constructor **/
    function __construct(){
        parent::__construct();
    }

    /**
     * @param $table
     * @param null $paramList
     * @param null $attrList
     * @param null $sortParams
     * @param null $limitBy
     * @param bool $distinct
     * @return mixed
     */
    function getGenericData($table,$paramList=null,$attrList=null,$sortParams=null,$limitBy=null,$distinct=false){

        if($attrList==null)
            $attrList ="*";

        $this->db->select($attrList);
        $this->db->from($table);

        if($paramList!=null)
            $this->db->where($paramList);

        if($sortParams!=null)
        {

            if(isset($sortParams['multiple'])){
                foreach($sortParams['params'] as $sortParam)
                {
                    log_message('info',indent(json_encode($sortParams)));
                    $this->db->order_by($sortParam['col'],$sortParam['order']);
                }
            }
            else{
                $this->db->order_by($sortParams['col'],$sortParams['order']);
            }
        }
        if($limitBy!=null)
            $this->db->limit($limitBy);

        if($distinct)
            $this->db->distinct();

        $data = $this->db->get();

        return $data->result();
    }

    /** Generic function to insert a row
     * @param null $table
     * @param null $data
     * @return mixed
     */
    function addGenericData($table=null,$data=null)
    {
        if($table!=null && $data!=null)
        {
            $this->db->insert($table, $data);
               return  $this->db->insert_id();

        }
    }

    /** generic function to update matching rows
     * @param $table
     * @param $paramList
     * @param $data
     * @return bool
     */
    function updateGenericData($table,$paramList,$data){
        $this->db->where($paramList);
        $this->db->update($table, $data);
        return true;
    }

    /** generic function to remove matching rows
     * @param $table
     * @param $paramList
     * @return boolean
     */
    function removeGenericData($table,$paramList){
        $this->db->delete($table, $paramList);
        return $this->db->affected_rows();
    }

    /**
     * @param $table1
     * @param $joinElement1
     * @param $joinType
     * @param $table2
     * @param $joinElement2
     * @param null $paramList
     * @param null $attrList
     * @param null $sortParams
     * @param null $limitBy
     * @return mixed
     */
    function joinGenericData($table1,$joinElement1,$joinType,$table2,$joinElement2,$paramList=null,$attrList=null,$sortParams=null,$limitBy=null){
        if($attrList==null)
            $attrList ="*";

        $this->db->select($attrList);
        $this->db->from($table1);
        $this->db->join($table2,$table1.".".$joinElement1.' = '.$table2.".".$joinElement2,$joinType);

        if($paramList!=null)
            $this->db->where($paramList);

        if($sortParams!=null)
        {

            if(isset($sortParams['multiple'])){
                foreach($sortParams['params'] as $sortParam)
                {
                    log_message('info',indent(json_encode($sortParams)));
                    $this->db->order_by($sortParam['col'],$sortParam['order']);
                }
            }
            else{
                $this->db->order_by($sortParams['col'],$sortParams['order']);
            }
        }

        if($limitBy!=null)
            $this->db->limit($limitBy);

        $data = $this->db->get();

        return $data->result();
    }

}


/**
 * End of model
 */