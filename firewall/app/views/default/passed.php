<?php

if (!file_exists(FIREWALL_ROOT.'receipt_db.php')) { header('Location:./'); }
$paths 	= $this->install_paths();
$compat	= checksum($paths['trim']);
$url	= "http://feedafever.com/licenses/add?domain={$paths['trim']}&amp;compat={$compat}";

# Todo: decouple app name/slogan
?>
<script type="text/javascript">
parent.allChecksComplete('<strong>Congratulations, this server is compatible!</strong> <a href="<?php e($url); ?>">Feed it Fever!</a> The next time you visit (or <a href="./">reload</a>) this url Fever will be ready to activate and install. What are you waiting for?');
</script>

ALL CHECKS DONE AND PASSED.

<?php $this->apptivate(); ?>