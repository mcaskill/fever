<?php
$this->relationships();
$html	= '';
foreach($this->feeds as $feed)
{
	if ($feed['id'] == 0) { continue; }
	$selected = ($feed['is_spark']) ? ' selected="selected"' : '';
	$html .= '<option value="'.$feed['id'].'"'.$selected.'>'.$this->title($feed).'</option>';
}

?>
<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="edit-sparks" />

	<h1>Edit Sparks</h1>
	<fieldset>
		<table>
			<tr>
				<th class="proto">Feed(s) <span class="btn help" onmouseover="Fever.displayHelp(this, 'multiple');">?</span></th>
				<td>
					<span class="w"><select name="feed_ids[]" multiple="multiple"><?php e($html); ?></select></span>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Save<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>