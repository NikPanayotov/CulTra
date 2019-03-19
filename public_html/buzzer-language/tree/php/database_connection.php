<?php
	// from:  https://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
	/*
	// Our database object
	$db = new Db();

	// Quote and escape form submitted values
	$name = $db -> quote($_POST['username']);
	$email = $db -> quote($_POST['email']);

	// Insert the values into the database
	$result = $db -> query("INSERT INTO `users` (`name`,`email`) VALUES (" . $name . "," . $email . ")");
	
	// A select query:
	$db = new Db();
	$rows = $db -> select("SELECT `name`,`email` FROM `users` WHERE id=5");
	
	
	rossi 12 months ago
	I recommend to put the DB connect into the constructor, this will safe the 
	"$connection = $this -> connect();" statement in each function 
	and the static declaration of the connection 
	which can be turned into a private variable.
	
	*/
	
	// Database class for connecting to MySQL
	
	class Db {
		// The database connection
		public static $connection;

		
		// NIK: get insert ID
		public function getInsertId() {
			$connection = $this -> connect();
			return $connection->insert_id;
		}
		
		
		/**
		 * Connect to the database
		 * 
		 * @return bool false on failure / mysqli MySQLi object instance on success
		 */
		public function connect() {    
			// Try and connect to the database if haven't already
			if(!isset(self::$connection)) {
				// Load configuration as an array. Use the actual location of your configuration file
				// NIK: .. instead of .
				$config = parse_ini_file('../../../../private/config_transmission.ini'); 
				self::$connection = new mysqli('localhost',$config['username'],$config['password'],$config['dbname']);
			}

			// If connection was not successful, handle the error
			if(self::$connection === false) {
				// Handle error - notify administrator, log to a file, show an error screen, etc.
				return false;	// mysqli_connect_error(); 
			}
			return self::$connection;
		}

		/**
		 * Query the database
		 *
		 * @param $query The query string
		 * @return mixed The result of the mysqli::query() function
		 */
		public function query($query) {
			// Connect to the database
			$connection = $this -> connect();
			// NIK
			if (!$connection) {
				return false;
			}
			
			// Query the database
			$result = $connection -> query($query);

			// NIK
			if($result === false) {
				// Handle failure - log the error, notify administrator, etc.
				//$error = $this -> error();
				// Send the error to an administrator, log to a file, etc.
				return false;
			}
			
			return $result;
		}

		/**
		 * Fetch rows from the database (SELECT query)
		 *
		 * @param $query The query string
		 * @return bool False on failure / array Database rows on success
		 */
		public function select($query) {
			//$rows = array();
			$result = $this -> query($query);
			if($result === false) {
				// Handle failure - log the error, notify administrator, etc.
				//$error = $this -> error();
				// Send the error to an administrator, log to a file, etc.
				return false;
			}
			return $result;
			/*while ($row = $result -> fetch_assoc()) {
				$rows[] = $row;
			}
			return $rows;*/
		}

		/**
		 * Fetch the last error from the database
		 * 
		 * @return string Database error message
		 */
		public function error() {
			$connection = $this -> connect();
			return $connection -> error;
		}

		/**
		 * Quote and escape value for use in a database query
		 *
		 * @param string $value The value to be quoted and escaped
		 * @return string The quoted and escaped string
		 */
		public function quote($value) {
			$connection = $this -> connect();
			$value = $this->sanitiseMySQL($value);	// NIK
			return "'" . $connection -> real_escape_string($value) . "'";
		}
		
		
		/*
			NIK. Sanitisation Functions
		*/
		public function sanitiseString($var) {
			$var = stripslashes($var);
			$var = strip_tags($var);
			$var = htmlentities($var);
			return $var;
		}
		
		public function sanitiseMySQL($var) {
			$connection = $this->connect();
			$var = $connection->real_escape_string($var);
			$var = $this->sanitiseString($var);
			return $var;
		}
	}

?>