<?php onload($this->render('reader/js-reload', 1));?>
<!-- XHR FRAGMENT -->
<?php if (!isset($_GET['page'])):?>
<?php $this->render('reader/groups');?>
<!-- XHR FRAGMENT -->
<?php include($this->view_file('reader/feeds'));?>
<!-- XHR FRAGMENT -->
<?php include($this->view_file('reader/feeds-alpha'));?>
<!-- XHR FRAGMENT -->
<?php endif;?>
<?php if ($this->prefs['ui']['section']):?>
<?php $this->render('reader/items');?>
<?php else:?>
<?php $this->render('reader/links');?>
<?php endif;?>
