<?php
$this->relationships();
$group	= $this->groups[$_GET['group_id']];
$html	= '';
foreach($this->feeds as $feed)
{
	if ($feed['id'] == 0) { continue; }
	$selected = (isset($this->feed_ids_by_group_id[$group['id']]) && in($this->feed_ids_by_group_id[$group['id']], $feed['id'])) ? ' selected="selected"' : '';
	$html .= '<option value="'.$feed['id'].'"'.$selected.'>'.$this->title($feed).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" class="tabbed">
	<input type="hidden" name="action" value="edit-group" />
	<input type="hidden" name="group[id]" value="<?php e($group['id']); ?>" />

	<h1>Edit group</h1>

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
					<td class="proto"><span class="w"><input type="text" name="group[title]" value="<?php e(h($group['title'])); ?>" /></span></td>
				</tr>
				<tr>
					<th class="proto">Feed(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></th>
					<td>
						<span class="w"><select name="group[feed_ids][]" multiple="multiple"><?php e($html); ?></select></span>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="tab-display" class="tab">
		<h2>Display</h2>
		<fieldset>

<?php include($this->view_file('manage/display-options-group')); ?>

		</fieldset>
	</div><!-- /.tab -->

	<div class="btn-row">
		<fieldset>
			<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
			<button><span class="btn text default">Save<i></i></span></button>
		</fieldset>
	</div>
</form>