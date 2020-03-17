<?php
$feed = $this->get_one('feeds', $this->prepare_sql('`id` = ?', $_GET['feed_id']));
$feed_auth = array
(
	'username' => '',
	'password' => ''
);
if ($feed['requires_auth'] && !empty($feed['auth']))
{
	list($feed_auth['username'], $feed_auth['password']) = explode(':', base64_decode($feed['auth']));
}
?>
<form method="post" action="<?php e(errors_url('./?manage'));?>" target="refresh" onsubmit="Fever.dismissDialog(); return true;">
	<input type="hidden" name="action" value="auth" />
	<input type="hidden" name="feed[id]" value="<?php e($feed['id']); ?>" />

	<h1>Authentication required</h1>
	<h2 title="<?php e(h($feed['url']));?>"><?php e($this->title($feed)); ?> requires a username and password.</h2>

	<fieldset>
		<table>
			<tr>
				<th>Username</th>
				<td colspan="2"><span class="w"><input type="text" name="feed[username]" value="<?php e($feed_auth['username']); ?>" autocomplete="off" /></span></td>
			</tr>
			<tr>
				<th class="proto">Password</th>
				<td class="proto"><span class="w"><input type="password" name="feed[password]" value="<?php e($feed_auth['password']); ?>" autocomplete="off" /></span></td>
				<td class="btn-row">
					<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
					<button><span class="btn text default">Authenticate<i></i></span></button>
				</td>
			</tr>
		</table>
	</fieldset>
</form>
