<?php
$this->relationships();
$this->build_items();

$sections = array
(
	'Hot',
	'Kindling',
	'Saved',
	'Sparks',
	'Search'
);

$back_title = $sections[$this->prefs['ui']['section']];
if ($this->prefs['ui']['show_feeds'] == 0)
{
	$back_title = 'Fever';
}
else if ($this->prefs['ui']['section'] == 1)
{
	$back_title = $this->groups[$this->prefs['ui']['group_id']]['title'];
}

?>
<?php if ($this->page == 1):?>
<div id="items">
	<div class="header">
		<div class="left">
			<a onclick="Fever.iPhone.previousScreen();" class="btn back"><?php e($back_title)?></a>
		</div>

		<!-- TODO: something with the title/class -->
		<div class="title">
			<?php e($this->feeds[$this->prefs['ui']['feed_id']]['title'])?>
		</div>

		<div class="right">
		</div>
	</div>
<?php endif; // if($this->page == 1):?>

<?php if (!empty($this->items)):?>
<?php if ($this->page == 1):?>
	<div class="box">
		<ul class="list">
<?php endif; // if($this->page == 1):?>

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
						$class = 'new unread';
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
			?>
			<li id="item-<?php e($item['id'])?>" class="<?php e($class)?>" onclick="Fever.iPhone.loadItem(<?php e($item['id'])?>);Fever.iPhone.markItemAsRead(<?php e($item['id'])?>);">
				<a class="feed-<?php e($item['feed_id']);?>">
					<i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i>
					<?php e($this->highlight(widont($item['title'])))?>
				</a>
				<div class="item-content"><?php e(excerpt($item['description']))?></div>
			</li>
			<?php endforeach;?>
<?php if ($this->page == 1):?>
		</ul>
	</div>
<?php endif; // if($this->page == 1):?>
<?php elseif ($this->page != 1):?>
<?php onload("Fever.iPhone.pageMaxed=true;");?>
<?php endif; // if(!empty($this->items)):else if($this->page != 1):?>
<?php if ($this->page == 1):?>
</div>
<?php endif; // if($this->page == 1):?>