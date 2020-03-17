<?php
$this->relationships();
$item_id = $_GET['mobile']['item_id'];
$item = $this->get_one('items', $this->prepare_sql('`id`=?', $item_id));
$feed = $this->feeds[$item['feed_id']];

$item_allows 			= $this->option('item_allows', $feed['id']);
$item_excerpts			= false;
$prevents_hotlinking	= $feed['prevents_hotlinking'];
$item_content 			= $this->content($item['description'], $item_excerpts, $item_allows, $prevents_hotlinking);
$item_content 			= sr('<img ', '<img onload="Fever.iPhone.resizeScreenContainer();" ', $item_content);

if ($item['is_saved'])
{
	$class = 'saved';
}
else // if ($this->prefs['ui']['section'] != 4)
{
	if (!$item['read_on_time'])
	{
		if ($item['added_on_time'] > $this->cfg['last_session_on_time'])
		{
			$class = 'new';
		}
		else
		{
			$class = 'unread';
		}
	}
	else
	{
		$class = 'read';
	}
}

$sections = array
(
	'Hot',
	'Kindling',
	'Saved',
	'Sparks',
	'Search'
);

$back_title = $this->feeds[$this->prefs['ui']['feed_id']]['title'];
if ($this->prefs['ui']['section'] == 0)
{
	$back_title = 'Hot';
}
else if ($this->prefs['ui']['feed_id'] == 0)
{
	if ($this->prefs['ui']['section'] == 1)
	{
		$back_title = $this->groups[$this->prefs['ui']['group_id']]['title'];
	}
	else
	{
		$back_title = $sections[$this->prefs['ui']['section']];
	}
}

?>

	<div id="item">
		<div class="header">
			<div class="left">
				<!-- TODO: DETECT PREVIOUS SCREEN USING SECTION/GROUP_ID/FEED_ID AND OTHER SETTINGS -->
				<a onclick="Fever.iPhone.previousScreen();" class="btn back"><?php e($back_title)?></a>
			</div>

			<div class="title unread">
				<?php e($item['title'])?>
			</div>

			<div class="right">
				<!-- <a onclick="Fever.iPhone.displayShareTo();" class="btn">Share to</a>-->
			</div>
		</div>
		<div class="box item <?php e($class)?>">
			<div class="meta">
				<h1><a href="<?php e($item['link'])?>" rel="external"><?php e($this->highlight(widont($item['title'])))?></a></h1>
				<h2>
					<a><i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i> <?php e($feed['title'])?></a>
					<span><?php e(ucfirst(ago($item['created_on_time']))); ?>
<?php if (!empty($item['author'])): ?>
					by <?php e($item['author']);?>
<?php endif;?>
				</span>
				</h2>
				<span onclick="Fever.iPhone.toggleItemSaveState(<?php e($item['id'])?>);" class="state"></span>
			</div>
			<div class="item-content"><?php e($item_content);?></div><!-- .item-content -->
		</div>
		<div class="footer">
			<div class="left">
				<a onclick="Fever.iPhone.loadPreviousItem();" class="btn back" id="prev-item">Previous Item</a>
			</div>

			<div class="right">
				<a onclick="Fever.iPhone.loadNextItem();" class="btn forward" id="next-item">Next Item</a>
			</div>
		</div>
	</div>
<?php onload("Fever.iPhone.onItemLoaded({$item_id});");?>