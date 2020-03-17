<?php

function format_degree($degree)
{
	// split integer and decimal for decimal alignment
	$html = r('#^([^\.]+)(.+)$#', '<span class="integer">$1</span><span class="decimal">$2<em>&deg;</em></span>', $degree);
	// manually kern our ones
	$html = sr('1', '<span class="kern-1">1</span>', $html.'');
	return $html;
}

?>
<?php if (!empty($this->links_by_degrees)):?>

<?php foreach($this->links_by_degrees as $degree => $links):?>
	<div class="degree">
		<div class="notches"><b></b></div>
		<div class="temperature"><?php e(format_degree($degree))?></div>

<?php foreach($links as $link):?>
<?php
$link['feed'] 			= $this->feeds[$link['feed_id']];
$item_allows 			= $this->option('item_allows', $link['feed_id']);
$item_excerpts			= $this->option('item_excerpts', $link['feed_id']);
$prevents_hotlinking 	= $link['feed']['prevents_hotlinking'];

$item_class 	= ($link['is_item'] && $link['is_local']) ? ' an-item item-'.$link['item_id'] : '';
$item_class    .= $item_excerpts ? '' : ' full';
$item_class    .= $link['is_saved'] ? ' saved' : '';
?>
		<div class="box link<?php e($item_class); ?>" id="link-<?php e($link['id']); ?>" onclick="Fever.Reader.onBoxClick(this);">
			<div class="content">
				<div class="meta">
<?php $onclick = ($link['is_item']) ? ' onclick="Fever.Reader.markItemAsRead('.$link['item_id'].');"' : ''; ?>
					<h1><a href="<?php e($link['url'])?>" rel="external"<?php e($onclick);?>><?php e(widont($link['title']))?></a></h1>
<?php if ($link['is_item'] && $link['is_local']):?>
					<h2>from <a href="./?ui[feed_id]=<?php e($link['feed']['id'])?>" onclick="return Fever.Reader.loadFeed(<?php e($link['feed']['id'])?>);" class="feed-<?php e($link['feed']['id']);?>">
						<i class="favicon <?php e($this->favicon_class($link['feed']))?>"><i></i></i>
						<?php e($this->title($link['feed']))?>
					</a></h2>
					<span class="state" onclick="Fever.Reader.toggleLinkSaveState(<?php e($link['id'])?>);" title="Toggle link save state"></span>
<?php endif;?>
					<span class="btn blacklist" onclick="Fever.Reader.addLinkToBlacklist(<?php e($link['id']); ?>);" title="Add link to blacklist"></span>
				</div>
				<div class="item-content"><?php e($this->content($link['description'], 1, $item_allows, $prevents_hotlinking));?></div><!-- .item-content -->

				<ul class="source-list">
<?php foreach($link['item_ids'] as $id):?>
<?php $item = $this->items[$id];?>
<?php $feed = $this->feeds[$item['feed_id']];?>
					<li><span class="source item-<?php e($item['id'])?> <?php e(($item['read_on_time']) ? 'read' : 'unread');?>">
						<a href="<?php e($item['link'])?>" rel="external" onclick="Fever.Reader.markItemAsRead(<?php e($item['id'])?>);"><?php e($item['title'])?></a> from
						<a href="./?ui[feed_id]=<?php e($feed['id'])?>" onclick="return Fever.Reader.loadFeed(<?php e($feed['id'])?>);" class="inline-feed feed-<?php e($feed['id'])?>"><i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i> <?php e($feed['title'])?></a>
					</span>
					</li>
<?php endforeach;?>
				</ul><!-- .source-list -->
			</div><!-- .content -->
			<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b></s>
		</div><!-- .box.link -->
<?php endforeach;?>
	</div><!-- .degree -->
<?php endforeach;?>
<?php else:?>
<?php
// TODO: format and style, in items too
$msg = '';
if ($this->page == 1)
{
	if ($this->prefs['ui']['hot_range'] < 7)
	{
		$msg  = 'Nothing to report for this time period. ';
		$msg .= 'Try the <a class="btn text" href="./?ui[hot_start]=0&amp;ui[hot_range]=7" onclick="return Fever.Reader.loadHot(0, 7);">past week starting now<i></i></a>';
	}
	else if ($this->prefs['ui']['hot_range'] < 31)
	{
		$msg  = 'Nothing to report for this time period. ';
		$msg .= 'Try the <a class="btn text" href="./?ui[hot_start]=0&amp;ui[hot_range]=31" onclick="return Fever.Reader.loadHot(0, 31);">past month starting now<i></i></a>';
	}
	else
	{
		$msg = 'Try subscribing to more feeds with overlapping focuses to increase Fever\'s effectiveness. <span class="btn text" onclick="return Fever.Reader.addFeed();">New feed...<i></i></span>';
	}
}
else
{
	onload("Fever.Reader.onLastPage();");
}
e($msg);
?>
<?php endif;?>