================================================================================
FEED YOUR SERVER FEVER

Following Instructions 101: 

	Please read *every* step of the relevant instructions before undertaking 
	the first step. A little context goes a long way towards understanding.

Installation in three easy steps. Uninstall instructions below.
--------------------------------------------------------------------------------

1. Upload the entire /fever/ folder to your server.

2. Change the /fever/ folder's permissions to 755 (777 might be necessary on 
some servers).

3. Now load the Fever Server Compatibility Suite in a web browser 
<http://yourdomain.com/fever/boot.php> and proceed with the Suite. You should
have your MySQL database connection details ready. Please consult your host's
documentation or ask your sysadmin if you have a question about your database.
If you encounter 500 error your server is probably running SuPHP. Changing the
/fever/ folder's permissions to 775 might solve the problem.


================================================================================
Uninstalling Fever

Too hot for some
--------------------------------------------------------------------------------

1. Log into your Fever installation and click on the action menu.

2. Click on Uninstall and follow the instructions.

3. Finally, remove the /fever/ folder from your server.

If you cannot delete the /fever/ folder or the Uninstall option is not available
you should rename safety-unlace.php to unlace.php and load it in a web browser
<http://yourdomain.com/fever/unlace.php>.


================================================================================
Copyright 2008-2009 Shaun Inman. This package cannot be redistributed without
permission from http://shauninman.com/

More info at: http://feedafever.com/