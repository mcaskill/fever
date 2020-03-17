<?php
$db = $_POST['db'];
$db['table_prefix'] = 'fever_scs_'.time().'_';

// trim whitespace
foreach($db as $key => $value)
{
	$db[$key] = trim($value);
}

/**************************************************************************
 db_connect()
 **************************************************************************/
function db_connect($db)
{
	if (!@mysql_connect($db['server'],$db['username'],$db['password']))
	{
		return 'Could not connect to the database server. Error: '.mysql_error();
	}

	return true;
}

/**************************************************************************
 db_select()
 **************************************************************************/
function db_select($db, $second_attempt = false)
{
	if (!mysql_select_db($db['database']))
	{
		$query = "CREATE DATABASE `{$db['database']}`";
		if (!mysql_query($query))
		{
			return 'Could not select or create the database. Error: '.mysql_error();
		}
		else if (!mysql_select_db($db['database']))
		{
			return 'Could not select the database. Error: '.mysql_error();
		}
	}

	return true;
}

/**************************************************************************
 db_create_table()
 **************************************************************************/
function db_create_table($db)
{
	$mysqlVersion = mysql_get_client_info();
	$mysqlVersion = preg_replace('#(^\D*)([0-9.]+).*$#', '\2', $mysqlVersion); // strip extra-version cruft
	$engine_type = ($mysqlVersion > 4) ? 'ENGINE' : 'TYPE';

	$query = "CREATE TABLE `{$db['table_prefix']}tmp` (id int(10) unsigned NOT NULL auto_increment, PRIMARY KEY (id)) {$engine_type}=MyISAM;";
	if (!mysql_query($query))
	{
		return 'Could not create database table. Error: '.mysql_error();
	}
	return true;
}

/**************************************************************************
 db_alter_table()
 **************************************************************************/
function db_alter_table($db)
{
	$query = "ALTER TABLE `{$db['table_prefix']}tmp` ADD tmp VARCHAR(15) NOT NULL";
	if (!mysql_query($query))
	{
		return 'Could not alter database table. Error: '.mysql_error();
	}
	return true;
}

/**************************************************************************
 db_drop_table()
 **************************************************************************/
function db_drop_table($db)
{
	$query = "DROP TABLE `{$db['table_prefix']}tmp`";
	if (!mysql_query($query))
	{
		return 'Could not drop database table. Error: '.mysql_error();
	}
	return true;
}

$db_tests = array
(
	'db_connect',
	'db_select',
	'db_create_table',
	'db_alter_table',
	'db_drop_table'
);

$msg = 'pass';
foreach($db_tests as $test)
{
	$result = call_user_func($test, $db);
	if ($result !== true)
	{
		$msg = $result;
		break;
	}
}

if ($msg == 'pass')
{
	// save connection details for app to grab
	$db_password = r("/'/", "\'", $db['password']);
	$db_php  = '<?php';
	$db_php .= <<<PHP

define('DB_SERVER', 	'{$db['server']}');
define('DB_DATABASE', 	'{$db['database']}');
define('DB_USERNAME', 	'{$db['username']}');
define('DB_PASSWORD', 	'{$db_password}');

PHP;

	save_to_file($db_php, FIREWALL_ROOT.'receipt_db.php');
}
else
{
	$msg .= ' Please direct any enquiries about database errors to your host or sysadmin.';
}

?>
<script type="text/javascript">
parent.mysqlCheckComplete("<?php e($msg); ?>");
</script>

MYSQL CHECK DONE.