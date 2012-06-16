<?php 
vf_check();

# ----------------------
# Class: System Setting
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_system
{
	private static $_instance;
	private $_memoryLimit = null;
	private $_systemStarted;
	public $host = '';
	public $secure = '';
	public $key = '';
	public $time;
	public $config;
	public $safeMode;
	public $canRunWithThisPHP;
	public $disableFunctions = array();
	public $sessionSupport;
	public $systemOs;
	public $curlSupport;
	public $opendirSupport;
	public $rewriteSupport;
	public $vF;
	
	public function __construct()
	{
		$this->vF = $GLOBALS['vF'];
		if( $this->_install ) return;
		$this->key = $vF_Config->key;
		$this->_install = true;
		return;
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function startSystem()
	{
		if( $this->_systemStarted ) return;

		$this->_loadConfig();
		if( vF_constant::vF_MEMORY_LIMIT > 0 ) $this->setMemoryLimit( vF_constant::vF_MEMORY_LIMIT );
		if (!@ini_get('output_handler')) while ( @ob_end_clean() );

		error_reporting(E_ALL | E_STRICT & ~8192);
		date_default_timezone_set('UTC');

		$this->host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'] );
		$this->secure = ( isset( $_SERVER['HTTPS'] ) and $_SERVER['HTTPS'] == 'on' );
		$this->time = time();
		if( !isset( $_COOKIE ) ) $_COOKIE = array();
		if( !isset( $_SESSION ) ) @session_start();
		session_save_path( vF_DIR . '/' . vF_constant::vF_SESSION_DIR . '/' );

		$this->disableFunctions = ( ( $disable_functions = ini_get( "disable_functions" ) ) != "" and $disable_functions != false ) ? array_map( 'trim', preg_split( "/[\s,]+/", $disable_functions ) ) : array();
		$this->safeMode = ( ini_get( 'safe_mode' ) == '1' || strtolower( ini_get( 'safe_mode' ) ) == 'on' ) ? true : false;
		$this->canRunWithThisPHP = (PHP_VERSION >= 5.2) ? true : false;
		$this->sessionSupport = ( extension_loaded( 'session' ) ) ? true : false;
		$this->systemOs = strtoupper( ( function_exists( "php_uname" ) and ! in_array( 'php_uname', $this->disableFunctions ) and php_uname( 's' ) != '' ) ? php_uname( 's' ) : PHP_OS );
		$this->curlSupport = ( extension_loaded( 'curl' ) and ( empty( $this->disableFunctions ) or ( ! empty( $this->disableFunctions ) and ! preg_grep( '/^curl\_/', $this->disableFunctions ) ) ) ) ? true : false;
		$this->opendirSupport = ( function_exists( 'opendir' ) and ! in_array( 'opendir', $this->disableFunctions ) ) ? true : false;
		$this->rewriteSupport = $this->_checkRewriteSupport();

		$this->_systemStarted = true;
	}

	private function _loadConfig()
	{
		$this->config = $GLOBALS['vF_Config'];
	}

	public function setMemoryLimit( $limit )
	{
		if( $this->_memoryLimit === null )
		{
			$curLimit = @ini_get('memory_limit');
			switch( substr( $curLimit, -1 ) )
			{
				case 'g': case 'G':
					$curLimit *= 1024;
				case 'm': case 'M':
					$curLimit *= 1024;
				case 'k': case 'K':
					$curLimit *= 1024;
			}

			$this->_memoryLimit = intval( $curLimit );
		}

		$limit = intval( $limit );
		if ( $limit > $this->_memoryLimit and $this->_memoryLimit > 0)
		{
			@ini_set('memory_limit', $limit);
			$this->_memoryLimit = $limit;
		}
	}

	protected function _checkRewriteSupport()
	{
		if( $this->systemOs == "LINUX" ) return true;

		if( function_exists( 'apache_get_modules' ))
		{
			$apacheModules = apache_get_modules();
			if( in_array( 'mod_rewrite', $apacheModules ) ) return true;
		}

		return false;
	}

}