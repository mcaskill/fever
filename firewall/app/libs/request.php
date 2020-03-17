<?php
/******************************************************************************

 todo: add separate timeouts for request and dns
       currently a request could take twice the timeout provided (dns + request)
       better error reporting
 ------------------------------------------------------------------------------
 REQUEST
 ******************************************************************************/
if (!defined('REQUEST_UA')) 			{ define('REQUEST_UA', 'SI_Request'); }
if (!defined('REQUEST_REDIRECT_LIMIT'))	{ define('REQUEST_REDIRECT_LIMIT', 4); }
if (!isset($REQUEST_TIMEOUT)) 			{ $REQUEST_TIMEOUT = 20; } // seconds

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
	global $REQUEST_REDIRECT_COUNT, $REQUEST_TIMEOUT;

	if ($following_redirect)
	{
		$REQUEST_REDIRECT_COUNT++;
	}
	else
	{
		$REQUEST_REDIRECT_COUNT = 0;
	}

	$use_curl		= in(get_loaded_extensions(), 'curl');
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
	$break			= str_repeat("\r\n", 2);

	// memory_event('b:'.$parsed_url['host']);

	// Determine how to handle provided post data
	if (is_array($post) && count($post))
	{
		$post = array_to_query($post);
	}

	$path_with_query = (!empty($parsed_url['query'])) ? "{$parsed_url['path']}?{$parsed_url['query']}" : $parsed_url['path'];

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
			curl_setopt($request, CURLOPT_HEADER, 1);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

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
		$request = @fsockopen
		(
			$parsed_url['host'],
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

	$response		= explode($break, 'Status: '.$response, 2);
	$response[0]	= explode("\r\n", $response[0]);

	$response_headers = array();
	$response_headers['response_code'] = ''; // originally defaulted to 404
	foreach($response[0] as $header)
	{
		list($key, $value) = filled_explode(': ', $header, 2);
		$response_headers[$key] = $value;
		if ($key == 'Status' && empty($response_headers['response_code']))
		{
			if (m('#[0-9]{3}#', $value, $m))
			{
				$response_headers['response_code'] = $m[0];
			}
		}
	}

	$response_obj['headers']	= $response_headers;
	$response_obj['error']		= $error;
	$response_obj['body']		= (isset($response[1])) ? $response[1] : '';

	// used to identify redirect terminal
	$response_obj['headers']['request_url']	= $url;

	// handle a specific number of redirects
	if (isset($response_obj['headers']['Location']) && $REQUEST_REDIRECT_COUNT < REQUEST_REDIRECT_LIMIT)
	{
		$redirect 		= resolve($url, $response_obj['headers']['Location']);
		$response_obj 	= request($method, $redirect, $post, $headers, true);
	}

	// memory_event('r:'.$parsed_url['host']);

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