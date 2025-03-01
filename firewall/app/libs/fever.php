<?php
define('FEVER_TEXT_TAGS',	'h1,h2,h3,h4,h5,h6,ul,ol,li,dl,dt,dd,i,b,em,strong,del,ins,s,strike,blockquote,cite,abbr,acronym,p,br,code,pre,table,tbody,thead,tr,th,td,a[href],*[title]');
define('FEVER_IMG_TAGS',	FEVER_TEXT_TAGS.',img[src|alt|width|height]');
define('FEVER_MOBILE', 		'mobile');
define('VALID_EMAIL', 		'!^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$!');

class Fever
{
	public $app_name	= 'Fever';
	public $version 	= 142;
	protected $db		= array
	(
		'server' 	=> 'localhost',
		'database'	=> '',
		'username'	=> '',
		'password'	=> '',
		'prefix'	=> 'fever_',
		'connected'	=> false
	);
	protected $dbc		= null;
	protected $cfg		= array
	(
		'email'					=> '',
		'password'				=> '',

		'version'				=> 0,
		'activation_key'		=> '',
		'installed_on_time' 	=> 0,

		'last_viewed_on_time'	=> 0,
		'last_session_on_time'	=> 0,

		'last_optimize_on_time'	=> 0,
		'last_repair_on_time'	=> 0,

		'updates'				=> array
		(
			'last_checked_on_time' 	=> 0,
			'last_updated_on_time'	=> 0,
			'last_checked_version'	=> 0,
			'last_updated_manually'	=> 0
		)
	);
	protected $prefs	= array
	(
		'ui'			=> array
		(
			'section'	=> 0, // Hot
			'previous'	=> 0, // also Hot
			'group_id'	=> 0, // supergroup
			'feed_id'	=> 0, // superfeed
			'search'	=> '',
			'has_focus'	=> 'groups', // id without the hash

			'show_feeds' 	=> 1,
			'show_read'		=> 1, // new default in 1.29 to avoid confusion

			'hot_start'		=> 0, // now
			'hot_range'		=> 7 // 7 days, one week
		),

		'use_celsius' 		=> false,
		'refresh_interval'	=> 15, 	// minutes
		'item_expiration'	=> 10, 	// weeks
		'new_window'		=> true,
		'unread_counts'		=> false,
		'layout'			=> 0, // 0:fixed, 1:fluid, 2:???
		'item_excerpts'		=> true,
		'item_allows'		=> 1, // text w/images
		'sort_order'		=> 0, // newest first
		'auto_spark'		=> false,
		'per_page'			=> 20,
		'auto_read'			=> true,

		// iPhone-specifc
		'mobile_read_on_scroll' 	=> true,
		'mobile_read_on_back_out'	=> true,
		'mobile_view_in_app'		=> true,

		'auto_reload'		=> false, // reload after refresh
		'auto_refresh'		=> 1, // iframe (0:cron)
		'auto_update'		=> 1,
		'toggle_click'		=> 1,
		'anonymize'			=> 0,

		'session_timeout'	=> 6, // minutes
		'blacklist'			=> '',

		'share'				=> false,
		'services'			=> array
		(
			array
			(
				'name' 	=> 'Email',
				'url'	=> 'mailto:?subject=%t&body=%u',
				'key'	=> 'e'
			),
			array
			(
				'name' 	=> 'Delicious',
				'url'	=> 'http://delicious.com/save?url=%u&title=%t&v=5&noui=1&jump=doclose',
				'key'	=> 'd'
			),
			array
			(
				'name' 	=> 'Instapaper',
				'url'	=> 'http://instapaper.com/edit?url=%u&title=%t&summary=%e',
				'key'	=> 'i'
			),
			array
			(
				'name' 	=> 'Twitter',
				'url'	=> 'http://twitter.com/?status=%t%20%u',
				'key'	=> 't'
			)
		)
	);
	protected $manifest = array
	(
		'_config'		=> "
		(
			`id` int(10) unsigned NOT NULL auto_increment,
			`cfg` MEDIUMTEXT NOT NULL,
			`prefs` MEDIUMTEXT NOT NULL,
			PRIMARY KEY  (`id`)
		)",
		'feeds'			=> "
		(
			`id` int(11) unsigned NOT NULL auto_increment,
			`favicon_id` int(11) unsigned default '0',
			`title` varchar(255) default NULL,
			`url` varchar(255) NOT NULL default '',
			`url_checksum` int(10) unsigned NOT NULL,
			`site_url` varchar(255) default NULL,
			`domain` varchar(255) default NULL,
			`requires_auth` tinyint(1) unsigned default '0',
			`auth` varchar(255) default NULL,
			`is_spark` tinyint(1) unsigned NOT NULL default '0',
			`prevents_hotlinking` tinyint(1) unsigned NOT NULL default '0',
			`item_excerpts` tinyint(1) NOT NULL default '-1',
			`item_allows` tinyint(1) NOT NULL default '-1',
			`unread_counts` tinyint(1) NOT NULL default '-1',
			`sort_order` tinyint(1) NOT NULL default '-1',
			`last_refreshed_on_time` int(10) unsigned NOT NULL default '0',
			`last_updated_on_time` int(10) unsigned NOT NULL default '0',
			`last_added_on_time` int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `url_checksum` (`url_checksum`),
			KEY `favicon_id` (`favicon_id`),
			KEY `title` (`title`),
			KEY `domain` (`domain`),
			KEY `is_spark` (`is_spark`),
			KEY `last_refreshed_on_time` (`last_refreshed_on_time`),
			KEY `last_updated_on_time` (`last_updated_on_time`),
			KEY `last_added_on_time` (`last_added_on_time`)
		)",
		'feeds_groups'	=> "
		(
			`feed_id` int(11) unsigned NOT NULL,
			`group_id` int(11) unsigned default NULL,
			KEY `feed_group` (`feed_id`,`group_id`)
		)",
		'groups'		=> "
		(
			`id` int(11) unsigned NOT NULL auto_increment,
			`title` varchar(255) NOT NULL,
			`item_excerpts` tinyint(1) NOT NULL default '-1',
			`item_allows` tinyint(1) NOT NULL default '-1',
			`unread_counts` tinyint(1) NOT NULL default '-1',
			`sort_order` tinyint(1) NOT NULL default '-1',
			PRIMARY KEY  (`id`),
			KEY `title` (`title`)
		)",
		'items'			=> "
		(
			`id` int(11) unsigned NOT NULL auto_increment,
			`feed_id` int(11) unsigned default NULL,
			`uid` varchar(255) default NULL,
			`title` varchar(255) default NULL,
			`author` varchar(255) default NULL,
			`description` text,
			`link` varchar(255) default NULL,
			`url_checksum` int(10) unsigned NOT NULL,
			`read_on_time` int(10) unsigned NOT NULL default '0',
			`is_saved` tinyint(1) unsigned NOT NULL default '0',
			`created_on_time` int(10) unsigned NOT NULL,
			`added_on_time` int(10) unsigned NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `feed_id` (`feed_id`),
			KEY `uid` (`feed_id`, `uid`),
			KEY `title` (`title`),
			KEY `url_checksum` (`url_checksum`),
			KEY `read_on_time` (`read_on_time`),
			KEY `is_saved` (`is_saved`),
			KEY `created_on_time` (`created_on_time`),
			KEY `added_on_time` (`added_on_time`)
		)",
		'links'			=> "
		(
			`id` int(11) unsigned NOT NULL auto_increment,
			`feed_id` int(11) unsigned NOT NULL,
			`item_id` int(11) unsigned NOT NULL,
			`is_blacklisted` tinyint(1) unsigned NOT NULL default '0',
			`is_item` tinyint(1) unsigned NOT NULL default '0',
			`is_local` tinyint(1) unsigned NOT NULL default '0',
			`is_first` tinyint(1) unsigned NOT NULL default '0',
			`title` varchar(255) default NULL,
			`url` varchar(255) default NULL,
			`url_checksum` int(10) unsigned NOT NULL,
			`title_url_checksum` int(10) unsigned NOT NULL,
			`weight` int(11) unsigned default '0',
			`created_on_time` int(10) unsigned NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `feed_id` (`feed_id`),
			KEY `item_id` (`item_id`),
			KEY `created_on_time` (`created_on_time`),
			KEY `weight` (`weight`),
			KEY `url_checksum` (`url_checksum`),
			KEY `title_url_checksum` (`title_url_checksum`),
			KEY `is_blacklisted` (`is_blacklisted`),
			KEY `is_item` (`is_item`),
			KEY `is_local` (`is_local`),
			KEY `is_first` (`is_first`)
		)",
		'favicons' 		=> "
		(
			`id` int(11) unsigned NOT NULL auto_increment,
			`cache` text NOT NULL,
			`url` varchar(255) NOT NULL,
			`url_checksum` int(10) unsigned NOT NULL,
			`last_cached_on_time` int(10) unsigned default '0',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `url_checksum` (`url_checksum`),
			KEY `last_cached_on_time` (`last_cached_on_time`)
		)"
	);

	protected $vars		= array();
	protected $errors	= array
	(
		'fatal'	=> false,
		'note'	=> '',
		'list'	=> array()
	);
	protected $groups	= array();
	protected $feeds	= array();
	protected $items	= array();

	protected $total_feeds 	= 0;
	protected $total_items	= 0;
	protected $total_unread	= 0;
	protected $total_saved	= 0;

	protected $last_refreshed_on_time	= 0;
	protected $last_cached_on_time		= 0;

	protected $links_by_degrees		= array();
	protected $group_ids_by_feed_id	= array();
	protected $feed_ids_by_group_id	= array();
	protected $sparks_feed_ids		= array();
	protected $saved_feed_ids		= array();
	protected $feed_ids				= array();

	protected $is_silent = false;
	protected $is_mobile = false;
	protected $is_ipad = false;
	protected $page	= 1;

	var $DateParser;

	protected $item_allows	= array
	(
		'text only',
		'text w/images',
		'unfiltered'
	);
	protected $sort_order 	= array
	(
		'newest first',
		'oldest first'
	);
	protected $hot_start	= array
	(
		0 => 'now',
		'-' => '-',
		1 => 'yesterday',
		2 => '2 days ago',
		3 => '3 days ago',
		4 => '4 days ago',
		5 => '5 days ago',
		6 => '6 days ago',
		'--' => '-',
		7 	=> 'a week ago',
		14 	=> '2 weeks ago',
		21 	=> '3 weeks ago',
		28 	=> '4 weeks ago',
		35 	=> '5 weeks ago'
	);
	protected $hot_range	= array
	(
		1 => 'day',
		2 => '2 days',
		3 => '3 days',
		4 => '4 days',
		5 => '5 days',
		6 => '6 days',
		'-' => '-',
		7 	=> 'week',
		14 	=> '2 weeks',
		21 	=> '3 weeks',
		28 	=> '4 weeks',
		'--' => '-',
		31 	=> 'month'
	);
	protected $exp_range	= array
	(
		2,4,6,10
	);

	/**************************************************************************
	 Fever()
	 **************************************************************************/
	public function __construct()
	{
		$this->db['server']		= FEVER_DB_SERVER;
		$this->db['database'] 	= FEVER_DB_DATABASE;
		$this->db['username'] 	= FEVER_DB_USERNAME;
		$this->db['password'] 	= FEVER_DB_PASSWORD;
		$this->db['prefix']		= FEVER_DB_PREFIX;

		if ($this->connect())
		{
			// $this->query('SET sql_mode="STRICT_ALL_TABLES"');
			$this->load();
		}
		define('REQUEST_UA', 'Fever/'.$this->formatted_version().' (Feed Parser; http://feedafever.com; Allow like Gecko)');
		$this->is_mobile =
		(
			isset($_SERVER['HTTP_USER_AGENT']) &&
			m('#(iPhone|iPod|Pre|Pixi|Android|Mobile|webOS)#', $_SERVER['HTTP_USER_AGENT'], $m)
		);
		$this->is_ipad =
		(
			isset($_SERVER['HTTP_USER_AGENT']) &&
			m('#(iPad|Tablet)#', $_SERVER['HTTP_USER_AGENT'], $m)
		);

		// allow forcing mobile
		if (isset($_GET['mobile'])) {
			$this->is_ipad = false;
			$this->is_mobile = true;
		}

		if (err()) debug(array('GET'=>$_GET,'POST'=>$_POST, 'capabilities'=>$this->capabilities()), 'New instance of Fever');
	}

	/**************************************************************************
	 capabilities()
	 **************************************************************************/
	public function capabilities()
	{
		$caps = array();
		$caps['fever_version']			= version_clean($this->formatted_version());
		$caps['php_version'] 			= version_clean(phpversion());
		$caps['mysql_client_version'] 	= version_clean($this->dbc->client_version());
		$caps['mysql_server_version'] 	= version_clean($this->dbc->server_version());
		$caps['has_pdo_mysql']			= extension_loaded('pdo_mysql')?1:0; 			// SIDB API, prefered
		$caps['has_mysqli']				= extension_loaded('mysqli')?1:0;				// SIDB API, fallback
		$caps['has_mysql']				= function_exists('mysql_connect')?1:0;			// SIDB API, last resort
		$caps['has_iconv']				= function_exists('iconv')?1:0; 				// OMDOMDOM encoding, prefered
		$caps['has_mbstring']			= function_exists('mb_convert_encoding')?1:0; 	// OMDOMDOM encoding, fallback
		$caps['has_gd_png']				= has_gd_png()?1:0;								// favicon caching
		$caps['has_curl']				= in(get_loaded_extensions(), 'curl')?1:0;		// request
		if ($caps['has_curl'] && in(ini_get('disable_functions'), 'curl_')) $caps['has_curl'] = 0;
		return $caps;
	}

	/**************************************************************************
	 formatted_version()
	 **************************************************************************/
	public function formatted_version()
	{
		$version = (!isset($this->cfg['version']) || !$this->cfg['version']) ? $this->version : $this->cfg['version'];
		$len = (substr($version.'', -1) == '0') ? 1 : 2;
		return number_format($version/100, $len);
	}

	/**************************************************************************
	 error()
	 **************************************************************************/
	public function error($error, $level = 0)
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
	public function fatal_error($error)
	{
		$this->error($error, 2);
	}

	/**************************************************************************
	 annotate_error()
	 **************************************************************************/
	public function annotate_error($note)
	{
		$this->errors['note'] = $note;
	}

	/**************************************************************************
	 drop_error()
	 **************************************************************************/
	public function drop_error($containing = '')
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
	 reset_errors()
	 **************************************************************************/
	public function reset_errors()
	{
		$data = get_class_vars(static::class);
		$this->errors = $data['errors'];
	}

	/**************************************************************************
	 render_errors()
	 **************************************************************************/
	public function render_errors()
	{
		$html = '';
		if(!empty($this->errors['note']))
		{
			$html .= '<div class="error-note">'.$this->errors['note'].'</div>';
		}

		if(!empty($this->errors['list']))
		{
			$html .= '<ul class="errors">';
			foreach($this->errors['list'] as $error)
			{
				$html .= '<li>'.$error.'</li>';
			}
			$html .= '</ul>';
		}
		e($html);
	}

	/**************************************************************************
	 connect()

	 TODO: rephrase recognized MySQL errors into helpful human-readable errors
	 **************************************************************************/
	public function connect()
	{
		$this->dbc = SIDB($this->db['database'], $this->db['username'], $this->db['password'], $this->db['server']); //, SIDB_API_MYSQL);
		$connected = $this->dbc->is_connected;

		if ($this->dbc->error) $this->fatal_error($this->dbc->error);
		if (!$connected) $this->annotate_error('<p>Fever was unable to connect to the database.</p>');

		$this->db['connected'] = $connected;
		return $connected;
	}

	/**************************************************************************
	 is_connected()
	 **************************************************************************/
	public function is_connected()
	{
		return $this->db['connected'];
	}

	/**************************************************************************
	 is_connected()
	 **************************************************************************/
	public function close()
	{
		$this->dbc->close();
		exit();
	}

	/**************************************************************************
	 query()
	 **************************************************************************/
	public function query($query)
	{
		$before = ms();
		$error  = false;
		if (!$this->dbc->query($query)) {
			$error = $this->dbc->error;
			$this->error($error);
			debug($error);
		}
		$duration = number_format(ms() - $before, 4);
		debug($query, $duration.'s');

		return !$error;
	}

	/**************************************************************************
	 insert()
	 **************************************************************************/
	public function insert($query)
	{
		$this->query($query);
		return $this->dbc->insert_id();
	}

	/**************************************************************************
	 save_one()
	 **************************************************************************/
	public function save_one($table, $row_data, $where = '')
	{
		if (!isset($this->manifest[$table]))
		{
			return;
		}

		if (isset($row_data['id'])) // update
		{
			$sql_fragments = array();
			$values = array();
			foreach ($row_data as $col => $value)
			{
				if ($col == 'id' || strpos($this->manifest[$table], "`{$col}`") === false)
				{
					continue;
				}

				$sql_fragments[] 	= "`{$col}` = ?";
				$values[] 			= $value;
			}
			$sql = "UPDATE `{$this->db['prefix']}{$table}` SET ".implode(',', $sql_fragments)." WHERE `id` = ?";
			if (!empty($where))
			{
				$sql .= " AND ({$where})";
			}
			array_unshift($values, $sql);
			array_push($values, $row_data['id']);
			$update = call_user_func_array(array($this, 'prepare_sql'), $values);
			$this->query($update);
		}
		else // insert
		{
			$columns 	= array();
			$values		= array();
			foreach ($row_data as $col => $value)
			{
				if (strpos($this->manifest[$table], "`{$col}`") === false)
				{
					continue;
				}
				$columns[]	= $col;
				$values[]	= $value;
			}

			if (!empty($columns))
			{
				$sql = "INSERT INTO `{$this->db['prefix']}{$table}` (`".implode('`,`', $columns)."`) VALUES (?".str_repeat(',?', count($columns) - 1).")";
				array_unshift($values, $sql);
				$insert = call_user_func_array(array($this, 'prepare_sql'), $values);
				return $this->insert($insert);
			}
		}
	}

	/******************************************************************************
	 query_one()

	 Selects a single record using the provided query.
	 ******************************************************************************/
	public function query_one($query)
	{
		$query = "{$query} LIMIT 1";

		if ($this->query($query)) {
			$rows = $this->dbc->rows();
			if ($rows) {
				return $rows[0];
			}
		}
		return false;
	}

	/******************************************************************************
	 query_all()

	 Selects all records using the provided query.
	 ******************************************************************************/
	public function query_all($query)
	{
		$return = array();
		if ($this->query($query)) {
			$rows = $this->dbc->rows();
			if ($rows) {
				$return = $rows;
			}
		}
		return $return;
	}

	/******************************************************************************
	 query_col()

	 Selects the value of a single column from a single record using the provided
	 query.
	 ******************************************************************************/
	public function query_col($col, $query)
	{
		if ($row = $this->query_one($query)) {
			if (isset($row[$col])) {
				return $row[$col];
			}
			else {
				$this->error("Column <code>{$col}</code> doesn't exist.<br />Query: {$query}");
			}
		}
		return false;
	}

	/******************************************************************************
	 query_cols()

	 Selects the value of a single column from a number of records using the provided
	 query.
	 ******************************************************************************/
	public function query_cols($col, $query)
	{
		$return = array();
		if ($rows = $this->query_all($query)) {
			foreach($rows as $row) {
				if (isset($row[$col])) {
					$return[] = $row[$col];
				}
				else {
					$this->error("Column `{$col}` doesn't exist.<br />Query: {$query}");
					break;
				}
			}
		}
		return $return;
	}

	/******************************************************************************
	 get_one()

	 Selects a single record from $table (without prefix) where $where matches.
	 ******************************************************************************/
	public function get_one($table, $where = 1)
	{
		return $this->query_one("SELECT * FROM `{$this->db['prefix']}{$table}` WHERE {$where}");
	}

	/******************************************************************************
	 get_col()

	 Selects a single column from a single record from $table (without prefix) where
	 $where matches.
	 ******************************************************************************/
	public function get_col($col, $table, $where)
	{
		return $this->query_col($col, "SELECT `{$col}` FROM `{$this->db['prefix']}{$table}` WHERE {$where}");

	}

	/******************************************************************************
	 get_cols()

	 Selects a single column from a number of records from $table (without prefix)
	 where $where matches.
	 ******************************************************************************/
	public function get_cols($col, $table, $where = 1)
	{
		return $this->query_cols($col, "SELECT `{$col}` FROM `{$this->db['prefix']}{$table}` WHERE {$where}");

	}

	/******************************************************************************
	 get_count()

	 Returns the total number of records from $table (without prefix) where $where
	 matches.
	 ******************************************************************************/
	public function get_count($table, $where = 1)
	{
		return $this->query_col('total', "SELECT COUNT(*) AS 'total' FROM `{$this->db['prefix']}{$table}` WHERE {$where}");
	}

	/******************************************************************************
	 get_all()

	 Selects all records from $table (without prefix) where $where matches.
	 ******************************************************************************/
	public function get_all($table, $where = 1)
	{
		return $this->query_all("SELECT * FROM `{$this->db['prefix']}{$table}` WHERE {$where}");
	}

	/**************************************************************************
	 escape_sql()
	 **************************************************************************/
	public function escape_sql($str = '')
	{
		return r("/(^'|'$)/", '', $this->dbc->quote($str));
	}

	/**************************************************************************
	 prepare_sql()

	 Sample usage:

		prepare_sql('`email` = ? AND `password` = ?', 'my@email.com', '94s5w3R6');

	 **************************************************************************/
	public function prepare_sql($query)
	{
		$args = func_get_args();
		return call_user_func_array(array($this->dbc, 'prepare'), $args);
	}

	/**************************************************************************
	 render()

	 TODO: make sure resource exists
	 **************************************************************************/
	public function render($view_name = '', $string_output = false)
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
			$this->close();
		}
	}

	/**************************************************************************
	 view_file()
	 **************************************************************************/
	public function view_file($base_file_name)
	{
		$dir	= 'default';
		if ($this->is_mobile)
		{
			$dir = 'mobile';
		}
		$file = FIREWALL_ROOT.'app/views/'.$dir.'/'.$base_file_name.'.php';
		if (!file_exists($file))
		{
			$file = FIREWALL_ROOT.'app/views/default/'.$base_file_name.'.php';
		}
		return $file;
	}

	/**************************************************************************
	 route()
	 **************************************************************************/
	public function route()
	{
		// the database has been configured but we are not connected
		if ($this->is_db_configured() && !$this->is_connected())
		{
			$this->render('errors');
		}

		if (!$this->is_installed())
		{
			$this->route_installation();
		}

		if ($this->cfg['version'] && $this->cfg['version'] < $this->version)
		{
			$this->update();
		}

		if (isset($_GET['refresh']))
		{
			$this->route_refresh();
		}

		$this->check_for_updates();

		// used for caching on the iPhone
		if (isset($_GET['manifest']))
		{
			$this->route_manifest();
		}

		if ($this->prefs['share'] && isset($_GET['rss']))
		{
			$this->route_rss();
		}

		if (isset($_GET['api']))
		{
			$this->route_api();
		}

		$this->route_auth();

		/**********************************************************************
		login required below this point
		***********************************************************************/

		if (isset($_GET['revert']))
		{
			$this->route_revert();
		}

		if (isset($_GET['uninstall']))
		{
			$this->route_uninstall();
		}

		if (isset($_GET['empty']))
		{
			$this->route_empty();
		}

		if (isset($_GET['unorphan']))
		{
			$this->route_unorphan();
		}

		if (isset($_GET['flush']))
		{
			$this->route_flush();
		}

		if (isset($_GET['subscribe']))
		{
			$this->route_subscribe();
		}

		if (isset($_GET['manage']))
		{
			$this->route_manage();
		}

		if (isset($_GET['blacklist']))
		{
			$this->route_blacklist();
		}

		if (isset($_GET['updates']))
		{
			$this->route_updates();
		}

		if (isset($_GET['update']))
		{
			$this->route_update();
		}

		if (isset($_GET['favicons']))
		{
			$this->route_favicons();
		}

		if (isset($_GET['empty-cache']))
		{
			$this->empty_cache(true);
		}

		if (isset($_GET['img']))
		{
			$this->route_image();
		}

		if (isset($_GET['visit']))
		{
			$this->visit_feed_site($_GET['visit']);
		}

		if (isset($_GET['feedlet']))
		{
			$this->route_feedlet();
		}

		if ($this->cfg['updates']['last_updated_on_time'])
		{
			$this->route_updated();
		}

		/**********************************************************************
		update message delivered before this point
		***********************************************************************/

		if (isset($_GET['extras']))
		{
			$this->route_extras();
		}

		if (isset($_GET['shortcuts']))
		{
			$this->route_shortcuts();
		}

		$this->route_reader();
	}

	/**************************************************************************
	 route_installation()

	 License verification performed by the separate Apptivator prior to install.
	 **************************************************************************/
	public function route_installation()
	{
		// use the Compatibilizer-captured DB details if available.
		if (file_exists(FIREWALL_ROOT.'receipt_db.php') && !isset($_POST['action']))
		{
			include(FIREWALL_ROOT.'receipt_db.php');

			$_POST['action'] 		= 'database';
			$_POST['db_server']		= DB_SERVER;
			$_POST['db_database']	= DB_DATABASE;
			$_POST['db_username']	= DB_USERNAME;
			$_POST['db_password']	= DB_PASSWORD;
			$_POST['db_prefix']		= $this->db['prefix'];
		}

		if (!isset($_POST['action']))
		{
			$this->render('install/database');
		}
		else
		{
			switch($_POST['action'])
			{
				case 'database':
					if ($this->save_db() && !$this->is_installed())
					{
						$this->render('install/configuration');
					}
				break;

				case 'install':
					if ($this->install())
					{
						$this->render('install/info');
					}
					else
					{
						$this->render('errors');
					}
				break;
			}
		}
	}

	/**************************************************************************
	 route_uninstall()
	 **************************************************************************/
	public function route_uninstall()
	{
		if (isset($_POST['confirm']))
		{
			if ($_POST['confirm'])
			{
				$this->uninstall($_POST['confirm']);
				redirect_to('./');
			}
			else
			{
				$this->error('You must check "Confirm uninstall" to uninstall Fever.');
				$this->render('errors');
			}
			$this->close();
		}

		$this->render('uninstall');
	}

	/**************************************************************************
	 route_empty()
	 **************************************************************************/
	public function route_empty()
	{
		if (isset($_POST['confirm']))
		{
			if ($_POST['confirm'])
			{
				$this->empty_all($_POST['confirm']);
				redirect_to('./');
			}
			else
			{
				$this->error('You must check "Confirm empty" to empty Fever.');
				$this->render('errors');
			}
			$this->close();
		}

		$this->render('empty');
	}

	/**************************************************************************
	 route_unorphan()
	 **************************************************************************/
	public function route_unorphan()
	{
		if (isset($_POST['confirm']))
		{
			if ($_POST['confirm'])
			{
				$this->unorphan($_POST['confirm']);
				redirect_to('./');
			}
			else
			{
				$this->error('You must check "Confirm" to delete orphaned items and links from Fever.');
				$this->render('errors');
			}
			$this->close();
		}

		$this->render('unorphan');
	}

	/**************************************************************************
	 route_flush()
	 **************************************************************************/
	public function route_flush()
	{
		if (isset($_POST['confirm']))
		{
			if ($_POST['confirm'])
			{
				$this->flush($_POST['confirm']);
				redirect_to('./');
			}
			else
			{
				$this->error('You must check "Confirm flush" to flush Fever.');
				$this->render('errors');
			}
			$this->close();
		}

		$this->render('flush');
	}

	/**************************************************************************
	 route_updates()
	 **************************************************************************/
	public function route_updates()
	{
		$this->check_for_updates(true);
		$this->render('manage/update');
	}

	/**************************************************************************
	 route_revert()

	 Reloads the a fresh copy of current version from the Fever gateway
	 **************************************************************************/
	public function route_revert()
	{
		if (!$this->is_development_copy())
		{
			$this->cfg['updates']['last_checked_version'] = $this->version;
			$this->version--;
			$this->cfg['version']--;
			$this->save();
			$this->route_update();
		}
	}

	/**************************************************************************
	 route_update()
	 **************************************************************************/
	public function route_update()
	{
		$this->cfg['updates']['last_updated_manually'] = 1;
		$this->save();
		$this->update_files();
	}

	/**************************************************************************
	 route_updated()
	 **************************************************************************/
	public function route_updated()
	{
		$last_updated_manually = $this->cfg['updates']['last_updated_manually'];
		$this->cfg['updates']['last_updated_on_time']	= 0;
		$this->cfg['updates']['last_updated_manually']	= 0;
		$this->save();

		$this->vars['last_updated_manually'] = $last_updated_manually;
		$this->render('updated');
	}

	/**************************************************************************
	 route_manifest()
	 **************************************************************************/
	public function route_manifest()
	{
		// should create a manifest that tells MobileSafari which files
		// to cache for later, possibly offline, viewing
		// cache seems to be immediately corrupted
		if ($this->is_mobile)
		{
			$this->render('manifest');
			$this->close();
		}
	}

	/**************************************************************************
	 route_rss()
	 **************************************************************************/
	public function route_rss()
	{
		if ($_GET['rss'] == 'saved')
		{
			$paths 		= $this->install_paths();
			$this->vars = array
			(
				'title' 		=> $paths['trim'].'&#8217;s Saved Items',
				'base_url'		=> $paths['full'],
				'description'	=> '',
				'items'			=> array()
			);
			// TODO: should this feed exempt items from protected feeds?
			$saved_items = $this->get_all('items', '`is_saved`=1 ORDER BY `created_on_time` DESC LIMIT 30');
			foreach ($saved_items as $saved_item)
			{
				$this->vars['items'][] = array
				(
					'title' 		=> $saved_item['title'],
					'description'	=> $saved_item['description'],
					'link'			=> h($saved_item['link']),
					'guid'			=> $saved_item['id'].'@'.$paths['trim'].$paths['dir'],
					'pub_date'		=> gmdate('D, d M Y H:i:s', $saved_item['created_on_time']).' GMT'
				);
			}
			unset($saved_items);
			$this->render('rss');
		}
	}

	/**************************************************************************
	 route_api()
	 **************************************************************************/
	public function route_api()
	{
		$data = array
		(
			'api_version' => 3, // 0.03
			'auth' => 0
		);

		// auth
		if (isset($_POST['api_key']) && low($_POST['api_key']) == md5("{$this->cfg['email']}:{$this->cfg['password']}"))
		{
			$data['auth'] = 1;
		}
		else
		{
			// exit without performing API queries
			$this->render_api($data, $_GET['api']);
		}

		// meta
		$data['last_refreshed_on_time'] = $this->get_col('last_refreshed_on_time', 'feeds', '1 ORDER BY `last_refreshed_on_time` DESC');

		// TODO: add last_item_id

		// update read/saved state
		if (isset($_POST['mark'], $_POST['as'], $_POST['id']))
		{
			$before 		= (isset($_POST['before'])) ? $_POST['before'] : null;
			$method_name 	= "mark_{$_POST['mark']}_as_{$_POST['as']}";
			if (method_exists($this, $method_name))
			{
				$this->{$method_name}($_POST['id'], $before);

				// trigger the appropriate list of item ids
				switch($_POST['as'])
				{
					case 'read':
					case 'unread':
						$_GET['unread_item_ids'] = true;
					break;

					case 'saved':
					case 'unsaved':
						$_GET['saved_item_ids'] = true;
					break;
				}
			}
		}

		if (isset($_POST['unread_recently_read']))
		{
			$this->unread_recently_read();
			$_GET['unread_item_ids'] = true;
		}

		// groups
		if (isset($_GET['groups']))
		{
			$data['groups'] = array();
			$groups = $this->get_all('groups');
			foreach ($groups as $i => $group)
			{
				$data['groups'][] = array
				(
					'id' 	=> intval($group['id']),
					'title'	=> $group['title']
				);
			}
			unset($groups);
		}

		// feed/group relationships
		if (isset($_GET['groups']) || isset($_GET['feeds']))
		{
			// feeds
			$data_feeds = array();
			$spark_feeds = array();
			$feeds = $this->get_all('feeds');
			foreach ($feeds as $feed)
			{
				$data_feeds[] = array
				(
					'id' 					=> intval($feed['id']),
					'favicon_id'			=> $feed['favicon_id'] ? intval($feed['favicon_id']) : 1,
					'title'					=> $this->title($feed),
					'url'					=> $feed['url'],
					'site_url'				=> $feed['site_url'],
					'is_spark'				=> $feed['is_spark'] ? 1 : 0,
					'last_updated_on_time' 	=> intval($feed['last_updated_on_time'])
				);

				if ($feed['is_spark']) $spark_feeds[] = intval($feed['id']);
			}
			unset($feeds);

			// relationships
			$data['feeds_groups'] = array();

			$feeds_by_group 	= array();
			$feeds_to_groups 	= $this->get_all('feeds_groups');
			foreach($feeds_to_groups as $feed_group)
			{
				if (in_array($feed_group['feed_id'], $spark_feeds)) continue; // ignore spark feeds
				$feeds_by_group[$feed_group['group_id']][] 	= intval($feed_group['feed_id']);
			}
			foreach($feeds_by_group as $group_id => $group_feeds)
			{
				$data['feeds_groups'][] = array
				(
					'group_id' 	=> intval($group_id),
					'feed_ids'	=> implode(',', $group_feeds)
				);
			}
			unset($feeds_by_group);
			unset($feeds_to_groups);

			// feeds clean-up
			if (isset($_GET['feeds']))
			{
				$data['feeds'] = $data_feeds;
				unset($data_feeds);
				unset($spark_feeds);
			}
		}

		// favicons
		if (isset($_GET['favicons']))
		{
			$data['favicons'] = array();
			$favicons = $this->get_all('favicons', '1 ORDER BY `id` ASC');
			foreach($favicons as $favicon)
			{
				$data['favicons'][] = array
				(
					'id' 	=> intval($favicon['id']),
					'data'	=> $favicon['cache']
				);
			}
		}

		// items
		if (isset($_GET['items']))
		{
			$data['total_items'] = $this->get_count('items');

			$data['items'] = array();

			// can't just dump all items because some servers will run out of memory
			$item_limit = 50;

			$where = '';

			if (isset($_GET['feed_ids']) || isset($_GET['group_ids'])) // added 0.3
			{
				$feed_ids = array();
				if (isset($_GET['feed_ids']))
				{
					$feed_ids = explode(',', $_GET['feed_ids']);
				}
				if (isset($_GET['group_ids']))
				{
					$group_ids = explode(',', $_GET['group_ids']);

					$query	= '`group_id` IN ('.implode(',', array_fill(0, count($group_ids), '?')).')';
					$args	= array_merge(array($query), $group_ids);
					$feed_ids_where = call_user_func_array(array($this, 'prepare_sql'), $args);
					$group_feed_ids = $this->get_cols('feed_id','feeds_groups', $feed_ids_where);

					$feed_ids = array_unique(array_merge($feed_ids, $group_feed_ids));
				}

				$query	= '`feed_id` IN ('.implode(',', array_fill(0, count($feed_ids), '?')).')';
				$args	= array_merge(array($query), $feed_ids);
				$where .= call_user_func_array(array($this, 'prepare_sql'), $args);
			}

			if (isset($_GET['max_id'])) // descending from most recently added
			{
				// use the max_id argument to request the previous $item_limit items
				$max_id = ($_GET['max_id'] > 0) ? $_GET['max_id'] : 0;

				if ($max_id)
				{
					if (!empty($where)) $where .= ' AND ';
					$where .= $this->prepare_sql('`id` < ?', $max_id);
				}
				else if (empty($where))
				{
					$where .= '1';
				}

				// $where .= $max_id ? $this->prepare_sql('`id` < ?', $max_id) : '1';
				$where .= ' ORDER BY `id` DESC';
			}
			else if (isset($_GET['with_ids'])) // selective
			{
				if (!empty($where)) $where .= ' AND '; // group_ids & feed_ids don't make sense with this query but just in case

				$item_ids = explode(',', $_GET['with_ids']);
				$query	= '`id` IN ('.implode(',', array_fill(0, count($item_ids), '?')).')';
				$args	= array_merge(array($query), $item_ids);

				$where .= call_user_func_array(array($this, 'prepare_sql'), $args);
			}
			else // ascending from first added
			{
				// use the since_id argument to request the next $item_limit items
				$since_id 	= isset($_GET['since_id']) ? $_GET['since_id'] : 0;

				if ($since_id)
				{
					if (!empty($where)) $where .= ' AND ';
					$where .= $this->prepare_sql('`id` > ?', $since_id);
				}
				else if (empty($where))
				{
					$where .= '1';
				}

				// $where .= $since_id ? $this->prepare_sql('`id` > ?', $since_id) : '1';
				$where .= ' ORDER BY `id` ASC';
			}

			$where .= ' LIMIT '.$item_limit;
			$items = $this->get_all('items', $where);

			foreach ($items as $item)
			{
				$data['items'][] = array
				(
					'id'				=> intval($item['id']),
					'feed_id'			=> intval($item['feed_id']),
					'title'				=> $item['title'],
					'author'			=> $item['author'],
					'html'				=> $item['description'],
					'url'				=> $item['link'],
					'is_saved'			=> $item['is_saved'] ? 1 : 0,
					'is_read'			=> $item['read_on_time'] ? 1 : 0,
					'created_on_time'	=> intval($item['created_on_time'])
				);
			}
			unset($items);
		}

		// links
		if (isset($_GET['links']))
		{
			$data['links'] = array();
			// set temporarily
			$this->prefs['ui']['hot_start'] = isset($_GET['offset']) ? intval($_GET['offset']) : 0; // now
			$this->prefs['ui']['hot_range'] = isset($_GET['range'])  ? intval($_GET['range'])  : 7; // week
			$this->page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$this->build_links();

			$data['use_celsius'] = $this->prefs['use_celsius'] ? 1 : 0;

			foreach($this->links_by_degrees as $degree => $links)
			{
				foreach($links as $link)
				{
					$data['links'][] = array
					(
						'id' 				=> intval($link['id']),
						'feed_id' 			=> intval($link['feed_id']),
						'item_id' 			=> intval($link['item_id']),
						'temperature'		=> floatval($degree),
						'is_item' 			=> $link['is_item'] ? 1 : 0,
						'is_local' 			=> $link['is_local'] ? 1 : 0,
						'is_saved'			=> $link['is_saved'] ? 1 : 0,
						'title' 			=> $link['title'],
						'url' 				=> $link['url'],
						'item_ids' 			=> implode(',', $link['item_ids'])
					);
				}
			}
		}

		// unread items
		if (isset($_GET['unread_item_ids']))
		{
			$unread_item_ids = $this->get_cols('id', 'items', '`read_on_time`=0');
			$data['unread_item_ids'] = implode(',', $unread_item_ids);
		}

		// saved items
		if (isset($_GET['saved_item_ids']))
		{
			$saved_item_ids = $this->get_cols('id', 'items', '`is_saved`=1');
			$data['saved_item_ids'] = implode(',', $saved_item_ids);
		}

		$this->render_api($data, $_GET['api']);
	}

	/**************************************************************************
	 render_api()
	 **************************************************************************/
	public function render_api($data = array(), $output = '')
	{
		include(FIREWALL_ROOT.'app/libs/api.php');
		switch($output)
		{
			case 'xml':
				header('Content-type:text/xml; charset=utf-8');
				e(array_to_xml($data, 'response'));
			break;

			case 'json':
			default:
				// header('Content-type:application/json; charset=utf-8');
				header('Content-type:text/json; charset=utf-8');
				e(array_to_json($data));
			break;
		}
		$this->close();
	}

	/**************************************************************************
	 route_auth()
	 **************************************************************************/
	public function route_auth()
	{
		if (isset($_GET['logout']))
		{
			$this->logout();
			redirect_to('./');
		}

		if (!$this->is_logged_in())
		{
			if (isset($_POST['action']))
			{
				switch($_POST['action'])
				{
					case 'login':
						if ($this->authenticate())
						{
							return;
						}
					break;

					case 'remind':
						$this->remind();
					break;
				}
			}
			if (isset($_GET['feedlet']) && isset($_GET['js']))
			{
				header('Content-type:text/javascript');
				$this->render('feedlet/login');
				$this->close();
			}
			$this->render('login');
		}
	}

	/**************************************************************************
	 route_favicons()
	 **************************************************************************/
	public function route_favicons()
	{
		$last_cached_on_time	= $this->get_col('last_cached_on_time', 'favicons', '1 ORDER BY `last_cached_on_time` DESC');

		// Safari doesn't provide the If-Modified-Since header so hopefully
		// it implements it's own smart caching
		if
		(
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) &&
			$last_cached_on_time <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			$this->close();
		}

		header('Content-type: text/css');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_cached_on_time).' GMT');
		// good idea, no effect on Safari
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + (5 * 365 * 24 * 60 * 60)).' GMT');

		if (extension_loaded('zlib')) { ob_start('ob_gzhandler'); }

		$favicons = $this->get_all('favicons', '1 ORDER BY `id` ASC');
		$css = ''; // nl('i.favicon i,');
		foreach($favicons as $favicon)
		{
			if ($favicon['id'] == 1) { continue; }
			$css .= nl("i.f{$favicon['id']} i{background-image:url(data:{$favicon['cache']});}");
		}
		e($css);
		$this->close();
	}

	/**************************************************************************
	 route_image()

	 TODO: add error checking
	 **************************************************************************/
	public function route_image()
	{
		$image_url	= $_GET['img'];
		$response 	= get($image_url);
		if ($response['headers']['response_code'] == 200)
		{
			foreach ($response['headers'] as $header => $value)
			{
				switch(low($header))
				{
					case 'content-type':
						if (!m('#^image/.+$#i', $value, $m))
						{
							$this->close();
						}
					case 'content-length':
						header($header.':'.$value);
					break;
				}
			}
			e($response['body']);
		}
		$this->close();
	}

	/**************************************************************************
	 route_refresh()
	 **************************************************************************/
	public function route_refresh()
	{
		if (!$this->is_logged_in())
		{
			$this->refresh_cron();
		}
		else
		{
			if ($this->is_mobile)
			{
				$this->is_silent = true;
				ignore_user_abort(true);
				echo '<body><script type="text/javascript">parent.Fever.iPhone.releaseRefresh();</script>';
				push();
			}

			if (isset($_GET['group_id']))
			{
				$_GET['force'] = true; // force
				$this->refresh($_GET['group_id']);
			}
			else if (isset($_GET['feed_id']))
			{
				$this->refresh_one($_GET['feed_id'], isset($_GET['favicon']));
			}
			else
			{
				$this->refresh();
			}
		}
	}

	/**************************************************************************
	 route_manage()
	 **************************************************************************/
	public function route_manage()
	{
		if (isset($_POST['action']))
		{
			switch($_POST['action'])
			{
				case 'save-preferences':
					$this->save_preferences();
				break;

				case 'import':
					if (isset($_FILES['opml']) && $_FILES['opml']['error'] == UPLOAD_ERR_OK)
					{
						$opml = file_get_contents($_FILES['opml']['tmp_name']);
						$this->import($opml, isset($_POST['import_groups']));
					}
				break;

				case 'export':
					$group_ids 	= ($_POST['with_groups']) ? $_POST['group_ids'] : array();
					$this->export($group_ids, isset($_POST['flatten']), isset($_POST['include_sparks']));
					$this->close();
				break;

				case 'auth':
					$this->authorize_feed($_POST['feed']['id'], $_POST['feed']['username'], $_POST['feed']['password']);
				break;

				case 'add-group':
					if ($group_id = $this->add_group($_POST['group']))
					{
						$this->prefs['ui']['group_id'] = $group_id;
						$this->save();
					}
				break;

				case 'edit-group':
					$this->edit_group($_POST['group']);
				break;

				case 'delete-group':
					$this->delete_group($_POST['id'], isset($_POST['unsubscribe']));
				break;

				case 'add-feed':
					if (($feed_id = $this->add_feed($_POST['feed'])) && $this->prefs['ui']['show_feeds'])
					{
						$this->prefs['ui']['feed_id'] = $feed_id;
						$this->save();
					}
				break;

				case 'add-to-blacklist':
					$specificity = $_POST['specificity'];
					$link = trim($_POST['link']);

					if ($specificity == 1)
					{
						$link = normalize_url($link);
						$link = r('#^([^/]+).*#', '\1', $link);
						$link = '#^[^:]+://(www\.)?'.$link.'#';
					}

					$this->prefs['blacklist'] = trim($this->prefs['blacklist']."\r".$link);
					$this->save();
					$this->blacklist();
				break;

				// from feedlet
				case 'add-feeds':
					if (isset($_POST['feeds']))
					{
						foreach ($_POST['feeds'] as $feed)
						{
							$this->add_feed($feed);
						}
					}
					redirect_to(prevent_xss($_POST['url']));
				break;

				case 'edit-feed':
					$this->edit_feed($_POST['feed']);
				break;

				case 'delete-feed':
					$this->delete_feed($_POST['id']);
				break;

				case 'edit-sparks':
					$feed_ids = (!isset($_POST['feed_ids'])) ? array() : $_POST['feed_ids'];
					$this->mark_feeds_as_sparks($feed_ids);
				break;

				case 'delete-sparks':
					$this->delete_sparks();
				break;
			}
		}

		if (!empty($_GET['manage']))
		{
			switch($_GET['manage'])
			{
				case 'clear-search':
					$this->prefs['ui']['search'] = '';
					$this->save();
					$this->close();
				break;

				case 'statuses':
					if (isset($_GET['mark'], $_GET['as'], $_GET['id']))
					{
						$before 		= (isset($_GET['before'])) ? (r('/000$/', '', $_GET['before'])) : null;
						$method_name 	= "mark_{$_GET['mark']}_as_{$_GET['as']}";
						if (method_exists($this, $method_name))
						{
							$this->{$method_name}($_GET['id'], $before);
						}
					}
					$this->close();
				break;

				case 'add-feed-to-group':
					if (isset($_GET['feed_id'], $_GET['group_id']))
					{
						$this->add_feed_to_group($_GET['feed_id'], $_GET['group_id']);
					}
					$this->close();
				break;

				case 'remove-feed-from-group':
					if (isset($_GET['feed_id'], $_GET['group_id']))
					{
						$this->remove_feed_from_group($_GET['feed_id'], $_GET['group_id']);
					}
					$this->close();
				break;

				case 'unread-read':
					$this->unread_recently_read();
					$this->close();
				break;

				case 'item':
					$item 			= $this->get_one('items', $this->prepare_sql('`id` = ?', $_GET['id']));
					$feed 			= $this->get_one('feeds', $this->prepare_sql('`id` = ?', $item['feed_id']));
					$item_allows 	= $this->option('item_allows', $this->prefs['ui']['feed_id']);
					$description 	= $this->content($item['description'], isset($_GET['excerpt']), $item_allows, $feed['prevents_hotlinking']);
					e($description);
					$this->close();
				break;

				default:
					$this->render('manage/'.$_GET['manage']);
				break;
			}
		}
		// if nothing has been rendered or
		// we haven't exited yet, redirect to Fever proper
		redirect_to('./');
	}

	/**************************************************************************
	 route_blacklist()
	 **************************************************************************/
	public function route_blacklist()
	{
		if (isset($_POST['blacklist']))
		{
			$blacklist = trim($_POST['blacklist']);

			if ($this->prefs['blacklist'] != $blacklist)
			{
				$this->prefs['blacklist'] = $blacklist;
				$this->save();
				$this->blacklist();
			}
		}

		$this->render('blacklist');
	}

	/**************************************************************************
	 route_subscribe()
	 **************************************************************************/
	public function route_subscribe()
	{
		if (isset($_GET['url']))
		{
			if ($feed_id = $this->add_feed(array('url'=>$_GET['url'])))
			{
				$this->refresh_feed($feed_id);
			}
		}
		redirect_to('./');
	}

	/**************************************************************************
	 route_feedlet()
	 **************************************************************************/
	public function route_feedlet()
	{
		if (isset($_GET['js']))
		{
			header('Content-type:text/javascript');
			$this->render('feedlet/bookmarklet');
			$this->close();
		}
		else if (isset($_GET['url']))
		{
			$this->var['feedlet'] = true;
			$this->relationships();
			$this->render('feedlet');
		}
		else
		{
			// no url provided
			redirect_to('./');
		}
	}

	/**************************************************************************
	 route_extras()
	 **************************************************************************/
	public function route_extras()
	{
		$this->vars['paths'] = $this->install_paths();
		$this->render('extras');
	}

	/**************************************************************************
	 route_extras()
	 **************************************************************************/
	public function route_shortcuts()
	{
		$this->render('keyboard-shortcuts');
	}

	/**************************************************************************
	 route_reader()
	 **************************************************************************/
	public function route_reader()
	{
		// capture query vars
		$page 		= (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
		$page		= ($page < 1) ? 1 : $page; // sensible
		$this->page = $page;

		// p($this->page, 'Page');

		// captured from $_GET['ui'] for default views, saved
		$this->capture_state();

		// manage session
		$now = time();
		if ($this->cfg['last_viewed_on_time'] < $now - ($this->prefs['session_timeout'] * 60))
		{
			// starting new session
			$this->cfg['last_session_on_time'] = $this->cfg['last_viewed_on_time'];
		}
		$this->cfg['last_viewed_on_time'] = $now;

		// save
		$this->save();

		// captured from $_GET['mobile'] for mobile view, NOT SAVED
		$this->capture_state(FEVER_MOBILE);

		// build reader
		if (!$this->is_mobile) // prevent unnecessary processing
		{
			$this->relationships();
			if ($this->prefs['ui']['section'])
			{
				$this->build_items();
			}
			else
			{
				$this->build_links();
			}
		}

		// are we sending a full page or just updates?
		if (isset($_GET['xhr']))
		{
			$this->render('reader/xhr');
		}
		else
		{
			$this->render('reader');
		}
	}

	/**************************************************************************
	 capture_state()
	 **************************************************************************/
	public function capture_state($sender = 'ui')
	{
		if (isset($_GET[$sender]))
		{
			if (isset($_GET[$sender]['section']) && $this->prefs['ui']['section'] != 4 && $sender != FEVER_MOBILE)
			{
				$this->prefs['ui']['previous'] = $this->prefs['ui']['section'];
			}

			foreach($_GET[$sender] as $prop => $value)
			{
				if (isset($this->prefs['ui'][$prop]))
				{
					$this->prefs['ui'][$prop] = $value;
				}
			}
		}

		$mobile_ui = array
		(
			'search',
			'show_feeds',
			'show_read',
			'hot_start',
			'hot_range'
		);
		foreach ($mobile_ui as $prop)
		{
			if (isset($_GET[FEVER_MOBILE][$prop]))
			{
				$this->prefs['ui'][$prop] = $_GET[FEVER_MOBILE][$prop];
			}
		}

		$mobile_custom = array
		(
			'read_on_scroll',
			'read_on_back_out',
			'view_in_app'
		);
		foreach ($mobile_custom as $prop)
		{
			if (isset($_GET[FEVER_MOBILE][$prop]))
			{
				$this->prefs["mobile_{$prop}"] = $_GET[FEVER_MOBILE][$prop];
			}
		}
	}

	/**************************************************************************
	 visit_feed_site()
	 **************************************************************************/
	public function visit_feed_site($feed_id)
	{
		$feed = $this->get_one('feeds', $this->prepare_sql('`id` = ?', $feed_id));
		if (!empty($feed['site_url']))
		{
			header("Location:{$feed['site_url']}");
			$this->close();
		}
	}

	/**************************************************************************
	 save_db()

	 **************************************************************************/
	public function save_db()
	{
		$this->db['server']		= $_POST['db_server'];
		$this->db['database']	= $_POST['db_database'];
		$this->db['username']	= $_POST['db_username'];
		$this->db['password']	= $_POST['db_password'];
		$this->db['prefix']		= $_POST['db_prefix'];

		$confirmed = false;
		$different = false;
		if (isset($_POST['db_option']))
		{
			switch($_POST['db_option'])
			{
				case 1: // use different prefix
					if ($_POST['db_prefix_alt'] != $_POST['db_prefix'])
					{
						$this->db['prefix'] = $_POST['db_prefix_alt'];
						$confirmed = true;
						$different = true;
					}
				break;

				case 2: // use/update existing
					$confirmed = true;
				break;

				case 3: // delete and replace existing
					if (isset($_POST['db_confirm_delete']))
					{
						$this->connect();
						$this->reset(true);
						$confirmed = true;
					}
				break;
			}
		}

		if (!$this->connect())
		{
			$this->reset_errors();
			$this->error('Could not connect to the database with the information provided.');
			$this->render('errors');
		}
		else
		{
			$db_password = r("/'/", "\'", $this->db['password']);
			$db_php  = '<?php';
			$db_php .= <<<PHP

			define('FEVER_DB_SERVER', 	'{$this->db['server']}');
			define('FEVER_DB_DATABASE', '{$this->db['database']}');
			define('FEVER_DB_USERNAME', '{$this->db['username']}');
			define('FEVER_DB_PASSWORD', '{$db_password}');
			define('FEVER_DB_PREFIX', 	'{$this->db['prefix']}');

			PHP;

			if ($this->load() && (!$confirmed || $different))
			{
				$this->render('install/existing');
			}
			else
			{
				save_to_file($db_php, FIREWALL_ROOT.'config/db.php');
				return true;
			}
		}
	}

	/**************************************************************************
	 validate_preferences()

	 **************************************************************************/
	public function validate_preferences()
	{
		$has_error = false;

		if (!isset($_POST['email']) || empty($_POST['email']) || !m(VALID_EMAIL, trim($_POST['email']), $m))
		{
			$this->error('Please enter a valid email address.');
			$has_error = true;
		}

		if (!isset($_POST['password']) || empty($_POST['password']) || m('#^\s+$#', $_POST['password'], $m))
		{
			$this->error('Your password cannot be empty.');
			$has_error = true;
		}

		if ($has_error)
		{
			$this->render('errors');
			$this->close();
		}
		else
		{
			$_POST['email'] 	= trim($_POST['email']);
			$_POST['password'] 	= trim($_POST['password']);
		}
	}

	/**************************************************************************
	 install()

	 **************************************************************************/
	public function install()
	{
		$this->validate_preferences();

		$this->cfg['activation_key']	= ACTIVATION_KEY;
		$this->cfg['email']				= $_POST['email'];
		$this->cfg['password']			= $_POST['password'];
		$this->cfg['version']			= $this->version;
		$this->cfg['installed_on_time']	= time();

		$this->prefs['use_celsius']		= $_POST['use_celsius'] ? true : false;

		$mysqlVersion = $this->dbc->client_version();
		$mysqlVersion = preg_replace('#(^\D*)([0-9.]+).*$#', '\2', $mysqlVersion); // strip extra-version cruft
		$engine_type = ($mysqlVersion > 4) ? 'ENGINE' : 'TYPE';

		foreach($this->manifest as $table => $sql)
		{
			$this->query("CREATE TABLE `{$this->db['prefix']}{$table}` {$sql} {$engine_type}=MyISAM;");
			$this->query("ALTER TABLE `{$this->db['prefix']}{$table}` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
			$this->drop_error('COLLATE');
		}
		$this->query($this->prepare_sql("INSERT INTO `{$this->db['prefix']}_config` VALUES (1, ?, ?);", serialize_safe($this->cfg), serialize_safe($this->prefs)));
		$this->add_default_favicon();
		$this->login();
		return $this->load();
	}

	/**************************************************************************
	 is_db_configured()
	 **************************************************************************/
	public function is_db_configured()
	{
		return (defined('FEVER_DB_DATABASE') && FEVER_DB_DATABASE != '') ? true : false;
	}

	/**************************************************************************
	 is_installed()
	 **************************************************************************/
	public function is_installed()
	{
		return ($this->cfg['installed_on_time']) ? true : false;
	}

	/**************************************************************************
	 reset()
	 **************************************************************************/
	public function reset($confirm = false)
	{
		if ($confirm)
		{
			$this->query("DROP TABLE `{$this->db['prefix']}".implode("`, `{$this->db['prefix']}", array_keys($this->manifest))."`");
			$data = get_class_vars(static::class);
			$this->cfg 		= $data['cfg'];
			$this->prefs 	= $data['prefs'];
		}
	}

	/**************************************************************************
	 is_development_copy()

	 used to prevent deletion/overwriting of development source
	 **************************************************************************/
	public function is_development_copy()
	{
		$paths = $this->install_paths();
		return (bool) m('/^localhost|\.test$/', '', $paths['trim']);
	}

	/**************************************************************************
	 uninstall()
	 **************************************************************************/
	public function uninstall($confirm = false)
	{
		if ($this->is_installed() && $confirm)
		{
			$this->reset($confirm);
			if (!$this->is_development_copy())
			{
				rm(FIREWALL_ROOT);
				rm('index.php');
			}
			$this->logout();
		}
	}

	/**************************************************************************
	 unorphan()
	 **************************************************************************/
	public function unorphan($confirm = false)
	{
		if ($confirm)
		{
			$feed_ids = $this->get_cols('id', 'feeds');
			$where = "WHERE `feed_id` NOT IN (".implode(',', $feed_ids).")";
			$this->query("DELETE FROM `{$this->db['prefix']}items` {$where}");
			$this->query("DELETE FROM `{$this->db['prefix']}links` {$where}");
		}
	}

	/**************************************************************************
	 empty_all()
	 **************************************************************************/
	public function empty_all($confirm = false)
	{
		if ($confirm)
		{
			$truncate = array_keys($this->manifest);
			foreach($truncate as $table)
			{
				if ($table == '_config') continue;
				$this->query("TRUNCATE TABLE `{$this->db['prefix']}{$table}`");
			}
			$this->add_default_favicon();
		}
	}

	/**************************************************************************
	 flush()
	 **************************************************************************/
	public function flush($confirm = false)
	{
		if ($confirm)
		{
			$this->query("TRUNCATE TABLE `{$this->db['prefix']}links`");
			$saved_items = $this->get_all('items', '`is_saved` = 1');
			foreach($saved_items as $saved_item)
			{
				$saved_item['title'] .= ' [SURVIVED FLUSH]';
				$this->save_one('items', $saved_item);
			}
			$this->query("DELETE FROM `{$this->db['prefix']}items` WHERE `is_saved` = 0");
			$this->query("UPDATE `{$this->db['prefix']}feeds` SET `title` = '', `site_url` = '', `domain` = '', `favicon_id` = 0, `last_refreshed_on_time` = 0");
			$this->query("TRUNCATE TABLE `{$this->db['prefix']}favicons`");
			$this->add_default_favicon();
		}
	}

	/**************************************************************************
	 can_update()

	 Whether now is a good time to update or not, prevents updating mid-action.
	 **************************************************************************/
	public function can_update()
	{
		$can_update = (
			empty($_POST) &&
			// consider adding: !isset($_GET['xhr']) &&
			!isset($_GET['refresh']) &&
			!isset($_GET['manage']) &&
			!isset($_GET['uninstall']) &&
			!isset($_GET['feedlet']) &&
			!isset($_GET['favicons']) &&
			!isset($_GET['extras']) &&
			!isset($_GET['visit'])
		);
		return $can_update;
	}

	/**************************************************************************
	 check_for_updates()

	 **************************************************************************/
	public function check_for_updates($force = false)
	{
		// don't update mid-refresh
		if (!$this->can_update())
		{
			return;
		}

		$now = time();
		if ($force || $this->cfg['updates']['last_checked_on_time'] < $now - (24 * 60 * 60))
		{
			$this->cfg['updates']['last_checked_on_time'] = $now;
			$this->save();

			/*#@+
			$response = $this->gateway_request('Version');
			$current_version = (int) $response['body'];
			*/
			// Default to the current version; indicating no updates available.
			$current_version = $this->version;

			if (defined('FEVER_GITHUB_REPOSITORY') && FEVER_GITHUB_REPOSITORY) {
				$url = 'https://api.github.com/repos/' . FEVER_GITHUB_REPOSITORY . '/releases/latest';

				$headers = array(
					'Accept: application/vnd.github+json',
					'X-GitHub-Api-Version: 2022-11-28',
				);

				if (defined('FEVER_GITHUB_API_TOKEN') && FEVER_GITHUB_API_TOKEN) {
					$headers[] = 'Authorization: Bearer ' . FEVER_GITHUB_API_TOKEN;
				}

				$response = get($url, $headers);
				if (
					$response['headers']['response_code'] == 200 &&
					$response['body']
				) {
					$body = json_decode($response['body'], true);

					if (
						isset($body['tag_name']) &&
						is_numeric($body['tag_name'])
					) {
						$current_version = (int) $body['tag_name'];
					}
				}
			}
			/*#@-*/

			if ($this->cfg['updates']['last_checked_version'] < $current_version)
			{
				$this->cfg['updates']['last_checked_version'] = $current_version;
				$this->save();

				if ($this->prefs['auto_update'] && !isset($_GET['updates']))
				{
					$this->update_files();
				}
			}
		}
	}

	/**************************************************************************
	 update_files()
	 **************************************************************************/
	public function update_files()
	{
		if
		(
			$this->can_update() &&
			$this->cfg['version'] < $this->cfg['updates']['last_checked_version'] &&
			!$this->is_development_copy()
		)
		{
			$this->error('Updates are disabled for '.$this->app_name);
			$this->render('errors');
			$this->close();

			/*#@+
			$paths		= $this->install_paths();

			global $REQUEST_TIMEOUT;
			$old_timeout 		= $REQUEST_TIMEOUT;
			$REQUEST_TIMEOUT 	= 20; // longer because of retina images
			$response 			= $this->gateway_request('Update');
			$REQUEST_TIMEOUT 	= $old_timeout;

			if (!empty($response['error']['msg'])) {
				$this->error($response['error']['type'].' Error ('.$response['error']['no'].'): '.$response['error']['msg']);
				$this->render('errors');
				$this->close();
			}
			else if (!isset($response['headers']['X-Apptivator-Verified']))
			{
				$this->error('Invalid response headers from the gateway ('.prevent_xss(array_to_query($response['headers'])).')');
				$this->render('errors');
				$this->close();
			}
			else if (!$response['headers']['X-Apptivator-Verified'])
			{
				$this->error('The Activation Key <strong>'.ACTIVATION_KEY.'</strong> is not valid for '.$this->app_name.' on: '.$paths['trim']);
				$this->render('errors');
				$this->close();
			}

			$zip_name = 'data';
			if (m('#([^"]+).zip"\s*$#', $response['headers']['Content-Disposition'], $m))
			{
				$zip_name = $m[1];
			}
			else
			{
				debug($m, 'Could not determine zip file name');
			}

			$zip_data = $response['body'];
			$zip_path = FIREWALL_ROOT.'tmp/'.$zip_name.'.zip';

			mkdir(FIREWALL_ROOT.'tmp');
			save_to_file($zip_data, $zip_path);

			if (!file_exists($zip_path))
			{
				debug('Could not save zip file');
			}

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

			if ($archive->extract(PCLZIP_OPT_PATH, FIREWALL_ROOT.'tmp'))
			{
				$existing_app_path 	= FIREWALL_ROOT.'app';
				$new_app_path 		= FIREWALL_ROOT.'tmp/'.$root_path.'/app';
				rm(FIREWALL_ROOT.'app');
				rename($new_app_path, $existing_app_path);
			}
			else
			{
				debug('Could not extract archive');
			}
			rm(FIREWALL_ROOT.'tmp');
			redirect_to('./');
			#@-*/
		}
	}

	/**************************************************************************
	 update_available()
	 **************************************************************************/
	public function update_available()
	{
		return ($this->cfg['version'] < $this->cfg['updates']['last_checked_version']);
	}

	/**************************************************************************
	 update_url()
	 **************************************************************************/
	public function update_url()
	{
		if (defined('FEVER_GITHUB_REPOSITORY') && FEVER_GITHUB_REPOSITORY) {
			return 'https://github.com/' . FEVER_GITHUB_REPOSITORY . '/releases/latest';
		}

		return null;
	}

	/**************************************************************************
	 gateway_request()

	 **************************************************************************/
	public function gateway_request($action)
	{
		$paths		= $this->install_paths();
		$response 	= post('http://feedafever.com/gateway/', array
		(
			'app_name'			=> low($this->app_name),
			'activation_key' 	=> ACTIVATION_KEY,
			'domain_name'		=> $paths['trim'],
			'capabilities'		=> $this->capabilities()
		),
		array('X-Apptivator-Action:'.$action));
		return $response;
	}

	/**************************************************************************
	 update()
	 **************************************************************************/
	public function update()
	{
		// files have been updated
		// update database or customize updated files

		$data = get_class_vars(static::class);

		if ($this->cfg['version'] < 32)
		{
			$this->prefs['layout'] = $data['prefs']['layout'];
		}

		if ($this->cfg['version'] < 33)
		{
			$this->cfg['updates'] 			= $data['cfg']['updates'];
			$this->cfg['activation_key']	= ACTIVATION_KEY;
			$this->prefs['auto_update'] 	= $data['prefs']['auto_update'];
		}

		if ($this->cfg['version'] < 38)
		{
			$this->cfg['updates']['last_updated_manually'] = $data['cfg']['updates']['last_updated_manually'];
		}

		if ($this->cfg['version'] < 40)
		{
			$query = "ALTER TABLE `{$this->db['prefix']}feeds` ADD `prevents_hotlinking` tinyint(1) unsigned NOT NULL default '0' AFTER `is_spark`";
			$this->query($query);
		}

		if ($this->cfg['version'] < 41)
		{
			$this->prefs['blacklist'] 	= '';
			$query = "ALTER TABLE `{$this->db['prefix']}links` ADD `is_blacklisted` tinyint(1) unsigned NOT NULL default '0' AFTER `item_id`, ADD INDEX (`is_blacklisted`)";
			$this->query($query);
		}

		if ($this->cfg['version'] < 101)
		{
			$this->prefs['toggle_click'] = $data['prefs']['toggle_click'];
		}

		if ($this->cfg['version'] < 103)
		{
			$this->prefs['anonymize'] = $data['prefs']['anonymize'];
		}

		if ($this->cfg['version'] < 104)
		{
			$this->cfg['last_optimize_on_time']	= $data['cfg']['last_optimize_on_time'];
			$this->cfg['last_repair_on_time']	= $data['cfg']['last_repair_on_time'];

			$this->query("ALTER TABLE `{$this->db['prefix']}links` ADD `is_first` tinyint(1) unsigned NOT NULL default '0' AFTER `is_local`");
			$this->query("ALTER TABLE `{$this->db['prefix']}links` ADD INDEX (`is_first`)");

			// force a refresh to reweight links
			$this->query("UPDATE `{$this->db['prefix']}feeds` SET `last_refreshed_on_time` = 0 WHERE 1");
		}

		if ($this->cfg['version'] < 105)
		{
			// clean-up after repeated unsuccessful attempts to update to 1.04
			$i = 2;
			while ($this->dbc->query("ALTER TABLE `{$this->db['prefix']}links` DROP INDEX `is_first_{$i}`") !== false)
			{
				// delete duplicate indexes until we get an error that the are no duplicate indexes
				$i++;
			}
		}

		if ($this->cfg['version'] < 109)
		{
			$this->prefs['share'] 		= $data['prefs']['share'];
			$this->prefs['services'] 	= $data['prefs']['services'];
		}

		if ($this->cfg['version'] < 112)
		{
			$this->prefs['mobile_read_on_scroll'] 		= $data['prefs']['mobile_read_on_scroll'];
			$this->prefs['mobile_read_on_back_out'] 	= $data['prefs']['mobile_read_on_back_out'];
		}

		if ($this->cfg['version'] < 114)
		{
			// update default favicon (had a redundant `data:` prefix)
			$default_favicon = $this->get_one('favicons', '`id` = 1');
			$default_favicon['cache'] = r('/^data:/', '', $default_favicon['cache']);
			$this->save_one('favicons', $default_favicon);
		}

		if ($this->cfg['version'] < 120)
		{
			$this->prefs['mobile_view_in_app'] 		= $data['prefs']['mobile_view_in_app'];
		}

		if ($this->cfg['version'] < 125)
		{
			// add default values for strict servers
			$this->query("ALTER TABLE  `{$this->db['prefix']}_config` CHANGE  `cfg`  `cfg` MEDIUMTEXT NOT NULL default '', CHANGE  `prefs`  `prefs` MEDIUMTEXT NOT NULL default ''");
			$this->query("ALTER TABLE `{$this->db['prefix']}feeds` CHANGE `site_url` `site_url` varchar(255) default NULL, CHANGE `last_refreshed_on_time` `last_refreshed_on_time` int(10) unsigned NOT NULL default '0', CHANGE `last_updated_on_time` `last_updated_on_time` int(10) unsigned NOT NULL default '0'");
		}

		/*#@+
		if ($this->cfg['version'] < 136) {
			$this->gateway_request('Version'); // first capabilities report
		}
		#@-*/

		// save the update
		$this->cfg['updates']['last_updated_on_time'] = time(); // added v038
		$this->cfg['version'] = $this->version;
		$this->save();
	}

	/**************************************************************************
	 login()
	 **************************************************************************/
	public function login()
	{
		$name 	= "{$this->db['prefix']}auth";
		$value 	= md5("FEVER-{$this->cfg['installed_on_time']}-{$this->cfg['password']}");
		setcookie($name, $value, time() + (365 * 24 * 60 * 60));
		$_COOKIE[$name] = $value;
	}

	/**************************************************************************
	 is_logged_in()
	 **************************************************************************/
	public function is_logged_in()
	{
		$name 	= "{$this->db['prefix']}auth";
		$value 	= md5("FEVER-{$this->cfg['installed_on_time']}-{$this->cfg['password']}");
		return (isset($_COOKIE[$name]) && $_COOKIE[$name] == $value);
	}

	/**************************************************************************
	 logout()
	 **************************************************************************/
	public function logout()
	{
		$name 	= "{$this->db['prefix']}auth";
		setcookie($name, '', (time() - (60 * 60 * 24 * 365)));
		unset($_COOKIE[$name]);
	}

	/**************************************************************************
	 authenticate()
	 **************************************************************************/
	public function authenticate()
	{
		if (trim($_POST['email']) == $this->cfg['email'] && trim($_POST['password']) == $this->cfg['password'])
		{
			$this->login();
			return true;
		}
		else
		{
			$this->error('Email/password combo is incorrect.');
			return false;
		}
	}

	/**************************************************************************
	 remind()
	 **************************************************************************/
	public function remind()
	{
		if ($_POST['email'] == $this->cfg['email'])
		{
			$paths	= $this->install_paths();
			$to		= $this->cfg['email'];
			$subj	= 'Your Fever Password';
			$msg	= 'Password: '.$this->cfg['password'];
			$msg   .= "\n\n\nThank you for using Fever.";
			$msg   .= "\n.............................";
			$msg   .= "\n{$paths['full']}";
			$from 	= "From: Fever <fever@{$paths['trim']}>";

			mail($to, $subj, $msg, $from);
			$this->error('Password sent.');
		}
		else
		{
			$this->error('Incorrect email.');
		}
	}

	/**************************************************************************
	 install_paths()
	 **************************************************************************/
	public function install_paths()
	{
		$paths		= array();
		$self		= (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF']))?$_SERVER['PHP_SELF']:((isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME']))?$_SERVER['SCRIPT_NAME']:$_SERVER['SCRIPT_URL']);
		$domain		= (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
		$protocol 	= ((isset($_SERVER['HTTPS']) && low($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_HTTPS']) && low($_SERVER['HTTP_HTTPS']) == 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https' : 'http';

		$paths['protocol']	= $protocol;
		$paths['dir']		= r('#/+[^/]*$#', '', $self);
		$paths['domain']	= $domain;
		$paths['trim']		= r('/(^www\.|:\d+$)/', '', $paths['domain']);
		$paths['full']		= $protocol.'://'.$paths['domain'].$paths['dir'];

		return $paths;
	}

	/**************************************************************************
	 load()
	 **************************************************************************/
	public function load()
	{
		if ($data = $this->get_one('_config'))
		{
			// TODO: remove for release
			$unserialize_func = strpos($data['cfg'], 'a:') === 0 ? 'unserialize' : 'unserialize_safe';

			$cfg	= $unserialize_func($data['cfg']);
			$prefs	= $unserialize_func($data['prefs']);

			if ($cfg !== false)
			{
				$this->cfg	= $cfg;
			}
			else
			{
				$this->fatal_error('Fever\'s configuration data appears to be damaged beyond repair.');
				$this->render('errors');
			}

			if ($prefs !== false)
			{
				$this->prefs = $prefs;
			}
			else
			{
				$this->fatal_error('Fever\'s preference data appears to be damaged beyond repair.');
				$this->render('errors');
			}
		}

		$is_installed = $this->is_installed();
		if (!$is_installed)
		{
			// Fever hasn't been installed yet so drop the error regarding the missing _config table
			$this->drop_error("_config' doesn't exist");
		}
		$this->is_silent = isset($_GET['silent']);
		return $is_installed;
	}

	/**************************************************************************
	 save()
	 **************************************************************************/
	public function save()
	{
		if ($this->errors['fatal'])
		{
			$this->error('Save aborted to prevent data loss.');
			return;
		}

		$cfg 	= serialize_safe($this->cfg);
		$prefs 	= serialize_safe($this->prefs);
		$this->query($this->prepare_sql("UPDATE `{$this->db['prefix']}_config` SET `cfg` = ?, `prefs` = ? WHERE `id`=1", $cfg, $prefs));
	}

	/**************************************************************************
	 save_preferences()
	 **************************************************************************/
	public function save_preferences()
	{
		$this->validate_preferences();

		// inputs
		$this->cfg['email'] 	= $_POST['email'];
		$this->cfg['password']	= $_POST['password'];

		// radio buttons
		$this->prefs['use_celsius']		= ($_POST['use_celsius']) ? true : false;
		$this->prefs['sort_order']		= ($_POST['sort_order']) ? true : false;
		$this->prefs['auto_refresh']	= ($_POST['auto_refresh']) ? true : false;
		$this->prefs['auto_update']		= ($_POST['auto_update']) ? true : false;
		$this->prefs['layout']			= ($_POST['layout']) ? true : false;

		// checkboxes
		$this->prefs['new_window']		= isset($_POST['new_window']);
		$this->prefs['auto_read']		= isset($_POST['auto_read']);
		$this->prefs['auto_spark']		= isset($_POST['auto_spark']);
		$this->prefs['auto_reload']		= isset($_POST['auto_reload']);
		$this->prefs['unread_counts']	= isset($_POST['unread_counts']);
		$this->prefs['item_excerpts']	= isset($_POST['item_excerpts']);
		$this->prefs['toggle_click']	= isset($_POST['toggle_click']);
		$this->prefs['anonymize']		= isset($_POST['anonymize']);
		$this->prefs['share']			= isset($_POST['share']);

		// select
		$this->prefs['item_allows']		= $_POST['item_allows'];
		$this->prefs['item_expiration'] = $_POST['item_expiration'];

		// sharing services
		$services = array();
		foreach ($_POST['service'] as $service)
		{
			$url = trim($service['url']);
			if (empty($url)) continue;

			$services[] = array
			(
				'name' 	=> trim($service['name']),
				'url'	=> r('#\s+#', '%20', $url),
				'key'	=> low(trim($service['key']))
			);
		}
		$this->prefs['services'] = $services;

		$this->save();
		$this->login();
	}

	/**************************************************************************
	 import()
	 **************************************************************************/
	public function import($opml, $import_groups = true)
	{
		include_once(FIREWALL_ROOT.'app/libs/omdomdom.php');
		$DOM	= OMDOMDOM::parse($opml);
		$nodes	= $DOM->get_nodes_by_name('outline');

		if (empty($nodes))
		{
			debug($opml, 'no outline elements found in');
		}

		$group_id = 0;

		if (!$import_groups) // create a single group for all new feeds
		{
			$titles = $DOM->get_nodes_by_name('title');
			if (isset($titles[0]))
			{
				$title = $titles[0]->inner_text();
				$title = html_entity_decode_utf8($title);
				$title = trim($title);
				$group['title'] = "{$title} (imported)";
				$group_id = $this->add_group($group);
			}
		}

		$is_spark = false;
		foreach ($nodes as $node)
		{
			$has_text 	= $node->has_attr('text');
			$is_feed	= $node->has_attr('xmlUrl');
			if ($has_text && !$is_feed) // group
			{
				$title 			= ($has_text) ? $node->get_attr('text') : $node->get_attr('title');
				$group['title'] = html_entity_decode_utf8($title);
				$is_spark 		= ($group['title'] == 'Sparks');

				if ($import_groups && $group['title'] != 'All' && $group['title'] != 'Sparks')
				{
					$group_id 		= $this->add_group($group);
				}
			}
			else if ($is_feed) // feed
			{
				$parent_node 		= $node->parent();
				$parent_node_name	= $parent_node->get_node_name();

				if ($parent_node_name == 'body' && $import_groups)
				{
					$group_id = 0;
				}

				// values in OPML are/should be double encoded
				$title = ($has_text) ? $node->get_attr('text') : $node->get_attr('title');

				$url = $node->get_attr('xmlUrl');
				$feed = array
				(
					'title' 	=> html_entity_decode_utf8($title),
					'url' 		=> html_entity_decode_utf8($url),
					'group_ids' => array($group_id),
					'is_spark'	=> $is_spark?1:0
				);
				$feed_id = $this->add_feed($feed);
				$this->add_feed_to_group($feed_id, $group_id);
			}
		}

		return $group_id;
	}

	/**************************************************************************
	 export()
	 **************************************************************************/
	public function export($group_ids = array(), $flatten = false, $include_sparks = false)
	{
		$this->relationships();
		$title 		= 'Fever';
		$file_name	= 'fever';

		if (empty($group_ids))
		{
			$group_ids = array_keys($this->groups);
		}

		if (count($group_ids) == 1 && $group_ids[0] != 0)
		{
			$group 		 = $this->groups[$group_ids[0]];
			$title 		.= ' // '.$group['title'];
			$file_name 	.= '-'.text_for_filename($group['title']);
			$flatten	 = true; // no sense in having a group if there's only one
		}

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml .= '<opml version="1.0">'."\n";
		$xml .= '<!-- OPML generated by Fever -->'."\n";
		$xml .= '<head><title>'.$title.'</title></head>'."\n";
		$xml .= '<body>'."\n";

		$output_feed_ids = array();
		foreach ($group_ids as $group_id)
		{
			$group 		= $this->groups[$group_id];
			$group_name	= $group['title'];
			$feed_ids	= $this->feed_ids_by_group_id[$group_id];

			$group_xml = '';
			foreach($feed_ids as $feed_id)
			{
				if ($feed_id == 0 || ($flatten && in($output_feed_ids, $feed_id)))
				{
					continue;
				}

				$feed 		= $this->feeds[$feed_id];
				if ($feed['is_spark'])
				{
					continue;
				}
				$feed_title = sr('"', '&quot;', $this->title($feed));
				$feed_title = (strpos($feed_title, '&')) ? h($feed_title) : $feed_title; // need to encode ampersands in encoded entities, I don't understand it either
				$feed_xml	= "\t".'<outline type="rss" text="'.lt($feed_title).'" title="'.lt($feed_title).'" xmlUrl="'.h($feed['url']).'" htmlUrl="'.h($feed['site_url']).'"/>'."\n";
				$group_xml .= $feed_xml;
				$output_feed_ids[] = $feed_id;
			}

			if ($flatten)
			{
				$xml .= $group_xml;
			}
			else
			{
				$xml .= '<outline text="'.h($group_name).'">'."\n".$group_xml.'</outline>'."\n";
			}
		}

		if ($include_sparks)
		{
			$sparks_xml = '';
			foreach($this->sparks_feed_ids as $feed_id)
			{
				if ($feed_id == 0)
				{
					continue;
				}

				$feed 		= $this->feeds[$feed_id];
				$feed_title = $this->title($feed);
				$feed_title = (strpos($feed_title, '&')) ? h($feed_title) : $feed_title; // need to encode ampersands in encoded entities, I don't understand it either
				$feed_xml	= "\t".'<outline type="rss" text="'.$feed_title.'" title="'.$feed_title.'" xmlUrl="'.h($feed['url']).'" htmlUrl="'.h($feed['site_url']).'"/>'."\n";
				$sparks_xml .= $feed_xml;
			}

			if ($flatten)
			{
				$xml .= $sparks_xml;
			}
			else if (!empty($sparks_xml))
			{
				$xml .= '<outline text="Sparks">'."\n".$sparks_xml.'</outline>'."\n";
			}
		}

		$xml .= '</body>'."\n";
		$xml .= '</opml>';

		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"{$file_name}.opml\"");
		e($xml);
	}

	/**************************************************************************
	 add_group()
	 **************************************************************************/
	public function add_group($group = array())
	{
		$group_id = null;

		if (!isset($group['feed_ids']))
		{
			$group['feed_ids'] = array();
		}

		if (!empty($group['title']))
		{
			$group['title'] = trim($group['title']);
			// don't create another group named Kindling
			if (m('#^kindling$#i', $group['title'], $m))
			{
				$group_id = 0;
			}
			// see if this group already exists
			else if ($existing = $this->get_one('groups', $this->prepare_sql('`title` = ?', $group['title'])))
			{
				$group_id = $existing['id'];
			}
			else
			{
				if ($group_id = $this->save_one('groups', $group))
				{
					foreach($group['feed_ids'] as $feed_id)
					{
						$this->add_feed_to_group($feed_id, $group_id);
					}
				}
			}
		}

		return $group_id;
	}

	/**************************************************************************
	 edit_group()
	 **************************************************************************/
	public function edit_group($group = array())
	{
		if (!isset($group['id']))
		{
			return;
		}

		if (!isset($group['feed_ids']))
		{
			$group['feed_ids'] = array();
		}

		$this->save_one('groups', $group);

		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds_groups` WHERE `group_id` = ?", $group['id']));
		foreach($group['feed_ids'] as $feed_id)
		{
			$query = $this->prepare_sql("INSERT INTO `{$this->db['prefix']}feeds_groups` (`feed_id`, `group_id`) VALUES (?, ?)", $feed_id, $group['id']);
			$this->query($query);
		}
	}

	/**************************************************************************
	 delete_group()
	 **************************************************************************/
	public function delete_group($group_id, $unsubscribe = false)
	{
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}groups` WHERE `id` = ?", $group_id));

		if ($unsubscribe)
		{
			$feed_ids = $this->get_cols('feed_id', 'feeds_groups', $this->prepare_sql('`group_id` = ?', $group_id));

			$feeds 	= $this->get_all('feeds', '1 ORDER BY `title` ASC, `url` ASC');
			$feeds 	= key_remap('id', $feeds);
			foreach($feed_ids as $feed_id)
			{
				$feed = $feeds[$feed_id];
				if ($feed['is_spark'])
				{
					$this->remove_feed_from_group($feed_id, $group_id);
				}
				else
				{
					$this->delete_feed($feed_id);
				}
			}
		}

		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds_groups` WHERE `group_id` = ?", $group_id));

		if ($this->prefs['ui']['group_id'] == $group_id)
		{
			$this->prefs['ui']['group_id'] = 0;
			$this->save();
		}
	}

	/**************************************************************************
	 delete_sparks()
	 **************************************************************************/
	public function delete_sparks()
	{
		$feed_ids = $this->get_cols('id', 'feeds', '`is_spark`=1');
		foreach($feed_ids as $feed_id)
		{
			$this->delete_feed($feed_id);
		}
	}

	/**************************************************************************
	 add_feed()
	 **************************************************************************/
	public function add_feed($feed = array())
	{
		$feed_id = null;
		if (!isset($feed['url']) || empty($feed['url']))
		{
			debug($feed, 'no url provided');
			return $feed_id;
		}

		if (!isset($feed['group_ids']))
		{
			$feed['group_ids'] = array();
		}

		// handle auth
		if (!empty($feed['username']) || !empty($feed['password']))
		{
			$feed['requires_auth'] = 1;
			$feed['auth'] = base64_encode("{$feed['username']}:{$feed['password']}");
		}
		unset($feed['username']);
		unset($feed['password']);

		$feed['url'] 			= r('#^feed://#', 'http://', trim($feed['url']));
		$feed['url_checksum']	= checksum(normalize_url($feed['url']));

		if (!isset($feed['is_spark']))
		{
			$feed['is_spark'] = $this->prefs['auto_spark'] ? 1 : 0;
		}

		// see if this feed already exists
		if ($existing = $this->get_one('feeds', '`url_checksum` = '.$feed['url_checksum']))
		{
			debug($existing['url'], 'Feed already exists');
			$feed_id = $existing['id'];
			// TODO: update existing to allow for multiple groups on import and sparking
		}
		else
		{
			if ($feed_id = $this->save_one('feeds', $feed))
			{
				foreach($feed['group_ids'] as $group_id)
				{
					$this->add_feed_to_group($feed_id, $group_id);
				}
			}
		}

		return $feed_id;
	}

	/**************************************************************************
	 delete_feed()
	 **************************************************************************/
	public function delete_feed($feed_id)
	{
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds` WHERE `id` = ?", $feed_id));
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}items` WHERE `feed_id` = ?", $feed_id));
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}links` WHERE `feed_id` = ?", $feed_id));
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds_groups` WHERE `feed_id` = ?", $feed_id));

		if ($this->prefs['ui']['feed_id'] == $feed_id)
		{
			$this->prefs['ui']['feed_id'] = 0;
			$this->save();
		}
	}

	/**************************************************************************
	 add_feed_to_group()
	 **************************************************************************/
	public function add_feed_to_group($feed_id, $group_id)
	{
		// don't allow 0:Kindling supergroup or duplicate feed/group relationships
		if ($group_id && !$this->get_one('feeds_groups', $this->prepare_sql('`feed_id` = ? AND `group_id` = ?', $feed_id, $group_id)))
		{
			$insert = $this->prepare_sql("INSERT INTO `{$this->db['prefix']}feeds_groups` (`feed_id`, `group_id`) VALUES (?, ?)", $feed_id, $group_id);
			$this->query($insert);
		}
	}

	/**************************************************************************
	 remove_feed_from_group()
	 **************************************************************************/
	public function remove_feed_from_group($feed_id, $group_id)
	{
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds_groups` WHERE `feed_id` = ? AND `group_id` = ?", $feed_id, $group_id));
	}

	/**************************************************************************
	 edit_feed()
	 **************************************************************************/
	public function edit_feed($feed = array())
	{
		if (!isset($feed['id']))
		{
			return;
		}

		if (!isset($feed['group_ids']))
		{
			$feed['group_ids'] = array();
		}

		// handle auth
		if (!empty($feed['username']) || !empty($feed['password']))
		{
			$feed['requires_auth'] = 1;
			$feed['auth'] = base64_encode("{$feed['username']}:{$feed['password']}");
		}
		unset($feed['username']);
		unset($feed['password']);

		$feed['url'] 			= r('#^feed://#', 'http://', trim($feed['url']));
		$feed['url_checksum']	= checksum(normalize_url($feed['url']));

		if (!isset($feed['is_spark']))
		{
			$feed['is_spark'] = 0;
		}
		if (!isset($feed['prevents_hotlinking']))
		{
			$feed['prevents_hotlinking'] = 0;
		}

		$this->save_one('feeds', $feed);

		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}feeds_groups` WHERE `feed_id` = ?", $feed['id']));
		foreach($feed['group_ids'] as $group_id)
		{
			$this->add_feed_to_group($feed['id'], $group_id);
		}
	}

	/**************************************************************************
	 authorize_feed()
	 **************************************************************************/
	public function authorize_feed($feed_id, $username, $password)
	{
		$auth = base64_encode("{$username}:{$password}");
		$query = $this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `auth` = ?, `last_refreshed_on_time` = 0 WHERE `id` = ?", $auth, $feed_id);
		$this->query($query);
		$this->refresh_one($feed_id);
	}

	/**************************************************************************
	 add_favicon()
	 **************************************************************************/
	public function add_favicon($favicon = array())
	{
		$favicon_id = null;
		if (!isset($favicon['cache'], $favicon['url']) || empty($favicon['cache']) || empty($favicon['url']))
		{
			return $favicon_id;
		}

		$url = trim(normalize_url($favicon['url']));
		$url_checksum = checksum($url);

		// see if this feed already exists
		if ($existing = $this->get_one('favicons', "`url_checksum` = {$url_checksum}"))
		{
			$favicon_id = $existing['id'];
		}
		else
		{
			$insert = $this->prepare_sql("INSERT INTO `{$this->db['prefix']}favicons` (`url`, `url_checksum`, `cache`, `last_cached_on_time`) VALUES (?, ?, ?, ?);", $url, $url_checksum, $favicon['cache'], time());
			$favicon_id = $this->insert($insert);
		}

		return $favicon_id;
	}

	/**************************************************************************
	 add_default_favicon()
	 **************************************************************************/
	public function add_default_favicon()
	{
		$default_favicon = 'image/gif;base64,R0lGODlhAQABAIAAAObm5gAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
		return $this->add_favicon(array('url' => 'favicon.png', 'cache' => $default_favicon));
	}

	/**************************************************************************
	 infiniterator()

	 Operate on an array of objects/values with the specified method regardless
	 of server max execution time. Remember $objs *must* be self-moderating,
	 if objects are not eliminated each iteration this function will refresh
	 infinitely.
	 **************************************************************************/
	public function infiniterator($objs, $each_method, $complete_method = '', $refresh_args = '')
	{
		global $REQUEST_TIMEOUT;

		$i 		= 0;
		$total	= count($objs);
		if (isset($_GET['total']) && $_GET['total'] > $total)
		{
			$i		= $_GET['total'] - $total;
			$total	= $_GET['total'];
		}
		$refresh_args .= "&total={$total}";

		$refresh_url				= errors_url("./?refresh{$refresh_args}&".time());
		$past_execution_times 		= array();
		$average_execution_time 	= 0;
		$max_execution_time			= (int) ini_get('max_execution_time');
		if ($max_execution_time <= 0)
		{
			$max_execution_time		= 300; // five minutes
		}
		$available_execution_time 	= $max_execution_time - elapsed_execution_time();

		// memory_event('b:'.(isset($_GET['uDOM']) ? 'uDOM' : 'OMDOMDOM'));

		foreach($objs as $obj)
		{
			$i++;

			// determine average execution time
			if (!empty($past_execution_times))
			{
				$tmp_execution_time = 0;
				$tmp_total_executions = 0;
				foreach($past_execution_times as $execution_time)
				{
					$tmp_execution_time += $execution_time;
					$tmp_total_executions++;
				}
				$average_execution_time = $tmp_execution_time / $tmp_total_executions;
			}

			// multiply the average execution time by a number greater than 1 to increase our margin for estimation errors
			// higher number means more refreshes but less margin for execution overflow errors
			if (elapsed_execution_time() + ($average_execution_time * 1.5) < $available_execution_time)
			{
				$REQUEST_TIMEOUT = floor($available_execution_time) - 1; // leave a second to wrap up if we run out of time
				$REQUEST_TIMEOUT = ($REQUEST_TIMEOUT > 30) ? 30 : $REQUEST_TIMEOUT; // let's be sensible
				$start_execution = ms();

				// execute each_method
				if (method_exists($this, $each_method))
				{
					call_user_func_array(array($this, $each_method), array($obj, $i, $total));

					if (!$this->is_silent)
					{
						push();
					}
					// memory_event('a:'.(isset($obj['domain']) ? $obj['domain'] : $each_method));
				}

				$end_execution = ms();
				array_push($past_execution_times, $end_execution - $start_execution);
			}
			else // running out of time, refresh
			{
				// memory_report_to_file();
				if ($this->is_silent)
				{
					header('Location:'.$refresh_url);
				}
				else
				{
					e('<meta http-equiv="refresh" content="0;url='.$refresh_url.'" />');
				}
				$this->close();
			}
		}

		if (method_exists($this, $complete_method))
		{
			$this->{$complete_method}();
			$this->close();
		}
	}

	/**************************************************************************
	 housekeeping()
	 **************************************************************************/
	public function housekeeping()
	{
		// optimize the database every 24 hours
		$do_optimize = ($this->cfg['last_optimize_on_time'] < time() - (60 * 60 * 24));

		// repair crashed tables once an hour
		$do_repair	= ($this->cfg['last_repair_on_time'] < time() - (60 * 60));

		// $do_optimize = $do_repair = true; // TODO: comment out for release

		// Update configs accordingly
		if ($do_optimize)	{ $this->cfg['last_optimize_on_time'] = time(); }
		if ($do_repair)		{ $this->cfg['last_repair_on_time'] = time(); }

		if ($do_optimize || $do_repair)
		{
			$this->save();

			$tables = array('items', 'links', 'feeds');
			foreach($tables as $table)
			{
				if ($do_optimize)
				{
					$this->query("OPTIMIZE TABLE `{$this->db['prefix']}{$table}`");
				}

				if ($do_repair)
				{
					// Reduced optimized statements should prevent crashes but just to be sure
					if ($rows = $this->query_all("CHECK TABLE {$this->db['prefix']}{$table} FAST")) {
						// debug(ptab($rows, 'CHECK', false));
						$status = end($rows);
						if ($status['Msg_type']=='error') { $this->query("REPAIR TABLE {$this->db['prefix']}{$table}"); }
					}
				}
			}
		}
	}

	/**************************************************************************
	 refresh()
	 **************************************************************************/
	public function refresh($group_id = 0)
	{
		// optimize and repair tables if necessary
		$this->housekeeping();

		$where	= 1;
		$args	= '';

		if ($group_id)
		{
			$args = "&group_id={$group_id}";
			$feed_ids = $this->get_cols('feed_id', 'feeds_groups', $this->prepare_sql('`group_id` = ?', $group_id));
			if (!empty($feed_ids))
			{
				$where = '`id` IN ('.implode(',', $feed_ids).') ';
			}
			else
			{
				// no feeds in this group
				return;
			}
		}

		// prevent forcing a refresh when in an infiniterator loop
		if ((isset($_GET['force']) || $group_id) && !isset($_GET['total']))
		{
			$this->query("UPDATE `{$this->db['prefix']}feeds` SET `last_refreshed_on_time` = 0 WHERE {$where}");
		}

		$stale = time() - $this->prefs['refresh_interval'] * 60;
		$feeds = $this->get_all('feeds', "{$where} AND `last_refreshed_on_time` < {$stale} ORDER BY `is_spark` ASC, `last_refreshed_on_time` ASC");

		$this->infiniterator($feeds, 'refresh_each', 'refresh_complete', $args);
	}

	/**************************************************************************
	 refresh_cron()
	 **************************************************************************/
	public function refresh_cron()
	{
		// this function is publicly exposed so prevent abuse
		unset($_GET['force']);
		unset($_GET['errors']);
		$this->is_silent = true;
		$this->refresh();
	}

	/**************************************************************************
	 refresh_one()
	 **************************************************************************/
	public function refresh_one($feed_id, $favicon = false)
	{
		$feed	= $this->get_one('feeds', $this->prepare_sql('`id` = ?', $feed_id));
		$action	= ($favicon) ? 'cache' : 'refresh';
		$this->{$action.'_each'}($feed);
		$this->{$action.'_complete'}();
		$this->close();
	}

	/**************************************************************************
	 refresh_each()
	 **************************************************************************/
	public function refresh_each($feed, $i = 1, $total = 1)
	{
		if (!$this->is_silent)
		{
			// TODO: this is html/script related, get it out of here
			$class = 'f'.$feed['favicon_id'];
			if ($feed['favicon_id'] <= 1)
			{
				$class = 'icon';
			}

			$title	= quote($this->title($feed));
			$title_html = '<i class="favicon '.$class.'"><i></i></i>'.$title;
			$html 	= <<<HTML
			<script type="text/javascript" language="javascript">parent.Fever.Reader.updateRefreshProgress('{$title_html}', {$i}, {$total});</script>
			HTML;
			e($html);
			// call push before refreshing the feed
			// so we have something to look at
			push();
		}

		if ($this->refresh_feed($feed))
		{
			if (!$this->is_silent)
			{
				$html 	= <<<HTML
				<script type="text/javascript" language="javascript">parent.Fever.Reader.feedRequiresAuth({$feed['id']});</script>
				HTML;
			// $html  .= "Refreshing {$i}/{$total} {$title}";
			e($html);
			push();
			}
		}
	}

	/**************************************************************************
	 refresh_complete()
	 **************************************************************************/
	public function refresh_complete()
	{
		if (!$this->is_silent)
		{
			// e('Done refreshing.<br />');
		}
		unset($_GET['total']);

		// force a refresh of indexes, shouldn't this happen automatically? MyWTF MySQL?
		// moved to housekeeping()
		// $this->query("OPTIMIZE TABLE  `{$this->db['prefix']}items`");
		// $this->query("OPTIMIZE TABLE  `{$this->db['prefix']}links`");

		// memory_report_to_file();
		$this->cache();
	}

	/**************************************************************************
	 refresh_feed()
	 **************************************************************************/
	public function refresh_feed($feed_or_id)
	{
		$force_complete_refresh = err();

		if (is_array($feed_or_id))
		{
			$feed = $feed_or_id;
		}
		else
		{
			$feed = $this->get_one('feeds', $this->prepare_sql('`id` = ?', $feed_or_id));
		}

		// a little housekeeping
		$now = time();
		$expires_on_time = $now - ($this->prefs['item_expiration'] * 7 * 24 * 60 * 60); // item_expiration is integer weeks
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}items` WHERE `feed_id` = ? AND `created_on_time` < {$expires_on_time} AND `is_saved` = 0", $feed['id']));
		$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}links` WHERE `feed_id` = ? AND `created_on_time` < {$expires_on_time}", $feed['id']));
		$reweight = ($this->dbc->affected_rows() > 0);

		if ($this->get_count('links', $this->prepare_sql("`feed_id` = ? AND `is_first` = 1", $feed['id'])) == 0)
		{
			$reweight = true;
		}
		if ($force_complete_refresh) $reweight = true;

		// need to update the refresh time whether our request is successful or not
		// unless another refresh thread beat us to the punch
		// eg. user refreshes in the browser during a scheduled cron refresh
		$this->query($this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `last_refreshed_on_time` = ? WHERE `id` = ? AND `last_refreshed_on_time` <= ?", $now, $feed['id'], $feed['last_refreshed_on_time']));
		$feed['last_refreshed_on_time'] = $now;
		if ($this->dbc->affected_rows()<1) { return; }

		// protected feeds
		$headers = array();
		if (!empty($feed['auth']))
		{
			$headers[] = "Authorization: Basic {$feed['auth']}";
		}

		if (m('#^feed://#', $feed['url'], $m))
		{
			$feed['url'] = r('#^feed://#', 'http://', trim($feed['url']));
			$this->save_one('feeds', $feed);
		}

		// get the feed
		memory_event('b:request');
		$request = get($feed['url'], $headers);
		memory_event('a:request');

		// if the feed is protected and we provided invalid credentials
		if ($request['headers']['response_code'] == 401)
		{
			$this->query($this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `requires_auth` = 1, `auth` = '' WHERE `id` = ?", $feed['id']));
			return true;
		}

		if
		(
			$request['headers']['response_code'] == 404 ||  // missing
			empty($request['body']) || 						// empty
			!m('#<(\?xml|rss|feed)#m', $request['body'], $m)		// not xml or rss
		)
		{
			debug('404, empty response or not xml or rss');
			return;
		}

		$xml = trim($request['body']);

		// unset($request); TODO: uncomment

		include_once(FIREWALL_ROOT.'app/libs/omdomdom.php');
		// $xml = encode_embedded_cdata($xml);
		memory_event('b:parse');
		$DOM = OMDOMDOM::parse($xml);
		unset($xml);
		memory_event('a:parse');

		// we don't have a title for this feed yet
		if (empty($feed['title']))
		{
			// if there's no title, it's probably not a feed
			$titles = $DOM->get_nodes_by_name('title');
			if (!isset($titles[0]))
			{
				debug('No titles, deemed not a feed');
				return;
			}

			$title = $titles[0]->inner_text();
			$this->query($this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `title` = ? WHERE `id` = ?", $title, $feed['id']));

			/** /
			if (!empty($feed['title']))
			{
				tmp_log_to_file($request['headers'], 'changing feed_title from `'.$feed['title'].'` to `'.$title.'`');
			}
			/**/

			$feed['title'] = $title;
		}

		// we don't have a site url for this feed yet
		if (empty($feed['site_url']))
		{
			$site_url = '';
			$links = $DOM->get_nodes_by_name('link');
			foreach($links as $link)
			{
				$parent_node = $link->parent();
				$parent_node_name = $parent_node->get_attr('_node_name');

				if ($parent_node_name == 'feed')
				{
					$has_href 		= $link->has_attr('href');
					$has_rel		= $link->has_attr('rel');
					$has_xml_base 	= $parent_node->has_attr('xml:base');

					$href 	= $has_href ? $link->get_attr('href') : '';
					$rel	= $has_rel ? $link->get_attr('rel') : '';

					// debug('LINK has_href:'.($has_href?'Y':'N').' has_rel:'.($has_rel?'Y':'N').' href:'.$link->get_attr('href').' rel:'.$link->get_attr('rel'));

					if ($has_href && (!$has_rel || ($has_rel && $rel != 'self' && $rel != 'hub' && $rel != 'license' && !m('#^http#', $rel, $r))))
					// if ($has_href && $has_rel && $rel != 'self' && $rel != 'license')
					{
						$site_url = $href;
						break;
					}
					else if ($has_xml_base)
					{
						$site_url = $parent_node->get_attr('xml:base');
						break;
					}
				}
				else if ($parent_node_name == 'channel')
				{
					$site_url = $link->inner_text();
					break;
				}
			}

			if (empty($site_url))
			{
				$site_url = $feed['url'];
			}

			if (strpos($site_url, '/') === 0)
			{
				$site_url = resolve($feed['url'], $site_url);
			}

			$domain = r('#(/.*)$#', '', normalize_url($site_url));

			/** /
			if (!empty($feed['domain']))
			{
				tmp_log_to_file($request['headers'], 'changing domain from `'.$feed['domain'].'` to `'.$domain.'`');
			}
			/**/

			$feed['domain']		= $domain;

			/** /
			if (!empty($feed['site_url']))
			{
				tmp_log_to_file($request['headers'], 'changing site_url from `'.$feed['site_url'].'` to `'.$site_url.'`');
			}
			/**/

			$feed['site_url']	= $site_url;
			$this->save_one('feeds', $feed);
		}

		$proto = (count($DOM->get_nodes_by_name('feed'))) ? 'entry' : 'item';
		$items = $DOM->get_nodes_by_name($proto);

		debug('Feed items extracted');

		$feed_updated = false;

		foreach($items as $i => $item_node)
		{
			if ($i >= 100) // arbitrary limit, most don't post more than 100 items in a 15 minute period
			{
				// but. some feeds don't put most recent items at the top...
				break;
			}
			memory_event('b:parse_item('.$i.')');
			$item_id 		= null; // don't want this carrying over from previous loop, duh.
			$item_updated 	= false;
			$new_item 		= $this->parse_item($item_node, $feed);
			memory_event('a:parse_item');

			// debug($new_item, $feed['url']);

			// ignore items that are older than our expiration date
			// allow undated items to pass
			if ($new_item['created_on_time'] > 0 && $expires_on_time > $new_item['created_on_time'])
			{
				continue;
			}

			// does this item already exist? update it
			if ($existing = $this->get_one('items', $this->prepare_sql('`feed_id` = ? AND `uid` = ?', $feed['id'], $new_item['uid'])))
			{
				// only update modified values, ignoring created_on_time and added_on_time fields
				$update = array();
				$set	= array();
				foreach($new_item as $key => $value)
				{
					if ($key == 'created_on_time' || $key == 'added_on_time')
					{
						continue;
					}

					if ($value != $existing[$key])
					{
						$update[]	= $value;
						$set[]		= "`{$key}` = ?";
					}
				}

				if (!empty($update))
				{
					$unprepared_sql = "UPDATE `{$this->db['prefix']}items` SET".implode(',', $set)." WHERE `id` = ?";
					array_unshift($update, $unprepared_sql);
					array_push($update, $existing['id']);
					$query = call_user_func_array(array($this, 'prepare_sql'), $update);
					$this->query($query);

					$feed_updated	= true;
					$item_updated 	= true;
					$new_item['id']	= $existing['id'];
					$item_id 		= $existing['id'];
				}

				if ($force_complete_refresh) {
					$feed_updated	= true;
					$item_updated 	= true;
					$new_item['id']	= $existing['id'];
					$item_id 		= $existing['id'];
				}
			}
			else // create
			{
				// no date provided or it couldn't be successfully parsed
				// must appear here when creating or undated items will
				// always be updated with a new date
				if($new_item['created_on_time'] <= 0)
				{
					$new_item['created_on_time'] = time();
				}

				if ($new_item['added_on_time'] > $feed['last_added_on_time'])
				{
					$feed['last_added_on_time'] = $new_item['added_on_time'];
				}

				// was outside the $existing conditional
				if ($new_item['created_on_time'] > $feed['last_updated_on_time'])
				{
					$feed['last_updated_on_time'] = $new_item['created_on_time'];
				}

				$cols 	= array();
				$vals 	= array();
				$set	= array();
				foreach($new_item as $key => $value)
				{
					$cols[]	= $key;
					$vals[]	= $value;
					$set[]	= '?';
				}
				$unprepared_sql = 'INSERT INTO `'.$this->db['prefix'].'items` (`'.implode('`, `', $cols).'`) VALUES ('.implode(', ', $set).');';
				array_unshift($vals, $unprepared_sql);
				$query = call_user_func_array(array($this, 'prepare_sql'), $vals);
				$item_id = $this->insert($query);

				$feed_updated 	= true;
				$new_item['id']	= $item_id;

			} // endif($existing)

			// our item is new or updated
			if (isset($item_id))
			{
				if ($item_updated) // item has been updated so we're refreshing all of its links
				{
					$this->query($this->prepare_sql("DELETE FROM `{$this->db['prefix']}links` WHERE `item_id` = ?", $item_id));
				}

				$links = $this->parse_links($new_item, $feed);
				// debug($links, 'links');

				foreach($links as $link)
				{
					$cols 	= array();
					$vals 	= array();
					$set	= array();
					foreach($link as $key => $value)
					{
						$cols[]	= $key;
						$vals[]	= $value;
						$set[]	= '?';
					}
					$unprepared_sql = 'INSERT INTO `'.$this->db['prefix'].'links` (`'.implode('`, `', $cols).'`) VALUES ('.implode(', ', $set).');';
					array_unshift($vals, $unprepared_sql);
					$query = call_user_func_array(array($this, 'prepare_sql'), $vals);
					$this->insert($query);
				}

			} // endif(isset($item_id))
		} // endforeach($items as $item_node)

		$this->query($this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `last_updated_on_time` = ?, `last_added_on_time` = ? WHERE `id` = ?", $feed['last_updated_on_time'], $feed['last_added_on_time'], $feed['id']));
		unset($DOM);

		// re-weight feed links
		if ($feed_updated || $reweight)
		{
			memory_event('b:weight_links');
			$this->weight_links($feed);
			memory_event('a:weight_links');
		}

		debug('Feed refreshed, items parsed, links weighted, ready for next feed.');
		debug(memory_report());
	}

	/**************************************************************************
	 cache()
	 **************************************************************************/
	public function cache()
	{
		// let it try, ico_to_png is only required for MobileSafari
		/** /
		if (!has_gd_png())
		{
			debug('PHP was not compiled with GD or lacks PNG support.');
			$this->cache_complete();
			return;
		}
		/**/

		// presence of a domain indicates that this feed has been parsed at least once
		// prevents selling an unresponsive server short
		$feeds = $this->get_all('feeds', '`favicon_id` = 0 AND `domain` IS NOT NULL ORDER BY `last_refreshed_on_time` ASC');
		$this->infiniterator($feeds, 'cache_each', 'cache_complete');
	}

	/**************************************************************************
	 cache_each()
	 **************************************************************************/
	public function cache_each($feed, $i = 1, $total = 1)
	{
		if (!$this->is_silent)
		{
			// TODO: this is html/script related, get it out of here
			$title	= quote($this->title($feed));
			$title_html = '<i class="favicon icon"><i></i></i>'.$title;
			$html 	= <<<HTML
			<script type="text/javascript" language="javascript">parent.Fever.Reader.updateRefreshProgress('{$title_html}', {$i}, {$total}, 1);</script>
			HTML;
			// $html  .= "Caching favicon {$i}/{$total} {$title}<br />";
			e($html);
			// call push before refreshing the feed
			// so we have something to look at
			push();
		}

		$this->cache_favicon($feed);
	}

	/**************************************************************************
	 cache_complete()
	 **************************************************************************/
	public function cache_complete()
	{
		if (!$this->is_silent)
		{
			$this->relationships();

			$html = <<<HTML
			<script type="text/javascript" language="javascript">
			window.setTimeout(function(){ parent.Fever.Reader.updateAfterRefresh({$this->total_feeds}); }, 1 * 1000);
			parent.Fever.Reader.updateFaviconCache({$this->last_cached_on_time}000);
			</script>
			HTML;
			// $html .= 'Done caching';
			e($html);
		}
	}

	/**************************************************************************
	 cache_favicon()
	 **************************************************************************/
	public function cache_favicon($feed)
	{
		// let it try, ico_to_png is only required for MobileSafari
		/** /
		if (!has_gd_png())
		{
			debug('PHP was not compiled with GD or lacks PNG support.');
			$this->cache_complete();
			return;
		}
		/**/

		global $REQUEST_TIMEOUT;

		// two requests, make sure we have time for both
		$REQUEST_TIMEOUT = $REQUEST_TIMEOUT / 2;
		$ms			= ms();
		$url 		= (!empty($feed['site_url'])) ? $feed['site_url'] : $feed['url'];
		debug($url, 'cache_favicon');
		// if the url is just an absolute directory listing
		if (m('#^/#', $url, $m))
		{
			// grab domain from the feed url
			$url = r('#(^[^/]+//[^/]+).*#', '$1', $feed['url']).$url;
		}

		$request	= get($url);
		$html		= $request['body'];
		$url		= $request['headers']['request_url']; // resync after potential redirects

		debug($request['headers'], 'favicon exploratory request headers');

		$favicon_urls = array(r('#(^[^/]+//[^/]+).*#', '$1', $url).'/favicon.ico'); // default
		// check the html, just in case
		if ($links = get_tags($html, 'link'))
		{
			foreach($links as $link_html)
			{
				if ($link = get_attrs($link_html))
				{
					if (isset($link['rel'], $link['href']) && ($link['rel'] == 'icon' || $link['rel'] == 'shortcut icon'))
					{
						$favicon_url = resolve($url, trim($link['href']));
						if ($favicon_url != $favicon_urls[0])
						{
							array_unshift($favicon_urls, $favicon_url); // move to the head of the line
						}
						break;
					}
				}
			}
		}
		debug($favicon_urls, 'potential favicon urls');

		// check both urls until we find a favicon
		foreach($favicon_urls as $favicon_url)
		{
			$favicon_type	= 'text/x-icon';
			$favicon_id		= 1; // default
			$favicon_data	= null;

			// skip known trouble favicons
			if (m('#imjustcreative\.com/favicon\.ico$#', $favicon_url, $escape))
			{
				debug($favicon_url, 'bad favicon');
				continue;
			}

			// add back on any time that wasn't used by first request
			// use ceil because PHP doesn't like subtracting integers and floating point?
			$REQUEST_TIMEOUT += $REQUEST_TIMEOUT - ceil(ms() - $ms);
			$ms = ms();
			$request = get($favicon_url);

			if
			(
				empty($request['error']['msg']) && 					// no request errors
				!empty($request['headers']['response_code']) && 	// we have received a response
				$request['headers']['response_code'] != 404 &&		// not a 404
				!empty($request['body']) &&							// received *anything* from the server
				!m('#not\s+found#i', $request['body'], $m) &&		// not a 404 returning as a 200
				!m('#<html#i', $request['body'], $m)				// not an html document
			)
			{
				if (isset($request['headers']['Content-Type']) && m('#^image#i', $request['headers']['Content-Type'], $m))
				{
					$favicon_type	= $request['headers']['Content-Type'];
				}

				debug('Found usable favicon: '.$request['headers']['request_url']);
				$favicon_data = $request['body'];
				break;
			}
			else
			{
				// err, try again
				debug($request['headers'], $url.' headers');
				debug(excerpt($request['body']), 'unusable response');
			}
		}

		if (isset($favicon_data))
		{
			$embedded_type = r('#[^a-z1-9]#i', '', substr($favicon_data, 0, 10)); // 1-9 to omit `00` embedded type oddity

			if
			(
				($favicon_type == 'text/x-icon' || $favicon_type == 'image/x-icon' || $favicon_type == 'image/vnd.microsoft.icon') && // ico
				empty($embedded_type) // not another image type misreported by the server as an ico
			)
			{
				$png_data = ico_to_png($favicon_data);
				// if image was successfully converted to png
				if (!empty($png_data))
				{
					$favicon_type = 'image/png';
					$favicon_data = $png_data;
				}
				else
				{
					debug('Could not convert favicon to png.');
				}
			}

			$favicon = array
			(
				'url' 	=> $favicon_url,
				'cache'	=> $favicon_type.';base64,'.base64_encode($favicon_data)
			);

			$favicon_id = $this->add_favicon($favicon);
		}

		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `favicon_id` = ? WHERE `id` = ?", $favicon_id, $feed['id']);
		$this->query($update);

		return $favicon_id;
	}

	/**************************************************************************
	 empty_cache()
	 **************************************************************************/
	public function empty_cache($confirm = false)
	{
		if ($confirm)
		{
			$this->query("UPDATE `{$this->db['prefix']}feeds` SET `favicon_id` = 0");
			$this->query("TRUNCATE TABLE `{$this->db['prefix']}favicons`");
			$this->add_default_favicon();

			// force a refresh
			$this->query("UPDATE `{$this->db['prefix']}feeds` SET `last_refreshed_on_time` = 0");
		}
	}

	/**************************************************************************
	 parse_item()
	 **************************************************************************/
	public function parse_item($item_node, $feed)
	{
		$now 		= time();
		$site_url 	= $feed['site_url'];
		$item 		= array
		(
			'feed_id'			=> $feed['id'],
			'title'				=> '',
			'author'			=> '',
			'description'		=> '',
			'link'				=> '',
			'url_checksum'		=> '',
			'created_on_time'	=> 0,
			'added_on_time'		=> $now,
			'uid'				=> ''
		);

		$image_url = null;

		$guid_is_permalink = true;

		$child_nodes = $item_node->children();
		foreach($child_nodes as $node)
		{
			switch (low($node->get_node_name()))
			{
				case 'title':
					$item['title'] = strip_tags_sane(html_entity_decode_utf8($node->inner_text()));

					if (empty($item['title']))
					{
						$item['title'] = '&#8230;';
					}
				break;

				case 'author':
				case 'dc:creator':
				case 'itunes:author':
				case 'media:credit':
					$author = '';
					$node_child_nodes = $node->children();
					if (count($node_child_nodes) > 1)
					{
						foreach($node_child_nodes as $name)
						{
							switch($name->get_node_name())
							{
								case 'name':
									$author = $name->inner_text();
								break;
							}
						}
					}
					else
					{
						$author = $node->inner_text();
					}

					$author = trim(strip_tags_sane(html_entity_decode_utf8($author)));

					// extract name from: "user@domain.com (Author Name)"
					// or user@domain.com (Author Name (username)), eg. Flickr comment feed
					if (m('/^[^@]+@[^\(]+\(([^\(\)]+)/', $author, $m))
					{
						if (!empty($m[1]))
						{
							$author = $m[1];
						}
					}

					// remove affiliation from: Name (Affiliation)
					if (m('/^([^\(]+)\([^\)]+\)$/', $author, $m))
					{
						if (!empty($m[1]))
						{
							$author = trim($m[1]);
						}
					}

					// extract name from: "Author Name user@domain.com" Digital Web
					if (m('/(.+)\s+[^\s]+@[^\s]+$/', $author, $m))
					{
						if (!empty($m[1]))
						{
							$author = trim($m[1]);
						}
					}

					// extract user from "user@domain.com"
					if (m('/^([^@]+)@/', $author, $m))
					{
						if (!empty($m[1]))
						{
							$author = trim($m[1]);
						}
					}

					// TODO: ignore author names that appear in the site url, implied

					$item['author'] = $author;
				break;

				case 'media:thumbnail':
					if (!empty($image_url)) {
						break;
					}

					if ($node->has_attr('url'))
					{
						$image_url = html_entity_decode_utf8(trim($node->get_attr('url')));
					}
				break;

				case 'enclosure':
				case 'link':
				case 'media:content':
					if (!empty($image_url)) {
						break;
					}

					if (!$node->has_attr('type') || !in($node->get_attr('type'), 'image/'))
					{
						break;
					}

					if ($node->has_attr('href'))
					{
						$image_url = html_entity_decode_utf8(trim($node->get_attr('href')));
					}
					elseif ($node->has_attr('url'))
					{
						$image_url = html_entity_decode_utf8(trim($node->get_attr('url')));
					}
				break;

				case 'description': // rss
				case 'content': // atom
				case 'content:encoded': // atom
				case 'summary': // atom

					$content = $node->inner_content();

					$node_name = $node->get_node_name();
					if (!empty($item['description']) && ($node_name == 'summary' || $node_name == 'description'))
					{
						break;
					}

					if (!empty($content))
					{
						$content = r('/<(\/?)html:/', '<$1', $content); // strips html: namespace prefix
						$content = html_entity_decode_utf8_pre($content); // converts &quot; to " so the replacement below can do its thing
						// preg_replace with /e is depreated but anonymous functions were added in 5.5 and Fever supports back to 4.2.3
						// $content = r('#(src|href)\s*=\s*("|\')([^\2]+?)\2#mie', "stripslashes('$1=$2'.resolve('$site_url', trim('$3')).'$2')", $content);
						if (ma('#(src|href)\s*=\s*("|\')([^\2]+?)\2#mi', $content, $m)) {
							foreach ($m[0] as $i=>$v) {
								$content = sr($m[0][$i], stripslashes("{$m[1][$i]}={$m[2][$i]}".resolve($site_url, trim($m[3][$i]))."{$m[2][$i]}"), $content);
							}
						}
						$item['description'] = trim($content);
					}
				break;

				case 'feedburner:origlink':
					$item['link'] = html_entity_decode_utf8($node->inner_text());
				break;

				case 'link':
					if (empty($item['link']))
					{
						if ($node->has_attr('href')) // atom
						{
							if (!$node->has_attr('rel') || $node->get_attr('rel') == 'alternate')
							{
								$item['link'] = html_entity_decode_utf8(trim($node->get_attr('href')));
							}
						}
						else // rss or Feedblunderer
						{
							$item['link'] = html_entity_decode_utf8($node->inner_text());
						}
					}
				break;

				case 'pubdate': // rss
				case 'published': // atom
				case 'updated': // atom
				case 'issued': // atom
				case 'modified': // atom
				case 'created': // atom
				case 'dc:date': // atom
					$item['created_on_time'] = $this->parse_date($node->inner_text());
				break;

				case 'guid': // rss
				case 'id': // atom
					$item['uid'] = $node->inner_text();

					if ($node->has_attr('isPermaLink') && $node->get_attr('isPermaLink') == 'false')
					{
						$guid_is_permalink = false;
					}
				break;
			}
		}


		if (!empty($image_url)) {
			$item['description'] = '<img src="' . $image_url . '" /><hr />' . $item['description'];
		}

		if (empty($item['link']) && $guid_is_permalink && !empty($item['uid']))
		{
			$item['link'] = $item['uid']; // lazy weirdos
		}

		// try to guess the date from the url
		if (empty($item['created_on_time']) && isset($item['link']) && m('#\d{4}/\d{2}/\d{2}#', $item['link'], $m))
		{
			$item['created_on_time'] = strtotime("{$m[0]} GMT");
		}

		// prevent future posting
		if ($item['created_on_time'] > $now)
		{
			$item['created_on_time'] = $now;
		}

		// resolve link url if necessary
		$xml_base = $item_node->get_attr('xml:base');
		if (!empty($xml_base))
		{
			$site_url = resolve($site_url, $xml_base);
		}
		$item['link'] = resolve($site_url, $item['link']);

		// stopgap for extralong redirect links getting truncated
		if (strpos($item['link'], '/rd?') !== false && m('#&(rd=http[^&]+)#i', $item['link'], $m))
		{
			$item['link'] = sr('/rd?', '/rd?'.$m[1].'&', sr($m[1], '', $item['link']));
		}

		if (empty($item['uid']) && isset($item['link']))
		{
			$item['uid'] = checksum($item['link']);
		}

		// ensures that uids from links match on subsequent refreshes
		$item['uid'] = substr($item['uid'], 0, 255);

		$item['url_checksum'] = checksum(normalize_url(true_url($item['link'])));

		return $item;
	}

	/**************************************************************************
	 parse_links()
	 **************************************************************************/
	public function parse_links($item, $feed)
	{
		// debug($feed,'feed');

		$normalized_urls = array();
		$links = array();

		$url = true_url($item['link']);
		$normalized_url = normalize_url($url);

		$link_domain = r('#(/.*)$#', '', $normalized_url);

		// debug(array($feed['domain'], $link_domain));

		$is_local = ($feed['domain'] == $link_domain)?1:0;

		// there's duplicate logic here and below as well as in the route_blacklist
		// method (although the regex is performed by MySQL up there)
		$is_blacklisted = 0;
		$blacklist_regexp = $this->build_blacklist_regexp();
		if (!empty($this->prefs['blacklist']) && !empty($blacklist_regexp))
		{
			$is_blacklisted = m('#'.$blacklist_regexp.'#i', $url, $b)?1:0;
		}

		$link 	= array
		(
			'feed_id'				=> $feed['id'],
			'item_id'				=> $item['id'],
			// broken
			'is_blacklisted'		=> $is_blacklisted,
			'is_item'				=> 1,
			'is_local'				=> $is_local,
			'title' 				=> $item['title'],
			'url'					=> $url,
			'url_checksum'			=> checksum($normalized_url),
			'title_url_checksum'	=> checksum($item['title'].$normalized_url),
			'created_on_time'		=> $item['created_on_time']
		);

		if (!empty($normalized_url))
		{
			array_push($normalized_urls, $normalized_url);
			array_push($links, $link);
		}

		// TODO: move to an external, updatable text file
		// to enable regex matching use # as a delimiter
		// eg. #google\.[^/]+#
		// this eliminates the need to manually escape all periods
		$ignore = array
		(
			'feedburner.com/fb/a/emailFlare',
			'referpals.com/apps/rd.aspx',
			'slashdot.org/submit.pl',
			'netscape.com/signin/',
			'furl.net/members/login',
			'newsvine.com/_tools/user/login',
			'ma.gnolia.com/signin',
			'secure.del.icio.us/login',
			'del.icio.us/post',
			'reddit.com/submit',
			'digg.com/submit',
			'facebook.com/share.php',
			'technorati.com/faves',
			'google.com/bookmarks/mark',
			'stumbleupon.com/submit'
		);
		$ignore_str = implode('|', $ignore);
		$ignore_str = sr('.', '\.', $ignore_str);

		if (ma('#<a[^>]+href\s*=\s*("|\')([^\\1]*)\\1[^>]*>(.*)</a>#siU', $item['description'], $m))
		{
			// debug($m[2],'found'); // expected match
			// $debug = array();

			foreach($m[2] as $i => $url)
			{
				// filters out empty urls, anchors, mailtos, local logins, nowhere links and the above ignored
				if (m('#(^\s*$|^\#|^mailto:|^/login|^https?://\s*$|('.$ignore_str.').+)#i', $url, $m_ignored))
				{
					continue;
				}

				$title 			= trim(strip_tags_sane($m[3][$i]));
				$url 			= resolve($feed['site_url'], true_url($url));
				$normalized_url	= normalize_url($url);
				$link_domain 	= r('#(/.*)$#', '', $normalized_url);
				$is_local 		= ($feed['domain'] == $link_domain)?1:0;
				$is_blacklisted = 0;
				if (!empty($this->prefs['blacklist']) && !empty($blacklist_regexp))
				{
					$is_blacklisted = m('#'.$blacklist_regexp.'#i', $url, $b)?1:0;
				}

				// store all non-empty links
				if (!empty($title))
				{
					// $debug[] = $url;

					$link = array
					(
						'feed_id'				=> $feed['id'],
						'item_id'				=> $item['id'],
						// broken
						'is_blacklisted'		=> $is_blacklisted,
						'is_local'				=> $is_local,
						'title' 				=> $title,
						'url'					=> $url,
						'url_checksum'			=> checksum($normalized_url),
						'title_url_checksum'	=> checksum($title.$normalized_url),
						'created_on_time'		=> $item['created_on_time']
					);

					array_push($normalized_urls, $normalized_url);
					array_push($links, $link);
				}
			}
			// debug(p($debug, 'survived', false));
		}
		return $links;
	}

	/**************************************************************************
	 parse_date()
	 **************************************************************************/
	public function parse_date($date)
	{
		if (!isset($this->DateParser))
		{
			include_once(FIREWALL_ROOT.'app/libs/simplepie/simplepie-parse-date.php');
			$this->DateParser = SimplePie_Parse_Date::get();
		}

		return $this->DateParser->parse($date);
	}

	/**************************************************************************
	 parse_date_from_item()
	 **************************************************************************/
	public function parse_date_from_item($item_node)
	{
		$date = 0;
		$child_nodes = $item_node->children();
		foreach($child_nodes as $node)
		{
			switch (low($node->get_node_name()))
			{
				case 'pubdate': // rss
				case 'published': // atom
				case 'updated': // atom
				case 'issued': // atom
				case 'modified': // atom
				case 'created': // atom
				case 'dc:date': // atom
					$date = $this->parse_date($node->inner_text());
				break;
			}
		}
		return $date;
	}

	/**************************************************************************
	 weight_links()
	 **************************************************************************/
	public function weight_links($feed)
	{
		$link_weights 			= array();
		$link_ids_by_weight		= array();
		$link_ids_by_is_first	= array();
		$links					= $this->get_all('links', $this->prepare_sql('`feed_id` = ? ORDER BY `created_on_time` ASC', $feed['id']));

		// debug(ptab($links, 'links', false));

		foreach($links as $link)
		{
			$checksum 		= $link['url_checksum'];
			$old_weight 	= $link['weight'];
			$old_is_first	= $link['is_first'];
			if (!isset($link_weights[$checksum]))
			{
				// drop the weight for local non-items and useless obfuscated feedburner links
				$is_feedburner 		= m('#^https?://feeds.feedburner.com#i', $link['url'], $m)?1:0;
				$is_local_nonitem 	= ($link['is_local'] && !$link['is_item'])?1:0;
				$link['weight'] 	= ($is_local_nonitem || $is_feedburner) ? 1 : 0;
				$link['is_first'] 	= 1;
			}
			else // repeat link, drop weight
			{
				$link['weight'] = $link_weights[$checksum] + 1;
				$link['is_first'] = 0;
			}

			// only update those links where weight or is_first actually change
			if ($link['weight'] != $old_weight)
			{
				$link_ids_by_weight[$link['weight']][] 		= $link['id'];
			}
			if ($link['is_first'] != $old_is_first)
			{
				$link_ids_by_is_first[$link['is_first']][] 	= $link['id'];
			}

			$link_weights[$checksum] = $link['weight'];
		}

		// Uses IN to limit number of database queries requried to resync link weights after every refresh
		// to minimize impact on database
		foreach($link_ids_by_weight as $weight => $link_ids)
		{
			$this->query("UPDATE `{$this->db['prefix']}links` SET `weight` = {$weight} WHERE `id` IN (".implode(',', $link_ids).")");
		}
		foreach($link_ids_by_is_first as $is_first => $link_ids)
		{
			$this->query("UPDATE `{$this->db['prefix']}links` SET `is_first` = {$is_first} WHERE `id` IN (".implode(',', $link_ids).")");
		}
		// debug(count($links).' to '.(count($link_ids_by_weight)+count($link_ids_by_is_first)), 'reduced queries from');
	}

	/**************************************************************************
	 blacklist()
	**************************************************************************/
	public function blacklist()
	{
		$this->query("UPDATE `{$this->db['prefix']}links` SET `is_blacklisted`=0");

		$total = $this->get_count('links');
		$limit = 3000;

		$iterations = ceil($total / $limit);

		$blacklisted_ids 	= array();
		$blacklist_regexp 	= $this->build_blacklist_regexp();

		$ms = ms();

		memory_event('b:blacklist');
		for($i=0; $i<=$iterations; $i++)
		{
			$start = $i * $limit;
			$links = $this->query_all("SELECT `id`, `url` FROM `{$this->db['prefix']}links` LIMIT {$start}, {$limit}");

			// build an array of ids that should be blacklisted
			if (!empty($this->prefs['blacklist']) && !empty($blacklist_regexp))
			{
				foreach($links as $link)
				{
					if (m('#'.$blacklist_regexp.'#i', $link['url'], $b))
					{
						$blacklisted_ids[] = $link['id'];
					}
				}
			}
			memory_event('after:'.$i);
		}

		// blacklist all urls from this batch with a single query
		if (!empty($blacklisted_ids))
		{
			$this->query("UPDATE `{$this->db['prefix']}links` SET `is_blacklisted` = 1 WHERE `id` IN (".implode(',', $blacklisted_ids).")");
		}
		memory_event('a:blacklist');

		redirect_to('./');
	}

	/**************************************************************************
	 build_blacklist_regexp()
	**************************************************************************/
	public function build_blacklist_regexp()
	{
		static $blacklist_regexp;
		if (!isset($blacklist_regexp))
		{
			$blacklist_arr		= preg_split('#\s+#', $this->prefs['blacklist']);
			$blacklisted_arr 	= array();

			foreach($blacklist_arr as $blacklisted)
			{
				if (empty($blacklisted))
				{
					continue;
				}

				if (strpos($blacklisted, '#') === 0) // regular expression
				{
					$blacklisted_arr[] = sr('#', '', $blacklisted);
				}
				else // treat as exact match
				{
					$blacklisted_arr[] = '^'.preg_quote($blacklisted).'$';
				}
			}
			$blacklist_regexp = !empty($blacklisted_arr) ? '('.implode('|', $blacklisted_arr).')' : '';
		}
		return $blacklist_regexp;
	}

	/**************************************************************************
	 mark_*_as_*()
	 **************************************************************************/
	public function mark_items_as_read($item_ids = array())
	{
		if (!empty($item_ids))
		{
			$where_in = '`id` IN (?'.str_repeat(',?', count($item_ids) - 1).')';
			$unprepared_query = "UPDATE `{$this->db['prefix']}items` SET `read_on_time` = ? WHERE {$where_in} AND `read_on_time` = 0";
			$unprepared_array = array($unprepared_query, time());
			$unprepared_array = array_merge($unprepared_array, $item_ids);
			$update = call_user_func_array(array($this, 'prepare_sql'), $unprepared_array);
			$this->query($update);
		}
	}

	public function mark_items_as_unread($item_ids = array())
	{
		if (!empty($item_ids))
		{
			$where_in = '`id` IN (?'.str_repeat(',?', count($item_ids) - 1).')';
			$unprepared_query = "UPDATE `{$this->db['prefix']}items` SET `read_on_time` = 0 WHERE {$where_in}";
			$unprepared_array = array($unprepared_query);
			$unprepared_array = array_merge($unprepared_array, $item_ids);
			$update = call_user_func_array(array($this, 'prepare_sql'), $unprepared_array);
			$this->query($update);
		}
	}

	public function mark_item_as_read($item_id)
	{
		$this->mark_items_as_read(array($item_id));
	}

	public function mark_item_as_unread($item_id)
	{
		$this->mark_items_as_unread(array($item_id));
	}

	public function mark_item_as_saved($item_id)
	{
		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}items` SET `is_saved` = 1 WHERE `id` = ?", $item_id);
		$this->query($update);
	}

	public function mark_item_as_unsaved($item_id)
	{
		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}items` SET `is_saved` = 0 WHERE `id` = ?", $item_id);
		$this->query($update);
	}

	public function mark_link_as_saved($link_id)
	{
		// create an item in the ghost Hot Links feed from the link_id
	}

	public function mark_link_as_unsaved($link_id)
	{
		// delete item from the ghost Hot Links feed
		// trouble is how do we get the item_id from the link_id?
	}

	public function mark_feed_as_read($feed_id, $before)
	{
		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}items` SET `read_on_time` = ? WHERE `feed_id` = ? AND `read_on_time` = 0 AND `added_on_time` < ?", time(), $feed_id, $before);
		$this->query($update);
	}

	public function mark_group_as_read($group_id, $before)
	{
		$where = '`read_on_time` = 0 AND `added_on_time` < '.$before;

		if ($group_id != 0)
		{
			if ($group_id == -1)
			{
				$feed_ids = $this->get_cols('id', 'feeds', '`is_spark` = 1');
			}
			else
			{
				$feed_ids = $this->get_cols('feed_id', 'feeds_groups', $this->prepare_sql('`group_id` = ?', $group_id));
			}

			if (!empty($feed_ids))
			{
				$where .= ' AND `feed_id` IN ('.implode(',', $feed_ids).') ';
				// $where .= ' AND (`feed_id` = '.implode(' OR `feed_id` = ', $feed_ids).')';
			}
			else
			{
				return;
			}
		}

		$update = "UPDATE `{$this->db['prefix']}items` SET `read_on_time` = ".time()." WHERE ".$where;
		$this->query($update);
	}

	public function mark_feed_as_spark($feed_id)
	{
		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `is_spark` = 1 WHERE `id` = ?", $feed_id);
		$this->query($update);
	}

	public function mark_feed_as_unspark($feed_id)
	{
		$update = $this->prepare_sql("UPDATE `{$this->db['prefix']}feeds` SET `is_spark` = 0 WHERE `id` = ?", $feed_id);
		$this->query($update);
	}

	public function mark_feeds_as_sparks($feed_ids = array())
	{
		// mark all as not spark
		$this->query("UPDATE `{$this->db['prefix']}feeds` SET `is_spark` = 0");

		$where_in = '`id` IN (?'.str_repeat(',?', count($feed_ids) - 1).')';
		$unprepared_query = "UPDATE `{$this->db['prefix']}feeds` SET `is_spark` = 1 WHERE {$where_in}";
		$unprepared_array = array_merge(array($unprepared_query), $feed_ids);
		$update = call_user_func_array(array($this, 'prepare_sql'), $unprepared_array);
		$this->query($update);
	}

	/**************************************************************************
	 unread_recently_read()
	 **************************************************************************/
	public function unread_recently_read()
	{
		$feed_ids 	= array();
		if ($this->prefs['ui']['section'] != 3)
		{
			$feed_ids 	= $this->get_cols('id', 'feeds', '`is_spark` = 1');
		}

		if (!empty($feed_ids))
		{
			// exclude sparks
			$where = '`feed_id` NOT IN ('.implode(',', $feed_ids).') ';
			// $where = '(`feed_id` != '.implode(' OR `feed_id` != ', $feed_ids).')';
		}
		else
		{
			$where = 1;
		}

		$last_unread 	= $this->get_col('read_on_time', 'items', $where.' ORDER BY `read_on_time` DESC');
		$leeway 		= 15; // seconds
		$update 		= $this->prepare_sql("UPDATE `{$this->db['prefix']}items` SET `read_on_time` = 0 WHERE `read_on_time` > ?", $last_unread - $leeway);

		$this->query($update);
	}

	/**************************************************************************
	 title()
	 **************************************************************************/
	public function title($feed)
	{
		return (!empty($feed['title'])) ? $feed['title'] : normalize_url($feed['url']);
	}

	/**************************************************************************
	 content()
	 **************************************************************************/
	public function content($item_description, $item_excerpts, $item_allows, $prevents_hotlinking = false)
	{
		if ($item_excerpts)
		{
			$content = excerpt($item_description);
		}
		else
		{
			switch($item_allows)
			{
				case 0: // text
					$content = strip_html($item_description, FEVER_TEXT_TAGS);
				break;

				case 1: // images
					$content = strip_html($item_description, FEVER_IMG_TAGS);
				break;

				case 2: // everything
					$content = $item_description;
				break;
			}

			if ($prevents_hotlinking)
			{
				// preg_replace with /e is depreated but anonymous functions were added in 5.5 and Fever supports back to 4.2.3
				// $content = r('#(img[^>]+)src\s*=\s*("|\')([^\2]+?)\2#ie', "stripslashes('$1src=$2./?img='.query_encode('$3').'$2')", $content);
				if (ma('#(img[^>]+)src\s*=\s*("|\')([^\2]+?)\2#i', $content, $m)) {
					foreach ($m[0] as $i=>$v) {
						$content = sr($m[0][$i], stripslashes("{$m[1][$i]}src={$m[2][$i]}./?img=".query_encode($m[3][$i])."{$m[2][$i]}"), $content);
					}
				}
			}

			// hide images that don't load (eg. ads that are tied to a specific domain)
			$content = r('#<img#', '<img onerror="this.style.display=\'none\';"', $content);
		}

		$content = $this->highlight($content);

		return $content;
	}

	/**************************************************************************
	 highlight()
	 **************************************************************************/
	public function highlight($str)
	{
		if ($this->prefs['ui']['section'] == 4)
		{
			$q = $this->prefs['ui']['search'];

			// protect HTML tags
			$hashes	= array();
			if (preg_match_all("#(<[^>]+>)#Ums", $str, $m))
			{
				foreach ($m[0] as $find)
				{
					$hash			= '<!--'.md5($find).'-->';
					$hashes[$hash]	= $find;
					$str			= str_replace($find, $hash, $str);
				}
			}

			$str = r('#('.preg_quote($q).')#i', '<span class="q">\1</span>', $str); // highlight
			$str = sr(array_keys($hashes), array_values($hashes), $str); // reinsert HTML
		}
		return $str;
	}

	/**************************************************************************
	 option()
	 **************************************************************************/
	public function option($option_name, $feed_id = 0, $group_id = null)
	{
		$this->relationships();

		if (!isset($group_id))
		{
			$group_id = $this->prefs['ui']['group_id'];

			// prevent extra-Kindling sections from inheriting the active group's option values
			if ($this->prefs['ui']['section'] != 1)
			{
				$group_id = 0;
			}
		}

		$option_value = $this->prefs[$option_name]; // global

		// if ($this->prefs['ui']['section'] == 1) do group // was breaking show unread count when not in Kindling section

		// group
		if ($group_id)
		{
			$group_option_value = $this->groups[$group_id][$option_name];
			if ($group_option_value != -1)
			{
				$option_value = $group_option_value;
			}
		}

		// feed
		if ($feed_id)
		{
			$feed_option_value = $this->feeds[$feed_id][$option_name];
			if ($feed_option_value != -1)
			{
				$option_value = $feed_option_value;
			}
		}

		return $option_value;
	}

	/**************************************************************************
	 favicon_class()
	 **************************************************************************/
	public function favicon_class($feed)
	{
		$class = 'f'.$feed['favicon_id'];
		if ($feed['favicon_id'] <= 1)
		{
			$class = 'icon';
		}

		if ($feed['requires_auth'] && empty($feed['auth']))
		{
			$class .= ' locked';
		}

		return $class;
	}

	/**************************************************************************
	 override_link()
	 **************************************************************************/
	public function override_link() {

		$file = 'override';
		if ($this->is_mobile) $file .= '-mobile';
		$file .= '.css';

		$link = '';
		if (file_exists($file))
		{
			$link = '<link rel="stylesheet" type="text/css" href="'.$file.'" />';
		}

		return $link;
	}

	/**************************************************************************
	 feedlet_link()
	 **************************************************************************/
	public function feedlet_link()
	{
		$paths = $this->install_paths();
		$html = <<<HTML
		<a
			class="btn text"
			href="javascript:(function(){s=document.createElement('script');s.type='text/javascript';s.src='{$paths['full']}/?feedlet&amp;js&amp;'+(new%20Date()).getTime();document.getElementsByTagName('head')[0].appendChild(s);})();"
			onclick="alert('Drag this link onto your browser bookmarks bar.'); return false;"
			>Feedlet<i></i></a>

		HTML;
		return r('#\r+#', '', $html);
	}

	/**************************************************************************
	 subscribe_link()
	 **************************************************************************/
	public function subscribe_link()
	{
		$paths = $this->install_paths();
		return $paths['full'].'/?subscribe&url=';
	}

	/**************************************************************************
	 relationships()
	 **************************************************************************/
	public function relationships($rebuild = false)
	{
		if (empty($this->groups) || $rebuild)
		{
			$groups_by_feed 		= array();
			$feeds_by_group 		= array();
			$unread_feed_ids		= array(0);

			$start = ms();
			$groups = $this->get_all('groups', '1 ORDER BY `title` ASC');
			$supergroup = array
			(
				'id' 	=> 0,
				'title'	=> 'Kindling'
			);
			array_unshift($groups, $supergroup);
			$groups = key_remap('id', $groups);

			// plug in some defaults for groups with 0 feeds
			foreach($groups as $group_id => $group)
			{
				$feeds_by_group[$group_id][] = 0; // superfeed
				$groups[$group_id]['total_feeds']	= 0;
				$groups[$group_id]['total_items']	= 0;
				$groups[$group_id]['unread_count']	= 0;
			}

			$unread_by_feed	= $this->query_all("SELECT `feed_id`, COUNT(*) AS  'unread_count' FROM  `{$this->db['prefix']}items` WHERE `read_on_time` = 0 GROUP BY `feed_id`");
			$unread_by_feed	= key_remap('feed_id', $unread_by_feed);

			$total_items_by_feed = $this->query_all("SELECT `feed_id`, COUNT(*) AS  'total_items' FROM  `{$this->db['prefix']}items` GROUP BY `feed_id`");
			$total_items_by_feed = key_remap('feed_id', $total_items_by_feed);

			$saved_by_feed 	= $this->query_all("SELECT `feed_id`, COUNT(*) AS  'saved_count' FROM  `{$this->db['prefix']}items` WHERE `is_saved` = 1 GROUP BY `feed_id`");
			$saved_by_feed 	= key_remap('feed_id', $saved_by_feed);
			$saved_feed_ids	= array_merge(array(0), array_keys($saved_by_feed));
			$total_saved	= 0;

			$feeds 	= $this->get_all('feeds', '1 ORDER BY `title` ASC, `url` ASC');

			$items = 'items';
			if (!$this->prefs['ui']['show_read'] && $this->prefs['ui']['section'] != 4)
			{
				$items = 'unread';
			}

			if ($this->prefs['ui']['section'] == 2)
			{
				$items = 'saved';
			}

			$superfeed = array
			(
				'id' 					=> 0,
				'title'					=> "All {$items}", // unread, saved, items(search)
				'is_spark'				=> false,
				'prevents_hotlinking'	=> false
			);

			array_unshift($feeds, $superfeed);
			$feeds 		= key_remap('id', $feeds);
			$feed_ids 	= array_keys($feeds);
			$feeds_by_group[0] = $feed_ids;
			foreach ($feed_ids as $feed_id)
			{
				$groups_by_feed[$feed_id][] 		= 0; // supergroup
				$feeds[$feed_id]['unread_count']	= (isset($unread_by_feed[$feed_id])) ? $unread_by_feed[$feed_id]['unread_count'] : 0;
				$feeds[$feed_id]['saved_count'] 	= (isset($saved_by_feed[$feed_id])) ? $saved_by_feed[$feed_id]['saved_count'] : 0;
				$feeds[$feed_id]['total_items'] 	= (isset($total_items_by_feed[$feed_id])) ? $total_items_by_feed[$feed_id]['total_items'] : 0;

				$total_saved += $feeds[$feed_id]['saved_count'];

				if ($feeds[$feed_id]['unread_count'])
				{
					$unread_feed_ids[] = $feed_id;
				}
			}
			$groups_by_feed[0] = array_keys($groups); // the superfeed lives in all groups
			unset($unread_by_feed);
			unset($total_items_by_feed);
			unset($feed_ids);

			$feeds_to_groups = $this->get_all('feeds_groups');
			foreach($feeds_to_groups as $feed_group)
			{
				// while a feed may have been associated with a group previously
				// it *displays* no affiliations while a spark
				if ($feeds[$feed_group['feed_id']]['is_spark'])
				{
					// continue; // this is breaking group association when saving feed edits within sparks
				}
				$groups_by_feed[$feed_group['feed_id']][] 	= $feed_group['group_id'];
				$feeds_by_group[$feed_group['group_id']][] 	= $feed_group['feed_id'];
			}
			unset($feeds_to_groups);

			$sparks_feed_ids = array(0);
			foreach($feeds_by_group as $group_id => $groups_feeds)
			{
				$groups[$group_id]['total_feeds'] = count($groups_feeds) - 1; // minus the superfeed
				foreach($groups_feeds as $feed_id)
				{
					// don't add sparks unread
					if ($feeds[$feed_id]['is_spark'])
					{
						$sparks_feed_ids[] = $feed_id;
						continue;
					}

					$groups[$group_id]['unread_count']	+= $feeds[$feed_id]['unread_count'];
					$groups[$group_id]['total_items']	+= $feeds[$feed_id]['total_items'];
				}
			}

			$this->groups 	= $groups;
			$this->feeds	= $feeds;

			debug(count($this->groups), 'Total groups');
			debug(count($this->feeds), 'Total feeds');

			$this->group_ids_by_feed_id = $groups_by_feed;
			$this->feed_ids_by_group_id = $feeds_by_group;
			$this->sparks_feed_ids		= $sparks_feed_ids;
			$this->saved_feed_ids		= $saved_feed_ids;

			$this->total_feeds	= $groups[0]['total_feeds'];
			$this->total_items	= $groups[0]['total_items'];
			$this->total_unread	= $groups[0]['unread_count'];
			$this->total_saved	= $total_saved;

			// uses array_intersect to alphabetize search and saved feeds based on order in Kindling
			switch($this->prefs['ui']['section'])
			{
				case 4: // search
					if (!empty($this->prefs['ui']['search']))
					{
						$escaped_search = $this->escape_sql($this->prefs['ui']['search']);

						// perform search on feeds
						$feeds_cols		= array('title', 'url', 'site_url');
						$feeds_where	= '(`'.implode("` LIKE '%{$escaped_search}%' OR `", $feeds_cols)."` LIKE '%{$escaped_search}%'".')';
						$feed_ids		= $this->get_cols('id', 'feeds', $feeds_where);

						// perform search on items
						$items_cols		= array('title', 'link', 'description');
						$items_where	= '(`'.implode("` LIKE '%{$escaped_search}%' OR `", $items_cols)."` LIKE '%{$escaped_search}%'".')';
						$item_feed_ids	= $this->get_cols('feed_id', 'items', "{$items_where} GROUP BY `feed_id`");

						$this->feed_ids = array_unique(array_merge(array(0), $feed_ids, $item_feed_ids));
						// $this->feed_ids = array_intersect($this->feed_ids_by_group_id[0], $search_feed_ids);

						$search_count_by_feed_id = $this->query_all("SELECT `feed_id`, COUNT(*) AS  'search_count' FROM  `{$this->db['prefix']}items` WHERE {$items_where} GROUP BY `feed_id`");
						$search_count_by_feed_id = key_remap('feed_id', $search_count_by_feed_id);
						$search_count = 0;

						foreach($this->feed_ids as $feed_id)
						{
							if (isset($search_count_by_feed_id[$feed_id]))
							{
								$this->feeds[$feed_id]['search_count'] = $search_count_by_feed_id[$feed_id]['search_count'];
								$search_count += $search_count_by_feed_id[$feed_id]['search_count'];
							}
							else
							{
								$this->feeds[$feed_id]['search_count'] = 0;
							}
						}

						$this->feeds[0]['search_count'] = $search_count;

					}
					else
					{
						$this->feed_ids = array(0);
					}
				break;

				case 3: // sparks
					$feed_ids = $this->sparks_feed_ids;
					$this->feed_ids = $feed_ids;

					$total_unread_sparks = 0;
					foreach($this->feed_ids as $feed_id)
					{
						$total_unread_sparks += $this->feeds[$feed_id]['unread_count'];
					}
					$this->feeds[0]['unread_count'] = $total_unread_sparks;
				break;

				case 2: // saved
					$this->feed_ids = array_intersect($this->feed_ids_by_group_id[0], $this->saved_feed_ids);
					$this->feeds[0]['saved_count'] = $total_saved;
				break;

				case 1: // groups
					$feed_ids = $this->feed_ids_by_group_id[$this->prefs['ui']['group_id']];
					$feed_ids = array_diff($feed_ids, $this->sparks_feed_ids); // inadvertently removes superfeed with sparks
					array_unshift($feed_ids, 0);
					/** /
					if (!$this->prefs['ui']['show_read'])
					{
						// this is breaking paging...
						$feed_ids = array_intersect($unread_feed_ids, $feed_ids);
					}
					/**/
					$this->feed_ids = $feed_ids;
					$this->feeds[0]['unread_count']	= $this->groups[$this->prefs['ui']['group_id']]['unread_count'];
					$this->feeds[0]['total_items']	= $this->groups[$this->prefs['ui']['group_id']]['total_items'];
				break;
			}

			// alphabetize feeds, array_intersect approach assumed that feed_id => group_id was already alphabetized--which wasn't!
			$feed_ids_by_title = array();

			foreach ($this->feed_ids as $feed_id)
			{
				if ($feed_id == 0)
				{
					continue;
				}
				$title = low($this->title($this->feeds[$feed_id]).$feed_id);
				$feed_ids_by_title[$title] = $feed_id;
			}
			ksort($feed_ids_by_title);
			$this->feed_ids = array_merge(array(0), array_values($feed_ids_by_title));

			$this->last_refreshed_on_time	= $this->get_col('last_refreshed_on_time', 'feeds', '1 ORDER BY `last_refreshed_on_time` DESC');
			$this->last_cached_on_time		= $this->get_col('last_cached_on_time', 'favicons', '1 ORDER BY `last_cached_on_time` DESC');


			// unless we're showing read, if the current group or feed
			// has no unread, pass focus to the supergroup/superfeed
			if (!$this->prefs['ui']['show_read'] && $this->page == 1)
			{
				if ($this->prefs['ui']['section'] == 1)
				{
					$focused_group_id = $this->prefs['ui']['group_id'];
					if (isset($this->groups[$focused_group_id]))
					{
						$focused_group = $this->groups[$focused_group_id];
						if ($focused_group['unread_count'] == 0)
						{
							// can't just fudge this
							$this->prefs['ui']['group_id'] = 0;

							// make sure feeds list includes all unread, non-spark feeds
							$feed_ids = array_diff($this->feed_ids_by_group_id[0], $this->sparks_feed_ids);
							array_unshift($feed_ids, 0);
							$this->feed_ids = $feed_ids;
						}

					}

					$focused_feed_id = $this->prefs['ui']['feed_id'];
					if (isset($this->feeds[$focused_feed_id]))
					{
						$focused_feed = $this->feeds[$focused_feed_id];
						if ($focused_feed['unread_count'] == 0)
						{
							if (!$this->is_mobile) // prevent displaying group/kindling unread when feed with no unread is selected
							{
								$this->prefs['ui']['feed_id'] = 0;
							}
						}
					}
				}
			}
		}
	}

	/**************************************************************************
	 build_links()
	 **************************************************************************/
	public function build_links()
	{
		$day		= 24 * 60 * 60;
		$scale 		= 1.25;
		$now		= time();
		$start_time = $now - $this->prefs['ui']['hot_start'] * $day;
		$end_time 	= $start_time - $this->prefs['ui']['hot_range'] * $day;

		$select 	= "SELECT `url_checksum`, SUM({$scale} / POW(2, `weight`)) as 'weight' FROM `{$this->db['prefix']}links` WHERE ";
		$where 		= '`is_blacklisted` = 0 AND `created_on_time` > '.$end_time;
		$hot_where	= '`is_first` = 1 AND ';
		if ($start_time != $now)
		{
			$where .= ' AND `created_on_time` < '.$start_time;
		}
		$group_by	= ' GROUP BY `url_checksum`';
		$order_by	= ' ORDER BY `weight` DESC';
		$limit		= ' LIMIT '.(($this->page - 1) * $this->prefs['per_page']).', '.$this->prefs['per_page'];

		// get weighted checksums
		$url_checksums_by_weight = $this->query_all($select.$hot_where.$where.$group_by.$order_by.$limit);

		// ptab($url_checksums_by_weight, 'unfiltered');

		// isolate just the hot checksums
		$url_checksums = array();
		$hot_checksums = array(); // tmp array
		foreach($url_checksums_by_weight as $checksum)
		{
			if ($checksum['weight'] > $scale)
			{
				array_push($url_checksums, $checksum['url_checksum']);
				array_push($hot_checksums, $checksum);
			}
		}
		// filter our original array because we'll be looping through it later
		$url_checksums_by_weight = $hot_checksums;
		unset($hot_checksums);

		// ptab($url_checksums_by_weight, 'filtered');

		// cold
		if (!count($url_checksums_by_weight))
		{
			return;
		}

		// now get all related links based on hot checksums
		$links = $this->get_all('links', '`url_checksum` IN ('.implode(',', $url_checksums).") AND {$where} ORDER BY `created_on_time` DESC, `is_item` DESC");
		$orig_links = $this->get_all('links', '`url_checksum` IN ('.implode(',', $url_checksums).") AND `is_local`=1 AND `is_item`=1 ORDER BY `created_on_time` DESC, `is_item` DESC");
		$links = array_merge($links, $orig_links);
		unset($orig_links);
		$links = key_remap('id', $links);
		unset($url_checksums);

		// reindex links by checksum
		$link_ids_by_url_checksum = array();
		// get related item ids to retrieve items
		$item_ids = array();
		foreach($links as $link)
		{
			array_push($item_ids, $link['item_id']);
			if (!isset($link_ids_by_url_checksum[$link['url_checksum']]))
			{
				$link_ids_by_url_checksum[$link['url_checksum']] = array();
			}
			array_push($link_ids_by_url_checksum[$link['url_checksum']], $link['id']);
		}
		$item_ids = array_unique($item_ids); // may have duplicates

		// now get all related items
		$items = $this->get_all('items', '`id` IN ('.implode(',', $item_ids).') '); // specific id query, no other qualifiers needed
		$items = key_remap('id', $items);
		unset($item_ids);

		$links_by_degrees = array();
		foreach($url_checksums_by_weight as $checksum)
		{
			// for our best guess at a title
			$title 		 	= '';
			$description 	= '';
			$alt_desc		= '';
			$item_titles 	= array(); // item title
			$link_titles 	= array(); // a/link title
			$source_ids		= array(); // ids of the items that contain this url

			// prepare degrees
			$degrees = (98.6 - $scale) + $checksum['weight'];
			if ($this->prefs['use_celsius'])
			{
				$degrees = ($degrees - 32) * 5 / 9;
			}
			$degrees = number_format($degrees, 1);

			if (!isset($links_by_degrees[$degrees]))
			{
				$links_by_degrees[$degrees] = array();
			}

			// ids of all links associated with this checksum
			$link_ids = $link_ids_by_url_checksum[$checksum['url_checksum']];
			$hot_link = null;
			foreach($link_ids as $link_id)
			{
				// a source
				$link = $links[$link_id];

				// try to find the original source of this hotness
				if
				(
					// no hot link yet, automatic pass
					!isset($hot_link) ||

					// current hot link is not an item but this link is
					(!$hot_link['is_item'] && $link['is_item']) ||

					// previous tests being equal, the current hot link isn't local but this link is
					($hot_link['is_item'] == $link['is_item'] && !$hot_link['is_local'] && $link['is_local']) ||

					// previous tests being equal, the current hot link is newer than this item link (older the better)
					(
						$hot_link['is_item'] == $link['is_item'] &&
						$hot_link['is_local'] == $link['is_local'] &&
						$hot_link['created_on_time'] > $link['created_on_time']
					)
				)
				{
					$hot_link = $link;
					// debug($hot_link, $link['title']);
				}

				if (isset($items[$link['item_id']]))
				{
					$item 			= $items[$link['item_id']];
					$source_ids[]	= $item['id'];
					$item_titles[] 	= $item['title'];

					if (empty($alt_desc) && $item['link'] == $link['url'])
					{
						$alt_desc = $item['description'];
					}

					// prevent # and other single character links from being considered
					if (strlen(html_entity_decode_utf8($link['title'])) > 1 && $link['title'] != "[link]")
					{
						$link_titles[] = $link['title'];
					}
				}
			}

			// our best guess at a useful label for this hot link
			// if this link is an item we have it easy.
			$is_saved = 0;
			if ($hot_link['is_item'])
			{
				$title 		= $hot_link['title'];
				$item		= $items[$hot_link['item_id']];
				$is_saved	= $item['is_saved'];

				if ($hot_link['is_local'])
				{
					$description = $item['description'];
				}
				// remove isolated achors, see df links
				$description = r('#<div><a title="Perma(nent )?link.+</a></div>$#', '', trim($description));
			}
			else
			{
				// check link text for duplicates
				$by_frequency 	= array_frequency($link_titles);
				$sorted_titles 	= array_keys($by_frequency);
				$frequencies 	= array_values($by_frequency);

				if (isset($frequencies[0]) && $frequencies[0] > 1)
				{
					// bingo
					$title 	= $sorted_titles[0]; // .'*';
				}
				else
				{
					// check item titles for duplicates
					$item_by_frequency 	= array_frequency($item_titles);
					$item_sorted_titles	= array_keys($item_by_frequency);
					$item_frequencies 	= array_values($item_by_frequency);

					if (isset($item_frequencies[0]) && $item_frequencies[0] > 1)
					{
						// rare to be sure but it could happen
						$title 	= $item_sorted_titles[0]; // .'**';
					}
					else
					{
						// look for words from the link text that also appear in the link url
						$by_word_frequency = array();
						foreach($sorted_titles as $a_title)
						{
							// eliminate non-word characters and whitespace for our comparison
							$tokens = preg_split('/(\b[^a-z0-9]* |-)/i', $a_title);
							$clean_tokens = array();
							foreach($tokens as $token)
							{
								if (!empty($token))
								{
									$clean_tokens[] = r('/[^a-z]+/i', '', $token);
								}
							}
							$tokens_match = low('#('.implode('|', $clean_tokens).')#');
							ma($tokens_match, low($hot_link['url']), $m);
							$by_word_frequency[$a_title] = count($m[0]);
						}
						arsort($by_word_frequency);

						$word_titles 		= array_keys($by_word_frequency);
						$word_frequencies 	= array_values($by_word_frequency);
						if (isset($word_frequencies[0]) && $word_frequencies[0] > 0)
						{
							// found a title with a high match for the actual url
							$title = $word_titles[0]; // .'** *';
						}
						else
						{
							// look for words from an item title that also appear in the item url
							$item_by_word_frequency = array();
							foreach($item_sorted_titles as $a_title)
							{
								$tokens = preg_split('/(\b[^a-z0-9]* |-)/i', $a_title);
								$clean_tokens = array();
								foreach($tokens as $token)
								{
									if (!empty($token))
									{
										$clean_tokens[] = r('/[^a-z]+/i', '', $token);
									}
								}
								$tokens_match = low('#('.implode('|', $clean_tokens).')#');
								ma($tokens_match, low($hot_link['url']), $m);
								$item_by_word_frequency[$a_title] = count($m[0]);
							}
							arsort($item_by_word_frequency);

							$item_word_titles 		= array_keys($item_by_word_frequency);
							$item_word_frequencies 	= array_values($item_by_word_frequency);
							if (isset($item_word_frequencies[0]) && $item_word_frequencies[0] > 0)
							{
								// this *should* never happen
								$title = $item_word_titles[0]; // .'** **';
							}
							else
							{
								// just use an item title? not sure this is ever a good idea
								$title = $item_sorted_titles[0]; // .'** ** *';
							}
						}
					}
				}
			}

			if (empty($description) && !empty($alt_desc))
			{
				// $description = $alt_desc;
			}

			$hot_link['title']				= $title;
			$hot_link['description']		= $description;
			$hot_link['item_ids'] 			= array_unique($source_ids);
			$hot_link['is_saved']			= $is_saved;
			$links_by_degrees[$degrees][] 	= $hot_link;
		}

		// sort same temp is_item first
		foreach($links_by_degrees as $degrees => $links)
		{
			$sorted_links 	= array();
			$unsorted_links = array();
			foreach($links as $link)
			{
				foreach($link['item_ids'] as $i => $item_id)
				{
					// filter out original items the aren't in the specified timeframe
					if ($items[$item_id]['created_on_time'] > $start_time || $items[$item_id]['created_on_time'] < $end_time)
					{
						unset($link['item_ids'][$i]);
					}
				}

				if ($link['is_item'] && $link['is_local'])
				{
					$sorted_links[] = $link;
				}
				else
				{
					$unsorted_links[] = $link;
				}
			}
			$links_by_degrees[$degrees] = array_merge($sorted_links, $unsorted_links);
		}

		$this->items = $items;
		$this->links_by_degrees = $links_by_degrees;
	}

	/**************************************************************************
	 build_items()
	 **************************************************************************/
	public function build_items()
	{
		// a feed can only be selected if we're displaying it
		if (!in($this->feed_ids, $this->prefs['ui']['feed_id']))
		{
			$this->prefs['ui']['feed_id'] = 0;
		}

		if
		(
			$this->prefs['ui']['show_feeds'] &&	// feeds are showing and
			$this->prefs['ui']['feed_id']		// a feed is selected
		)
		{
			$where = $this->prepare_sql('`feed_id` = ?', $this->prefs['ui']['feed_id']);
		}
		else if
		(
			$this->prefs['ui']['group_id'] || // a group is selected or
			$this->prefs['ui']['section'] > 1 // saved, sparks or search is selected
		)
		{
			if (!empty($this->feed_ids))
			{
				$where = '`feed_id` IN ('.implode(',', $this->feed_ids).') ';
				// $where = '(`feed_id` = '.implode(' OR `feed_id` = ', $this->feed_ids).')';
			}
			else
			{
				$where = 0;
			}
		}
		else // display kindling
		{
			// $where = '(`feed_id` != '.implode(' AND `feed_id` != ', $this->sparks_feed_ids).')';
			$where = '`feed_id` NOT IN ('.implode(',', $this->sparks_feed_ids).') ';
		}

		if ($this->prefs['ui']['section'] == 4)
		{
			$escaped_search = $this->escape_sql($this->prefs['ui']['search']);
			$items_cols		= array('title', 'link', 'description');

			$where .= 'AND (`'.implode("` LIKE '%{$escaped_search}%' OR `", $items_cols)."` LIKE '%{$escaped_search}%'".')';
		}

		if ($this->prefs['ui']['section'] == 2)
		{
			$where .= ' AND `is_saved` = 1';
		}

		$start = ($this->page - 1) * $this->prefs['per_page'];
		if
		(
			($this->prefs['ui']['section'] == 1 || $this->prefs['ui']['section'] == 3) &&
			!$this->prefs['ui']['show_read']
		)
		{
			$where .= ' AND `read_on_time` = 0';

			// because read_on_time is used in the query but modified between queries
			// $start must always be zero if mark as read on scroll or the related
			// mobile_read_on_scroll is set
			if ($this->is_mobile)
			{
				if ($this->prefs['mobile_read_on_scroll'])
				{
					$start = 0;
				}
			}
			else if ($this->prefs['auto_read'])
			{
				$start = 0;
			}
		}

		// build from feed, group, global preference
		$order = ($this->option('sort_order', $this->prefs['ui']['feed_id'])) ? 'ASC' : 'DESC';

		$where .= ' ORDER BY `created_on_time` '.$order;
		$where .= ' LIMIT '.$start.', '.$this->prefs['per_page'];

		$this->items = $this->get_all('items', $where);
	}
}
