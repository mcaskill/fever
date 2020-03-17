<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Fever&deg; Compatibilizer</title>
<link rel="stylesheet" type="text/css" href="firewall/app/views/default/styles/reset.css?compatibilizer" />
<link rel="stylesheet" type="text/css" href="firewall/app/views/default/styles/shared.css?compatibilizer" />
<link rel="stylesheet" type="text/css" href="firewall/app/views/default/styles/page.css?compatibilizer" />
</head>
<body>
<div class="page">
	<div class="box">
		<div class="content">
			<h1>Compatibility Suite</h1>
			<h2>Does Fever have the hots for this server?</h2>
			
			<ul>
				<li id="paths">
					<div class="msg in-progress">Verifying path-related superglobals...</div>
					<div class="msg pass">Path-related superglobals appear to be in order!</div>
					<div class="msg fail">This server is misreporting path-related superglobals.</div>
				</li>

				<li id="query">
					<div class="msg in-progress">Verifying expected handling of empty query string variables...</div>
					<div class="msg pass">Query string variables are handled as expected!</div>
					<div class="msg fail">This server is does not create the expected superglobal indexes from empty query string variables.</div>
				</li>
				
				<li id="utf8">
					<div class="msg in-progress">Verifying server's ability to parse XML...</div>
					<div class="msg pass">Parsing XML shouldn't be a problem!</div>
					<div class="msg fail">This server does not provide the <code>utf8_encode()</code> function. Contact your host to have PHP recompiled with the <code>libxml2</code> or <code>libexpat</code> library.</div>
				</li>
				
				<li id="png">
					<div class="msg in-progress">Verifying server ability to process favicon images...</div>
					<div class="msg pass">Required image functions are available!</div>
					<div class="msg fail">PHP on this server was not compiled with the GD library or lacks PNG support. Contact your host to have PHP recompiled with the necessary library.</div>
					<div class="disclaimer">
						This issue is not a deal-breaker. It means some favicons may not display in MobileSafari because their original format is unsupported.
						<a href="#ignore" onclick="return pngIgnore(this);" class="btn text">Proceed regardless<i></i></a>
					</div>
				</li>
				
				<li id="mbstring">
					<div class="msg in-progress">Verifying server ability to convert string encoding...</div>
					<div class="msg pass">Extended character sets shouldn't be a problem!</div>
					<div class="msg fail">PHP on this server was not compiled with the mbstring (Multibyte String) library. Contact your host to have PHP recompiled with the necessary library.</div>
					<div class="disclaimer">
						This issue is not a deal-breaker. It means some characters in some non-English feeds may not display properly.
						<a href="#ignore" onclick="return mbIgnore(this);" class="btn text">Proceed regardless<i></i></a>
					</div>
				</li>

				<li id="flush">
					<div class="msg in-progress">Verifying server's ability to push content to the browser as it becomes available...</div>
					<div class="msg pass">Flush appears to be working!</div>
					<div class="msg fail">
						This server does not push content to the browser as it becomes available.
						<a href="./?fix-flush" target="checker" id="fix-flush" class="btn text">Attempt to fix?<i></i></a>
					</div>
					<div class="disclaimer">
						This issue is not a deal-breaker. Progress indicators may appear to stall but the processes they represent will continue to work in the background.
						<a href="#ignore" onclick="return flushIgnore(this);" class="btn text">Proceed regardless<i></i></a>
					</div>
				</li>
				
				<li id="mysql">
					<div class="msg in-progress">

						In order to verify sufficient MySQL privileges please enter your database connection details below.
						The suite will then attempt to perform common application actions. This information is not transmitted
						to a third-party.

						<form action="./?mysql" method="post" target="checker" id="db">
							<table>
								<tr>
									<th>Database Server</th>
									<td><span class="w"><input type="text" name="db[server]" value="localhost" /></span></td>
								</tr>
								<tr>
									<th>Database Name</th>
									<td><span class="w"><input type="text" name="db[database]" /></span></td>
								</tr>
								<tr>
									<th>Database Username</th>
									<td><span class="w"><input type="text" name="db[username]" /></span></td>
								</tr>
								<tr>
									<th>Database Password</th>
									<td><span class="w"><input type="password" name="db[password]" /></span></td>
								</tr>
								<tr>
									<td colspan="2" class="btn-row">
										<button><span class="btn text default">Check Privileges<i></i></span></button>
									</td>
								</tr>
							</table>
						</form>
					</div>
					<div class="msg pass">The provided database details have the required privileges!</div>
					<div class="msg fail" id="mysql-error"><!-- provided by callback --></div>
				</li>
			</ul>

			<div id="success"></div>
		</div><!-- /.content -->
	
		<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b><s><s></s></s></s>
	</div><!-- /.box -->
	<div class="footer">
		<a href="http://feedafever.com/" id="logo"><img src="firewall/app/views/default/styles/images/logo-fever.png" alt="Fever" /></a>
		<a href="http://feedafever.com">Fever</a> &copy; 2007&ndash;<?php e(gmdate('Y')); ?> <a href="http://shauninman.com/">Shaun Inman</a>. All rights reserved. 
		Available at <a href="http://feedafever.com">feedafever.com</a>.
	</div>
</div><!-- /.page -->
<iframe id="checker" name="checker" src="about:blank"></iframe>
<?php $paths = $this->install_paths(); ?>
<script type="text/javascript">

var failed = 0; // failed if > 0

function $(id) { return document.getElementById(id); };

// PHP-determined path variables
var php_dir		= '<?php e($paths['dir']); ?>';
var php_domain	= '<?php e($paths['domain']); ?>';
var php_trim	= '<?php e($paths['trim']); ?>';
var php_full	= '<?php e($paths['full']); ?>';

// JavaScript control values
var js_dir		= window.location.pathname.replace(/\/+[^\/]*$/, ''); // strip trailing slash
var js_domain	= window.location.host;
var js_trim		= js_domain.replace(/(^www\.|:\d+$)/gi, ''); // remove www. and :port
var js_full		= 'http://' + js_domain + js_dir;

// TODO: use CSS to hide show pass/fail messages
if
(
	php_dir 	!= js_dir 		|| 
	php_domain 	!= js_domain 	|| 
	php_trim	!= js_trim 		||
	php_full	!= js_full
)
{
	$('paths').className = 'done fail';
	failed++;
}
else
{
	$('paths').className = 'done pass';
};

function queryCheck()
{
	$('query').className = 'spinner';
	$('checker').src = './?query';
};

function queryCheckComplete(pass)
{
	if (!pass)
	{
		$('query').className = 'done fail';
		failed++;
	}
	else
	{

		$('query').className = 'done pass';
		utf8Check();
	};
};

queryCheck();

function utf8Check()
{
	$('utf8').className = 'spinner';
	$('checker').src = './?utf8';
};

function utf8CheckComplete(pass)
{
	if (!pass)
	{
		$('utf8').className = 'done fail';
		failed++;
	}
	else
	{

		$('utf8').className = 'done pass';
		pngCheck();
	};
};

function pngCheck()
{
	$('png').className = 'spinner';
	$('checker').src = './?png';
};

function pngCheckComplete(pass)
{
	if (!pass)
	{
		$('png').className = 'done fail';
		failed++;
	}
	else
	{

		$('png').className = 'done pass';
		mbCheck();
	};
};

function pngIgnore(elem)
{
	failed--;
	elem.parentNode.removeChild(elem);
	$('png').className = 'done ignore';
	mbCheck();
	return false;
};

function mbCheck()
{
	$('mbstring').className = 'spinner';
	$('checker').src = './?mbstring';
};

function mbCheckComplete(pass)
{
	if (!pass)
	{
		$('mbstring').className = 'done fail';
		failed++;
	}
	else
	{

		$('mbstring').className = 'done pass';
		flushCheck();
	};
}

function mbIgnore(elem)
{
	failed--;
	elem.parentNode.removeChild(elem);
	$('mbstring').className = 'done ignore';
	flushCheck();
	return false;
}

var now, flushed;
function flushCheck()
{
	now 	= (new Date()).getTime();
	flushed = false;
	$('checker').src = './?refresh&flush';
	$('flush').className = 'spinner';
};

function flushCheckComplete()
{
	// too much time has passed, fail
	if (!flushed)
	{
		if ((new Date).getTime() - now > 9 * 1000)
		{
			$('flush').className = 'done fail';
			failed++;
		}
		else
		{
			flushed = true;
			$('checker').src = 'about:blank';
			$('flush').className = 'done pass';
		};
	};
};

function flushFixComplete()
{
	var fix = $('fix-flush');
	fix.parentNode.removeChild(fix);
	// see if that worked
	flushCheck();
};

function flushIgnore(elem)
{
	failed--;
	elem.parentNode.removeChild(elem);
	
	var fix = $('fix-flush');
	if (fix)
	{
		fix.parentNode.removeChild(fix);
	};
	
	$('flush').className = 'done ignore';
	return false;
};

function mysqlCheckComplete(msg)
{
	if (msg == 'pass')
	{
		$('mysql').className = 'done pass';
		$('checker').src = './?passed';
	}
	else
	{
		var form = $('db');
		form.parentNode.removeChild(form);
		
		var error = $('mysql-error');
		error.innerHTML = msg;
		error.appendChild(form);
		
		$('mysql').className = 'done fail';
		failed++;
	};
};

function allChecksComplete(msg)
{
	$('success').innerHTML = msg;
	$('success').style.display = 'block';
};

</script>
</body>
</html>