<?php 
vF_Check();

# ----------------------
# Class: Init
# Author: Yplit
# Date: 14/6/2012
#-----------------------
class vF_init
{
	private static $_instance;
	public $User;
	public $Client;
	public $Options;
	public $Lang;

	public function __construct()
	{
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function newParam( $paramName, $paramValue = stdClass )
	{
		$this->$paramName = $paramValue;
	}

}