<?php
/**
 * User: Kapil Shamlani
 * Date: 11/12/18
 * Time: 10:02
 */
class Migration_create_flight_store_table extends CI_Migration {
    function up()
    {
        //*** flight_store
        //Drop table if exists
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->query('DROP TABLE IF EXISTS flight_store');
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // creating flight_store table
        $this->dbforge->add_field(array(
            'id' =>  array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),
            'year' => array(
                'type' => 'INT',
                'null'  => true,
                'comment' => 'eg 1999-2018'
            ),
            'month' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'eg 1-12'
            ),
            'day' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'eg 1-31'
            ),
            'week' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'eg 1(monday) - 7(sunday)'
            ),
            'departure_time' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'scheduled departure time (hhmm)'
            ),
            'actual_departure_time' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'recorded departure time (hhmm)'
            ),
            'arrival_time' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'recorded arrival time (hhmm)'
            ),
            'carrier' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'null' => false,
                'comment' => 'carrier code (unique)'
            ),
            'flight_number' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'flight number'
            ),
            'departure_delay' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'in minutes'
            ),
            'arrival_delay' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'in minutes'
            ),
            'cancellation' => array(
                'type' => 'ENUM("yes","no")',
                'default' => 'no',
                'null' => true,
                'comment' => 'yes & no'
            ),
            'weather_delay' => array(
                'type' => 'INT',
                'null' => true,
                'comment' => 'in minutes'
            ),
            'created_on' => array(
                'type' => 'INT',
                'null'  => TRUE
            ),
            'updated_on' => array(
                'type' => 'INT',
                'null'  => TRUE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('flight_store');
    }

    function down()
    {
        $this->dbforge->drop_table('flight_store');
    }
}