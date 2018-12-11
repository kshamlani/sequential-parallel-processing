Steps of installation
=====================

**Dev. Environment**
----------------

Step 1 : Clone or Download
----------------

git clone https://github.com/kshamlani/sequential-parallel-processing.git



Step 2 : Setup database
------------------------

First create a database in your local environment "template". Then configure the settings in application/config/database.php file as follows:

    $db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => 'root',
	'database' => 'sequential_parallel_processing',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

P.S. make sure your database name is same as above defined

Step 3 : Change project url to your localhost folder
----------------

Open the following url in the browser and this should create the database from migrations:

   e.g. http://localhost/sequential_parallel_processing/www/

   make sure your project folder name is sequential_parallel_processing,
   - if you want to change the folder name, make sure to make name changes in following files
     - index.php
	 - www/.htaccess
	 - application/config/config.php

