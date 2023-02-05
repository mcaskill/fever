<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Fever&deg;<?php if ($this->prefs['unread_counts'] && $this->total_unread):?> (<?php e($this->total_unread); ?>)<?php endif; ?></title>
<meta http-equiv="X-UA-Compatible" content="chrome=1" />
<link rel="apple-touch-icon" href="firewall/app/images/webclip.png" />
<link rel="apple-touch-icon" sizes="72x72" href="firewall/app/images/webclip-ipad.png" />
<link rel="apple-touch-icon" sizes="114x114" href="firewall/app/images/webclip@2x.png" />
<link rel="apple-touch-icon" sizes="144x144" href="firewall/app/images/webclip-ipad@2x.png" />
<meta name="viewport" id="viewport" content="width=1024" />
<link rel="shortcut icon" type="image/png" href="firewall/app/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="firewall/app/views/default/styles/reader.css?v=<?php e($this->version);?>" />
<?php e($this->override_link());?>
<script type="text/javascript" src="firewall/app/views/default/scripts/fever.js?v=<?php e($this->version);?>"></script>
<script type="text/javascript" src="firewall/app/views/default/scripts/reader.js?v=<?php e($this->version);?>"></script>
<script type="text/javascript" language="javascript">
// <![CDATA[
<?php $this->render('reader/js-initial');?>
// ]]>
</script>
</head>
<?php

$body_class = '';

if ($this->prefs['ui']['show_feeds'])
{
	$body_class .= ' show-feeds';
}

if (!$this->prefs['ui']['section'])
{
	$body_class .= ' hot';
}

/** /
if (!$this->prefs['unread_counts'])
{
	$body_class .= ' hide-unread-counts';
}
/**/

if ($this->prefs['layout'])
{
	$body_class .= ' fluid';
}


$body_class = trim($body_class);

if (!empty($body_class))
{
	$body_class = ' class="'.$body_class.'"';
}

?>
<body<?php e($body_class); ?>>
	<div id="top"></div>
	<div id="fixed">
		<div class="container">
			<div id="fever">
				<span id="logo"><img src="firewall/app/images/logo-fever.png" width="50" height="17" alt="fever&deg;" title="Fever <?php e($this->formatted_version()." (Using {$this->dbc->api})"); ?>" /></span>
				<span class="btn menu action" onclick="Fever.displayMenu(this, 'action');"></span>
			</div><!-- #fever -->