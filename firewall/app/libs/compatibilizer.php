<?php

error_reporting(E_ALL);

class Compatibilizer
{
	var $version 	= 5;
	var $app_name 	= '';
	var $errors		= array
	(
		'fatal'	=> false,
		'note'	=> '',
		'list'	=> array()
	);

	/**************************************************************************
	 Apptivator()
	 **************************************************************************/
	function Compatibilizer()
	{
		$this->app_name = trim(file_get_contents(FIREWALL_ROOT.'receipt.txt'));
		define('REQUEST_UA', $this->app_name.' Compatibilizer/'.$this->formatted_version().' ('.$this->app_name.' Server Compatibility Suite)');
	}

	/**************************************************************************
	 formatted_version()
	 **************************************************************************/
	function formatted_version()
	{
		$version = (!isset($this->cfg['version']) || !$this->cfg['version']) ? $this->version : $this->cfg['version'];
		$len = (substr($version.'', -1) == '0') ? 1 : 2;
		return number_format($version/100, $len);
	}

	/**************************************************************************
	 error()
	 **************************************************************************/
	function error($error, $level = 0)
	{
		$this->errors['fatal'] = ($level == 2);

		if ($level) // higher priority, add to the beginning of the errors list
		{
			array_unshift($this->errors['list'], $error);
		}
		else
		{
			array_push($this->errors['list'], $error);
		}
	}

	/**************************************************************************
	 fatal_error()
	 **************************************************************************/
	function fatal_error($error)
	{
		$this->error($error, 2);
	}

	/**************************************************************************
	 annotate_error()
	 **************************************************************************/
	function annotate_error($note)
	{
		$this->errors['note'] = $note;
	}

	/**************************************************************************
	 drop_error()
	 **************************************************************************/
	function drop_error($containing = '')
	{
		if (empty($containing))
		{
			array_pop($this->errors['list']); // remove last
		}
		else
		{
			$errors_list = array();
			foreach ($this->errors['list'] as $error)
			{
				if (strpos($error, $containing) !== false)
				{
					continue;
				}
				array_push($errors_list, $error);
			}
			$this->errors['list'] = $errors_list;
		}
	}

	/**************************************************************************
	 has_error()

	 Whether or not any errors or a specific error has been logged already.
	 **************************************************************************/
	function has_error($containing = '')
	{
		if (empty($containing) && !empty($this->errors['list']))
		{
			return true;
		}

		foreach ($this->errors['list'] as $error)
		{
			if (strpos($error, $containing) !== false)
			{
				return true;
			}
		}
		return false;
	}

	/**************************************************************************
	 has_fatal_error()

	 Whether or not a fatal error has occured.
	 **************************************************************************/
	function has_fatal_error()
	{
		return $this->errors['fatal'];
	}

	/**************************************************************************
	 route()
	 **************************************************************************/
	function route()
	{
		if (isset($_GET['uninstall']))
		{
			$this->uninstall(true);
		}

		if (isset($_GET['query']))
		{
			$this->render('query_check');
		}

		if (isset($_GET['utf8']))
		{
			$this->render('utf8_check');
		}

		if (isset($_GET['png']))
		{
			$this->render('png_check');
		}

		if (isset($_GET['mbstring']))
		{
			$this->render('mbstring_check');
		}

		if (isset($_GET['flush']))
		{
			$this->render('flush_check');
		}

		if (isset($_GET['fix-flush']))
		{
			$this->render('flush_fix');
		}

		if (isset($_GET['mysql']))
		{
			$this->render('mysql_check');
		}

		if (isset($_GET['passed']))
		{
			$this->render('passed');
		}

		$this->render('check');
	}

	/**************************************************************************
	 render()
	 **************************************************************************/
	function render($view_name = '', $string_output = false)
	{
		static $depth = 0;
		$depth++;

		if ($string_output)
		{
			ob_start();
		}
		include($this->view_file($view_name));
		if ($string_output)
		{
			return ob_get_clean();
		}

		$depth--;
		if ($depth == 0)
		{
			exit();
		}
	}

	/**************************************************************************
	 view_file()
	 **************************************************************************/
	function view_file($base_file_name)
	{
		return FIREWALL_ROOT.'app/views/default/'.$base_file_name.'.php';
	}

	/**************************************************************************
	 install_paths()
	 **************************************************************************/
	function install_paths()
	{
		$paths	= array();
		$self	= (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF']))?$_SERVER['PHP_SELF']:((isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME']))?$_SERVER['SCRIPT_NAME']:$_SERVER['SCRIPT_URL']);
		$domain	= (!empty($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] != $_SERVER['SERVER_NAME'])?$_SERVER["HTTP_HOST"]:$_SERVER['SERVER_NAME'];

		$paths['dir']		= r('#/+[^/]*$#', '', $self);
		$paths['domain']	= $domain;
		$paths['trim']		= r('/(^www\.|:\d+$)/', '', $paths['domain']);
		$paths['full']		= 'http://'.$paths['domain'].$paths['dir'];

		return $paths;
	}

	/**************************************************************************
	 apptivate()
	 **************************************************************************/
	function apptivate()
	{
		// todo: copy from boot.php
		define('SOURCES_URL', 'http://feedafever.com/gateway/');

		mkdir(FIREWALL_ROOT.'tmp');
		remote_copy(SOURCES_URL.'public/apptivator.zip', FIREWALL_ROOT.'tmp/apptivator.zip');
		include(FIREWALL_ROOT.'app/libs/pclzip/pclzip.lib.php');
		$archive = new PclZip(FIREWALL_ROOT.'tmp/apptivator.zip');
		rm(FIREWALL_ROOT.'app');
		$archive->extract(PCLZIP_OPT_PATH, FIREWALL_ROOT, PCLZIP_OPT_REMOVE_PATH, 'apptivator');
		rm(FIREWALL_ROOT.'tmp');
	}

	/**************************************************************************
	 uninstall()
	 **************************************************************************/
	function uninstall($confirm = false)
	{
		rm(FIREWALL_ROOT);
		rm('index.php');
		header('Location:./');
		exit();
	}
}