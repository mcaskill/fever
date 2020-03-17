<div id="dialog-container">
	<div id="dialog-window">
		<div id="dialog" class="dialog"></div><!-- #dialog.dialog -->
	</div><!-- #dialog-window -->
</div><!-- #dialog-container -->

<div id="menu-container" onclick="Fever.dismissMenu();">
	<div id="menu">
		<ul id="menu-options"></ul><!-- #menu-options -->
		<div id="menu-shadow"></div><!-- #menu-shadow -->
	</div><!-- #menu -->
</div><!-- #menu-container -->

<div id="help-container">
	<div id="help">
		<dl id="help-text"></dl><!-- #help-text -->
		<div id="help-dangle"></div>
	</div><!-- #help -->
</div><!-- #help-container -->

<div id="inline-help">
<?php if($this->is_logged_in() || !$this->is_installed()): ?>
<?php include(FIREWALL_ROOT.'app/data/help.php'); ?>
<?php endif; ?>
</div><!-- #inline-help -->
