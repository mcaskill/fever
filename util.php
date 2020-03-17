<?php // -------------------------------------------------------- //

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

