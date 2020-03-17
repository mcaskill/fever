<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./'));?>">
	<input type="hidden" name="action" value="verify-key" />
	<h1>Installation</h1>
	<h2>Activation</h2>
	<p>Your Activation Key can be found in the email titled "Thank you for purchasing Fever" or by logging into the <a href="http://www.feedafever.com/account/">Fever Account Center</a>.</p>

	<table>
		<tr>
			<th class="proto">Activation key</th>
			<td class="proto"><span class="w"><input type="text" name="activation_key" /></span></td>
			<td class="btn-row"><button><span class="btn text default">Continue<i></i></span></button></td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>