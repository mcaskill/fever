<?php
$this->relationships();
$sections = array
(
	'Hot',
	'Kindling',
	'Saved',
	'Sparks',
	'Search'
);

$title = $sections[$this->prefs['ui']['section']];
$title_class = ' '.low($title);
if ($this->prefs['ui']['section'] == 1)
{
	$title = $this->groups[$this->prefs['ui']['group_id']]['title'];
	if ($this->prefs['ui']['group_id'] != 0)
	{
		$title_class = '';
	}
}
?>
<div id="feeds">
	<div class="header">
		<div class="left">
			<a onclick="Fever.iPhone.previousScreen();" class="btn back">Fever</a>
		</div>

		<div class="title<?php e($title_class)?>">
			<?php e($title)?>
		</div>

		<div class="right">
		</div>
	</div>
	<div class="box">
		<ul class="list">
			<?php foreach($this->feed_ids as $id):?>
			<?php
			$feed = $this->feeds[$id];
			if (!$this->prefs['ui']['show_read'] && !$feed['unread_count'] && $feed['id'] != 0  && $this->prefs['ui']['section'] != 2 && $this->prefs['ui']['section'] != 4) { continue; }

			$feed_title = lt($this->title($feed));
			$count_label = ($this->prefs['ui']['section'] == 2) ? 'saved' : (($this->prefs['ui']['section'] == 4) ? 'search' : 'unread');
			$class = (!$feed['unread_count'] && ($this->prefs['ui']['section'] != 2 && $this->prefs['ui']['section'] != 4)) ? ' read' : ' unread';
			$class .= ($this->option('unread_counts', $feed['id']) == 0) ? ' hide-unread' : '';
			?>

				<li id="feed-<?php e($feed['id'])?>" class="<?php e($class)?>" onclick="Fever.iPhone.loadFeed(<?php e($feed['id'])?>);">
					<a>
						<?php if($feed['id'] > 0):?>
							<i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i>
						<?php endif; ?>
						<?php e($feed_title)?>
					</a>
					<span class="<?php e($count_label)?>-count"><?php e($feed[$count_label.'_count'])?></span>
				</li>
			<?php endforeach;?>
		</ul>
	</div>
</div>