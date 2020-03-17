Fever.iPhone.lastRefreshedOnTime	= <?php e($this->last_refreshed_on_time); ?>000;
Fever.iPhone.lastCachedOnTime		= <?php e($this->last_cached_on_time); ?>000;
Fever.iPhone.groupIdsByFeedId = {<?php
$groups_js = array();
foreach($this->group_ids_by_feed_id as $feed_id => $feeds_group_ids)
{
	$groups_js[] = $feed_id.':['.join(',', $feeds_group_ids).']';
}
e(join(',', $groups_js));
?>};

Fever.iPhone.sparksFeedIds = [<?php e(join(',', $this->sparks_feed_ids)); ?>];