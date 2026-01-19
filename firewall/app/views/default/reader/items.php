<?php if (!empty($this->items)):?>
<?php foreach($this->items as $item):?>
<?php
$feed = $this->feeds[$item['feed_id']];
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

if (empty($item['title'])) {
	$item['title'] = '&#8230;';
}

$item_allows 			= $this->option('item_allows', $feed['id']);
$item_excerpts			= $this->option('item_excerpts', $feed['id']);
$prevents_hotlinking	= $feed['prevents_hotlinking'];

$class .= $item_excerpts ? '' : ' full';
?>
	<div id="item-<?php e($item['id'])?>" class="box item <?php e($class);?>" onclick="Fever.Reader.onBoxClick(this);">
		<div class="content">
			<div class="meta">
				<h1><a href="<?php e($item['link'])?>" rel="external" onclick="Fever.Reader.markItemAsRead(<?php e($item['id'])?>);"><?php e($this->highlight(widont($item['title'])))?></a></h1>
				<h2>
					<a href="./?ui[feed_id]=<?php e($feed['id'])?>" onclick="return Fever.Reader.loadFeed(<?php e($feed['id'])?>, 1);" class="inline-feed feed-<?php e($feed['id'])?><?php e(($feed['unread_count']) ? '' : ' read')?>">
						<i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i>
						<?php e($this->title($feed))?>
					</a>
					<span class="posted">
						<span class="timestamp ago-<?php e($item['created_on_time'])?>000"><?php e(ucfirst(ago($item['created_on_time']))); ?></span>
<?php if (!empty($item['author'])): ?>
						by <?php e($item['author']);?>
<?php endif;?>
					</span>
					<span class="btn menu feed" onclick="Fever.displayMenu(this, 'feed', <?php e($feed['id'])?>);"></span>
				</h2>
				<span class="state" onclick="Fever.Reader.toggleItemSaveState(<?php e($item['id'])?>);" title="Toggle item save state"></span>
				<span class="btn menu item" onclick="Fever.displayMenu(this, 'item', <?php e($item['id'])?>);"></span>
			</div><!-- .meta -->
			<div class="item-content"><?php e($this->content($item['description'], $item_excerpts, $item_allows, $prevents_hotlinking));?></div><!-- .item-content -->
		</div><!-- .content -->

		<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b></s>
	</div><!-- .box.item -->
<?php endforeach;?>
<?php onload("Fever.Reader.onItemsLoaded();"); ?>
<?php else:?>
<?php
$msg = '';
// TODO: format and style, in links too
if ($this->page == 1)
{
	switch($this->prefs['ui']['section'])
	{
		case 4: // search
			if (empty($this->prefs['ui']['search']))
			{
				$msg = 'Please enter a search term.';
				// include a search form?
			}
			else
			{
				$msg = 'No items matched your search.';
			}
		break;

		case 2: // saved
			$msg = 'There are no saved items. Save an item by clicking the plus icon that appears when you roll over it.';
		break;

		default: // sparks and groups
			$label = ($this->prefs['ui']['feed_id']) ? 'feed' : 'group';
			if (!$this->prefs['ui']['show_read'])
			{
				$msg  = 'There are no unread items in this '.$label.'. <a class="btn text" href="./?ui[show_read]=0" onclick="return Fever.Reader.toggleUnread();">Show read<i></i></a> ';
				$msg .= '<span class="btn text" onclick="Fever.Reader.refresh'.ucfirst($label).'('.$this->prefs['ui'][$label.'_id'].');">Refresh '.$label.'<i></i></span>';
			}
			else if ($this->prefs['ui']['feed_id'])
			{
				$feed = $this->feeds[$this->prefs['ui']['feed_id']];
				if ($feed['requires_auth'] && empty($feed['auth']))
				{
					$msg = 'This feed requires authentication. <span class="btn text" onclick="Fever.Reader.feedRequiresAuth('.$feed['id'].');">Authentication feed<i></i></span>';
				}
				else
				{
					$msg = 'There are no items in this feed. It may not have been updated in the past ten weeks. <span class="btn text" onclick="Fever.Reader.refreshFeed('.$feed['id'].');">Refresh feed<i></i></span>';
				}
			}
			else if (count($this->feed_ids) > 1)
			{
				$msg = 'There are no items in this group. <span class="btn text" onclick="Fever.Reader.refreshGroup('.$this->prefs['ui']['group_id'].');">Refresh group<i></i></span>';
			}
			else
			{
				$msg = 'There are no feeds in this group. <span class="btn text" onclick="return Fever.Reader.addFeed();">New feed...<i></i></span>';
			}
	}
}
else
{
	onload("Fever.Reader.onLastPage();");
}
e($msg);
?>
<?php endif;?>