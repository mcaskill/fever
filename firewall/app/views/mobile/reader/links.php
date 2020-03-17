<?php
$this->relationships();
$this->build_links();

function format_degree($degree)
{
	$html = r('#([0-9])#', '<span class="num n$1">$1</span>', $degree);
	$html = sr('.', '<span class="num np">.</span>', $html).'<span class="num nd">&deg;</span>';
	return $html;
}
?>
<?php if ($this->page == 1):?>
<div id="hot">
	<div class="header">
		<div class="left">
			<a onclick="Fever.iPhone.previousScreen();" class="btn back">Fever</a>
		</div>

		<div class="title">
			Hot
		</div>

		<div class="right"></div>
	</div>

	<div class="control">
		<form>
			Links from the past
			<span class="btn menu" id="btn-hot-range">
				<?php e($this->hot_range[$this->prefs['ui']['hot_range']])?>
				<select id="menu-hot-range" onchange="Fever.iPhone.reloadHot();">
					<?php foreach($this->hot_range as $value => $text):?>
					<?php $selected = ($this->prefs['ui']['hot_range'] == $value) ? ' selected="selected"' : '';?>
					<option value="<?php e($value)?>"<?php e($selected)?>><?php e(($text != '-') ? $text : '')?></option>
					<?php endforeach;?>
				</select>
				<span></span>
			</span>
			starting
			<span class="btn menu" id="btn-hot-start">
				<?php e($this->hot_start[$this->prefs['ui']['hot_start']])?>
				<select id="menu-hot-start" onchange="Fever.iPhone.reloadHot();">
					<?php foreach($this->hot_start as $value => $text):?>
					<?php $selected = ($this->prefs['ui']['hot_start'] == $value) ? ' selected="selected"' : '';?>
					<option value="<?php e($value)?>"<?php e($selected)?>><?php e(($text != '-') ? $text : '')?></option>
					<?php endforeach;?>
				</select>
				<span></span>
			</span>
		</form>
	</div>
<?php endif; // if($this->page == 1):?>

<?php if (!empty($this->links_by_degrees)):?>

<?php foreach($this->links_by_degrees as $degree => $links):?>

	<div class="box">
		<div class="tmp"><?php e(format_degree($degree))?></div>

<?php foreach($links as $link):?>
<?php
$link['feed'] 			= $this->feeds[$link['feed_id']];
$item_allows 			= $this->option('item_allows', $link['feed_id']);
$item_excerpts			= $this->option('item_excerpts', $link['feed_id']);
$prevents_hotlinking 	= $link['feed']['prevents_hotlinking'];

$item_class 	= ($link['is_item'] && $link['is_local']) ? ' item-'.$link['item_id'] : '';
$item_class    .= $item_excerpts ? '' : ' full';
?>
		<div class="link" id="link-<?php e($link['id'])?>">
			<?php if ($link['is_item'] && $link['is_local']):?>
			<h1><a onclick="Fever.iPhone.loadItem(<?php e($link['item_id'])?>,<?php e($link['id'])?>);"><?php e(widont($link['title']))?></a></h1>
			<h2><a><i class="favicon <?php e($this->favicon_class($link['feed']))?>"><i></i></i> <span>from</span> <?php e($this->title($link['feed']))?></a></h2>
			<div class="item-content"><?php e(excerpt($link['description']));?></div>
			<?php else:?>
			<h1><a href="<?php e($link['url'])?>" onclick="return Fever.iPhone.loadWebView(this.href);"><?php e(widont($link['title']))?></a></h1>
			<?php endif;?>

			<ul class="source-list">
<?php foreach($link['item_ids'] as $id):?>
<?php $item = $this->items[$id];?>
<?php $feed = $this->feeds[$item['feed_id']];?>
<?php $class = (!$item['read_on_time']) ? 'unread' : 'read'; ?>
				<li onclick="Fever.iPhone.loadItem(<?php e($item['id'])?>,<?php e($link['id'])?>);Fever.iPhone.markItemAsRead(<?php e($item['id'])?>);" class="<?php e($class)?> item-<?php e($item['id'])?>" id="link-<?php e($link['id'])?>-item-<?php e($item['id'])?>"><a class="feed-<?php e($feed['id'])?>"><i class="favicon <?php e($this->favicon_class($feed))?>"><i></i></i><?php e($item['title'])?></a></li>
<?php endforeach; // items ?>
			</ul><!-- .source-list -->
		</div><!-- .link -->

<?php endforeach; // links ?>
	</div><!-- .box -->
<?php endforeach; // degrees?>


<?php elseif ($this->page != 1):?>
<?php onload("Fever.iPhone.pageMaxed=true;");?>
<?php endif; // if(empty($this->links_by_degrees)):else if ($this->page != 1):?>

<?php if ($this->page == 1):?>
</div>
<?php endif; // if($this->page == 1):?>