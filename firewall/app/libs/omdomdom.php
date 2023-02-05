<?php
// add ignore whitespace flag
// case-insensitive attributes
// - low memory foot print (due to intentional abuse of global)

class OMDOMDOM
{
	public static function parse($xml)
	{
		global $__OMDOMDOM_nodes;
		$__OMDOMDOM_nodes = array();

		$encoding = 'UTF-8';
		// Newsfire is UTF-16 despite encoding claiming to be ISO-8859-1
		if (bin2hex(substr($xml, 0, 2)) == 'fffe')
		{
			$encoding = 'UTF-16';
		}
		// detect encoding, default to UTF-8
		$encoding = preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $xml, $m) ? strtoupper($m[1]) : $encoding;

		// convert decoding where necessary
		if ($encoding != 'UTF-8')
		{
			$do_fallback = true;
			if (function_exists('iconv'))
			{
				$converted_xml = iconv($encoding, 'UTF-8', $xml);
				if ($converted_xml !== false)
				{
					$xml = $converted_xml;
					$do_fallback = false;
				}
			}

			if ($do_fallback && function_exists('mb_convert_encoding'))
			{
				$xml = mb_convert_encoding($xml, 'UTF-8', $encoding);
			}
			else
			{
				debug('Unable to convert encoding. PHP is not compiled with mbstring.');
			}
		}

		// hash out non-display non-elements
		$hashes			= array();
		$replacements 	= array
		(
			'#comment'					=> '#<!--.*-->#Ums',
			'#cdata'					=> '#<!\[CDATA\[.*]]>#Ums',
			'#processing_instruction'	=> '#<(?:\?|!).*\??>#Ums' // must come after comments and CDATA because of <!DOCTYPE
		);
		foreach($replacements as $key => $regex)
		{
			if (preg_match_all($regex, $xml, $m))
			{
				foreach ($m[0] as $i => $find)
				{
					$hash					= md5($find);
					$hashes[$key][$hash]	= $find;
					$xml					= str_replace($m[0][$i], "<{$key} hash=\"{$hash}\" />", $xml);
				}
			}
		}
		$xml 	= trim($xml);

		debug($xml);

		$nodes 	= array // just node arrays by node id
		(
			0 => array
			(
				'_node_id'				=> 0,
				'_node_name'			=> '#document',
				'_node_ancestry'		=> '/'
			)
		);
		$ancestors = array // flat associative array, of tagnames indexed by node id
		(
			0 => '#document'
		);

		$tag_fragments = explode('<', $xml);
		foreach($tag_fragments as $tag_fragment)
		{
			if (empty($tag_fragment))
			{
				continue;
			}

			// split `tag attr="value">inner content `
			// into `tag attr="value"` and `inner content ` or
			// `/tagname>` into `/tagname` and ``;
			// the more sensible '#([^>]+)>(.*)#ms' doesn't seem to work (breaks datetime detection somehow?)
			// making the following $tag_fix necessary
			preg_match('#^(.+)>([^>]*)$#ms', $tag_fragment, $n);
			list($tag, $content) = array_pad(array_slice($n, 1, 2), 2, '');

			// eg. http://www.nzbsrus.com/rssfeed.php?cat=90
			if (strpos($tag, '>') !== false)
			{
				$tag_fix = filled_explode('>', $tag, 2);
				$tag = $tag_fix[0];
				$content = $tag_fix[1].'>'.$content;
			}

			// handle closing tags first
			if (preg_match('#^\s*/(.+)#', $tag, $m)) // prevent `< /tagname>` from throwing us off
			{
				$closing_tag_name = $m[1];

				// find the node id of the last occurrence of this tag name
				$reverse_ancestors = array_reverse($ancestors, true);
				if ($node_id = array_search($closing_tag_name, $reverse_ancestors))
				{
					if ($new_length = array_search($node_id, array_keys($ancestors)))
					{
						// p('close '.$closing_tag_name.' ('.$node_id.')');
						$updated_ancestors = array();
						$i = 0;
						foreach ($ancestors as $id => $tag)
						{
							if (count($updated_ancestors) == $new_length)
							{
								break;
							}
							else
							{
								$updated_ancestors[$id] = $tag;
							}
						}
						$ancestors = $updated_ancestors;
					}
				}

				if (!empty($content))
				{
					$node_id 	= count($nodes);
					$node 		= array
					(
						'_node_id'			=> $node_id,
						'_node_name'		=> '#text',
						'_node_ancestry'	=> '//'.implode('/', array_keys($ancestors)),
						'_node_content'		=> $content
					);
					$nodes[$node_id] = $node;
				}
				continue;
			}
			// done with closing tags

			// prevent `tagname / ` from throwing us off
			$tag = trim($tag);

			// handle opening tags
			list($tag_name, $attrs_str) = array_pad(preg_split('/\s+/', $tag, 2), 2, '');

			$node_id 		= count($nodes);
			$self_closing 	= false;

			if (preg_match('#/$#', $tag))
			{
				$self_closing = true;
				$attrs_str = preg_replace('#\s+/$#', '', $attrs_str);
			}

			// get attributes regardless of quote style or lack of value
			$attrs = array();
			// determine attr name, quote style and value
			if (preg_match_all('#([-a-z:]+)\s*=\s*("|\')((?:\\\.|[^\\2])*)\\2#siU', $attrs_str, $m))
			{
				for ($i = 0; $i < count($m[1]); $i++)
				{
					$attr	= $m[1][$i];
					$value	= $m[3][$i];
					$quote	= $m[2][$i];

					// remove quote related escaping
					$value 	= str_replace('\\'.$quote, $quote, $value);

					$attrs[$attr]	= $value;
					$attrs_str 		= str_replace($m[0][$i], '', $attrs_str);
				}
			}

			if (!empty($tag_name) && $tag_name[0] == '#' && isset($hashes[$tag_name]) && isset($attrs['hash']))
			{
				$cdata = $hashes[$tag_name][$attrs['hash']];
				$attrs = array('_node_content' => $cdata);
			}

			// what remains is unquoted, split on whitespace
			$unquoted_attrs = preg_split('#\s+#', trim($attrs_str));
			foreach($unquoted_attrs as $unquoted_attr)
			{
				// if there's an equal sign, get the value
				if (strpos($unquoted_attr, '=') !== false)
				{
					list($attr, $value) = explode('=', $unquoted_attr);
					$attrs[$attr] = $value;
				}
				// or assign the name as the value
				else if (!empty($unquoted_attr))
				{
					$attrs[$unquoted_attr] = $unquoted_attr;
				}
			}

			// update properties and add to nodes
			$node 		= array
			(
				'_node_id'			=> $node_id,
				'_node_name'		=> strtolower($tag_name),
				'_node_ancestry'	=> '//'.implode('/', array_keys($ancestors))
			);
			if ($self_closing)
			{
				$node['_node_self_closing'] = true;
			}
			$node = array_merge($attrs, $node);
			$nodes[$node_id] = $node;

			// add to ancestors *if not self-closing*
			if (!$self_closing)
			{
				$ancestors[$node_id] = $tag_name;
			}

			// add following content
			if ($content != '')
			{

				$node_id 	= count($nodes);
				$node 		= array
				(
					'_node_id'			=> $node_id,
					'_node_name'		=> '#text',
					'_node_ancestry'	=> '//'.implode('/', array_keys($ancestors)),
					'_node_content'		=> $content
				);
				$nodes[$node_id] = $node;
			}
		}

		// debug($hashes, 	'hashes');
		// debug($nodes, 	'nodes');

		$__OMDOMDOM_nodes = $nodes;
		return new OMDOMDOMNode(0);
	}
}

class OMDOMDOMNode
{
	protected $__node_id;

	public function __construct($node_id)
	{
		$this->__node_id = $node_id;
	}

	public function __node()
	{
		global $__OMDOMDOM_nodes;
		return $__OMDOMDOM_nodes[$this->__node_id];
	}

	public function has_attr($attr)
	{
		global $__OMDOMDOM_nodes;
		return isset($__OMDOMDOM_nodes[$this->__node_id][$attr]);
	}

	public function get_node_name()
	{
		return $this->get_attr('_node_name');
	}

	public function get_attr($attr)
	{
		global $__OMDOMDOM_nodes;
		$node = $__OMDOMDOM_nodes[$this->__node_id];
		$value = null;
		if (isset($node[$attr]))
		{
			$value = $node[$attr];
		}
		return $value;
	}

	public function set_attr($attr, $value)
	{
		global $__OMDOMDOM_nodes;
		$__OMDOMDOM_nodes[$this->__node_id][$attr] = $value;
	}

	public function get_child_nodes($__node_id_only = false)
	{
		return $this->get_descendant_nodes(true, $__node_id_only);
	}

	public function children($__node_id_only = false)
	{
		return $this->get_child_nodes($__node_id_only);
	}

	public function get_parent_node()
	{
		global $__OMDOMDOM_nodes;
		$node = $__OMDOMDOM_nodes[$this->__node_id];
		$parent = null;
		if (preg_match('#([0-9]+)$#', $node['_node_ancestry'], $m))
		{
			$parent = new OMDOMDOMNode($m[0]);
		}
		return $parent;
	}

	public function parent()
	{
		return $this->get_parent_node();
	}

	public function get_descendant_nodes($__children_only = false, $__node_id_only = false)
	{
		global $__OMDOMDOM_nodes;

		$ancestry 	= $__OMDOMDOM_nodes[$this->__node_id]['_node_ancestry'];
		$parent		= $ancestry.'/'.$this->__node_id;

		$nodes 		= array();
		foreach($__OMDOMDOM_nodes as $node_id => $COPY_node)
		{
			if
			(
				strpos($COPY_node['_node_ancestry'], $ancestry) === 0 &&
				(!$__children_only || $COPY_node['_node_ancestry'] == $parent)
			)
			{
				$nodes[] = $__node_id_only ? $node_id : new OMDOMDOMNode($node_id);
			}
		}
		return $nodes;
	}

	public function get_nodes_by($attr, $value = '', $__children_only = false)
	{
		global $__OMDOMDOM_nodes;

		if ($attr == '_node_name')
		{
			$value = strtolower($value );
		}

		// TODO: allow for an array of matching attr/value pairs
		$nodes = $this->get_descendant_nodes($__children_only, true);
		$matching_nodes = array();
		foreach($nodes as $node_id)
		{
			$node = $__OMDOMDOM_nodes[$node_id];
			if (isset($node[$attr]) && $node[$attr] == $value)
			{
				$matching_nodes[] = new OMDOMDOMNode($node_id);
			}
		}
		return $matching_nodes;
	}

	public function get_nodes_by_name($node_name)
	{
		return $this->get_nodes_by('_node_name', $node_name);
	}

	public function get_child_nodes_by($attr, $value = '')
	{
		return $this->get_nodes_by($attr, $value, true);
	}

	public function get_child_nodes_by_name($node_name)
	{
		return $this->get_child_nodes_by('_node_name', $node_name);
	}

	public function inner_content()
	{
		global $__OMDOMDOM_nodes;
		$node = $__OMDOMDOM_nodes[$this->__node_id];

		$inner_content = '';
		if ($node['_node_name'][0] == '#' && isset($node['_node_content']))
		{
			// handle unencoded CDATA in a content:encoded element
			if ($node['_node_name'] == '#cdata')
			{
				$cdata	= r('#<!\[CDATA\[(.*?)]]>#smu', "$1", $node['_node_content']);
				$parent = $this->parent();
				if ($parent->get_node_name() == 'content:encoded')
				{
					$cdata = htmlentities($cdata, ENT_COMPAT, 'UTF-8');
				}
				$inner_content .= $cdata;
			}
			else
			{
				$inner_content .= $node['_node_content'];
			}
		}
		else
		{
			$child_nodes = $this->children();
			foreach($child_nodes as $child_node)
			{
				$inner_content .= $child_node->outer_content();
			}
		}
		return $inner_content;
	}

	public function outer_content()
	{
		global $__OMDOMDOM_nodes;
		$node = $__OMDOMDOM_nodes[$this->__node_id];
		$self_closing = isset($node['_node_self_closing']);
		$has_tags = ($node['_node_name'][0] != '#');

		$outer_content = '';
		if ($has_tags)
		{
			$outer_content .= '<'.$node['_node_name'];

			foreach($node as $attr => $value)
			{
				if (strpos($attr, '_node_') === 0)
				{
					continue;
				}
				$outer_content .= ' '.$attr.'="'.$value.'"';
			}
			if ($self_closing)
			{
				$outer_content .= ' />';
			}
			else
			{
				$outer_content .= '>';
			}
		}

		$outer_content .= $this->inner_content();

		if (!$self_closing && $has_tags)
		{
			$outer_content .= '</'.$node['_node_name'].'>';
		}

		return $outer_content;
	}

	public function inner_text()
	{
		return trim(preg_replace('#\s+#', ' ', strip_tags_sane($this->inner_content())));
	}
}