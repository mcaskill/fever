<?php
// this path is soon-to-be deprecated

// no url provided
if (!isset($_GET['url']) || empty($_GET['url']))
{
	redirect_to('./');
}

$url 				= rebuild_url($_GET['url'], $_GET['protocol']);
$title 				= 'this page';
$error				= '';
$feeds_by_title 	= array();
$menus_js 			= array();
$subscribed_count	= 0;
$i					= 0;
$total_feeds		= 0;

$feeds_by_checksum	= key_remap('url_checksum', $this->feeds);

// new feedlet
if (isset($_GET['feeds']) && !empty($_GET['feeds']))
{
	if (isset($_GET['title']) && !empty($_GET['title']))
	{
		$title = $_GET['title'];
	}

	$sloppy_flag = false;

	foreach($_GET['feeds'] as $embedded_feed)
	{

		if ($embedded_feed['type'] == 'a')
		{
			$sloppy_flag = true;
		}

		$href = rebuild_url($embedded_feed['href'],$embedded_feed['protocol']);
		$checksum = checksum(normalize_url($href));
		$embedded_feed['is_subscribed']	= isset($feeds_by_checksum[$checksum]);
		$embedded_feed['title'] = trim(strip_tags_sane($embedded_feed['title']));
		if (empty($embedded_feed['title']))
		{
			$embedded_feed['title'] = 'Feed';
		}

		if ($embedded_feed['is_subscribed'])
		{
			$subscribed_count++;
		}

		// pick the first of duplicate content feeds
		if (m('#\s*\(?((?:rss|atom)(?:\s*[.0-9]*)?(?:\s*feeds?)?)\)?#i', $embedded_feed['title'], $m))
		{
			$feed_title = trim(sr($m[0], '', $embedded_feed['title']));
			$feed_title = (empty($feed_title)) ? 'Feed' : $feed_title;

			if (!isset($feeds_by_title[$feed_title]))
			{
				$feeds_by_title[$feed_title] = array
				(
					'default'	=> 0,
					'feeds' 	=> array()
				);
			}
			array_push($feeds_by_title[$feed_title]['feeds'], $embedded_feed);

			if ($embedded_feed['is_subscribed'])
			{
				$feeds_by_title[$feed_title]['default'] = count($feeds_by_title[$feed_title]['feeds']) - 1;
			}
		}
		else
		{
			$feeds_by_title[$embedded_feed['title']] = array
			(
				'default'	=> 0,
				'feeds' 	=> array($embedded_feed)
			);
		}
	}

	$total_feeds = count($feeds_by_title);

	if ($sloppy_flag)
	{
		$error = 'Feeds displayed in brown are guesses based on link url or link text and may not be an actual feed. Hover over a feed title to see its url.';
	}
}
// old feedlet
else
{
	$embedded_feeds 	= array();
	$request 			= get($url);
	$html 				= $request['body'];

	// auth required
	switch($request['headers']['response_code'])
	{
		case 401:
			$error .= ' Fever is not authorized to access the requested page.';
		break;
		case 404:
			$error .= ' The requested page was not found.';
		break;
	}

	// no response
	if (empty($html))
	{
		if (!empty($request['error']['msg']))
		{
			$error .= ' '.ucfirst($request['error']['msg']).' using '.$request['error']['type'].'.';
		}
		else
		{
			$error .= ' The requested page was empty!';
		}
	}

	// accomodate CHOCKLOCK
	$html = preg_replace_callback('/(<LINK |(HREF|REL|TYPE|TITLE)=)/', 'callback_low', $html);

	if ($links = get_tags($html, 'link'))
	{
		foreach($links as $link_html)
		{
			if ($link = get_attrs($link_html))
			{
				if (isset($link['href'], $link['rel'], $link['type']) && m('#.*alternate.*#i', $link['rel'], $alt) && m('#^application/(rss|atom)\+xml$#i', $link['type'], $format))
				{
					$title 		= (isset($link['title'])) ? $link['title'] : $format[1];
					$href		= resolve($url, $link['href']);
					$checksum	= checksum(normalize_url($href));
					$feed	= array
					(
						'title' 		=> $title,
						'href'			=> $href,
						'type'			=> 'link',
						'is_subscribed'	=> isset($feeds_by_checksum[$checksum])
					);

					if ($feed['is_subscribed'])
					{
						$subscribed_count++;
					}

					array_push($embedded_feeds, $feed);
				}
			}
		}

		foreach($embedded_feeds as $embedded_feed)
		{
			// pick the first of duplicate content feeds
			if (m('#\s*\(?((?:rss|atom)(?:\s*[.0-9]*)?(?:\s*feed)?)\)?#i', $embedded_feed['title'], $m))
			{
				$title = trim(sr($m[0], '', $embedded_feed['title']));
				$title = (empty($title)) ? 'Feed' : $title;

				if (!isset($feeds_by_title[$title]))
				{
					$feeds_by_title[$title] = array
					(
						'default'	=> 0,
						'feeds' 	=> array()
					);
				}
				array_push($feeds_by_title[$title]['feeds'], $embedded_feed);

				if ($embedded_feed['is_subscribed'])
				{
					$feeds_by_title[$title]['default'] = count($feeds_by_title[$title]['feeds']) - 1;
				}
			}
			else
			{
				$feeds_by_title[$embedded_feed['title']] = array
				(
					'default'	=> 0,
					'feeds' 	=> array($embedded_feed)
				);
			}
		}

		$total_feeds = count($feeds_by_title);

		$title = normalize_url($url);
		if (m('#<title[^>]*>([^<]+)</title>#i', $html, $m))
		{
			$title = html_entity_decode_utf8($m[1]);
		}

		// no feeds
		if (!$total_feeds)
		{
			$error .= ' No feeds found.';

			// url (or redirect url) suggests password protected-content
			if (m('#(login|account|user|admin)#', $url.$request['headers']['request_url'], $m))
			{
				$error .= ' Login may be required to access the requested page.';
			}
		}
	}
}

// prepare groups select menu
$groups_html = '';
foreach($this->groups as $group)
{
	// ignore the All supergroup
	if ($group['id'] == 0) { continue; }

	$groups_html .= '<option value="'.$group['id'].'">'.h($group['title']).'</option>';
}
?>
<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="add-feeds" />
	<input type="hidden" name="url" value="<?php e(prevent_xss($url));?>" />

	<h1>Feedlet</h1>
	<h2>Subscribe to <?php e(($total_feeds == 1) ? 'a feed' : 'feeds')?> on <?php e(prevent_xss(lt($title))); ?></h2>
<?php if(!empty($error)):?>
	<p><?php e($error); ?></p>
<?php endif;?>
	<table>
<?php foreach($feeds_by_title as $title => $feed_meta):?>
<?php
$default	= $feed_meta['default'];
$feed 		= $feed_meta['feeds'][$default];

// no dash to play nice with JavaScript
$menu_name		= $i;
$menu_options	= array();
$is_guess		= false;
foreach($feed_meta['feeds'] as $j => $alt_feed)
{
	if ($alt_feed['type']=='a')
	{
		$is_guess = true;
	}
	$display_title  = trim(sr($title, '', $alt_feed['title']));
	$display_title  = r('#[()]#', '', $display_title);
	$href = rebuild_url($alt_feed['href'],$alt_feed['protocol']);
	if (empty($display_title))
	{
		$display_title = normalize_url($href);
	}
	else
	{
		$display_title .= ' ('.normalize_url($href).')';
	}
	$menu_options[] = "{value:'{$href}',text:'{$display_title}'}";
}
array_push($menus_js, $menu_name.':['.implode(',', $menu_options).']');
?>
		<tr>
<?php if (!$feed['is_subscribed']):?>
			<td class="proto">
					<label<?php echo($is_guess ? ' class="guess"' : '') ?>>
					<span class="i"><input class="select-all" type="checkbox" id="feed-<?php e($i); ?>" name="feeds[<?php e($i); ?>][url]" value="<?php e($href); ?>"<?php e($total_feeds == 1 ? ' checked="checked"' : '');?> /></span>
					Add <strong title="<?php e($href);?>"><?php e($title);?></strong>
				</label>
			</td>
			<td>
				<span class="btn text" onclick="Fever.Feedlet.options(<?php e($i); ?>, '<?php e(quote($title));?>');">Options<i></i></span>
				<div id="feed-options-<?php e($i); ?>" class="feed-options"></div>
			</td>
<?php else:?>
			<td colspan="2">
				<span class="i"><input type="checkbox" checked="checked" disabled="disabled" /></span>
				Already subscribed to <strong title="<?php e($href);?>"><?php e($title); ?></strong>
			</td>
<?php endif;?>
		</tr>
<?php $i++; ?>
<?php endforeach; ?>
		<tr>
			<td colspan="2" class="btn-row">
<?php if ($i > 1):?>
				<span onclick="toggleAll(this);" id="select-all" class="btn text">Select All<i></i></span>
<?php endif;?>
				<a href="<?php e(prevent_xss($url));?>" class="btn text">Cancel<i></i></a>
<?php if ($total_feeds && $subscribed_count != $total_feeds):?>
				<button><span class="btn text default">Subscribe<i></i></span></button>
<?php endif;?>
			</td>
		</tr>
	</table>
</form>

<div id="feed-options-template">
	<h1></h1>

	<ul class="tabs">
		<li class="active"><a href="#feed_tab-feed">Feed</a></li>
		<li><a href="#feed_tab-display">Display</a></li>
		<li><a href="#feed_tab-authentication">Authentication <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-auth');">?</span></a></li>
	</ul>

	<div id="feed_tab-feed" class="tab active">
		<h2>Feed</h2>
		<fieldset>
			<table class="top">
				<tr>
					<th class="proto">Feed title <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-title');">?</span></th>
					<td class="proto"><span class="w"><input type="text" name="feed[title]" value="" /></span></td>
				</tr>
				<tr>
					<th>Images</th>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="feed[prevents_hotlinking]" value="1" /></span> site prevents hotlinking
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'hotlinking');">?</span>
						</label>
					</td>
				</tr>
				<tr>
					<th>Add to <span style="white-space:nowrap;">group(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></span></th>
					<td>
						<span class="w"><select onchange="one('#feed_is_spark').checked=false;" name="feed[group_ids][]" multiple="multiple"><?php e($groups_html); ?></select></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="proto">
						<input type="hidden" id="feed_is_spark_default" name="feed[is_spark]" value="0" />
						<label>
							<span class="i"><input type="checkbox" id="feed_is_spark" name="feed[is_spark]" value="1"<?php e(($this->prefs['auto_spark']) ? ' checked="checked"' : '')?> /></span> add to Sparks
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'sparks');">?</span>
						</label>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="feed_tab-display" class="tab">

		<h2>Display</h2>
		<fieldset>
<?php
$feed = array
(
	'id' 			=> 0,
	'sort_order' 	=> -1,
	'item_allows' 	=> -1,
	'item_excerpts'	=> -1,
	'unread_counts'	=> -1
);
include($this->view_file('manage/display-options-feed'));
?>
		</fieldset>
	</div><!-- /.tab -->

	<div id="feed_tab-authentication" class="tab">
		<h2>Authentication <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-auth');">?</span></h2>
		<fieldset>
			<table>
				<tr>
					<th>Username</th>
					<td><span class="w"><input type="text" name="feed[username]" autocomplete="off" /></span></td>
				</tr>
				<tr>
					<th class="proto">Password</th>
					<td class="proto"><span class="w"><input type="password" name="feed[password]" autocomplete="off" /></span></td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div class="btn-row">
		<fieldset>
			<span onclick="Fever.dismissDialog();" class="btn text">Cancel<i></i></span>
			<span onclick="Fever.Feedlet.dismissOptions(); Fever.dismissDialog();" class="btn text default">Done<i></i></span>
		</fieldset>
	</div>
</div><!-- #feed-options-template -->

<script type="text/javascript" language="javascript">
// <![CDATA[

var toggleAll = function(elem)
{
	var i = '<i></i>'; // wah!
	var checkboxes = $('input.select-all');
	var check = (elem.innerHTML == 'Select All' + i);
	// alert(elem.innerHTML);
	elem.innerHTML = (check ? 'Deselect All' : 'Select All') + i;
	for (var j = 0; j < checkboxes.length; j++)
	{
		checkboxes[j].checked = check;
	};
};

Fever.Feedlet =
{
	abbr	: function(title)
	{
		if (title.length > 50)
		{
			title = title.substr(0, 50) + '&#8230;';
		}
		return title;
	},
	options : function(i, title)
	{
		var html	= '';
		var options = one('#feed-options-' + i);
		if (options.innerHTML.isEmpty())
		{
			html = one('#feed-options-template').innerHTML;
			html = html.replace(/name="feed\[/g, 'name="feeds[' + i + '][');
			html = html.replace(/id="feed_/g, 'id="feed_' + i + '_');
			html = html.replace(/#feed_/g, '#feed_' + i + '_');
			html = html.replace('<h1></h1>', '<h1>' + this.abbr(title) + '</h1>');

			var select = '';
			var url = one('#feed-' + i).value;
			if (this.feeds[i].length > 1)
			{
				select += '<tr>';
				select += '<th>Format';
				select += '</th>';
				select += '<td><span class="w">';
				select += '<select id="feed-' + i + '-url-select">';

				for (var j = 0; j < this.feeds[i].length; j++)
				{
					var feed = this.feeds[i][j];
					var selected = (feed.value == url) ? ' selected="selected"' : '';

					select += '<option value="' + feed.value + '"' + selected + '>';
					select += feed.text;
					select += '</option>';
				};
				select += '</select>';
				select += '</span></td>';
				select += '</tr>';
			};
			html = html.replace('<table class="top">', '<table class="top">' + select);
		};

		html = '<form id="feed-options-' + i + '-form" onsubmit="Fever.Feedlet.dismissOptions(); Fever.dismissDialog(); return false;" class="tabbed">' + html + '</form>';
		Fever.addDialog(html);

		// manually copy potentially updated options over to dialog form
		if (!options.innerHTML.isEmpty())
		{
			var form = one('#feed-options-' + i + '-form');
			while (options.childNodes.length > 0)
			{
				var node = options.firstChild;
				options.removeChild(node);
				form.appendChild(node);
			};
		};
	},
	dismissOptions : function()
	{
		var form = one('#dialog form');
		var i = form.id.replace(/feed-options-([0-9]+)-form/, '$1');

		var select = one('#feed-' + i + '-url-select');
		if (select)
		{
			one('#feed-' + i).value = select.value;
		};

		var options = one('#feed-options-' + i);
		options.innerHTML = '';
		while (form.childNodes.length > 0)
		{
			var node = form.firstChild;
			form.removeChild(node);
			options.appendChild(node);
		};
		one('#feed-' + i).checked = true;
	}
};
Fever.Feedlet.feeds = {<?php e(implode(',', $menus_js)); ?>};
// ]]>
</script>
<?php $this->render('page/footer');?>