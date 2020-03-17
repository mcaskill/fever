<?php e('<?xml version="1.0" encoding="utf-8"?'.">\n");?>
<rss version="2.0">
<channel>
<title><?php e($this->vars['title']);?></title>
<link><?php e($this->vars['base_url']);?></link>
<description><?php e($this->vars['description']);?></description>
<generator>Shaun Inman&#8217;s Fever</generator>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<?php if(!empty($this->vars['items'])):?>
<?php foreach($this->vars['items'] as $item):?>
<item>
	<title><![CDATA[<?php e($item['title']);?>]]></title>
	<description><![CDATA[<?php e($item['description']);?>]]></description>
	<link><?php e($item['link']);?></link>
	<guid isPermaLink="false"><?php e($item['guid']);?></guid>
	<pubDate><?php e($item['pub_date']);?></pubDate>
</item>
<?php endforeach?>
<?php endif?>
</channel>
</rss>