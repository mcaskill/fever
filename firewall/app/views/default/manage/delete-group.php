<?php

$group = $this->get_one('groups', $this->prepare_sql('`id` = ?', $_GET['group_id']));

?>

<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="delete-group" />
	<input type="hidden" name="id" value="<?php e($group['id']); ?>" />

	<h1>Delete Group</h1>
	<fieldset>
		<table>
			<tr>
				<td colspan="2">
					Are you sure you want to delete <strong><?php e(h($group['title'])); ?></strong>?
				</td>
			</tr>
			<tr>
				<td>
					<label><span class="i"><input type="checkbox" name="unsubscribe" value="1" /></span> unsubscribe from all feeds in this group</label>
				</td>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Delete<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>