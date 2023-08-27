<?php
/**************************************************************************
 SIDB aims to be a simple PHP 4.2.3-compatible (don't ask) path forward for
 MySQL. It will use PDO if available, falling back on mysqli_* functions,
 before trying the deprecated mysql_* functions, as a last resort.
 --------------------------------------------------------------------------
 SAMPLE USAGE
 --------------------------------------------------------------------------
 $db = SIDB('database', 'username', 'password');
 if (!$db->is_connected) die('Could not connect. '.$db->error);

 $sql = $db->prepare('SELECT * FROM `table` WHERE `id`=?', $unsafe_id);
 if ($db->query($sql)) {
 	if ($rows = $db->rows()) {
		// do something with $rows
	}
	else echo 'No matches';
 }
 else echo $db->error;

 $db->query('INSERT ...');
 $id = $db->insert_id();

 $db->quote($unsafe); // quotes and escapes, used automatically by prepare()
 **************************************************************************/
define('SIDB_API_ABSTRACT', 	'SIDB');
define('SIDB_API_PDO_MYSQL',	'SIDB_PDO_MySQL');	// preferred
define('SIDB_API_MYSQLI', 		'SIDB_MySQLi');		// fallback
define('SIDB_API_MYSQL', 		'SIDB_MySQL');		// deprecated

/**************************************************************************
 SIDB()
 Attempts to pick the best available (or requested) API.
 **************************************************************************/
function SIDB($database='', $username='', $password='', $server='localhost', $api=null) { // :SIDB
	if ($api==null) {
		if (extension_loaded('pdo_mysql') && version_compare(PHP_VERSION, '5.1.0')>=0) {
			$api = SIDB_API_PDO_MYSQL;
		}
		else if (extension_loaded('mysqli')) {
			$api = SIDB_API_MYSQLI;
		}
		else if (function_exists('mysql_connect')) {
			$api = SIDB_API_MYSQL;
		}
	}
	if (!class_exists($api)) $api = SIDB_API_ABSTRACT;

	$db = new $api;
	$db->connect($database, $username, $password, $server);
	return $db;
}

/**************************************************************************
 SIDB
 Defines an interface for all SIDB implementations.
 **************************************************************************/
class SIDB {
	public $api 			= 'Abstract SIDB'; 	// :string
	public $error			= false; 			// :string or false
	public $is_connected	= false; 			// :boolean
	protected $result		= false;			// :varies by API or false
	protected $sql			= ''; 				// :string, the most recent sql query

	// use to report unimplemented abstract methods
	public function fatal_error($method) {
		die('Fatal Error: '.get_class($this).'->'.$method.'() implementation missing');
	}

	// final methods
	public function parse_server($server) { // :array
		// eg. localhost:3306
		// or. localhost:/path/to/mysql.socket
		$result = array(
			'host'	=> '',
			'port'	=> null,
			'socket'=> null
		);

		$parts = explode(':', $server);
		$result['host'] = $parts[0];

		if (isset($parts[1])) {
			if (preg_match('/^[0-9]+$/', $parts[1])) {
				$result['port'] = $parts[1];
			}
			else {
				$result['socket'] = $parts[1];
			}
		}
		return $result;
	}
	public function prepare($sql /* ... */ ) { // :string
		$args = func_get_args();
		if (count($args) > 1)
		{
			$sql = preg_replace('/\?/', '%s', array_shift($args));
			foreach ($args as $key => $value)
			{
				$args[$key] = $this->quote($value);
			}
			array_unshift($args, $sql);
			$sql = call_user_func_array('sprintf', $args);
		}
		return $sql;
	}

	// abstract methods
	public function set_error() {
		$this->fatal_error('set_error');
	}
	public function connect($database='', $username='', $password='', $server='localhost') {
		$this->fatal_error('connect');
	}
	public function close() {
		$this->fatal_error('close');
	}
	public function quote($str) { // :string
		$this->fatal_error('quote');
	}
	public function query($sql) { // :boolean
		$this->fatal_error('query');
	}
	public function rows() { // :array
		$this->fatal_error('rows');
	}
	public function affected_rows() { // :int
		$this->fatal_error('affected_rows');
	}
	public function insert_id() { // :int
		$this->fatal_error('insert_id');
	}
	public function client_version() { // :string version
		$this->fatal_error('client_version');
	}
	public function server_version() { // :string version
		$this->fatal_error('server_version');
	}
}

/**************************************************************************
 Moved to external file to prevent PHP 4.x from shitting itself. Sigh.
 **************************************************************************/
if (version_compare(PHP_VERSION, '5.0.0')>=0) {
	include(FIREWALL_ROOT.'app/libs/SIDB5.php');
}

/**************************************************************************
 Deprecated mysql_* implementation
 **************************************************************************/
class SIDB_MySQL extends SIDB {
	public $api 		= 'MySQL (Deprecated)';
	protected $mysql	= null; // :Resource id

	public function set_error() {
		$this->error = $this->api.' Error ('.mysql_errno($this->mysql).'): '.mysql_error($this->mysql).' (SQL:'.$this->sql.')';
	}
	public function connect($database='', $username='', $password='', $server='localhost') {
		if (($this->mysql = @mysql_connect($server,  $username, $password))!==false) {
			if (@mysql_select_db($database, $this->mysql)) {
				$this->is_connected = true;
			}
			else {
				$this->set_error();
			}
		}
		else {
			$this->error = $this->api.' Error ('.mysql_errno().'): '.mysql_error();
		}
	}
	public function close() {
		@mysql_close($this->mysql);
		$this->mysql = null;
		$this->is_connected = false;
		$this->error = false;
	}
	public function quote($str) {
		if (!$this->is_connected) return "''";

		$esc = '';
		if (function_exists('mysql_real_escape_string')) {
			$esc = mysql_real_escape_string($str, $this->mysql);
		}
		else {
			$esc = mysql_escape_string($str);
		}
		return "'{$esc}'";
	}
	public function query($sql) { // : Resource id or false
		if (!$this->is_connected) return false;

		$this->error = false;
		$this->sql = $sql;

		$this->result = @mysql_query($sql, $this->mysql);
		if ($this->result===false) $this->set_error();
		return !$this->error;
	}
	public function rows() {
		$rows = array();

		if ($this->result!==false)
		{
			while ($row = mysql_fetch_assoc($this->result))
			{
				foreach ($row as $key => $value)
				{
					$row[$key] = $value;
				}
				$rows[] = $row;
			}
			mysql_free_result($this->result);
		}
		return $rows;
	}
	public function affected_rows() {
		return @mysql_affected_rows($this->mysql);
	}
	public function insert_id() {
		return @mysql_insert_id($this->mysql);
	}
	public function client_version() {
		if (!$this->is_connected) return '0.0.0';
		return mysql_get_client_info();
	}
	public function server_version() {
		if (!$this->is_connected) return '0.0.0';
		return mysql_get_server_info();
	}
}