<?php
$feed = $this->get_one('feeds', $this->prepare_sql('`id` = ?', $_GET['feed_id']));
?>
<form method="post" action="<?php e(errors_url('./?manage'));?>">
	<input type="hidden" name="action" value="delete-feed" />
	<input type="hidden" name="id" value="<?php e($feed['id']); ?>" />

	<h1>Unsubscribe</h1>
	<fieldset>
		<p>Are you sure you want to unsubscribe from <strong><?php e(widont($this->title($feed))); ?></strong>?</p>

		<table>
			<tr>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Unsubscribe<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>