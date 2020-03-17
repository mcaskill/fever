<?php $paths = $this->install_paths(); ?>
var __feedlet =
{
	fever : '<?php e($paths['full'])?>/',
	stripProtocol : function(url)
	{
		return url.replace(/^(https?|feed):\/\//, '');
	},
	getProtocol : function(url)
	{
		return url.replace(/([^:]+):\/\/.*/, '$1');
	},
	sniff : function()
	{
		var pageTitle	= document.title;
		var url			= window.location.href;
		var feeds 		= [];

		// this is a feed!
		if (document.xmlVersion || url.match(/^feed:\/\//i))
		{
			feeds.push(
			{
				title 	: pageTitle,
				href	: url,
				type	: 'feed'
			});
		}
		else
		{
			// check for links for feeds
			var links = document.getElementsByTagName('link');
			for (var i=0; i<links.length; i++)
			{
				var link = links[i];
				if (link.href &&
					link.rel &&
					link.type &&
					link.rel.match(/.*alternate.*/i) &&
					link.type.match(/^application\/(rss|atom)\+xml$/i)
					)
				{
					feeds.push(
					{
						title 	: link.title ? link.title : link.type.replace(/^application\/(rss|atom)\+xml$/i, '$1'),
						href	: link.href,
						type	: 'link'
					});
				};
			};

			var tmp = '';

			// also check anchors for feeds
			var unique 	= [];
			var anchors	= document.getElementsByTagName('a');
			var div = document.createElement('div');
			for (var j=0; j<anchors.length; j++)
			{
				var a = anchors[j];
				var str = a.href + ' ' + a.title + ' ' + a.innerHTML + ' ' + a.rel + ' ' + a.id;
				str = str.replace(/ class="[^"]+"/, ''); // fix for Feedlet on Wired.com which has a ton of feed- prefixed class names
				if (str.match(/\b(rss|atom|feed|xml|syndicate)\b/i))
				{
					tmp += str + '\n';

					// sniff out known web-based reader links
					var href = a.href.replace(/https?:\/\/(www\.)?(netvibes|bloglines)\.com\/(subscribe\.php\?url=|sub\/)(.+$)/i, '$4');
					if (this.arrayHas(unique, href))
					{
						continue;
					};

					// sniff out title
					var title = a.title;
					if (title.match(/^\s*$/))
					{
						div.innerHTML = a.innerHTML;

						var imgs = div.getElementsByTagName('img');
						for (var k=0; k<imgs.length; k++)
						{
							var img = imgs[k];
							if (title.match(/^\s*$/))
							{
								title = img.alt;
							};
						};

						if (title.match(/^\s*$/))
						{

							title = div.innerHTML.replace(/<noscript>.*<\/noscript>/, ''); // WIRED!
							title = title.replace(/(<[^>]+>|&nbsp;|^\s*|\s*$)/gi, '');
						};
					};

					unique.push(href);
					feeds.push(
					{
						title 	: title,
						href	: href,
						type	: 'a'
					});
				};
			};
		};

		var configUrl = this.fever + '?feedlet';
		configUrl += '&url=' + encodeURIComponent(this.stripProtocol(url));
		configUrl += '&protocol=' + encodeURIComponent(this.getProtocol(url));
		configUrl += '&title=' + encodeURIComponent(pageTitle);

		for (var n=0; n<feeds.length; n++)
		{
			var feed = feeds[n];
			configUrl += '&feeds['+n+'][href]=' + encodeURIComponent(this.stripProtocol(feed.href));
			configUrl += '&feeds['+n+'][protocol]=' + encodeURIComponent(this.getProtocol(feed.href));
			configUrl += '&feeds['+n+'][title]=' + encodeURIComponent(feed.title);
			configUrl += '&feeds['+n+'][type]=' + encodeURIComponent(feed.type);
		};

		// alert(configUrl);
		window.location.href = configUrl;
	},

	// support
	arrayHas : function(anArray, value)
	{
		for (var i=0; i<anArray.length; i++)
		{
			if (anArray[i] == value)
			{
				return true;
			};
		}
		return false;
	}
};
__feedlet.sniff();