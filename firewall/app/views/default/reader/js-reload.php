Fever.Reader.ui.groupId		= <?php e($this->prefs['ui']['group_id']); ?>;
Fever.Reader.ui.feedId		= <?php e($this->prefs['ui']['feed_id']); ?>;

Fever.Reader.lastRefreshedOnTime	= <?php e($this->last_refreshed_on_time); ?>000;
Fever.Reader.lastCachedOnTime		= <?php e($this->last_cached_on_time); ?>000;
Fever.Reader.lastRenderedOnTime		= <?php e(time()); ?>000;
Fever.Reader.totalFeeds				= <?php e($this->total_feeds); ?>;
Fever.Reader.totalItems				= <?php e($this->total_items); ?>;
Fever.Reader.totalUnread			= <?php e($this->total_unread); ?>;

Fever.Reader.page			= <?php e($this->page); ?>;
Fever.Reader.pageMaxed 		= false;
Fever.Reader.pageLoading 	= false;

Fever.Reader.groupIdsByFeedId = {<?php
$groups_js = array();
foreach($this->group_ids_by_feed_id as $feed_id => $feeds_group_ids)
{
	$groups_js[] = $feed_id.':['.join(',', $feeds_group_ids).']';
}
e(join(',', $groups_js));
?>};

Fever.Reader.sparksFeedIds = [<?php e(join(',', $this->sparks_feed_ids)); ?>];