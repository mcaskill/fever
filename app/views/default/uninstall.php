<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?uninstall'));?>" onsubmit="return confirmUninstall();">
	<input type="hidden" name="action" value="uninstall" />
	<input type="hidden" name="confirm" value="0" />

	<h1>Uninstall</h1>
	<h2>Confirmation</h2>
	<p>Are you sure you want to uninstall Fever? This action cannot be undone. If you haven't already you should export your feeds before uninstalling.</p>

	<table>
		<tr>
			<td class="proto">
				<button><span class="btn text">Uninstall<i></i></span></button>
				<label>
					<span class="i"><input type="checkbox" id="confirm-uninstall" name="confirm" value="1" /></span>
					confirm
				</label>
			</td>
			<td class="btn-row">
				<a href="./" class="btn text default">Cancel<i></i></a>
				<span class="btn text" onclick="Fever.addRemoteDialog('./?manage=export');">Export<i></i></span>
			</td>
		</tr>
	</table>
</form>


<script type="text/javascript" language="JavaScript">
// <![CDATA[

function confirmUninstall()
{
	var confirm = one('#confirm-uninstall').checked;
	if (!confirm)
	{
		alert('You must check "confirm" to uninstall Fever.');
		return false;
	};
	return true;
};

// ]]>
</script>
<?php $this->render('page/footer');?>