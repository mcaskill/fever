<?php

$this->relationships();
$html = '';
foreach($this->groups as $group)
{
	// ignore the All supergroup
	if ($group['id'] == 0) { continue; }

	$selected = ($this->prefs['ui']['section'] == 1 && $this->prefs['ui']['group_id'] == $group['id']) ? ' selected="selected"'  : '';
	$html .= '<option value="'.$group['id'].'"'.$selected.'>'.h($group['title']).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" class="tabbed">
	<input type="hidden" name="action" value="add-feed" />

	<h1>Add a feed</h1>

	<ul class="tabs">
		<li class="active"><a href="#tab-feed">Feed</a></li>
		<li><a href="#tab-display">Display</a></li>
		<li><a href="#tab-authentication">Authentication <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-auth');">?</span></a></li>
	</ul>

	<div id="tab-feed" class="tab active">
		<h2>Feed</h2>
		<fieldset>
			<table>
				<tr>
					<th class="proto">Feed url</th>
					<td class="proto"><span class="w"><input type="text" name="feed[url]" /></span></td>
				</tr>
				<tr>
					<th class="proto">Feed title <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-title');">?</span></th>
					<td class="proto"><span class="w"><input type="text" name="feed[title]" value="" /></span></td>
				</tr>
				<tr>
					<th>Images</th>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="feed[prevents_hotlinking]" value="1" /></span> site prevents hotlinking
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'hotlinking');">?</span>
						</label>
					</td>
				</tr>
				<tr>
					<th>Add to <span style="white-space:nowrap;">group(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></span></th>
					<td>
						<span class="w"><select onchange="one('#feed_is_spark').checked=false;" name="feed[group_ids][]" multiple="multiple"><?php e($html); ?></select></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="hidden" id="feed_is_spark_default" name="feed[is_spark]" value="0" />
						<label>
							<span class="i"><input type="checkbox" id="feed_is_spark" name="feed[is_spark]" value="1"<?php e(($this->prefs['auto_spark']) ? ' checked="checked"' : '')?> /></span> add to Sparks
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'sparks');">?</span>
						</label>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="tab-display" class="tab">
		<h2>Display</h2>
			<fieldset>
<?php
$feed = array
(
	'id' 			=> 0,
	'sort_order' 	=> -1,
	'item_allows' 	=> -1,
	'item_excerpts'	=> -1,
	'unread_counts'	=> -1
);
include($this->view_file('manage/display-options-feed'));
?>
			</fieldset>
	</div><!-- /.tab -->

	<div id="tab-authentication" class="tab">
		<h2>Authentication <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-auth');">?</span></h2>
		<fieldset>
			<table>
				<tr>
					<th>Username</th>
					<td><span class="w"><input type="text" name="feed[username]" autocomplete="off" /></span></td>
				</tr>
				<tr>
					<th class="proto">Password</th>
					<td class="proto"><span class="w"><input type="password" name="feed[password]" autocomplete="off" /></span></td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div class="btn-row">
		<fieldset>
			<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
			<button><span class="btn text default">Save<i></i></span></button>
		</fieldset>
	</div>
</form>