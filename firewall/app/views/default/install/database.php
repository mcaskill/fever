<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./'));?>">
	<input type="hidden" name="action" value="database" />
	<h1>Installation</h1>
	<h2>Database Connection</h2>

	<p>Fever needs to know how to connect to your MySQL database.</p>

	<table>
		<tr>
			<th>Server</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'db-server');"></span></td>
			<td><span class="w"><input type="text" name="db_server" value="<?php e($this->db['server']); ?>" /></span></td>
		</tr>
		<tr>
			<th class="proto">Database name</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'db-database');"></span></td>
			<td class="proto"><span class="w"><input type="text" name="db_database" /></span></td>
		</tr>
		<tr>
			<th>Username</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'db-username');"></span></td>
			<td><span class="w"><input type="text" name="db_username" /></span></td>
		</tr>
		<tr>
			<th>Password</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'db-password');"></span></td>
			<td><span class="w"><input type="password" name="db_password" /></span></td>
		</tr>
		<tr>
			<th>Table prefix</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'db-prefix');"></span></td>
			<td><span class="w"><input type="text" name="db_prefix" value="<?php e($this->db['prefix']); ?>" /></span></td>
		</tr>
		<tr>
			<td colspan="3" class="btn-row"><button><span class="btn text default">Save<i></i></span></button></td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>