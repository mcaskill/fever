		</div>

		<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b><s><s></s></s></s>
	</div>
	<div class="footer">
		<a href="http://feedafever.com/" id="logo" title="<?php e("Using {$this->dbc->api}"); ?>"><img src="firewall/app/images/logo-fever.png" alt="Fever" width="50" height="17" /></a>
		<?php $this->render('page/copyright'); ?>
	</div>
</div>
<?php $this->render('ui');?>
<script type="text/javascript" language="javascript">
// <![CDATA[
Fever.onload();
// ]]>
</script>
<?php debug_flush(); ?>
</body>
</html>