# Answers

### Is Fever a hosted service?

No, Fever is a PHP and MySQL application that you run on your own server. Fever has been designed with ease of maintenance in mind and can automatically update itself.

### Is the $30 a one-time fee?

Fever is licensed like desktop software. Minor updates (eg. 1.0 to 1.1) will be free to licensed users while major upgrades (eg. 1.x to 2.0) will be discounted.

### Does Fever support HTTP authenticated feeds?

Yes, Fever supports basic HTTP authentication and will prompt you for a username and password for a feed if necessary.

### What are the server requirements for Fever?

Fever requires a Unix-like server (no Windows/IIS) running Apache, PHP 4.2.3+ (preferably compiled with mbstring and GD with PNG support) and MySQL 3.23+.

### What are the browser requirements for Fever?

Fever requires a WebKit- or Gecko-based browser including Safari 3+, Firefox 3+ and Safari on iOS. Webkit on Android while reported to work is not officially supported.

### How can I help you better help me?

Be sure to mention your browser and OS, and versions for both, as well as your Fever url. If a particular feed is causing problems, please include a link to the feed.

### Do you offer free trials or refunds?

Because Fever is written in PHP and the un-obfuscated source is made available to you upon purchase, I cannot offer free trials or refunds. [#](#faq-refunds)

### Can you help me with this third-party Fever client?

As the sole designer/developer responsible for Fever, [Mint](http://haveamint.com/) and a number of other projects, I cannot support third-party clients. Please contact their respective developer for support. [#](#faq-3rd-party)

### Is there a hosted option?

There are no plans to offer a hosted version of Fever. For the foreseeable future Fever will remain a prosumer product that requires some technical proficiency and a server of your own to run it on. [#](#faq-hosted)

### How do I resolve the "unable to push content" error?

First try clicking the "attempt to fix" button. If that doesn't work you should be given the option to "proceed regardless" along with the following note:

This issue is not a deal-breaker. Progress indicators may appear to stall but the processes they represent will continue to work in the background.

I'm still working on the definitive solution but some hosts allow you to change the way PHP is served on your domain (from PHP-as-CGI or FastCGI to the PHP Apache mod). Under the CGI options, gzip-encoding (which prevents push because the entire request needs to be generated and gzipped before sending) cannot be disabled on a per-directory basis. [#](#faq-push)

### How do I disable includes for the Fever directory?

Adding the following to an `.htaccess` file in your `/fever/` directory should disable the includes:

```
php_value	auto_prepend_file	none
php_value	auto_append_file	none
```

If that does not work you will need to contact your host for assistance. [#](#faq-includes)

### My server returns a 404 error for boot.php

Your CMS or blogging software might be interfering with the request. Try adding an `.htaccess` file to the `/fever/` directory containing the following:

```
<IfModule mod_rewrite.c>
RewriteEngine   off
</IfModule>

```

That might fix it. If not you should refer to your CMS, blogging software or host’s documentation. [#](#faq-404)

### The Compatibility Suite has stalled and none of the above helped

Rename `safety-unlace.php` in your `/fever/` directory to `unlace.php` and load it in a web browser, eg. `http://yourdomain.com/fever/unlace.php`

Then revisit `boot.php` to automatically pull down an updated Compatibility Suite which may resolve the problem.

Be sure to change `unlace.php` back to `safety-unlace.php` once you’re done.

If it does not, you can also try this. Open up `/fever/.htaccess` and change:

```
RewriteEngine on
```

To

```
RewriteEngine off
```

Then run the suite again but this time don’t choose “Attempt to fix.”

If you do not see an "attempt to fix" or "proceed regardless" button you might be using Safari 3\. Try upgrading to Safari 4 or Firefox, both of which are free. [#](#faq-unlace)

### How do I move Fever?

The best way to move Fever is to migrate the database as you normally would (using the `mysqldump` CLI or a similar migration tool) then install [a fresh copy of Fever](https://feedafever.com/gateway/public/fever.zip).

As long as you provide the correct connection details for your new database Fever will find and offer to use/update the existing database tables.

While you can just copy your Fever files from one server to another, this will cause permission and file ownership conflicts that will prevent Fever from updating in the future due to PHP security limitations. [#](#faq-move)

### My server returns a 403 error for the Feedlet

This is usually the result of an overzealous `mod_security` rule. You ca try disabling `mod_security` in your `/fever/` directory by adding the following to its .htaccess file:

```
<IfModule mod_security.c>
SecFilterEngine Off
SecFilterScanPOST Off
</IfModule>

```

It has come to my attention that a recent update to either Apache or the `mod_security` module seems to prevent per directory `.htaccess`-based overrides so you may need to ask your host about disabling it for that directory. [#](#faq-403)

### My server returns a 500 error for boot.php

Hosts running SuExec (also referred to as `mod_suphp`) do not allow scripts to run with world-writable permissions. Try changing the `/fever/` folder's permissions to `755` and each file inside it to `644`. [#](#faq-500)

### Fever no longer updates itself

PHP security demands precise ownership and permissions. Manually modifying (or moving) any of Fever’s files or folders will most likely prevent Fever from being able to update.

Once modified the easiest way to restore update functionality is to delete your Fever directory and upload a [fresh copy](https://feedafever.com/gateway/public/fever.zip). As long as you provide the same database connection details Fever will find and offer to use/update the existing database tables. [#](#faq-update)

### Can I transfer my Fever license to another domain?

Of course! You can use this convenient [license transfer form](http://feedafever.com/licenses/transfer)–just be sure to uninstall and remove Fever from old domain first. [#](#faq-transfer)

### Where can I find the Fever change log?

You can find a list of changes by date and version [here](https://feedafever.com/todone.txt). [#](#faq-changelog)

### What's the best way to set up Fever with Fluid.app?

I don't know if it's the best way but here's some screenshots of my Fever Fluid.app relevant preferences that work for me.

*   [General](https://feedafever.com/images/fluid-general.png) (earlier versions of Fluid.app)
*   [Behavior](https://feedafever.com/images/fluid-behavior.png) (later versions of Fluid.app)
*   [Tabs](https://feedafever.com/images/fluid-tabs.png)
*   [Advanced](https://feedafever.com/images/fluid-advanced.png)

[#](#faq-fluid)

### Where can I get the Fever iPhone app?

The iPhone-optimized version of Fever you see in the demo is a webclip. Visit your Fever installation in Safari and hit the + button in the bottom Safari bar then click "Add to Home Screen." [#](#faq-iphone)

### How do I refresh Fever on the iPhone?

The short answer: you don't.

Fever will automatically begin refreshing any stale feeds any time you hit the default screen. Returning from any sub-screen will automatically resync with the server. You can also manually resync by tapping the Fever logo. Regardless of which interface you use to access Fever, consider setting up a cron job to handle refreshing feeds (as described on the Extras page in your Fever installation). [#](#faq-refresh)

### Does Fever support multiple users?

No, Fever is a single-user application. [#](#faq-multiuser)

### Can Fever be installed locally?

No, Fever cannot be licensed or installed on a local machine. [#](#faq-local)

### Will there be an official native Fever app for the iPhone/iPod touch/iPad?

Not in the near future, no. If you are a developer interested in creating a native Fever app you might be interested in the [new API](https://feedafever.com/api). [#](#faq-native)