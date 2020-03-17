<?php $this->render('page/header');?>
<h1>Extras</h1>

<?php

include(FIREWALL_ROOT.'app/data/feedlet.php');

// build curl options
$curl_options	= "-L -s --user-agent 'Fever Refresh Cron'";
$refresh_url 	= $this->vars['paths']['full'].'/?refresh';
if (strpos($refresh_url, 'https') === 0)
{
	$curl_options .= ' -k';
}
$curl_options .= " '{$refresh_url}'";


?>

<h2>Nobody likes to wait</h2>

<p>Fever can refresh stale feeds on each visit or you can automate this process using cron. Configuring your crontab file will vary from host-to-host--some even offer a web-based GUI to simplify the process. In any case you'll want cron to perform the following command every fifteen minutes:</p>

<pre><code>curl <?php e($curl_options); ?></code></pre>

<p>If you were manually editing your crontab file you would add the following line:</p>

<pre><code>00,15,30,45 * * * * curl <?php e($curl_options); ?></code></pre>

<p class="btn-row"><a class="btn text default" href="./">Done<i></i></a></p>

<?php $this->render('page/footer');?>