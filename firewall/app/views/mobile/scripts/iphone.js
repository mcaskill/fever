window.setTimeout(function() { window.scrollTo(0, 0); }, 0);

Fever.iPhone =
{
	lastCachedOnTime : 0,
	lastRenderedOnTime	: 0,

	requestId : 0, // used to ignore stale XHR requests

	screen 		: 0,

	page		: 1,
	pageMaxed	: false,
	pageLoading	: false,
	spinners	: [],

	yOffset 	: [], // stores the position of previos screens
	routes		: ['groups'], // TODO: store routes better

	// will need to preserve state on the server side for paging to work...
	ui :
	{
		search 		: '',
		hotStart	: 0,
		hotRange	: 0
	},

	mobile :
	{
		route		: '',
		section 	: 0,
		groupId 	: 0,
		feedId		: 0,
		itemId		: 0,
		linkId		: 0,

		// shared with UI above
		search 		: '',
		hotStart	: 0,
		hotRange	: 0,

		showFeeds	: 1,
		showRead	: 0,

		readOnScroll 	: 1,
		readOnBackOut 	: 1,
		viewInApp		: 1,

		autoRead	: 1
	},

	setRoute : function(route)
	{
		if (this.routes[this.routes.length - 1] != route)
		{
			if (route == 'groups' || route == 'feeds')
			{
				this.pageMaxed = false;
			};

			this.mobile.route = route;
			this.routes.push(route);
		};
	},
	backOutRoute : function()
	{
		this.routes.pop();
		this.mobile.route = this.routes[this.routes.length - 1];
	},
	xhrPreprocessor : function(props)
	{
		var toPHP 		= function(str) { return str.replace(/([A-Z])/, '_$1').toLowerCase(); };
		var returnObj 	=
		{
			url					: './?xhr',
			screen 				: (this.screen + 1),
			useCurrentScreen 	: false,
			callback 			:
			{
				beforeInsert	: function(data)
				{
					if (m = data.responseText.match(/<!--REQUEST_ID:([0-9]+)-->/)) {
						if (m[1] != Fever.iPhone.requestId) return false; // cancel insert of stale requests
					};
				},
				afterInsert		: function(data)
				{
					Fever.iPhone.onContentLoaded();
					Fever.iPhone.nextScreen();
				}
			}
		};
		this.cancelLoading();

		props += ' showRead showFeeds';
		var mobile	= props.split(/\s+/);
		for (var i = 0; i < mobile.length; i++)
		{
			var prop = mobile[i];
			if (prop == 'useCurrentScreen')
			{
				returnObj.useCurrentScreen = true;
				continue;
			};
			returnObj.url += '&mobile[' + toPHP(prop) + ']=' + this.mobile[prop];
		};

		this.requestId++;
		returnObj.url += '&request_id='+this.requestId;

		if (returnObj.useCurrentScreen)
		{
			returnObj.screen = this.screen;
			returnObj.callback =
			{
				afterInsert : function(data)
				{
					window.scrollTo(0,0);

					Fever.iPhone.onContentLoaded();
					var screensContainer = one('#screens-container');
					var thisScreen = one('#screen-' + Fever.iPhone.screen);
					css(screensContainer, 'height', thisScreen.offsetHeight + 'px');
				}
			};
		};

		return returnObj;
	},
	reload : function()
	{
		var target = one('#fever .header .left');
		addClass(target, 'spinning');
		this.spinners.push(toSelector(target));

		one('#refresh').src = './?refresh';

		var url = './?xhr&mobile[route]=groups';
		XHR.get(url, one('#screen-0'), function()
		{
			Fever.iPhone.cacheFavicons();
			Fever.iPhone.onContentLoaded();
		});
	},
	loadRemote : function(props)
	{
		var xhrProperties = this.xhrPreprocessor(props);

		var url			= xhrProperties.url;
		var screen 		= xhrProperties.screen;
		var callback 	= xhrProperties.callback;

		if (window.event)
		{
			var e = window.event;
			if (e.currentTarget)
			{
				var target = e.currentTarget;
				// alert(toSelector(target));
				if (target.nodeName == 'FORM' || target.nodeName == 'SELECT')
				{
					target = target.parentNode;
				};
				addClass(target, 'spinning');
				// alert(toSelector(target));
				this.spinners.push(toSelector(target));
			};
		};

		this.pageLoading = true;
		XHR.get(url, one('#screen-' + screen), callback);
		return false;
	},
	loadSection : function(section)
	{
		window.event.stopPropagation();
		this.setRoute((section == 0) ? 'links' : this.mobile.showFeeds ? 'feeds' : 'items');
		this.mobile.section		= section;
		this.mobile.feedId		= 0;
		return this.loadRemote('section search hotStart hotRange feedId route');
	},
	loadGroup : function(groupId)
	{
		this.setRoute(this.mobile.showFeeds ? 'feeds' : 'items');
		this.mobile.section 	= 1;
		this.mobile.groupId 	= groupId;
		this.mobile.feedId 		= 0;
		return this.loadRemote('section groupId feedId route');
	},
	loadFeed : function(feedId)
	{
		this.setRoute('items');
		this.mobile.feedId 		= feedId;
		return this.loadRemote('section groupId feedId route');
	},
	loadItem : function(itemId)
	{
		if (arguments.length > 1)
		{
			this.mobile.linkId = arguments[1];
		};
		this.setRoute('item');
		this.mobile.itemId 	= itemId;
		return this.loadRemote('section groupId feedId itemId route');
	},
	loadNextItem : function()
	{
		var isLink	= this.mobile.section == 0;
		var id 		= (isLink) ? '#link-' + this.mobile.linkId + '-item-' + this.mobile.itemId : '#item-' + this.mobile.itemId;
		var linkRE	= new RegExp('link-' + this.mobile.linkId + '-item-');
		var itemRE	= /item-/;
		var item 	= one(id);

		var targetItem = nextSibling(item);
		if (targetItem)
		{
			var targetItemId = targetItem.id.replace((isLink) ? linkRE : itemRE, '');
			this.markItemAsRead(targetItemId);
			this.setRoute('item');
			this.mobile.itemId 	= targetItemId;
			return this.loadRemote('section groupId feedId itemId route useCurrentScreen');
		}
		else if (!this.pageMaxed)
		{
			this.loadNextPage();

			// in the loadNextPage callback
			// this.loadNextItem();
		};
	},
	loadPreviousItem : function()
	{
		var isLink	= this.mobile.section == 0;
		var id 		= (isLink) ? '#link-' + this.mobile.linkId + '-item-' + this.mobile.itemId : '#item-' + this.mobile.itemId;
		var linkRE	= new RegExp('link-' + this.mobile.linkId + '-item-');
		var itemRE	= /item-/;
		var item 	= one(id);

		var targetItem = previousSibling(item);

		if (targetItem)
		{
			var targetItemId = targetItem.id.replace((isLink) ? linkRE : itemRE, '');
			this.markItemAsRead(targetItemId);
			this.setRoute('item');
			this.mobile.itemId 	= targetItemId;
			return this.loadRemote('section groupId feedId itemId route useCurrentScreen');
		};
	},
	loadWebView : function(url)
	{
		if (!this.mobile.viewInApp) return true;

		css(one('#screens-container'), 'display', 'none');
		one('#viewport').content = 'width=980,user-scalable=yes,minimum-scale=0,maximum-scale=100,initial-scale=0.32,date='+ (new Date()).getTime();

		this.yOffset.push(window.pageYOffset);
		window.scrollTo(0,0);

		one('#iframe').src = url;
		one('#safari').href = url;

		css(one('#webview'), 'display', 'block');
		this.setRoute('webview');

		window.resizeTo(980,screen.height);

		return false;
	},
	unloadWebView : function()
	{
		css(one('#webview'), 'display', 'none');
		one('#iframe').src = 'about:blank';
		// removed user-scalable=no but kept maximum- and minimum-scales to allow manual pinch or double-tap to desired scale
		one('#viewport').content = 'width=device-width,minimum-scale=1.0,maximum-scale=1.0,initial-scale=1.0,date='+ (new Date()).getTime();

		var oldYOffset = this.yOffset.pop();
		window.scrollTo(0, oldYOffset);

		css(one('#screens-container'), 'display', 'block');
		this.backOutRoute();
	},
	reloadHot : function()
	{
		this.pageMaxed = false;
		var hotStart = one('#menu-hot-start').value;
		var hotRange = one('#menu-hot-range').value;

		if (hotStart == '-' || hotStart == '--') return false;
		if (hotRange == '-' || hotRange == '--') return false;

		if (hotStart != this.mobile.hotStart || hotRange != this.mobile.hotRange)
		{
			this.setRoute('links');
			this.mobile.hotStart = hotStart;
			this.mobile.hotRange = hotRange;

			return this.loadRemote('section hotStart hotRange route useCurrentScreen');
		}
	},
	loadNextPage : function()
	{
		if (!this.pageMaxed && !this.pageLoading)
		{
			this.pageLoading = true;
			this.page++;

			var xhrProperties 	= this.xhrPreprocessor('section groupId feedId route');
			var url				= xhrProperties.url + '&page=' + this.page;

			// this.markAllVisibleItemsAsRead();
			XHR.get(url, null, function(data)
			{
				var tmp, content;
				if (Fever.iPhone.mobile.section)
				{
					tmp = document.createElement('ul');
					content = one('#items div.box ul.list');
				}
				else // hot
				{
					tmp = document.createElement('div');
					content = one('#hot');
				};
				tmp.innerHTML = data.responseText;

				var uniqueChildren = 0;
				while (tmp.childNodes.length > 0)
				{
					var child = tmp.removeChild(tmp.firstChild);
					if (child.nodeType == 3 || (child.id && document.getElementById(child.id)))
					{
						// skip textnodes and duplicate items/links
						continue;
					};
					content.appendChild(child);
					uniqueChildren++;
				};

				if (uniqueChildren == 0)
				{
					Fever.iPhone.pageMaxed = true;
				};

				Fever.iPhone.pageLoading = false;
				Fever.iPhone.onContentLoaded();
				if (one('#item'))
				{
					// alert('updating item');
					Fever.iPhone.onItemLoaded(Fever.iPhone.mobile.itemId);
				};
			});
			return false;
		};
	},
	loadSearch : function()
	{
		var q = one('#q');
		this.mobile.search = q.value;
		q.blur();
		window.scrollTo(0,99999);
		this.loadSection(4);
		return false;
	},

	cancelLoading : function()
	{
		this.pageLoading = false;
		while (this.spinners.length)
		{
			var selector = this.spinners.pop();
			var obj = one(selector);
			if (obj)
			{
				removeClass(obj, 'spinning');
			};
		};
	},
	onContentLoaded : function()
	{
		this.cancelLoading();
		this.resizeScreenContainer();
		this.openInWebView();
	},
	onItemLoaded : function(itemId)
	{
		var isLink	= this.mobile.section == 0;
		var id 		= (isLink) ? '#link-' + this.mobile.linkId + '-item-' + itemId : '#item-' + itemId;
		var item 	= one(id);
		if (item)
		{	var previousItem 	= previousSibling(item);
			var previousDisplay = (!previousItem) ? 'none' : '';
			css(one('#prev-item'), 'display', previousDisplay);

			var nextItem 	= nextSibling(item);
			var nextDisplay = (!nextItem) ? 'none' : '';
			css(one('#next-item'), 'display', nextDisplay);

			if (!nextItem && !isLink)
			{
				this.mobile.route = 'items';
				this.loadNextPage();
				this.mobile.route = 'item';
			};
		};
	},
	onIframeLoaded : function()
	{
		var iframe = one('#iframe');
		var title = one('#webview div.title');
		if (iframe.src != 'about:blank')
		{
			// alert('hide-spinner');
			removeClass(title, 'spinning');
		}
		else
		{
			// alert('show-spinner');
			addClass(title, 'spinning');
		}
	},

	nextScreen : function()
	{
		var thisScreen = one('#screen-' + this.screen);
		var screens = one('#screens');
		var screensContainer = one('#screens-container');
		var screenWidth = screensContainer.offsetWidth;
		var targetScreenNum = this.screen + 1;
		var targetScreen = one('#screen-' + targetScreenNum);
		this.yOffset.push(window.pageYOffset);
		css(targetScreen, 'top', window.pageYOffset + 'px');
		var targetHeight = (targetScreen.offsetHeight > thisScreen.offsetHeight) ? targetScreen.offsetHeight : thisScreen.offsetHeight;
		css(screensContainer, 'height', targetHeight + 'px');

		Fever.animate(screens, 'left', screenWidth * targetScreenNum * -1, function()
		{
			if (css(targetScreen, 'top') != 0)
			{
				for (var i=0; i<2; i++)
				{
					if (i==0) targetScreen.style.top = 0;
					if (i==1) window.scrollTo(0,0);
				};
			};
			css(screensContainer, 'height', targetScreen.offsetHeight + 'px');
		});
		this.screen = targetScreenNum;
	},
	previousScreen : function()
	{
		var thisScreen = one('#screen-' + this.screen);
		var screens = one('#screens');
		var screensContainer = one('#screens-container');
		var screenWidth = screensContainer.offsetWidth;
		var targetScreenNum = this.screen - 1;
		var targetScreen = one('#screen-' + targetScreenNum);
		var targetHeight = (targetScreen.offsetHeight > thisScreen.offsetHeight) ? targetScreen.offsetHeight : thisScreen.offsetHeight;
		css(screensContainer, 'height', targetHeight + 'px');
		var oldYOffset = this.yOffset.pop();
		css(targetScreen, 'top', '-' + oldYOffset + 'px');

		this.markAllVisibleItemsAsRead();
		this.backOutRoute();

		Fever.animate(screens, 'left', screenWidth * targetScreenNum * -1, function()
		{
			for (var i=0; i<2; i++)
			{
				if (i==0) targetScreen.style.top = 0;
				if (i==1) window.scrollTo(0,oldYOffset);
			};

			// empty innerHTML of any screens to the right once
			var i = 3;
			while (i > Fever.iPhone.screen)
			{
				one('#screen-' + i).innerHTML = '';
				i--;
			};

			css(screensContainer, 'height', targetScreen.offsetHeight + 'px');
		});
		this.screen = targetScreenNum;

		if (this.screen == 0)
		{
			this.reload();
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

	markItemAsRead : function(itemId)
	{
		var items = $('.item-' + itemId + ', #item-' + itemId);
		for (var j = 0; j < items.length; j++)
		{
			var anItem = items[j];
			removeClass(anItem, 'unread');
			addClass(anItem, 'read');

			// update feed (and superfeed) unread counts
			var feedIds = [0, one('#item-' + itemId + ' a, .item-' + itemId + ' a').className.replace(/feed-/, '')];
			for (var i = 0; i < feedIds.length; i++)
			{
				var feedId		= feedIds[i];

				var feedUnread 	= one('#feed-' + feedId + ' span.unread-count');
				if (feedUnread)
				{
					var unreadCount = parseInt(feedUnread.innerHTML);
					if (unreadCount > 0)
					{
						unreadCount--;
						feedUnread.innerHTML = unreadCount;
						if (!unreadCount)
						{
							var feed = feedUnread.parentNode;
							removeClass(feed, 'unread');
							addClass(feed, 'read');
						};
					};
				};
			};

			// update group unread counts
			if (this.groupIdsByFeedId[feedId])
			{
				for (var i = 0; i < this.groupIdsByFeedId[feedId].length; i++)
				{
					var groupId 	= this.groupIdsByFeedId[feedId][i];
					var groupUnread = one('#group-' + groupId + ' span.unread-count');
					if (groupUnread)
					{
						var unreadCount = parseInt(groupUnread.innerHTML);
						if (unreadCount > 0)
						{
							unreadCount--;

							groupUnread.innerHTML = unreadCount;
							if (!unreadCount)
							{
								var group = groupUnread.parentNode;
								removeClass(group, 'unread');
								addClass(group, 'read');
							};
						};
					};
				};
			};
		};

		if (arguments.length == 1)
		{
			XHR.get('./?manage=statuses&mark=item&as=read&id=' + itemId);
		};
	},
	markItemsAsRead : function(itemIds)
	{
		var idsQuery = '';
		for (var j = 0; j < itemIds.length; j++)
		{
			idsQuery += '&id[]='+itemIds[j];
			this.markItemAsRead(itemIds[j], false);
		};

		XHR.get('./?manage=statuses&mark=items&as=read' + idsQuery);
	},
	markAllVisibleItemsAsRead: function()
	{
		if (this.mobile.route == 'items' && (this.mobile.section == 1 || this.mobile.section == 3) && this.mobile.readOnBackOut)
		{
			var items = $('#items div.box ul.list li.unread');
			var itemIds = [];

			for (var i = 0; i < items.length; i++)
			{
				var anItem = items[i]
				var itemId = parseInt(anItem.id.replace(/item-/, ''));
				itemIds.push(itemId);
			};

			if (itemIds.length)
			{
				Fever.iPhone.markItemsAsRead(itemIds);
			};
		};
	},

	toggleItemSaveState : function(itemId)
	{
		var saved		= one('#section-2 span.saved-count');
		var savedCount 	= parseInt(saved.innerHTML);
		var item		= one('#item .item');
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

		// TODO: update feed (and superfeed) saved
		var feedIds = [0, one('#item-' + itemId + ' a, .item-' + itemId + ' a').className.replace(/feed-/, '')];
		for (var i = 0; i < feedIds.length; i++)
		{
			var feedId		= feedIds[i];
			var feedSaved 	= one('#feed-' + feedId + ' span.saved-count');
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
					addClass(feedSaved.parentNode, 'read');
				}
				else
				{
					removeClass(feedSaved.parentNode, 'read');
					removeClass(feedSaved.parentNode, 'unread');
				};

				feedSaved.innerHTML = savedCount;
			};
		};

		XHR.get('./?manage=statuses&mark=item&as=' + state + '&id=' + itemId);
	},

	services : [],
	buildUrl : function(template)
	{
		var link 	= one('#item div.item div.meta h1 a');
		var item 	= one('#item div.item div.item-content');
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

		template = template.replace(/%t/g, encodeURIComponent(title));
		template = template.replace(/%u/g, encodeURIComponent(url));
		template = template.replace(/%e/g, encodeURIComponent(excerpt));
		return template;
	},
	shareTo : function(service)
	{
		var url = this.buildUrl(this.services[service][1]);
		if (url.match(/^mailto:/))
		{
			// does not send to MobileMail.app
			window.location.href = url;
		}
		else
		{
			// not as useful as the desktop equivalent
			this.loadWebView(url);
		};
		Fever.dismissDialog();
	},
	displayShareTo: function()
	{
		Fever.addDialog(one('#dialog-share-to').innerHTML);
	},

	displayPreferences : function()
	{
		Fever.addDialog(one('#dialog-preferences').innerHTML);
		Fever.checkCheckbox('#action-show-feeds', this.mobile.showFeeds);
		Fever.checkCheckbox('#action-show-read', this.mobile.showRead);
		Fever.checkCheckbox('#action-read-scroll', this.mobile.readOnScroll);
		Fever.checkCheckbox('#action-read-back', this.mobile.readOnBackOut);
		Fever.checkCheckbox('#action-elsewhere', this.mobile.viewInApp);
	},
	dismissPreferences : function()
	{
		var url = './?xhr';
		url += '&mobile[show_feeds]='+this.mobile.showFeeds;
		url += '&mobile[show_read]='+this.mobile.showRead;
		url += '&mobile[read_on_scroll]='+this.mobile.readOnScroll;
		url += '&mobile[read_on_back_out]='+this.mobile.readOnBackOut;
		url += '&mobile[view_in_app]='+this.mobile.viewInApp;
		XHR.get(url);
		Fever.dismissDialog();
	},
	toggleFeeds : function(elem)
	{
		Fever.toggleCheckbox(elem);
		this.mobile.showFeeds = one('#action-show-feeds').checked ? 1 : 0;
	},
	toggleUnread : function(elem)
	{
		Fever.toggleCheckbox(elem);
		this.mobile.showRead = one('#action-show-read').checked ? 1 : 0;
		if (this.mobile.showRead)
		{
			removeClass(one('#screen-0'), 'hide-read');
		}
		else
		{
			addClass(one('#screen-0'), 'hide-read');
		};
		this.resizeScreenContainer();
	},
	toggleReadOnScroll : function(elem)
	{
		Fever.toggleCheckbox(elem);
		this.mobile.readOnScroll = one('#action-read-scroll').checked ? 1 : 0;
	},
	toggleReadOnBackOut : function(elem)
	{
		Fever.toggleCheckbox(elem);
		this.mobile.readOnBackOut = one('#action-read-back').checked ? 1 : 0;
	},
	toggleElsewhere	: function(elem)
	{
		Fever.toggleCheckbox(elem);
		this.mobile.viewInApp = one('#action-elsewhere').checked ? 1 : 0;
	},
	unreadRecentlyRead : function()
	{
		XHR.get('./?manage=unread-read', null, function(){ Fever.iPhone.reload(); });
		Fever.dismissDialog();
	},

	resizeScreenContainer : function()
	{
		css(one('#screens'), 'min-height', (window.innerHeight+60)+'px');
		var thisScreen = one('#screen-' + this.screen);
		if (thisScreen)
		{
			var screensContainer = one('#screens-container');
			css(screensContainer, 'height', thisScreen.offsetHeight + 'px');
		};
	},
	openInWebView : function()
	{
		var links = $('a[rel=external], div.item-content a');

		for (var i = 0; i < links.length; i++)
		{
			links[i].target 	= '_blank';
			links[i].onclick 	= function()
			{
				return Fever.iPhone.loadWebView(this.href);
			};
		};
	},

	releaseRefresh : function()
	{
		one('#refresh').src = 'about:blank';
	},

	touchMoved		: false,
	startTouchPos 	: 0,
	finishTouchPos	: 0,
	onSwipeLeft : function()
	{
		// alert('left');
		if (one('#item'))
		{
			this.loadNextItem();
		};
	},
	onSwipeRight : function()
	{
		// alert('right');
		if (one('#item'))
		{
			this.loadPreviousItem();
		};
	},
	detectPortrait : function() {
		var inner_width = Math.round(window.innerWidth)
		var inner_height = Math.round(window.innerHeight)
		var is_portrait = (inner_height >= inner_width);
		document.body.className = is_portrait ? 'portrait' : 'landscape';
	},
	onOrientationChange : function()
	{
		this.detectPortrait();

		css(one('#screens'), 'left', (window.innerWidth * this.screen * -1) + 'px');
		this.resizeScreenContainer();
	},
	onscroll : function()
	{
		if (window.previousPageYOffset != window.pageYOffset)
		{
			window.previousPageYOffset = window.pageYOffset;

			if (window.previousPageYOffset + window.innerHeight >= document.body.scrollHeight)
			{
				if (Fever.iPhone.mobile.route == 'items' || Fever.iPhone.mobile.route == 'links')
				{
					Fever.iPhone.loadNextPage();
				};
			};

			if (Fever.iPhone.mobile.route == 'items')
			{
				var items = $('#items div.box ul.list li.unread');
				var itemIds = [];

				for (var i = 0; i < items.length; i++)
				{
					var anItem = items[i];
					var pos = getPos(anItem);
					var adjY = pos.y + Math.floor(anItem.offsetHeight * 0.75);

					if (adjY <= window.previousPageYOffset)
					{
						var itemId = parseInt(anItem.id.replace(/item-/, ''));
						itemIds.push(itemId);
					}
					else
					{
						break;
					};
				};

				if (itemIds.length && Fever.iPhone.mobile.readOnScroll)
				{
					Fever.iPhone.markItemsAsRead(itemIds);
				};
			};
		};
	},
	onload : function()
	{
		this.onOrientationChange();

		screen.orientation.addEventListener('change', function()
		{
			Fever.iPhone.onOrientationChange();

		}, false);

		window.previousPageYOffset = window.pageYOffset;
		window.addEventListener('scroll', function(e)
		{
			Fever.iPhone.onscroll();

		}, false);

		window.currentWidth = window.innerWidth;
		if (window.navigator.standalone)
		{
			window.setInterval(function()
			{
				if (window.currentWidth != window.innerWidth)
				{
					window.currentWidth = window.innerWidth;
					Fever.iPhone.onOrientationChange();
				};
				Fever.iPhone.onscroll();

			}, 500);
		};
		this.cacheFavicons();

		document.body.addEventListener('touchstart', function(e)
		{
			var touch = e.touches[0];
			Fever.iPhone.startTouchPos = touch.pageX;
			Fever.iPhone.touchMoved = false;
		}, false);

		document.body.addEventListener('touchmove', function(e)
		{
			var touch = e.touches[0];
			Fever.iPhone.finishTouchPos = touch.pageX;
			Fever.iPhone.touchMoved = true;
		}, false);

		document.body.addEventListener('touchend', function(e)
		{
			if (Fever.iPhone.touchMoved)
			{
				if ((Fever.iPhone.finishTouchPos - Fever.iPhone.startTouchPos) > 150)
				{
					Fever.iPhone.onSwipeRight();
				}
				else if ((Fever.iPhone.startTouchPos - Fever.iPhone.finishTouchPos) > 150)
				{
					Fever.iPhone.onSwipeLeft();
				};
			};

			Fever.iPhone.startTouchPos 	= 0;
			Fever.iPhone.finishTouchPos = 0;
		}, false);

		this.resizeScreenContainer();
	}
};

// silence errors from missing desktop functions
Fever.Reader = {
	updateRefreshProgress : function(){},
	updateRefreshProgress : function(){},
	updateFaviconCache : function(){},
	updateAfterRefresh : function(){}
};

window.addEventListener('load', function() { Fever.iPhone.onload(); }, false);
