<?php if ($this->prefs['ui']['section'] && $this->prefs['ui']['show_feeds']):?>
<?php
$alpha = array
(
	'A' => '',
	'B' => '',
	'C' => '',
	'D' => '',
	'E' => '',
	'F' => '',
	'G' => '',
	'H' => '',
	'I' => '',
	'J' => '',
	'K' => '',
	'L' => '',
	'M' => '',
	'N' => '',
	'O' => '',
	'P' => '',
	'Q' => '',
	'R' => '',
	'S' => '',
	'T' => '',
	'U' => '',
	'V' => '',
	'W' => '',
	'X' => '',
	'Y' => '',
	'Z' => ''
);
?>
							<ul class="box-list">
<?php foreach($this->feed_ids as $id):?>
<?php
$feed = $this->feeds[$id];
$feed_title = lt($this->title($feed));
$char = up($feed_title[0]);
$count_label = ($this->prefs['ui']['section'] == 2) ? 'saved' : (($this->prefs['ui']['section'] == 4) ? 'search' : 'unread');
$class = (!$feed['unread_count'] && ($this->prefs['ui']['section'] != 2 && $this->prefs['ui']['section'] != 4)) ? ' read' : ' unread';
$class .= (!$feed['total_items'] && $feed['id'] != 0) ? ' abandoned' : '';
$class .= ($this->option('unread_counts', $feed['id']) == 0) ? ' hide-unread' : '';

if (isset($alpha[$char]) && empty($alpha[$char]) && !empty($feed['title']))
{
	$alpha[$char] = $id;
}
?>
<?php if($feed['id'] == 0):?>
								<li id="feed-<?php e($feed['id'])?>"<?php e(($this->prefs['ui']['feed_id'] == $feed['id']) ? ' class="has-focus"' : ''); ?>>
									<a href="./?ui[feed_id]=<?php e($feed['id'])?>" onclick="return Fever.Reader.loadFeed(<?php e($feed['id'])?>);" class="feed-<?php e($feed['id'].$class)?>">
										<i class="icon unread"></i>
										<?php e($feed_title)?>
										<em class="<?php e($count_label)?>-count"><?php e($feed[$count_label.'_count'])?></em>
									</a>
									<span class="btn menu group" onclick="Fever.displayMenu(this, 'group', <?php e($this->prefs['ui']['group_id'])?>);"></span>
									<span class="btn help" onmouseover="Fever.displayHelp(this, 'all-items');"></span>
<?php else: ?>
<?php

if (!$this->prefs['ui']['show_read'] && !$feed['unread_count'] && $count_label != 'saved' && $this->prefs['ui']['section'] != 4)
{
	continue;
}

?>
								<li id="feed-<?php e($feed['id'])?>"<?php e(($this->prefs['ui']['feed_id'] == $feed['id']) ? ' class="has-focus"' : ''); ?>>
									<a href="./?ui[feed_id]=<?php e($feed['id'])?>" onclick="return Fever.Reader.loadFeed(<?php e($feed['id'])?>);" class="feed-<?php e($feed['id'].$class)?> proper">
										<i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i>
										<?php e($feed_title)?>
										<em class="<?php e($count_label)?>-count"><?php e($feed[$count_label.'_count'])?></em>
									</a>
									<span class="btn menu feed" onclick="Fever.displayMenu(this, 'feed', <?php e($feed['id'])?>);"></span>
									<span class="btn refresh" onclick="Fever.Reader.refreshFeed(<?php e($feed['id'])?>);"></span>
<?php endif; ?>
								</li>
<?php endforeach;?>
							</ul><!-- .box-list -->
<?php endif; ?>