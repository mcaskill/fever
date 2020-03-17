<?php $this->render('page/header');?>
<form method="post" action="" id="login-form"><!-- action must remain empty -->
	<input type="hidden" name="action" value="login" />

	<h1>Not so fast, hotshot</h1>
	<h2>Login required</h2>

	<?php $this->render_errors(); ?>

	<table>
		<tr>
			<th>Email</th>
			<td colspan="2"><span class="w"><input type="email" id="email" name="email" value="<?php e((isset($_POST['email']))?prevent_xss($_POST['email']):'');?>" /></span></td>
		</tr>
		<tr>
			<th class="proto">Password</th>
			<td class="proto"><span class="w"><input type="password" name="password" /></span></td>
			<td class="btn-row">
				<button><span class="btn text default">Login<i></i></span></button>
				<span class="btn text" id="remind-me">Remind me<i></i></span>
			</td>
		</tr>
	</table>
</form>

<form method="post" action="" id="remind-form">
	<input type="hidden" name="action" value="remind" />

	<h1>Remind me</h1>
	<h2>Send password reminder email</h2>
	<p>
		Enter the email address used to administer Fever below and your password will be emailed to you shortly.
	</p>
	<table>
		<tr>
			<th>Email</th>
			<td class="proto"><span class="w"><input type="email" id="email-reminder" name="email" value="<?php e((isset($_POST['email']))?prevent_xss($_POST['email']):'');?>" /></span></td>
			<td class="btn-row">
				<button><span class="btn text default">Send<i></i></span></button>
				<span class="btn text" id="nevermind">Nevermind<i></i></span>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript" language="JavaScript">
// <![CDATA[

var email 			= one('#email');
var emailReminder	= one('#email-reminder');
email.onblur = function()
{
	emailReminder.value = this.value;
};
emailReminder.onblur = function()
{
	email.value = this.value;
};
one('#remind-me').onclick = function()
{
	css(one('#login-form'), 'display', 'none');
	css(one('#remind-form'), 'display', 'block');
	emailReminder.focus();
	emailReminder.select();
};
one('#nevermind').onclick = function()
{
	css(one('#remind-form'), 'display', 'none');
	css(one('#login-form'), 'display', 'block');
	email.focus();
	email.select();
};

// ]]>
</script>
<?php $this->render('page/footer');?>