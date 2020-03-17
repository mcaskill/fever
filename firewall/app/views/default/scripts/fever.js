var Fever =
{
	isIPad				: false,
	isLion				: window.navigator.userAgent.match(/Mac OS X 10_7/), // skinny scrollbars
	Reader				: {},
	autoScrolling		: true,
	scrollers 			: {},
	scrollId 			: null,
	scrollTo : function()
	{
		if (arguments.length)
		{
			var target		= arguments[0];
			var parent		= (arguments.length > 1) ? arguments[1] : document.body;
			var callback	= (arguments.length > 2) ? arguments[2] : null;

			if (parent == null)
			{
				parent = document.body;
			};

			// don't try to scroll window on iPad
			// disabled now that iPad Safari is more capable
			// if (this.isIPad && parent == document.body)
			// {
			// 	if (typeof callback == 'function')
			// 	{
			// 		callback();
			// 	};
			// 	return;
			// };

			// add our scrollers, one per unique parent
			this.scrollers[toSelector(parent)] = [target, parent, callback];

			// start the setInterval if not running
			if (!this.scrollId)
			{
				this.scrollId = window.setInterval(function(){ Fever.scrollTo(); }, 50);

				// first call
				this.scrollTo();
			};
		}
		else if (this.scrollId)
		{
			this.autoScrolling = false;
			// operate on all scrollers
			var activeScrollers = 0;
			for (var prop in this.scrollers)
			{
				if (typeof this.scrollers[prop] != 'undefined')
				{
					var target 		= this.scrollers[prop][0];
					var parent 		= this.scrollers[prop][1];
					var callback	= this.scrollers[prop][2];

					if (typeof target != 'undefined' && typeof parent != 'undefined')
					{
						if (parent.nodeName == 'BODY' && document.documentElement && !navigator.userAgent.match(/webkit/i))
						{
							parent = document.documentElement;
						};

						var parentY			= getPos(parent).y;
						var targetY 		= (typeof target != 'number') ? getPos(target).y : parseInt(target) - parent.scrollTop; // minus the *current* scrollTop
						var targetAbsY		= parent.scrollTop + targetY;
						var visibleHeight	= (parent.nodeName.match(/(BODY|HTML)/)) ? window.innerHeight : parent.offsetHeight;
						var offsetY			= (parent.nodeName.match(/(BODY|HTML)/)) ? 0 : parentY;

						if (parent.scrollHeight - targetAbsY + offsetY < visibleHeight)
						{
							targetAbsY 	= parent.scrollHeight - visibleHeight;
							targetY		= targetAbsY - parent.scrollTop;
						}
						else
						{
							targetY -= offsetY;
						};

						var distance		= targetY / 2;
						var newY			= parent.scrollTop + distance;

						if (Math.abs(distance) <= 1)
						{
							delete this.scrollers[prop];
							parent.scrollTop = parent.scrollTop + targetY;

							if (typeof callback == 'function')
							{
								callback();
							};
						}
						else
						{
							activeScrollers++;
							parent.scrollTop = newY;
						};
					};
				};
			};

			// when none are left, clearInterval
			if (!activeScrollers)
			{
				window.clearInterval(this.scrollId);
				this.scrollId = null;
				this.autoScrolling = true;
			};
		};
	},
	clearScrollers : function()
	{
		this.scrollers = {};
		window.clearInterval(this.scrollId);
		this.scrollId = null;
	},

	menuArgs : [], // set when menu method is called
	menuControllers : {},
	helpId		: null,
	displayMenu : function(e)
	{
		// store the arguments for later use
		this.menuArgs	= toArray(arguments);
		var menuName	= this.menuArgs[1];

		var menuController = this.menuControllers[menuName];
		if (menuController.onOpen)
		{
			menuController.onOpen();
		};

		var elem = this.menuArgs[0];

		// build menu
		var ul	= one('#menu-options');
		for (var i = 0; i < menuController.items.length; i++)
		{
			var item 	= menuController.items[i];
			var li 		= document.createElement('li');

			if (item.divider)
			{
				addClass(li, 'divider');
				// li.className = 'divider';
			}
			else
			{
				li.innerHTML = item.text;

				if (item.disabled)
				{
					addClass(li, 'disabled');
					//li.className += ' disabled';
				}
				else if (item.onClick)
				{
					li.onclick = item.onClick;
				}
				else if (menuController.onClick)
				{
					li.onclick = menuController.onClick;
				};

				if (item.selected)
				{
					addClass(li, 'selected');
					//li.className += ' selected';
				}

				// store a custom value
				if (item.value)
				{
					li.value = item.value;
				};
			};
			ul.appendChild(li);
		};

		var container 	= one('#menu-container');
		css(container, 'display', 'block');

		// figure out where we are on the page
		var menu	= one('#menu');
		var h		= menu.offsetHeight;
		var w		= menu.offsetWidth;
		var pos		= getPos(elem);
		var x 		= pos.x;
		var y 		= pos.y - 4; // minus padding-top
		var wh 		= window.innerHeight;
		var ww 		= window.innerWidth;

		// keep menu inside the window
		var safety = 24; // 16 for scrollbars, 8 for aesthetics
		if (y + h + safety > wh)
		{
			y = wh - h - safety;
		};

		if (x + w + safety > ww)
		{
			x = ww - w - safety;
		};

		if (this.isIPad)
		{
			// iPad scrolls entire UI (not in iOS 5)
			// x += window.pageXOffset;
			// y += window.pageYOffset;
		};

		css(menu, 'top',  y + 'px');
		css(menu, 'left', x + 'px');
	},
	dismissMenu : function()
	{
		var container		= one('#menu-container');
		if (css(container, 'display') == 'block')
		{
			var elem 			= this.menuArgs[0];
			var menuName		= this.menuArgs[1];
			var menuController 	= this.menuControllers[menuName];

			css(container, 'display', 'none');

			// reset positioning
			var menu = one('#menu');
			css(menu, 'top', 'auto');
			css(menu, 'left', 'auto');

			// gut
			if (menuController.onDismiss)
			{
				menuController.onDismiss();
			};
			one('#menu-options').innerHTML = '';
			this.menuArgs = [];
		};
	},
	displayHelp : function(elem, helpName)
	{
		document.addEventListener('mousemove', Fever.dismissHelp, true);
		Fever.helpId = window.setTimeout(function()
		{
			// insert requested help text
			one('#help-text').innerHTML = one('#help-' + helpName).innerHTML;

			var container 	= one('#help-container');
			css(container, 'display', 'block');

			// figure out where we are on the page
			var help	= one('#help');
			var eh		= elem.offsetHeight;
			var hw		= help.offsetWidth;
			var hh		= help.offsetHeight;
			var pos		= getPos(elem);
			var wh 		= window.innerHeight;
			var ww 		= window.innerWidth;
			var dangle	= one('#help-dangle');

			var hx		= pos.x + 4 + 13;
			var hy		= pos.y - hh/2 + eh/2;
			var dx		= pos.x - 4;
			var dy		= pos.y - 16;

			// keep help inside the window
			if (hy < 12)
			{
				hy = 12;
			};

			css (dangle, 'top', dy + 'px');
			css (dangle, 'left', dx + 'px');
			css(help, 'top',  hy + 'px');
			css(help, 'left', hx + 'px');
		}, 500);
	},
	dismissHelp : function(e)
	{
		if (!e.target.className.match(/\bhelp\b/))
		{
			window.clearTimeout(Fever.helpId);
		};

		// alert(toSelector(e.target));
		if (e.target.id == 'help-container')
		{
			document.removeEventListener('mousemove', Fever.dismissHelp, true);

			css(one('#help-container'), 'display', 'none');
		};
	},

	dialogs : [],
	addDialog : function(html)
	{
		this.dialogs.push(html);
		this.displayNextDialog();
	},
	addRemoteDialog : function(url)
	{
		XHR.get(url, null, function(request)
		{
			Fever.addDialog(request.responseText);
		});
		return false;
	},
	displayNextDialog : function()
	{
		var container = one('#dialog-container');
		// if there are any notices to display and one is not currently being displayed
		if (this.dialogs.length && css(container, 'display') == 'none')
		{
			var dialog = one('#dialog');
			dialog.innerHTML = this.dialogs.shift();
			css(container, 'display', 'block');
			this.armTabs();
			this.selectFirst('#dialog input[type=text], #dialog .btn.default');
			this.maximizeSelect();
		};
	},
	dismissDialog : function()
	{
		 // a split-second delay is required to allow Firefox to submit a form successfuly before dismissing
		window.setTimeout(function()
		{
			var container = one('#dialog-container');
			css(container, 'display', 'none');

			var dialog = one('#dialog');
			dialog.innerHTML = '';
			Fever.displayNextDialog();

		},50);
	},
	onDialogLoaded : function()
	{
		this.armTabs();
	},

	selectFirst : function(selector)
	{
		var firstInput = one(selector);
		if (firstInput)
		{
			firstInput.focus();
			if (firstInput.select)
			{
				firstInput.select();
			};
		};
	},
	maximizeSelect : function()
	{
		var dialog	= one('#dialog');
		var select 	= one('#dialog select[multiple]');

		// reset
		if (dialog && select)
		{
			select.size = 0;
			css(select, 'height', 'auto');

			var dp		= getPos(dialog);
			var sp		= getPos(select);
			var wh		= window.innerHeight;
			var so		= sp.y-dp.y;
			var bh		= dialog.offsetHeight - (so+select.offsetHeight);
			var nh		= wh - (dp.y*2 + so + bh);

			select.size = select.options.length;
			if (select.offsetHeight > nh)
			{
				css(select, 'height', nh+'px');
			};
		};
	},
	armTabs : function()
	{
		var tabs = $('#dialog ul.tabs a');
		for (var i=0; i < tabs.length; i++)
		{
			tabs[i].onclick = function()
			{
				removeClass(one('#dialog ul.tabs li.active'), 'active');
				removeClass(one('#dialog div.tab.active'), 'active');
				addClass(this.parentNode, 'active');
				addClass(one(this.href.replace(/^[^#]*/, '')), 'active');
				Fever.selectFirst('#dialog input[type=text], #dialog .btn.default');
				return false;
			};
		};
	},

	// for pages
	onload : function()
	{
		this.selectFirst('input[type=text], a.btn.default');
	}
};

Fever.menuControllers.feedlet =
{
	menus : {}, // generated by Fever
	items : [], // built dynamically by onOpen
	onOpen : function()
	{
		var menu		= Fever.menuArgs[0];
		var menuName 	= Fever.menuArgs[2];
		var selected	= (typeof menu.value != 'undefined') ? menu.value : Fever.menuArgs[3];

		this.items = this.menus[menuName];
		for (var i = 0; i < this.items.length; i++)
		{
			this.items[i].selected = (this.items[i].value == selected) ? true : false;
		};

		menu.value = selected;
	},
	onClick : function(e)
	{
		var menu		= Fever.menuArgs[0];
		var menuName 	= Fever.menuArgs[2];
		var feedInput	= one('#' + menuName);

		// only perform action if menu.value has changed
		if (menu.value != this.value)
		{
			menu.value = this.value;
			var url = Fever.menuControllers.feedlet.menus[menuName][menu.value].url;
			feedInput.value = url;
		};
		feedInput.checked = true;
	}
};

// remove HTML markup
function stripHTML(html)
{
	return html.replace(/<(?:.|\s)*?>/g, '');
};
// decode HTML entities
function decodeEntities(html)
{
	var textarea = document.createElement('textarea');
	textarea.innerHTML = html;
	return textarea.value;
};
// onscreen debugger
function debug(msg)
{
	var container = one('#debug');
	if (!container)
	{
		// the debug div
		var div = document.createElement('div');
		div.id = 'debug';
		document.body.appendChild(div);

		var button = document.createElement('input');
		button.type = 'submit';
		button.id = 'debug-clear';
		button.value = 'Close';
		button.onclick = function()
		{
			var debug = one('#debug');
			debug.parentNode.removeChild(debug);
		};
		div.appendChild(button);

		// the search filter
		var input = document.createElement('input');
		input.id = 'debug-filter';
		input.type = 'search';
		input.onkeyup = input.onclick = function()
		{
			var filters = this.value.split(' ');

			var ps = $('#debug li p');
			for (var i=0; i<ps.length; i++)
			{
				var p = ps[i];
				var li = p.parentNode;
				var display = true;
				for (var j=0;j<filters.length; j++)
				{
					var filter = filters[j];
					if (filter.substr(0,1) == '-')
					{
						filter = filter.substr(1);
						if (p.innerHTML.match(filter))
						{
							display = false;
							break;
						};
					}
					else
					{
						if (!p.innerHTML.match(filter))
						{
							display = false;
							break;
						};
					};
				};
				li.style.display = display ? 'block' : 'none';
			};
		};
		div.appendChild(input);

		// the list
		var ul = document.createElement('ul');
		div.appendChild(ul);
	};

	var list = one('#debug ul');
	var li = document.createElement('li');
	msg = window.decodeURIComponent(msg);
	li.innerHTML = msg;

	if (msg.match('New instance of Fever'))
	{
		li.className = 'debug-restart';
	}

	list.appendChild(li);
	Fever.scrollTo(li,list);
};
// passes errors query to url if present on current page
function u(url)
{
	if (window.location.href.match(/\berrors\b/))
	{
		url += (url.indexOf('?') != -1 ? '&' : '?') + 'errors';
	}
	return url;
}
// CSS selector
function $()
{
	var selected = new Array();

	var parser	= function(fullSelector, parents)
	{
		var selected	= new Array();
		var selectors	= fullSelector.split(/,\s*/);

		for (var i = 0; i < selectors.length; i++)
		{
			var tmpSelected = Array();
			var split		= selectors[i].match(/([^ ]+) ?(.*)?/);
			var selector	= (typeof split[1] == 'undefined' || split[1] == '') ? '' : split[1];
			var remainder	= (typeof split[2] == 'undefined' || split[2] == '') ? '' : split[2];
			var breakdown	= selector.match(/^([a-z0-9]+)?(#([-_a-z0-9]+))?((\.[-_a-z0-9]+)*)?((\[[a-z]+(=[^\]]+)?\])*)?((:[-_a-z0-9]+)*)?$/i);

			var tag			= (typeof breakdown[1] == 'undefined' || breakdown[1] == '') ? '*'	: breakdown[1].toUpperCase();
			var id			= (typeof breakdown[3] == 'undefined' || breakdown[3] == '') ? '' 	: breakdown[3];
			var classes		= (typeof breakdown[4] == 'undefined' || breakdown[4] == '') ? '.' 	: breakdown[4];
			var attributes	= (typeof breakdown[6] == 'undefined' || breakdown[6] == '') ? '[]' : breakdown[6];
			var pseudos		= (typeof breakdown[9] == 'undefined' || breakdown[9] == '') ? ':' 	: breakdown[9];

			var attributeValues = new Array();
			var attributeNames	= new Array();
			classes 	= classes.substring(1, classes.length).split('.');
			attributes	= attributes.substring(1, attributes.length -1).split('][');
			pseudos		= pseudos.substring(1, pseudos.length).split(':');

			// cleanup
			if (classes[0] 		== '')	{ classes.length 	= 0; };
			if (attributes[0] 	== '')	{ attributes.length = 0; };
			if (pseudos[0] 		== '')	{ pseudos.length 	= 0; };

			for (var h = 0; h < attributes.length; h++)
			{
				var attributeSplit 	= attributes[h].match(/([a-z]+)(=([^\]]+))?/i);
				var attrName		= (typeof attributeSplit[1] == 'undefined'|| attributeSplit[1] == '') ? '' : attributeSplit[1];
				var attrValue		= (typeof attributeSplit[3] == 'undefined'|| attributeSplit[3] == '') ? '' : attributeSplit[3];

				attributeNames.push(attrName);
				attributeValues.push(attrValue);
			};

			/* * /
			alert
			(
				selector		+ ' | ' +
				remainder		+ ' | ' +
				tag				+ ' | ' +
				id				+ ' | ' +
				classes			+ ' | ' +
				attributes		+ ' | ' +
				attributeValues	+ ' | ' +
				pseudos
			);
			/* */

			for (var j = 0; j < parents.length; j++)
			{
				// element and id selectors
				var elems = (id != '') ? [document.getElementById(id)] : parents[j].getElementsByTagName(tag);

				validationLoop:
				for (var k = 0; k < elems.length; k++)
				{
					var elem = elems[k];
					if (elem == null) { continue; }; // failed getElementById()

					// class selectors
					var elemClasses = elem.className.split(/\s+/);
					if
					(
						(tag != '*' && elem.nodeName != tag) ||
						(classes.length && !classes.foundIn(elemClasses))
					)
					{
						continue validationLoop;
					};

					// attribute selectors
					for (var l = 0; l < attributeNames.length; l++)
					{
						var attribute	= attributeNames[l];
						var attrValue	= attributeValues[l];

						if (elem[attribute])
						{
							var value		= elem.getAttribute(attribute);
							var match		= (new Boolean(value.match((new RegExp('^(' + attrValue + ')$')))));

							if (value == null || (attrValue != '' && match == false))
							{
								continue validationLoop;
							};
						}
						else
						{
							continue validationLoop;
						};
					};

					// haven't implemented all pseudo selectors yet
					// requires custom max-height definitions to id correctly
					for (var m = 0; m < pseudos.length; m++)
					{
						switch(pseudos[m])
						{
							case 'visited':
								if (css(elem, 'max-height') != '9999px')
								{
									continue validationLoop;
								}
							break;

							case 'unvisited':
								if (css(elem, 'max-height') == '9999px')
								{
									continue validationLoop;
								}
							break;

							case 'hover':
								if (css(elem, 'max-height') != '9998px')
								{
									continue validationLoop;
								}
							break;
						};
					};

					tmpSelected.push(elem);
				};
			};

			if (remainder != '')
			{
				// the current tmpSelected is the pool from which we'll
				// return selected elements, validate based on successfully
				// selecting remaining children
				if (remainder.indexOf('<') == 0)
				{
					var isParent = function(parent, elem)
					{
						do
						{
							if (elem.parentNode && parent == elem.parentNode)
							{
								return true;
							};
						} while (elem = elem.parentNode);

						return false;
					};

					remainder = remainder.replace(/^<\s?/, '');
					var qualifiers = parser(remainder, tmpSelected);
					var qualifiedSelected = Array();
					parentLoop:
					for (var n = 0; n < tmpSelected.length; n++)
					{
						for (var o = 0; o < qualifiers.length; o++)
						{
							if (isParent(tmpSelected[n], qualifiers[o]))
							{
								qualifiedSelected.push(tmpSelected[n]);
								continue parentLoop;
							};
						};
					};
					tmpSelected = qualifiedSelected;
				}
				else
				{
					tmpSelected = parser(remainder, tmpSelected);
				};
			};
			selected = selected.concat(tmpSelected);
		};
		return selected;
	};

	// Make sure we haven't been passed another array (arguments from another function)
	var args = (arguments.length === 1 && typeof arguments[0] != 'string') ? arguments[0] : arguments;
	for (var i = 0; i < args.length; i++)
	{
		selected = selected.concat(((typeof args[i] == 'object') ? args[i] : parser(args[i], [document])));
	}
	return selected.unique();
};
// first of a given selector
function one(selector)
{
	return $(selector)[0];
};
// last, hopefully in source order?
function last(selector)
{
	var matches = $(selector);
	if (matches.length > 0)
	{
		return matches[matches.length - 1];
	};
};
// gets (or sets depending on number of args) the rendered value
// of the CSS property of a given element
function css(elem, prop)
{
	if (!elem) return;

	var value = '';
	var styleProp = prop.replace(/\-(\w)/g, function(m, n){return n.toUpperCase();});

	if (arguments.length == 3)
	{
		value = arguments[2];
		elem.style[styleProp] = value;
	}
	else
	{
		if (document.defaultView && document.defaultView.getComputedStyle)
		{
			value = document.defaultView.getComputedStyle(elem, '').getPropertyValue(prop);
		}
		else if (elem.currentStyle)
		{
			value	= elem.currentStyle[styleProp];
		};
	};
	return value;
};
// generates a CSS selector for the given element
function toSelector(elem)
{
	var selector = '';
	if (elem.id)
	{
		selector += '#' + elem.id;
	}
	else
	{
		selector = elem.nodeName.toLowerCase();

		if (elem.parentNode && elem.parentNode.nodeName != 'HTML')
		{
			selector = toSelector(elem.parentNode) + ' ' + selector;
		}
		selector += (elem.className) ? '.' + elem.className.replace(/\s+/, '.') : '';

	};
	return selector;
};
// add/removeClass do exactly what you'd expect them to do
function addClass(elem, className)
{
	// prevent duplicates
	removeClass(elem, className);
	// elem can be an element or an array of elements
	var elems = (elem.nodeName) ? [elem] : elem;
	for (var i = 0; i < elems.length; i++)
	{
		elems[i].className += ' ' + className;
	};
};
function removeClass(elem, className)
{
	// elem can be an element or an array of elements
	var elems 	= (elem.nodeName) ? [elem] : elem;
	var classes	= className.split(/\s+/);
	for (var i = 0; i < elems.length; i++)
	{
		className = elems[i].className
		for (var j = 0; j < classes.length; j++)
		{
			className = className.replace(new RegExp('\\s?\\b' + classes[j] + '\\b'), '');
		};

		elems[i].className = className;
	};
};
// gets the first/last child element (ignores text and comments)
function firstChild(elem)
{
	for (var i = 0; i < elem.childNodes.length; i++)
	{
		var child = elem.childNodes[i];
		if (child.nodeType === 1)
		{
			return child;
		};
	};
	return null;
};
function lastChild(elem)
{
	for (var i = elem.childNodes.length - 1; i >= 0; i--)
	{
		var child = elem.childNodes[i];
		if (child.nodeType === 1)
		{
			return child;
		};
	};
	return null;
};
// gets the previous or next sibling of the same nodeName
function previousSibling(elem)
{
	var nodeName = elem.nodeName
	do
	{
		if (elem.previousSibling && elem.previousSibling.nodeName == nodeName)
		{
			return elem.previousSibling;
		};
	}
	while (elem = elem.previousSibling);

	return null;
};
function nextSibling(elem)
{
	var nodeName = elem.nodeName
	do
	{
		if (elem.nextSibling && elem.nextSibling.nodeName == nodeName)
		{
			return elem.nextSibling;
		};
	}
	while (elem = elem.nextSibling);

	return null;
};
// ajax, yawn
var XHR =
{
	fragmentIdentifier : '<!-- XHR FRAGMENT -->',
	// insert html fragments into one or more elements
	insert : function(target, html)
	{
		var targets = (target.constructor == Array) ? target : [target];
		var content = html;
		var inserts	= content.split(this.fragmentIdentifier);

		for (var i = 0; i < targets.length; i++)
		{
			if (!inserts[i]) { inserts[i] = ''; };

			if (window.IE && (target.nodeName == 'TBODY' || target.nodeName == 'TABLE'))
			{
				IE.fixInnerHTML(targets[i], inserts[i]);
			}
			else
			{
				targets[i].innerHTML = inserts[i];
			};
		};
	},
	// url, target, callback, callback args
	get		: function(url)
	{
		this.request('GET', arguments);
	},
	// url, data, target, callback, callback args
	post	: function(url, data)
	{
		this.request('POST', arguments);
	},
	// form, target, callback, callback args
	form	: function(form)
	{
		var method 	= (form.method && form.method.toUpperCase() == 'POST') ? 'POST' : 'GET';
		var url 	= form.getAttribute('action');

		var query	= [];
		for (var i=0; i<form.elements.length;i++)
		{
			var e = form.elements[i];
			if (e.name!='')
			{
				switch(e.nodeName)
				{
					case 'INPUT':
						if
						(
							e.type.match(/(submit|image|cancel|reset)/) ||
							(e.type.match(/(checkbox|radio)/) && !e.checked)
						)
						{
							continue;
						};
						query[query.length] = escape(e.name) + '=' + escape(e.value);
					break;

					case 'TEXTAREA':
						query[query.length] = escape(e.name) + '=' + escape(e.value);
					break;

					case 'SELECT':
						query[query.length] = escape(e.name) + '=' + escape(e.options[e.selectedIndex].value);
					break;
				};
			};
		};
		var data = query.join('&');
		var args = toArray(arguments);
		args.shift(); // remove the form element

		if (method == 'GET')
		{
			url += (url.indexOf('?') != -1) ? '&' : '?';
			url += data;
			args.unshift(url);
		}
		else
		{
			args.unshift(data); // [1]
			args.unshift(url); // [0]
		};

		this.request(method, args);
	},
	// PRIVATE: Use get(), post() or form() instead
	request	: function(method, args)
	{
		var request	= false, data = null, url, target, callback, callback_args;

		if (window.XMLHttpRequest) { request = new XMLHttpRequest(); };
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		if (!request)
		{
			try { request = new ActiveXObject('Microsoft.XMLHTTP'); }
			catch (e) { request = false; };
		};
		@end @*/
		if (!request) { return; }; // important that this doesn't return false

		args	= toArray(args);
		url		= u(args.shift());
		url    += ((url.indexOf('?') != -1) ? '&' : '?') + (new Date()).getTime();

		if (method == 'POST') 			{ data 			= args.shift(); }
		if (args[0] && args[0] != null)	{ target 		= args[0]; };
		if (args[1])					{ callback		= args[1]; };
		if (args[2])					{ callback_args = args[2]; };

		request.open(method, url, true);
		if (method == 'POST')
		{
			request.setRequestHeader("Method","POST " + url + " HTTP/1.1");
			request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		};
		request.send(data);

		if (target || callback)
		{
			request.onreadystatechange = function()
			{
				if (request.readyState == 4 && request.status == 200)
				{
					if (callback && typeof callback == 'object' && callback.beforeInsert)
					{
						if (callback_args)
						{
							callback.beforeInsert(request, callback_args);
						}
						else
						{
							callback.beforeInsert(request);
						};
					};

					if (target)
					{
						XHR.insert(target, request.responseText);
					};

					if (callback)
					{
						if (typeof callback == 'object' && callback.afterInsert)
						{
							if (callback_args)
							{
								callback.afterInsert(request, callback_args);
							}
							else
							{
								callback.afterInsert(request);
							};
						}
						else
						{
							if (callback_args)
							{
								callback(request, callback_args);
							}
							else
							{
								callback(request);
							};
						};
					};
				};
			};
		};
		return false;
	}
};
// whether or not the element in question has a fixed parent
function hasFixedParent(elem)
{
	do
	{
		if (elem.offsetParent && css(elem.offsetParent, 'position') == 'fixed')
		{
			return true;
		};
	}
	while (elem = elem.offsetParent);
	return false;
};
// returns the string with the first character capitalized
function ucfirst(str)
{
 	return str.substring(0, 1).toUpperCase() + str.substring(1);
};
// returns the x/y coordinate with respect to scroll and fixed positioning
function getPos(elem)
{
	var pos = { x : 0, y : 0 };
	if (elem)
	{
		do
		{
			pos.x += elem.offsetLeft;
			pos.y += elem.offsetTop;

			if (css(elem, 'position') == 'fixed')
			{
				break;
			};

			// account for scroll position
			if (elem.offsetParent)
			{
				if (elem.offsetParent.nodeName == 'BODY')
				{
					pos.x -= window.pageXOffset;
					pos.y -= window.pageYOffset;
				}
				else
				{
					pos.x -= elem.offsetParent.scrollLeft;
					pos.y -= elem.offsetParent.scrollTop;
				};
			};
		}
		while (elem = elem.offsetParent);
	};

	return pos;
};
// Used to convert a function's arguments object into a true array
function toArray(argumentsObject)
{
	var returnArray = new Array();
	for (var i = 0; i < argumentsObject.length; i++)
	{
		returnArray[i] = argumentsObject[i];
	};
	return returnArray;
};
// null out document.write() to prevent feeds with archaic Javascript from nuking Fever on subsequent page loads
document.write = function(){};
// removes duplicate values from an array
Array.prototype.unique = function()
{
	var original = this.slice(0);
	this.length	 = 0;

	for (var i = 0; i < original.length; i++)
	{
		var unique = true;
		for (var j = 0; j < this.length; j++)
		{
			if (original[i] == this[j])
			{
				unique = false;
				break;
			};
		};
		if (unique)
		{
			this.push(original[i]);
		};
	};
	return this;
};
// if the needle is found in the array its index is returned, if not -1 is returned
Array.prototype.search = function(needle)
{
	var index = -1;

	for (var i = 0; i < this.length; i++)
	{
		if (this[i] == needle)
		{
			index = i;
			break;
		};
	};

	return index;
};
// Returns true if all elements of the array can be found in the otherArray
Array.prototype.foundIn = function(otherArray)
{
	var found = true;

	for (var i = 0; i < this.length; i++)
	{
		if (otherArray.search(this[i]) == -1)
		{
			found = false;
			break;
		};
	};

	return found;
};
// Returns true if the string contains only whitespace
String.prototype.isEmpty = function()
{
	return this.match(/^\s*$/);
}