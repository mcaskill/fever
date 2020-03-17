<?php
if (isset($_GET['errors'])) { error_reporting(E_ALL); } else { error_reporting(0); }

define('FIREWALLED', true);
define('FIREWALL_ROOT', 'firewall/');
include(FIREWALL_ROOT.'app/index.php');