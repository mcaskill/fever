<?php
/**************************************************************************
 e()
 **************************************************************************/
function e($str = '')
{
	echo $str;
}

/**************************************************************************
 p()
 **************************************************************************/
function p($obj = null, $title = '')
{
	e('<pre>');
	if (!empty($title)) { e("{$title}: "); }
	print_r($obj);
	e('</pre>'."\n");
}

/**************************************************************************
 low()
 **************************************************************************/
function low($str = '')
{
	return strtolower($str);
}

/**************************************************************************
 up()
 **************************************************************************/
function up($str = '')
{
	return strtoupper($str);
}

/**************************************************************************
 sr()
 **************************************************************************/
function sr($find = '', $replace = '', $str = '')
{
	return str_replace($find, $replace, $str);
}

/**************************************************************************
 r()
 **************************************************************************/
function r($find = '', $replace = '', $str = '')
{
	return preg_replace($find, $replace, $str);
}

/**************************************************************************
 m()
 **************************************************************************/
function m($find = '', $str = '', &$matches)
{
	return preg_match($find, $str, $matches);
}

/**************************************************************************
 ma()
 **************************************************************************/
function ma($find = '', $str = '', &$matches)
{
	return preg_match_all($find, $str, $matches);
}

/**************************************************************************
 filled_explode()
 
 Like explode but returns a $limit-length array.
 **************************************************************************/
function filled_explode($delimiter, $string, $limit)
{
	$strings = explode($delimiter, $string, $limit);
	$strings_count = count($strings);
	if ($strings_count < $limit)
	{
		$fill_to = $limit - $strings_count;
		for ($i = 0; $i < $fill_to; $i++)
		{
			$strings[] = '';
		}
	}
	
	return $strings;
}

/******************************************************************************
 in()
 
 Searches a string or array for a needle in the haystack. A $sensitive string
 search is case-sensitive, while a $sensitive array search will search for case-
 insensitive partial matches in array values
 ******************************************************************************/
function in($haystack, $needle, $sensitive = false)
{
	$found = false;
	if (is_string($haystack))
	{
		if (!$sensitive)
		{
			$haystack	= low($haystack);
			$needle		= low($needle);
		}
		$found = (strpos($haystack, $needle) !== false);
	}
	else if (is_array($haystack))
	{
		if (!$sensitive)
		{
			$found = in_array($needle, $haystack);
		}
		else
		{
			foreach($haystack as $straw)
			{
				if ($found = in($straw, $needle))
				{
					break;
				}
			}
		}
	}
	return $found;
}

/**************************************************************************
 checksum()
 
 A 32/64-bit compatible crc32() function. Returns unsigned regarless.
 **************************************************************************/
function checksum($value)
{
	return sprintf('%u', crc32($value));
}

/******************************************************************************
 array_to_query()
 
 Converts an array into the equivalent query string, handles nested arrays
 ******************************************************************************/
function array_to_query($array = array(), $nested = array())
{
	$tmpArray = array();
	foreach ($array as $key => $value)
	{
		if (is_array($value))
		{
			$nested[] = $key;
			$tmpArray[] = array_to_query($value, $nested);
			array_pop($nested);
		}
		else
		{
			if (!empty($nested))
			{
				$keyName = $nested[0];
				if (count($nested) > 1)
				{
					$keyName .= '['.implode('][', array_slice($nested, 1)).']';
				}
				$keyName .= '['.$key.']';
			}
			else
			{
				$keyName = $key;
			}
			
			$tmpArray[] = "{$keyName}={$value}";
		}
	}
	$array = implode('&', $tmpArray);
	return $array;
}

/**************************************************************************
 err()

 Whether or not the ?errors argument was passed to this request.
 **************************************************************************/
function err()
{
	return isset($_GET['errors']);
}

/**************************************************************************
 errors_url()

 Appends the ?errors argument to the provided link if present on the current
 request.
 **************************************************************************/
function errors_url($link)
{
	if (err())
	{
		$link .= ((strpos($link, '?') === false) ? '?' : '&amp;').'errors';
	}
	return $link;
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
		$parsed_url		= array
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
 push()
 
 Pushes any available output of the script to the browser.
 **************************************************************************/
function push()
{
	e(str_pad('', 1024));
	@ob_flush();
	flush();
}