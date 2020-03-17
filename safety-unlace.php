<?php

if (!preg_match('#/unlace\.php$#', __FILE__))
{
	exit('This file must be renamed to <code>unlace.php</code> before using and should be renamed or deleted after use.');
}

if (!file_exists('unlace.php'))
{
	// that's this file, it must exist. DANGER! DANGER!
	// something is wrong with this server's working or
	// include path
	exit('This server failed a basic check intended to prevent dataloss.');
}

define('UNLACE', true);
include('boot.php');

if (!file_exists('util.php'))
{
	// save a copy of our utility funcs that the new unlace.php can use
	$boot	= file_get_contents('boot.php');
	$parts	= explode('// SNIP ', $boot, 3);
	save_to_file($parts[1], 'util.php');
}

remote_copy(SOURCES_URL.'public/unlace.php', 'index.php');
header('Location:./');