<?php $this->render('page/header');?>
<h1>Uh-oh</h1>
<h2>Fever encountered the following error(s)</h2>

<?php $this->render_errors(); ?>

<p class="btn-row"><a class="btn text default" href="<?php e(errors_url('./')); ?>" onclick="history.back(); return false;">Back<i></i></a></p>
<?php $this->render('page/footer');?>