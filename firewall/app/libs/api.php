<?php

/**************************************************************************
 is_associative()
 **************************************************************************/
function is_associative($array)
{
	return (array_keys($array) !== array_keys(array_keys($array)));
}

/**************************************************************************
 remove_control_characters()
 **************************************************************************/
function remove_control_characters($data)
{
	return r('/[\x00-\x1F\x7F]/', '', $data);
}

/**************************************************************************
 array_to_xml()
 **************************************************************************/
function array_to_xml($array, $container = '', $is_root = true)
{
	if (!is_array($array)) return array_to_xml(array($array));

	$xml = '';

	if ($is_root)
	{
		$xml .= '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= "<{$container}>";
	}

	foreach($array as $key => $value)
	{
		// make sure key is a string
		$elem = $key;

		if (!is_string($key) && !empty($container))
		{
			$elem = $container;
		}

		$xml .= "<{$elem}>";

		if (is_array($value))
		{
			if (is_associative($value))
			{
				$xml .= array_to_xml($value, '', false);
			}
			else
			{
				$xml .= array_to_xml($value, r('/s$/', '', $elem), false);
			}
		}
		else
		{
			$xml .= (h($value) != $value) ? "<![CDATA[{$value}]]>" : $value;
		}

		$xml .= "</{$elem}>";
	}

	if ($is_root)
	{
		$xml .= "</{$container}>";
	}

	return remove_control_characters($xml);
}

/**************************************************************************
 array_to_json()

 doesn't handle objects
 **************************************************************************/
function array_to_json($array = array())
{
	if (!is_array($array)) return array_to_json(array($array));

	$is_assoc = is_associative($array);

	$json  = '';
	$json .= $is_assoc ? '{' : '[';

	foreach($array as $key => $value)
	{
		if ($is_assoc)
		{
			$json .= '"'.$key.'":';
		}

		switch(gettype($value))
		{
			case 'array':
				$json .= array_to_json($value);
			break;

			case 'string':
				$find_and_replace = array
				(
					'\\'	=> '\\\\',
					'"' 	=> '\"',
					'/'		=> '\/',
					"\b"	=> "\\b",
					"\f"	=> "\\f",
					"\n"	=> "\\n",
					"\r"	=> "\\r",
					"\t"	=> "\\t"
				);
				$json .= '"'.sr(array_keys($find_and_replace), array_values($find_and_replace), $value).'"';
			break;

			case 'boolean':
				$json .= $value ? 'true' : 'false';
			break;

			case 'integer':
			case 'float':
			case 'double':
				$json .= $value;
			break;

			default:
				$json .= 'null';
		}

		$json .= ',';
	}

	$json = r('/,$/', '', $json);

	$json .= $is_assoc ? '}' : ']';

	return remove_control_characters($json);
}