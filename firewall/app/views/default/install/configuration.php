<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./'));?>">
	<input type="hidden" name="action" value="install" />
	<input type="hidden" name="activation_key" value="<?php e(prevent_xss($_POST['activation_key'])); ?>" />
	<h1>Installation</h1>
	<h2>Create a login</h2>

	<table>
		<tr>
			<th>Email</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'email');"></span></td>
			<td colspan="2"><span class="w"><input type="email" name="email" /></span></td>
		</tr>
		<tr>
			<th>Password</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'password');"></span></td>
			<td colspan="2"><span class="w"><input type="password" name="password" /></span></td>
		</tr>
		<tr>
			<th class="proto">Temperature</th>
			<td><span class="btn help" onmouseover="Fever.displayHelp(this, 'temperature');"></span></td>
			<td class="proto">
				<label><input type="radio" name="use_celsius" value="0" checked="checked" /> fahrenheit</label>
				<label><input type="radio" name="use_celsius" value="1" /> celsius</label>
			</td>
			<td class="btn-row"><button><span class="btn text default">Install<i></i></span></button></td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>