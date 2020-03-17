<?php

error_reporting(E_ALL);

class Apptivator
{
	var $version 	= 1;
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
	function Apptivator()
	{
		$this->app_name = trim(file_get_contents(FIREWALL_ROOT.'receipt.txt'));
		define('REQUEST_UA', $this->app_name.' Apptivator/'.$this->formatted_version().' (Self-hosted Application Activator)');
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
		
		if (isset($_POST['activation_key']))
		{
			$this->activate();
		}
		
		$this->render('activation');
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
	 activate()
	
	 todo: L10N
	 **************************************************************************/
	function activate()
	{
		if (!isset($_POST['accept_eula']))
		{
			$this->error('To continue with installation you <em>must</em> accept the '.$this->app_name.' End User License Agreement.');
		}
		
		if (empty($_POST['activation_key']))
		{
			$this->error('Please enter your Activation Key for '.$this->app_name);
		}
		
		if ($this->has_error())
		{
			$this->render('errors');
			exit();
		}
		
		// todo: copy from boot.php
		define('SOURCES_URL', 'http://feedafever.com/gateway/');
		
		global $REQUEST_TIMEOUT;
		$REQUEST_TIMEOUT = 30;
		
		// todo: validate
		$activation_key	= $_POST['activation_key'];
		$paths 			= $this->install_paths();
		$response 		= post(SOURCES_URL, array
		(
			'app_name'			=> low($this->app_name),
			'activation_key' 	=> $activation_key,
			'domain_name'		=> $paths['trim']
		),
		array('X-Apptivator-Action:Install'));
		
		if (!empty($response['error']['msg'])) 
		{
			$this->annotate_error("Could not connect to the {$this->app_name} activation server. {$response['error']['type']} error:");
			$this->error("{$response['error']['msg']} ({$response['error']['no']})");
			$this->render('errors');
			exit();
		}
		else if (!isset($response['headers']['X-Apptivator-Verified']) || !$response['headers']['X-Apptivator-Verified'])
		{
			$this->error('The Activation Key <strong>'.$activation_key.'</strong> is not valid for '.$this->app_name.' on: '.$paths['trim']);
			$this->render('errors');
			exit();
		}
		
		$zip_name = 'data';
		if (m('#([^"]+).zip"\s*$#', $response['headers']['Content-Disposition'], $m))
		{
			$zip_name = $m[1];
		}
		
		$zip_data = $response['body'];
		$zip_path = FIREWALL_ROOT.'tmp/'.$zip_name.'.zip';
		
		mkdir(FIREWALL_ROOT.'tmp');
		save_to_file($zip_data, $zip_path);
		include(FIREWALL_ROOT.'app/libs/pclzip/pclzip.lib.php');
		$archive = new PclZip($zip_path);
		
		$contents 	= $archive->listContent();
		$root_path 	= '';
		
		foreach($contents as $file_meta)
		{
			if (m('#(.+)/app/$#', $file_meta['filename'], $m))
			{
				$root_path = $m[1];
				break;
			}
		}
		
		rm(FIREWALL_ROOT.'app');
		$archive->extract(PCLZIP_OPT_PATH, FIREWALL_ROOT, PCLZIP_OPT_REMOVE_PATH, $root_path);
		rm(FIREWALL_ROOT.'tmp');
		rm(FIREWALL_ROOT.$zip_name); // not sure where this is coming from
		
		$key_php  = '<?php';
		$key_php .= <<<PHP

define('ACTIVATION_KEY', '{$activation_key}');

PHP;
		save_to_file($key_php, FIREWALL_ROOT.'config/key.php');
		
		header('Location:./');
		exit();
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