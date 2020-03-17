<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="delete-sparks" />

	<h1>Unsubscribe from Sparks</h1>
	<fieldset>
		<table>
			<tr>
				<td>
					Are you sure you want to unsubscribe from all feeds in Sparks?
				</td>
			</tr>
			<tr>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Unsubscribe<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>