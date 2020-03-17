<dl id="help-hot">
	<dt>Hot</dt>
	<dd>
		Links from all of your feeds are weighted by
		frequency and disposition of the linking feed,
		then ordered by temperature using the normal
		body temperature of <?php

		$degrees = 98.6;
		if ($this->prefs['use_celsius'])
		{
			$degrees = ($degrees - 32) * 5 / 9;
		}
		e($degrees);

		?>&deg; as a base.
	</dd>
</dl>

<dl id="help-kindling">
	<dt>Kindling</dt>
	<dd>
		Kindling is a supergroup containing
		all of your non-Spark feeds. These
		are your must-reads.
	</dd>
</dl>

<dl id="help-saved">
	<dt>Saved</dt>
	<dd>
		Unlike other items which are automatically
		deleted after ten weeks saved items are
		never deleted (until you unsubscribe from
		the item's feed). See the Sharing tab in
		Preference to publish an RSS feed of your
		saved items.
	</dd>
</dl>

<dl id="help-sparks">
	<dt>Sparks</dt>
	<dd>
		Sparks are inessential feeds that increase
		the temperature of links in the Hot view.
		Their unread items will never appear in the
		Kindling supergroup or in any of your custom
		groups. Link blogs and sites that frequently
		repost content are excellent candidates for
		Sparks.
	</dd>
</dl>

<dl id="help-hotlinking">
	<dt>Hotlinking</dt>
	<dd>
		In order to prevent bandwidth abuse, some
		sites prevent others from linking directly
		to their images by returning either a 403
		Forbidden error or alternate image. Fever can
		route around this problem when necessary.
	</dd>
</dl>

<dl id="help-all-items">
	<dt>All items in this group</dt>
	<dd>
		Just a steady stream of items from all
		feeds in this group.
	</dd>
</dl>

<dl id="help-multiple">
	<dt>Selecting multiple items</dt>
	<dd>
<?php if (m('#mac(?!hine)#i', $_SERVER['HTTP_USER_AGENT'], $m)):?>
		Hold command to select/deselect multiple
		non-contiguous items.
<?php else: ?>
		Hold control to select/deselect multiple
		non-contiguous items.
<?php endif; ?>
		Hold shift to select a contiguous group of items.
		Don't forget you can also drag and drop feeds
		to add and remove them from groups and Sparks.
	</dd>
</dl>

<dl id="help-feed-auth">
	<dt>Authentication <em>(optional)</em></dt>
	<dd>
		Some feeds require a username and
		password to access their content.
		If required but not provided Fever
		will prompt when refreshing the feed.
	</dd>
</dl>

<dl id="help-feed-title">
	<dt>Feed title <em>(optional)</em></dt>
	<dd>
		If left blank the title provided by
		the feed will be used.
	</dd>
</dl>

<dl id="help-excerpts">
	<dt>Excerpts</dt>
	<dd>
		Some feeds link directly to the page
		the author is commenting on. If excerpted
		you might miss out on their commentary.
	</dd>
</dl>

<dl id="help-service-url">
	<dt>Service url</dt>
	<dd>
		Most social media and bookmarking services
		provide a url for adding new items. Use the
		following tokens to modify that url. Each
		token will be replaced with the appropriate
		encoded value before redirecting:
		<code>%t</code> with title,
		<code>%u</code> with url, and
		<code>%e</code> with excerpt.
		Prefix the url with <code>#</code> to notify the service in the background.
	</dd>
</dl>

<dl id="help-saved-feed">
	<dt>Saved items feed</dt>
	<dd>
		When enabled this feed is discoverable
		from your Fever login screen.
	</dd>
</dl>

<dl id="help-item-expiration">
	<dt>Item Expiration</dt>
	<dd>
		By default Fever deletes unsaved items
		older than 10 weeks. Reduce this timeframe
		to further limit Fever's database usage but
		remember, the less data Fever has, the less
		effective older Hot timeframes will be.
	</dd>
</dl>

<?php if (!$this->is_installed()):?>
<dl id="help-db-server">
	<dt>Database Server</dt>
	<dd>
		The name of the server that hosts your database. This is
		typically <code>localhost</code> but will vary
		from host to host. If you are unsure, try the
		default first. If Fever cannot connect
		please check with your host or their
		documentation.
	</dd>
</dl>

<dl id="help-db-database">
	<dt>Database Name</dt>
	<dd>
		The name of the database Fever will be
		connecting to. This database must already
		exist. Please see your host's documentation
		for questions about creating a database
		or regarding the name of your database.
	</dd>
</dl>

<dl id="help-db-username">
	<dt>Database Username</dt>
	<dd>
		The username to use when connecting to
		your database.
	</dd>
</dl>

<dl id="help-db-password">
	<dt>Database Password</dt>
	<dd>
		The password to use when connecting to
		your database.
	</dd>
</dl>

<dl id="help-db-prefix">
	<dt>Database Table Prefix</dt>
	<dd>
		The names of all database tables created
		by Fever will be prefixed with this value
		to avoid conflicts with existing tables.
	</dd>
</dl>

<dl id="help-email">
	<dt>Email</dt>
	<dd>
		The email address you will use when logging into
		this Fever installation. Completely independent
		of your Fever Account Center email address.
	</dd>
</dl>

<dl id="help-password">
	<dt>Password</dt>
	<dd>
		The password you will use when logging into
		this Fever installation. Completely independent
		of your Fever Account Center password.
	</dd>
</dl>

<dl id="help-temperature">
	<dt>Temperature Scale</dt>
	<dd>
		Fever assigns a temperature to reoccurring
		or "hot" links found in your feeds. Pick the
		more familiar scale.
	</dd>
</dl>
<?php endif; ?>