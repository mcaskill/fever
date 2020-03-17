<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?unorphan'));?>" onsubmit="return confirmAction();">
	<input type="hidden" name="action" value="empty" />
	<input type="hidden" name="confirm" value="0" />

	<h1>Unorphan</h1>
	<h2>Confirmation</h2>
	<p>This will delete any orphaned items or links from Fever. It cannot be undone.</p>

	<table>
		<tr>
			<td class="proto">
				<button><span class="btn text">Unorphan<i></i></span></button>
				<label>
					<span class="i"><input type="checkbox" id="confirm-action" name="confirm" value="1" /></span>
					confirm
				</label>
			</td>
			<td class="btn-row">
				<a href="./" class="btn text default">Cancel<i></i></a>
			</td>
		</tr>
	</table>
</form>


<script type="text/javascript" language="JavaScript">
// <![CDATA[

function confirmAction()
{
	var confirm = one('#confirm-action').checked;
	if (!confirm)
	{
		alert('You must check "confirm" to delete orphan items and links from Fever.');
		return false;
	};
	return true;
};

// ]]>
</script>
<?php $this->render('page/footer');?>