<?php
if (!defined('FIREWALL_ROOT')) { header('Location:../'); }

include(FIREWALL_ROOT.'app/libs/util.php');
include(FIREWALL_ROOT.'app/libs/compatibilizer.php');

$Compatibilizer = new Compatibilizer();

include(FIREWALL_ROOT.'app/libs/request.php');

$Compatibilizer->route();