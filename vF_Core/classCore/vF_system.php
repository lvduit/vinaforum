<?php 
vf_check();

# ----------------------
# Class: System Setting
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_system
{
	private $_instance;
	private $_memoryLimit = null;
	private $_system_started;
	public $host = '';
	public $secure = '';
	public $key = '';
	
	public function __construct()
	{
		global $vF;
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
		if( $this->_system_started ) return;

		if( defined( 'VF_MEMORY_LIMIT' ) and VF_MEMORY_LIMIT > 0 )
		{
			$this->setMemoryLimit( VF_MEMORY_LIMIT );
		}

		if (!@ini_get('output_handler')) while (@ob_end_clean());
		error_reporting(E_ALL | E_STRICT & ~8192);

		date_default_timezone_set('UTC');

		$this->host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'] );

		$this->secure = ( isset( $_SERVER['HTTPS'] ) and $_SERVER['HTTPS'] == 'on' );

		$this->_system_started = true;
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

}