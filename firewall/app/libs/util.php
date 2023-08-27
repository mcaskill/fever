<?php

/**************************************************************************
 ms()														microtime float
 **************************************************************************/
function ms()
{
	list($s, $m) = explode(' ', microtime());
	return ((float) $s + (float) $m);
}

/**************************************************************************
 elapsed_execution_time()

 Returns how long this script has been running
 **************************************************************************/
define('EXECUTION_START', ms());
function elapsed_execution_time()
{
	return ms() - EXECUTION_START;
}

/**************************************************************************
 e()
 **************************************************************************/
function e($str)
{
	echo $str;
}

/**************************************************************************
 p()
 **************************************************************************/
function p($obj, $title = '', $print=true)
{
	$html = '';
	if (!empty($title)) { $html .= "{$title}: "; }
	$html .= print_r($obj, true);

	if ($print) e("<pre>{$html}</pre>\n");
	return $html;
}

/**************************************************************************
 ptab()

 Prints a tab-separated table of an array of associative database rows
 **************************************************************************/
function ptab($rows, $label='', $print=true) {
	$html = '';

	if (!empty($label)) {
		$html .= $label.":\r";
	}

	foreach ($rows[0] as $lab=>$value) {
		$html .= $lab."\t";
	}
	$html .= "\r";

	foreach ($rows as $row) {
		foreach ($row as $lab=>$value) {
			$html .= $value."\t";
		}
		$html .= "\r";
	}

	if ($print) p($html);
	return $html;
}

/**************************************************************************
 low()
 **************************************************************************/
function low($str)
{
	return strtolower($str);
}

/**************************************************************************
 up()
 **************************************************************************/
function up($str)
{
	return strtoupper($str);
}

/**************************************************************************
 sr()
 **************************************************************************/
function sr($find, $replace, $str)
{
	return str_replace($find, $replace, $str);
}

/**************************************************************************
 r()
 **************************************************************************/
function r($find, $replace, $str)
{
	return preg_replace($find, $replace, $str);
}

/**************************************************************************
 m()
 **************************************************************************/
function m($find, $str, &$matches = null)
{
	return preg_match($find, $str, $matches);
}

/**************************************************************************
 ma()
 **************************************************************************/
function ma($find, $str, &$matches = null)
{
	return preg_match_all($find, $str, $matches);
}

/**************************************************************************
 nl()
 **************************************************************************/
function nl($str)
{
	return "{$str}\n";
}

/**************************************************************************
 t()
 **************************************************************************/
function t($str, $tabs = 0)
{
	return str_repeat("\t", $tabs).$str;
}

/**************************************************************************
 excerpt()

 Takes an html string and returns the first 200 characters of the first
 non-empty paragraph tag/double line-break trimmed to the nearest word and
 appended with an ellipsis.
 **************************************************************************/
function excerpt($html, $max_length = 200)
{
	$content = $html.'</p>'; // the following regex executes forever on some strings without the closing </p>
	if (ma('#(.+)(</p>|(<br( /)?>{2,}))#misU', $content, $m))
	{
 		foreach($m[1] as $p)
		{
			$stripped = trim(strip_tags_sane($p));
			if (!empty($stripped))
			{
				$content = $p;
				break;
			}
		}
	}

	$content	= strip_tags_sane($content);
	$content	= trim(r('/\s+/', ' ', $content));
	if (strlen($content) > $max_length)
	{
		$content	= substr($content, 0, $max_length);
		$last_space	= strrpos($content, ' ');
		$content	= substr($content, 0, $last_space);
		$content	= r('/\.$/', '', $content);
		$content   .= '&#8230;';
	}
	return $content;
}

/**************************************************************************
 pluralize()

 A basic pluralize function
 **************************************************************************/
function pluralize($num, $singular_str, $include_num = true)
{
	$return = '';
	if ($include_num)
	{
		$return .= $num.' ';
	}
	if ($num == 1)
	{
		$return .= $singular_str;
	}
	else
	{
		$return .= $singular_str.'s';
	}
	return $return;
}

/**************************************************************************
 checksum()

 A 32/64-bit compatible crc32() function. Returns unsigned regardless.
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
 array_frequency()

 Returns an array indexed by the values of the argument array, sorted by
 frequency of those values. Now case-insensitive.
 **************************************************************************/
function array_frequency($array)
{
	$frequency_array 	= array();
	$low_array 			= array();
	$case_map 			= array();
	foreach($array as $key => $value)
	{
		$low = low($value);
		if (!isset($case_map[$low]))
		{
			$case_map[$low] = $value;
		}

		if (!isset($low_array[$low]))
		{
			$low_array[$low] = 0;
		}

		$low_array[$low]++;
		$frequency_array[$case_map[$low]] = $low_array[$low];
	}
	arsort($frequency_array);
	return $frequency_array;
}

/**************************************************************************
 key_remap()

 Returns a new multi-dimensional array with array values indexed by the
 specified key of the nested array.
 **************************************************************************/
function key_remap($key, $multi)
{
	$remapped = array();
	foreach($multi as $array)
	{
		if (isset($array[$key]))
		{
			$remapped[$array[$key]] = $array;
		}
	}

	return $remapped;
}

/**************************************************************************
 empty_explode()

 Like explode but an empty string results in an empty array.
 **************************************************************************/
function empty_explode($delimiter, $string)
{
	$array = array();
	if (!empty($string))
	{
		$array = explode($delimiter, $string);
	}
	return $array;
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
 h()							A convenience function for htmlspecialchars
 **************************************************************************/
function h($str)
{
	return htmlspecialchars($str, ENT_COMPAT, 'ISO-8859-1'); // default charset in 5.4 is UTF-8
//	$args = func_get_args();
//	return call_user_func_array('htmlspecialchars', $args);
}

/**************************************************************************
 lt()									   Encodes left angle brackets only
 **************************************************************************/
function lt($string)
{
	return sr('<', '&lt;', $string);
}

/**************************************************************************
 onload()

 Allows you to run arbitrary JavaScript from an XHR response.
 **************************************************************************/
function onload($js)
{
	e('<img onload="'.$js.'this.parentNode.removeChild(this);" class="onloader" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" />');
}

/**************************************************************************
 encode_embedded_cdata()						  DEPRECATED/NO LONGER USED

 Sniffs out <![CDATA[ ... ]]> blocks and encodes html entites found within.
 **************************************************************************/
function encode_embedded_cdata($html)
{
	// p($html);
	$html = preg_replace_callback('#<!\[CDATA\[(.*?)]]>#smu', 'callback_stripslashes_htmlentities', $html);
	// p($html);
	return $html;
}
function callback_stripslashes_htmlentities($m) {
	return stripslashes(htmlentities($m[1], ENT_COMPAT, 'UTF-8'));
}

/**************************************************************************
 get_safe_html_translation_table()

 Accounts for changes introduced in PHP 5.4.
 **************************************************************************/
function get_safe_html_translation_table() {
	if (defined('ENT_HTML401')) return get_html_translation_table(HTML_ENTITIES, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
	else return get_html_translation_table(HTML_ENTITIES);
}

/**************************************************************************
 html_entity_decode_utf8()

 Decodes HTML entities into their utf-8 equivalents.
 **************************************************************************/
function html_entity_decode_utf8($str)
{
	static $translation_table;
	$str = preg_replace_callback('/&#x0*([0-9a-f]+);/i', 'callback_code2utf_hexdec', $str);
	$str = preg_replace_callback('/&#0*([0-9]+);/', 'callback_code2utf', $str);
	if (!isset($translation_table))
	{
		$translation_table = array();
		foreach (get_safe_html_translation_table(HTML_ENTITIES) as $val=>$key)
		{
			$translation_table[$key] = utf8_encode($val);
		}
	}
	return strtr($str, $translation_table);
}
function callback_code2utf_hexdec($m) {
	return code2utf(hexdec($m[1]));
}
function callback_code2utf($m) {
	return code2utf($m[1]);
}

/**************************************************************************
 html_entity_decode_utf8_pre()

 Acts like html_entity_decode_utf8() but does not decode entities inside
 pre and code elements. THIS IS A HACK. Need smarter decoding in OMDOMDOM.
 **************************************************************************/
function html_entity_decode_utf8_pre($str)
{
	// protect pre and code from decoding by hashing
	$protected = array
	(
		'pre',
		'code'
	);
	$hashes	= array();
	foreach($protected as $tag)
	{
		if (preg_match_all("#(<{$tag}[^>]*>.*</{$tag}>)#Ums", $str, $m))
		{
			foreach ($m[0] as $i => $find)
			{
				$hash			= '<!--'.md5($find).'-->';
				$hashes[$hash]	= $find;
				$str			= str_replace($m[0][$i], $hash, $str);
			}
		}
	}
	$str = html_entity_decode_utf8($str); // converts &quot; to " so the replacement below can do its thing
	$str = sr(array_keys($hashes), array_values($hashes), $str); // reinsert protected tags
	return $str;
}

/**************************************************************************
 code2utf()

 Used by html_entity_decode_utf8().
 **************************************************************************/
function code2utf($num)
{
	if ($num < 0)
	{
		return false;
	}
	if ($num < 128)
	{
		return chr($num);
	}

	if ($num < 160)
	{
		if 		($num == 128) { $num = 8364;	}
		elseif	($num == 129) { $num = 160; 	}
		elseif	($num == 130) { $num = 8218;	}
		elseif	($num == 131) { $num = 402; 	}
		elseif	($num == 132) { $num = 8222;	}
		elseif	($num == 133) { $num = 8230;	}
		elseif	($num == 134) { $num = 8224;	}
		elseif	($num == 135) { $num = 8225;	}
		elseif	($num == 136) { $num = 710; 	}
		elseif	($num == 137) { $num = 8240;	}
		elseif	($num == 138) { $num = 352; 	}
		elseif	($num == 139) { $num = 8249;	}
		elseif	($num == 140) { $num = 338; 	}
		elseif	($num == 141) { $num = 160; 	}
		elseif	($num == 142) { $num = 381; 	}
		elseif	($num == 143) { $num = 160; 	}
		elseif	($num == 144) { $num = 160; 	}
		elseif	($num == 145) { $num = 8216;	}
		elseif	($num == 146) { $num = 8217;	}
		elseif	($num == 147) { $num = 8220;	}
		elseif	($num == 148) { $num = 8221;	}
		elseif	($num == 149) { $num = 8226;	}
		elseif	($num == 150) { $num = 8211;	}
		elseif	($num == 151) { $num = 8212;	}
		elseif	($num == 152) { $num = 732; 	}
		elseif	($num == 153) { $num = 8482;	}
		elseif	($num == 154) { $num = 353; 	}
		elseif	($num == 155) { $num = 8250;	}
		elseif	($num == 156) { $num = 339; 	}
		elseif	($num == 157) { $num = 160; 	}
		elseif	($num == 158) { $num = 382; 	}
		elseif	($num == 159) { $num = 376; 	}
	}

	if ($num < 2048)
	{
		return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	}
	if ($num < 65536)
	{
		return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	}
	if ($num < 2097152)
	{
		return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	}
	return false;
}

/**************************************************************************
 text_for_filename()

 **************************************************************************/
function text_for_filename($text)
{
	$text = low($text);
	$text = r('/\s+/', '-', $text);
	$text = r('/[^-_a-z0-9]+/', '', $text);
	$text = r('/-+/', '-', $text);
	return 	$text;
}

/**************************************************************************
 prevent_xss()

 **************************************************************************/
function prevent_xss($value)
{
	// prevent double encoding of HTML values
	return r('#&amp;([^\s]+;)#', '&\1', h(strip_tags_sane($value)));
}

/******************************************************************************
 get_tags()

 Given an html string it will return an array of opening or self-closing tags.
 Specify specific tag(s) with a regex fragment in the optional $tags argument.
 ******************************************************************************/
function get_tags($html, $tags = '[a-z:]+')
{
	$all_tags = false;

	if (ma('#<('.$tags.')[^>]*>#iU', $html, $m))
	{
		$all_tags = $m[0];
	}
	return $all_tags;
}

/******************************************************************************
 get_attrs()

 Given an html tag string it will return an array of all boolean, single-,
 double- and un- quoted attributes. HTML5, ON YOUR RUG!

 TODO: Why does this return false and not an empty array?
 ******************************************************************************/
function get_attrs($html_tag, $case_sensitive = false)
{
	$all_attrs	= false;
	$html_attrs = r('#(^<[a-z:]+|/?>$)#i', '', $html_tag);

	if (ma('#\s+([-a-z:]+)\s*=\s*("|\')((?:\\\.|[^\\2])*)\\2#siU', $html_attrs, $n))
	{
		$all_attrs = array();
		for ($i = 0; $i < count($n[1]); $i++)
		{
			$attr	= $n[1][$i];
			$value	= $n[3][$i];
			$quote	= $n[2][$i];

			// remove quote related escaping
			$value 	= sr('\\'.$quote, $quote, $value);

			if ($case_sensitive)
			{
				$all_attrs[$attr] = $value;
			}
			else
			{
				$all_attrs[low($attr)] = $value;
			}
			$html_attrs = sr($n[0][$i], '', $html_attrs);
		}
	}
	$unquoted_attrs = preg_split('#\s+#', trim($html_attrs));

	foreach($unquoted_attrs as $unquoted_attr)
	{
		if (in($unquoted_attr, '='))
		{
			list($attr, $value) = explode('=', $unquoted_attr);

			if ($case_sensitive)
			{
				$all_attrs[$attr] = $value;
			}
			else
			{
				$all_attrs[low($attr)] = $value;
			}
		}
		else if (!empty($unquoted_attr))
		{
			if ($case_sensitive)
			{
				$all_attrs[$unquoted_attr] = $unquoted_attr;
			}
			else
			{
				$all_attrs[low($unquoted_attr)] = $unquoted_attr;
			}
		}
	}
	return $all_attrs;
}

/******************************************************************************
 strip_tags_sane()

 Strips HTML tags, manually removing <script> and <style> tags to prevent their
 content from appearing as text in the result.
 ******************************************************************************/
function strip_tags_sane($html, $allowed_html_tags = null)
{
	$html = r('#<script[^>]*>.*</script>#miUs', '', $html);
	$html = r('#<style[^>]*>.*</style>#miUs', '', $html);
	return strip_tags($html, $allowed_html_tags);
}

/**************************************************************************
 strip_html()

 Takes an HTML string and removes any tags and/or attributes not included
 in the optional CSS selector-link $allowed argument. The following

	a[href|title], i, b, strong, em, *[class]

 would allow <a>, <i>, <b>, <strong>, and <em> tags. All allowed tags would
 be allowed the class attribute and <a> would be allowed the addition href
 and title attributes.

 The optional $callback argument allows you to specify a function to
 further process the value of specific attributes. The callback function
 has the following signature:

	callback($attr, $value)

 and should return a(n optionally) modified $value.
 **************************************************************************/
function strip_html($html, $allowed = '', $callback = '')
{
	if (!empty($allowed))
	{
		$allowed_each 	= preg_split('#[,\s]+#', $allowed);
		$allowed_tags 	= array();
		$allowed_attrs 	= array(); // an array of arrays
		$global_attrs	= array();

		foreach($allowed_each as $i => $allowed_mixed)
		{
			m('#^([*a-z][a-z0-9]*)(?:\[([^\]]+))?#i', $allowed_mixed, $m);
			$tag 	= $m[1];
			$attrs 	= isset($m[2]) ? $m[2] : '';

			if ($tag != '*')
			{
				$allowed_tags[$i] 	= $tag;
				$allowed_attrs[$i] 	= empty_explode('|', $attrs);
			}
			else
			{
				$global_attrs = empty_explode('|', $attrs);
			}
		}

		if (!empty($allowed_tags))
		{
			$allowed_html = '<'.implode('><', $allowed_tags).'>';
			$html = strip_tags_sane($html, $allowed_html);
		}

		foreach($allowed_tags as $i => $tag)
		{
			if (m('#<'.$tag.'(?=[^a-z])([^>]+)>#imU', $html, $n))
			{
				$full_tag 	= $n[0];

				$allowed_tag_attrs = array_merge($allowed_attrs[$i], $global_attrs);
				$new_attrs = '';


				if (!empty($allowed_tag_attrs))
				{
					$attrs = get_attrs($full_tag);
					foreach($allowed_tag_attrs as $attr)
					{
						if (isset($attrs[$attr]))
						{
							$value = $attrs[$attr];

							if (!empty($callback) && function_exists($callback))
							{
								$value = call_user_func($callback, $attr, $value);
							}

							// escape any unescaped double-quotes
							$value = r('#(?<!\\\)"#', '\"', $value);

							$new_attrs .= ' '.$attr.'="'.$value.'"';
						}
					}
				}
				$html = sr($full_tag, "<{$tag}{$new_attrs}>", $html);
			}
		}
	}
	else
	{
		$html = strip_tags_sane($html);
	}

	return $html;
}

/**************************************************************************
 query_encode()

 Encodes just the characters used to structure a query string

 **************************************************************************/
function query_encode($url)
{
	$swap = array
	(
		'?' => '%3F',
		'&' => '%26',
		'=' => '%3D'
	);
	return sr(array_keys($swap), array_values($swap), $url);
}

/**************************************************************************
 is_https()
 **************************************************************************/
function is_https()
{
	return ((isset($_SERVER['HTTPS']) && low($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_HTTPS']) && low($_SERVER['HTTP_HTTPS']) == 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443));
}

/**************************************************************************
 resolve()

 Maps the target url onto the base url.

 Eg. Takes:

	$base 	= 'http://site.com/blog/article.php';
	$target	= '../portfolio/'

 Returns:

	http://site.com/portfolio/

 **************************************************************************/
function resolve($base, $target)
{
	if (m('#^(https?|javascript):#i', $target, $m))
	{
		return $target;
	}
	else if (m('#^//#', $target, $m))
	{
		return (is_https()?'https:':'http:').$target;
	}
	else if (m('#^/#', $target, $m))
	{
		return r('#^(https?://[^/]+).*#i', '\1', $base).$target;
	}
	else
	{
		$i = substr_count($target, '../');

		$parsed_url		= array
		(
			'scheme'	=> 'http',
			'host'		=> 'localhost',
			'path'		=> '/',
			'query'		=> '',
			'port'		=> 80
		);
		$parsed_url		= array_merge($parsed_url, parse_url($base));
		$base = $parsed_url['scheme'].'://'.$parsed_url['host'].r('#([^\./]+\.[^\./]+)$#', '', $parsed_url['path']);

		return r('#([^/]+/){'.$i.'}(\.\./){'.$i.'}#', '', $base.$target);
	}
}

/**************************************************************************
 redirect_to()

 Redirect. Observes ?errors argument
 **************************************************************************/
function redirect_to($link)
{
	header('Location:'.errors_url($link));
	exit();
}

/**************************************************************************
 debug()

 **************************************************************************/
$DEBUG_OUTPUT_BUFFER = array();
function debug($obj, $label = null)
{
	if (err())
	{
		$func = 'none';
		$file = 'fever.php';
		$html = '';
		if (function_exists('debug_backtrace'))
		{
			$backtrace = debug_backtrace(); // removed PHP5-only argument
			if (isset($backtrace[1]))
			{
				$file = $backtrace[1]['file'];
				$line = $backtrace[1]['line'];
				$func = $backtrace[1]['function'];

				if (isset($backtrace[1]['class']))
				{
					$func = $backtrace[1]['class'].$backtrace[1]['type'].$func;
				}

				$file = r('#^.*/firewall/#', '', $file);
				$html .= '<p><em>'.$func.'()</em> line <em>'.$line.'</em> in <em>'.$file.'</em></p>';
			}
		}

		if (is_string($obj))
		{
			if ((strpos($obj, '_config') || strpos($obj, '`auth`')))
			{
				if (strlen($obj) > 64)
				{
					$obj = substr($obj, 0, 64).'...';
				}
			}
			$obj = htmlentities($obj, ENT_COMPAT, 'UTF-8');
		}

		ob_start();
		p($obj, $label);
		$html .= ob_get_clean();

		// black out any passwords
		$html = r('#(password\]\s*=>\s*)([^\s]+)#', '\1********', $html);

		// human readable version for redirects
		e("<!--\r{$html}-->");

		$html = quote(rawurlencode($html));
		$html = "msg='{$html}';if(window.debug){debug(msg);}else if(parent.debug){parent.debug(msg);};";

		if ($_GET == array('errors' => null))
		{
			global $DEBUG_OUTPUT_BUFFER;
			$DEBUG_OUTPUT_BUFFER[] = $html;
		}
		else
		{
			onload($html);
		}
	}
}

/**************************************************************************
 debug_flush()

 **************************************************************************/
function debug_flush()
{
	if (err())
	{
		global $DEBUG_OUTPUT_BUFFER;
		while (count($DEBUG_OUTPUT_BUFFER))
		{
			onload(array_shift($DEBUG_OUTPUT_BUFFER));
		}
	}
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
 true_url()

 Sniffs out the actual link obfuscated by Bird Feeder, tinyurl.com and
 other redirects.
 **************************************************************************/
function true_url($link)
{
	// Bird Feeder
	if (m('#/feeder/\?(.+)#i', $link, $m))
	{
		if (isset($m[1]))
		{
			parse_str($m[1], $args);
			if (isset($args['seed']))
			{
				$link = $args['seed'];
			}
		}
	}

	// rd links (not sure what these are, feedburner post-google acquisition?)
	if (m('#/rd\?(.+)#i', $link, $m))
	{
		if (isset($m[1]))
		{
			parse_str($m[1], $args);
			if (isset($args['rd']))
			{
				$link = $args['rd'];
			}
			else if (isset($args['url']))
			{
				$link = $args['url'];
			}
		}
	}

	/** /
	// terminal discovery did not greatly impact relevance of link weight
	$redirectors = array
	(
		'feeds.feedburner.com',
		'icanhaz.com',
		'tinyurl.com'
	);
	$redirectors_str = implode('|', $redirectors);
	$redirectors_str = sr('.', '\.', $redirectors_str);

	if (m('#('.$redirectors_str.')/.*#i', $link, $m))
	{
		$link = get_redirect_terminal($link);
	}
	/**/

	return $link;
}

/******************************************************************************
 rebuild_url()

 ******************************************************************************/
function rebuild_url($link, $protocol='http')
{
	if (empty($protocol)) $protocol='http';

	if (!m('#^(https?|feed)://#', $link, $m))
	{
		$link = $protocol.'://'.$link;
	}
	return $link;
}

/**************************************************************************
 normalize_url()

 Strips off generic differentiation. May produce broken links, only use for
 comparison.

 eg. Both

	http://www.ShaunInman.com/a/Dir/index.php?arg=1
	https://shauninman.com/a/Dir/?arg=1

 become:

	shauninman.com/a/Dir?arg=1

 Known issue: `http://www.com/etc` becoms `com/etc`

 **************************************************************************/
function normalize_url($link)
{
	$link = r('#^(?:https?|feed)://(?:www\.)?([^.]+\.)#i', '\\1', $link); // removes protocol and generic www subdomain
	$link = preg_replace_callback('#(^[^/]+/)#', "callback_low", $link); // lowercases the domain name
	$link = r('#/index\.[a-z0-9]+#i', '', $link); // removes /index.php and ilk
	$link = r('#/\?#', '?', $link); // removes directory slash before query
	// $link = r('/#,*$/', '', $link); // removes trailing anchor, which /index.php replacement was mistakenly doing
	$link = r('#/$#', '', $link); // removes final directory slash
	return $link;
}
function callback_low($m) {
	return low($m[1]);
}

/**************************************************************************
 push()

 Pushes any available output of the script to the browser.
 **************************************************************************/
function push()
{
	echo str_pad('', 1024);
	echo '<!-- -->';
	@ob_flush();
	flush();
}

/******************************************************************************
 ago()

 ******************************************************************************/
function ago($time)
{
	if ($time == 0)
	{
		return 'Never';
	}

	$diff = time() - $time;
	if ($diff < 60)
	{
		return 'Just now';
	}

	$diff = round($diff / 60);
	if ($diff < 60)
	{
		$min = 'minute'.(($diff > 1) ? 's' : '');
		return "{$diff} {$min} ago";
	}

	$diff = round($diff / 60);
	if ($diff < 24)
	{
		$hr = 'hour'.(($diff > 1) ? 's' : '');
		return "{$diff} {$hr} ago";
	}

	$diff = round($diff / 24);
	if ($diff < 7)
	{
		return ($diff > 1) ? "{$diff} days ago" : 'Yesterday';
	}

	$diff = round($diff / 7);
	if ($diff < 12)
	{
		return ($diff > 1) ? "{$diff} weeks ago" : 'Last week';
	}
	else if ($diff < 52)
	{
		$mo = floor($diff / (30 / 7));
		return "Around {$mo} months ago";
	}

	$diff = round($diff / 52);
	return ($diff > 1) ? "Around {$diff} years ago" : 'Last year';
}

/**************************************************************************
 widont()

 Inserts a non-breaking space between the last two words of $str to prevent
 unwanted typographic widows.
 **************************************************************************/
function widont($str)
{
	return r('|([^\s])\s+([^\s]+)\s*$|', '$1&nbsp;$2', $str);
}

/**************************************************************************
 quote()

 Encodes/escapes quotes for inclusion in double or single quote strings.
 **************************************************************************/
function quote($str)
{
	$quotes = array('"' => '&quot;', "'" => "\'");
	return sr(array_keys($quotes), array_values($quotes), $str);
}

/**************************************************************************
 memory_event()

 Used to track memory consumption tied to labelled events.
 **************************************************************************/
function memory_event($event_name)
{
	if (!err()) return;

	global $__MEMORY_EVENTS;
	if (!isset($__MEMORY_EVENTS))
	{
		$__MEMORY_EVENTS = array();
	}

	$__MEMORY_EVENTS[] = array(substr($event_name, 0, 16), function_exists('memory_get_usage') ? memory_get_usage() : 0, ms());
}

/**************************************************************************
 memory_report()

 Used to track memory consumption tied to labelled events.
 **************************************************************************/
function memory_report()
{
	if (!err()) return;

	global $__MEMORY_EVENTS;
	if (!isset($__MEMORY_EVENTS))
	{
		$__MEMORY_EVENTS = array();
	}

	$limit		= trim(ini_get('memory_limit'));
	$available	= (int) preg_replace('/\D/', '', $limit);
	$unit		= strtolower(preg_replace('/\d/', '', $limit));
	switch($unit)
	{
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$available *= 1024;
		case 'm':
			$available *= 1024;
		case 'k':
			$available *= 1024;
	}

	if ($available == 0)
	{
		$available++;
	}

	$when = gmdate('Y-m-d h:i:s');
	$available_formatted = number_format($available);
	$tma = str_pad("Total memory available:".r('# +#', ' ', " {$limit} ")."({$available_formatted}B)", 61, ' ', STR_PAD_LEFT);
	$text = <<<TEXT
	--------------------------------------------------------------------------------
	{$when}{$tma}
	--------------------------------------------------------------------------------
	                                        |           SINCE LAST EVENT
	                    Total               ----------------------------------------
	                    Memory      % of    |   Memory      % of                Time
	Event               Consumed    Total   |   Consumed    Total            Elapsed
	--------------------------------------------------------------------------------

	TEXT;

	$last_used		= 0;
	$last_microtime	= EXECUTION_START;
	foreach($__MEMORY_EVENTS as $i => $event)
	{
		$name 	= str_pad(!empty($event[0]) ? $event[0] : 'tick '.$i, 20);
		$mc		= str_pad(number_format(round($event[1]/1024)).'kB', 12);
		$pt		= str_pad(round($event[1]/$available * 100, 2).'%', 8);

		$diff 	= $event[1] - $last_used;
		$emc	= str_pad(number_format(round($diff/1024)).'kB', 12);
		$ept	= str_pad(round($diff/$available * 100, 2).'%', 8);
		$ete	= str_pad(number_format($event[2] - $last_microtime, 6).'s', 16, ' ', STR_PAD_LEFT);

		$text  .= $name.$mc.$pt.'|   '.$emc.$ept.$ete."\n";

		$last_used 		= $event[1];
		$last_microtime	= $event[2];
	}

	$nm	 = function_exists('memory_get_usage') ? memory_get_usage() : 0;
	$tmc = str_pad(number_format(round(($nm - $__MEMORY_EVENTS[0][1])/1024)).'kB', 12);
	$tpt = str_pad(round(($nm - $__MEMORY_EVENTS[0][1])/$available * 100, 2).'%', 8);
	$tet = str_pad(number_format($last_microtime - EXECUTION_START, 6).'s', 40, ' ', STR_PAD_LEFT);
	$text .= <<<TEXT
--------------------------------------------------------------------------------
Since first event   {$tmc}{$tpt}{$tet}


TEXT;

	// empty our events
	$__MEMORY_EVENTS = array();

	return $text;
}

/**************************************************************************
 memory_report_to_file()

 Saves the memmory report to file.
 **************************************************************************/
function memory_report_to_file()
{
	if (!err()) return;

	if ($file = fopen('memory_report.txt', 'a+'))
	{
		fwrite($file, memory_report());
		fclose($file);
	}
}

/**************************************************************************
 tmp_log_to_file()
 **************************************************************************/
function tmp_log_to_file($obj, $title = '')
{
	if ($file = fopen('log.txt', 'a+'))
	{
		$data = tmp_log($obj, $title);
		fwrite($file, $data);
		fclose($file);
	}
}

/**************************************************************************
 tmp_log()
 **************************************************************************/
function tmp_log($obj, $title = '')
{
	$debug = debug_backtrace(false);
	ob_start();
	p($obj, $title);
 	p($debug[2], 'debug');
	return ob_get_clean();
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

/**************************************************************************
 serialize_safe()

 Returns a serialized, base64-encoded string from a PHP object.
 **************************************************************************/
function serialize_safe($data)
{
	return base64_encode(serialize($data));
}

/**************************************************************************
 unserialize_safe()

 Returns a PHP object from a base64-encoded, serialized string.
 **************************************************************************/
function unserialize_safe($base64_data)
{
	return unserialize(base64_decode($base64_data));
}

/**************************************************************************
 version_clean()

 Strips cruft from the end of version numbers
 **************************************************************************/
function version_clean($version)
{
	return r('/[-a-z].+$/i', '', $version);
}

/**************************************************************************
 has_gd_png()

 **************************************************************************/
function has_gd_png()
{
	return (function_exists('imagetypes') && imagetypes() & IMG_PNG);
}

/**************************************************************************
 ico_to_png()

 Convert a favicon datastream into a png datastream. Never. Again.
 **************************************************************************/
function ico_to_png($ico_data)
{
	$png_data = '';

	if (!has_gd_png())
	{
		debug('PHP was not compiled with GD or lacks PNG support.');
		return $png_data;
	}

	// my first foray into unpack() and MS file format docs: http://msdn2.microsoft.com/en-us/library/ms997538.aspx
	$dir	= substr($ico_data, 0, 6);
	$dir 	= unpack('sidReserved/sidType/sidCount', $dir);
	if
	(
		$dir['idReserved'] 	!= 0 ||	// must be 0
		$dir['idType'] 		!= 1 || // must be 1
		$dir['idCount'] 	< 1) 	// number of embedded icons
	{
		return $png_data;
	}


	$dir['idEntries'] = array();
	for ($i = 0; $i < $dir['idCount']; $i++)
	{
		$entry_data = substr($ico_data, 6 + ($i * 16), 16); // 16 bytes total, offset by the length of dir and previous entries
		$unpacked 	= unpack('CbWidth/CbHeight/CbColorCount/CbReserved/swPlanes/swBitCount/LdwBytesInRes/LdwImageOffset', $entry_data);
		array_push($dir['idEntries'], $unpacked);
	}

	// select the optimum icon
	$bit_count	= 1; // bits per pixel
	$icon		= 0; // we want the 16 x 16 icon
	foreach($dir['idEntries'] as $i => $entry)
	{
		// p($entry);
		if
		(
			$entry['bWidth'] == 16 && 				// prefer 16 x 16 icons
			$entry['wBitCount'] > $bit_count && 	// ensure we get the highest color depth
			$entry['wBitCount'] < 33				// can't parse higher than 32-bit
		)
		{
			$icon		= $i;
			$bit_count	= $entry['wBitCount'];
		}
	}
	$icon_starts 	= $dir['idEntries'][$icon]['dwImageOffset'];
	$icon_length	= $dir['idEntries'][$icon]['dwBytesInRes'];
	$icon_width		= $dir['idEntries'][$icon]['bWidth']; // but don't count on a 16 x 16 icon
	$raw_data 		= substr($ico_data, $icon_starts, $icon_length);

	// http://msdn2.microsoft.com/en-us/library/cc215276.aspx
	$bitmap_info = array();
	$bitmap_info['header'] = unpack('LbiSize/LbiWidth/LbiHeight/SbiPlanes/SbiBitCount/LbiCompression/LbiSizeImage/LbiXPelsPerMeter/LbiYPelsPerMeter/LbiClrUsed/LbiClrImportant', substr($raw_data, 0, 40));

	$bitmap_info['header']['biHeight'] /= 2; // combined height of XOR & AND masks, halve
	$color_count	= 0;
	$color_length	= $bitmap_info['header']['biWidth'] * $bitmap_info['header']['biHeight'] * $bitmap_info['header']['biBitCount'] / 8;

	// http://msdn2.microsoft.com/en-us/library/cc215249.aspx
	if ($bitmap_info['header']['biBitCount'] > 16)
	{
		// maximum of 2^24 colors
		$color_count = 0;
	}
	else if ($bitmap_info['header']['biBitCount'] < 16)
	{
		// maximum of 2^16 colors
		$color_count   = (int) pow(2, $bitmap_info['header']['biBitCount']);
		$color_length *= ($bitmap_info['header']['biBitCount'] == '1') ? 2 : 1;
	}
	else
	{
		// no idea what's going on, exit
		debug('Could not determine color depth in ico_to_png.');
		return $png_data;
	}

	// build our palette
	for ($i = 0; $i < $color_length; $i++)
	{
		$color_data = substr($raw_data, 40 + ($i * 4), 4);
		if (!empty($color_data))
		{
			$bitmap_info['colors'][] = unpack('CrgbBlue/CrgbGreen/CrgbRed/CrgbReserved', $color_data);
		}
	}
	$raw_offset = 40 + ($color_count * 4);

	$colors = array();
	for ($i = 0; $i < $color_length; $i++)
	{
		$color = unpack('Cvalue', substr($raw_data, $raw_offset + $i, 1));
		array_push($colors, $color['value']);
	}
	$raw_offset += $color_length;

	// something isn't right here, not sure what the issue is though
	$alphas = '';
	for ($i = 0; $i < 16; $i++)
	{
		$xy 	 = unpack('Cx/Cy', substr($raw_data, $raw_offset, 2));
		$alphas .= str_pad(decbin($xy['x']), 8, '0', STR_PAD_LEFT).str_pad(decbin($xy['y']), 8, '0', STR_PAD_LEFT);
		$raw_offset += 4;
	}

	// draw the png with transparency
	$img = imagecreatetruecolor($icon_width, $icon_width);
	imagealphablending($img, false);
	$none = imagecolorallocatealpha($img, 0, 0, 0, 127);
	imagefill($img, 0, 0, $none);
	imagesavealpha($img, true);

	$png_offset	= 0;
	for($y = 0; $y < $icon_width; $y++)
	{
		for($x = 0; $x < $icon_width; $x++)
		{
			$r = 0;
			$g = 0;
			$b = 0;
			$a = 0;

			// accomodate various color depths
			switch($bitmap_info['header']['biBitCount'])
			{
				case 32:
					$r = $colors[($png_offset * 4) + 2];
					$g = $colors[($png_offset * 4) + 1];
					$b = $colors[($png_offset * 4) + 0];

					$a = round((255 - $colors[($png_offset * 4) + 3]) / 2); // 0-127
					// safety
					$a = ($a < 0) 	?   0 : $a;
					$a = ($a > 127) ? 127 : $a;
				break;

				case 24:
					$r = $colors[($png_offset * 3) + 2];
					$g = $colors[($png_offset * 3) + 1];
					$b = $colors[($png_offset * 3) + 0];
					$a = (substr($alphas, $png_offset, 1) == '1') ? 127 : 0;
				break;

				case 8:
					$c = $bitmap_info['colors'][$colors[$png_offset]];

					$r = $c['rgbRed'];
					$g = $c['rgbGreen'];
					$b = $c['rgbBlue'];
					$a = (substr($alphas, $png_offset, 1) == '1') ? 127 : 0;
				break;

				case 4:
					$c = ($colors[floor($png_offset / 2)]);
					$c = str_pad(decbin($c), 8, '0', STR_PAD_LEFT);
					$m = (fmod($png_offset + 1, 2) == 0) ? 1 : 0;
					$c = bindec(substr($c, ($m * 4), 4));
					$c = $bitmap_info['colors'][$c];

					$r = $c['rgbRed'];
					$g = $c['rgbGreen'];
					$b = $c['rgbBlue'];
					$a = (substr($alphas, $png_offset, 1) == '1') ? 127 : 0;
				break;

				case 1:
					$c = ($colors[floor($png_offset / 8)]);
					$c = str_pad(decbin($c), 8, '0', STR_PAD_LEFT);
					$m = fmod($png_offset + 8, 8) + 1;
					$c = (int) substr($c, $m - 1, 1);
					$c = $bitmap_info['colors'][$c];

					$r = $c['rgbRed'];
					$g = $c['rgbGreen'];
					$b = $c['rgbBlue'];
					$a = (substr($alphas, $png_offset, 1) == '1') ? 127 : 0;
				break;
			} // switch

			$color = imagecolorallocatealpha($img, $r, $g, $b, $a);
			imagesetpixel($img, $x, $icon_width - 1 - $y, $color);
			$png_offset++;

		} // x

		$png_offset += ($bitmap_info['header']['biBitCount'] == 1) ? 16 : 0;

	} // y

	// capture the image by dumping to the output buffer
	if (!ob_start())
	{
		debug('Could not start output buffering, cannot save complete ico_to_png conversion.');
		return $png_data;
	}
	imagepng($img);
	imagedestroy($img);
	$png_data = ob_get_clean();

	return $png_data;
}