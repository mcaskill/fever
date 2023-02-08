Fever.iPhone.lastRefreshedOnTime	= <?php e($this->last_refreshed_on_time); ?>000;
Fever.iPhone.lastCachedOnTime		= <?php e($this->last_cached_on_time); ?>000;
Fever.iPhone.groupIdsByFeedId = {<?php
$groups_js = array();
foreach($this->group_ids_by_feed_id as $feed_id => $feeds_group_ids)
{
	$groups_js[] = $feed_id.':['.implode(',', $feeds_group_ids).']';
}
e(implode(',', $groups_js));
?>};

Fever.iPhone.sparksFeedIds = [<?php e(implode(',', $this->sparks_feed_ids)); ?>];