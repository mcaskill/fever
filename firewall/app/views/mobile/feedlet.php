<?php
// duplicates code in views/default/feedlet.php

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
		$error = 'Feeds displayed in yellow are guesses based on link url or link text and may not be an actual feed.';
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
<style rel="stylesheet">
#feedlet
{
	color: rgba(255, 255, 255, 0.8);
}

#feedlet .header .title
{
	padding: 4px 8px 0 40px;
	text-align: left;
}

#feedlet p.error
{
	margin: -6px 0 12px;
	color: rgba(255, 255, 255, 0.6);
}

#feedlet form
{
	padding: 6px 8px 0 40px;
}

#feedlet li
{
	position: relative;
}

label.checkbox
{
	font-weight: bold;
	color: #fff;
	display: block;
	width: 100%;
	-webkit-box-sizing: border-box;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	margin-right: 0;
	padding: 0 0 0 30px;
}

label.checkbox.disabled
{
	opacity: 0.6;
	padding-right: 0;
}

label.checkbox input
{
	display: none;
}

li.guess label.checkbox
{
	color: #ffc;
}

#feedlet .options-btn
{
	position: absolute;
	top: -2px;
	left: -32px;
	text-indent: -999px;
	width: 26px;
	height: 26px;
	background: url(firewall/app/views/mobile/styles/images/btn-options.png) 0 0 no-repeat;
	background-size: 26px 26px;
}

@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
	#feedlet .options-btn
	{
		background-image: url(firewall/app/views/mobile/styles/images/btn-options@2x.png);
	}
}

#feed-options-template,
#feedlet .feed-options
{
	display: none;
}

#feedlet .btn
{
	max-width: 100%;
}

#action select
{
	width: 100%;
}

#action h1
{
	color: rgba(255, 255, 255, 0.8);
	font-size: 16px;
	margin: 0 30px 12px 0;
	height: 16px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

</style>
<div class="screen" id="screen-0">
	<div id="feedlet">
		<div class="header">
			<div class="left"></div>

			<div class="title">
				Feeds on <?php e(prevent_xss(lt($title))); ?>
			</div>

			<div class="right"></div>
		</div>

		<form method="post" action="<?php e(errors_url('./?manage'));?>">
			<input type="hidden" name="action" value="add-feeds" />
			<input type="hidden" name="url" value="<?php e(prevent_xss($url));?>" />

			<?php if(!empty($error)):?>
				<p class="error"><?php e($error); ?></p>
			<?php endif;?>

			<ul>

<?php foreach($feeds_by_title as $title => $feed_meta):?>
<?php
$default	= $feed_meta['default'];
$feed 		= $feed_meta['feeds'][$default];
$is_guess	= $feed['type']=='a';
$href		= rebuild_url($feed['href'],$feed['protocol']);
?>
<?php if (!$feed['is_subscribed']):?>
		<li<?php echo $is_guess ? ' class="guess"' : ''; ?>>
			<label for="feed-<?php e($i); ?>" class="checkbox<?php e($total_feeds == 1 ? ' checked' : '');?>" onclick="Fever.toggleCheckbox(this);">
				<input type="checkbox" id="feed-<?php e($i); ?>" name="feeds[<?php e($i); ?>][url]" value="<?php e($href); ?>"<?php e($total_feeds == 1 ? ' checked="checked"' : '');?> />
				Add <strong id="feed-title-<?php e($i); ?>"><?php e($title); ?></strong>
			</label>

			<span onclick="Fever.Feedlet.options(<?php e($i); ?>);" class="options-btn">Options</span>
			<div id="feed-options-<?php e($i); ?>" class="feed-options"></div>
		</li>
		<?php else:?>
			<li>
				<label class="checkbox checked disabled">Subscribed to <?php e($title); ?></label>
			</li>
<?php endif;?>
<?php $i++; ?>
<?php endforeach; ?>
				<li	 class="btn-row">
	<?php if ($total_feeds && $subscribed_count != $total_feeds):?>
					<button><span class="btn">Subscribe</span></button>
				<?php endif;?>
					<a href="<?php e(prevent_xss($url));?>" class="btn">Cancel</a>
				</li>
			</ul>

		</form>
	</div>
</div>

	</div><!-- #screens -->
</div><!-- #screens-container -->

<div id="dialog-container">
	<div class="box">
		<div id="dialog">
			<a class="close" onclick="Fever.Feedlet.dismissOptions();">Close</a>
			<div id="feed-options"></div>
		</div>
	</div>
</div>

<div id="feed-options-template">
	<h1></h1>
	<ul>
		<li>
			<input type="hidden" id="feed_is_spark_default" name="feed[is_spark]" value="0" />
			<label for="feed_is_spark" class="checkbox<?php e(($this->prefs['auto_spark']) ? ' checked' : '')?>" onclick="Fever.toggleCheckbox(this);">
				<input type="checkbox" id="feed_is_spark" name="feed[is_spark]" value="1"<?php e(($this->prefs['auto_spark']) ? ' checked="checked"' : '')?> />
				Add to Sparks
			</label>
		</li>
		<li>
			<select onchange="Fever.checkCheckbox('#feed_is_spark', false);" name="feed[group_ids][]"><option>Add to group</option><?php e($groups_html); ?></select>
		</li>
	</ul>
</div>

<script type="text/javascript">

Fever.Feedlet =
{
	dismissOptions : function()
	{
		removeClass($('body'), 'dialog');

		var form = one('#feed-options');
		var options = one('#'+form.className);

		Fever.checkCheckbox('#'+form.className.replace('options-', ''), true);

		while (form.childNodes.length > 0)
		{
			var node = form.firstChild;
			form.removeChild(node);
			options.appendChild(node);
		};
		form.className = '';
		one('html').removeEventListener('click', Fever.dialogListener, true);
	},
	options : function(i)
	{
		var dialog = one('#dialog');
		dialog.style.top = 64 + window.pageYOffset + 'px';
		var options = one('#feed-options-'+i);
		if (options.innerHTML.isEmpty())
		{
			var template 	= one('#feed-options-template');
			var html 		= template.innerHTML;
			var title 		= one('#feed-title-'+i).innerHTML;

			html = html.replace(/name="feed\[/g, 'name="feeds[' + i + '][');
			html = html.replace(/(id|for)="feed_/g, '$1="feed_' + i + '_');
			html = html.replace(/#feed_/g, '#feed_' + i + '_');
			html = html.replace('<h1></h1>', '<h1>' + title + '</h1>');

			options.innerHTML = html;
		};

		var form = one('#feed-options');
		form.className = 'feed-options-'+i;

		while (options.childNodes.length > 0)
		{
			var node = options.firstChild;
			options.removeChild(node);
			form.appendChild(node);
		};

		addClass($('body'), 'dialog');
		one('html').addEventListener('click', Fever.dialogListener, true);
	},

	onOrientationChange : function()
	{
		Fever.iPhone.detectPortrait();
	},
	onload : function()
	{
		this.onOrientationChange();
		screen.orientation.addEventListener('change', function()
		{
			Fever.Feedlet.onOrientationChange();

		}, false);
	}
};
window.addEventListener('load', function() { Fever.Feedlet.onload(); }, false);
</script>

</body>
</html>
