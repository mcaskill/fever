<?php

$htaccess_content	= '';
$htaccess_path 		= '.htaccess';
if (file_exists($htaccess_path))
{
	$htaccess_content = file_get_contents($htaccess_path);
}

// don't add the fix more than once
$comment = '# Potential fix for broken flush()';
if (!in($htaccess_content, $comment))
{
	$htaccess_content .= <<<HTACCESS


<IfModule mod_rewrite.c>
	{$comment}.
	RewriteEngine on
	RewriteCond %{QUERY_STRING} ^refresh.*$
	RewriteRule (.*) $1 [E=no-gzip:1]
</IfModule>

HTACCESS;

	save_to_file($htaccess_content, $htaccess_path);	
}

?>
<script type="text/javascript">
parent.flushFixComplete();
</script>

FLUSH FIX DONE.