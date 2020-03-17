// initial
Fever.iPhone.mobile.section		= <?php e($this->prefs['ui']['section']); ?>;
Fever.iPhone.mobile.groupId		= <?php e($this->prefs['ui']['group_id']); ?>;
Fever.iPhone.mobile.feedId		= <?php e($this->prefs['ui']['feed_id']); ?>;
Fever.iPhone.mobile.search		= '<?php e(quote($this->prefs['ui']['search'])); ?>';
Fever.iPhone.mobile.hotStart	= <?php e($this->prefs['ui']['hot_start']); ?>;
Fever.iPhone.mobile.hotRange	= <?php e($this->prefs['ui']['hot_range']); ?>;
Fever.iPhone.mobile.showFeeds	= <?php e($this->prefs['ui']['show_feeds']); ?>;
Fever.iPhone.mobile.showRead	= <?php e($this->prefs['ui']['show_read']); ?>;
Fever.iPhone.mobile.autoRead	= <?php e(($this->prefs['auto_read']) ? 1 : 0); ?>; // deprecated
Fever.iPhone.mobile.readOnScroll	= <?php e(($this->prefs['mobile_read_on_scroll']) ? 1 : 0); ?>;
Fever.iPhone.mobile.readOnBackOut	= <?php e(($this->prefs['mobile_read_on_back_out']) ? 1 : 0); ?>;
Fever.iPhone.mobile.viewInApp		= <?php e(($this->prefs['mobile_view_in_app']) ? 1 : 0); ?>;
Fever.iPhone.services 			= [<?php
$services_js = array();
foreach($this->prefs['services'] as $service)
{
	$services_js[] = "['{$service['name']}','{$service['url']}','{$service['key']}']";
}
// not used, save bandwidth by commenting out
// e(join(',', $services_js));
?>];

// initial and reload
<?php $this->render('reader/js-reload');?>