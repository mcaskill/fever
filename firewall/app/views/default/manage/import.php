<form method="post" action="<?php e(errors_url('./?manage'));?>" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import" />

	<h1>Import feeds from OPML</h1>

	<fieldset>
		<table>
			<tr>
				<td class="proto">
					<label class="btn text file">Choose OPML <input type="file" name="opml" /><i></i></label>
					<label>
						<span class="i"><input type="checkbox" type="checkbox" name="import_groups" value="1" checked="checked" /></span>
						import groups
					</label>
				</td>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Import<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>