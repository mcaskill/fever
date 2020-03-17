<?php $this->render('page/header');?>
<?php $this->relationships();?>
<script type="text/javascript" language="javascript">
// <![CDATA[
<?php $this->render('reader/js-initial');?>

// ]]>
</script>

<div class="screen<?php e(!$this->prefs['ui']['show_read']?' hide-read':''); ?>" id="screen-0">
	<?php $this->render('reader/groups'); ?>
</div>

<div class="screen" id="screen-1">
	<?php // $this->render('reader/feeds'); ?>
</div>

<div class="screen" id="screen-2">
	<?php // $this->render('reader/items'); ?>
</div>

<div class="screen" id="screen-3">
	<?php // $this->render('reader/item'); ?>
</div>

<iframe src="./?refresh" id="refresh" name="refresh"></iframe>

<?php $this->render('page/footer'); ?>