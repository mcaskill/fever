<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" id="viewport" content="width=device-width">
<title>Fever&deg;</title>
<meta http-equiv="X-UA-Compatible" content="chrome=1" />
<link rel="apple-touch-icon" href="firewall/app/images/webclip.png" />
<link rel="apple-touch-icon" sizes="72x72" href="firewall/app/images/webclip-ipad.png" />
<link rel="apple-touch-icon" sizes="114x114" href="firewall/app/images/webclip@2x.png" />
<link rel="apple-touch-icon" sizes="144x144" href="firewall/app/images/webclip-ipad@2x.png" />
<link rel="shortcut icon" type="image/png" href="firewall/app/images/favicon.png" />
<?php if($this->prefs['share']):?>
<link type="application/rss+xml" rel="alternate" title="Saved Items" href="./?rss=saved" />
<?php endif;?>
<link rel="stylesheet" type="text/css" href="firewall/app/views/default/styles/page.css?v=<?php e($this->version);?>" />
<?php e($this->override_link());?>
<script type="text/javascript" src="firewall/app/views/default/scripts/fever.js?v=<?php e($this->version);?>"></script>
</head>
<body>
<div class="page">
	<div class="box">
		<div class="content">