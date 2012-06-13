<?php 
vf_check();

# ----------------------
# Class: Input
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_input
{
	private $_method = array( 
		'_GET', '_POST', '_REQUEST', '_COOKIE', '_SESSION', '_SERVER', '_ENV'
	);
	public $domain;
	public $host;
	public $path;
	public $site_url;
	public $server_name;

	// Cookie
	public $cookie_path;
	public $cookie_domain;
	public $cookie_prefix = 'vF_';
	public $cookie_hash = true;
	
	// Session
	public $session_save_path = '/';
	
	public function __construct()
	{
	
	}

	public 
}