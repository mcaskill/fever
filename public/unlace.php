<?php
include('util.php');
rm('util.php');
if (file_exists('index.php'))	{ rm('index.php'); }
if (file_exists('firewall')) 	{ rm('firewall'); }
echo 'All application files have been removed from your server. You can now delete this directory.';