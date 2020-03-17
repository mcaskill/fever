var Fever =
{
	iPhone : {},

	checkCheckbox : function(selector, checked)
	{
		var checkbox = one(selector);
		var label = checkbox.parentNode;

		checkbox.checked = checked;
		if (checkbox.checked)
		{
			addClass(label, 'checked');
		}
		else
		{
			removeClass(label, 'checked');
		};
	},
	toggleCheckbox : function(label)
	{
		var checkbox = one('#'+label.htmlFor);

		if (checkbox.checked)
		{
			checkbox.checked = false;
			removeClass(label, 'checked');
		}
		else
		{
			checkbox.checked = true;
			addClass(label, 'checked');
		};

		window.event.preventDefault();
	},

	dialogs : [],
	addDialog : function(html)
	{
		this.dialogs.push(html);
		this.displayNextDialog();
	},
	displayNextDialog : function()
	{
		var container = one('#dialog-container');
		// if there are any notices to display and one is not currently being displayed
		if (this.dialogs.length && css(container, 'display') == 'none')
		{
			var dialog = one('#dialog');
			dialog.innerHTML = this.dialogs.shift();
			addClass(document.body, 'dialog');
			one('html').addEventListener('click', Fever.dialogListener, true);
		};
	},
	dismissDialog : function()
	{
		one('#dialog').innerHTML = '';
		removeClass(document.body, 'dialog');
		one('html').removeEventListener('click', Fever.dialogListener, true);
		Fever.displayNextDialog();
	},
	dialogListener : function(e)
	{
		if (e.target)
		{
			var action = one('#dialog');
			var target = e.target;
			do
			{
				if (target == action)
				{
					return;
				};
				if (target.parentNode)
				{
					target = target.parentNode;
				}
				else
				{
					break;
				};
			}
			while (target != document.body);

			e.preventDefault();
			e.stopPropagation();

			var closeBtn = one('#dialog a.close');
			if (closeBtn)
			{
				closeBtn.onclick();
			}
			else
			{
				Fever.dismissDialog();
			};
		};
	},

	animate : function(elem, property, value) // callback is optional fourth argument
	{
		// prevent conflicting animations
		if (elem.animateId)
		{
			window.clearInterval(elem.animateId);
			elem.animateId = null;
		};

		var callback = (arguments.length == 4) ? arguments[3] : function(){};
		var animate = function()
		{
			// only animating position for now so px is hardcoded
			var currentValue	= parseInt(css(elem, property));
			var distance 		= Math.floor((Math.abs(currentValue - value) / 2) * 1.7);
			var direction 		= (currentValue > value) ? -1 : 1;
			var updatedValue 	= currentValue + (direction * distance);

			if (distance <= 1)
			{
				elem.style[property] = value + 'px';
				// our work is done
				window.clearInterval(elem.animateId);
				callback();
			}
			else
			{
				elem.style[property] = updatedValue + 'px';
			};
		};
		animate();
		elem.animateId = window.setInterval(animate, 50);
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
	return selector.replace(/\.+/, '.');
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
		url		= args.shift();
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
						var returnValue;
						if (callback_args)
						{
							returnValue = callback.beforeInsert(request, callback_args);
						}
						else
						{
							returnValue = callback.beforeInsert(request);
						};

						if (returnValue == false) return;
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
				/** /
				// while technically correct, following adjustments
				// result in undesired values in recent versions
				// of MobileSafari
				pos.x -= elem.offsetParent.scrollLeft;
				pos.y -= elem.offsetParent.scrollTop;
				/**/
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