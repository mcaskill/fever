<?php
/******************************************************************************
 added: https/SSL support
		cookie support
 TODO: add separate timeouts for request and dns
       currently a request could take twice the timeout provided (dns + request)
       better error reporting
 ------------------------------------------------------------------------------
 REQUEST
 ******************************************************************************/
if (!defined('REQUEST_UA')) 			{ define('REQUEST_UA', 'SI_Request'); }
if (!defined('REQUEST_REDIRECT_LIMIT'))	{ define('REQUEST_REDIRECT_LIMIT', 4); }
if (!isset($REQUEST_TIMEOUT)) 			{ $REQUEST_TIMEOUT = 10; } // seconds
if (!isset($REQUEST_SOCKET))			{ $REQUEST_SOCKET = false; }

/*-----------------------------------------------------------------------------
 get()									  Get the contents of the requested url
 ******************************************************************************/
function get($url = '', $headers = array())
{
	return request('GET', $url, '', $headers);
}

/*-----------------------------------------------------------------------------
 post()							Get the contents of the requested url with post
 ******************************************************************************/
function post($url = '', $post = '', $headers = array())
{
	return request('POST', $url, $post, $headers);
}

/*-----------------------------------------------------------------------------
 head()								Get just the headers from the requested url
 ******************************************************************************/
function head($url = '', $headers = array())
{
	return request('HEAD', $url, '', $headers);
}

/******************************************************************************
 request()

 Used by get and post functions. Branches based on availability of cURL. $method
 is either GET or POST, $post may be an associative array or a query string,
 $headers should be an array of strings in the following format, Header: value
 ******************************************************************************/
$REQUEST_REDIRECT_COUNT = 0;
function request($method = '', $url = '', $post = '', $headers = array(), $following_redirect = false)
{
	global $REQUEST_REDIRECT_COUNT, $REQUEST_TIMEOUT, $REQUEST_SOCKET;

	if ($following_redirect)
	{
		$REQUEST_REDIRECT_COUNT++;
	}
	else
	{
		$REQUEST_REDIRECT_COUNT = 0;
	}

	$use_curl		= (in(get_loaded_extensions(), 'curl') && !$REQUEST_SOCKET);
	// curl installed but disabled
	if ($use_curl && in(ini_get('disable_functions'), 'curl_'))
	{
		$use_curl = false;
	}
	$response 		= '';
	$response_obj	= array();
	$time_out		= $REQUEST_TIMEOUT;
	$error			= array
	(
		'type'		=> '',
		'msg'		=> '',
		'no' 		=> 0
	);

	$ua = 'User-Agent: '.REQUEST_UA;
	if (!in($headers, $ua))
	{
		$headers[] = $ua;
	}

	// Parse url for parts
	$parsed_url		= array
	(
		'scheme'	=> 'http',
		'host'		=> 'localhost',
		'path'		=> '/',
		'query'		=> '',
		'port'		=> 80
	);
	$parsed_url		= array_merge($parsed_url, parse_url($url));
	if ($parsed_url['scheme'] == 'https')
	{
		$parsed_url['port'] = 443;
	}
	$break			= str_repeat("\r\n", 2);

	// memory_event('b:'.$parsed_url['host']);

	// Determine how to handle provided post data
	if (is_array($post) && count($post))
	{
		$post = array_to_query($post);
	}

	$path_with_query = (!empty($parsed_url['query'])) ? "{$parsed_url['path']}?{$parsed_url['query']}" : $parsed_url['path'];

	debug(array('timeout' => $time_out, 'url' => $parsed_url, 'headers' => $headers),'request ('.($use_curl ? 'curl' : 'socket').')');

	// cURL branch
	if ($use_curl)
	{
		$error['type'] = 'cURL';
		$curl_url = "{$parsed_url['scheme']}://{$parsed_url['host']}:{$parsed_url['port']}{$path_with_query}";
		if ($request = curl_init($curl_url))
		{
			curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $time_out);
			curl_setopt($request, CURLOPT_TIMEOUT, $time_out);
			curl_setopt($request, CURLOPT_HEADER, true);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($request, CURLOPT_ENCODING, 1);

			if ($parsed_url['scheme'] == 'https')
			{
				curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
			}

			if ($method == 'POST')
			{
				curl_setopt($request, CURLOPT_POST, true);
				curl_setopt($request, CURLOPT_POSTFIELDS, $post);
			}
			else if ($method == 'HEAD')
			{
				curl_setopt($request, CURLOPT_NOBODY, true);
			}

			$response = curl_exec($request);

			if ($error['no'] = curl_errno($request))
			{
				$error['msg'] = curl_error($request);
			}
			curl_close($request);
		}
		else
		{
			$error['msg'] = "Invalid request for: {$curl_url} (cURL)";
		}
	}

	// Socket branch
	else
	{
		$error['type'] = 'Socket';
		$sock_headers = array
		(
			"{$method} {$path_with_query} HTTP/1.0",
			"Host: {$parsed_url['host']}",
		);
		if ($method == 'POST')
		{
			$sock_headers[] = 'Content-type: application/x-www-form-urlencoded';
			$sock_headers[] = 'Content-length: '.strlen($post);
		}
		$sock_headers = array_merge($sock_headers, $headers);
		$sock_headers[] = 'Connection: Close';

		$socket_scheme = '';
		if ($parsed_url['scheme'] == 'https')
		{
			$socket_scheme = 'ssl://';
		}
		$request = fsockopen
		(
			$socket_scheme.$parsed_url['host'],
			$parsed_url['port'],
			$error['no'],
			$error['msg'],
			$time_out
		);

		if ($request)
		{
			$str_headers = implode("\r\n", $sock_headers).$break.$post;

			fwrite($request, $str_headers);
			stream_set_timeout($request, $time_out);
			while (!feof($request))
			{
				$response .= fgets($request, 1024);
			}
		}
	}

	$debug = explode('close', $response, 2);

	## separating headers from content
	// 0d0a \r\n normal
	// 0a0a \n\n news.ycombinator.com

	## separating headers from headers
	// 0d0a \r\n normal
	// 0a   \n   news.ycombinator.com

	$response		= preg_split("#(\r?\n){2}#", 'Status: '.$response, 2);
	$response[0]	= preg_split("#\r?\n#", $response[0]);

	$response_headers = array();
	$response_headers['response_code'] = ''; // originally defaulted to 404
	foreach($response[0] as $header)
	{
		list($key, $value) = filled_explode(': ', $header, 2);
		if (!isset($response_headers[$key]))
		{
			$response_headers[$key] = $value;
		}
		else
		{
			if (!is_array($response_headers[$key]))
			{
				$response_headers[$key] = array($response_headers[$key]);
			}
			$response_headers[$key][] = $value;
		}
		if ($key == 'Status' && empty($response_headers['response_code']))
		{
			if (m('#[0-9]{3}#', $value, $m))
			{
				$response_headers['response_code'] = $m[0];
			}
		}
	}

	debug($response_headers, 'response headers');

	$response_obj['headers']	= $response_headers;
	$response_obj['cookies']	= array();
	$response_obj['error']		= $error;
	$response_obj['body']		= (isset($response[1])) ? $response[1] : '';

	if (isset($response_obj['headers']['set-cookie']))
	{
		foreach((array) $response_obj['headers']['set-cookie'] as $set_cookie)
		{
			$cookie_parts = explode(';', $set_cookie);
			if (isset($cookie_parts[0]))
			{
				$cookie_parts[0] = trim($cookie_parts[0]);
				if (!empty($cookie_parts[0]))
				{
					list($cookie_name, $cookie_value) = array_pad(explode('=', $cookie_parts[0], 2), 2, null);
					$response_obj['cookies'][$cookie_name] = $cookie_value;
				}
			}
		}
	}

	// used to identify redirect terminal
	$response_obj['headers']['request_url']	= $url;

	// handle a specific number of redirects
	if ((isset($response_obj['headers']['Location']) || isset($response_obj['headers']['location'])) && $REQUEST_REDIRECT_COUNT < REQUEST_REDIRECT_LIMIT)
	{
		$location 		= (isset($response_obj['headers']['Location'])) ? $response_obj['headers']['Location'] : $response_obj['headers']['location'];
		$redirect 		= resolve($url, $location);
		$response_obj 	= request($method, $redirect, $post, $headers, true);
	}

	if (!empty($error['msg']) || $error['no'] != 0)
	{
		debug($error, 'request error');
	}

	memory_event('r:'.$parsed_url['host']);

	return $response_obj;
}

/**************************************************************************
 get_redirect_terminal()

 Follows redirects until there are none left to follow. SLOW with some DNS!

 eg. get_redirect_terminal('http://dn.vc//4rn');
 **************************************************************************/
function get_redirect_terminal($url)
{
	$response = head($url);
	return $response['headers']['request_url'];
}