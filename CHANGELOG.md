# Changelog

This document contains the full history of Fever.

Note that this repository's codebase only contains a copy of v1.39.

## v1.39 (2014-09-22)

- updated mobile CSS for the iPhones 6
- fix for `html:` namespacing prefix throwing off entity decoding in Atom feeds
- improved gateway connectivity error reporting
- "reload interface after browser refresh" now defaults to off because I'm tired of getting support requests about the default behavior
- added feeds to the list of database tables regularly optimized and repaired
- now explicitly closes database connection before exiting


## v1.38 (2014-02-10)

- fixed bug where only the first PDO-powered MySQL query would work (affected a tiny fraction of servers)
- added PHP 5.1 requirement for PDO support (required for, at least, query & fetch)
- optimized all PNGs with http://imageoptim.com (update archive down from 1.3MB to 1.1MB)


## v1.37 (2014-02-05)

- fixed PHP 4.x SIDB fatal errors (due to using PHP 5-only control structures)


## v1.36 (2014-02-05)

- reports anonymous server capabilities to feedafever.com during gateway requests so I can make informed decisions about future development
  data collected (see `Fever->capabilities()` for implementation):
  - `fever_version`
  - `php_version`
  - `mysql_client_version`
  - `mysql_server_version`
  - `has_pdo_mysql`
  - `has_mysqli`
  - `has_mysql`
  - `has_iconv`
  - `has_mbstring`
  - `has_gd_png`
  - `has_curl`
- removed use of deprecated e flag with `preg_replace`
- increased request timeout when requesting updates to accommodate the larger zip (caused by @2x assets)
- added SIDB to eliminate dependency on the deprecated `mysql_*` family of functions
- removed two lingering `allow_call_time_pass_reference` errors
- fixed a problem with links never heating up on OS X Mavericks Server


## v1.35 (2014-01-27)

- dummied out desktop refresh functions in mobile JavaScript to eliminate undefined errors
- only links `override.css` or `override-mobile.css` if they exist
- manually added `@2x` assets with `@media` query--like a schlep
- removed automatic `.htaccess` retinafication technique


## v1.34 (2014-01-25)

- STOPGAP: deleted retinafication .htaccess file on problem servers

  If you updated to 1.33 and images or CSS now 404, you
  have a problem server. I believe the problem involves
  `mod_rewrite`'s default RewriteBase or possibly Apache's
  include paths. Not sure how to fix just yet. Manually
  defining the RewriteBase to the expected value results
  in 500 errors in problem servers. Hence this stopgap.


## v1.33 (2014-01-24)


- added ?mobile query to force the mobile view on non-mobile (or unsupported) devices
- added retina support to desktop and mobile views
- added hooks for persistent style customization

  Just add an `override.css` or `override-mobile.css` to the root `fever/` directory.
  Copy selectors from existing CSS and use `!important` where necessary.
  NOTE: Persists through updates but otherwise unsupported.


## v1.32 (2014-01-16)


- added support for `media:credit` (mapped to item author)
- fixed missing space in curl options on Extras page
- removed `-webkit-text-size-adjust` from desktop stylesheets (Safari/Chrome resize should now match Firefox)


## v1.31 (2013-04-14)


- reverted to original "reload interface after browser refresh" behavior until I have time to revisit properly (can still be disabled entirely in your Fever Preferences under the Refreshing tab)


## v1.30 (2013-04-14)


- updated cURL cron example to include single quotes around the refresh url (absence causes problems on some hosts)
- rewrote encoding handling for non-UTF-8 feeds
- added a special case for rel="hub" links in Atom feeds


## v1.29 (2013-04-13)


NOTE: If your Feedlet stops working with this update grab the current version from your Extras page (accessible in the action menu below the Fever logo)

- fixed bug that caused Sparks previously associated with a group to be deleted when the group was deleted
- updated curl command to provide a user agent to prevent 500 errors on some hosts
- added a Changelog link to the Fever menu
- added a basic subscribe link for use with browser extensions (requires login), eg. `http://yourdomain.com/fever/?subscribe&url=`
- fixed Feedlet on wired.com homepage and feed list
- fixed the Feedlet 500 error that occurs on some hosts
- updated "Show read" default to avoid confusion when adding groups and feeds for the first time
- updated "reload interface after browser refresh" behavior to never replace the currently open item
- iPhone: updated iPhone stylesheets for 4" devices (no retina yet, sorry!)
- added support for protocol agnostic urls in feeds
- updated multiple select box behavior to make use of all space available to them
- updated DOM parsing to handle illogically marked up images with spaces inside the the src quotes
- updated XML parser to better handle 'WINDOWS-1256' encoding
- fixed a couple of SQL bugs that could prevent importing OPML, emptying feeds and emptying the favicon cache (hat-tip: Matt Long)


## v1.28 (2013-02-06)

- updated XML parser to better handle 'WINDOWS-1250' encoding
- updated `__config` table definition (setting default values for MEDIUMTEXT causes an error on some versions of MySQL preventing installation)


## v1.27 (2012-10-19)

- updated all instances of `date()` in copyright notices to `gmdate()` to remove warnings in newer verisions of PHP
- removed letterbox from webclip on 4" iOS devices (need to delete and re-add to home screen to take effect)
- updated `HTTP_HTTPS` server variable detection
- updated Service Url help text


## v1.26 (2012-08-14)

- iPad: fixed group/feed list overlap when zoomed in on items
- iPad: restored scroll-to-top-of-new-feed functionality
- iPad: updated webclip icons for retina
- added new keyboard shortcuts for Hot (1), Kindling (2), Saved (3), Sparks (4), and Search results (5)
- updated keyboard shortcut to prevent interaction with the Pocket Chrome extension
- updated sharing service url filtering, prefix a url with # to connect to the sharing url in the background (no new window or tab)


## v1.25 (2012-07-14)

- updated string encoding functions to accomodate changes made to the default value of optional arguments in PHP 5.4 (fixes garbled extended characters)
- added some additional default database values to accomodate servers running in MySQL strict mode


## v1.24 (2012-04-19)

- API v3: Sparks are now filtered out of the `feeds_groups` response (past group relationships are retained, just not exposed through the API)
- API v3: added comma separated `feed_ids` and `group_ids` to `items` API GET request to limit the items returned to specific feed(s)/group(s)
- API v3: added `mark_item_as_unread()` support
- updated `request.php` to support gzipped content (requires cURL 7.1+)
- fixed bug where editing a Spark feed caused it to lose its previous group association
- updated `refresh_feed()` to better detect `site_url`s marked-up in unusual formats
- fixed unescaped single quotes in database password


## v1.23 (2012-01-11)

- iPad: optimizations for iOS 5 (two finger scroll group/feed lists)
- fixed scrollable group/feed lists ugliness on Lion
- option keyboard shortcut limited to Macs instead of just excluding Windows (Linux-friendly)
- Feedlet: updated routing to prompt for login if not already logged in instead of failing silently (reverts to old, server-parsing method in these instances)
- Feedlet: updated to match link elements with multiple values in their rel attribute
- iPhone: updated login form so that clicking "Go" on the iPhone's keyboard submits login rather than reminder by default
- iPhone: eliminated harmless error from line 775 in `iphone.js` on login screen
- addressed "Call-time pass-by-reference" warnings in convenience functions in `util.php`


## v1.22

- updated MySQL version detection to better support alphanumeric version strings
- added keyboard shortcut Shift+R to refresh the current group or feed
- iPhone: fixed bug that caused a recently read feed to display the parent group's unread items when reselected


## v1.21

- iPhone: after returning from a zoomed Elsewhere view you can now pinch or doubletap to restore the intended Fever UI scale
- updated all email inputs to `type="email"` to prevent undesired auto-capitalization on iOS devices


## v1.20

- iPhone: added checks to better prevent conflicting animations from leaving the UI an unusable state
- iPhone: added checks to better prevent near-simultaneous content requests from leaving the UI an unusable state
- iPhone: added preference to view links in another MobileSafari tab (for non-WebClip users)
- iPhone: fixed bug in "Mark as read on scroll past headline" logic affecting recent versions of MobileSafari
- iPhone: updated copyright logic in footer on login screen


## v1.19

- fixed up/down arrow key bug (should scroll page when items/hot links have focus, not skip to next item/link)


## v1.18

- added compatibility with MySQL 5.5 (which drops support for deprecated TYPE keyword and requires ENGINE keyword when creating tables)
- activated the "Delete..." item in the Sparks contextual menu allowing you to unsubscribe from all Sparks at once
- added additional activation error message
- improved vim-like h-j-k-l keyboard behavior
- silenced some PHP warnings in a third-party library


## v1.17

- iOS: updated webclip for Retina Display (delete existing Fever webclip and Add to Home Screen from Safari again)
- iPad: added mark as read button to bottom of group/feed list (since you can't scroll to autoread the last few items)
- iPad: eliminated some :hover states so double-tapping is no longer necessary for the majority of frequent functions
- iPad: set viewport width to prevent CSS sprite tiling issues from rounding errors
- iPad: disabled feed/group scrolling
- iPad: hid Help popovers


## v1.16

- removed some errant Sharing debug code
- added `text-rendering: optimizeLegibility;` to `shared.css`
- API: added `remove_control_characters()` to prevent invalid XML/JSON output (hattip: Tom Krush)


## v1.15

- API: added `max_id` and `with_ids` arguments to items request, see the updated API documentation http://feedafever.com/api
- updated `request.php` to default to using sockets when curl is installed but disabled via `php.ini`
- eliminated possible false negative when checking that a feed is really a feed before parsing
- API: updated API key comparison, now case-insensitive
- updated %e sharing token, now has its whitespace collapsed when used with the `javascript:` pseudocol
- API: added `total_items` containing the total number of items stored in the remote Fever installation to API requests for `items`


## v1.14

- added beginnings of a basic API (documentation http://feedafever.com/api)
- added custom iPad webclip icon
- fixed redundant `data:` prefix in the default cached favicon data
- disabled ?errors query command on cron-based refresh
- removed some errant PHP shortags in the Saved Items feed that were preventing the feed from working on servers that don't support short tags
- fixed missing hyphen before `k` curl option for https hosts (thanks Andreas)
- fixed a bug where marking a group/feed as read via keyboard shortcut didn't update internal pointers to the new focused group/feed


## v1.13
 (bug fixes)
- reverted to original "back out of group/feed" behavior (the last few items would never be marked as read since you have to scroll to the top to back out)
- special-cased the new o-shortcut so that typing "o" in the search input doesn't try to open a selected item


## v1.12

- updated "back out of group/feed" behavior of iPhone-optimized version to only mark onscreen items as read
- added two independent "Mark as read" preferences to iPhone-optimized version of Fever, on "scroll past headline" and "back out of group/feed"
- added additional keyboard shortcut to open currently selected item or link (o)
- expanded mobile detection to include all WebKit-based WebOS browsers
- nulled out `document.write()` to prevent blank pages when auto-paging by scrolling
- improved title detection of links from Reddit feeds (thanks to http://karanlyons.com)


## v1.11

- fixed a bug in the rendering of multi-byte characters in group names (mostly in the add/edit/export forms)
- fixed a bug that allowed the Sparks "All items/unread" supergroup context menu "Mark group as read" to mark all Kindling items as read
- updated `ago()` calculations to switch from weeks unit to months after 12 weeks (most likely visible in Saved)
- fixed the Alt key to no longer toggle item content on Windows (was breaking Alt-tabbing to other applications)
- fixed inconsistent url truncation
- fixed `debug()` output for SELECT durations by adding `number_format()`ing to prevent useless E-5s
- fixed a bug preventing keyboard-based auto-pagination from working consistently
- updated saved items rss feed to place item titles in a cdata (to better handle poorly-/un- encoded entities)

Feeds that were brought to my attention that now work properly (or better) in Fever 1.11:

- http://www.buzzfeed.com/index.xml


## v1.10

- fixed bug that broke pagination when "mark items as read as they scroll past" Preference was unchecked
- fixed bug where a previously selected read group defaulted to Kindling without listing its unread feeds
- added an item retention preference to the Refreshing tab to further limit Fever's database usage, default is (and has always been) 10 weeks
- added preliminary unofficial support for Internet Explorer via Google's new Chrome Frame plugin (http://code.google.com/chrome/chromeframe/)
- updated sharing JavaScript to only open a new window when the service url starts with http or https (better support for sending links to `Tweetie.app` with `tweetie:%t%20%u`)
- updated `infiniterator()` method to correctly handle servers with a `max_execution_time` that is less than or equal to 0 (unlimited)
- updated sharing JavaScript to support the `javascript:` pseudocol (replacement tokens may be used in single-quoted strings)
- updated keyboard shortcut JavaScript to prevent conflicts with native shortcuts on Windows platform


## v1.09

- iPhone: fixed a few minor JavaScript errors
- added Alt/Option key as an additional item content toggle (to accommodate newer MacBook Pros)
- updated third-party service integration to be user customizable (including their keyboard shortcuts)
- updated `install_paths()` method to better detect https
- updated CSS to move the "Choose OPML" button out of the way of the "import groups" checkbox in Firefox
- added "Send to Instapaper" option for items and keyboard shortcut (I)
- added "Send to Delicious" option for items and keyboard shortcut (D)
- added "Twitter" option for items and keyboard shortcut (T)
- added "Email" option for items (uses default email client) and keyboard shortcut (E)
- added item contextual menu
- updated auto-scroll during toggling of excerpts to occur only when the top of an item is off screen
- added optional Saved Items feed (see the new Sharing tab in Preferences to enable)
- added Sharing tab to Preferences


## v1.08

- added keyboard shortcuts for "Visit site" and "Visit site and mark as read" (V and Shift + V respectively)
- added "Visit site and mark as read" to feed context menu
- added additional styling to `ins`/`del` family of tags
- added partial support for `xml:base`
- updated the Hot view to use the title/description of an available source item, even if the original item is beyond the specified timeframe (does not affect temperature calculation)
- added the ability to save Hot links that are directly associated with an item in a feed you are already subscribed to
- added the url of a feed that requires authentication as a tool tip (in case clarification is necessary)
- updated feedlet to visually differentiate between legitimate feeds and guesses based on link url and text
- updated feedlet to sniff out links that could point to feeds even when embedded feeds are found in a pages head (was one or the other before)
- added search term highlighting in search results
- fixed bug in displaying search terms with double-quotes in them
- updated `resolve()` to leave `javascript:` protocol links alone
- updated `request()` to treat the Location header as case-insensitive
- updated `refresh_feed()` to ensure that the same feed isn't unnecessarily refreshed multiple times when updating initial undefined values
- updated Fever's query functions to free up memory only when actually used (slaps forehead)

Feeds that were brought to my attention that now work properly (or better) in Fever 1.08:

- http://www.tbray.org/ongoing/ongoing.atom
- http://www.intertwingly.net/blog/index.atom
- http://bitworking.org/news/feed/
- http://blog.tagesanzeiger.ch/mamablog/index.php/feed/atom/


## v1.07

- iPhone: added preliminary support for automatic background refreshing on the iPhone--current implementation may not refresh the stalest feeds--will revisit if necessary (uses `ignore_user_abort(true)` to continue with refresh request without maintaining a persistent connection)
- iPhone: added limited Feedlet support to iPhone version
- marking Spark items as read no longer incorrectly decrements the total unread count displayed in Kindling and the browser title bar (since Sparks don't contribute to these numbers)
- Fever now ignores this broken png favicon to prevent choking the caching process: http://www.imjustcreative.com/favicon.ico
- rewrote `blacklist()` to eliminate dropped connections when modifying the blacklist
- updated Fever's query functions to free up memory wherever possible
- Fever now manually removes `<style>` elements (and their contents) before using PHP's native `strip_tags()` (which unhelpfully removes the tag but not the CSS code it contained)
- rewrote `weight_links()` method to perform far fewer updates each refresh (up to 90% fewer in common situations)
- ?feedlet&js request now sent with the proper `content-type: text/javascript`
- fixed typo ("ellapsed") in debug output
- added -k option to the example curl resquest on the Extras page when served over https (this allows curl to accept self-signed certificates)

Feeds that were brought to my attention that now work properly in Fever 1.07:

- `feed://apelad.blogspot.com/feeds/posts/default`
- http://feeds2.feedburner.com/imjustcreative (favicon was wrecking havoc with Fever's favicon caching routine)


## v1.06

- iPhone: added support for swiping to the previous/next item
- if a group or feed with no unread has focus and "Show read" is disabled then focus is passed to the supergroup or superfeed (applies to iPhone version too)
- groups now observe the "Show read" setting (hiding groups with no unread when "Show read" is disabled)
- had to single out and neuter crunchbase.com's JavaScript because its `document.write()`'s were causing Firefox to break completely when paging (used after the initial page load `document.write()` replaces the existing document)
- added debug error reporting to `request()`
- updated `get_attrs()` to be case-insensitive by default (specifically to allow `<img SRC="">` in some feeds)
- added https support to the feedlet (be sure to grab the updated Feedlet from your Extras page if you're using SSL)
- Fever now manually removes `<script>` elements (and their contents) before using PHP's native `strip_tags()` (which unhelpfully removes the tag but not the JavaScript code it contained)
- esc keyboard shortcut now also dismisses dialogs (the previous cmd/ctrl+. still works too)
- clicking a contributing link will now mark its item as read (bug fix)
- J keyboard shortcut now loads the next page of items/links when available or skips to the next unread group (like the spacebar shortcut)
- double-quotes are now encoded in exported opml

Feeds that were brought to my attention that now work properly in Fever 1.06:

- http://www.jwz.org/cheesegrater/RSS/apod.rss
- http://www.macupdate.com/mommy/macsurferx.xml


## v1.05

NOTE: Be sure to grab the updated Feedlet from your Extras page (accessible in the action menu below the Fever logo) (updated in 1.04)

- rather than reweight links on update Fever now forces a refresh after the update which checks for unweighted feeds and reweights only if necessary (which should achieve the same thing only with a user-visible progress indicator)
- removed PHP5-only `include_object` argument from `debug_backtrace()` calls


## v1.04

- updated feedlet to work on protected urls and actual feeds and to sniff out likely `a.hrefs` in the absence of proper feed links
- updated `normalize_url()` to work with `feed://` urls
- added title attribute to "Add link to blacklist" and "Toggle item save state" icons
- revised how Hot links are calculated (to better prevent Hot spamming) and sorted (subscribed-to items before unknown links)
- added ?unorphan query command to delete orphaned items and links (these will occasionally be created when deleting a feed during a refresh)
- uncommented login errors (not sure why those were commented out)
- added extra steps to prevent encoded entities inside pre and code elements from being decoded
- introduced more robust error reporting (initial messages may appear out of sequential order)
- stalled, incomplete browser-based refreshes now resume after 30 seconds
- fixed bug in up/down arrow key behavior that prevented keyboarding to the Hot, Saved, Sparks and Search section
- added some housekeeping code to optimize the item and link tables every 24 hours and check (and repair if necessary) for crashed tables once an hour
- swapped the J/K keyboard shortcuts (they were backwards as originally introduced)

Feeds that were brought to my attention that now work properly in Fever 1.04:

- http://daringfireball.net/index.xml (all versions)


## v1.03

- added J/K keyboard shortcuts for scrolling to previous/next item (similar to spacebar without the page up/down behavior for longer items)
- updated Feedlet options dialog to prevent long feed titles from overlapping tabs
- removed confusing text regarding importing an OPML from the Extras page
- bumped the memory saving arbitrary 40 item parse limit up to 100 because even though some sites insist on keeping their entire archive in their feed they don't have the common courtesy to sort them reverse chronologically
- fixed bug in OMDOMDOM that caused the parser to trip up when unencoded CDATA was found inside of a `<content:encoded>` node
- added an "anonymize referrers from this Fever installation" preference under Behavior (currently uses http://feedafever.com/anon/?go=%s)
- fixed bug where feeds with no unread weren't being listed in the feed list on search results (MobileSafari version)
- Firefox should now download exported OPMLs
- selected group now scrolls into view (if necessary) when using keyboard shortcuts

Feeds that were brought to my attention that now work in Fever 1.03:

- http://www.planetpython.org/atom.xml
- http://www.themorningnews.org/rss.xml
- http://darkgate.net/comic/rss2.php?dilbert&foxtrot&stonesoup&sherman&userf&vgcats&bizarro&ctrlaltdel&mothergooseandgrimm&pennyarcade&spiderman
- http://feeds.postrank.com/1b145f386ef2b61f5990c31709d974d4?level=best


## v1.02

- updated `refresh_feed()` to correct previously saved `feed://` urls
- added a startup image for webclip (requires iPhone OS 3.0)
- added a link to the temporary changelog to the updated screen
- added tabs to shorten the longer dialogs (including add/edit feed/group, preferences, Feedlet options)
- added :focus styles for various inputs
- made groups independently scrollable (preliminary, still a bit twitchy, will revisit)
- added shift+up/down arrow key commands to select the contributing items of a hot link, right arrow key to open


## v1.01

- fixed bug where feeds with no unread weren't being listed in the feed list on search results
- added ?flush query command to completely wipe any potentially corrupt data while preserving groups, feeds, settings and saved items
- fixed a bug that could result in incorrect temperature fluctuations every other refresh and missing discussion
- added ?revert query command to reload a fresh copy of the current version of Fever from the gateway
- added an "Edit Sparks" form, accessible from the Sparks context menu, to bulk add/remove feeds from Sparks
- added an "Add to blacklist" button to links (appears on hover) and related keyboard shortcuts
- added ?empty query command to delete all groups and unsubscribe from all feeds
- tweaked tag splitting regex in OMDOMDOM to better handle feeds with unencoded >s
- fixed some HTML entity encoding issues when editing group and feed titles
- MobileSafari now respects the "mark items as read as they scroll past" preference
- added "toggle item excerpts on click" preference (default behavior remains the same)
- if the groups list is taller than the window groups and feeds will scroll with the page (this is just a stopgap until I can implement a feeds list-like solution)
- you can now optionally specify a title when subscribing to a new feed (left blank, the title provided by the feed is used)
- if the Feedlet only finds one feed it will be automatically selected
- collapsing an expanded, scrolled item now scrolls the top of the item back into view
- added check for GD and PNG support before trying to convert favicons
- improved support for extended character sets
- improved OMDOMDOM's handling of CDATA
- OMDOMDOM can now parse UTF-16 with a BOM (unresolved oddity: the initial open angle bracket turns into a diagonal backslash)
- the ?errors query command is now inherited by subrequests (and as a result, actually useful now)
- added support for `https://` to `request.php` (now supports Gmail feeds)
- fixed a tag matching bug in OMDOMDOM
- importing a group-less OPML with "import groups" unchecked now correctly adds feeds to the newly created "`<opml name>` (Imported)" group
- whitespace around email and password is now stripped when saving and logging in
- help bubbles now appear after a half-second delay
- `Fluid.app` Fever SSB dock badge now respects "show unread counts" preference
- buttons now align in Safari 4 Final
- the "automatically add new feeds to Sparks" preference is now respected when importing from OPML and adding a feed using the Feedlet without customizing a feed's options.
- clicking on an item's excerpt/content now toggles its excerpt/content in Firefox
- set a max-height for group and feed select boxes
- feed urls using the `feed://` protocol are now automatically switched to `http://`
- updated Keyboard Shortcuts page with new shortcuts and less obvious previously existing ones
- added / keyboard shortcut to focus the search field
- added Enter/Return keyboard shortcut to expand/collapse focused item (better than 0 for laptop users)
- expanded MobileSafari support to Pre and Android browsers

Feeds that were brought to my attention that now work in Fever 1.01:

- https://mail.google.com/mail/feed/atom/inbox
- http://feeds.feedburner.com/TheiPhoneBlog
- http://apod.nasa.gov/apod.rss
- http://www.smbc-comics.com/rss.php
- http://news.ycombinator.com/rss
- http://www.ftd.de/static/ticker/ftd-topnews.rdf
- http://www.akademie.de/rss/tipps.xml
- http://www.wunschliste.de/rss.pl?news=1
- http://www.laut.de/partner/allgemein/news.rdf
- http://www.spiegel.de/schlagzeilen/index.rss
- http://www.lefigaro.fr/rss/figaro_une.xml
- http://www.tagesschau.de/xml/rss2
- http://google.blogspace.com/rss
- http://www.thinkgeek.com/thinkgeek.rss
- http://www.nzbsrus.com/rssfeed.php?cat=90
- http://rsspect.com/rss/qwantz.xml
