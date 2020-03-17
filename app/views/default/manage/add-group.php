<?php

$this->relationships();
$html = '';
foreach($this->feeds as $feed)
{
	// ignore the superfeed
	if ($feed['id'] == 0) { continue; }

	$html .= '<option value="'.$feed['id'].'">'.$this->title($feed).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" class="tabbed">
	<input type="hidden" name="action" value="add-group" />

	<h1>Create a new group</h1>

	<ul class="tabs">
		<li class="active"><a href="#tab-group">Group</a></li>
		<li><a href="#tab-display">Display</a></li>
	</ul>

	<div id="tab-group" class="tab active">
		<h2>Group</h2>
		<fieldset>
			<table>
				<tr>
					<th class="proto">Group name</th>
					<td class="proto"><span class="w"><input type="text" name="group[title]" /></span></td>
				</tr>
				<?php if(!empty($html)):?>
				<tr>
					<th class="proto">Add feed(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></th>
					<td>
						<span class="w"><select name="group[feed_ids][]" multiple="multiple"><?php e($html); ?></select></span>
					</td>
				</tr>
				<?php endif;?>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="tab-display" class="tab">
		<h2>Display</h2>
		<fieldset>
<?php
$group = array
(
	'id' 			=> 0,
	'sort_order' 	=> -1,
	'item_allows' 	=> -1,
	'item_excerpts'	=> -1,
	'unread_counts'	=> -1
);
include($this->view_file('manage/display-options-group'));
?>
		</fieldset>
	</div><!-- /.tab -->

	<div class="btn-row">
		<fieldset>
			<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
			<button><span class="btn text default">Add<i></i></span></button>
		</fieldset>
	</div>
</form>