<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_base_tables extends CI_Migration {

	public function up()
	{
	    //***** table auth_users
        //Drop auth_users table if exists
        $this->dbforge->drop_table('auth_users', TRUE);

        // Table structure for table 'auth_users'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => FALSE
            ),
            'password' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null'  => TRUE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'profile_pic_url' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'phone_number' => array(
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null'  => FALSE
            ),
            'updated_on' => array(
                'type' => 'DATETIME',
                'null'  => TRUE
            ),
            'status' => array(
                'type' => 'ENUM("inactive","active","blocked")',
                'default' => 'inactive',
                'comment' => 'inactive: User is yet to complete activation process, active: User is active, blocked: admin has blocked user'
            ),
            'login_type' => array(
                'type' => 'ENUM("general","google","facebook")',
                'default' => 'general',
                'comment' => 'general: Default signup process, google: singup from google, facebook: signup from facebook'
            ),
            'social_id' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'Social id - when user sign up with social login'
            ),
            'social_auth_token' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'Social auth token - when user sign up with social login token will be stored'
            ),
            'gender' => array(
                'type' => 'ENUM("male","female","other")',
                'null' => TRUE,
            ),
            'dob' => array(
                'type' => 'DATE',
                'comment' => 'Date of birth',
                'null' => TRUE,
            ),
            'is_first_time' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => 0,
                'comment' => 'Whether user is visiting first time(1) or not(0)'
            ),
            'is_phone_verified' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => 0,
                'comment' => 'Whether user\'s phone number is verified(1) or not(0)'
            ),
            'is_tnc_accepted' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => 0,
                'comment' => 'Whether user has accepted terms and conditions(1) or not(0)'
            ),
            'is_profile_complete' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => 0,
                'comment' => 'Whether user\'s profile is complete(1) or not(0)'
            ),
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => '16'
            ),
            'salt' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'activation_code' => array(
                'type' => 'VARCHAR',
                'constraint' => '40',
                'null' => TRUE
            ),
            'forgotten_password_code' => array(
                'type' => 'VARCHAR',
                'constraint' => '40',
                'null' => TRUE
            ),
            'forgotten_password_time' => array(
                'type' => 'INT',
                'constraint' => '11',
                'unsigned' => TRUE,
                'null' => TRUE
            ),
            'remember_code' => array(
                'type' => 'VARCHAR',
                'constraint' => '40',
                'null' => TRUE
            ),
            'last_login' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            )

        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_users');

        //Insert data for administrator
        $data = array(
            'email' => 'admin@admin.com',
            'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
            'name' => 'Administrator',
            'created_on' => '2017-05-12 15:20:00',
            'login_type' => 1, //for active initially
            'ip_address' => '127.0.0.1',
            'salt' => '',
            'status' => 2,
            'is_phone_verified' => 1

        );

        $this->db->insert('auth_users', $data);

        // Drop table 'login_attempts' if it exists
        $this->dbforge->drop_table('login_attempts', TRUE);

        // Table structure for table 'login_attempts'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'MEDIUMINT',
                'constraint' => '8',
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => '16'
            ),
            'login' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null', TRUE
            ),
            'time' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('login_attempts');


        //*** table auth_groups
        //Drop table if exists
        $this->dbforge->drop_table('auth_groups', TRUE);

        //Table structure for auth_groups

        $this->dbforge->add_field(array(
            'id' => array(
               'type' => 'INT',
               'auto_increment' => TRUE,
               'null' => FALSE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null'  => FALSE,
                'comment' => 'Auth group name'

            ),
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_groups');

        //Insert data for 'auth_groups'
        $data = array(
            array(
                'id' => 1,
                'name' => 'Admin'
            ),
            array(
                'id' => 2,
                'name' => 'Tourist'
            ),
            array(
                'id' => 3,
                'name' => 'Tour_guide'
            )
        );

        $this->db->insert_batch('auth_groups', $data);


        //*** auth_users_groups
        //Drop table if exists
        $this->dbforge->drop_table('auth_users_groups', TRUE);

        $this->dbforge->add_field(array(
            'id' =>  array(
               'type' => 'INT',
               'auto_increment' => TRUE
            ),
            'group_id'  => array(
                'type' => 'INT',
            ),
            'user_id' => array(
                'type' => 'INT'
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_users_groups');


        // add constraint for 'auth_users_groups'
        $this->db->query("ALTER TABLE auth_users_groups ADD INDEX `fk_group_id_idx` (`group_id` ASC)");
        $this->db->query("ALTER TABLE auth_users_groups ADD INDEX `fk_user_id_idx` (`user_id` ASC)");
        $this->db->query("ALTER TABLE auth_users_groups ADD CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->db->query("ALTER TABLE auth_users_groups ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION");


        //Insert data for admin group in 'auth_users_groups'
        $data = array(
            'group_id' => 1,
            'user_id' => 1
        );

        $this->db->insert('auth_users_groups', $data);


        //*** auth_users_otp
        //Drop table if exists
        $this->dbforge->drop_table('auth_users_otp', TRUE);

        $this->dbforge->add_field(array(
            'id' =>  array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),
            'user_id'  => array(
                'type' => 'INT',
            ),
            'otp' => array(
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => FALSE,
                'comment' => 'One time password'
            ),
            'is_active' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => 1,
                'comment' => 'Whether otp is active(1) or expired(0)'
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null'  => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_users_otp');


        // add constraint for 'auth_users_otp'
        $this->db->query("ALTER TABLE auth_users_otp ADD INDEX `fk_otp_user_id_idx` (`user_id` ASC)");
        $this->db->query("ALTER TABLE auth_users_otp ADD CONSTRAINT `fk_otp_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE ");


        //*** auth_tokens
        //Drop table if exists
        $this->dbforge->drop_table('auth_tokens', TRUE);

        $this->dbforge->add_field(array(
            'id' =>  array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),
            'user_id'  => array(
                'type' => 'INT',
                'null' => FALSE
            ),
            'auth_token' => array(
                'type' => 'TEXT',
                'null' => FALSE,
                'comment' => 'JWT token'
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null'  => TRUE
            ),
            'is_active' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null'  => TRUE
            )

        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_tokens');

        // add constraint for 'auth_tokens'
        $this->db->query("ALTER TABLE auth_tokens ADD INDEX `fk_auth_tokens_user_id_idx` (`user_id` ASC)");
        $this->db->query("ALTER TABLE auth_tokens ADD CONSTRAINT `fk_auth_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE ");

        //***** table keys
        //Drop keys table if exists
        $this->dbforge->drop_table('keys', TRUE);

        // Table structure for table 'keys'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'key' => array(
                'type' => 'VARCHAR',
                'constraint' => '40',
                'null' => FALSE
            ),
            'level' => array(
                'type' => 'INT',
                'constraint' => '2',
                'null'  => FALSE
            ),
            'ignore_limits' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => FALSE

            ),
            'is_private_key' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => FALSE,
            ),
            'ip_addresses' => array(
                'type' => 'TEXT',
                'null'  => FALSE
            ),
            'date_created' => array(
                'type' => 'INT',
                'null' => FALSE
            )

        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('keys');


        //Insert data for keys
        $data = array(
            'key' => '2J32k8nXc5bRoIxbJrloZrh78ytg6tat7HRjer2h',
            'level' => 0,
            'ignore_limits' => 0,
            'is_private_key' => 0,
            'ip_addresses' => 'NULL',
            'date_created' => 0,
        );
        $this->db->insert('keys', $data);


        //***** table auth_devices
        //Drop auth_devices table if exists
        $this->dbforge->drop_table('auth_devices', TRUE);

        // Table structure for table 'auth_devices'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'user_id' => array(
                'type' => 'INT',
                'null' => FALSE
            ),
            'device_id' => array(
                'type' => 'VARCHAR',
                'constraint' => '300',
                'null' => TRUE
            ),
            'device_token' => array(
                'type' => 'TEXT',
                'null' => FALSE
            ),
            'aws_arn' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'os_type' => array(
                'type' => 'TINYINT',
                'constraint' => '2',
                'default' => 0,
                'null' => TRUE
            ),
            'os_version' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE
            ),
            'app_version' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'last_seen' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'last_login' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'is_active' => array(
                'type' => 'TINYINT',
                'constraint' => '2',
                'default' => 1,
                'null' => FALSE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('auth_devices');


        // add constraint for 'auth_devices'
        $this->db->query("ALTER TABLE auth_devices ADD INDEX `fk_auth_devices_user_id_idx` (`user_id` ASC)");
        $this->db->query("ALTER TABLE auth_devices ADD CONSTRAINT `fk_auth_devices_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE");



    }

	public function down()
	{
        $this->dbforge->drop_table('keys', TRUE);
        $this->dbforge->drop_table('auth_users_otp', TRUE);
        $this->dbforge->drop_table('auth_users_groups', TRUE);
        $this->dbforge->drop_table('auth_groups', TRUE);
        $this->dbforge->drop_table('auth_users', TRUE);
        $this->dbforge->drop_table('auth_devices', TRUE);


	}
}
