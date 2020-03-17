<?php $this->render('page/header');?>
<h1>Blacklist</h1>
<h2>Prevent specific urls from heating up</h2>
<form action="<?php e(errors_url('./?blacklist'));?>" method="post">
	<p>Place each exact url or regular expression on its own line. Regular expressions should use <code>#</code> as a delimiter and are case-insensitive.</p>
	<table>
		<tr>
			<td><span class="w"><textarea rows="8" cols="40" name="blacklist"><?php e($this->prefs['blacklist']); ?></textarea></span></td>
		</tr>
		<tr>
			<td class="btn-row">
				<a href="./" class="btn text">Cancel<i></i></a>
				<button><span class="btn text default">Save and Filter<i></i></span></button>
			</td>
		</tr>
	</table>
</form>
<?php $this->render('page/footer');?>