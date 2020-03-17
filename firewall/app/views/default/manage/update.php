<?php if ($this->update_available()): ?>
	<div id="update" class="update-available">
		<div class="content">
			Update available
		</div>
		<a class="btn text" href="./?update">update now<i></i></a>
		<s></s>
	</div><!-- .update-available -->
<?php elseif (isset($_GET['updates'])):?>
	<div id="update" class="no-update-available" onclick="css(this,'display', 'none');">
		<div class="content">
			There are no updates available
		</div>
		<s></s>
	</div><!-- .update-available -->
<?php endif; ?>