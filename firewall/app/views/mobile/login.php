<?php $this->render('page/header');?>

	<div class="screen" id="login">
		<h1>Not so fast, hotshot.</h1>

		<form method="post" action="" id="login-form"><!-- action must remain empty -->
			<div class="box">
				<input type="hidden" name="action" value="login" />

				<fieldset>
						<label>Email <input type="email" id="email" name="email" value="<?php e((isset($_POST['email']))?prevent_xss($_POST['email']):'');?>" autocapitalize="off" /></label>
						<button class="btn text default" id="remind-btn">Remind</button>
				</fieldset>

				<fieldset>
					<label>Password <input type="password" name="password" autocapitalize="off" /></label>
					<button class="btn text default" id="login-btn">Login</button>
				</fieldset>
			</div>
		</form>

		<div class="footer">
			<?php $this->render('page/copyright'); ?>
		</div>

	</div>
<script type="text/javascript">
var form = one('#login-form');
form.clickAction = 'login';
one('#login-btn').ontouchend = function()  { this.form.clickAction = 'login'; };
one('#remind-btn').ontouchend = function() { this.form.clickAction = 'remind'; };
form.onsubmit = function()
{
	if (this.password.value.length == 0) this.clickAction = 'remind';
	this.action.value = this.clickAction;

	window.scrollTo(0,99999);
	this.focus();
};
</script>
<?php $this->render('page/footer');?>