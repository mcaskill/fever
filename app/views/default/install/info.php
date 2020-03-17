<?php $this->render('page/header');?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import" />

	<h1>Get started</h1>

	<?php $show_opml_msg = true; ?>
	<?php include(FIREWALL_ROOT.'app/data/feedlet.php'); ?>

	<h2>Chop-chop!</h2>

	<p>If you have an OPML exported from your previous feed reader
	that you would like to import, choose it now.</p>

	<table>
		<tr>
			<td class="proto">
				<label class="btn text file">Choose OPML <input type="file" name="opml" /><i></i></label>
				<label><span class="i"><input type="checkbox" type="checkbox" name="import_groups" value="1" checked="checked" /></span> import groups</label>
			</td>
			<td class="btn-row"><button><span class="btn text default">Continue<i></i></span></button></td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>