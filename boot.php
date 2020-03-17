<?php error_reporting(0); ob_start(); ?>
This server either doesn't support PHP or its version of PHP doesn't support output buffer functions. <div style="display:none;">
<?php ob_end_clean();

// Make sure we haven't included any unexpected files
// that might interfer with operations.
$included_files = get_included_files();
if (!defined('UNLACE') && count($included_files) > 1)
{

	echo 'Encountered unexpected includes. Please ensure that PHP auto prepend/append file directives have been disabled for this directory.';
	echo '<pre>Files: ';
	print_r($included_files);
	echo '</pre>';
	exit();
}

// SNIP <?php // -------------------------------------------------------- //

define('APP_NAME', 'Fever');
define('SOURCES_URL', 'http://feedafever.com/gateway/');

/**************************************************************************
 remote_copy()

 Enables remote copying even when allow_url_fopen is disabled
 **************************************************************************/
function remote_copy($remote_url, $local_path)
{
	if (ini_get('allow_url_fopen'))
	{
		return copy($remote_url, $local_path);
	}
	else
	{
		$socket_timeout 	= 30;
		$request_timeout	= 30;
		$parsed_url			= array
		(
			'scheme'	=> 'http',
			'host'		=> 'localhost',
			'path'		=> '/',
			'query'		=> '',
			'port'		=> 80
		);
		$parsed_url			= array_merge($parsed_url, parse_url($remote_url));
		$path_with_query	= (!empty($parsed_url['query'])) ? "{$parsed_url['path']}?{$parsed_url['query']}" : $parsed_url['path'];

		$request = @fsockopen($parsed_url['host'], $parsed_url['port'], $error_no, $error_str, $socket_timeout);
		if ($request)
		{
			$headers  = "GET {$path_with_query} HTTP/1.1\r\n";
			$headers .= "Host: {$parsed_url['host']}\r\n\r\n";

			fwrite($request, $headers);
			stream_set_timeout($request, $request_timeout);

			$response	= '';
			while (!feof($request))
			{
				$response .= fgets($request, 1024);
			}
			fclose($request);

			$chunks	 = explode(str_repeat("\r\n", 2), $response, 2);
			$content = isset($chunks[1]) ? $chunks[1] : '';

			return save_to_file($content, $local_path);
		}
		else
		{
			return false;
		}
	}
}

/**************************************************************************
 save_to_file()
 **************************************************************************/
function save_to_file($content, $local_path)
{
	if (($h = fopen($local_path, 'w')) !== false)
	{
		return (fwrite($h, $content) && fclose($h));
	}
	else
	{
		return false;
	}
}

/**************************************************************************
 rm()

 Deletes files and directories recursively.
 **************************************************************************/
function rm($file_path)
{
	if (empty($file_path)) return;

	if (is_dir($file_path) && !is_link($file_path))
	{
		if ($dir = opendir($file_path))
		{
			while (($item = readdir($dir)) !== false)
			{
				if ($item == '.' || $item == '..')
				{
					continue;
				}
				rm($file_path.'/'.$item);
			}
			closedir($dir);
		}
		return rmdir($file_path);
	}
	else
	{
		return unlink($file_path);
	}
}

// SNIP // -------------------------------------------------------------- //

// Allow unlace.php to include just the above constants and functions.
if (defined('UNLACE')) { return; }

$required_php_version	= '4.2.3';
$required_mysql_version	= '3.23';

// No IIS. Full stop.
if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'IIS') !== false)
{
	exit('Microsoft IIS is not support.');
}

// Check PHP version
if (!version_compare(PHP_VERSION, $required_php_version, 'ge'))
{
	exit('PHP '.$required_php_version.' or higher is required. This server is running PHP '.PHP_VERSION.'.');
}

// Check MySQL version
$extensions = get_loaded_extensions();
if (!in_array('mysql', $extensions))
{
	exit('PHP on this server does not appear to be compiled with support for MySQL. MySQL or higher is required.');
}
else
{
	$mysql_version = mysql_get_client_info();
	$mysql_version = preg_replace('#(^\D*)([0-9.]+).*$#', '\2', $mysql_version); // strip extra-version cruft
	if (!version_compare($mysql_version, $required_mysql_version, 'ge'))
	{
		exit('MySQL '.$required_mysql_version.' or higher is required. This server is running MySQL '.$mysql_version.'.');
	}
}

if (!file_exists('boot.php'))
{
	// that's this file, it must exist. DANGER! DANGER!
	// something is wrong with this server's working or
	// include path
	exit('This server failed a basic check intended to prevent dataloss.');
}

if (!file_exists('index.php'))
{
	// save a copy of our utility funcs that index.php can use
	$boot	= file_get_contents('boot.php');
	$parts	= explode('// SNIP ', $boot, 3);
	save_to_file($parts[1], 'util.php');

	if (!file_exists('util.php'))
	{
		// just created this file
		exit('This directory must be writable, please set its permissions to 777.');
	}

	if (!is_dir('firewall')) { mkdir('firewall'); }
	remote_copy(SOURCES_URL.'public/firewall.php', 'index.php');
	if (!file_exists('index.php'))
	{
		// just copied this file from a remote server
		exit('Unable to copy the necessary files from a remote server. Your host may block outgoing connections.');
	}

	// existing .htaccess file must be 0777 so Pluto can take ownership of file for later modification
	$htaccess_content	= '';
	$htaccess_path 		= '.htaccess';
	if (file_exists($htaccess_path))
	{
		$htaccess_content = file_get_contents($htaccess_path);
		unlink($htaccess_path);
	}
	// $htaccess_content .= "\n# user script addition";
	save_to_file($htaccess_content, $htaccess_path);
	@chmod($htaccess_path, 0777);
}

header('Location:./');