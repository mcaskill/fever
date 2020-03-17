<?php
$link = $this->get_one('links', $this->prepare_sql('`id` = ?', $_GET['link_id']));
?>
<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="add-to-blacklist" />

	<h1>Add to Blacklist</h1>
	<fieldset>
		<table>
			<tr>
				<th class="proto">Link</th>
				<td class="proto"><span class="w"><input type="text" name="link" value="<?php e(prevent_xss($link['url'])); ?>" /></span></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<label><input type="radio" name="specificity" value="0" checked="checked" /> exact link</label> &nbsp;
					<label><input type="radio" name="specificity" value="1" /> any link from this domain</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Blacklist<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>