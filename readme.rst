Steps of installation
=====================

**Dev. Environment**
----------------

Step 1 : Clone
----------------

      

git clone



Step 2 : Setup database
------------------------

First create a database in your local environment "template". Then configure the settings in application/config/database.php file as follows:

    $db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => 'root',
	'database' => 'template',
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



Step 3 : Open web page
----------------

Open the following url in the browser and this should create the database from migrations:

    http://localhost/template/www/



Step 4 : APIs
----------------

The Apis are available at :

    http://test.test.com/docs/XXXXX

API DOC Repository

    https://bitbucket.org/test/XXXX

Step 5 : Postman collection
----------------

The postman collection is available here:

http://test.test.com/docs/XXXX.json