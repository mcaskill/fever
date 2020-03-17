<?php $this->relationships(); ?>
<div id="fever">
	<div class="header">
		<div class="left" onclick="Fever.iPhone.reload();">

		</div>

		<div class="title" onclick="Fever.iPhone.reload();">
			Fever
		</div>

		<div class="right">
			<a onclick="Fever.iPhone.displayPreferences();" class="btn action">Preferences</a>
		</div>
	</div>
	<div class="box">
		<ul class="list">
			<li id="section-0" onclick="Fever.iPhone.loadSection(0);"><a>Hot</a></li>
		<?php foreach($this->groups as $group):?>
			<?php
			$class  = (!$group['unread_count']) ? 'read' : 'unread';
			$class .= ($this->option('unread_counts', 0, $group['id']) == 0) ? ' hide-unread' : '';
			// if (!$this->prefs['ui']['show_read'] && !$group['unread_count'] && $group['id'] != 0) { continue; }
			?>
			<li onclick="Fever.iPhone.loadGroup(<?php e($group['id']);?>);" id="group-<?php e($group['id'])?>" class="<?php e($class)?>"><a><?php e($group['title'])?></a> <span class="unread-count"><?php e($group['unread_count'])?></span></li>
		<?php endforeach;?>
			<li onclick="Fever.iPhone.loadSection(2);" id="section-2"><a>Saved</a> <span class="saved-count"><?php e($this->total_saved)?></span></li>
			<li onclick="Fever.iPhone.loadSection(3);" id="section-3"><a>Sparks</a></li>
			<li id="section-4"><form onsubmit="return Fever.iPhone.loadSearch();"><input id="q" onclick="window.event.cancelBubble=true;" type="search" value="<?php e(quote($this->prefs['ui']['search']))?>" /></form></li>
		</ul>
	</div>

	<div class="footer">
		<div class="left">
			Last refreshed <strong id="total-feeds"><?php e($this->total_feeds);?></strong> <?php e(pluralize($this->total_feeds, 'feed', false))?>
			<strong id="last-refresh" class="timestamp ago-<?php e($this->last_refreshed_on_time); ?>000"><?php e(ago($this->last_refreshed_on_time)); ?></strong>
		</div>

		<div class="right">
			<a onclick="window.location='./?logout';" class="btn logout">Logout</a>
		</div>
	</div>
</div>