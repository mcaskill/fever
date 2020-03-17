<?php
if (!defined('FIREWALL_ROOT')) { header('Location:../'); }

include(FIREWALL_ROOT.'app/libs/util.php');
include(FIREWALL_ROOT.'app/libs/apptivator.php');

$Apptivator = new Apptivator();

include(FIREWALL_ROOT.'app/libs/request.php');

$Apptivator->route();