<?php

$base 	= 'http://feedafever.com/';

$paths 	= $this->install_paths();
$compat	= checksum($paths['trim']);
$url	= "{$base}licenses/add?domain={$paths['trim']}&amp;compat={$compat}";
$eula	= "{$base}eula";

?>

<?php $this->render('page/header');?>

<form action="<?php e(errors_url('./'));?>" method="post">
	<h1>Activation</h1>
	<h2>Still need to purchase <?php e($this->app_name); ?>?</h2>
	
	<p> 
	This <a href="<?php e($url);?>">handy link</a> will pre-populate the license 
	form on feedafever.com. Just come back here once you're sorted. <strong>Already have your Activation Key? Enter it below.</strong>
	</p>

	<table>
		<tr>
			<th>Domain Name</th>
			<td><span class="w"><input type="text" readonly="readonly" value="<?php e($paths['trim']); ?>" /></span></td>
		</tr>
		<tr>
			<th class="proto">Compatibility Confirmation</th>
			<td><span class="w"><input type="text" readonly="readonly" value="<?php e($compat); ?>" /></span></td>
		</tr>
		<tr>
			<th>Activation Key</th>
			<td><span class="w"><input type="text" name="activation_key" /></span></td>
		</tr>
	</table>
	<table>
		<tr>
			<td>
				<label><input type="checkbox" name="accept_eula" /> I accept the <?php e($this->app_name); ?> <a href="<?php e($eula); ?>">End User License Agreement</a></label>
			</td>
			<td class="btn-row">
				<button><span class="btn text default">Activate<i></i></span></button>
			</td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>