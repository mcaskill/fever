<?php

if (!preg_match('#/reboot\.php$#', __FILE__))
{
	exit('This file must be renamed to <code>reboot.php</code> before using and should be renamed or deleted after use.');
}

if (!file_exists('reboot.php'))
{
	// that's this file, it must exist. DANGER! DANGER!
	// something is wrong with this server's working or
	// include path
	exit('This server failed a basic check intended to prevent dataloss.');
}

define('UNLACE', true);
include('boot.php');
error_reporting(E_ALL);

if (!file_exists('util.php'))
{
	// save a copy of our utility funcs that the new reboot.php can use
	$boot	= file_get_contents('boot.php');
	$parts	= explode('// SNIP ', $boot, 3);
	save_to_file($parts[1], 'util.php');
}

define('FIREWALL_ROOT', 'firewall/');
mkdir(FIREWALL_ROOT.'tmp');
remote_copy(SOURCES_URL.'public/pclzip.lib.php', FIREWALL_ROOT.'tmp/pclzip.lib.php');
remote_copy(SOURCES_URL.'public/apptivator.zip', FIREWALL_ROOT.'tmp/apptivator.zip');
rm(FIREWALL_ROOT.'app');
rm(FIREWALL_ROOT.'config');
include(FIREWALL_ROOT.'tmp/pclzip.lib.php');
$archive = new PclZip(FIREWALL_ROOT.'tmp/apptivator.zip');
$archive->extract(PCLZIP_OPT_PATH, FIREWALL_ROOT, PCLZIP_OPT_REMOVE_PATH, 'apptivator');
rm(FIREWALL_ROOT.'tmp');
remote_copy(SOURCES_URL.'public/index.php', 'index.php');
rm('util.php');
header('Location:./');