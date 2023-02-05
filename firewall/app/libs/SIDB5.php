<?php
/**************************************************************************
 PDO/MySQL implementation
 **************************************************************************/
class SIDB_PDO_MySQL extends SIDB {
	var $api = 'PDO/MySQL';
	var $pdo = null; // :PDO

	function set_error() {
		list($e, $errno, $error) = $this->pdo->errorInfo();
		$this->error = "{$this->api} Error ({$errno}): {$error}".' (SQL:'.$this->sql.')';
	}
	function connect($database='', $username='', $password='', $server='localhost') {
		$server = $this->parse_server($server);

		$dsn = 'mysql:';
		if ($server['socket']) {
			$dsn .= "unix_socket={$server['socket']};";
		}
		else {
			$dsn .= "host={$server['host']};";
			if ($server['port']) $dsn .= "port={$server['port']};";
		}
		$dsn .= "dbname={$database};";

		try {
			$this->pdo = new PDO($dsn, $username, $password);
			$this->is_connected = true;
		}
		catch (PDOException $e) {
			$this->error = $this->api.' Error ('.$e->getCode().'): '.$e->getMessage();
		}
	}
	function close() {
		$this->pdo = null;
		$this->is_connected = false;
		$this->error = false;
	}
	function quote($str) {
		if (!$this->is_connected) return "''";
		return $this->pdo->quote($str);
	}
	function query($sql) {
		if (!$this->is_connected) return false;

		// See http://br2.php.net/manual/en/pdo.query.php (paragraph
		// above the first NOTE re: PDOStatement::closeCursor())
		if ($this->result) {
			$this->result->closeCursor();
			$this->result = false;
		}

		$this->error = false;
		$this->sql = $sql;

		$this->result = $this->pdo->query($sql);
		if ($this->result===false) $this->set_error();

		return !$this->error;
	}
	function rows() {
		$rows = array();

		if ($this->result!==false)
		{
			while ($aRow = $this->result->fetch())
			{
				$row = array();
				foreach ($aRow as $key => $value)
				{
					if (is_int($key)) continue;
					$row[$key] = $value;
				}
				$rows[] = $row;
			}
		}
		return $rows;
	}
	function affected_rows() {
		return $this->result->rowCount();
	}
	function insert_id() {
		return $this->pdo->lastInsertId();
	}
	function client_version() {
		if (!$this->is_connected) return '0.0.0';
		return $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}
	function server_version() {
		if (!$this->is_connected) return '0.0.0';
		return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
}

/**************************************************************************
 MySQL Improved implementation
 **************************************************************************/
class SIDB_MySQLi extends SIDB {
	var $api 	= 'MySQL Improved';
	var $mysqli	= null; // :mysqli

	function set_error() {
		$this->error = $this->api.' Error ('.$this->mysqli->errno.'): '.$this->mysqli->error.' (SQL:'.$this->sql.')';
	}
	function connect($database='', $username='', $password='', $server='localhost') {
		$server = $this->parse_server($server);

		if (($this->mysqli = @mysqli_connect($server['host'], $username, $password, $database, $server['port'], $server['socket']))!==false) {
			$this->is_connected = true;
		}
		else {
			$this->error = $this->api.' Error ('.mysqli_connect_errno().'): '.mysqli_connect_error();
		}
	}
	function close() {
		if (!$this->is_connected) return;

		$this->mysqli->close();
		$this->mysqli = null;
		$this->is_connected = false;
		$this->error = false;
	}
	function quote($str) {
		if (!$this->is_connected) return "''";
		return "'".$this->mysqli->real_escape_string($str)."'";
	}
	function query($sql) {
		if (!$this->is_connected) return false;

		$this->error = false;
		$this->sql = $sql;

		$this->result = $this->mysqli->query($sql);
		if ($this->result===false) $this->set_error();

		return !$this->error;
	}
	function rows() {
		$rows = array();

		if ($this->result!==false)
		{
			while ($row = $this->result->fetch_array(MYSQLI_ASSOC))
			{
				foreach ($row as $key => $value)
				{
					$row[$key] = $value;
				}
				$rows[] = $row;
			}
			$this->result->free();
		}
		return $rows;
	}
	function affected_rows() {
		return $this->mysqli->affected_rows;
	}
	function insert_id() {
		return $this->mysqli->insert_id;
	}
	function client_version() {
		if (!$this->is_connected) return '0.0.0';
		return $this->mysqli->client_info;
	}
	function server_version() {
		if (!$this->is_connected) return '0.0.0';
		return $this->mysqli->server_info;
	}
}