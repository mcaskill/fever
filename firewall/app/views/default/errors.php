<?php $this->render('page/header');?>

<h1>Uh-oh</h1>
<h2><?php e($this->app_name); ?> encountered the following error(s)</h2>

<?php if(!empty($this->errors['note'])):?>
<div class="error-note"><?php e($this->errors['note']); ?></div>
<?php endif;?>

<?php if(!empty($this->errors['list'])):?>
<ul class="errors">
<?php foreach($this->errors['list'] as $error):?>
	<li><?php e($error);?></li>
<?php endforeach;?>
</ul>
<?php endif;?>

<p class="btn-row"><a class="btn text default" href="<?php e(errors_url('./'));?>" onclick="history.back();return false;">Back<i></i></a></p>

<?php $this->render('page/footer');?>