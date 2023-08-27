Fever.Reader =
{
	event				: null, // shame

	lastRefreshedOnTime	: 0,
	lastCachedOnTime	: 0,
	lastRenderedOnTime	: 0,
	totalFeeds			: 0,
	totalItems			: 0,
	totalUnread			: 0,
	autoRead			: 1,
	autoReadEnabled 	: 1,
	autoReload			: 1,
	toggleClick			: 1,

	page				: 1,
	pageMaxed			: false,
	pageY				: 0,
	pageLoading			: false,

	ui :
	{
		section		: 0,
		previous	: 0,
		groupId		: 0,
		feedId		: 0,
		search		: '',
		hasFocus	: 'groups',

		hotStart	: 0,
		hotRange	: 7,

		showFeeds 	: 1,
		showRead	: 0
	},

	unreadCounts	: 0,
	newWindow		: 1,

	groupIdsByFeedId : {},
	sparksFeedIds : [],

	reload : function()
	{
		this.loadRemote();
	},
	loadRemote : function()
	{
		var toPHP = function(str) { return str.replace(/([A-Z])/, '_$1').toLowerCase(); };
		var url	= './?xhr';

		if (arguments.length)
		{
			var ui	= arguments[0].split(/\s+/);
			for (var i = 0; i < ui.length; i++)
			{
				var prop = ui[i];
				url += '&ui[' + toPHP(prop) + ']=' + this.ui[prop];
			};
		};

		this.onContentRequested();
		XHR.get(url, [one('#RAM'), one('#groups'), one('#feeds-scroller'), one('#feeds-alpha'), one('#content-container')], {
			beforeInsert 	: function(data)
			{
				Fever.Reader.autoReadEnabled = false;
			},
			afterInsert		: function(data)
			{
				Fever.Reader.onContentInserted();
				// give the browser a moment to load the updated values from "RAM"
				// TODO: is this separation and delay really necessary?
				window.setTimeout(function(){ Fever.Reader.onContentLoaded(); }, 50);
			}
		});

		return false;
	},
	loadHot : function(hotStart, hotRange)
	{
		this.ui.hotStart = hotStart;
		this.ui.hotRange = hotRange;
		return this.loadRemote('hotStart hotRange');
	},
	loadSection : function(section)
	{
		one('#groups-scroller').scrollTop = 0;
		this.groupsScrollTop = 0;

		this.ui.hasFocus	= 'groups';
		this.ui.section		= section;
		this.ui.feedId		= 0;
		return this.loadRemote('section search feedId');
	},
	loadGroup : function(groupId)
	{
		if (groupId == 0)
		{
			one('#groups-scroller').scrollTop = 0;
			this.groupsScrollTop = 0;
		};
		this.ui.hasFocus 	= 'groups';
		this.ui.section 	= 1;
		this.ui.feedId 		= 0;
		this.ui.groupId 	= groupId;
		return this.loadRemote('section groupId feedId hasFocus');
	},
	loadFeed : function(feedId)
	{
		// if hot or saved
		if (this.ui.section == 0 || (this.ui.section == 2 && arguments.length == 2))
		{
			// determine which section to send user to, sparks or kindling
			this.ui.section = (this.sparksFeedIds.search(feedId) != -1) ? 3 : 1;
			// change to the Kindling supergroup if not in the active feed
			this.ui.groupId = (this.groupIdsByFeedId[feedId].search(this.ui.groupId) == -1) ? 0 : this.ui.groupId;
			this.ui.showRead = 1;
		};
		this.ui.hasFocus 	= 'feeds';
		this.ui.showFeeds	= 1;
		this.ui.feedId 		= feedId;
		return this.loadRemote('section groupId feedId showFeeds showRead hasFocus');
	},
	loadNextPage : function()
	{
		if (!this.pageMaxed && !this.pageLoading)
		{
			this.pageLoading = true;
			this.page++;

			var url = './?xhr&page=' + this.page;

			this.onContentRequested();
			XHR.get(url, null, function(data)
			{
				// alert(data.responseText);

				// add to existing content
				var tmp = document.createElement('div');
				tmp.innerHTML = data.responseText;

				var content = one('#content-container');
				while (tmp.childNodes.length > 0)
				{
					var child = tmp.removeChild(tmp.firstChild);
					if (child.id && document.getElementById(child.id))
					{
						// skip duplicate items/links
						continue;
					};
					// alert(child.innerHTML);
					content.appendChild(child);
				};
				Fever.Reader.onContentInserted();
				Fever.Reader.onContentLoaded();
			});
			return false;
		};
	},
	loadNextUnread : function()
	{
		if (!this.pageLoading)
		{
			var nextUnread = one('#feeds-scroller a.unread.proper, #groups li.droppable a.unread');
			if (nextUnread)
			{
				var id = nextUnread.className.replace(/^.*\b(feed|group)-([0-9]+)\b.*/, '$2');
				if (this.ui.showFeeds && nextUnread.className.indexOf('feed') != -1)
				{
					this.loadFeed(id);
				}
				else
				{
					this.loadGroup(id);
				};
				return true;
			};
		};
		return false;
	},

	groupsScrollTop : 0,
	feedsScrollTop : 0,
	onContentRequested : function()
	{
		var feedsScroller = one('#feeds-scroller');
		if (feedsScroller)
		{
			this.feedsScrollTop = feedsScroller.scrollTop;
		};
		var groupsScroller = one('#groups-scroller');
		if (groupsScroller)
		{
			this.groupsScrollTop = groupsScroller.scrollTop;
		};
		addClass(one('#fever'), 'loading');
	},
	onContentInserted : function()
	{
		var body = one('body');
		if (this.ui.showFeeds)
		{
			addClass(body, 'show-feeds');
		}
		else
		{
			removeClass(body, 'show-feeds');
		};

		if (this.ui.section == 0)
		{
			addClass(body, 'hot');
		}
		else
		{
			removeClass(body, 'hot');
		};
		removeClass(one('#fever'), 'loading');
	},
	onContentLoaded : function()
	{
		this.lastRenderedOnTime = (new Date).getTime();
		this.makeFeedsDraggable();
		this.updateBadge();

		if (this.page == 1 && !this.pageMaxed)
		{
			this.scrollToTop();
		}
		else
		{
			this.autoReadEnabled = true;
			this.refocus();
			// alert('refocus(): onContentLoaded()');
		};
		this.redraw();
		this.armSearch();
		this.armLinks();

		if (this.ui.showFeeds && this.ui.section != 0)
		{
			// preseve original scrollTop
			one('#groups-scroller').scrollTop = this.groupsScrollTop;
			one('#feeds-scroller').scrollTop = this.feedsScrollTop;
			this.scrollToFeed(this.ui.feedId);
		};
	},
	onSubContentLoaded : function()
	{
		removeClass(one('#fever'), 'loading');
		this.armLinks();
	},
	onLastPage : function()
	{
		this.pageMaxed = true;
	},
	onItemsLoaded : function()
	{
		if (!Fever.isIPad || this.ui.section == 2 || this.ui.section == 4) return;

		var cc = one('#content-container');
		var inline = one('#inline-read');

		if (!inline)
		{
			inline = document.createElement('div');
			inline.id = 'inline-read';
			inline.onclick = function()
			{
				Fever.Reader.markActiveAsRead();
				window.scrollTo(0,0);
			};
			inline.innerHTML = 'Mark '+this.ui.hasFocus.replace(/s$/,'')+' as read<s></s>';
		}
		else
		{
			cc.removeChild(inline);
		};

		cc.appendChild(inline);
	},

	padContent : function()
	{
		// TODO: wrap head around asynchronous state changes
		var itemOrLink = last('#content-container div.item, #content-container div.link');
		if (this.pageMaxed)
		{
			// update padding
			itemOrLink.style.marginBottom = '800px';
		}
		else
		{
			// remove padding
			itemOrLink.style.marginBottom = '4px';
		};
	},

	toggleFeeds : function()
	{
		this.ui.showFeeds = (this.ui.showFeeds) ? 0 : 1;
		return this.loadRemote('showFeeds');
	},
	toggleUnread : function()
	{
		this.ui.showRead = (this.ui.showRead) ? 0 : 1;
		return this.loadRemote('showRead');
	},

	iframe : function(url)
	{
		one('#refresh').src = u(url);
	},
	refresh : function()
	{
		var force = (arguments.length && arguments[0]) ? '&force' : '';
		this.iframe('./?refresh' + force + '&' + (new Date).getTime());
		return false;
	},
	refreshFeed : function(feedId)
	{
		this.iframe('?refresh&feed_id=' + feedId + '&' + (new Date).getTime());
		return false;
	},
	refreshGroup : function(groupId)
	{
		this.iframe('?refresh&group_id=' + groupId + '&' + (new Date).getTime());
		return false;
	},
	cancelRefresh : function()
	{
		one('#refresh').src = 'about:blank';
		this.updateAfterRefresh(this.totalFeeds);
		return false;
	},
	updateAfterRefresh : function(totalFeeds)
	{
		window.clearTimeout(this.refreshTimeoutId);

		this.totalFeeds = totalFeeds;
		css(one('#refreshing'), 'display', 'none');
		css(one('#refreshed'), 'display', 'block');

		var lastRefresh = one('#last-refresh');
		lastRefresh.className = 'timestamp ago-' + this.lastRefreshedOnTime;
		lastRefresh.innerHTML = this.ago(this.lastRefreshedOnTime);
		one('#total-feeds').innerHTML = this.totalFeeds;

		if (this.autoReload)
		{
			this.reload();
		};
	},
	refreshTimeoutId : null,
	onRefreshTimeout : function()
	{
		// soft restart the refresh
		this.refresh();
	},
	updateRefreshProgress : function(html, i, total)
	{
		window.clearTimeout(this.refreshTimeoutId);
		this.refreshTimeoutId = window.setTimeout(function()
		{
			Fever.Reader.onRefreshTimeout();
		}, 30 * 1000);

		css(one('#refreshing'), 'display', 'block');
		css(one('#refreshed'), 'display', 'none');

		var isFavicon = (arguments.length == 4);
		var label	= (isFavicon) ? 'favicons' : 'feeds';
		var action	= (isFavicon) ? 'Caching favicon for' : 'Refreshing';
		var mercury = one('#mercury');
		css(mercury, 'height', Math.floor(i / total * 100) + '%');

		one('#x-feeds').innerHTML = i;
		one('#y-feeds').innerHTML = total + ' ' + label;
		one('#refreshing-feed').innerHTML = action + ' <strong>' + html + '</strong>';

		if (!isFavicon)
		{
			this.lastRefreshedOnTime = (new Date).getTime();
		};
	},
	cacheFavicons : function()
	{
		var link	= document.createElement('link');
		link.type 	= 'text/css';
		link.rel	= 'stylesheet';
		link.id		= 'favicons';
		link.href	= './?favicons&' + this.lastCachedOnTime;
		one('head').appendChild(link);
	},
	updateFaviconCache : function(datetime)
	{
		var favicons = one('#favicons');
		var re = new RegExp('&' + datetime + '$');
		if (!favicons.href.match(re))
		{
			favicons.href = favicons.href.replace(/[0-9]+$/, datetime);
		};
	},
	feedRequiresAuth : function (feedId)
	{
		return Fever.addRemoteDialog('?manage=auth-feed&feed_id=' + feedId);
	},

	redraw : function()
	{
		this.redrawGroups();
		this.redrawFeeds();
	},
	redrawFeeds : function()
	{
		// if (Fever.isIPad) return; // not in iOS 5

		// nothing to see here if showFeeds is off or we're on the Hot page
		if (this.ui.showFeeds && this.ui.section != 0)
		{
			var container 	= one('#feeds-container');
			var scroller	= one('#feeds-scroller');

			var aH	= window.innerHeight - 42; // 16px top/bottom padding + 5px box padding

			if (container.offsetHeight > aH)
			{
				addClass(container, 'scrollable');
				css(scroller, 'max-height', aH + 'px');
			}
			else
			{
				removeClass(container, 'scrollable');
				css(scroller, 'max-height', '100%');
			};
		};
	},
	redrawGroups : function()
	{
		if (Fever.isIPad) return;

		var container 	= one('#groups-container');
		var scroller	= one('#groups-scroller');

		var aH	= window.innerHeight - 42; // 16px top/bottom padding + 5px box padding

		if (container.offsetHeight > aH)
		{
			var nH = aH - 60 - 90 - 34;
			addClass(container, 'scrollable');
			css(scroller, 'max-height', nH + 'px');
		}
		else
		{
			removeClass(container, 'scrollable');
			css(scroller, 'max-height', '100%');
		};
	},
	armSearch : function(e)
	{
		if (!this.ui.search.isEmpty())
		{
			this.armClearSearch();
		};

		one('#search').onsubmit = function()
		{
			return Fever.Reader.loadSection(4);
		};

		one('#q').onkeyup = function(e)
		{
			if (!e) var e = window.event;

			if (e.keyCode == 27 || this.value.isEmpty())
			{
				Fever.Reader.clearSearch();
			};

			if (!this.value.isEmpty())
			{
				Fever.Reader.armClearSearch();
			};

			Fever.Reader.ui.search = this.value;
		};
	},
	armClearSearch : function()
	{
		var clearSearch = one('#clear-search');
		addClass(clearSearch, 'clear');
		clearSearch.onclick = function()
		{
			Fever.Reader.clearSearch();
		};
	},
	clearSearch : function()
	{
		var clearSearch = one('#clear-search');
		removeClass(clearSearch, 'clear');
		clearSearch.onclick = function(){};
		this.ui.search 	= '';
		one('#q').value = '';

		if (this.ui.section == 4)
		{
			this.loadSection(this.ui.previous);
		}
		else
		{
			XHR.get('./?manage=clear-search');
		};
	},
	syncInterface : function()
	{
		// refresh feeds
		var hour = 1000 * 60 * 15; // every 15 minutes
		if (this.autoRefresh && (this.lastRefreshedOnTime + hour) < (new Date).getTime())
		{
			this.refresh();
		};

		one('#last-refresh').className = 'timestamp ago-' + this.lastRefreshedOnTime;
		one('#total-feeds').innerHTML = this.totalFeeds;

		// refresh timestamps
		var agos = $('.timestamp');
		for (var i = 0; i < agos.length; i++)
		{
			var ago 		= agos[i];
			var timestamp 	= ago.className.replace(/^.*timestamp\s+ago\-|[^0-9]*$/, '');
			ago.innerHTML 	= this.ago(timestamp);
		};
	},
	updateBadge : function()
	{
		if (window.fluid)
		{
			window.fluid.dockBadge = (this.unreadCounts && this.totalUnread) ? this.totalUnread : '';
		};

		document.title = document.title.replace(/\s+\(.+$/, '');
		if (this.unreadCounts && this.totalUnread)
		{
			document.title += ' (' + this.totalUnread + ')';
		};
	},

	ago : function(time)
	{
		if (time == 0)
		{
			return 'Never';
		};

		var diff = Math.round(((new Date).getTime() - time) / 1000); // trim off thousandths of a second
		if (diff < 60)
		{
			return 'Just now';
		};

		diff = Math.round(diff / 60);
		if (diff < 60)
		{
			var min = 'minute' + ((diff > 1) ? 's' : '');
			return diff + ' ' + min + ' ago';
		};

		diff = Math.round(diff / 60);
		if (diff < 24)
		{
			hr = 'hour' + ((diff > 1) ? 's' : '');
			return diff + ' ' + hr + ' ago';
		};

		diff = Math.round(diff / 24);
		if (diff < 7)
		{
			return (diff > 1) ? diff + ' days ago' : 'Yesterday';
		};

		diff = Math.round(diff / 7);
		if (diff < 12)
		{
			return (diff > 1) ? diff + ' weeks ago' : 'Last week';
		}
		else if (diff < 52)
		{
			var mo = Math.floor(diff / (30 / 7));
			return 'Around ' + mo + ' months ago';
		};

		diff = Math.round(diff / 52);
		return (diff > 1) ? 'Around ' + diff + ' years ago' : 'Last year';
	},
	armLinks : function()
	{
		if (this.newWindow || this.anonymize)
		{
			var links = $('a[rel=external], div.item-content a');

			for (var i = 0; i < links.length; i++)
			{
				if (this.anonymize)
				{
					links[i].onclick = function()
					{
						var anonUrl = 'http://feedafever.com/anon/?go=' + window.encodeURIComponent(this.href);
						if (Fever.Reader.newWindow)
						{
							window.open(anonUrl, '_blank');
						}
						else
						{
							window.location.href = anonUrl;
						};
						return false;
					};
				}
				else if (this.newWindow)
				{
					links[i].target = '_blank';
				};
			};
		};
	},

	unreadRecentlyRead : function()
	{
		XHR.get('./?manage=unread-read', null, function(){ Fever.Reader.reload(); });
	},
	markActiveAsRead : function()
	{
		switch (this.ui.section)
		{
			case 1:
			case 3:
				if (this.ui.hasFocus != 'groups' && this.ui.showFeeds && this.ui.feedId != 0)
				{
					this.markFeedAsRead(this.ui.feedId);
				}
				else
				{
					if (this.ui.section == 1) // groups
					{
						this.markGroupAsRead(this.ui.groupId);
					}
					else if (this.ui.section == 3) // sparks
					{
						this.markGroupAsRead(-1);
					};
				};
			break;
		};
	},
	markAllAsRead : function()
	{
		this.markGroupAsRead(0);
	},
	markGroupAsRead : function(groupId)
	{
		if (this.ui.section == 3) // sparks
		{
			groupId = -1
		};

		XHR.get('./?manage=statuses&mark=group&as=read&id=' + groupId + '&before=' + this.lastRenderedOnTime, null, function(){ Fever.Reader.reload(); });
	},
	markFeedAsRead : function(feedId)
	{
		XHR.get('./?manage=statuses&mark=feed&as=read&id=' + feedId + '&before=' + this.lastRenderedOnTime, null, function(){ Fever.Reader.reload(); });
	},
	markItemAsRead : function(itemId)
	{
		var items 	= $('#item-' + itemId + ', span.item-' + itemId);
		var updated = false;
		for (var i = 0; i < items.length; i++)
		{
			var item = items[i];
			if (!item.className.match(/\b(read|saved)\b/))
			{
				removeClass(item, 'new unread');
				addClass(item, 'read');
				updated 		= true;
			};
		};

		if (updated)
		{
			var aFeed;
			if (this.ui.section)
			{
				aFeed = one('#item-' + itemId + ' div.meta h2 a');
			}
			else
			{
				aFeed = one('ul.source-list span.item-' + itemId + ' a.inline-feed');
			};
			var feedId = aFeed.className.replace(/^.*\bfeed-([0-9]+)\b.*/, '$1');

			// update feed (and superfeed) unread
			var feedIds 	= [0, feedId];
			for (var i = 0; i < feedIds.length; i++)
			{
				var feedId		= feedIds[i];
				var feedUnread 	= one('#feed-' + feedId + ' em.unread-count');
				if (feedUnread)
				{
					var unreadCount = parseInt(feedUnread.innerHTML);
					if (unreadCount > 0)
					{
						unreadCount--;

						feedUnread.innerHTML = unreadCount;
						if (!unreadCount)
						{
							var feed = one('#feed-' + feedId + ' a');
							removeClass(feed, 'unread');
							addClass(feed, 'read');
						};
					};
				};
			};

			// update group unread
			if (this.groupIdsByFeedId[feedId])
			{
				if (this.ui.section != 3)
				{
					for (var i = 0; i < this.groupIdsByFeedId[feedId].length; i++)
					{
						var groupId 	= this.groupIdsByFeedId[feedId][i];
						var groupUnread = one('#group-' + groupId + ' em.unread-count');
						if (groupUnread)
						{
							var unreadCount = parseInt(groupUnread.innerHTML);
							if (unreadCount > 0)
							{
								unreadCount--;

								if (groupId == 0)
								{
									this.totalUnread = unreadCount;
									this.updateBadge();
								};

								groupUnread.innerHTML = unreadCount;
								if (!unreadCount)
								{
									var group = one('#group-' + groupId + ' a');
									removeClass(group, 'unread');
									addClass(group, 'read');
								};
							};
						};
					};
				};
			};

			// alert('marking as read ' + itemId);
			XHR.get('./?manage=statuses&mark=item&as=read&id=' + itemId);
			this.updateBadge();
		};
	},
	markItemAsUnread : function(itemId)
	{
		var items 	= $('#item-' + itemId + ', span.item-' + itemId);
		var updated = false;
		for (var i = 0; i < items.length; i++)
		{
			var item = items[i];
			if (item.className.match(/\b(read|saved)\b/))
			{
				removeClass(item, 'read');
				addClass(item, 'unread');
				updated 		= true;
			};
		};

		if (updated)
		{
			var aFeed;
			if (this.ui.section)
			{
				aFeed = one('#item-' + itemId + ' div.meta h2 a');
			}
			else
			{
				aFeed = one('ul.source-list span.item-' + itemId + ' a.inline-feed');
			};
			var feedId = aFeed.className.replace(/^.*\bfeed-([0-9]+)\b.*/, '$1');

			// update feed (and superfeed) unread
			var feedIds 	= [0, feedId];
			for (var i = 0; i < feedIds.length; i++)
			{
				var feedId		= feedIds[i];
				var feedUnread 	= one('#feed-' + feedId + ' em.unread-count');
				if (feedUnread)
				{
					var unreadCount = parseInt(feedUnread.innerHTML);
					unreadCount++;

					feedUnread.innerHTML = unreadCount;
					var feed = one('#feed-' + feedId + ' a');
					removeClass(feed, 'unread read');
					addClass(feed, 'unread');
				};
			};

			// update group unread
			if (this.groupIdsByFeedId[feedId])
			{
				if (this.ui.section != 3)
				{
					for (var i = 0; i < this.groupIdsByFeedId[feedId].length; i++)
					{
						var groupId 	= this.groupIdsByFeedId[feedId][i];
						var groupUnread = one('#group-' + groupId + ' em.unread-count');
						if (groupUnread)
						{
							var unreadCount = parseInt(groupUnread.innerHTML);
							unreadCount++;

							if (groupId == 0)
							{
								this.totalUnread = unreadCount;
								this.updateBadge();
							};

							groupUnread.innerHTML = unreadCount;
								var group = one('#group-' + groupId + ' a');
								removeClass(group, 'unread read');
								addClass(group, 'unread');
						};
					};
				};
			};

			// alert('marking as read ' + itemId);
			XHR.get('./?manage=statuses&mark=item&as=unread&id=' + itemId);
			this.updateBadge();
		};
	},

	addGroup : function()
	{
		Fever.addRemoteDialog('./?manage=add-group');
	},
	addFeed : function()
	{
		Fever.addRemoteDialog('./?manage=add-feed');
	},
	editGroup : function(groupId)
	{
		Fever.addRemoteDialog('./?manage=edit-group&group_id=' + groupId);
	},
	editFeed : function(feedId)
	{
		Fever.addRemoteDialog('./?manage=edit-feed&feed_id=' + feedId);
	},
	editActive : function()
	{
		if (this.ui.hasFocus != 'groups' && this.ui.showFeeds && this.ui.feedId != 0)
		{
			this.editFeed(this.ui.feedId);
		}
		else
		{
			if (this.ui.section == 1 && this.ui.groupId != 0) // groups
			{
				this.editGroup(this.ui.groupId);
			}
			else if (this.ui.section == 3) // sparks
			{
				this.editSparks();
			};
		};
	},
	editSparks : function()
	{
		Fever.addRemoteDialog('./?manage=edit-sparks');
	},

	addFeedToSparks : function(feedId)
	{
		XHR.get('./?manage=statuses&mark=feed&as=spark&id=' + feedId, null, function()
		{
			Fever.Reader.reload();
		});
	},
	removeFeedFromSparks : function(feedId)
	{
		XHR.get('./?manage=statuses&mark=feed&as=unspark&id=' + feedId, null, function()
		{
			Fever.Reader.reload();
		});
	},
	addFeedToGroup : function(feedId, groupId)
	{
		XHR.get('./?manage=add-feed-to-group&feed_id=' + feedId + '&group_id=' + groupId, null, function()
		{
			Fever.Reader.reload();
		});
	},
	removeFeedFromGroup : function(feedId, groupId)
	{
		XHR.get('./?manage=remove-feed-from-group&feed_id=' + feedId + '&group_id=' + groupId, null, function()
		{
			Fever.Reader.reload();
		});
	},
	addLinkToBlacklist : function(linkId)
	{
		Fever.addRemoteDialog('?manage=add-to-blacklist&link_id=' + linkId);
	},
	keyToToggleItemContent : function()
	{
		var acted = false;
		var itemOrLink = one('#content-container div.item.has-focus, #content-container div.link.has-focus');
		if (itemOrLink)
		{
			var isLink 	= itemOrLink.className.match(/\blink\b/);
			var hasItem = itemOrLink.className.match(/\bitem-\b/);
			if (isLink)
			{
				if (hasItem)
				{
					var itemClass = itemOrLink.className.replace(/.*\b(item-[0-9]+)\b.*/, '$1');
					var itemId = itemClass.replace(/^item-/, '');
					this.toggleItemContent(itemId, itemOrLink.id);
				};
			}
			else
			{
				var itemId = itemOrLink.id.replace(/^item-/, '');
				this.toggleItemContent(itemId);
			};
			acted = true;
		};
		return acted;
	},
	keyToPreviousNextItemLink : function(isPrevious)
	{
		var doScroll 	= arguments.length == 1;
		var acted 		= false;
		var itemOrLink 	= one('#content-container div.item.has-focus, #content-container div.link.has-focus');
		if (itemOrLink)
		{
			if (isPrevious) // previous
			{
				var previousItemOrLink = previousSibling(itemOrLink);
				if (previousItemOrLink && previousItemOrLink.className.match(/item|link/))
				{
					itemOrLink = previousItemOrLink;
				}
				else if (itemOrLink.className.indexOf('link') != -1)
				{
					var previousDegree = previousSibling(itemOrLink.parentNode);
					if (previousDegree)
					{
						var lastLink = lastChild(previousDegree);
						if (lastLink)
						{
							itemOrLink = lastLink;
						};
					};
				};
			}
			else // next
			{
				var nextItemOrLink = nextSibling(itemOrLink);
				if (nextItemOrLink)
				{
					itemOrLink = nextItemOrLink;
				}
				else if (itemOrLink.className.indexOf('link') != -1)
				{
					var nextDegree = nextSibling(itemOrLink.parentNode);
					if (nextDegree)
					{
						// TODO: relies on markup source order, okay for now
						itemOrLink = nextDegree.getElementsByTagName('div')[2];
					};
				};
			};
		}
		else
		{
			itemOrLink = one('#content-container div.item, #content-container div.link');
		};

		var firstItemOrLink	= one('#content-container div.item, #content-container div.link');
		var lastItemOrLink = last('#content-container div.item, #content-container div.link');

		if (itemOrLink && !doScroll && lastItemOrLink == itemOrLink)
		{
			if (!this.pageMaxed)
			{
				this.loadNextPage();
				acted = true;
			}
			else
			{
				return this.loadNextUnread();
			};
		};

		if (itemOrLink &&
		(
			this.ui.hasFocus != itemOrLink.id || // focus should change
			(itemOrLink.id == lastItemOrLink.id && !(window.pageYOffset + window.innerHeight + 1 >= document.body.scrollHeight)) || // paging down
			(itemOrLink.id == firstItemOrLink.id && itemOrLink.id != lastItemOrLink.id && window.pageYOffset != 0) // paging up
		))
		{
			var y 		= window.pageYOffset + getPos(itemOrLink).y - 16;
			var newY 	= y;

			if (doScroll)
			{
				if (isPrevious)
				{
					var minY = window.pageYOffset - window.innerHeight;
					if (newY < minY)
					{
						newY = minY + 16;

						// if itemOrLink is onscreen
						// TODO: still not perfect but close enough for now
						var b = y + itemOrLink.offsetHeight + 16;
						if (y < window.pageYOffset && b > window.pageYOffset)
						{
							this.ui.hasFocus = itemOrLink.id;
						};
					};
				}
				else
				{
					var maxY = window.pageYOffset + window.innerHeight;
					if (newY > maxY || this.ui.hasFocus == itemOrLink.id)
					{
						newY = maxY - 16;
					};
				};
			};

			// no adjustment necessary
			if (y == newY)
			{
				this.ui.hasFocus = itemOrLink.id;
			};
			this.refocus();

			this.freeScrollTo(newY);
			acted = true;
		}
		else
		{
			acted = this.loadNextUnread();
		};
		return acted;
	},
	keyToBlacklist : function()
	{
		var acted = false;
		var link = one('#content-container div.link.has-focus');
		if (link)
		{
			var linkId = link.id.replace(/^link-/, '');
			this.addLinkToBlacklist(linkId);
			acted = true;
		};
		return acted;
	},

	toggleLinkSaveState : function(linkId)
	{
		var saved		= one('#section-2 em.saved-count');
		var savedCount 	= parseInt(saved.innerHTML);
		var link 		= one('#link-' + linkId);
		var state		= (link.className.indexOf('saved') != -1) ? 'unsaved' : 'saved';

		if (link.className.match(/\ban-item\b/))
		{
			var itemId = link.className.replace(/^.*item-([0-9]+).*/, '$1');
			this.markItemAsRead(itemId);
			XHR.get('./?manage=statuses&mark=item&as=' + state + '&id=' + itemId);
		}
		else
		{
			// alert('TODO: save link');
			return;
			/*#@+
			XHR.get('./?manage=statuses&mark=link&as=' + state + '&id=' + linkId);
			#@-*/
		};

		if (state == 'saved')
		{
			savedCount++;
			addClass(link, 'saved');
		}
		else
		{
			savedCount--;
			removeClass(link, 'saved');
		};

		if (savedCount < 0) // sanity check
		{
			savedCount = 0;
		};
		saved.innerHTML = savedCount;
	},
	toggleItemSaveState : function(itemId)
	{
		this.markItemAsRead(itemId);
		var saved		= one('#section-2 em.saved-count');
		var savedCount 	= parseInt(saved.innerHTML);
		var item		= one('#item-' + itemId);

		var state		= (item.className.indexOf('saved') != -1) ? 'unsaved' : 'saved';
		if (state == 'saved')
		{
			savedCount++;
			item.className = item.className.replace(/\b(new|unread|read)\b/, 'saved');
		}
		else
		{
			savedCount--;
			item.className	= item.className.replace(/\bsaved\b/, 'read');
		};

		if (savedCount < 0) // sanity check
		{
			savedCount = 0;
		};
		saved.innerHTML = savedCount;

		// update feed (and superfeed) saved
		var feedIds 	= [0, one('#item-' + itemId + ' div.meta h2 a,.link.item-' + itemId +  + ' div.meta h2 a').className.replace(/^.*\bfeed-([0-9]+)\b.*/, '$1')];
		for (var i = 0; i < feedIds.length; i++)
		{
			var feedId		= feedIds[i];
			var feedSaved 	= one('#feed-' + feedId + ' em.saved-count');
			if (feedSaved)
			{
				savedCount = parseInt(feedSaved.innerHTML);
				if (state == 'saved')
				{
					savedCount++;
				}
				else
				{
					savedCount--;
				};

				if (savedCount < 0) // sanity check
				{
					savedCount = 0;
				};

				if (savedCount == 0)
				{
					addClass(one('#feed-' + feedId + ' a'), 'read');
				}
				else
				{
					removeClass(one('#feed-' + feedId + ' a'), 'read');
				};

				feedSaved.innerHTML = savedCount;
			};
		};

		XHR.get('./?manage=statuses&mark=item&as=' + state + '&id=' + itemId);
	},
	toggleItemContent : function(itemId)
	{
		var selector = '#item-' + itemId;
		if (arguments.length == 2)
		{
			selector = '#' + arguments[1];
		}
		else
		{
			this.markItemAsRead(itemId);
		};

		var item	= one(selector);
		var full	= (item.className.indexOf('full') != -1);
		var content = one(selector + ' div.item-content');
		var hasFull 	= (item.fullContent && item.fullContent != null);
		var hasExcerpt	= (item.excerptContent && item.excerptContent != null);

		if (full) // get or display excerpt
		{
			removeClass(item, 'full');

			if (!hasFull)
			{
				item.fullContent = content.innerHTML;
			};

			if (!hasExcerpt)
			{
				this.onContentRequested();
				XHR.get('./?manage=item&excerpt&id=' + itemId, content, function()
				{
					Fever.Reader.onSubContentLoaded();
				});
			}
			else
			{
				content.innerHTML 	= item.excerptContent;
			};
		}
		else // get or display full content
		{
			addClass(item, 'full');
			var newY = window.pageYOffset + getPos(item).y - 16;

			if (!hasExcerpt)
			{
				item.excerptContent = content.innerHTML;
			};

			if (!hasFull)
			{
				this.onContentRequested();
				XHR.get('./?manage=item&id=' + itemId, content, function()
				{
					Fever.Reader.onSubContentLoaded();
				});
			}
			else
			{
				content.innerHTML = item.fullContent;
			};
		};

		var itemY = getPos(item).y;
		if (itemY < 0)
		{
			var y = window.pageYOffset + itemY - 16;
			this.freeScrollTo(y);
		};
	},
	visitFeedSite : function(feedId)
	{
		var url = u('./?visit=' + feedId);
		if (this.newWindow)
		{
			window.open(url);
		}
		else
		{
			window.location.href = url;
		};
	},
	visitFeedSiteAndMarkAsRead : function(feedId)
	{
		this.markFeedAsRead(feedId);
		this.visitFeedSite(feedId);
	},
	preferences : function()
	{
		Fever.addRemoteDialog('./?manage=preferences');
	},
	blacklist : function()
	{
		window.location.href = u('./?blacklist');
	},
	checkForUpdates : function()
	{
		this.onContentRequested();
		XHR.get('./?updates', one('#update-container'), function(data)
		{
			Fever.Reader.onSubContentLoaded();
			if (data.responseText.indexOf('no-update') != -1)
			{
				window.setTimeout(function()
				{
					css(one('#update'), 'display', 'none');
				}, 5 * 1000);
			};
		});
	},

	services : [],
	buildUrl : function(itemId, template)
	{
		var link 	= one('#item-' + itemId + ' div.meta h1 a');
		var item 	= one('#item-' + itemId + ' div.item-content');
		var title	= decodeEntities(stripHTML(link.innerHTML));
		var content = decodeEntities(stripHTML(item.innerHTML));
		var url		= link.href;

		// Bird Feeder link
		if (url.match(/^.+\?FeederAction=[^&]+&feed=[^&]+&seed=([^&]+).*/))
		{
			url = decodeURIComponent(url.replace(/^.+\?FeederAction=[^&]+&feed=[^&]+&seed=([^&]+).*/, '$1'));
		};

		// excerpt, could be it's own function
		var excerpt		= content.replace(/(^\s*|\s*$)/g, '');
		if (excerpt.length > 200)
		{
			excerpt = excerpt.substr(0, 200);
			excerpt = excerpt.replace(/\s[^\s]+$/, '') + '…';
		};

		// escape single quotes in javascript: pseudocol
		if (template.match(/^javascript:/))
		{
			title	= title.replace(/'/, "\\'");
			url		= url.replace(/'/, "\\'");
			excerpt	= excerpt.replace(/\s+/g, ' ').replace(/'/, "\\'");
		};

		template = template.replace(/%t/g, encodeURIComponent(title));
		template = template.replace(/%u/g, encodeURIComponent(url));
		template = template.replace(/%e/g, encodeURIComponent(excerpt));
		return template;
	},
	sendToService : function(itemId, template)
	{
		var url = this.buildUrl(itemId, template);

		// alert(url);

		if (url.match(/^#/)) { // prefix the url with # to load url in background
			var img = new Image();
			img.src = url.replace(/^#/, '');
		}
		else if (!url.match(/^https?:/))
		{
			window.location.href = url;
		}
		else
		{
			window.open(url);
		};
	},
	addNewService : function()
	{
		var i = $('#services tr.a-service').length;
		var html = one('#service-template').innerHTML;
		html = html.replace(/name="service\[/g, 'name="service[' + i + '][');
		html = html.replace(/id="service-/g, 'id="service-' + i + '-');
		html = html.replace(/#service-/g, '#service-' + i + '-');

		var services = one('#services');
		services.innerHTML += html;
	},
	deleteService : function(serviceId)
	{
		if (window.confirm('Are you sure you want to delete this service?'))
		{
			var row = one(serviceId);
			row.parentNode.removeChild(row);
		};
	},

	scrollToTop : function()
	{
		this.autoReadEnabled = false;
		Fever.scrollTo(one('#top'), null, function()
		{
			Fever.Reader.autoReadEnabled = true;
			Fever.Reader.refocus();
		});
	},
	scrollToFeed : function(feed_id)
	{
		if (!this.ui.showFeeds || this.ui.section == 0)
		{
			return false;
		};

		// only scroll if feed is not in view
		var target	= one('#feed-' + feed_id);
		var parent 	= one('#feeds-scroller');
		var targetY = getPos(target).y - getPos(parent).y;
		if
		(
			arguments.length > 1 ||
			targetY < 0 ||
			targetY + target.offsetHeight > parent.offsetHeight
		)
		{
			Fever.scrollTo(target, parent);
		};

		return false;
	},
	scrollToGroup : function(group_id)
	{
		if (group_id == 0) return;

		// only scroll if feed is not in view
		var target	= one('#group-' + group_id);
		var parent 	= one('#groups-scroller');
		var targetY = getPos(target).y - getPos(parent).y;
		if
		(
			arguments.length > 1 ||
			targetY < 0 ||
			targetY + target.offsetHeight > parent.offsetHeight
		)
		{
			Fever.scrollTo(target, parent);
		};

		return false;
	},
	freeScrollTo : function(newY)
	{
		Fever.Reader.autoReadEnabled = 0;
		Fever.scrollTo(newY, null, function()
		{
			Fever.Reader.autoReadEnabled = 1;
		});
	},

	makeFeedsDraggable : function()
	{
		var draggables = $('#feeds-scroller a, a.inline-feed');
		for (var i = 0; i < draggables.length; i++)
		{
			if (draggables[i].parentNode.id == 'feed-0') { continue; };

			Draggable.make(draggables[i]);
			draggables[i].onDragStart = function()
			{
				if ((Fever.Reader.ui.section == 1 && Fever.Reader.ui.groupId != 0) || Fever.Reader.ui.section == 3)
				{
					one('#remove-from').innerHTML = (Fever.Reader.ui.section == 3) ? 'Sparks' : 'group';
					css(one('#remove'), 'display', 'block');
					css(one('#search'), 'display', 'none');
				};
			};
			draggables[i].onDragEnd = function()
			{
				if (Draggable.target)
				{
					var feedId = Draggable.obj.className.replace(/.*feed-([0-9]+).*/, '$1');

					if (Draggable.target.id == 'section-3')
					{
						Fever.Reader.addFeedToSparks(feedId);
					}
					else if (Draggable.target.id == 'section-4')
					{
						if (Fever.Reader.ui.section == 3)
						{
							Fever.Reader.removeFeedFromSparks(feedId);
						}
						else
						{
							Fever.Reader.removeFeedFromGroup(feedId, Fever.Reader.ui.groupId);
						};
					}
					else
					{
						var groupId = Draggable.target.id.replace(/^group-/, '');
						if (groupId && groupId != Fever.Reader.ui.groupId)
						{
							Fever.Reader.addFeedToGroup(feedId, groupId);
						};
					};
				};

				css(one('#search'), 'display', 'block');
				css(one('#remove'), 'display', 'none');
			};
		};
	},
	onBoxClick : function(itemOrLink)
	{
		this.focusElement(itemOrLink);

		var e = window.event ? window.event : this.event;
		var target = e.target;
		var targetSelector = toSelector(target);
		var shouldMarkAsRead = !(target.className.match(/\b(btn|inline-feed)\b/) || targetSelector.match(/\binline-feed\b/));
		var shouldToggle = this.toggleClick && !(target.nodeName == 'A' || target.parentNode.nodeName == 'A' || target.nodeName == 'OBJECT' || target.nodeName == 'EMBED' || target.className.match(/\b(btn|state|inline-feed)\b/) || targetSelector.match(/\binline-feed\b/));
		var isLink 	= itemOrLink.className.match(/\blink\b/);
		var hasItem = itemOrLink.className.match(/\bitem-\b/);

		if (isLink)
		{
			if (hasItem)
			{
				var itemClass 	= itemOrLink.className.replace(/.*\b(item-[0-9]+)\b.*/, '$1');
				var itemId 		= itemClass.replace(/^item-/, '');
				if (shouldToggle)
				{
					this.toggleItemContent(itemId, itemOrLink.id);
				};
			};
		}
		else
		{
			var itemId = itemOrLink.id.replace(/^item-/, '');
			if (shouldMarkAsRead)
			{
				this.markItemAsRead(itemId);
			};
			if (shouldToggle)
			{
				this.toggleItemContent(itemId);
			};
		};
	},

	refocusId : null,
	refocus : function()
	{
		// unfocus current, might have multiple by mistake
		var focussed = $('#groups.has-focus, #feeds.has-focus, #content-container div.item.has-focus, #content-container div.link.has-focus, #content-container div.link.has-focus li.has-focus');
		for (var i = 0; i < focussed.length; i++)
		{
			removeClass(focussed[i], 'has-focus');
		};

		// feeds cannot be focus if they are not visible
		if (this.ui.hasFocus == 'feeds' && (!this.ui.showFeeds || this.ui.section == 0))
		{
			this.ui.hasFocus = 'groups';
		};

		// sanity check, items may go "missing"
		var focus = one('#' + this.ui.hasFocus);
		if (!focus)
		{
			if (this.ui.showFeeds && this.ui.section != 0)
			{
				this.ui.hasFocus = 'feeds';
				focus = one('#feeds');
			}
			else
			{
				this.ui.hasFocus = 'groups';
				focus = one('#groups');
			};
		};

		// mark item as read
		if (this.autoRead && this.autoReadEnabled && focus.className.indexOf('item') != -1)
		{
			var itemId = focus.id.replace(/^item-/, '');
			this.markItemAsRead(itemId);
		};

		addClass(focus, 'has-focus');
	},
	focusElement : function(elem)
	{
		this.ui.hasFocus = elem.id;
		this.refocus();
		// alert('refocus(): focusElement()');
	},

	onload : function()
	{
		document.addEventListener('click', function(e) { Fever.Reader.event = e; }, true); // shame

		if (Fever.isLion) document.getElementsByTagName('html')[0].className += 'lion'; // fix for skinny/invisible scrollbars problems

		this.onContentLoaded();
		this.cacheFavicons();

		if (Fever.isIPad)
		{
			this.zoom = document.documentElement.clientWidth / window.innerWidth;
			window.addEventListener('resize', function()
			{
				var zoomNew = document.documentElement.clientWidth / window.innerWidth;
				if (this.zoom != zoomNew)
				{
					this.zoom = zoomNew;
					if (this.zoom == 1)
					{
						removeClass($('#fixed'), 'zoomed');
					}
					else
					{
						addClass($('#fixed'), 'zoomed');
					};
				};
			}, true);
		}
		window.addEventListener('resize', function()
		{
			Fever.dismissMenu();
			Fever.maximizeSelect();
			Fever.Reader.redraw();
		}, true);
		window.addEventListener('scroll', function(e)
		{
			Fever.dismissMenu();

			// rest only applies to scrolling the body (window in Opera)
			if (e.target.nodeName == '#document' || e.target == window)
			{
				if
				(
					(
						window.pageYOffset > Fever.Reader.pageY ||  // scrolling down
						(
							Fever.isIPad && window.pageYOffset == 0 && Fever.Reader.pageY == 0 // trigger scroll when there's nowhere to scroll to on an iPad
						)
					) &&
					window.pageYOffset >= document.body.scrollHeight - (window.innerHeight * 1.25) && // there's only one (give or take) screen height left to scroll
					!Fever.Reader.pageLoading // not already loading the next page
				)
				{
					Fever.Reader.loadNextPage();
				};

				if (Fever.autoScrolling)
				{
					var itemsOrLinks = $('#content-container div.item, #content-container div.link');
					var offset = 64;
					for (var i = 0; i < itemsOrLinks.length; i++)
					{
						var itemOrLink = itemsOrLinks[i];
						var y = getPos(itemOrLink).y;
						var h = y + itemOrLink.offsetHeight;
						if (y > offset || h > offset)
						{
							if (Fever.Reader.autoRead && Fever.Reader.autoReadEnabled)
							{
								Fever.Reader.ui.hasFocus = itemOrLink.id;
								Fever.Reader.refocus();
							};
							break;
						}
						else if (Fever.Reader.autoRead && Fever.Reader.autoReadEnabled && itemOrLink.className.indexOf('item') != -1)
						{
							var itemId = itemOrLink.id.replace(/^item-/, '');
							Fever.Reader.markItemAsRead(itemId);
						};
					};
				};

				Fever.Reader.pageY = window.pageYOffset;
			};
		}, true);
		document.addEventListener('keydown', function(e)
		{
			var inDialog 	= css(one('#dialog-container'), 'display') == 'block';
			var inInput 	= e.target.nodeName == 'INPUT';
			var inTextarea 	= e.target.nodeName == 'TEXTAREA';
			var isShortKey	= (e.ctrlKey || e.metaKey);
			var isMac		= navigator.platform.indexOf('Mac') != -1;

			var acted = false;

			// see if any of the user-defined services are responsible for this key
			if (!isShortKey && !inInput && !inDialog)
			{
				var key = String.fromCharCode(e.keyCode).toLowerCase();

				for (var i=0; i<Fever.Reader.services.length; i++)
				{
					var service = Fever.Reader.services[i];
					if (service[2].toLowerCase() == key)
					{
						var item = one('div.item.has-focus');
						if (item)
						{
							var itemId = item.id.replace(/item-/, '');
							Fever.Reader.sendToService(itemId, service[1]);
							acted = true;
							break;
						};
					};
				};
			};

			// if none of the user-defined services took responsibility
			if (!acted)
			{
				// alert(e.keyCode);
				switch(e.keyCode)
				{
					case 49: // 1, hot
						if (isShortKey || inInput || inDialog) break;
						Fever.Reader.loadSection(0);
						acted = true;
					break;

					case 50: // 2, kindling
						if (isShortKey || inInput || inDialog) break;
						Fever.Reader.loadGroup(0);
						acted = true;
					break;

					case 51: // 3, saved
						if (isShortKey || inInput || inDialog) break;
						Fever.Reader.loadSection(2);
						acted = true;
					break;

					case 52: // 4, sparks
						if (isShortKey || inInput || inDialog) break;
						Fever.Reader.loadSection(3);
						acted = true;
					break;

					case 53: // 4, sparks
						if (isShortKey || inInput || inDialog) break;
						Fever.Reader.loadSection(4);
						acted = true;
					break;

					// cancel dialog
					case 190: // period
						if (isShortKey && inDialog)
						{
							Fever.dismissDialog();
							acted = true;
						};
					break;

					// submit dialog
					case 13: // enter/return
						if (inDialog)
						{
							if (!inTextarea)
							{
								var form = one('#dialog form')
								if (typeof form.onsubmit != 'function' || form.onsubmit())
								{
									form.submit();
									acted = true;
								};
							};
						}
						else if (!inInput)
						{
							acted = Fever.Reader.keyToToggleItemContent();
						};
					break;

					// mark all (shift) of the current group/feed items as read
					case 65: // a
						if (isShortKey || inInput || inDialog) break;

						if (e.shiftKey)
						{
							Fever.Reader.markAllAsRead();
						}
						else
						{
							Fever.Reader.markActiveAsRead();
						};
						acted = true;
					break;

					// visit site and mark as read
					case 86: // v
						if (isShortKey || inInput || inDialog) break;

						var feedLink = one('div.item.has-focus a.inline-feed, div.an-item.has-focus div.meta h2 a, #feeds li.has-focus a');
						if (feedLink.className.match(/\bfeed-[0-9]+\b/))
						{
							var feedId = feedLink.className.replace(/^.*\bfeed-([0-9]+)\b.*$/, '$1');
							if (feedId > 0)
							{
								if (e.shiftKey)
								{
									Fever.Reader.visitFeedSiteAndMarkAsRead(feedId);
								}
								else
								{
									Fever.Reader.visitFeedSite(feedId);
								};
								acted = true;
							};
						};
					break;

					// undo/unread most recently read items
					case 90: // z
						if (isShortKey || inInput || inDialog) break;

						Fever.Reader.unreadRecentlyRead();
						acted = true;
					break;

					// add new feed/group (shift)
					case 78: // n
						if (isShortKey || inInput || inDialog) break;
						if (e.shiftKey)
						{
							Fever.Reader.addGroup();
						}
						else
						{
							Fever.Reader.addFeed();
						};
						acted = true;
					break;

					// refresh feeds
					case 82: // r
						if (isShortKey || inInput || inDialog) break;

						if (e.shiftKey && Fever.Reader.ui.section == 1)
						{
							if (Fever.Reader.ui.hasFocus == 'groups') {
								Fever.Reader.refreshGroup(Fever.Reader.ui.groupId);
							}
							else if (Fever.Reader.ui.feedId != 0)
							{
								Fever.Reader.refreshFeed(Fever.Reader.ui.feedId);
							};
							acted = true;
						}
						else
						{
							Fever.Reader.refresh(1);
							acted = true;
						}
					break;

					// preferences
					case 80: // p
						if (isShortKey || inInput || inDialog) break;

						Fever.Reader.preferences();
						acted = true;
					break;

					// blacklist
					case 66: // b
						if (inInput || inDialog) break;

						if (e.shiftKey)
						{
							Fever.Reader.blacklist();
							acted = true;
						}
						else
						{
							acted = Fever.Reader.keyToBlacklist();
						}
					break;

					// show/hide feeds
					case 70: // f
						if (isShortKey || inInput || inDialog) break;

						Fever.Reader.toggleFeeds();
						acted = true;
					break;

					// show/hide read
					case 85: // u
						if (isShortKey || inInput || inDialog) break;

						Fever.Reader.toggleUnread();
						acted = true;
					break;

					// save current item
					case 83: // s
						if (isShortKey || inInput || inDialog) break;

						// TODO: make work on Hot view
						var item = one('div.item.has-focus');

						if (item)
						{
							var itemId = item.id.replace(/item-/, '');
							Fever.Reader.toggleItemSaveState(itemId);
							acted = true;
						}
					break;

					// edit active group/feed
					case 73: // i
						if (isShortKey || inInput || inDialog) break;

						Fever.Reader.editActive();
						acted = true;
					break;

					// focus search input
					case 191: // ?
						if (isShortKey || inInput || inDialog) break;

						one('#q').focus();
						acted = true;
					break;

					// clear Search
					// dismiss dialog
					case 27: // esc
						if (inDialog)
						{
							Fever.dismissDialog();
							acted = true;
						};
						if (inInput) break;

						Fever.Reader.clearSearch();
						acted = true;
					break;

					// switch focus
					case 37: // left
					case 72: // h:vim
					case 39: // right
					case 76: // l:vim
						if (isShortKey || inInput || inDialog) break;

						var left 	= e.keyCode==37 || e.keyCode==72;
						var right	= e.keyCode==39 || e.keyCode==76;

						var refocus = true;
						var noFeeds = (Fever.Reader.ui.section == 0 || !Fever.Reader.ui.showFeeds);

						if (Fever.Reader.ui.hasFocus == 'groups')
						{
							if (left || noFeeds) // left
							{
								// first *visible* item/link // TODO: is a dupe
								var itemsOrLinks = $('#content-container div.item, #content-container div.link');
								for (var i = 0; i < itemsOrLinks.length; i++)
								{
									var itemOrLink = itemsOrLinks[i];
									if (getPos(itemOrLink).y > 0)
									{
										Fever.Reader.ui.hasFocus = itemOrLink.id;
										break;
									};
								};
							}
							else
							{
								Fever.Reader.ui.hasFocus = 'feeds';
							};
						}
						else if (Fever.Reader.ui.hasFocus == 'feeds')
						{
							if (left) // left
							{
								Fever.Reader.ui.hasFocus = 'groups';
							}
							else
							{
								// first *visible* item/link // TODO: is a dupe
								var itemsOrLinks = $('#content-container div.item, #content-container div.link');
								for (var i = 0; i < itemsOrLinks.length; i++)
								{
									var itemOrLink = itemsOrLinks[i];
									if (getPos(itemOrLink).y > 0)
									{
										Fever.Reader.ui.hasFocus = itemOrLink.id;
										break;
									};
								};
							};
						}
						else // item/link
						{
							if (right || noFeeds) // right
							{
								var contributer = one('#content-container div.link.has-focus li.has-focus span.source a');
								var link = one('#content-container div.item.has-focus h1 a, #content-container div.link.has-focus h1 a');
								if (right && link)
								{
									refocus = false;
									link = (contributer) ? contributer : link;
									if (Fever.Reader.newWindow)
									{
										window.open(link.href, '_blank');
									}
									else
									{
										window.location.href = link.href;
									};
								}
								else
								{
									Fever.Reader.ui.hasFocus = 'groups';
								};
							}
							else
							{
								Fever.Reader.ui.hasFocus = 'feeds';
							};
						};

						// refocus isn't required if we've just pressed right to open a link
						if (refocus)
						{
							Fever.Reader.refocus();
						};
						acted = true;
					break;

					// open
					case 79: // O (capital o)
						if (isShortKey || inInput || inDialog) break;

						// duped from right arrow key case above
						var contributer = one('#content-container div.link.has-focus li.has-focus span.source a');
						var link = one('#content-container div.item.has-focus h1 a, #content-container div.link.has-focus h1 a');
						if (link)
						{
							link = (contributer) ? contributer : link;
							if (Fever.Reader.newWindow)
							{
								window.open(link.href, '_blank');
							}
							else
							{
								window.location.href = link.href;
							};
							acted = true;
						};
					break;

					// activate group/feed
					case 38: // up
					case 75: // k:vim(up)
					case 40: // down
					case 74: // j:vim(down)
						if (isShortKey || inInput || inDialog) break;

						var up 		= e.keyCode==38 || e.keyCode==75;
						var down 	= e.keyCode==40 || e.keyCode==74;

						if (e.shiftKey)
						{
							if (Fever.Reader.ui.section == 0 && Fever.Reader.ui.hasFocus.match(/link-/))
							{
								var contributers 	= $('#' + Fever.Reader.ui.hasFocus + ' li');
								var focussed 		= one('#' + Fever.Reader.ui.hasFocus + ' li.has-focus');

								if (focussed)
								{
									removeClass(focussed, 'has-focus');
									if (up) // up
									{
										focussed = previousSibling(focussed);
									}
									else // down
									{
										focussed = nextSibling(focussed);
									};
								}
								else
								{
									if (up) // up, from bottom
									{
										focussed = contributers[contributers.length - 1];
									}
									else // down, from top
									{
										focussed = contributers[0];
									};
								};

								if (focussed)
								{
									addClass(focussed, 'has-focus');

									// scroll the focused element into view if necessary
									var y		= getPos(focussed).y;
									var bottom 	= y + focussed.offsetHeight;
									if (bottom > window.innerHeight || y < 0)
									{
										Fever.scrollTo(focussed);
									};
								};
							};
						}
						else
						{
							var focussed = null;
							if (Fever.Reader.ui.hasFocus.match(/(groups|feeds)/))
							{
								var groupsOrFeeds = $('#' + Fever.Reader.ui.hasFocus +' li');
								for (var i = 0; i < groupsOrFeeds.length; i++)
								{
									var groupOrFeed = groupsOrFeeds[i];
									if (groupOrFeed.className.indexOf('has-focus') != -1)
									{
										removeClass(groupOrFeed, 'has-focus');
										if (up) // up
										{
											var prev = (i ? i : groupsOrFeeds.length) - 1;
											focussed = groupsOrFeeds[prev];
											if (focussed.id == 'groups-scroller-container')
											{
												focussed = groupsOrFeeds[prev - 1];
											};
										}
										else
										{
											var next = (i < groupsOrFeeds.length - 1) ? i + 1 : 0;
											focussed = groupsOrFeeds[next];
											if (focussed.id == 'groups-scroller-container')
											{
												focussed = groupsOrFeeds[next + 1];
											};
										};

										break;
									};
								};

								if (focussed)
								{
									addClass(focussed, 'has-focus');
									var m = focussed.id.split('-');
									var loader = ucfirst(m[0]);
									var id = m[1];

									if (loader == 'Group' || loader == 'Feed')
									{
										Fever.Reader['scrollTo' + loader](id);
									};

									window.clearTimeout(Fever.Reader.refocusId);
									Fever.Reader.refocusId = window.setTimeout(function()
									{
										Fever.Reader['load' + loader](id);
									}, 500);
									acted = true;
								};
							};
						};
					// break; // pass through to the next command

					// prev/next item/link
					case 74: // j:vim(down)
					case 75: // k:vim(up)
						if (e.keyCode == 38 || e.keyCode == 40) break; // escape if passed through from arrow keys
						if (inInput || inDialog || acted) break;
						acted = Fever.Reader.keyToPreviousNextItemLink(e.keyCode == 75, true);
					break;

					// prev/next/scroll item/link
					case 32: // space
						if (inInput || inDialog) break;
						acted = Fever.Reader.keyToPreviousNextItemLink(e.shiftKey);
					break;

					// toggle item content
					case 48: // 0
					case 96: // 0 (number pad)
						if (inInput || inDialog) break;
						acted = Fever.Reader.keyToToggleItemContent();
					break;

					// toggle item content
					case 18: // option key (for new MacBook Pro)
						if (isShortKey || inInput || inDialog || !isMac) break;
						acted = Fever.Reader.keyToToggleItemContent();
					break;

				};
			};

			if (acted && e.preventDefault)
			{
				e.preventDefault();
			};

			Fever.dismissMenu();
		});
		window.setInterval(function(){ Fever.Reader.syncInterface(); }, 30 * 1000);
		Fever.Reader.syncInterface();
	}
};
Fever.menuControllers =
{
	// TODO: eliminate redundancy
	hotStart :
	{
		items : [],
		onOpen : function()
		{
			var menu = Fever.menuArgs[0];
			menu.value = Fever.Reader.ui.hotStart;
			for (var i = 0; i < this.items.length; i++)
			{
				if (this.items[i].selected)
				{
					this.items[i].selected = false;
				};

				if (this.items[i].value == Fever.Reader.ui.hotStart)
				{
					this.items[i].selected = true;
				};
			};
		},
		onClick : function()
		{
			var menu = Fever.menuArgs[0];

			if (menu.value != this.value)
			{
				Fever.Reader.ui.hotStart 	= this.value;
				menu.value 			= this.value;
				Fever.Reader.loadRemote('hotStart');
				// window.location.href = './?ui[hot_start]=' + this.value;
			};
		}
	},
	hotRange :
	{
		items : [],
		onOpen : function()
		{
			var menu = Fever.menuArgs[0];
			menu.value = Fever.Reader.ui.hotRange;
			for (var i = 0; i < this.items.length; i++)
			{
				if (this.items[i].selected)
				{
					this.items[i].selected = false;
				};

				if (this.items[i].value == Fever.Reader.ui.hotRange)
				{
					this.items[i].selected = true;
				};
			};
		},
		onClick : function()
		{
			var menu = Fever.menuArgs[0];

			if (menu.value != this.value)
			{
				Fever.Reader.ui.hotRange 	= this.value;
				menu.value 			= this.value;
				Fever.Reader.loadRemote('hotRange');
				// window.location.href = './?ui[hot_range]=' + this.value;
			};
		}
	},
	group :
	{
		items : [], // built dynamically by onOpen
		onOpen : function()
		{
			var groupId 	= Fever.menuArgs[2];
			var allRead		= one('#group-' + groupId + ' a').className.match(/\bread\b/);
			var isSuper		= toSelector(Fever.menuArgs[0]).match(/^#feed-0/);
			var divider		= { divider : true };

			var read =
			{
				disabled : allRead,
				text : 'Mark group as read',
				onClick : function()
				{
					Fever.Reader.markGroupAsRead(groupId);
				}
			};

			var edit =
			{
				disabled : groupId == 0 || isSuper,
				text : 'Edit&#8230; <span class="key">i</span>',
				onClick : function()
				{
					Fever.Reader.editGroup(groupId);
				}
			};

			var deleteGroup =
			{
				disabled : groupId == 0 || isSuper,
				text : 'Delete&#8230;',
				onClick : function()
				{
					Fever.addRemoteDialog('./?manage=delete-group&group_id=' + groupId);
				}
			};

			this.items =
			[
				read,
				divider,
				edit,
				divider,
				deleteGroup
			];
		}
	},
	feed :
	{
		items : [], // built dynamically by onOpen
		onOpen : function()
		{
			var feedId 		= Fever.menuArgs[2];
			var allRead		= one('a.feed-' + feedId).className.match(/\bread\b/) || Fever.Reader.ui.section == 2;
			var divider		= { divider : true };

			// TODO: for saved section, sparks superfeed

			var read =
			{
				disabled : allRead,
				text : 'Mark feed as read',
				onClick : function()
				{
					Fever.Reader.markFeedAsRead(feedId);
				}
			};

			var visit =
			{
				text : 'Visit site&#8230; <span class="key">v</span>',
				onClick : function()
				{
					Fever.Reader.visitFeedSite(feedId);
				}
			};

			var visitRead =
			{
				text : 'Visit site and mark as read&#8230; <span class="shift key">v</span>',
				onClick : function()
				{
					Fever.Reader.visitFeedSiteAndMarkAsRead(feedId);
				}
			};

			var edit =
			{
				text : 'Edit&#8230; <span class="key">i</span>',
				onClick : function()
				{
					Fever.Reader.editFeed(feedId);
				}
			};

			var unsubscribe =
			{
				text : 'Unsubscribe&#8230;',
				onClick : function()
				{
					Fever.addRemoteDialog('./?manage=delete-feed&feed_id=' + feedId);
				}
			};

			this.items =
			[
				read,
				divider,
				visit,
				visitRead,
				divider,
				edit,
				divider,
				unsubscribe
			];
		}
	},
	item :
	{
		items : [], // built dynamically by onOpen
		onOpen : function()
		{
			var divider		= { divider : true };
			var itemId 		= Fever.menuArgs[2];
			var itemOrLink	= Fever.menuArgs[0].parentNode.parentNode.parentNode;
			var isLink		= itemOrLink.className.match(/\blink\b/);
			var isExcerpt	= !itemOrLink.className.match(/\bfull\b/);
			var isSaved		= itemOrLink.className.match(/\bsaved\b/);
			var isRead		= itemOrLink.className.match(/\bread\b/);
			var showWhich 	= Fever.menuArgs[3];

			var excerpt =
			{
				text		: 'Excerpt <span class="key">↵</span>',
				selected 	: isExcerpt,
				onClick		: function()
				{
					if (isLink)
					{
						Fever.Reader.toggleItemContent(itemId, itemOrLink.id);
					}
					else
					{
						Fever.Reader.toggleItemContent(itemId);
					};
				}
			};

			var saveItem =
			{
				text		: (isSaved ? 'Saved' : 'Save') + ' <span class="key">S</span>',
				selected 	: isSaved,
				onClick		: function()
				{
					if (!isLink)
					{
						Fever.Reader.toggleItemSaveState(itemId);
					};
				}
			};

			var unreadItem =
			{
				text : 'Mark as Unread',
				onClick  : function()
				{
					Fever.Reader.markItemAsUnread(itemId);
					Fever.Reader.ui.hasFocus = 'feeds';
					Fever.Reader.refocus();
				}
			};

			this.items =
			[
				excerpt,
				// unreadItem,
				saveItem,
				divider
			];

			// add user-defined services
			for (var i=0; i<Fever.Reader.services.length; i++)
			{
				this.items.push(
				{
					text : Fever.Reader.services[i][0] + '<span class="key">' + Fever.Reader.services[i][2] + '</span>',
					onClick : function(url)
					{
						return function() { Fever.Reader.sendToService(itemId, url); };
					}(Fever.Reader.services[i][1])
				});
			};
		}
	},
	sparks :
	{
		items :
		[
			{
				text : 'Edit&#8230; <span class="key">i</span>',
				onClick : function()
				{
					Fever.Reader.editSparks();
				}
			},
			{ divider : true },
			{
				text : 'Delete...',
				onClick : function()
				{
					Fever.addRemoteDialog('./?manage=delete-sparks');
				}
			}
		]
	},
	action :
	{
		items : [], // built dynamically by onOpen
		onOpen : function()
		{
			var divider		= { divider : true };
			var which		= (Fever.Reader.ui.hasFocus != 'groups' && Fever.Reader.ui.showFeeds && Fever.Reader.ui.feedId != 0) ? 'Feed' : 'Group';
			var whichLow	= which.toLowerCase();
			var disabled 	= true;

			if (Fever.Reader.ui.section == 1 || Fever.Reader.ui.section == 3)
			{
				var unreadCount = one('#' + whichLow + '-' + Fever.Reader.ui[whichLow + 'Id'] + ' em.unread-count');
				disabled = !unreadCount || !parseInt(unreadCount.innerHTML);
			};

			var unread =
			{
				text		: 'Unread most recently read <span class="key">z</span>',
				disabled	: Fever.Reader.totalItems == Fever.Reader.totalUnread,
				onClick		: function()
				{
					Fever.Reader.unreadRecentlyRead();
				}
			};

			var read =
			{
				text		: 'Mark current ' + which.toLowerCase() + ' as read <span class="key">a</span>',
				disabled 	: disabled,
				onClick		: function()
				{
					Fever.Reader.markActiveAsRead();
				}
			};

			var refreshAll =
			{
				text 	: 'Refresh all  <span class="key">r</span>',
				onClick 	: function()
				{
					Fever.Reader.refresh(1);
				}
			};

			var showFeeds =
			{
				text 		: 'Show feeds <span class="key">f</span>',
				selected 	: Fever.Reader.ui.showFeeds,
				onClick 	: function()
				{
					Fever.Reader.toggleFeeds();
				}
			};

			var showRead =
			{
				text 		: 'Show read <span class="key">u</span>',
				selected 	: Fever.Reader.ui.showRead,
				disabled	: Fever.Reader.ui.section == 2 || Fever.Reader.ui.section == 4,
				onClick 	: function()
				{
					Fever.Reader.toggleUnread();
				}
			};

			var newGroup =
			{
				text	: 'New group&#8230; <span class="key shift">n</span>',
				onClick		: function()
				{
					Fever.Reader.addGroup();
				}
			};

			var newFeed =
			{
				text	: 'New Feed&#8230; <span class="key">n</span>',
				onClick		: function()
				{
					Fever.Reader.addFeed();
				}
			};

			var importOPML =
			{
				text	: 'Import&#8230;',
				onClick		: function()
				{
					Fever.addRemoteDialog('./?manage=import');
				}
			};

			var exportOPML =
			{
				text	: 'Export&#8230;',
				onClick		: function()
				{
					Fever.addRemoteDialog('./?manage=export');
				}
			};

			var preferences =
			{
				text 	: 'Preferences&#8230; <span class="key">p</span>',
				onClick : function()
				{
					Fever.Reader.preferences();
				}
			};

			var blacklist =
			{
				text	: 'Blacklist&#8230; <span class="key shift">b</span>',
				onClick : function()
				{
					Fever.Reader.blacklist();
				}
			};

			var updates =
			{
				text 	: 'Check for updates&#8230;',
				onClick : function()
				{
					Fever.Reader.checkForUpdates();
				}
			};

			var changelog =
			{
				text 	: 'Changelog&#8230;',
				onClick : function()
				{
					var url = 'https://github.com/mcaskill/fever/blob/main/CHANGELOG.md';
					if (Fever.Reader.newWindow)
					{
						window.open(url);
					}
					else
					{
						window.location.href = url;
					};
				}
			};

			var shortcuts =
			{
				text 	: 'Keyboard Shortcuts&#8230;',
				onClick : function()
				{
					window.location.href = u('./?shortcuts');
				}
			};

			var extras =
			{
				text	: 'Extras&#8230;',
				onClick : function()
				{
					window.location.href = u('./?extras');
				}
			};

			var logout =
			{
				text 	: 'Logout',
				onClick : function()
				{
					window.location.href = u('./?logout');
				}
			};

			var empty =
			{
				text 	: 'Empty&#8230;',
				onClick : function()
				{
					window.location.href = u('./?empty');
				}
			};

			var uninstall =
			{
				text 	: 'Uninstall&#8230;',
				onClick : function()
				{
					window.location.href = u('./?uninstall');
				}
			};

			this.items =
			[
				unread,
				read,
				refreshAll,
				divider,
				showRead,
				showFeeds,
				divider,
				newGroup,
				newFeed,
				divider,
				importOPML,
				exportOPML,
				divider,
				preferences,
				blacklist,
				divider,
				updates,
				changelog,
				divider,
				shortcuts,
				extras,
				logout,
				divider,
				// empty,
				uninstall
			];
		}
	}
};
// a draggable interface
var Draggable =
{
	id	 : null,
	obj  : null,
	target : null,
	make : function(elem)
	{
		elem.onDragStart	= function(x, y){};
		elem.onDrag 		= function(x, y){};
		elem.onDragEnd 		= function(x, y){};

		elem.addEventListener('mousedown', Draggable.start, true);
	},
	start :function(e)
	{
		Draggable.obj 		= this;

		// allow drag-free normal clicks
		Draggable.id = window.setTimeout(function()
		{
			Draggable.obj.style.opacity	= 0.5;

			var mx = e.pageX;
			var my = e.pageY;
			var pos = getPos(Draggable.obj);

			Draggable.grabX = mx - pos.x;
			Draggable.grabY = my - pos.y;

			var x = mx - Draggable.grabX;
			var y = my - Draggable.grabY;

			var dragger 		= document.createElement('div');
			dragger.id			= 'dragger';
			dragger.innerHTML 	= Draggable.obj.innerHTML;
			document.body.appendChild(dragger);

			dragger.style.top	= y + 'px';
			dragger.style.left 	= x + 'px';

			Draggable.obj.onDragStart(mx, my);

			document.addEventListener('mousemove', Draggable.drag, true);
			Draggable.id = null;
		}, 150);

		document.addEventListener('mouseup', Draggable.end, true);
		e.preventDefault();
		return false;
	},
	drag : function(e)
	{
		var dragger = document.getElementById('dragger');

		var mx = px = e.pageX;
		var my = py = e.pageY;

		var x = mx - Draggable.grabX;
		var y = my - Draggable.grabY;
		var w = dragger.offsetWidth;
		var h = dragger.offsetHeight;

		var droppables = $('.droppable');
		Draggable.target = null;
		for (var i = 0; i < droppables.length; i++)
		{
			var droppable = droppables[i];
			var pos = getPos(droppable);
			var dw = droppable.offsetWidth;
			var dh = droppable.offsetHeight;

			droppable.className = droppable.className.replace(/ target\b/, '');

			// adjust for fixed positioning and a scrolled body
			if (hasFixedParent(droppable))
			{
				px = mx - window.pageXOffset;
				py = my - window.pageYOffset;
			};

			if
			(
				px >= pos.x &&
				px < pos.x + dw &&
				py >= pos.y &&
				py < pos.y + dh
			)
			{
				Draggable.target = droppable;
				droppable.className += ' target';
			};
		};

		dragger.style.top	= y + 'px';
		dragger.style.left 	= x + 'px';

		Draggable.obj.onDrag(mx, my);

		e.preventDefault();
		return false;
	},
	end : function(e)
	{
		// false alarm, this was just a normal click
		if (Draggable.id != null)
		{
			window.clearTimeout(Draggable.id);
			Draggable.id = null;
		}
		// finish drag
		else
		{
			var mx = e.pageX;
			var my = e.pageY;

			Draggable.obj.onDragEnd(mx, my);
			// alert(toSelector(Draggable.target));
			var dragger = document.getElementById('dragger');
			dragger.parentNode.removeChild(dragger);
			Draggable.obj.style.opacity = 1;
			Draggable.obj = null;
			if (Draggable.target != null)
			{
				Draggable.target.className = Draggable.target.className.replace(/ target\b/, '');
				Draggable.target = null;
			};

			document.removeEventListener('mousemove', Draggable.drag, true);
		};
		document.removeEventListener('mouseup', Draggable.end, true);
	}
};