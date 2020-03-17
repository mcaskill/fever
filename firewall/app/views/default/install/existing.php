<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./'));?>" onsubmit="return confirmDelete();">
	<input type="hidden" name="action" value="database" />
	<input type="hidden" name="db_server" value="<?php e($this->db['server']); ?>" />
	<input type="hidden" name="db_database" value="<?php e($this->db['database']); ?>" />
	<input type="hidden" name="db_username" value="<?php e($this->db['username']); ?>" />
	<input type="hidden" name="db_password" value="<?php e($this->db['password']); ?>" />
	<input type="hidden" name="db_prefix" value="<?php e($this->db['prefix']); ?>" />

	<h1>Installation</h1>
	<h2>Quick Question</h2>

	<p>An existing Fever installation was found on this database. How would you like to proceed?</p>

	<table>
		<tr>
			<td><input id="db_option_1" type="radio" name="db_option" value="1" checked="checked" /></td>
			<th>
				<label for="db_option_1">create a new installation using an alternate table prefix</label>
				<span class="w"><input type="text" name="db_prefix_alt" value="<?php e($this->db['prefix']); ?>" /></span></label>
			</th>
		</tr>
		<tr>
			<td><input id="db_option_2" type="radio" name="db_option" value="2" /></td>
			<th>
				<label for="db_option_2">use/update the existing Fever installation</label>
			</th>
		</tr>
		<tr>
			<td><input id="db_option_3" type="radio" name="db_option" value="3" /></td>
			<th>
				<label for="db_option_3">
					delete and replace the existing Fever installation
					<span class="i"><input type="checkbox" name="db_confirm_delete" id="confirm-delete" /></span> confirm

				</label>
			</th>
		</tr>
		<tr>
			<td colspan="2" class="btn-row">
				<a class="btn text" href="./" onclick="history.back(); return false;">Back<i></i></a>
				<button><span class="btn text default">Proceed<i></i></span></button>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript" language="JavaScript">
// <![CDATA[

function confirmDelete()
{
	var deleted = one('#db_option_3').checked;
	var confirm = one('#confirm-delete').checked;
	if (deleted && !confirm)
	{
		alert('You must check "confirm" to delete the existing Fever installation.');
		return false;
	};
	return true;
};

// ]]>
</script>
<?php $this->render('page/footer');?>