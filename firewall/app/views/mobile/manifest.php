<?php header('Content-type:text/cache-manifest');
?>CACHE MANIFEST

?favicons&<?php e($this->get_col('last_cached_on_time', 'favicons', '1 ORDER BY `last_cached_on_time` DESC'));?>

firewall/app/views/mobile/styles/iphone.css?v=<?php e($this->version);?>

firewall/app/views/mobile/scripts/fever.js?v=<?php e($this->version);?>

firewall/app/views/mobile/scripts/iphone.js?v=<?php e($this->version);?>

<?php

// could not get this working in mobile or desktop safari

$images_dir = 'firewall/app/views/mobile/styles/images/';
if ($dir = opendir($images_dir))
{
	while (($image = readdir($dir)) !== false)
	{
		if (m('#^\.#', $image, $m))
		{
			continue;
		}
		e("{$images_dir}{$image}?v={$this->version}\n");
	}
	closedir($dir);
}


?>