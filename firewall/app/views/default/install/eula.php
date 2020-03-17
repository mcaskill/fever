<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./'));?>" id="eula" onsubmit="return confirmEULA();">
	<input type="hidden" name="action" value="accept-eula" />
	<h1>Installation</h1>
	<h2>End User License Agreement</h2>
	<p>Thank you for purchasing Fever. To continue with installation you must accept the End User License Agreement.</p>

	<table>
		<tr>
			<td colspan="2">
				<span class="w"><textarea disabled="disabled" rows="13" cols="40"><?php include(FIREWALL_ROOT.'app/data/EULA.txt'); ?></textarea></span>
			</td>
		</tr>
		<tr>
			<td class="proto">
				<label><span class="i"><input type="checkbox" id="accept-eula" name="accept" value="true" /></span> I accept the Fever End User License Agreement.</label>
			</td>
			<td class="btn-row">
				<button><span class="btn text default">Continue<i></i></span></button>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript" language="JavaScript">
// <![CDATA[
function confirmEULA()
{
	var accept = document.getElementById('accept-eula').checked;
	if (!accept)
	{
		alert('To continue with installation you must accept the Fever End User License Agreement.');
		return false;
	};
	return true;
};
// ]]>
</script>
<?php $this->render('page/footer');?>