<?php 
vF_Check();

# ----------------------
# Class: Get Param vF
# Author: Yplit
# Date: 14/6/2012
#-----------------------
class vF_getVf
{
	private static $_instance;
	private $vF;

	public function __construct()
	{
		$this->vF = $GLOBALS['vF'];
	}

	public static function get( $getName )
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		if( isset( self::$_instance->vF->$getName ) )
		{
			return self::$_instance->vF->$getName;
		}

		return false;
	}
}
