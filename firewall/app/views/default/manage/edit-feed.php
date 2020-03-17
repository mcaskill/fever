<?php
$this->relationships();

// set up our feed
$feed = $this->feeds[$_GET['feed_id']];
$feed_auth = array
(
	'username' => '',
	'password' => ''
);
if ($feed['requires_auth'] && !empty($feed['auth']))
{
	list($feed_auth['username'], $feed_auth['password']) = explode(':', base64_decode($feed['auth']));
}

$html = '';
foreach($this->groups as $group)
{
	// ignore the All supergroup
	if ($group['id'] == 0) { continue; }

	$selected = (isset($this->group_ids_by_feed_id[$feed['id']]) && in($this->group_ids_by_feed_id[$feed['id']], $group['id'])) ? ' selected="selected"' : '';
	$html .= '<option value="'.$group['id'].'"'.$selected.'>'.h($group['title']).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" class="tabbed">
	<input type="hidden" name="action" value="edit-feed" />
	<input type="hidden" name="feed[id]" value="<?php e($feed['id']); ?>" />

	<h1>Edit feed</h1>

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
					<th class="proto">Feed title <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-title');">?</span></th>
					<td class="proto"><span class="w"><input type="text" name="feed[title]" value="<?php e($feed['title']); ?>" /></span></td>
				</tr>
				<tr>
					<th>Feed url</th>
					<td><span class="w"><input type="text" name="feed[url]" value="<?php e($feed['url']); ?>" /></span></td>
				</tr>
				<tr>
					<th>Images</th>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="feed[prevents_hotlinking]" value="1"<?php e(($feed['prevents_hotlinking']) ? ' checked="checked"' : '')?> /></span> site prevents hotlinking
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'hotlinking');">?</span>
						</label>
					</td>
				</tr>
				<tr>
					<th class="proto">In group(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></th>
					<td>
						<span class="w"><select onchange="one('#feed_is_spark').checked=false;" name="feed[group_ids][]" multiple="multiple"><?php e($html); ?></select></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<label>
							<span class="i"><input type="checkbox" id="feed_is_spark" name="feed[is_spark]" value="1"<?php e(($feed['is_spark']) ? ' checked="checked"' : '')?> /></span> is a Spark
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'sparks');">?</span>
						</label>
					</td>
				</tr>
				<!--
				<tr>
					<th>Favicon</th>
					<td><i class="favicon f<?php e($feed['favicon_id']);?>"><i></i></i> <span class="btn text">recache<i></i></span></td>
				</tr>
				-->
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="tab-display" class="tab">
		<h2>Display</h2>
		<fieldset>
<?php include($this->view_file('manage/display-options-feed')); ?>
		</fieldset>
	</div><!-- /.tab -->

	<div id="tab-authentication" class="tab">
		<h2>Authentication <span class="btn help" onmouseover="Fever.displayHelp(this, 'feed-auth');">?</span></h2>
		<fieldset>
			<table>
				<tr>
					<th>Username</th>
					<td><span class="w"><input type="text" name="feed[username]" value="<?php e($feed_auth['username']); ?>" autocomplete="off" /></span></td>
				</tr>
				<tr>
					<th class="proto">Password</th>
					<td class="proto"><span class="w"><input type="password" name="feed[password]" value="<?php e($feed_auth['password']); ?>" autocomplete="off" /></span></td>
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