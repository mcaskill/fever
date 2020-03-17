<?php if ($this->prefs['ui']['section'] && $this->prefs['ui']['show_feeds']):?>
				<li><a href="./?ui[show_feeds]=0" class="btn cancel" onclick="return Fever.Reader.toggleFeeds();"></a></li>
				<li><a href="#feed-0" onclick="return Fever.Reader.scrollToFeed(0, 1);">#</a></li>
<?php foreach($alpha as $char => $feed_id):?>
<?php if(!empty($feed_id)):?>
				<li><a href="#feed-<?php e($feed_id); ?>" onclick="return Fever.Reader.scrollToFeed(<?php e($feed_id); ?>, 1);"><?php e($char); ?></a></li>
<?php else: ?>
				<li><?php e($char); ?></li>
<?php endif; ?>
<?php endforeach;?>
<?php endif;?>