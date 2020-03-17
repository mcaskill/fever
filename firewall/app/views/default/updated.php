<?php $this->render('page/header');?>
<h1>
<?php if ($this->vars['last_updated_manually']):?>
	Is it hot in here?
<?php else:?>
	While you were out
<?php endif; ?>
</h1>
<h2>Fever <?php e($this->vars['last_updated_manually'] ? 'has been' : 'was')?> updated to <?php e($this->formatted_version()); ?> successfully.</h2>
<p class="text">Check the <a href="http://feedafever.com/todone.txt">changelog</a>.</p>
<p class="btn-row"><a class="btn text default" href="<?php e(errors_url('./')); ?>">Okay<i></i></a></p>
<?php $this->render('page/footer');?>