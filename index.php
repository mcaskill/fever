<?php
error_reporting(E_ALL);
define('FIREWALL_ROOT', 'firewall/');
mkdir(FIREWALL_ROOT.'tmp');
include('util.php');
rm('util.php');
save_to_file(APP_NAME, FIREWALL_ROOT.'receipt.txt');
remote_copy(SOURCES_URL.'public/pclzip.lib.php', FIREWALL_ROOT.'tmp/pclzip.lib.php');
remote_copy(SOURCES_URL.'public/compatibilizer.zip', FIREWALL_ROOT.'tmp/compatibilizer.zip');
include(FIREWALL_ROOT.'tmp/pclzip.lib.php');
$archive = new PclZip(FIREWALL_ROOT.'tmp/compatibilizer.zip');
$archive->extract(PCLZIP_OPT_PATH, FIREWALL_ROOT, PCLZIP_OPT_REMOVE_PATH, 'compatibilizer');
rm(FIREWALL_ROOT.'tmp');
remote_copy(SOURCES_URL.'public/index.php', 'index.php');
header('Location:./');