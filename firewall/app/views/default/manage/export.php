<?php
$this->relationships();

$html = '';
foreach($this->groups as $group)
{
	// ignore the All supergroup
	if ($group['id'] == 0) { continue; }

	$selected = ($this->prefs['ui']['group_id'] == $group['id']) ? ' selected="selected"'  : '';
	$html .= '<option value="'.$group['id'].'"'.$selected.'>'.h($group['title']).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" onsubmit="Fever.dismissDialog(); return true;">
	<input type="hidden" name="action" value="export" />

	<h1>Export OPML</h1>
	<fieldset>

		<table>
			<tr>
				<td colspan="2">
					<label><input type="radio" name="with_groups" value="0" checked="checked" /> all groups</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="proto">
					<label>
						<input type="radio" name="with_groups" value="1" /> just the selected groups<br />
						<span class="w radio-inset"><select name="group_ids[]" multiple="multiple"><?php e($html); ?></select></span>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label class="radio-inset"><span class="i"><input type="checkbox" name="flatten" value="1" /></span> flatten groups</label> &nbsp;
					<label><span class="i"><input type="checkbox" name="include_sparks" value="1" /></span> include Sparks</label>
				</td>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Export<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>