<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?flush'));?>" onsubmit="return confirmFlush();">
	<input type="hidden" name="action" value="flush" />
	<input type="hidden" name="confirm" value="0" />

	<h1>Flush</h1>
	<h2>Confirmation</h2>
	<p>This will delete everything except your groups, feeds, their settings, preferences and any saved items. It cannot be undone.</p>

	<table>
		<tr>
			<td class="proto">
				<button><span class="btn text">Flush<i></i></span></button>
				<label>
					<span class="i"><input type="checkbox" id="confirm-flush" name="confirm" value="1" /></span>
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

function confirmFlush()
{
	var confirm = one('#confirm-flush').checked;
	if (!confirm)
	{
		alert('You must check "confirm" to flush Fever.');
		return false;
	};
	return true;
};

// ]]>
</script>
<?php $this->render('page/footer');?>