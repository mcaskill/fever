// initial
Fever.isIPad				= <?php e($this->is_ipad ? 1 : 0); ?>;
Fever.Reader.ui.section		= <?php e($this->prefs['ui']['section']); ?>;
Fever.Reader.ui.previous	= <?php e($this->prefs['ui']['previous']); ?>;
Fever.Reader.ui.search		= '<?php e(quote($this->prefs['ui']['search'])); ?>';
Fever.Reader.ui.hasFocus	= '<?php e($this->prefs['ui']['has_focus']); ?>';
Fever.Reader.ui.hotStart	= <?php e($this->prefs['ui']['hot_start']); ?>;
Fever.Reader.ui.hotRange	= <?php e($this->prefs['ui']['hot_range']); ?>;
Fever.Reader.ui.showFeeds 	= <?php e(($this->prefs['ui']['show_feeds']) ? 1 : 0); ?>;
Fever.Reader.ui.showRead	= <?php e(($this->prefs['ui']['show_read']) ? 1 : 0); ?>;

Fever.Reader.unreadCounts	= <?php e(($this->prefs['unread_counts']) ? 1 : 0); ?>;
Fever.Reader.newWindow		= <?php e(($this->prefs['new_window']) ? 1 : 0); ?>;
Fever.Reader.autoRead		= <?php e(($this->prefs['auto_read']) ? 1 : 0); ?>;
Fever.Reader.autoReload		= <?php e(($this->prefs['auto_reload']) ? 1 : 0); ?>;
Fever.Reader.autoRefresh	= <?php e(($this->prefs['auto_refresh']) ? 1 : 0); ?>;
Fever.Reader.toggleClick	= <?php e(($this->prefs['toggle_click']) ? 1 : 0); ?>;
Fever.Reader.anonymize		= <?php e(($this->prefs['anonymize']) ? 1 : 0); ?>;

<?php
$hot_start_fragments = array();
foreach($this->hot_start as $value => $text)
{
	if ($text == '-')
	{
		$hot_start_fragments[] = '{divider:true}';
	}
	else
	{
		$hot_start_fragments[] = "{text:'{$text}',value:{$value}}";
	}
}
$hot_start_js = join(',', $hot_start_fragments);

$hot_range_fragments = array();
foreach($this->hot_range as $value => $text)
{
	if ($text == '-')
	{
		$hot_range_fragments[] = '{divider:true}';
	}
	else
	{
		$hot_range_fragments[] = "{text:'{$text}',value:{$value}}";
	}
}
$hot_range_js = join(',', $hot_range_fragments);
?>
Fever.menuControllers.hotStart.items = [<?php e($hot_start_js); ?>];
Fever.menuControllers.hotRange.items = [<?php e($hot_range_js); ?>];

Fever.Reader.services = [<?php
$services_js = array();
foreach($this->prefs['services'] as $service)
{
	$services_js[] = "['{$service['name']}','".sr("'", '%27', $service['url'])."','{$service['key']}']";
}
e(join(',', $services_js));
?>];

// initial and reload
<?php $this->render('reader/js-reload');?>