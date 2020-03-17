<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?empty'));?>" onsubmit="return confirmEmpty();">
	<input type="hidden" name="action" value="empty" />
	<input type="hidden" name="confirm" value="0" />

	<h1>Empty</h1>
	<h2>Confirmation</h2>
	<p>This will unsubscribe from all feeds and delete all groups. It cannot be undone. If you haven't already you might want to export your feeds before emptying.</p>

	<table>
		<tr>
			<td class="proto">
				<button><span class="btn text">Empty<i></i></span></button>
				<label>
					<span class="i"><input type="checkbox" id="confirm-empty" name="confirm" value="1" /></span>
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

function confirmEmpty()
{
	var confirm = one('#confirm-empty').checked;
	if (!confirm)
	{
		alert('You must check "confirm" to empty Fever.');
		return false;
	};
	return true;
};

// ]]>
</script>
<?php $this->render('page/footer');?>