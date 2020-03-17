<?php
if (!defined('FIREWALL_ROOT')) { header('Location:../'); }
header('Content-Type: text/html; charset=utf-8');

include(FIREWALL_ROOT.'app/libs/util.php');

if (isset($_SERVER['HTTP_REFERER']) && m('#[\?&]errors\b#', $_SERVER['HTTP_REFERER'], $m))
{
	error_reporting(E_ALL);
}

include(FIREWALL_ROOT.'app/libs/SIDB423.php');
include(FIREWALL_ROOT.'app/libs/fever.php');
include(FIREWALL_ROOT.'config/db.php');
include(FIREWALL_ROOT.'config/key.php');

$Fever = new Fever();
include(FIREWALL_ROOT.'app/libs/request.php');
$Fever->route();