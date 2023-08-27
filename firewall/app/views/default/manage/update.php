<?php if ($this->update_available()): ?>
	<div id="update" class="update-available">
		<div class="content">
			Update available
		</div>
		<?php if ($update_url = $this->update_url()): ?>
			<a class="btn text" href="<?php e($update_url); ?>" rel="noopener"<?php e(($this->prefs['new_window']) ? ' target="_blank"' : '')?>>view update<i></i></a>
		<?php else: ?>
			<a class="btn text" href="./?update">update now<i></i></a>
		<?php endif; ?>
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